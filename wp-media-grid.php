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

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );

		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' =>'image',
			'post_status' => 'inherit',
			'posts_per_page' => 75,
			'paged' => 1,
		);

		$images = new WP_Query( $args );
	?>
		<div id="media-library" class="wrap">
			<h2>Media Library</h2>
			<ol class="media-grid">
			<?php foreach ( $images->posts as $image) : ?>
				<?php /* <pre><?php var_dump($image); ?></pre> */ ?>
				<li class="media-item" id="media-<?php echo $image->ID; ?>" data-id="<?php echo $image->ID; ?>">
					<div class="media-thumb">
						<?php $img_attr = wp_get_attachment_image_src( $image->ID, array(180,180) ); ?>
						<img class="default" src="<?php echo $img_attr[0]; ?>" width="$img_attr[1]" height="$img_attr[2]" data-width="<?php echo $img_attr[1]; ?>" data-height="<?php echo $img_attr[2]; ?>">
					</div>
					<div class="media-details">
						<?php echo wp_get_attachment_image( $image->ID, array(35,35) ); ?>
						<h3><?php echo $image->post_title; ?></h3>
						<ul class="media-options">
							<li><a class="media-edit" href="#">Edit</a></li>
							<li><a class="media-delete" href="#">Delete</a></li>
						</ul>
					</div>
				</li>
			<?php endforeach; ?>
			</ol>
			<div id="selected-media-details">
				<fieldset class="thumbnail-size">
					<input type="text" data-slider="true" data-slider-step="0.1" data-slider-snap="false" value="1" data-slider-range="0.7,2">
				</fieldset>
				<h2>Selected Media</h2>
				<ul class="selected-media-options">
					<li class="selected-count"><strong>0</strong> items selected</li>
					<li><a href="#">Compare</a></li>
					<li><a href="#">Tag</a></li>
					<li><a href="#">Download</a></li>
					<li><a href="#">Delete</a></li>
				</ul>
				<ol class="selected-media">
				</ol>
			</div>
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