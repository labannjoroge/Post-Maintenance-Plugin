<?php
namespace LTG\PostsMaintenance\Endpoints\V1;

use LTG\PostsMaintenance\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;
use WP_Error;
use Exception;

/**
 * Class Maintenance
 *
 * Handles the maintenance-related functionality for the plugin.
 */
class Maintenance extends Endpoint
{
	private static $instance = null;

	/**
	 * Retrieves the singleton instance of the class.
	 *
	 * @return self The single instance of the class.
	 */
	public static function get_instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * API endpoint for the maintenance scan.
	 *
	 * @var string
	 */
	protected $endpoint = 'maintenance/scan-posts';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->register_archived_status();
	}

	/**
	 * Register the custom post status 'archived'.
	 */
	public function register_archived_status()
	{
		register_post_status('archived', array(
			'label' => _x('Archived', 'post'),
			'public' => true,
			'internal' => false,
			'protected' => true,
			'private' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>'),
		));
	}

	/**
	 * Register the routes for maintenance functionality.
	 */
	public function register_routes()
	{
		register_rest_route(
			$this->get_namespace(),
			$this->get_endpoint(),
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'handle_scan_request'),
				'permission_callback' => array($this, 'permission_check'),
				'args' => array(
					'post_types' => array(
						'required' => true,
						'validate_callback' => array($this, 'validate_post_types'),
					),
					'categories' => array(
						'validate_callback' => array($this, 'validate_categories'),
					),
					'age_threshold' => array(
						'required' => true,
						'validate_callback' => array($this, 'validate_threshold'),
					),
					'engagement_threshold' => array(
						'required' => true,
						'validate_callback' => array($this, 'validate_threshold'),
					),
				),
			)
		);
	}

	/**
	 * Permission callback for the endpoint.
	 *
	 * @return bool True if the user has the required capability, false otherwise.
	 */
	public function permission_check()
	{
		return is_user_logged_in() && current_user_can('edit_posts');
	}

	/**
	 * Manually triggers a scan for a specific post.
	 *
	 * @param int $post_id The ID of the post to scan.
	 * @return bool True if the scan was successful, false otherwise.
	 */
	public function manual_scan_trigger($post_id)
	{
		$post = get_post($post_id);

		if (!$post || $post->post_type !== 'your_post_type') {
			return false;
		}

		// Perform the scan logic
		update_post_meta($post_id, 'ltg_test_last_scan', current_time('mysql'));

		// Trigger custom action hook
		do_action('ltg_post_scanned', $post_id);

		return true;
	}

	/**
	 * Handle the incoming request to scan posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response containing the result of the scan.
	 */
	public function handle_scan_request(WP_REST_Request $request): WP_REST_Response
	{
		// Extract and validate parameters
		$post_types = $this->validate_post_types($request->get_param('post_types'));
		$categories = $this->validate_categories($request->get_param('categories'));
		$age_threshold = $this->validate_threshold($request->get_param('age_threshold'), 'age_threshold');
		$engagement_threshold = $this->validate_threshold($request->get_param('engagement_threshold'), 'engagement_threshold');

		if (is_wp_error($post_types) || is_wp_error($categories) || is_wp_error($age_threshold) || is_wp_error($engagement_threshold)) {
			return rest_ensure_response($this->generate_error_response([$post_types, $categories, $age_threshold, $engagement_threshold]));
		}

		// Build the query arguments
		$args = $this->build_query_args($post_types, $categories, $age_threshold, $engagement_threshold);

		try {
			// Execute the query
			$query_results = $this->perform_query($args);

			// Ensure $query_results is of type WP_Query
			if (!$query_results instanceof WP_Query) {
				return rest_ensure_response(new WP_Error('invalid_query', __('Invalid query results.', 'ltg-posts-maintenance'), array('status' => 500)));
			}

			// Process the query results
			$response_data = $this->process_query_results($query_results);

			// Add a success message to the response
			$response_data['message'] = __('Posts scanned successfully.', 'ltg-posts-maintenance');
			$response_data['status'] = 'success';

			
			// Check if any posts were archived or deleted and set the transient
			if (!empty($response_data['posts_archived']) || !empty($response_data['posts_deleted'])) {
				$this->set_maintenance_notification_transient($response_data);
				$this->send_email_notification($response_data);
			}

			return rest_ensure_response($response_data);

		} catch (Exception $e) {
			// Log error and return error response
			error_log($e->getMessage());
			return rest_ensure_response(new WP_Error('exception', __('An unexpected error occurred.', 'ltg-posts-maintenance'), array('status' => 500)));
		}

	}



	/**
	 * Validate post types.
	 *
	 * @param mixed $post_types Post types parameter from the request.
	 * @return array|WP_Error Valid post types or WP_Error.
	 */
	public function validate_post_types($post_types)
	{
		$post_types = (array) wp_unslash($post_types);

		if (empty($post_types)) {
			return new WP_Error('missing_data', __('Select a post type.', 'ltg-posts-maintenance'), array('status' => 400));
		}

		foreach ($post_types as $post_type) {
			if (!post_type_exists($post_type)) {
				return new WP_Error('invalid_data', __('Invalid post type.', 'ltg-posts-maintenance'), array('status' => 400));
			}
		}

		return $post_types;
	}

	/**
	 * Validate categories.
	 *
	 * @param mixed $categories Categories parameter from the request.
	 * @return array|WP_Error Valid categories or WP_Error.
	 */
	public function validate_categories($categories)
	{
		$categories = (array) wp_unslash($categories);

		if (!empty($categories)) {
			foreach ($categories as $category_id) {
				if (!get_term($category_id, 'category')) {
					return new WP_Error('invalid_data', __('Invalid category ID.', 'ltg-posts-maintenance'), array('status' => 400));
				}
			}
		}

		return $categories;
	}

	/**
	 * Validate threshold parameters.
	 *
	 * @param mixed  $threshold The threshold value.
	 * @param string $name      The name of the threshold (for error messaging).
	 * @return int|WP_Error Validated threshold value or WP_Error.
	 */
	public function validate_threshold($threshold, $name)
	{
		$threshold = intval(wp_unslash($threshold));

		if ($threshold <= 0) {
			return new WP_Error('invalid_data', sprintf(__('Invalid %s value.', 'ltg-posts-maintenance'), $name), array('status' => 400));
		}

		return $threshold;
	}

	/**
	 * Build query arguments.
	 *
	 * @param array $post_types          Validated post types.
	 * @param array $categories          Validated categories.
	 * @param int   $age_threshold       Validated age threshold.
	 * @param int   $engagement_threshold Validated engagement threshold.
	 * @return array Query arguments.
	 */
	private function build_query_args($post_types, $categories, $age_threshold, $engagement_threshold)
	{
		return [
			'post_type' => $post_types,
			'category__in' => $categories,
			'date_query' => [
				'column' => 'post_date',
				'before' => date('Y-m-d', strtotime("-$age_threshold days")),
			],
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => 'post_views_count',
					'value' => $engagement_threshold,
					'compare' => '<',
					'type' => 'NUMERIC',
				],
				[
					'key' => 'comment_count',
					'value' => $engagement_threshold,
					'compare' => '<',
					'type' => 'NUMERIC',
				],
			],
		];
	}

	/**
	 * Perform the query to retrieve posts.
	 *
	 * @param array $args Query arguments.
	 * @return WP_Query Query results.
	 */
	private function perform_query($args)
	{
		$query = new WP_Query($args);

		// Return an empty array if no posts are found
		// if (empty($query->posts)) {
		// 	return [];
		// }

		return $query;
	}


	/**
	 * Process the query results.
	 *
	 * @param WP_Query $query_results Query results.
	 * @return array Processed data.
	 */
	private function process_query_results(WP_Query $query_results): array
	{
		$posts_archived = [];
		$posts_deleted = [];

		foreach ($query_results->posts as $post) {
			$archive_post = $this->archive_post($post->ID);
			if ($archive_post) {
				$posts_archived[] = $post->ID;
			}

			$delete_post = $this->delete_post($post->ID);
			if ($delete_post) {
				$posts_deleted[] = $post->ID;
			}
		}

		return [
			'posts_archived' => $posts_archived,
			'posts_deleted' => $posts_deleted,
		];
	}

	/**
	 * Archive a post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if archived successfully, false otherwise.
	 */
	private function archive_post($post_id)
	{
		return wp_update_post([
			'ID' => $post_id,
			'post_status' => 'archived',
		]) !== false;
	}

	/**
	 * Delete a post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if deleted successfully, false otherwise.
	 */
	private function delete_post($post_id)
	{
		return wp_delete_post($post_id, true) !== false;
	}

	/**
	 * Set a transient notification for maintenance.
	 *
	 * @param array $data Data to include in the notification.
	 */
	private function set_maintenance_notification_transient($data)
	{
		set_transient('ltg_maintenance_notification', $data, 12 * HOUR_IN_SECONDS);
	}

	/**
	 * Send email notification about maintenance results.
	 *
	 * @param array $data Data to include in the email.
	 */
	private function send_email_notification($data)
	{
		$to = get_option('admin_email');
		$subject = __('Maintenance Report', 'ltg-posts-maintenance');
		$message = sprintf(
			__('Maintenance results: %d posts archived, %d posts deleted.', 'ltg-posts-maintenance'),
			count($data['posts_archived']),
			count($data['posts_deleted'])
		);
		wp_mail($to, $subject, $message);
	}

	/**
	 * Generate an error response.
	 *
	 * @param array $errors Array of WP_Error objects.
	 * @return WP_Error Generated WP_Error object.
	 */
	protected function generate_error_response(array $errors): WP_REST_Response
	{
		$error_data = [];

		foreach ($errors as $error) {
			if (is_wp_error($error)) {
				$error_data[] = $error->get_error_message();
			}
		}

		// Create a combined error message
		$combined_error_message = implode(' ', $error_data);

		// Create and return a WP_REST_Response with the error message
		return new WP_REST_Response(
			[
				'code' => 'validation_error',
				'message' => $combined_error_message,
				'data' => [],
			],
			400 // Set the status code here
		);
	}

}

add_action('rest_api_init', function () {
	\LTG\PostsMaintenance\Endpoints\V1\Maintenance::get_instance()->register_routes();
});
