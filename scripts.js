var wpMediaGrid;
var timeoutId;

(function($) {
	wpMediaGrid = {
		init: function() {
			// Moar media!
			$( '.more-media' ).on( 'click', function(event) {
				event.preventDefault();
				var link = $(this);
				if ( link.hasClass( 'loading' ) ) {
					return;
				}

				var url = link.data('url'),
					next_page = parseInt( link.attr('href') ) + 1,
					filter = '',
					tag = '';

				filter = $( '.media-nav' ).data( 'filter' );
				tag = $( '.media-nav' ).data( 'tag' );
				link.addClass( 'loading' ).html( 'Loading more items&hellip;' );

				console.log( filter, tag );

				$.get( url, {
					media_action: 'more',
					next_page: next_page,
					filter: filter,
					tag: tag
				} ).done( function(data) {
					if ( data ) {
						$( '.media-grid' ).append( data );
						wpMediaGrid.changeThumbSize( $( '.thumbnail-size input' ).val() );
						link.attr( 'href', next_page.toString() );
						link.removeClass( 'loading' ).html('Get moar!');
					} else {
						link.remove();
					}
					wpMediaGrid.initLiveSearch();
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

			// Live search of viewable items
			wpMediaGrid.initLiveSearch();

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
					selected.find('#detail-' + id + ' h3').hide();
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

			// View tagged items
			$( '.tags select' ).on( 'change', function() {
				var tag = $(this).val();
				window.location = "/wp-admin/upload.php?tag=" + tag;
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

			// View Selected
			$( '#selected-media-details' ).on( 'click', '.selected-compare', function(event) {
				event.preventDefault();
				var items = $( '.media-item.selected' ),
					modal = $( '#media-compare' ),
					compare_items = modal.find( '.compare-items' );

					$.each( items, function( key, value ) {
						compare_items.append( '<li><img src="' + $( value ).find('.media-url').attr('href') + '"></li>' );
					});

				modal.fadeIn();
			});

			// Close compare modal
			$( '#media-compare' ).on( 'click', function() {
				$( this ).fadeOut();
				$( this ).find( '.compare-items' ).empty();
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

		initLiveSearch: function() {
			$( '.media-grid' ).liveFilter('.live-search input', 'li', {
				filterChildSelector: '.media-details'
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

			// Update the count on the page
			count.html( selected.length );

			if ( selected.length == 0 ) {
				$( '.selected-media-options' ).fadeOut();
			} else {
				$( '.selected-media-options' ).fadeIn();
			}
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
		},

		getParam: function(name) {
			name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);