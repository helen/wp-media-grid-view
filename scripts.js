(function($) {
	wp.media({
		frame: 'manage',
		filterable: 'uploaded',
		modal: false,
		title: 'Media Library',
		container: jQuery('#media-modal-js').first()
	}).open();
})(jQuery);