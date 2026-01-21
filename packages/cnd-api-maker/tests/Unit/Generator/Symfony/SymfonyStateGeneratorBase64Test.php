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
use CndApiMaker\Core\Generator\Support\Naming;
use CndApiMaker\Core\Generator\Support\FieldTypeResolver;
use CndApiMaker\Core\Generator\Symfony\SymfonyStateGenerator;
use CndApiMaker\Core\Renderer\StubRepository;
use CndApiMaker\Core\Renderer\TemplateRenderer;
use CndApiMaker\Core\Writer\FileWriter;
use PHPUnit\Framework\TestCase;

final class SymfonyStateGeneratorBase64Test extends TestCase
{
	public function testItShouldInjectBase64ServiceAndGenerateApplyAssignmentsForBlobFields(): void
	{
		$stubs = $this->createMock(StubRepository::class);
		$renderer = new TemplateRenderer();
		$writer = $this->createMock(FileWriter::class);
		$names = $this->createMock(Naming::class);
		$types = new FieldTypeResolver();

		$names->method('camel')->willReturnCallback(static function (string $s): string {
			$s = str_replace(['-', '_'], ' ', $s);
			$s = ucwords($s);
			$s = str_replace(' ', '', $s);
			return lcfirst($s);
		});

		$stubs->method('get')->willReturnMap([
			['symfony/state.repository', $this->repoStub()],
			['symfony/state.mapper', $this->mapperStub()],
			['symfony/state.payload_resolver', $this->payloadStub()],
			['symfony/state.collection_provider', $this->collectionStub()],
			['symfony/state.item_provider', $this->itemStub()],
			['symfony/state.write_processor', $this->writeProcessorStub()],
			['symfony/state.delete_processor', $this->deleteProcessorStub()],
		]);

		$profiles = new FieldDefinition('profiles', 'blob', false, false, true, null);
		$profiles->fillable = true;

		$salary = new FieldDefinition('salary', 'long', false, false, true, null);
		$salary->fillable = true;

		$api = new ApiDefinition('/api/v1', 'employee:read', 'employee:write', true);
		$features = new FeaturesDefinition( );
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
			fields: [$salary, $profiles],
			storage: $storage
		);

		$ctx = GenerationContext::from($def, 'symfony', '/app', true, true);

		$captures = [];

		$writer->expects(self::exactly(7))->method('write')->willReturnCallback(
			static function (string $path, string $content) use (&$captures): void {
				$captures[$path] = $content;
			}
		);

		$g = new SymfonyStateGenerator($stubs, $renderer, $writer, $names, $types);
		$g->generate($ctx);

		$mapperPath = '/app/src/State/Employee/EmployeeMapper.php';
		$writePath = '/app/src/State/Employee/EmployeeWriteProcessor.php';

		self::assertArrayHasKey($mapperPath, $captures);
		self::assertArrayHasKey($writePath, $captures);

		self::assertStringContainsString('use App\\Service\\Base64FileService;', $captures[$mapperPath]);
		self::assertStringContainsString('private Base64FileService $base64Files', $captures[$mapperPath]);
		self::assertStringContainsString('$stored = $base64Files->store((string) $input->profiles, \'employee/profiles\');', $captures[$mapperPath]);
		self::assertStringContainsString('$entity->profiles = $stored->storageKey;', $captures[$mapperPath]);

		self::assertStringContainsString('use App\\Service\\Base64FileService;', $captures[$writePath]);
		self::assertStringContainsString('private Base64FileService $base64Files', $captures[$writePath]);
	}

	private function repoStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Repository;

final class {{entity}}Repository
{
}
PHP;
	}

	private function mapperStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

{{base64Uses}}
final readonly class {{entity}}Mapper
{
    public function __construct(
        private {{entity}}PayloadResolver $resolver{{base64CtorArg}}
    ) {
    }

    public function applyInput(object $entity, object $input): void
    {
{{applyAssignments}}
    }
}
PHP;
	}

	private function payloadStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

final class {{entity}}PayloadResolver
{
    public function resolve(string $id, string $entity): ?object
    {
        return null;
    }

    public function resolveNullable(mixed $id, string $entity): ?object
    {
        return null;
    }
}
PHP;
	}

	private function collectionStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

final class {{entity}}CollectionProvider
{
}
PHP;
	}

	private function itemStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

final class {{entity}}ItemProvider
{
}
PHP;
	}

	private function writeProcessorStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
{{base64Uses}}

final readonly class {{entity}}WriteProcessor implements ProcessorInterface
{
    public function __construct(
        private {{entity}}Mapper $mapper{{base64CtorArg}}
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        return $data;
    }
}
PHP;
	}

	private function deleteProcessorStub(): string
	{
		return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\State\{{entity}};

final class {{entity}}DeleteProcessor
{
}
PHP;
	}
}
