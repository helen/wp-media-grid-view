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

		if (strpos($_SERVER["REQUEST_URI"], "upload.php") === FALSE) {
			return;
		}

		add_action( 'load-upload.php', array( $this, 'media_grid' ) );
		add_action( 'admin_init', array( $this, 'enqueue' ) );
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
		wp_enqueue_script( 'live-filter', plugins_url( 'libs/jquery.liveFilter.js', __FILE__ ) );
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;