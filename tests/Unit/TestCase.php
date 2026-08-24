<?php
/**
 * Base test case.
 *
 * Sets up brain/monkey for WordPress function stubbing and tears it down
 * after each test.
 *
 * @package Vector\ElementorWidgets\Tests\Unit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case with Brain Monkey wiring.
 */
abstract class TestCase extends PHPUnitTestCase {

	/**
	 * Set up Brain Monkey.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain Monkey.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
