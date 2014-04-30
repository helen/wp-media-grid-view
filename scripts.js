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

			// Inifite Scroll
			$(window).scroll(function () {
				if ($(window).scrollTop() >= $(document).height() - $(window).height() - 800) {
					$( '.more-media' ).trigger( 'click' );
				}
			});

			// Keyboard Nav
			//wpMediaGrid.initKeyboardNav();

			// Live search of viewable items
			wpMediaGrid.initLiveSearch();

			// View Item
			$( '.media-grid' ).on( 'click', '.media-thumb', function(event) {
				var item = $( this ).closest( '.media-item' );
				if ( item.hasClass( 'selected' ) ) {
					wpMediaGrid.clearSelectedItems();
				} else {
					wpMediaGrid.viewItemDetails( item );
				}
			} );

			// View Item Full Size
			$( '#media-sidebar' ).on( 'click', '.media-description', function(event) {
				$( '#media-sidebar' ).toggleClass( 'view-full-size' );
			} );

			// Size Chooser
			$( '.size-options' ).on( 'click', 'li', function(event) {
				var size_button = $(this),
					size_ratio = size_button.data( 'size' ),
					grid = $( '.media-grid' );

				$( '.size-options .current' ).removeClass( 'current' );
				size_button.addClass( 'current' );

				grid.attr( 'class', 'media-grid' );
				grid.addClass( size_ratio )
			} );
		},

		initLiveSearch: function() {
			$( '.media-grid' ).liveFilter('.live-search input', 'li', {
				filterChildSelector: '.media-details'
			});
		},

		clearSelectedItems: function() {
			wpMediaGrid.clearSidebar();
			$('.media-grid').find( '.selected' ).removeClass( 'selected' );
		},

		viewItemDetails: function( item ) {
			var sidebar = $( '#media-sidebar' ),
				item_id = item.attr( 'id' ),
				url = item.data( 'url' ),
				thumb_url = item.find( '.media-thumb img' ).attr( 'src' ),
				sidebar_background = sidebar.find( '.sidebar-background' ),
				item_details = item.find( '.media-details' ).clone();

			wpMediaGrid.clearSelectedItems();

			item.toggleClass( 'selected' );

			sidebar.css( 'background-image', 'url(' + thumb_url + ')' );
			//sidebar_background.attr( 'src', url );

			sidebar.append( item_details );
		},

		clearSidebar: function() {
			var sidebar = $( '#media-sidebar' );

			sidebar.find('.media-details').remove();
			sidebar.css( 'background-image', 'none' );
		},

		initKeyboardNav: function() {
			var modal = $( '#media-modal' );

			$(document).keydown(function(e){
				if ( modal.is( ':visible' ) ) {
					var current_item_id = modal.find( '#media-id' ).val(),
						current_item = $( '#' + current_item_id ),
						prev_item = current_item.prev( '.media-item' ),
						next_item = current_item.next( '.media-item' );

					if (e.keyCode == 37) {
						wpMediaGrid.clearModal();
						prev_item.find( '.media-thumb' ).trigger( 'click' );
					} else if (e.keyCode == 39) {
						wpMediaGrid.clearModal();
						next_item.find( '.media-thumb' ).trigger( 'click' );
					} else if (e.keyCode == 27 ) {
						wpMediaGrid.closeModal();
					}
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
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);