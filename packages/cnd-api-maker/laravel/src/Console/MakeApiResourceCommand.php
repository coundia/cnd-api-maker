<?php

declare(strict_types=1);

namespace CndApiMaker\Laravel\Console;

use CndApiMaker\Laravel\Generator\GeneratorPipeline;
use Illuminate\Console\Command;

final class MakeApiResourceCommand extends Command
{
    protected $signature = 'cnd:api-maker:generate
        {--file=}
        {--config=*}
        {--force}
        {--dry-run}';

    protected $description = 'Generate API Platform resource from a definition file jdl';

    public function __construct(
        private readonly GeneratorPipeline $pipeline
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $definitionPath = (string) $this->option('file');
        if ($definitionPath === '') {
            $this->error('Missing --file=path.jdl');
            return self::FAILURE;
        }

        $configPaths = (array) $this->option('config');
        $config = $this->loadConfigFiles($configPaths);

        $files = $this->pipeline->run(
            $this->toAbsPath($definitionPath),
            (bool) $this->option('force'),
            (bool) $this->option('dry-run'),
            $config
        );

        foreach ($files as $f) {
            if (is_array($f) && isset($f['type'], $f['path'])) {
                $this->line($f['type'].': '.$f['path']);
            }
        }

        return self::SUCCESS;
    }

    private function toAbsPath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim(base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
    }

    private function loadConfigFiles(array $paths): array
    {
        $merged = [];

        foreach ($paths as $p) {
            $file = $this->toAbsPath((string) $p);

            if ($file === '' || !is_file($file)) {
                throw new \RuntimeException(sprintf('Config file not found: %s', $file));
            }

            $data = require $file;

            if (!is_array($data)) {
                throw new \RuntimeException(sprintf('Config file must return an array: %s', $file));
            }

            $merged = $this->mergeRecursiveDistinct($merged, $data);
        }

        return $merged;
    }

    private function mergeRecursiveDistinct(array $base, array $over): array
    {
        foreach ($over as $k => $v) {
            if (is_array($v) && isset($base[$k]) && is_array($base[$k])) {
                $base[$k] = $this->mergeRecursiveDistinct($base[$k], $v);
                continue;
            }
            $base[$k] = $v;
        }

        return $base;
    }
}
