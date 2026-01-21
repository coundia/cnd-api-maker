<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Adapter\Jdl;

use CndApiMaker\Core\Adapter\Jdl\JdlConfig;
use CndApiMaker\Core\Adapter\Jdl\JdlDocument;
use CndApiMaker\Core\Adapter\Jdl\JdlToResourceMapper;
use CndApiMaker\Core\Generator\Support\Naming;
use PHPUnit\Framework\TestCase;

final class JdlToResourceMapperRelationsOneToManyTest extends TestCase
{
	public function testItShouldCreateEmployeeJobOneToMany(): void
	{
		$doc = $this->doc();
		$mapper = new JdlToResourceMapper(new Naming());

		$defs = $mapper->map($doc);

		$byEntity = [];
		foreach ($defs as $d) {
			$byEntity[$d->entity] = $d;
		}

		$employee = $byEntity['Employee'];
		$job = $byEntity['Job'];

		$employeeJobs = $this->findField($employee->fields, 'jobs', 'relation');
		self::assertNotNull($employeeJobs);
		self::assertSame('OneToMany', $employeeJobs->relationKind);
		self::assertSame('Job', $employeeJobs->targetEntity);
		self::assertTrue($employeeJobs->isCollection);
		self::assertFalse($employeeJobs->isOwningSide);
		self::assertSame('employee', $employeeJobs->mappedBy);

		$jobEmployee = $this->findField($job->fields, 'employee', 'relation');
		self::assertNotNull($jobEmployee);
		self::assertSame('ManyToOne', $jobEmployee->relationKind);
		self::assertSame('Employee', $jobEmployee->targetEntity);
		self::assertFalse($jobEmployee->isCollection);
		self::assertTrue($jobEmployee->isOwningSide);
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
			'Employee' => new \CndApiMaker\Core\Adapter\Jdl\JdlEntity('Employee', [
				new \CndApiMaker\Core\Adapter\Jdl\JdlField('firstName', 'String', true),
				new \CndApiMaker\Core\Adapter\Jdl\JdlField('lastName', 'String', true),
			]),
			'Job' => new \CndApiMaker\Core\Adapter\Jdl\JdlEntity('Job', [
				new \CndApiMaker\Core\Adapter\Jdl\JdlField('jobTitle', 'String', true),
			]),
		];

		$enums = [];

		$relations = [
			new \CndApiMaker\Core\Adapter\Jdl\JdlRelation('OneToMany', 'Employee', 'jobs', 'Job', 'employee'),
		];

		return new JdlDocument($config, $entities, $enums, $relations);
	}
}
