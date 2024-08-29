<?php
/**
 * CLI Commands for the WP Posts Maintenance Plugin.
 *
 * This file contains the CLI commands used in the WP Posts Maintenance plugin,
 * allowing interaction with the WordPress command line interface.
 *
 * @link          https://example.com/
 * @since         1.0.0
 * @package       WPPostsMaintenance
 *
 */

// Abort if this file is called directly to ensure security.
defined( 'WPINC' ) || die;

/**
 * Check if WP-CLI is defined and enabled.
 *
 * Only proceed if WP-CLI is active.
 *
 * @since 1.0.0
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Scans public posts and pages, updating the last scan timestamp.
	 *
	 * This command allows for scanning all published posts and pages or
	 * selecting specific post types, date ranges, or post IDs.
	 *
	 * ## OPTIONS
	 *
	 * [--post_type=<type>]
	 * : The post type to scan. Defaults to 'post'. Accepts a comma-separated list of types.
	 *
	 * [--start_date=<date>]
	 * : Only scan posts published after this date. Format: YYYY-MM-DD.
	 *
	 * [--end_date=<date>]
	 * : Only scan posts published before this date. Format: YYYY-MM-DD.
	 *
	 * [--post_ids=<ids>]
	 * : Comma-separated list of post IDs to scan.
	 *
	 * [--meta_key=<key>]
	 * : The meta key to update with the scan timestamp. Defaults to 'wp_posts_maintenance_last_scan'.
	 *
	 * [--dry_run]
	 * : Perform a dry run without updating any posts.
	 *
	 * [--verbose]
	 * : Display detailed output during the scan.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wp-posts-maintenance scan_posts
	 *     wp wp-posts-maintenance scan_posts --post_type=page
	 *     wp wp-posts-maintenance scan_posts --start_date=2023-01-01 --end_date=2023-06-30
	 *     wp wp-posts-maintenance scan_posts --post_ids=1,2,3
	 *     wp wp-posts-maintenance scan_posts --dry_run
	 *     wp wp-posts-maintenance scan_posts --verbose
	 *
	 * @since 1.0.0
	 */
	WP_CLI::add_command(
		'wp-posts-maintenance scan_posts',
		function ( $args, $assoc_args ) {

			// Extract options with defaults.
			$post_types = isset( $assoc_args['post_type'] ) ? explode( ',', $assoc_args['post_type'] ) : array( 'post' );
			$start_date = isset( $assoc_args['start_date'] ) ? $assoc_args['start_date'] : null;
			$end_date   = isset( $assoc_args['end_date'] ) ? $assoc_args['end_date'] : null;
			$post_ids   = isset( $assoc_args['post_ids'] ) ? explode( ',', $assoc_args['post_ids'] ) : array();
			$meta_key   = isset( $assoc_args['meta_key'] ) ? $assoc_args['meta_key'] : 'wp_posts_maintenance_last_scan';
			$dry_run    = isset( $assoc_args['dry_run'] );
			$verbose    = isset( $assoc_args['verbose'] );

			// Fetch posts based on provided criteria.
			$args = array(
				'post_type'   => $post_types,
				'post_status' => 'publish',
				'numberposts' => -1,
			);

			// Filter by date range.
			if ( $start_date ) {
				$args['date_query'][] = array(
					'after' => $start_date,
				);
			}
			if ( $end_date ) {
				$args['date_query'][] = array(
					'before' => $end_date,
				);
			}

			// Filter by post IDs.
			if ( ! empty( $post_ids ) ) {
				$args['post__in'] = $post_ids;
			}

			$posts = get_posts( $args );

			// Verbose output: show how many posts will be processed.
			if ( $verbose ) {
				WP_CLI::log( sprintf( 'Found %d posts to scan.', count( $posts ) ) );
			}

			foreach ( $posts as $post ) {
				if ( $verbose ) {
					WP_CLI::log( sprintf( 'Scanning post ID %d: %s', $post->ID, $post->post_title ) );
				}

				// Dry run: just log the post ID and title, without updating metadata.
				if ( $dry_run ) {
					continue;
				}

				update_post_meta( $post->ID, $meta_key, current_time( 'mysql' ) );

				if ( $verbose ) {
					WP_CLI::log( sprintf( 'Updated post ID %d with current timestamp.', $post->ID ) );
				}
			}

			// Output success message via WP-CLI.
			WP_CLI::success( $dry_run ? 'Dry run completed. No posts were updated.' : 'Posts scanned successfully.' );
		}
	);
}
