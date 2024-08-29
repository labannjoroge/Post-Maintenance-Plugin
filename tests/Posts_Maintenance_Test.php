<?php
namespace LTG\PostsMaintenance\Tests;

use WP_REST_Request;
use WP_REST_Response;
use WP_UnitTestCase;
use LTG\PostsMaintenance\Endpoints\V1\Maintenance;
use WP_Query;
use WP_Error;

/**
 * Class Posts_Maintenance_Test
 *
 * Tests the handle_scan_request method in the Maintenance class.
 */
class Posts_Maintenance_Test extends WP_UnitTestCase
{
    private $maintenance;
    private $request;

    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Initialize the Maintenance class
        $this->maintenance = Maintenance::get_instance();

        // Create a WP_REST_Request object
        $this->request = new WP_REST_Request('POST', '/wp-json/ltg/maintenance/scan-posts');

        // Set up request parameters
        $this->request->set_param('post_types', ['post']);
        $this->request->set_param('categories', []);
        $this->request->set_param('age_threshold', 30);
        $this->request->set_param('engagement_threshold', 10);
    }

    /**
     * Test handle_scan_request with valid parameters.
     */
    public function test_handle_scan_request_success()
    {
        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();
        $this->assertArrayHasKey('posts_archived', $data);
        $this->assertArrayHasKey('posts_deleted', $data);
    }

    /**
     * Test handle_scan_request with invalid post types.
     */
    public function test_handle_scan_request_invalid_post_types()
    {
        // Set an invalid post type
        $this->request->set_param('post_types', ['invalid_post_type']);

        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertTrue($response->is_error());

        // Extract and decode the error data
        $errors = $response->get_data();
        $this->assertArrayHasKey('message', $errors);

        // Check if the error message is in the response
        $error_message = $errors['message'];
        $this->assertStringContainsString('Invalid post type.', $error_message);
    }

    /**
     * Test handle_scan_request with invalid categories.
     */
    public function test_handle_scan_request_invalid_categories()
    {
        // Set an invalid category
        $this->request->set_param('categories', [9999]); // Assuming 9999 is an invalid category ID

        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertTrue($response->is_error());

        // Extract and decode the error data
        $errors = $response->get_data();
        $this->assertArrayHasKey('message', $errors);

        // Check if the error message is in the response
        $error_message = $errors['message'];
        $this->assertStringContainsString('Invalid category ID.', $error_message);
    }


    /**
     * Test handle_scan_request with invalid thresholds.
     */
    public function test_handle_scan_request_invalid_thresholds()
    {
        // Set invalid thresholds
        $this->request->set_param('age_threshold', -10); // Invalid threshold
        $this->request->set_param('engagement_threshold', 'invalid'); // Invalid threshold

        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertTrue($response->is_error());

        // Extract and decode the error data
        $errors = $response->get_data();
        $this->assertArrayHasKey('message', $errors);

        // Check if the error messages are in the response
        $error_messages = $errors['message'];
        $this->assertStringContainsString('Invalid age_threshold value.', $error_messages);
        $this->assertStringContainsString('Invalid engagement_threshold value.', $error_messages);
    }


    /**
     * Test handle_scan_request with missing parameters.
     */
    public function test_handle_scan_request_missing_parameters()
    {
        // Remove required parameters
        $this->request->set_param('post_types', null);

        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertTrue($response->is_error());

        // Extract and decode the error data
        $errors = $response->get_data();
        $this->assertArrayHasKey('message', $errors);

        // Check if the error message is in the response
        $error_message = $errors['message'];
        $this->assertStringContainsString('Select a post type.', $error_message);
    }


    /**
     * Test handle_scan_request with an empty result set.
     */
    public function test_handle_scan_request_no_results()
    {
        // Set parameters that would result in no posts being found
        $this->request->set_param('post_types', ['post']);
        $this->request->set_param('categories', [0]); // Assuming 0 is an invalid category ID
        $this->request->set_param('age_threshold', 1000); // Set a high threshold to exclude posts

        // Handle the request
        $response = $this->maintenance->handle_scan_request($this->request);

        // Verify the response
        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();
        $this->assertIsArray($data['posts_archived']);
        $this->assertIsArray($data['posts_deleted']);
        $this->assertCount(0, $data['posts_archived']);
        $this->assertCount(0, $data['posts_deleted']);
    }
}
