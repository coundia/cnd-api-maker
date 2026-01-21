<?php

declare(strict_types=1);

namespace CndApiMaker\Tests\Unit\Core\Definition;

use CndApiMaker\Core\Definition\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
	public function testItShouldApplyScalarDefaultsWhenMissing(): void
	{
		$def = (object) [];

		Config::applyGlobalDefaults($def, [
			'api' => [
				'uriPrefix' => '/api/v1',
				'uuid' => true,
			],
		]);

		self::assertTrue(isset($def->api));
		self::assertSame('/api/v1', $def->api->uriPrefix);
		self::assertTrue($def->api->uuid);
	}

	public function testItShouldNotOverrideExistingValues(): void
	{
		$def = (object) [
			'api' => (object) [
				'uriPrefix' => '/custom',
				'uuid' => false,
			],
		];

		Config::applyGlobalDefaults($def, [
			'api' => [
				'uriPrefix' => '/api/v1',
				'uuid' => true,
			],
		]);

		self::assertSame('/custom', $def->api->uriPrefix);
		self::assertFalse($def->api->uuid);
	}

	public function testItShouldDeepMergeAssocArraysIntoObjects(): void
	{
		$def = (object) [
			'security' => (object) [
				'prefix' => 'APP_',
			],
		];

		Config::applyGlobalDefaults($def, [
			'security' => [
				'enabled' => true,
				'prefix' => 'IGNORED_',
				'defaultPermissions' => ['LIST', 'VIEW'],
				'levels' => [
					'admin' => [
						'grants' => ['*:*'],
					],
				],
			],
		]);

		self::assertTrue($def->security->enabled);
		self::assertSame('APP_', $def->security->prefix);
		self::assertSame(['LIST', 'VIEW'], $def->security->defaultPermissions);

		self::assertTrue(isset($def->security->levels));
		self::assertTrue(isset($def->security->levels->admin));
		self::assertSame(['*:*'], $def->security->levels->admin->grants);
	}

	public function testItShouldNotOverrideExistingNestedValuesDuringDeepMerge(): void
	{
		$def = (object) [
			'security' => (object) [
				'levels' => (object) [
					'admin' => (object) [
						'grants' => ['EMPLOYEE:*'],
					],
				],
			],
		];

		Config::applyGlobalDefaults($def, [
			'security' => [
				'levels' => [
					'admin' => [
						'grants' => ['*:*'],
						'extra' => true,
					],
					'user' => [
						'grants' => ['EMPLOYEE:LIST'],
					],
				],
			],
		]);

		self::assertSame(['EMPLOYEE:*'], $def->security->levels->admin->grants);
		self::assertTrue($def->security->levels->admin->extra);
		self::assertSame(['EMPLOYEE:LIST'], $def->security->levels->user->grants);
	}

	public function testItShouldSetNumericArraysWhenMissing(): void
	{
		$def = (object) [
			'security' => (object) [],
		];

		Config::applyGlobalDefaults($def, [
			'security' => [
				'defaultPermissions' => ['LIST', 'VIEW', 'CREATE'],
			],
		]);

		self::assertSame(['LIST', 'VIEW', 'CREATE'], $def->security->defaultPermissions);
	}

	public function testItShouldNotOverrideNumericArraysWhenPresent(): void
	{
		$def = (object) [
			'security' => (object) [
				'defaultPermissions' => ['LIST'],
			],
		];

		Config::applyGlobalDefaults($def, [
			'security' => [
				'defaultPermissions' => ['LIST', 'VIEW', 'CREATE'],
			],
		]);

		self::assertSame(['LIST'], $def->security->defaultPermissions);
	}

	public function testItShouldIgnoreNonObjectExistingRoot(): void
	{
		$def = (object) [
			'api' => 'not-an-object',
		];

		Config::applyGlobalDefaults($def, [
			'api' => [
				'uriPrefix' => '/api/v1',
			],
		]);

		self::assertSame('not-an-object', $def->api);
	}
}
