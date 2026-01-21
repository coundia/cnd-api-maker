<?php

declare(strict_types=1);

use CndApiMaker\Core\Adapter\Jdl\JdlDefinitionAdapter;
use CndApiMaker\Core\Adapter\Jdl\JdlParser;
use CndApiMaker\Core\Adapter\Jdl\JdlToResourceMapper;
use CndApiMaker\Core\Definition\DefinitionLoader;
use CndApiMaker\Core\Generator\Builders\DtoPropertiesBuilder;
use CndApiMaker\Core\Generator\Common\DtoGenerator;
use CndApiMaker\Core\Generator\Common\SymfonyGeneratorCommun;
use CndApiMaker\Core\Generator\ResourceGenerator;
use CndApiMaker\Core\Generator\Strategy\SymfonyDoctrineResourceStrategy;
use CndApiMaker\Core\Generator\Support\Naming;
use CndApiMaker\Core\Generator\Support\FieldTypeResolver;
use CndApiMaker\Core\Generator\Support\UniqueFieldPicker;
use CndApiMaker\Core\Generator\Symfony\State\ApplyAssignmentsBuilder;
use CndApiMaker\Core\Generator\Symfony\State\Base64FeatureResolver;
use CndApiMaker\Core\Generator\Symfony\State\MapperAssignmentsBuilder;
use CndApiMaker\Core\Generator\Symfony\State\RepositoryMethodsBuilder;
use CndApiMaker\Core\Generator\Symfony\State\SymfonyStateFilesWriter;
use CndApiMaker\Core\Generator\Symfony\State\SymfonyStatePlanBuilder;
use CndApiMaker\Core\Generator\Symfony\SymfonyEntityGenerator;
use CndApiMaker\Core\Generator\Symfony\SymfonyFactoriesGenerator;
use CndApiMaker\Core\Generator\Symfony\SymfonyStateGenerator;
use CndApiMaker\Core\Generator\Symfony\SymfonyTestsGenerator;
use CndApiMaker\Core\Renderer\StubRepository;
use CndApiMaker\Core\Renderer\TemplateRenderer;
use CndApiMaker\Core\Writer\FileWriter;
use CndApiMaker\Symfony\Command\MakeApiResourceCommand;
use CndApiMaker\Symfony\Generator\GeneratorPipeline;
use CndApiMaker\Symfony\Generator\SymfonyApiResourceGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $c): void {
	$services = $c->services()
		->defaults()
		->autowire()
		->autoconfigure()
		->private();

	$stubsDir = dirname(__DIR__, 2).'/../core/stubs';

	$services->set(Naming::class);
	$services->set(FieldTypeResolver::class);
	$services->set(Naming::class);
	$services->set(UniqueFieldPicker::class);

	$services->set(StubRepository::class)->args([$stubsDir]);
	$services->set(TemplateRenderer::class);
	$services->set(FileWriter::class);

	$services->set(DefinitionLoader::class);

	$services->set(CndApiMaker\Core\Generator\Support\DoctrineColumnResolver::class);
	$services->set(CndApiMaker\Core\Generator\Support\FieldConstraints::class);
	$services->set(CndApiMaker\Core\Generator\Common\InputCommun::class);

	$services->set(DtoPropertiesBuilder::class);
	$services->set(DtoGenerator::class);

	$services->set(SymfonyEntityGenerator::class);
	$services->set(SymfonyStateGenerator::class);
	$services->set(SymfonyTestsGenerator::class);
	$services->set(SymfonyFactoriesGenerator::class);
	$services->set(SymfonyGeneratorCommun::class);

	$services->set(Base64FeatureResolver::class);

	$services->set(MapperAssignmentsBuilder::class)
		->args([service(Naming::class), service(FieldTypeResolver::class)]);

	$services->set(ApplyAssignmentsBuilder::class)
		->args([service(Naming::class), service(FieldTypeResolver::class)]);

	$services->set(RepositoryMethodsBuilder::class);

	$services->set(SymfonyStatePlanBuilder::class)
		->args([
			service(Base64FeatureResolver::class),
			service(MapperAssignmentsBuilder::class),
			service(ApplyAssignmentsBuilder::class),
			service(RepositoryMethodsBuilder::class),
		]);

	$services->set(SymfonyStateFilesWriter::class)
		->args([service(StubRepository::class), service(TemplateRenderer::class), service(FileWriter::class)]);

	$services->set(SymfonyDoctrineResourceStrategy::class)
		->tag('apiplatform_maker.resource_strategy');

	$services->set(ResourceGenerator::class)
		->args([tagged_iterator('apiplatform_maker.resource_strategy')]);

	$services->set(JdlParser::class);
	$services->set(JdlToResourceMapper::class);

	$services->set(JdlDefinitionAdapter::class)
		->args([service(JdlParser::class), service(JdlToResourceMapper::class)]);

	$services->set(SymfonyApiResourceGenerator::class)
		->args([
			service(DefinitionLoader::class),
			service(ResourceGenerator::class),
			'%kernel.project_dir%',
			service(JdlDefinitionAdapter::class),
		]);

	$services->set(GeneratorPipeline::class)
		->args([service(SymfonyApiResourceGenerator::class)]);

	$services->set(MakeApiResourceCommand::class)
		->args([service(GeneratorPipeline::class), '%kernel.project_dir%'])
		->tag('console.command');
};
