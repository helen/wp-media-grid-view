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
		add_action( 'admin_init', array( $this, 'enqueue' ) );

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

		/**
		 * Ajax Messages 
		 *    -->uses javascript to load and populate
		 */
		?>
		<div id="message" class="updated"></div>
		
		<div id="media-library" class="wrap">
			<h2>Media Library
				<?php if ( current_user_can( 'upload_files' ) ) { ?>
					<a href="media-new.php" class="add-new-h2"><?php echo esc_html_x('Add New', 'file'); ?></a>
				<?php } ?>
				<a href="./upload.php" class="add-new-h2" id="media-list-button">Media List</a></h2>

			<ul class="media-nav" data-filter="<?php echo $filter; ?>" data-tag="<?php echo $current_tag; ?>">
				<?php /* 
				<li><a href="<?php echo admin_url( 'upload.php?grid' ); ?>"<?php if ( ( $filter == 'all' ) && ( empty( $current_tag ) ) ) echo ' class="current"'; ?>>All Media <span class="count">523</span></a></li>
				<li><a href="#">Media Groups <span class="count">18</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?grid&filter=image' ); ?>"<?php if ( $filter == 'image' ) echo ' class="current"'; ?>>Images <span class="count">293</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?grid&filter=video' ); ?>"<?php if ( $filter == 'video' ) echo ' class="current"'; ?>>Video <span class="count">25</span></a></li>
				<li><a href="<?php echo admin_url( 'upload.php?grid&filter=document' ); ?>"<?php if ( $filter == 'document' ) echo ' class="current"'; ?>>Documents <span class="count">82</span></a></li>
				<li class="tags">
					<?php $tags = get_terms( 'media_tag', array( 'hide_empty' => false ) ); ?>
					<select>
						<?php if( empty($current_tag) ): ?>
						<option value="none">Filter by tag&hellip;</option>
						<?php else: ?>
						<option value="none">Show All</option>
						<?php endif; ?>
						<?php foreach( $tags as $tag ): ?>
						<option<?php if( $current_tag == $tag->name ) echo ' selected="selected"'; ?>><?php echo $tag->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				*/ ?>
				<li class="thumbnail-size">
					<input type="text" data-slider="true" data-slider-step="0.1" data-slider-snap="false" value="1" data-slider-range="0.8,2.2">
				</li>
				<li class="live-search">
					<input type="search" placeholder="Search viewable media&hellip;">
				</li>
				<li class="media-select-all"><input type="checkbox" name="media-select-all" value=""><span>Check All</span></li>
				<li id="total-view-items">
					<div id="total-items"><?php 
						$found = $items->found_posts;
						echo  '<span class="found">' . $found . '</span>'; if($found==1){?>item<?php }else{ ?>items<?php } ?></div>
					<div id="view-items"></div>
				</li>
			</ul>

			<ol class="media-grid">
				<?php self::renderMediaItems( $items->posts ); ?>
			</ol>

			<div id="add-media">
				<p>Drop media here to upload, or <button>Browse</button> your computer.</p>				
			</div>
			<div id="selected-media-details">
				<div class="selected-meta">
					<h2 class="selected-count"><strong>0</strong> items selected / &nbsp;&nbsp;&nbsp;Actions:</h2>
					<ul class="selected-media-options inactive">
						<li><a class="selected-delete" href="#"><div class="dashicons dashicons-trash"></div></a></li>
						<li><a class="selected-tag" href="#"><div class="dashicons dashicons-tag"></div></a></li>
						<li><a class="selected-unselect" href="#"><button type="button" title="Clear selection" class="bttn_clear_all">Clear selection</button></a></li>


					</ul>
				</div>
				<?php /* save for later?
				<ol class="selected-media">
				</ol>
				*/ ?>
			</div>

			<a href="1" class="more-media" data-url="<?php echo $_SERVER['REQUEST_URI']; ?>">Moar!</a>

			<div id="media-modal">
				<input type="hidden" value="" id="media-id">
				<ol class="compare-items"></ol>
				<div class="modal-sidebar">
					<div class="modal-actions">
						<span class="close-button"><b>Back to Media</b></span>
						<a href="#" target="_blank" class="full-size button-secondary">Full Size</a>
					</div>
					<div class="modal-details">
					</div>
					<ul class="modal-nav">
						<li class="nav-prev"></li>
						<li class="nav-next"></li>
					</ul>
				</div>
			</div>
		</div>
		<?php

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	public function renderMediaItems( $items ) {
		foreach ( $items as $item) : ?>
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
				$item_meta = wp_get_attachment_metadata( $item->ID );
			?>
			<li class="media-item" id="media-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-url="<?php echo $item->guid; ?>">
				<div class="media-thumb">
					<?php echo $thumb; ?>
				</div>
				<?php // Reinstating media options popup ?>
				<ul class="media-options">
						<li class="media-select"><input type="checkbox" name="media[]" value="<?php echo $item->ID ?>"></li>
						<li><a class="media-edit" href="<?php echo admin_url( 'post.php?post=' . $item->ID . '&action=edit' ) ?>" title="Edit Details"><span>Edit</span></a></li>
						<li><a class="media-delete" href="#">Delete</a></li>
				</ul>
				<div class="media-details">
					<?php echo $tiny_thumb; ?>
					<h3><?php echo $item->post_title; ?></h3>
					<?php if ( !empty( $item_meta ) ): ?>
					<dl class="media-meta">
						<?php
							$media_author = get_userdata( $item->post_author );
							if ( !empty( $item->post_parent ) )
								$related_post = get_post( $item->post_parent );
						?>

						<dt class="photo-rating">Rating</dt>
						<dd class="photo-rating">
							<ol class="star-rating">
								<li class="star"><b>1 star</b></li>
								<li class="star"><b>2 star</b></li>
								<li class="star"><b>3 star</b></li>
								<li class="star"><b>4 star</b></li>
								<li class="star"><b>5 star</b></li>
							</ol>
						</dd>

						<dt class="photo-uploader">Uploaded By</dt>
						<dd class="photo-uploader">
							<?php echo get_avatar( $media_author->user_email, 64 ); ?>
							<?php echo $media_author->user_login; ?>
						</dd>

						<dt>Uploaded On</dt>
						<dd><?php echo $item->post_date_gmt; ?></dd>

						<dt>Size</dt>
						<dd><?php echo $item_meta['height']; ?>px by <?php echo $item_meta['width']; ?>px</dd>

						<dt class="mm-filepath">File Path</dt>
						<dd class="mm-filepath"><input type="text" value="<?php echo $item->guid; ?>"></dd>

						<?php if ( isset($related_post) ): ?>
						<dt>Related Post</dt>
						<dd><a href="<?php echo get_edit_post_link( $related_post->ID ); ?>"><?php echo $related_post->post_title; ?></a></dd>
						<?php endif; ?>

						<dt>Comments</dt>
						<dd><?php echo $item->comment_count; ?></dd>

						<?php /* Image EXIF */ ?>

						<?php if ( !empty( $item_meta['image_meta']['camera'] ) ): ?>
						<dt>Camera</dt>
						<dd><?php echo $item_meta['image_meta']['camera']; ?></dd>
						<?php endif; ?>

						<?php if ( !empty( $item_meta['image_meta']['credit'] ) ): ?>
						<dt>Credit</dt>
						<dd><?php echo $item_meta['image_meta']['credit']; ?></dd>
						<?php endif; ?>

						<?php if ( !empty( $item_meta['image_meta']['created_timestamp'] ) ): ?>
						<dt>Photo Taken On</dt>
						<dd><?php echo date( 'm/d/Y', $item_meta['image_meta']['created_timestamp'] ); ?></dd>
						<?php endif; ?>
					</dl>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {
		// redo wp-media-grid enqueueing so we can localize with our variables
		wp_register_script( 'wp-media-grid', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'wp-media-grid', 'pdAjax', array('ajaxurl' => admin_url('admin-ajax.php'), 'customDeleteNonce' => wp_create_nonce('pdajax-custom-delete-nonce'),));
		// ----------------------------------------------------------------------
		wp_enqueue_script( 'wp-media-grid');
		wp_enqueue_style( 'wp-media-grid', plugins_url( 'styles.css', __FILE__ ) );

		wp_enqueue_script( 'media-size-slider', plugins_url( 'libs/simple-slider.min.js', __FILE__ ) );
		wp_enqueue_style( 'media-size-slider', plugins_url( 'libs/simple-slider.css', __FILE__ ) );

		wp_enqueue_script( 'live-filter', plugins_url( 'libs/jquery.liveFilter.js', __FILE__ ) );
	}
	
	

}
/**
 *
 *  Ajax handling
 *
 */
