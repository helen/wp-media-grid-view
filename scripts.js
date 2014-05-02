(function($) {
	$(document).ready( function() {
		wp.media({
			frame: 'manage',
			filterable: 'uploaded',
			title: 'Media Library',
			container: $('#media-library')
		}).open();
	});
})(jQuery);