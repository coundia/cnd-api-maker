<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Adapter\Jdl;

use CndApiMaker\Core\Adapter\Jdl\JdlConfig;
use CndApiMaker\Core\Adapter\Jdl\JdlDocument;
use CndApiMaker\Core\Adapter\Jdl\JdlEntity;
use CndApiMaker\Core\Adapter\Jdl\JdlField;
use CndApiMaker\Core\Adapter\Jdl\JdlRelation;
use CndApiMaker\Core\Adapter\Jdl\JdlToResourceMapper;
use CndApiMaker\Core\Generator\Support\Naming;
use PHPUnit\Framework\TestCase;

final class JdlToResourceMapperRelationsManyToManyTest extends TestCase
{
	public function testItShouldCreateJobTaskManyToMany(): void
	{
		$doc = $this->doc();
		$mapper = new JdlToResourceMapper(new Naming());

		$defs = $mapper->map($doc);

		$byEntity = [];
		foreach ($defs as $d) {
			$byEntity[$d->entity] = $d;
		}

		$job = $byEntity['Job'];
		$task = $byEntity['Task'];

		$jobTasks = $this->findField($job->fields, 'tasks', 'relation');
		self::assertNotNull($jobTasks);
		self::assertSame('ManyToMany', $jobTasks->relationKind);
		self::assertSame('Task', $jobTasks->targetEntity);
		self::assertTrue($jobTasks->isCollection);
		self::assertTrue($jobTasks->isOwningSide);
		self::assertSame('jobs', $jobTasks->inversedBy);

		$taskJobs = $this->findField($task->fields, 'jobs', 'relation');
		self::assertNotNull($taskJobs);
		self::assertSame('ManyToMany', $taskJobs->relationKind);
		self::assertSame('Job', $taskJobs->targetEntity);
		self::assertTrue($taskJobs->isCollection);
		self::assertFalse($taskJobs->isOwningSide);
		self::assertSame('tasks', $taskJobs->mappedBy);
	}

	private function findField(array $fields, string $name, string $type): ?object
	{
		foreach ($fields as $f) {
			if (!is_object($f)) {
				continue;
			}
			if (($f->name ?? null) === $name && ($f->type ?? null) === $type) {
				return $f;
			}
		}
		return null;
	}

	private function doc(): JdlDocument
	{
		$config = new JdlConfig(
			framework: 'symfony',
			driver: 'doctrine',
			uriPrefix: '/api/v1',
			uuid: true,
			tenant: false,
			softDeletes: false,
			audit: false,
			factory: false
		);

		$entities = [
			'Job' => new JdlEntity('Job', [
				new JdlField('jobTitle', 'String', true),
			]),
			'Task' => new JdlEntity('Task', [
				new JdlField('title', 'String', true),
			]),
		];

		$enums = [];

		$relations = [
			new JdlRelation('ManyToMany', 'Job', 'tasks', 'Task', 'jobs'),
		];

		return new JdlDocument($config, $entities, $enums, $relations);
	}
}
