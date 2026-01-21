<?php

declare(strict_types=1);

namespace CndApiMaker\Symfony\Generator;

interface GeneratorInterface
{
	public function generate(string $definitionFile, bool $force, bool $dryRun): array;
}
