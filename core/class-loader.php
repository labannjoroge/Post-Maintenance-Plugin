<?php
/**
 * Class to boot up the PM plugin.
 *
 * @link    https://example.com/
 * @since   1.0.0
 *
 * @author  Your Name
 * @package PM_Plugin
 */

namespace LTG\PostsMaintenance;

use LTG\PostsMaintenance\Base;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

final class Loader extends Base {
	/**
	 * Minimum supported PHP version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $php_version = '7.4';

	/**
	 * Minimum WordPress version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $wp_version = '6.1';

	/**
	 * Initialize functionality of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	protected function __construct() {
		if ( ! $this->can_boot() ) {
			return;
		}

		$this->init();
	}

	/**
	 * Check if the plugin can be booted.
	 *
	 * @return bool
	 */
	private function can_boot() {
		global $wp_version;

		return (
			version_compare( PHP_VERSION, $this->php_version, '>=' ) &&
			version_compare( $wp_version, $this->wp_version, '>=' )
		);
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function init() {
		App\Admin_Pages\Posts_Maintenance::instance()->init();
		Endpoints\V1\Maintenance::instance();
	}
}
