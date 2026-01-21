<?php

declare(strict_types=1);

namespace CndApiMaker\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use CndApiMaker\Laravel\Generator\GeneratorPipeline;

final class InstallApiPlatformMakerCommand extends Command
{
    protected $signature = 'cnd:api-maker:install {--force} {--dry-run}';
    protected $description = 'Install CndApiMaker and generate tenant resource once';

    public function __construct(
        private readonly GeneratorPipeline $pipeline
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->callSilent('vendor:publish', [
            '--provider' => 'CndApiMaker\\Laravel\\ApiPlatformMakerServiceProvider',
            '--tag' => 'cnd-api-platform-maker-definitions',
            '--force' => $force,
        ]);

        $tenantJdl = base_path('definitions/tenant.jdl');
        if (!is_file($tenantJdl)) {
            $this->warn('tenant.jdl not found after publish: '.$tenantJdl);
            return self::SUCCESS;
        }

        $markerDir = storage_path('app/cnd-api-platform-maker');
        $markerFile = $markerDir.'/tenant.generated';

        if (is_file($markerFile) && !$force) {
            $this->line('Tenant already generated (marker found).');
            return self::SUCCESS;
        }

        if (!$dryRun) {
            File::ensureDirectoryExists($markerDir);
        }

        $files = $this->pipeline->run($tenantJdl, $force, $dryRun, []);

        foreach ($files as $f) {
            if (is_array($f) && isset($f['type'], $f['path'])) {
                $this->line($f['type'].': '.$f['path']);
            }
        }

        if (!$dryRun) {
            File::put($markerFile, (string) time());
        }

        return self::SUCCESS;
    }
}