function pd_custom_delete() {
	$nonce = $_POST['customDeleteNonce'];
	//Checking nonce
	if(!wp_verify_nonce($nonce, 'pdajax-custom-delete-nonce')) {
		die('Not allowed here!');
	}
	//Only if user has sufficient permissions
	if(current_user_can( 'edit_posts' )) {
		$itemIds = $_POST['itemId'];
		$numID = count($itemIds);
		if ($numID == 0){
			echo 'Did not work';
		} else {
			$responseID = array();
			foreach($itemIds as $itemId){
				$responseID[] = $itemId;
				
				if(false === wp_delete_attachment($itemId)){
					echo 'Fail';
					break;
				}
			}
			echo json_encode($responseID);
		}
	}
	die();
}
add_action('wp_ajax_pd_custom_delete', 'pd_custom_delete');
function pd_all_items() {
	$nonce = $_POST['customDeleteNonce'];
	//Checking nonce
	if(!wp_verify_nonce($nonce, 'pdajax-custom-delete-nonce')) {
		die('Not allowed here!');
	}
	//Only if user has sufficient permissions
	if(current_user_can( 'edit_posts' )) {
		//set the args
		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 25,
			'paged' => 1,
			'post_mime_type' => 'image'
			
		);
		$items = new WP_Query( $args );
		WP_Media_Grid::renderMediaItems( $items->posts );
		//echo $items;
	}
	die();
}
add_action('wp_ajax_pd_all_items', 'pd_all_items');
/**
 * Initialize
 */
new WP_Media_Grid;
?>