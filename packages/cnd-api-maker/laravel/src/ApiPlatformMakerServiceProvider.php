<?php

declare(strict_types=1);

namespace CndApiMaker\Laravel;

use CndApiMaker\Core\Adapter\DefinitionAdapter;
use CndApiMaker\Core\Adapter\Jdl\JdlDefinitionAdapter;
use CndApiMaker\Core\Definition\DefinitionLoader;
use CndApiMaker\Core\Generator\Builders\DtoPropertiesBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelMigrationColumnsBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelNativeControllerVarsBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelNativeRequestRulesBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelNativeRequestVarsBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelNativeResourceVarsBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelNativeRoutesVarsBuilder;
use CndApiMaker\Core\Generator\Builders\LaravelStateBuilders;
use CndApiMaker\Core\Generator\Builders\OpenApiSchemaVarsBuilder;
use CndApiMaker\Core\Generator\Common\DtoGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelCommunGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelFactoryGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelMigrationGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelModelGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelTestSupportGenerator;
use CndApiMaker\Core\Generator\Laravel\LaravelTestsGenerator;
use CndApiMaker\Core\Generator\LaravelNative\LaravelNativeHttpGenerator;
use CndApiMaker\Core\Generator\ResourceGenerator;
use CndApiMaker\Core\Generator\Strategy\LaravelEloquentResourceStrategy;
use CndApiMaker\Core\Generator\Support\FieldConstraints;
use CndApiMaker\Core\Generator\Support\FieldTypeResolver;
use CndApiMaker\Core\Generator\Support\LaravelCastResolver;
use CndApiMaker\Core\Generator\Support\Naming;
use CndApiMaker\Core\Generator\Support\UniqueFieldPicker;
use CndApiMaker\Core\Renderer\StubRepository;
use CndApiMaker\Core\Renderer\TemplateRenderer;
use CndApiMaker\Core\Writer\FileWriter;
use CndApiMaker\Laravel\Console\InstallApiPlatformMakerCommand;
use CndApiMaker\Laravel\Console\MakeApiResourceCommand;
use CndApiMaker\Laravel\Generator\GeneratorPipeline;
use CndApiMaker\Laravel\Generator\LaravelApiResourceGenerator;
use Illuminate\Support\ServiceProvider;

final class ApiPlatformMakerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeApiResourceCommand::class,
            InstallApiPlatformMakerCommand::class,
        ]);

        $this->app->singleton(StubRepository::class, function () {
            $stubsDir = realpath(__DIR__ . '/../../core/stubs') ?: (__DIR__ . '/../../core/stubs');
            return new StubRepository($stubsDir);
        });

        $this->app->singleton(TemplateRenderer::class, fn () => new TemplateRenderer());
        $this->app->singleton(FileWriter::class, fn () => new FileWriter());
        $this->app->singleton(Naming::class, fn () => new Naming());

        $this->app->singleton(DefinitionLoader::class, fn () => new DefinitionLoader());
        $this->app->singleton(FieldConstraints::class, fn () => new FieldConstraints());
        $this->app->singleton(FieldTypeResolver::class, fn () => new FieldTypeResolver());
        $this->app->singleton(UniqueFieldPicker::class, fn () => new UniqueFieldPicker());

        $this->app->singleton(DtoPropertiesBuilder::class, fn ($app) => new DtoPropertiesBuilder(
            $app->make(Naming::class),
            $app->make(FieldTypeResolver::class),
            $app->make(FieldConstraints::class)
        ));

        $this->app->singleton(DtoGenerator::class, fn ($app) => new DtoGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(DtoPropertiesBuilder::class)
        ));

        $this->app->singleton(LaravelStateBuilders::class, fn ($app) => new LaravelStateBuilders(
            $app->make(Naming::class),
            $app->make(FieldTypeResolver::class)
        ));

        $this->app->singleton(LaravelModelGenerator::class, fn ($app) => new LaravelModelGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(Naming::class),
            $app->make(LaravelCastResolver::class)
        ));

        $this->app->singleton(LaravelNativeControllerVarsBuilder::class, fn () => new LaravelNativeControllerVarsBuilder());

        $this->app->singleton(LaravelNativeResourceVarsBuilder::class, fn ($app) => new LaravelNativeResourceVarsBuilder(
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelNativeRoutesVarsBuilder::class, fn ($app) => new LaravelNativeRoutesVarsBuilder(
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelNativeRequestRulesBuilder::class, fn ($app) => new LaravelNativeRequestRulesBuilder(
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelNativeRequestVarsBuilder::class, fn ($app) => new LaravelNativeRequestVarsBuilder(
            $app->make(LaravelNativeRequestRulesBuilder::class)
        ));

        $this->app->singleton(LaravelCommunGenerator::class, fn ($app) => new LaravelCommunGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(LaravelStateBuilders::class),
            $app->make(LaravelNativeControllerVarsBuilder::class),
            $app->make(LaravelNativeResourceVarsBuilder::class),
            $app->make(LaravelNativeRoutesVarsBuilder::class),
            $app->make(LaravelNativeRequestVarsBuilder::class),
            $app->make(OpenApiSchemaVarsBuilder::class),
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelTestSupportGenerator::class, fn ($app) => new LaravelTestSupportGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class)
        ));

        $this->app->singleton(LaravelTestsGenerator::class, fn ($app) => new LaravelTestsGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(UniqueFieldPicker::class),
            $app->make(Naming::class),
            $app->make(LaravelTestSupportGenerator::class)
        ));

        $this->app->singleton(LaravelMigrationColumnsBuilder::class, fn () => new LaravelMigrationColumnsBuilder());

        $this->app->singleton(LaravelMigrationGenerator::class, fn ($app) => new LaravelMigrationGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(LaravelMigrationColumnsBuilder::class),
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelFactoryGenerator::class, fn ($app) => new LaravelFactoryGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(Naming::class)
        ));

        $this->app->singleton(LaravelEloquentResourceStrategy::class, fn ($app) => new LaravelEloquentResourceStrategy(
            $app->make(DtoGenerator::class),
            $app->make(LaravelModelGenerator::class),
            $app->make(LaravelCommunGenerator::class),
            $app->make(LaravelTestsGenerator::class),
            $app->make(LaravelMigrationGenerator::class),
            $app->make(LaravelFactoryGenerator::class)
        ));

        $this->app->singleton(ResourceGenerator::class, fn ($app) => new ResourceGenerator([
            $app->make(LaravelEloquentResourceStrategy::class),
        ]));

        $this->app->singleton(DefinitionAdapter::class, fn ($app) => $app->make(JdlDefinitionAdapter::class));

        $this->app->singleton(LaravelApiResourceGenerator::class, fn ($app) => new LaravelApiResourceGenerator(
            $app->make(DefinitionLoader::class),
            $app->make(ResourceGenerator::class),
            base_path(),
            $app->make(DefinitionAdapter::class)
        ));

        $this->app->singleton(GeneratorPipeline::class, fn ($app) => new GeneratorPipeline(
            $app->make(LaravelApiResourceGenerator::class)
        ));

        $this->app->singleton(LaravelNativeHttpGenerator::class, fn ($app) => new LaravelNativeHttpGenerator(
            $app->make(StubRepository::class),
            $app->make(TemplateRenderer::class),
            $app->make(FileWriter::class),
            $app->make(Naming::class)
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../definitions' => base_path('definitions'),
        ], 'cnd-api-platform-maker-definitions');
    }
}
