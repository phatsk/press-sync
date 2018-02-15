<?php

namespace Press_Sync;

/**
 * CLI Support for Press Sync.
 *
 * @since 0.1.0
 */
class CLI {

	/**
	 * Parent plugin class.
	 *
	 * @var   Press_Sync
	 * @since 0.1.0
	 */
	protected $plugin = null;

	/**
	 * The constructor.
	 *
	 * @param Press_Sync $plugin The Press Sync plugin.
	 */
	public function __construct( Press_Sync $plugin ) {

		$this->plugin = $plugin;

		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		// Register the CLI Commands.
		\WP_CLI::add_command( 'press-sync all', array( $this, 'sync_all' ) );
		\WP_CLI::add_command( 'press-sync posts', array( $this, 'sync_posts' ) );
		\WP_CLI::add_command( 'press-sync media', array( $this, 'sync_media' ) );
		\WP_CLI::add_command( 'press-sync pages', array( $this, 'sync_pages' ) );
		\WP_CLI::add_command( 'press-sync users', array( $this, 'sync_users' ) );
		\WP_CLI::add_command( 'press-sync options', array( $this, 'sync_options' ) );
	}

	/**
	 * Synchronize ALL content.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : May be any valid Press Sync option - unspecified options will be parsed from the database options.
	 * - ps_remote_domain     - The domain of the remote site.
	 * - ps_remote_key        - The remote site Press Sync key.
	 * - ps_sync_method       - The sync method (push or pull).
	 * - ps_duplicate_action  - Action to take for duplicate posts.
	 * - ps_force_update      - Whether or not to force update of posts.
	 * - ps_skip_assets       - Whether to skip full attachment uploads.
	 * - ps_options_to_sync   - Comma-separated list of wp_options to sync.
	 * - ps_preserve_ids      - Whether to preserve the IDs of post-type objects.
	 * - ps_content_threshold - Set to a number > 0 to compare duplicates based on content.
	 * - ps_partial_terms     - Set true if terms are already synced, allows for smaller post payloads.
	 * - ps_page_size         - The number of objects to sync in a batch.
	 *
	 * [--local_folder=<local_folder>]
	 * : The local folder of JSON data to read instead of the source database.
	 *
	 * [--verbose]
	 * : Display logs from the remote server during processing.
	 *
	 * @since 0.6.1
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 *
	 * @synopsis [--<field>=<value>] [--local_folder=<local_folder>] [--verbose]
	 */
	public function sync_all( $args, $assoc_args ) {
		$assoc_args = $this->parse_assoc_args( $assoc_args );

		// Get all of the objects to sync in the order that we need them.
		$order_to_sync_all = apply_filters( 'press_sync_order_to_sync_all', array() );

		foreach ( $order_to_sync_all as $wp_object ) {
			$assoc_args['ps_objects_to_sync'] = $wp_object;
			$response = $this->plugin->sync_object( $wp_object, $assoc_args, 1, false, true );
			$this->return_response( $response );
		}

		\WP_CLI::line( 'Syncing of all objects is complete.' );
	}

	/**
	 * Synchronize posts.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : May be any valid Press Sync option - unspecified options will be parsed from the database options.
	 * - ps_remote_domain     - The domain of the remote site.
	 * - ps_remote_key        - The remote site Press Sync key.
	 * - ps_sync_method       - The sync method (push or pull).
	 * - ps_duplicate_action  - Action to take for duplicate posts.
	 * - ps_force_update      - Whether or not to force update of posts.
	 * - ps_skip_assets       - Whether to skip full attachment uploads.
	 * - ps_options_to_sync   - Comma-separated list of wp_options to sync.
	 * - ps_preserve_ids      - Whether to preserve the IDs of post-type objects.
	 * - ps_fix_terms         - Whether terms should be repaired instead of doing a normal sync.
	 * - ps_content_threshold - Set to a number > 0 to compare duplicates based on content.
	 * - ps_partial_terms     - Set true if terms are already synced, allows for smaller post payloads.
	 * - ps_page_size         - The number of objects to sync in a batch.
	 *
	 * [--local_folder=<local_folder>]
	 * : The local folder of JSON data to read instead of the source database.
	 *
	 * [--verbose]
	 * : Display logs from the remote server during processing.
	 *
	 * @synopsis [--local_folder=<local_folder>] [--<field>=<value>]
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 */
	public function sync_posts( $args, $assoc_args ) {
		$assoc_args = $this->parse_assoc_args( $assoc_args );
		$response   = $this->plugin->sync_object( 'post', $assoc_args, 1, false, true );

		$this->return_response( $response );
	}

