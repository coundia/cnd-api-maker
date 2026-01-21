<?php

declare(strict_types=1);

namespace CndApiMaker\Laravel\Generator;

final readonly class GeneratorPipeline
{
    public function __construct(
        private LaravelApiResourceGenerator $laravel
    ) {
    }

    public function run(string $definitionFile, bool $force, bool $dryRun, array $config = []): array
    {
        return $this->laravel->generate($definitionFile, $force, $dryRun, $config);
    }
}
