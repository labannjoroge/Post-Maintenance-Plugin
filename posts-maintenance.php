<?php
/**
 * Plugin Name: Posts Maintenance
 * Description: Automate content maintenance tasks such as archiving or deleting outdated posts
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:           laban the great
 * Text Domain:       posts-maintenance
 *
 * @package           create-block
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}



// Support for site-level autoloading.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}


/// Define plugin version.
if (!defined('LTG_PostsMaintenance_VERSION')) {
	define('LTG_PostsMaintenance_VERSION', '1.0.0');
}

// Define plugin file path.
if (!defined('LTG_PostsMaintenance_PLUGIN_FILE')) {
	define('LTG_PostsMaintenance_PLUGIN_FILE', __FILE__);
}

// Define plugin directory path.
if (!defined('LTG_PostsMaintenance_DIR')) {
	define('LTG_PostsMaintenance_DIR', plugin_dir_path(__FILE__));
}

// Define plugin URL.
if (!defined('LTG_PostsMaintenance_URL')) {
	define('LTG_PostsMaintenance_URL', plugin_dir_url(__FILE__));
}

// Define assets URL.
if (!defined('LTG_PostsMaintenance_ASSETS_URL')) {
	define('LTG_PostsMaintenance_ASSETS_URL', LTG_PostsMaintenance_URL . 'assets'); // Removed trailing slash from '/assets/'
}

// Define shared UI version.
if (!defined('LTG_PostsMaintenance_SUI_VERSION')) {
	define('LTG_PostsMaintenance_SUI_VERSION', '2.12.23');
}

// Include the CLI commands file.
if (defined('WP_CLI') && WP_CLI) {
	require_once LTG_PostsMaintenance_DIR . 'cli-commands.php';
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __FILE__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );

/**
 * LTG_PostsMaintenance class.
 */
class LTG_PostsMaintenance
{

	public function __construct()
	{
		$this->init_cron();
	}

	/**
	 * Holds the class instance.
	 *
	 * @var LTG_PostsMaintenance $instance
	 */
	private static $instance = null;

	/**
	 * Return an instance of the class
	 *
	 * Return an instance of the LTG_PostsMaintenance Class.
	 *
	 * @return LTG_PostsMaintenance class instance.
	 * @since 1.0.0
	 *
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class initializer.
	 */
	public function load()
	{
		load_plugin_textdomain(
			'ltg-posts-maintenance',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);

		LTG\PostsMaintenance\Loader::instance();
	}

	public function init_cron()
	{
		// Schedule daily event
		if (!wp_next_scheduled('ltg_daily_scan_posts')) {
			wp_schedule_event(time(), 'daily', 'ltg_daily_scan_posts');
		}

		add_action('ltg_daily_scan_posts', array($this, 'scan_posts'));
	}

	/**
	 * Scans all public posts and updates the last scan timestamp.
	 *
	 * Retrieves all published posts and pages (or custom post types if specified)
	 * and updates the 'ltg_test_last_scan' post meta with the current time.
	 */
	public function scan_posts($post_types = array())
	{
		if (!is_array($post_types) && empty($post_types)) {
			$post_types = (array) get_option('ltg_scan_post_types', array());
		}

		// Set up the query arguments to retrieve all published posts and pages.
		$args = array(
			'post_type' => $post_types,
			'post_status' => 'publish',
			'numberposts' => -1,
		);

		// Get the posts based on the query arguments.
		$posts = get_posts($args);

		// Loop through each post and update its metadata.
		foreach ($posts as $post) {
			update_post_meta($post->ID, 'ltg_test_last_scan', current_time('mysql'));
		}
	}

	
}

// Init the plugin and load the plugin instance for the first time.
add_action(
	'plugins_loaded',
	function () {
		LTG_PostsMaintenance::get_instance()->load();
	}
);
