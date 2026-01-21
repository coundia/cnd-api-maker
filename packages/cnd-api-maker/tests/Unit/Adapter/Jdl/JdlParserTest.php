<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Adapter\Jdl;

use CndApiMaker\Core\Adapter\Jdl\JdlParser;
use PHPUnit\Framework\TestCase;

final class JdlParserTest extends TestCase
{
	public function testItShouldParseConfigEntitiesEnumsAndRelations(): void
	{
		$jdl = <<<'JDL'
config {
  framework symfony
  driver doctrine
  uriPrefix /api/v1
  uuid true
  features {
    tenant false
    softDeletes true
    audit false
    factory false
  }
}

entity Employee {
  firstName String required
  lastName String required
}

entity Job {
  jobTitle String required
}

entity Task {
  title String
}

enum Language {
  FRENCH, ENGLISH, SPANISH
}

relationship OneToMany {
  Employee{jobs} to Job{employee}
}

relationship ManyToMany {
  Job{tasks} to Task{jobs}
}

relationship OneToOne {
  Job{managerTask} to Task
}
JDL;

		$parser = new JdlParser();
		$doc = $parser->parse($jdl);

		self::assertSame('symfony', $doc->config->framework);
		self::assertSame('doctrine', $doc->config->driver);
		self::assertSame('/api/v1', $doc->config->uriPrefix);
		self::assertTrue($doc->config->uuid);
		self::assertFalse($doc->config->tenant);
		self::assertTrue($doc->config->softDeletes);

		self::assertArrayHasKey('Employee', $doc->entities);
		self::assertArrayHasKey('Job', $doc->entities);
		self::assertArrayHasKey('Task', $doc->entities);

		self::assertCount(2, $doc->entities['Employee']->fields);
		self::assertSame('firstName', $doc->entities['Employee']->fields[0]->name);
		self::assertSame('String', $doc->entities['Employee']->fields[0]->type);
		self::assertTrue($doc->entities['Employee']->fields[0]->required);

		self::assertArrayHasKey('Language', $doc->enums);
		self::assertSame(['FRENCH', 'ENGLISH', 'SPANISH'], $doc->enums['Language']->values);

		self::assertCount(3, $doc->relations);

		$r0 = $doc->relations[0];
		self::assertSame('OneToMany', $r0->kind);
		self::assertSame('Employee', $r0->fromEntity);
		self::assertSame('jobs', $r0->fromField);
		self::assertSame('Job', $r0->toEntity);
		self::assertSame('employee', $r0->toField);

		$r1 = $doc->relations[1];
		self::assertSame('ManyToMany', $r1->kind);
		self::assertSame('Job', $r1->fromEntity);
		self::assertSame('tasks', $r1->fromField);
		self::assertSame('Task', $r1->toEntity);
		self::assertSame('jobs', $r1->toField);

		$r2 = $doc->relations[2];
		self::assertSame('OneToOne', $r2->kind);
		self::assertSame('Job', $r2->fromEntity);
		self::assertSame('managerTask', $r2->fromField);
		self::assertSame('Task', $r2->toEntity);
		self::assertNull($r2->toField);
	}
}
