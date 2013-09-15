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
        //register_taxonomy_for_object_type( 'media_tag', 'attachment' );

		//add_action( 'start_media_lib',  array( $this, 'media_grid' ) );
		add_action( 'load-upload.php',  array( $this, 'media_grid' ) );
		//add_action( 'admin_print_scripts-upload.php', array( $this, 'enqueue' ) );
		add_action( 'admin_init', array( $this, 'enqueue' ) );

		add_action( 'init', array( $this, 'custom_taxonomy' ) );
	}

	// Register Custom Taxonomy
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

					
					if ( !empty( $_GET['filter'] ) && ( $_GET['filter'] !== 'all') ) {
						$args['post_mime_type'] = $_GET['filter'];
					}

					if ( !empty( $_GET['tag'] ) ) {
						$args['media_tag'] = $_GET['tag'];
					}

					$items = new WP_Query( $args );
					self::renderMediaItems( $items->posts );
					die();
					break;
				case 'tag':
					$tag = $_POST['tag'];
					$post_id = $_POST['post_id'];
					wp_set_object_terms( $post_id, $tag, 'media_tag', true );
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
			'posts_per_page' => 25,
			'paged' => 1,
		);

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

		$items = new WP_Query( $args );

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
	?>
		<div id="media-library" class="wrap">
			<h2>Media Library</h2>

			<ul class="media-nav" data-filter="<?php echo $filter; ?>" data-tag="<?php echo $current_tag; ?>">
				<li><a href="<?php echo admin_url( 'upload.php' ); ?>"<?php if ( ( $filter == 'all' ) && ( empty( $current_tag ) ) ) echo ' class="current"'; ?>>All Media <span class="count"><?php echo wp_count_posts('attachment')->publish; ?></span></a></li>
				<!-- <li><a href="#">Media Groups <span class="count">18</span></a></li> -->
				<li><a href="<?php echo admin_url( 'upload.php?filter=image' ); ?>"<?php if ( $filter == 'image' ) echo ' class="current"'; ?>>Images <span class="count">293</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?filter=video' ); ?>"<?php if ( $filter == 'video' ) echo ' class="current"'; ?>>Video <span class="count">25</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?filter=document' ); ?>"<?php if ( $filter == 'document' ) echo ' class="current"'; ?>>Documents <span class="count">82</span></a></li>
				<li class="tags">
					<?php $tags = get_terms( 'media_tag', array( 'hide_empty' => false ) ); ?>
					<select>
						<option>Filter by tag&hellip;</option>
						<?php foreach( $tags as $tag ): ?>
						<option<?php if( $current_tag == $tag->name ) echo ' selected="selected"'; ?>><?php echo $tag->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
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
				<fieldset class="live-search">
					<input type="search" placeholder="Search viewable media&hellip;">
				</fieldset>
				<h2 class="selected-count"><strong>0</strong> items selected</h2>
				<ul class="selected-media-options">
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

			<div id="media-compare">
				<ol class="compare-items"></ol>
			</div>
		</div>
		<?php

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	public function renderMediaItems( $items ) {
		foreach ( $items as $item) : ?>
			<?php /* var_dump( $item ); */ ?>
			<?php
				switch ($item->post_mime_type) {
					case 'image/jpeg':
					case 'image/png':
					case 'image/gif':
						$img_attr = wp_get_attachment_image_src( $item->ID, array(180,180) );
						$thumb = '<img class="default" src="' . $img_attr[0] . '" width="' . $img_attr[1] . '" height="' . $img_attr[2] . '" data-width="' . $img_attr[1] . '" data-height="' . $img_attr[2] . '">';
						$tiny_thumb = wp_get_attachment_image( $item->ID, 'thumbnail' );
						break;
					default:
						$thumb = '<img src="' . wp_mime_type_icon( $item->ID ) . '">';
						$tiny_thumb = '<img src="' . wp_mime_type_icon( $item->ID ) . '">';
						break;
				}
				$item_tags = get_the_terms( $item->ID, 'media_tag' );
			?>
			<li class="media-item" id="media-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>">
				<div class="media-thumb">
					<?php echo $thumb; ?>
				</div>
				<ul class="media-options">
					<li><a class="media-edit" href="#" title="Edit Details"><span>Edit</span></a></li>
					<li><a class="media-url" href="<?php echo $item->guid; ?>" target="_new" title="Open Media in New Window"><span>View</span></a></li>
					<?php if ( !empty( $item_tags ) ): ?>
					<li class="media-tags">
						<span>Tags</span>
						<ul>
						<?php foreach( $item_tags as $tag ): ?>
							<li><?php echo $tag->name; ?></li>
						<?php endforeach; ?>
						</ul>
					</li>
					<?php endif; ?>
					<li><a class="media-delete" href="#" title="Delete Media"><span>Delete</span></a></li>
				</ul>
				<div class="media-details">
					<?php echo $tiny_thumb; ?>
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

		wp_enqueue_script( 'media-size-slider', plugins_url( 'libs/simple-slider.min.js', __FILE__ ) );
		wp_enqueue_style( 'media-size-slider', plugins_url( 'libs/simple-slider.css', __FILE__ ) );

		wp_enqueue_script( 'live-filter', plugins_url( 'libs/jquery.liveFilter.js', __FILE__ ) );
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;