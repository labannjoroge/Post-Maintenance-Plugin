<?php
/**
 * Singleton class for all classes.
 *
 * @link    https://example.com/
 * @since   1.0.0
 *
 * @author  Your Name
 * @package PM_Plugin
 */

namespace LTG\PostsMaintenance;

// Abort if called directly.
defined( 'WPINC' ) || die;

/**
 * Class Singleton
 *
 * @package  LTG\PostsMaintenance
 */

abstract class Singleton {

	/**
	 * Protect the class from being initiated multiple times.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {}

	/**
	 * Instance obtaining method.
	 *
	 * @return static Called class instance.
	 * @since 1.0.0
	 */
	public static function instance() {
		static $instances = array();

		$called_class_name = get_called_class();

		if ( ! isset( $instances[ $called_class_name ] ) ) {
			$instances[ $called_class_name ] = new $called_class_name();
		}

		return $instances[ $called_class_name ];
	}
}

