<?php
/**
 * Kit migrator tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\KitMigrator;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Migrator contract.
 */
final class KitMigratorTest extends TestCase {

	/**
	 * Stub WP functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'update_option' )->alias( static fn ( string $o, $v ): bool => true );
		Functions\when( 'get_option' )->alias( static fn ( string $o, $d = false ) => $d );
	}

	/**
	 * Build a migrator with a store that reports no existing kits.
	 *
	 * @return KitMigrator
	 */
	private function migrator_with_empty_store(): KitMigrator {
		$store = $this->createMock( KitStore::class );
		$store->method( 'has' )->willReturn( false );
		$store->method( 'save' )->willReturn( 1 );
		return new KitMigrator( $store, '/tmp/kits/', static fn () => array() );
	}

	/**
	 * migrate() seeds a kit from a parsed CSS file.
	 *
	 * @return void
	 */
	public function test_migrate_seeds_kit_from_css(): void {
		$saved = array();
		$store = $this->createMock( KitStore::class );
		$store->method( 'has' )->willReturn( false );
		$store->method( 'save' )->willReturnCallback(
			static function ( $kit ) use ( &$saved ): int {
				$saved[] = $kit->slug();
				return 1;
			}
		);

		$migrator = new KitMigrator(
			$store,
			'/tmp/kits/',
			static fn ( string $p ): array => array( '/tmp/kits/gslg.css' ),
			static fn ( string $p ): string => ":root {\n	--brand: #1c628f;\n	--accent: #c9a24b;\n	--display: Georgia, serif;\n	--sans: Inter, sans-serif;\n	--section-space-y: clamp(64px, 9vw, 96px);\n}"
		);

		$count = $migrator->migrate();

		$this->assertSame( 1, $count );
		$this->assertSame( array( 'gslg' ), $saved );
	}

	/**
	 * migrate() skips kits that already exist.
	 *
	 * @return void
	 */
	public function test_migrate_skips_existing(): void {
		$saved = array();
		$store = $this->createMock( KitStore::class );
		$store->method( 'has' )->willReturnCallback(
			static fn ( string $slug ): bool => 'gslg' === $slug
		);
		$store->method( 'save' )->willReturnCallback(
			static function ( $kit ) use ( &$saved ): int {
				$saved[] = $kit->slug();
				return 1;
			}
		);

		$migrator = new KitMigrator(
			$store,
			'/tmp/kits/',
			static fn (): array => array( '/tmp/kits/gslg.css', '/tmp/kits/rore.css' ),
			static fn ( string $p ): string => ":root {\n	--brand: #1c628f;\n}"
		);

		$count = $migrator->migrate();

		$this->assertSame( 1, $count );
		$this->assertSame( array( 'rore' ), $saved );
	}

	/**
	 * maybe_migrate runs once, then no-ops after the option is set.
	 *
	 * @return void
	 */
	public function test_maybe_migrate_runs_once(): void {
		$option = false;
		Functions\when( 'get_option' )->alias(
			static function ( string $o, $d = false ) use ( &$option ) {
				return $option;
			}
		);
		Functions\when( 'update_option' )->alias(
			static function ( string $o, $v ) use ( &$option ): bool {
				$option = $v;
				return true;
			}
		);

		$migrator = $this->migrator_with_empty_store();
		$migrator->maybe_migrate();
		$this->assertSame( 1, $option );

		// Second call should no-op (option now true).
		$migrator->maybe_migrate();
		$this->assertSame( 1, $option );
	}
}