	/**
	 * Synchronize media.
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 *
	 * @synopsis [--remote_domain=<remote_domain>] [--remote_press_sync_key=<remote_press_sync_key>] [--local_folder=<local_folder>]
	 */
	public function sync_media( $args, $assoc_args ) {

		$response = $this->plugin->sync_object( 'attachment', $assoc_args, 1, false, true );

		$this->return_response( $response );
	}

	/**
	 * Synchronize pages.
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 *
	 * @synopsis [--remote_domain=<remote_domain>] [--remote_press_sync_key=<remote_press_sync_key>] [--local_folder=<local_folder>]
	 */
	public function sync_pages( $args, $assoc_args ) {

		$response = $this->plugin->sync_object( 'page', $assoc_args, 1, false, true );

		$this->return_response( $response );
	}

	/**
	 * Synchronize users.
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 *
	 * @synopsis [--remote_domain=<remote_domain>] [--remote_press_sync_key=<remote_press_sync_key>] [--local_folder=<local_folder>]
	 */
	public function sync_users( $args, $assoc_args ) {

		$response = $this->plugin->sync_object( 'user', $assoc_args, 1, false, true );

		$this->return_response( $response );
	}

	/**
	 * Synchronize users.
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args The associative arugments.
	 *
	 * @synopsis [--remote_domain=<remote_domain>] [--remote_press_sync_key=<remote_press_sync_key>] [--options=<options>] [--local_folder=<local_folder>]
	 */
	public function sync_options( $args, $assoc_args ) {

		$this->plugin->prepare_options( $assoc_args['options'] );

		$response = $this->plugin->sync_content( 'option', $assoc_args, 1, false, true );

		$this->return_response( $response );
	}

	/**
	 * Return the response after synchronizing content.
	 *
	 * @param array $response The response after synchronization.
	 */
	private function return_response( $response = array() ) {

		$total_objects            = isset( $response['total_objects'] ) ? (int) $response['total_objects'] : 0;
		$total_objects_processed  = isset( $response['total_objects_processed'] ) ? (int) $response['total_objects_processed'] : 0;

		\WP_CLI::line( '' ); // Insert a blank line.

		if ( $total_objects === $total_objects_processed ) {
			\WP_CLI::success( 'Successfully synced ' . $total_objects . ' objects.' );
		} else {
			\WP_CLI::error( 'There was a porblem. All of the content didn\'t sync.' );
		}
	}

	/**
	 * Parse the associative arguments with the Press Sync settings.
	 *
	 * @since NEXT
	 * @param  array $assoc_args The associative args from the command line.
	 * @return array
	 */
	private function parse_assoc_args( $assoc_args ) {
		$valid_keys   = array_keys( $this->plugin->parse_sync_settings() );
		$valid_keys[] = 'verbose';

		foreach ( $assoc_args as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ){
				continue;
			}

			unset( $assoc_args[ $key ] );
		}

		$final_args = $this->plugin->parse_sync_settings( $assoc_args );

		foreach ( $final_args as $key => $value ) {
			\WP_CLI::line( sprintf( "Arg set: [%s] => %s", $key, $value ) );
		}

		return $assoc_args;
	}
}
