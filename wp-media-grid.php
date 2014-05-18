<?php
/*
Plugin Name: Media Grid
Plugin URI: http://wordpress.org/plugins/media-grid/
Description: A grid view for the WordPress Media Library.
Version: 0.5
Author: Shaun Andrews
*/

class WP_Media_Grid {

	function __construct() {
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'wp_prepare_attachment_for_js' ), 10, 3 );
		add_action( 'wp_ajax_query-attachments', array( $this, 'wp_ajax_query_attachments' ), 0 );
		if ( basename( $_SERVER['REQUEST_URI'] ) !== 'upload.php' ) {
			return;
		}

		add_action( 'load-upload.php', array( $this, 'media_grid' ) );
		add_action( 'admin_init', array( $this, 'enqueue' ) );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
	}

	/**
	 * The main template file for the upload.php screen
	 *
	 * Replaces entire contents of upload.php
	 * @require admin-header.php and admin-footer.php
	 */
	function media_grid() {
		if ( isset( $_REQUEST['media_action'] ) ) {
			switch ( $_REQUEST['media_action'] ) {
				case 'more':
					$next_page = (int) $_GET['next_page'];
					$args = array(
						'post_type' => 'attachment',
						'post_status' => 'inherit',
						'posts_per_page' => 25,
						'paged' => $next_page,
					);

					$items = new WP_Query( $args );
					self::renderMediaItems( $items->posts );
					die();
					break;

				default:
					break;
			}
		}

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 25,
			'paged' => 1,
			'post_mime_type' => 'image',
		);

		$items = new WP_Query( $args );

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
	?>
		<div id="media-library" class="wrap">
		<?php

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {
		// To ease development, use forked versions of core JS.
		wp_deregister_script( 'media-models' );
		wp_deregister_script( 'media-views' );
		wp_register_script( 'media-models', plugins_url( 'core-js-overrides/media-models.js', __FILE__ ), array( 'wp-backbone' ), false, 1 );
		wp_register_script( 'media-views', plugins_url( 'core-js-overrides/media-views.js', __FILE__ ), array( 'utils', 'media-models', 'wp-plupload', 'jquery-ui-sortable', 'wp-mediaelement' ), false, 1 );
		wp_enqueue_media();
		wp_enqueue_script( 'wp-media-grid', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery', 'media-models' ) );
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'styles.css', __FILE__ ) );
	}

	public function print_media_templates() {
		require_once( plugin_dir_path( __FILE__ ) . 'media-template.php' );
	}

	/**
	 * A custom version of the core function, which allows for attachments
	 * to be unset via the `wp_prepare_attachment_for_js` filter.
	 *
	 * Required for filtering attachments by filesize.
	 *
	 * Eventually would be nice to have WP_Query filesize querying options.
	 */
	public function wp_ajax_query_attachments() {
		if ( ! current_user_can( 'upload_files' ) )
		wp_send_json_error();

		$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
		$query = array_intersect_key( $query, array_flip( array(
			's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
			'post_parent', 'post__in', 'post__not_in',
		) ) );

		$query['post_type'] = 'attachment';
		$query['post_status'] = 'inherit';
		if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) )
			$query['post_status'] .= ',private';

		/**
		 * Filter the arguments passed to WP_Query during an AJAX
		 * call for querying attachments.
		 *
		 * @since 3.7.0
		 *
		 * @see WP_Query::parse_query()
		 *
		 * @param array $query An array of query variables.
		 */
		$query = apply_filters( 'ajax_query_attachments_args', $query );
		$query = new WP_Query( $query );

		$posts = array_map( 'wp_prepare_attachment_for_js', $query->posts );

		$posts = array_filter( $posts );

		// Reset array keys in case any were unset via wp_prepare_attachment_for_js
		// returning false.
		$posts = array_values( $posts );

		wp_send_json_success( $posts );
	}

	/**
	 * Add support for filesize queries to the `query-attachments` AJAX endpoint.
	 *
	 * Add filesize information into the attachment's attributes.
	 */
	public function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		$bytes = filesize( get_attached_file( $attachment->ID ) );

		$response['filesizeBytes'] = $bytes;
		$response['filesizeHumanReadable'] = size_format( $bytes );

		if ( isset( $_REQUEST['query'] ) ) {
			if ( isset( $_REQUEST['query']['minimumFilesize'] ) && $_REQUEST['query']['minimumFilesize'] && $_REQUEST['query']['minimumFilesize'] * 1024 > $response['filesizeBytes'] ) {
				return false;
			}

			if ( isset( $_REQUEST['query']['maximumFilesize'] ) && $_REQUEST['query']['maximumFilesize'] && $_REQUEST['query']['maximumFilesize'] * 1024 < $response['filesizeBytes'] ) {
				return false;
			}
		}

		return $response;
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;