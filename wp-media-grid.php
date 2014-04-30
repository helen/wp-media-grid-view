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

			<ul class="media-nav">
				<li class="thumb-size">
					<h4>Thumbnail Size</h4>
					<ul class="size-options">
						<li class="size-small" data-size="small">Small</li>
						<li class="size-medium current" data-size="medium">Medium</li>
						<li class="size-large" data-size="large">Large</li>
					</ul>
				</li>
				<li class="live-search">
					<input type="search" placeholder="Search viewable media&hellip;">
				</li>
			</ul>

			<ol class="media-grid medium">
				<?php self::renderMediaItems( $items->posts ); ?>
			</ol>

			<div id="media-sidebar"></div>

			<a href="1" class="more-media" data-url="<?php echo $_SERVER['REQUEST_URI']; ?>">Moar!</a>
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
						$img_attr = wp_get_attachment_image_src( $item->ID, array(600,600) );
						$thumb = '<img class="default" src="' . $img_attr[0] . '" width="' . $img_attr[1] . '" height="' . $img_attr[2] . '" data-width="' . $img_attr[1] . '" data-height="' . $img_attr[2] . '">';
						$tiny_thumb = wp_get_attachment_image( $item->ID, full );
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
					<div class="media-description">
						<div>
							<?php echo $tiny_thumb; ?>
						</div>
					</div>
					<?php if ( !empty( $item_meta ) ): ?>
					<dl class="media-meta">
						<h3><?php echo $item->post_title; ?></h3>
						<p><?php echo $item_meta['height']; ?>px by <?php echo $item_meta['width']; ?>px</p>
						<?php
							$media_author = get_userdata( $item->post_author );
							if ( !empty( $item->post_parent ) )
								$related_post = get_post( $item->post_parent );
						?>

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
		wp_enqueue_script( 'live-filter', plugins_url( 'libs/jquery.liveFilter.js', __FILE__ ) );
	}
}

/**
 * Initialize
 */
new WP_Media_Grid;