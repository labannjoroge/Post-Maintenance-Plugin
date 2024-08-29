<?php
/**
 * Base class for all endpoint classes.
 *
 * @link  https://example.com/
 * @since 1.0.0
 *
 * @author  Your Name
 * @package LTG\PostsMaintenance
 */

namespace LTG\PostsMaintenance;


use WP_REST_Controller;
use WP_REST_Response;

// Abort if called directly.
defined('WPINC') || die;

class Endpoint extends WP_REST_Controller
{
    /**
     * API endpoint version.
     *
     * @since 1.0.0
     * @var   int
     */
    protected $version = 1;

    /**
     * API endpoint namespace.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $namespace;

    /**
     * API endpoint for the current endpoint.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $endpoint = '';

    /**
     * Endpoint constructor.
     *
     * Register the routes.
     *
     * @since 1.0.0
     */
    protected function __construct()
    {
        $this->namespace = 'ltg/v' . $this->version;
    
        $this->register_hooks();
    }

    /**
     * Instance obtaining method.
     *
     * @return static Called class instance.
     * @since  1.0.0
     */
    public static function instance()
    {
        static $instances = array();

        $called_class_name = get_called_class();

        if (!isset($instances[$called_class_name])) {
            $instances[$called_class_name] = new $called_class_name();
        }

        return $instances[$called_class_name];
    }

    /**
     * Set up WordPress hooks and filters.
     *
     * @return void
     * @since  1.0.0
     */
    public function register_hooks()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Check if a given request has access to manage projects.
     *
     * @param  \WP_REST_Request $request Request object.
     * @return bool
     * @since  1.0.0
     */
    public function edit_permission($request)
    {
        $capable = current_user_can('manage_options');

        return apply_filters('pm_plugin_rest_permission', $capable, $request);
    }

    /**
     * Get formatted response for the current request.
     *
     * @param  array $data    Response data.
     * @param  bool  $success Is request success.
     * @return WP_REST_Response
     * @since  1.0.0
     */
    public function get_response($data = array(), $success = true)
    {
        $status = $success ? 200 : 400;

        return new WP_REST_Response(
            array(
            'success' => $success,
            'data' => $data,
            ),
            $status
        );
    }

    /**
     * Get the Endpoint's namespace.
     *
     * @return string
     */
    public function get_namespace()
    {
        return $this->namespace;
    }

    /**
     * Get the Endpoint's endpoint part.
     *
     * @return string
     */
    public function get_endpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get the full Endpoint URL.
     *
     * @return string
     */
    public function get_endpoint_url()
    {
        return trailingslashit(rest_url()) . trailingslashit($this->get_namespace()) . $this->get_endpoint();
    }

    /**
     * Register the routes for the objects of the controller.
     *
     * This should be defined in extending class.
     *
     * @since 1.0.0
     */
    public function register_routes()
    {
    }
}
