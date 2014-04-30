<?php
/*
Plugin Name: Media Grid
Plugin URI: http://wordpress.org/plugins/media-grid/
Description: A grid view for the WordPress Media Library.
Version: 0.4
Author: Shaun Andrews
*/

class WP_Media_Grid {

	function __construct() {

		if (strpos($_SERVER["REQUEST_URI"], "upload.php") === FALSE)
        	return;
        //register_taxonomy_for_object_type( 'media_tag', 'attachment' );

		//add_action( 'start_media_lib',  array( $this, 'media_grid' ) );

		if ( isset( $_GET['grid'] ) ) {
			add_action( 'load-upload.php', array( $this, 'media_grid' ) );
		} else {
			add_action( 'admin_init', array( $this, 'add_grid_icon' ) );
			return;
		}
		//add_action( 'admin_print_scripts-upload.php', array( $this, 'enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		/* add_action( 'init', array( $this, 'custom_taxonomy' ) ); */
	}


	/* Expiremental support for tagging media
	function custom_taxonomy()  {

		$labels = array(
			'name'                       => _x( 'Media Tags', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Media Tag', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Media Tags', 'text_domain' ),
			'all_items'                  => __( 'All Media Tags', 'text_domain' ),
			'parent_item'                => __( 'Parent Media Tag', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Media Tag:', 'text_domain' ),
			'new_item_name'              => __( 'New Media Tag Name', 'text_domain' ),
			'add_new_item'               => __( 'Add Media Tag Genre', 'text_domain' ),
			'edit_item'                  => __( 'Edit Media Tag', 'text_domain' ),
			'update_item'                => __( 'Update Media Tag', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate Media Tags with commas', 'text_domain' ),
			'search_items'               => __( 'Search Media Tags', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove Media Tags', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used Media Tags', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'media_tag', 'attachment', $args );

	}
	*/


	/**
	 * Adds a button that will switch you to the grid view.
	 */
	function add_grid_icon() {
		wp_enqueue_script( 'wp-media-grid', plugins_url( 'add_icon.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'add_icon.css', __FILE__ ) );
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

					/*
					if ( !empty( $_GET['filter'] ) && ( $_GET['filter'] !== 'all') ) {
						$args['post_mime_type'] = $_GET['filter'];
					}
					*/

					$items = new WP_Query( $args );
					self::renderMediaItems( $items->posts );
					die();
					break;
				/*
				case 'tag':
					$tag = $_POST['tag'];
					$post_id = $_POST['post_id'];
					wp_set_object_terms( $post_id, $tag, 'media_tag', true );
					die();
					break;
				*/
				default:
					break;
			}
		}

		// $filter = 'all';

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 25,
			'paged' => 1,
			'post_mime_type' => 'image',
		);

		/*
		$current_tag = false;
		if ( isset( $_GET['tag'] ) ) {
			$current_tag = $_GET['tag'];
			$args['media_tag'] = $current_tag;
		}

		if ( isset( $_GET['filter'] ) ) {
			switch ($_GET['filter']) {
				case 'image':
					$filter = 'image';
					$args['post_mime_type'] = 'image';
					break;
				case 'video':
					$filter = 'video';
					$args['post_mime_type'] = 'video';
					break;
				case 'document':
					$filter = 'document';
					$args['post_mime_type'] = 'application';
					break;
			}
		}
		*/

		$items = new WP_Query( $args );

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
	?>
		<div id="media-modal-js" class="wrap">
		</div>
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

		wp_enqueue_script( 'wp-media-grid', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery', 'media-views' ), 1, true );
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'styles.css', __FILE__ ) );
		wp_enqueue_script( 'media-size-slider', plugins_url( 'libs/simple-slider.min.js', __FILE__ ) );
		wp_enqueue_style( 'media-size-slider', plugins_url( 'libs/simple-slider.css', __FILE__ ) );

		wp_enqueue_script( 'live-filter', plugins_url( 'libs/jquery.liveFilter.js', __FILE__ ) );
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;