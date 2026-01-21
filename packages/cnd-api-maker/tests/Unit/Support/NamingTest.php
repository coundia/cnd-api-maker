<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Support;

use CndApiMaker\Core\Generator\Support\Naming;
use PHPUnit\Framework\TestCase;

final class NamingTest extends TestCase
{
	public function testItShouldCamelizeWords(): void
	{
		$n = new Naming();

		self::assertSame('employee', $n->camel('Employee'));
		self::assertSame('employee', $n->camel('employee'));
		self::assertSame('employeeId', $n->camel('employee_id'));
		self::assertSame('employeeId', $n->camel('employee-id'));
		self::assertSame('employeeId', $n->camel('Employee Id'));
		self::assertSame('', $n->camel(''));
		self::assertSame('', $n->camel('   '));
	}
}
