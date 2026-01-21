<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Adapter\Jdl;

use CndApiMaker\Core\Adapter\Jdl\JdlConfig;
use CndApiMaker\Core\Adapter\Jdl\JdlDocument;
use CndApiMaker\Core\Adapter\Jdl\JdlToResourceMapper;
use CndApiMaker\Core\Generator\Support\Naming;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class JdlToResourceMapperRelationsTest extends TestCase
{
	public function testItShouldCreateEmployeeJobOneToMany(): void
	{
		$doc = $this->docForEmployeeJobOneToMany();
		$mapper = new JdlToResourceMapper(new Naming());

		$defs = $mapper->map($doc);

		$byEntity = [];
		foreach ($defs as $d) {
			$byEntity[$d->entity] = $d;
		}

		self::assertArrayHasKey('Employee', $byEntity);
		self::assertArrayHasKey('Job', $byEntity);

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

	private function docForEmployeeJobOneToMany(): JdlDocument
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

		$employee = $this->makeJdlEntity([
			'fields' => [
				$this->makeJdlField(['name' => 'firstName', 'type' => 'String', 'required' => true]),
				$this->makeJdlField(['name' => 'lastName', 'type' => 'String', 'required' => true]),
			],
		]);

		$job = $this->makeJdlEntity([
			'fields' => [
				$this->makeJdlField(['name' => 'jobTitle', 'type' => 'String', 'required' => true]),
			],
		]);

		$entities = [
			'Employee' => $employee,
			'Job' => $job,
		];

		$relations = [
			$this->makeJdlRelation([
				'kind' => 'OneToMany',
				'fromEntity' => 'Employee',
				'toEntity' => 'Job',
				'fromField' => null,
				'toField' => 'employee',
			]),
		];

		$enums = [];

		return new JdlDocument($config, $entities, $enums, $relations);
	}

	private function makeJdlEntity(array $props): object
	{
		return $this->make('CndApiMaker\\Core\\Adapter\\Jdl\\JdlEntity', $props);
	}

	private function makeJdlField(array $props): object
	{
		return $this->make('CndApiMaker\\Core\\Adapter\\Jdl\\JdlField', $props);
	}

	private function makeJdlRelation(array $props): object
	{
		return $this->make('CndApiMaker\\Core\\Adapter\\Jdl\\JdlRelation', $props);
	}

	private function make(string $class, array $props): object
	{
		$ref = new ReflectionClass($class);

		$obj = $ref->getConstructor() === null
			? $ref->newInstance()
			: $ref->newInstanceWithoutConstructor();

		foreach ($props as $k => $v) {
			$obj->{$k} = $v;
		}

		return $obj;
	}
}
