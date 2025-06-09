<?php

namespace Tests;

use Kirby\Cms\App as Kirby;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
	}

	protected function tearDown(): void
	{
		// Properly clean up Kirby instance using the public instance method
		if (Kirby::instance(null, true) !== null) {
			Kirby::destroy();
		}

		parent::tearDown();
		restore_error_handler();
		restore_exception_handler();

	}
}
