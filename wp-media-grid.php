<?php
/*
Plugin Name: Media Grid
Plugin URI: http://wordpress.org/plugins/media-grid/
Description: A grid view for the WordPress Media Library.
Version: 0.6
Author: The Media Grid Team
*/

class WP_Media_Grid {

	function __construct() {
		// AJAX-related functions that need to be run whether nor not
		// we're on the media grid screen.
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'wp_prepare_attachment_for_js' ), 10, 3 );
		add_action( 'wp_ajax_query-attachments', array( $this, 'wp_ajax_query_attachments' ), 0 );

		// Bail if we're not on the media grid screen.
		if ( basename( $_SERVER['REQUEST_URI'] ) !== 'upload.php' ) {
			return;
		}

		if ( basename( $_SERVER['REQUEST_URI'] ) == 'upload.php?tableview' ) {
			return;
		}

		add_action( 'load-upload.php', array( $this, 'render' ) );
		add_action( 'admin_init', array( $this, 'enqueue' ) );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
	}

	/**
	 * Render the media grid screen.
	 */
	function render() {
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
		?><div id="media-library" class="wrap">
		<a href="./upload.php?tableview" id="media-list-view">See the "old" List View</a>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * To ease development, use forked versions of core JS.
	 */
	public function enqueue() {
		wp_deregister_script( 'media-models' );
		wp_deregister_script( 'media-views' );
		wp_register_script( 'media-models',
			plugins_url( 'core-js-overrides/media-models.js', __FILE__ ),
			array( 'wp-backbone' ), false, 1 );
		wp_register_script( 'media-views',
			plugins_url( 'core-js-overrides/media-views.js', __FILE__ ),
			array( 'utils', 'media-models', 'wp-plupload', 'jquery-ui-sortable',
				'wp-mediaelement', 'jquery-ui-datepicker' ),
			false, 1 );
		wp_enqueue_media();
		wp_enqueue_script( 'wp-media-grid', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery', 'media-models' ) );
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'styles.css', __FILE__ ) );
		wp_enqueue_style( 'jquery-ui-fresh', plugins_url( 'jquery-ui-fresh.css', __FILE__ ) );
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
			if ( isset( $_REQUEST['query']['fromDate'] ) && $_REQUEST['query']['fromDate'] ) {
				$date_time = new DateTime( $_REQUEST['query']['fromDate'] );

				if ( $date_time->format( 'U' ) > ( $response['date'] / 1000 ) )
					return false;
			}
			if ( isset( $_REQUEST['query']['toDate'] ) && $_REQUEST['query']['toDate'] ) {
				$date_time = new DateTime( $_REQUEST['query']['toDate'] );
				// A "to date" query should be inclusive of the day.
				$date_time->modify( '+1 day' );

				if ( $date_time->format( 'U' ) < ( $response['date'] / 1000 ) )
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