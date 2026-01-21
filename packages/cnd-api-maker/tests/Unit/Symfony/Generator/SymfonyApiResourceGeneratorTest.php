<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Symfony\Generator;

use CndApiMaker\Core\Adapter\DefinitionAdapter;
use CndApiMaker\Core\Definition\DefinitionLoader;
use CndApiMaker\Core\Definition\ResourceDefinition;
use CndApiMaker\Core\Generator\GenerationResult;
use CndApiMaker\Core\Generator\ResourceGenerator;
use CndApiMaker\Symfony\Generator\SymfonyApiResourceGenerator;
use PHPUnit\Framework\TestCase;

final class SymfonyApiResourceGeneratorTest extends TestCase
{
	public function testItShouldUseLoaderForNonJdl(): void
	{
		$loader = $this->createMock(DefinitionLoader::class);
		$core = $this->createMock(ResourceGenerator::class);
		$adapter = $this->createMock(DefinitionAdapter::class);

		$def = $this->fakeResourceDefinition('Foo');
		$loader->expects(self::once())->method('load')->with('/tmp/foo.json')->willReturn($def);
		$adapter->expects(self::never())->method('fromFile');

		$core->expects(self::once())->method('generate')->with(
			$def,
			'symfony',
			'/app',
			true,
			false
		)->willReturn(new GenerationResult([['path' => '/x', 'type' => 't']]));

		$g = new SymfonyApiResourceGenerator($loader, $core, '/app', $adapter);

		$files = $g->generate('/tmp/foo.json', true, false);

		self::assertCount(1, $files);
		self::assertSame('/x', $files[0]['path']);
	}

	public function testItShouldUseJdlAdapterForJdl(): void
	{
		$loader = $this->createMock(DefinitionLoader::class);
		$core = $this->createMock(ResourceGenerator::class);
		$adapter = $this->createMock(DefinitionAdapter::class);

		$defA = $this->fakeResourceDefinition('Employee');
		$defB = $this->fakeResourceDefinition('Job');

		$adapter->expects(self::once())->method('fromFile')->with('/tmp/model.jdl')->willReturn([$defA, $defB]);
		$adapter->expects(self::exactly(2))->method('getFramework')->willReturn('symfony');

		$loader->expects(self::never())->method('load');

		$core->expects(self::exactly(2))->method('generate')->willReturnOnConsecutiveCalls(
			new GenerationResult([['path' => '/a', 'type' => 't']]),
			new GenerationResult([['path' => '/b', 'type' => 't']]),
		);

		$g = new SymfonyApiResourceGenerator($loader, $core, '/app', $adapter);

		$files = $g->generate('/tmp/model.jdl', true, false);

		self::assertSame(['/a', '/b'], array_map(static fn (array $x) => $x['path'], $files));
	}

	private function fakeResourceDefinition(string $entity): ResourceDefinition
	{
		$api = new \CndApiMaker\Core\Definition\ApiDefinition('/api/v1', 'x:read', 'x:write', true);
		$features = new \CndApiMaker\Core\Definition\FeaturesDefinition( );
		$tests = new \CndApiMaker\Core\Definition\TestsDefinition(true, 'auto', 'none');
		$storage = new \CndApiMaker\Core\Definition\StorageDefinition();
		$storage->table = 't_'.$entity;

		return new ResourceDefinition(
			entity: $entity,
			table: 't_'.$entity,
			driver: 'doctrine',
			module: null,
			api: $api,
			features: $features,
			tests: $tests,
			fields: [],
			storage: $storage
		);
	}
}
