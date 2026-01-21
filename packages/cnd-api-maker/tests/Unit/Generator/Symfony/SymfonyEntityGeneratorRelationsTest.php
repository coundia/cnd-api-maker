<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Generator\Symfony;

use CndApiMaker\Core\Definition\ApiDefinition;
use CndApiMaker\Core\Definition\FeaturesDefinition;
use CndApiMaker\Core\Definition\FieldDefinition;
use CndApiMaker\Core\Definition\ResourceDefinition;
use CndApiMaker\Core\Definition\StorageDefinition;
use CndApiMaker\Core\Definition\TestsDefinition;
use CndApiMaker\Core\Generator\GenerationContext;
use CndApiMaker\Core\Generator\Support\DoctrineColumnResolver;
use CndApiMaker\Core\Generator\Support\Naming;
use CndApiMaker\Core\Renderer\StubRepository;
use CndApiMaker\Core\Renderer\TemplateRenderer;
use CndApiMaker\Core\Generator\Support\Naming;
use CndApiMaker\Core\Writer\FileWriter;
use CndApiMaker\Core\Generator\Symfony\SymfonyEntityGenerator;
use PHPUnit\Framework\TestCase;

final class SymfonyEntityGeneratorRelationsTest extends TestCase
{
	public function testItShouldRenderManyToOneAndOneToManyBlocks(): void
	{
		$stubs = $this->createMock(StubRepository::class);
		$renderer = new TemplateRenderer();
		$writer = $this->createMock(FileWriter::class);
		$names = $this->createMock(Naming::class);
		$naming = new Naming();
		$doctrine = new DoctrineColumnResolver();

		$names->method('camel')->willReturnCallback(static function (string $s): string {
			$s = str_replace(['-', '_'], ' ', $s);
			$s = ucwords($s);
			$s = str_replace(' ', '', $s);
			return lcfirst($s);
		});

		$stubs->method('get')->with('symfony/entity')->willReturn($this->entityStub());

		$writer->expects(self::once())
			->method('write')
			->with(
				self::stringContains('src/Entity/Employee.php'),
				self::callback(static function (string $content): bool {
					return str_contains($content, 'use Doctrine\\Common\\Collections\\ArrayCollection;')
						&& str_contains($content, 'use Doctrine\\Common\\Collections\\Collection;')
						&& str_contains($content, '#[ORM\\OneToMany')
						&& str_contains($content, 'mappedBy: \'employee\'')
						&& str_contains($content, 'targetEntity: Job::class')
						&& str_contains($content, 'public Collection $jobs;');
				}),
				true,
				true
			);

		$employeeJobs = new FieldDefinition('jobs', 'relation', false, false, false, null);
		$employeeJobs->relationKind = 'OneToMany';
		$employeeJobs->targetEntity = 'Job';
		$employeeJobs->isCollection = true;
		$employeeJobs->isOwningSide = false;
		$employeeJobs->mappedBy = 'employee';

		$api = new ApiDefinition('/api/v1', 'employee:read', 'employee:write', true);
		$features = new FeaturesDefinition();
		$tests = new TestsDefinition(true, 'auto', 'none');
		$storage = new StorageDefinition();
		$storage->table = 'employees';

		$def = new ResourceDefinition(
			entity: 'Employee',
			table: 'employees',
			driver: 'doctrine',
			module: null,
			api: $api,
			features: $features,
			tests: $tests,
			fields: [$employeeJobs],
			storage: $storage
		);

		$ctx = GenerationContext::from($def, 'symfony', '/app', true, true);

		$g = new SymfonyEntityGenerator($stubs, $renderer, $writer, $names, $naming, $doctrine);
		$g->generate($ctx);
	}

	private function entityStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
{{extraUses}}

#[ORM\Entity]
class {{entity}}
{
{{constructor}}
{{idProperty}}
{{properties}}
}
PHP;
	}
}
