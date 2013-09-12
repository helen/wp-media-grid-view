var wpMediaGrid;
var timeoutId;

(function($) {
	wpMediaGrid = {
		init: function() {
			// Size Slider
			wpMediaGrid.changeThumbSize( 1 );
			$(".thumbnail-size input").bind("slider:changed", function (event, data) {
				wpMediaGrid.changeThumbSize( data.value );
			});

			// Toggle Select Items
			$( '.media-item' ).on('click', function() {
				var id = $(this).data('id'),
					details = $(this).find( '.media-details' ),
					selected = $('#selected-media-details .selected-media');

				if ( $(this).hasClass('selected') ) {
					$(this).removeClass('selected');
					selected.find( '#detail-' + id ).remove();
				} else {
					$(this).addClass('selected');
					selected.prepend('<li class="selected-details" id="detail-' + id + '" data-id="' + id + '">' + details.html() + '</li>');
					selected.find('#detail-' + id + ' .media-options').hide();
					selected.find('#detail-' + id + ' h3').show();
				}

				wpMediaGrid.selectedCount();
			});

			// Click to unselect from sidebar
			$( '#selected-media-details' ).on( 'click', '.selected-details', function() {
				var id = $(this).data('id'),
					item = $('#media-' + id);
				$(this).remove();
				item.removeClass('selected');
				wpMediaGrid.selectedCount();
			});

			// Delayed Actions for Mouseover on Items
			$('.media-item').on('mouseover', function() {
				var item = $(this);
				if (!timeoutId) {
					timeoutId = window.setTimeout(function() {
						item.find('.media-details .media-options').fadeIn('fast');
					}, 750);
				}
			});
			$('.media-item').on('mouseleave', function() {
				$(this).find('.media-details .media-options').hide();
				$(this).find('.media-details .media-url-input').hide();
				if (timeoutId) {
					window.clearTimeout(timeoutId);
					timeoutId = null;
				}
			});

			// View Item URL, but don't select the item
			$( '.media-url' ).on( 'click', function(event) {
				event.stopPropagation();
			} )

			// Delete Item
			$( '.media-item' ).on( 'click', '.media-delete', function( event ) {
				event.preventDefault();
				event.stopPropagation();
				var item = $(this).closest( '.media-item' );
				item.css({
					'opacity': '0',
					'margin-left': '-200px'
				});
				setTimeout( function() {
					if( item.hasClass( 'selected' ) ) {
						item.trigger( 'click' );
					}
					item.remove();
				}, 300);
			});
		},

		changeThumbSize: function(ratio) {
			var container_size = 200 * ratio,
				thumb_size = 180 * ratio,
				containers = $( '.media-item' ),
				thumbs = containers.find( '.media-thumb' )
				images = thumbs.find( 'img' );

			containers.height( container_size );
			containers.width( container_size );

			thumbs.height( thumb_size );
			thumbs.width( thumb_size );

			images.each( function(index) {
				$( this ).removeClass('default');
				og_height = $(this).data('height');
				og_width = $(this).data('width');
				$( this ).height( og_height * ratio );
				$( this ).width( og_width * ratio );
			} );
		},

		selectedCount: function() {
			var selected = $('#selected-media-details .selected-details')
				count = $( '#selected-media-details .selected-count strong' );

			count.html( selected.length );
		}
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);