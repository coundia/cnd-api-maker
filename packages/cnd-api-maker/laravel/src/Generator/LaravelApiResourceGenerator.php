<?php

declare(strict_types=1);

namespace CndApiMaker\Laravel\Generator;

use CndApiMaker\Core\Adapter\DefinitionAdapter;
use CndApiMaker\Core\Definition\Config;
use CndApiMaker\Core\Definition\DefinitionLoader;
use CndApiMaker\Core\Generator\ResourceGenerator;

final readonly class LaravelApiResourceGenerator
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

            $framework = (string) (
            ($globalConfig['app']['framework'] ?? null)
                ?: ($resourceDef->app->framework ?? null)
                ?: 'laravel'
            );

            if (!isset($resourceDef->driver) || (string) $resourceDef->driver === '') {
                $resourceDef->driver = 'eloquent';
            } else {
                $resourceDef->driver = 'eloquent';
            }


            $result = $this->coreGenerator->generate(
                $resourceDef,
                $framework,
                $this->projectDir,
                $force,
                $dryRun,
                $globalConfig
            );

            $files = array_merge($files, $result->files);
        }

        return $files;
    }
}
