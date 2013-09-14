<?php
/*
Plugin Name: wp-media-grid
Plugin URI:
Description: A grid view for the WordPress Media Library.
Version: 0.1
Author: Media Grid Team
*/

class WP_Media_Grid {

	function __construct() {

		if (strpos($_SERVER["REQUEST_URI"], "upload.php") === FALSE)
        	return;
        register_taxonomy_for_object_type( 'post_tag', 'attachment' );

		//add_action( 'start_media_lib',  array( $this, 'media_grid' ) );
		add_action( 'load-upload.php',  array( $this, 'media_grid' ) );
		//add_action( 'admin_print_scripts-upload.php', array( $this, 'enqueue' ) );
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
						'posts_per_page' => 15,
						'paged' => $next_page,
					);

					$items = new WP_Query( $args );
					self::renderMediaItems( $items->posts );
					die();
					break;
				case 'tag':
					$tag = $_POST['tag'];
					$post_id = $_POST['post_id'];
					wp_set_object_terms( $post_id, $tag, 'post_tag' );
					die();
					break;
				default:
					break;
			}
		}

		$filter = 'all';

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 50,
			'paged' => 1,
		);

		if ( isset( $_GET['filter'] ) ) {
			switch ($_GET['filter']) {
				case 'images':
					$filter = 'images';
					$args['post_mime_type'] = 'image';
					break;
				case 'videos':
					$filter = 'videos';
					$args['post_mime_type'] = 'video';
					break;
				case 'documents':
					$filter = 'documents';
					$args['post_mime_type'] = 'application';
					break;
			}
		}

		$items = new WP_Query( $args );

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
	?>
		<div id="media-library" class="wrap">
			<h2>Media Library</h2>

			<ul class="media-nav">
				<li><a href="<?php echo admin_url( 'upload.php' ); ?>"<?php if ( $filter == 'all' ) echo ' class="current"'; ?>>All Media <span class="count"><?php echo wp_count_posts('attachment')->publish; ?></span></a></li>
				<!-- <li><a href="#">Media Groups <span class="count">18</span></a></li> -->
				<li><a href="<?php echo admin_url( 'upload.php?filter=images' ); ?>"<?php if ( $filter == 'images' ) echo ' class="current"'; ?>>Images <span class="count">293</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?filter=videos' ); ?>"<?php if ( $filter == 'videos' ) echo ' class="current"'; ?>>Video <span class="count">25</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?filter=documents' ); ?>"<?php if ( $filter == 'documents' ) echo ' class="current"'; ?>>Documents <span class="count">82</span></a></li>
			</ul>

			<ol class="media-grid">
				<?php /* Grouped Items. Not even half-baked...
				<li class="media-group">
					<ol class="sub-grid grouped">
						<li class="group-description">
							<h3>Wireframes</h3>
							<p>3 items</p>
						</li>
						<li class="media-item" id="media-39672" data-id="39672" style="height: 200px; width: 200px;">
							<div class="media-thumb" style="height: 180px; width: 180px;">
								<img class="" src="http://wordpress/wp-content/uploads/2013/08/Screen-Shot-2013-08-30-at-2.19.39-PM-231x300.png" width="$img_attr[1]" height="$img_attr[2]" data-width="138" data-height="180" style="height: 180px; width: 138px;">
							</div>
							<div class="media-details">
								<img width="35" height="35" src="http://wordpress/wp-content/uploads/2013/08/Screen-Shot-2013-08-30-at-2.19.39-PM-150x150.png" class="attachment-35x35" alt="Screen Shot 2013-08-30 at 2.19.39 PM">						<h3>Screen Shot 2013-08-30 at 2.19.39 PM</h3>
								<ul class="media-options" style="display: none;">
									<li><a class="media-edit" href="#" title="Edit Details"><span>Edit</span></a></li>
									<li>
										<a class="media-url" href="http://wordpress/wp-content/uploads/2013/08/Screen-Shot-2013-08-30-at-2.19.39-PM.png" target="_new" title="Open Media in New Window"><span>URL</span></a>
									</li>
									<li><a class="media-delete" href="#" title="Delete Media"><span>Delete</span></a></li>
								</ul>
							</div>
						</li>
						<li class="media-item" id="media-39653" data-id="39653" style="height: 200px; width: 200px;">
							<div class="media-thumb" style="height: 180px; width: 180px;">
								<img class="" src="http://wordpress/wp-content/uploads/2013/08/Tabbed-Templates-300x209.png" width="$img_attr[1]" height="$img_attr[2]" data-width="180" data-height="125" style="height: 125px; width: 180px;">
							</div>
							<div class="media-details">
								<img width="35" height="35" src="http://wordpress/wp-content/uploads/2013/08/Tabbed-Templates-150x150.png" class="attachment-35x35" alt="Widget Area Blueprint">						<h3>Widget Area Blueprint</h3>
								<ul class="media-options" style="display: none;">
									<li><a class="media-edit" href="#" title="Edit Details"><span>Edit</span></a></li>
									<li>
										<a class="media-url" href="http://wordpress/wp-content/uploads/2013/08/Tabbed-Templates.png" target="_new" title="Open Media in New Window"><span>URL</span></a>
									</li>
									<li><a class="media-delete" href="#" title="Delete Media"><span>Delete</span></a></li>
								</ul>
							</div>
						</li>
						<li class="media-item" id="media-39646" data-id="39646" style="height: 200px; width: 200px;">
							<div class="media-thumb" style="height: 180px; width: 180px;">
								<img class="" src="http://wordpress/wp-content/uploads/2013/08/test-300x210.png" width="$img_attr[1]" height="$img_attr[2]" data-width="180" data-height="126" style="height: 126px; width: 180px;">
							</div>
							<div class="media-details">
								<img width="35" height="35" src="http://wordpress/wp-content/uploads/2013/08/test-150x150.png" class="attachment-35x35" alt="test">						<h3>test</h3>
								<ul class="media-options" style="display: none;">
									<li><a class="media-edit" href="#" title="Edit Details"><span>Edit</span></a></li>
									<li>
										<a class="media-url" href="http://wordpress/wp-content/uploads/2013/08/test.png" target="_new" title="Open Media in New Window"><span>URL</span></a>
									</li>
									<li><a class="media-delete" href="#" title="Delete Media"><span>Delete</span></a></li>
								</ul>
							</div>
						</li>
					</ol>
				</li>
				*/ ?>
				<?php self::renderMediaItems( $items->posts ); ?>
			</ol>

			<div id="selected-media-details">
				<fieldset class="thumbnail-size">
					<input type="text" data-slider="true" data-slider-step="0.1" data-slider-snap="false" value="1" data-slider-range="0.6,3">
				</fieldset>
				<h2>Selected Media</h2>
				<ul class="selected-media-options">
					<li class="selected-count"><strong>0</strong> items selected</li>
					<li><a class="selected-compare" href="#">Compare</a></li>
					<li><a class="selected-tag" href="<?php echo $_SERVER['REQUEST_URI']; ?>">Tag</a></li>
					<li><a class="selected-download" href="#">Download</a></li>
					<li><a class="selected-unselect" href="#">Unselect All</a></li>
					<li><a class="selected-delete" href="#">Delete</a></li>
				</ul>
				<ol class="selected-media">
				</ol>
			</div>

			<a href="1" class="more-media" data-url="<?php echo $_SERVER['REQUEST_URI']; ?>">Moar!</a>
		</div>
		<?php

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	public function renderMediaItems( $items ) {
		foreach ( $items as $item) : ?>
			<!--
			<?php var_dump( $item ); ?>
			-->
			<?php
				switch ($item->post_mime_type) {
					case 'image/jpeg':
					case 'image/png':
					case 'image/gif':
						$img_attr = wp_get_attachment_image_src( $item->ID, array(180,180) );
						$thumb = '<img class="default" src="' . $img_attr[0] . '" width="' . $img_attr[1] . '" height="' . $img_attr[2] . '" data-width="' . $img_attr[1] . '" data-height="' . $img_attr[2] . '">';
						break;
					default:
						$thumb = '<img src="' . wp_mime_type_icon( $item->ID ) . '">';
						break;
				}
				
			?>
			<li class="media-item" id="media-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>">
				<div class="media-thumb">
					<?php echo $thumb; ?>
				</div>
				<ul class="media-options">
					<li><a class="media-edit" href="#" title="Edit Details"><span>Edit</span></a></li>
					<li><a class="media-url" href="<?php echo $item->guid; ?>" target="_new" title="Open Media in New Window"><span>View</span></a></li>
					<li><a class="media-delete" href="#" title="Delete Media"><span>Delete</span></a></li>
				</ul>
				<div class="media-details">
					<?php echo wp_get_attachment_image( $item->ID, array(35,35) ); ?>
					<!--
					<?php var_dump( wp_get_post_tags( $item->ID ) ); ?>
					-->
					<h3><?php echo $item->post_title; ?></h3>
				</div>
			</li>
		<?php endforeach;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-media-grid', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'styles.css', __FILE__ ) );

		wp_enqueue_script( 'wp-size-slider', plugins_url( 'libs/simple-slider.min.js', __FILE__ ) );
		wp_enqueue_style( 'wp-size-slider', plugins_url( 'libs/simple-slider.css', __FILE__ ) );
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;