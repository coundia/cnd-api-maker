<?php

declare(strict_types=1);

namespace CndApiMaker\Symfony\Command;

use CndApiMaker\Symfony\Generator\GeneratorPipeline;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'cnd:api-maker:generate',
    description: 'Generate an API Platform resource (DTO/Entity/State/Tests) from a definition file (jdl).'
)]
final class MakeApiResourceCommand extends Command
{
    public function __construct(
        private readonly GeneratorPipeline $pipeline,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Path to  JDL definition file')
            ->addOption('config', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Path to PHP config file(s) returning an array')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing files')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $definition = (string) $input->getOption('file');
        if ($definition === '') {
            $output->writeln('<error>Missing --file</error>');
            return Command::FAILURE;
        }

        $definitionPath = $this->toAbsPath($definition);

        $includes = (array) $input->getOption('config');
        $config = $this->loadConfigFiles($includes);

        $force = (bool) $input->getOption('force');
        $dryRun = (bool) $input->getOption('dry-run');

        $files = $this->pipeline->run($definitionPath, $force, $dryRun, $config);

        foreach ($files as $f) {
            if (is_array($f) && isset($f['type'], $f['path'])) {
                $output->writeln($f['type'].': '.$f['path']);
            }
        }

        return Command::SUCCESS;
    }

    private function toAbsPath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim($this->projectDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
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
