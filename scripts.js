var wpMediaGrid;

(function($) {
	wpMediaGrid = {
		init: function() {
			$( '.media-item' ).on('click', function() {
				var id = $(this).data('id'),
					img = $(this).find('.media-thumb').html(),
					details = $(this).find( '.media-details' ).html(),
					selected = $('#selected-media-details .selected-media');

				console.log( img );

				if ( $(this).hasClass('selected') ) {
					$(this).removeClass('selected');
					selected.find( '#detail-' + id ).remove();
				} else {
					$(this).addClass('selected');
					selected.append('<li id="detail-' + id + '"">' + img + details + '</li>');
				}
			});
		}
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);