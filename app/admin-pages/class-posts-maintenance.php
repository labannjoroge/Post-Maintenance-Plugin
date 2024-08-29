<?php
/**
 * Posts Maintenance Admin Page.
 *
 * @link         https://labanthegreat.dev/
 * @since         1.0.0
 *
 * @package       LTG\PostsMaintenance
 */

namespace LTG\PostsMaintenance\App\Admin_Pages;

defined( 'WPINC' ) || die;

use LTG\PostsMaintenance\Base;

class Posts_Maintenance extends Base {
	private static $instance = null;

	/**
	 * Retrieves the singleton instance of the class.
	 *
	 * This method ensures that only one instance of the class is created (singleton pattern).
	 * If an instance already exists, it returns that instance.
	 * If no instance exists, it creates a new one and returns it.
	 *
	 * @return self The single instance of the class.
	 */
	public static function get_instance()
	{
		// Check if the instance is null, meaning it hasn't been created yet.
		if (self::$instance === null) {
			// If null, create a new instance of the class and assign it to the static $instance variable.
			self::$instance = new self();
		}
		// Return the single instance of the class.
		return self::$instance;
	}

	/**
	 * The page title.
	 *
	 * @var string
	 */
	private $page_title;

	/**
	 * The page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'ltg_posts_maintenance';

	/**
	 * Assets version.
	 *
	 * @var string
	 */
	private $assets_version = '';

	/**
	 * A unique string id to be used in markup and jsx.
	 *
	 * @var string
	 */
	private $unique_id = '';

	/**
	 * Initializes the admin page and schedules the daily scan.
	 *
	 * @return void
	 * @since 1.0.0
	 */

	/**
	 * List of page scripts.
	 *
	 * @var array
	 */
	private $page_scripts = array();

	/**
	 * Initializes the admin page and schedules the daily scan.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		$this->page_title     = __( 'Posts Maintenance', 'ltg-posts-maintenance' );
		$this->assets_version = LTG_PostsMaintenance_VERSION;
		$this->unique_id      = "ltg_posts_maintenance_main_wrap-{$this->assets_version}";

		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Registers the admin menu page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_admin_page() {
		$page = add_menu_page(
			__( 'Posts Maintenance', 'ltg-posts-maintenance' ),
			$this->page_title,
			'manage_options',
			$this->page_slug,
			array( $this, 'render_admin_page' )
		);

		add_action( 'load-' . $page, array( $this, 'enqueue_assets' ) );
	}

	/**
     * Manually triggers a scan for a specific post.
     *
     * @param int $post_id The ID of the post to scan.
     * @return bool True if the scan was successful, false otherwise.
     */
    public function manual_scan_trigger($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'post') {
            return false;
        }

        // Perform the scan logic (e.g., updating post meta, sending emails, etc.)
        update_post_meta($post_id, 'ltg_test_last_scan', current_time('mysql'));

        // Return true to indicate the scan was successful
        return true;
    }

	/**
	 * Enqueues necessary assets for the admin page.
	 *
	 * Enqueues the JavaScript file for the Posts Maintenance page, which depends on React, wp-element, and jQuery.
	 * Also localizes the script with the AJAX URL and a nonce for security.
	 */
	public function enqueue_assets() {
		// Initialize the page_scripts array if not already an array
		if ( ! is_array( $this->page_scripts ) ) {
			$this->page_scripts = array();
		}

		// Define the handle for your script and style
		$handle = 'ltg_posts_maintenance';

		// Define the URL for your JavaScript and CSS files
		$script_src = LTG_PostsMaintenance_ASSETS_URL . '/js/maintenance.min.js';
		$style_src  = LTG_PostsMaintenance_ASSETS_URL . '/css/maintenance.min.css';

		// Define dependencies for your script
		$dependencies = array(
			'react',
			'wp-components',
			'wp-element',
			'jquery',
			'wp-i18n',
			'wp-is-shallow-equal',
			'wp-polyfill',
		);

		// Enqueue the React component
		wp_enqueue_script(
			$handle . '-vendor',
			LTG_PostsMaintenance_ASSETS_URL . '/js/vendors.min.js',
			array(),
			null, // Use null to ensure it gets the latest version
			true // Load script in footer
		);

		// Enqueue the script
		wp_enqueue_script(
			$handle,
			$script_src,
			$dependencies,
			$this->assets_version,
			true // Load in the footer
		);

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-element' );

		// Enqueue the style
		wp_enqueue_style(
			$handle . '-style',
			$style_src,
			array(), // No dependencies for the style
			$this->assets_version
		);

		// Get all public post types
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		$post_types = array_map(
			function ( $post_type ) {
				return array(
					'value' => $post_type->name,
					'label' => $post_type->label,
				);
			},
			$post_types
		);

		$post_types = array_values( $post_types );

		// Generate the nonce
		$nonce = wp_create_nonce( 'wp_rest' );

		// Localize the script to pass data to your React component
		wp_localize_script(
			$handle,
			'LTG_Maintenance',
			array(
				'restEndpoint'   => esc_url_raw( rest_url( 'ltg/v1/maintenance/scan-posts' ) ),
				'nonce'          => $nonce,
				'postTypes'      => $post_types,
				'unique_id' => $this->unique_id,
				'additionalData' => array(
				),
			)
		);
	}


	/**
	 * Renders the admin page.
	 *
	 * Outputs a container for the React-based Posts Maintenance app.
	 */
	public function render_admin_page() {
		?>
		<div id="<?php echo esc_attr( $this->unique_id ); ?>" class="ltg-posts-maintenance-wrap">
			<div id="ltg-posts-maintenance-root"></div>
		</div>
		<?php
	}

	/**
	 * Process the scan on form submission.
	 */
	public function process_scan() {
		// The scan will be triggered via AJAX using the REST API endpoint.
	}

	/**
	 * Schedule daily post maintenance scan.
	 */
	public function schedule_scan() {
		if ( ! wp_next_scheduled( 'ltg_daily_post_maintenance_scan' ) ) {
			wp_schedule_event( time(), 'daily', 'ltg_daily_post_maintenance_scan' );
		}
	}

	/**
	 * Run the maintenance scan.
	 */
	public function run_scan() {
		// Logic for running the scan daily via cron.
		$response = wp_remote_post(
			rest_url( 'ltg/v1/maintenance/scan-posts' ),
			array(
				'body'    => array(
					'post_types'           => array( 'post' ),
					'categories'           => array(),
					'age_threshold'        => 365,
					'engagement_threshold' => 10,
				),
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . wp_create_nonce( 'wp_rest' ),
				),
			)
		);

		$scan_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_wp_error( $response ) || empty( $scan_data['success'] ) ) {
			// Handle scan failure.
		} else {
			// Log success and other details.
		}
	}
}



