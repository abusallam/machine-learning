<?php
/**
 * Tests for class Plugin.
 *
 * @package EasySurvey
 */

namespace EasySurvey;

use WP_Mock;

/**
 * Tests for class Plugin.
 */
class TestPlugin extends TestCase {

	/**
	 * Instance of plugin.
	 *
	 * @var Plugin
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() : void {
		parent::setUp();
		$this->instance = new Plugin( dirname( dirname( __FILE__ ) ) );
	}

	/**
	 * Test init().
	 *
	 * @covers \EasySurvey\Plugin::init()
	 */
	public function test_init() {
		WP_Mock::expectActionAdded( 'init', [ $this->instance, 'plugin_localization' ] );

		$this->instance->init();
	}

	/**
	 * Test init_classes.
	 *
	 * @covers \EasySurvey\Plugin::init_classes()
	 */
	public function test_init_classes() {
		$this->instance->init_classes();
		foreach ( [ 'Asset', 'Block' ] as $class ) {
			$this->assertEquals( __NAMESPACE__ . '\\' . $class, get_class( $this->instance->components->$class ) );
		}
	}

	/**
	 * Test plugin_localization().
	 *
	 * @covers \AugumentedReality\Plugin::plugin_localization()
	 */
	public function test_plugin_localization() {
		WP_Mock::userFunction( 'load_plugin_textdomain' )
			->once()
			->withSomeOfArgs( 'easy-survey' );

		$this->instance->plugin_localization();
	}

	/**
	 * Test get_path.
	 *
	 * @covers \EasySurvey\Plugin::get_path()
	 */
	public function test_get_path() {
		$this->assertIsString( $this->instance->get_path() );
	}

	/**
	 * Test get_dir.
	 *
	 * @covers \EasySurvey\Plugin::get_dir()
	 */
	public function test_get_dir() {
		$this->assertIsString( $this->instance->get_dir() );
	}


	/**
	 * Test get_script_path.
	 *
	 * @covers \EasySurvey\Plugin::get_script_path()
	 */
	public function test_get_script_path() {
		WP_Mock::userFunction( 'plugins_url' )
			->once()
			->andReturnArg( 0 );
		$slug = 'example';

		$this->assertStringContainsString( $slug, $this->instance->get_script_path( $slug ) );
	}
}
