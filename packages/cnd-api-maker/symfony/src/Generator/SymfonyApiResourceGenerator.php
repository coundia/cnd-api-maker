<?php

declare(strict_types=1);

namespace CndApiMaker\Symfony\Generator;

use CndApiMaker\Core\Adapter\DefinitionAdapter;
use CndApiMaker\Core\Definition\Config;
use CndApiMaker\Core\Definition\DefinitionLoader;
use CndApiMaker\Core\Generator\ResourceGenerator;

final readonly class SymfonyApiResourceGenerator implements GeneratorInterface
{
	public function __construct(
		private DefinitionLoader $loader,
		private ResourceGenerator $coreGenerator,
		private string $projectDir,
		private DefinitionAdapter $jdlAdapter
	) {
	}

	public function generate(string $definitionFile, bool $force, bool $dryRun, array $globalConfig = []): array
	{
		$ext = strtolower((string) pathinfo($definitionFile, PATHINFO_EXTENSION));

		$defs = $ext === 'jdl'
			? $this->jdlAdapter->fromFile($definitionFile)
			: [$this->loader->load($definitionFile)];


		$files = [];

		foreach ($defs as $resourceDef) {

			Config::applyGlobalDefaults($resourceDef, $globalConfig);

			$framework = $ext === 'jdl'
				? $this->jdlAdapter->getFramework()
				: ((string) (($resourceDef->app->framework ?? null) ?: 'auto'));

			$result = $this->coreGenerator->generate(
				$resourceDef,
				$framework,
				$this->projectDir,
				$force,
				$dryRun
			);

			$files = array_merge($files, $result->files);
		}

		return $files;
	}
}
