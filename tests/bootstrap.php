<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Plugin_Test
 */

// Determine the test suite directory.
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Log the test directory for debugging purposes.
error_log("WP_TESTS_DIR: $_tests_dir");

// Forward custom PHPUnit Polyfills configuration to the PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if ( false !== $_phpunit_polyfills_path ) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

// Check if the WordPress test functions are available.
if ( ! file_exists($_tests_dir . '/includes/functions.php') ) {
    error_log("Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?");
    exit(1);
}

// Include the necessary functions for WordPress tests.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname(dirname(__FILE__)) . '/posts-maintenance.php';
}

// Hook into 'muplugins_loaded' to load the plugin.
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Load the WordPress test environment.
require $_tests_dir . '/includes/bootstrap.php';

// Log successful loading of the bootstrap file.
error_log("Bootstrap loaded successfully.");
