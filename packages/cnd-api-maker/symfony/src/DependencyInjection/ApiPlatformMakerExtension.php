<?php

declare(strict_types=1);

namespace CndApiMaker\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class ApiPlatformMakerExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container): void
	{
		$loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../resources/config'));
		$loader->load('services.php');
	}
}
