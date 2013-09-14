var wpMediaGrid;
var timeoutId;

(function($) {
	wpMediaGrid = {
		init: function() {
			// Moar media!
			$( '.more-media' ).on( 'click', function(event) {
				event.preventDefault();
				var link = $(this),
					url = link.data('url'),
					next_page = parseInt( link.attr('href') ) + 1;
				if ( link.hasClass( 'loading' ) ) {
					return;
				}
				link.addClass( 'loading' ).html( 'Loading more items&hellip;' );
				console.log('moar!', url);
				$.get( url, {
					media_action: 'more',
					next_page: next_page
				} ).done( function(data) {
					if ( data ) {
						$( '.media-grid' ).append( data );
						wpMediaGrid.changeThumbSize( $( '.thumbnail-size input' ).val() );
						link.attr( 'href', next_page.toString() );
						link.removeClass( 'loading' ).html('Get moar!');
					} else {
						link.remove();
					}
				});
			});

			// Inifite Scroll, and done by a js hack...
			$(window).scroll(function () {
				if ($(window).scrollTop() >= $(document).height() - $(window).height() - 600) {
					$( '.more-media' ).trigger( 'click' );
				}
			});

			// Size Slider
			wpMediaGrid.changeThumbSize( 1 );
			$(".thumbnail-size input").bind("slider:changed", function (event, data) {
				wpMediaGrid.changeThumbSize( data.value );
			});

			// Toggle Select Items
			$( '.media-grid' ).on('click', '.media-item', function() {
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

			// Open Grouped Items
			$( '.sub-grid' ).on( 'click', '.group-description', function() {
				$(this).closest( '.sub-grid' ).toggleClass('expand');
			});

			// Click to unselect from sidebar
			$( '#selected-media-details' ).on( 'click', '.selected-details', function() {
				var id = $(this).data('id'),
					item = $('#media-' + id);
				$(this).remove();
				item.removeClass('selected');
				wpMediaGrid.selectedCount();
			});

			// Tag selected items
			$( '.selected-tag' ).on( 'click', function(event) {
				event.preventDefault();
				var tag = prompt( 'Enter tag to apply.' ),
					url = $(this).attr('href'),
					items = $( '.media-item.selected' );

				items.each( function() {
					var item_id = $( this ).data('id');
					$.post( url, {
						media_action: 'tag',
						tag: tag,
						post_id: item_id
					}, function(data) {
						console.log( item_id, tag + ' added!', data );
					});
				});
			});

			// Unselect All
			$( '#selected-media-details' ).on( 'click', '.selected-unselect', function(event) {
				event.preventDefault();
				$( '.media-item.selected' ).trigger( 'click' );
			});

			// Delete Selected
			$( '#selected-media-details' ).on( 'click', '.selected-delete', function(event) {
				event.preventDefault();
				if( confirm( 'This will delete these media items from your library. (Not really, this is just a prototype.)' ) ) {
					var items = $( '.media-item.selected' );
					items.each( function() {
						wpMediaGrid.delete( $(this) );
					});
				}
			});

			// Delayed Actions for Mouseover on Items
			$('.media-grid').on('mouseover', '.media-item', function() {
				var item = $(this);
				if (!timeoutId) {
					timeoutId = window.setTimeout(function() {
						item.find('.media-options').fadeIn('fast');
					}, 750);
				}
			});
			$('.media-grid').on('mouseleave', '.media-item', function() {
				$(this).find('.media-options').hide();
				if (timeoutId) {
					window.clearTimeout(timeoutId);
					timeoutId = null;
				}
			});

			// View Item URL, but don't select the item
			$( '.media-grid' ).on( 'click', '.media-url', function(event) {
				event.stopPropagation();
			} )

			// Delete Single Item
			$( '.media-grid' ).on( 'click', '.media-delete', function( event ) {
				event.preventDefault();
				event.stopPropagation();
				if( confirm( 'This will delete this media item from your library. (Not really, this is just a prototype.)' ) ) {
					var item = $( this ).closest( '.media-item' );
					wpMediaGrid.delete( item );
				}
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

			$( '.sub-grid' ).height( container_size );
			$( '.sub-grid' ).width( container_size );

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
		},

		// item is a .media-item block
		delete: function(item) {
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
		}
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);