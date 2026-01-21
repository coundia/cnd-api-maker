<?php

declare(strict_types=1);

namespace CndApiMaker\Symfony\Generator;

final readonly class GeneratorPipeline
{
	public function __construct(
		private SymfonyApiResourceGenerator $symfony
	) {
	}

	public function run(string $definitionFile, bool $force, bool $dryRun, array $config = []): array
	{
		return $this->symfony->generate($definitionFile, $force, $dryRun, $config);
	}
}
