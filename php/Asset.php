<?php
/**
 * Class Asset
 *
 * @package MachineLearning
 */

namespace MachineLearning;

/**
 * Class Asset
 *
 * @package MachineLearning
 */
class Asset {

	/**
	 * The slug of the block JS file.
	 *
	 * @var string
	 */
	const BLOCK_JS_SLUG = 'block';

	/**
	 * The slug of the block JS file.
	 *
	 * @var string
	 */
	const FRONT_END_SCRIPT_SLUG = 'front-end';

	/**
	 * The slug of the block CSS file.
	 *
	 * @var string
	 */
	const STYLE_SLUG = 'style';

	/**
	 * The plugin.
	 *
	 * @var Plugin
	 */
	public $plugin;

	/**
	 * Asset constructor.
	 *
	 * @param Plugin $plugin The instance of the plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Inits the class.
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_scripts' ] );
	}

	/**
	 * Registers the scripts for the block.
	 *
	 * Not simply enqueued here, as one of the scripts also is enqueued
	 * in the 'render_callback' of the block.
	 */
	public function enqueue_block_editor_scripts() {
		$this->enqueue_script( self::BLOCK_JS_SLUG );
	}

	/**
	 * Enqueues a script by its slug.
	 *
	 * @param string $slug The slug of the script.
	 */
	public function enqueue_script( $slug ) {
		$config = $this->get_script_config( $slug );
		\wp_enqueue_script(
			$this->get_prefixed_slug( $slug ),
			$this->plugin->get_script_path( $slug ),
			$config['dependencies'],
			$config['version'],
			true
		);
	}

	/**
	 * Enqueues a stylesheet by its slug.
	 *
	 * @param string $slug The slug of the stylesheet.
	 */
	public function enqueue_style( $slug ) {
		\wp_enqueue_style(
			$this->get_prefixed_slug( $slug ),
			$this->plugin->get_style_path( $slug ),
			[],
			Plugin::VERSION
		);
	}

	/**
	 * Gets the slug of the asset, prefixed with the plugin slug.
	 * For example, 'machine-learning-block'.
	 *
	 * @param string $asset_slug The slug of the asset.
	 * @return string $full_slug The slug of the asset, prepended with the plugin slug.
	 */
	public function get_prefixed_slug( $asset_slug ) {
		return Plugin::SLUG . '-' . $asset_slug;
	}

	/**
	 * Gets the config of the script, including dependencies.
	 *
	 * @param string $slug The slug of the script.
	 * @return array The config of the script.
	 */
	private function get_script_config( $slug ) {
		$plugin_path = $this->plugin->get_dir();
		return require "{$plugin_path}/js/dist/{$slug}.asset.php";
	}
}
