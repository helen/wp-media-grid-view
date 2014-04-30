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
			<h2>Media Library
				<?php if ( current_user_can( 'upload_files' ) ) { ?>
					<a href="media-new.php" class="add-new-h2"><?php echo esc_html_x('Add New', 'file'); ?></a>
				<?php } ?>
			</h2>

			<ul class="media-nav" data-filter="<?php echo $filter; ?>" data-tag="<?php echo $current_tag; ?>">
				<li class="thumbnail-size">
					<input type="text" data-slider="true" data-slider-step="0.1" data-slider-snap="false" value="1" data-slider-range="0.8,2.2">
				</li>
				<li class="live-search">
					<input type="search" placeholder="Search viewable media&hellip;">
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
					<h2 class="selected-count"><strong>0</strong> items selected</h2>
					<ul class="selected-media-options inactive">
						<li><a class="selected-compare" href="#">Compare</a></li>
						<li><a class="selected-unselect" href="#">Unselect</a></li>
					</ul>
				</div>
				<ol class="selected-media">
				</ol>
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