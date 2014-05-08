var wpMediaGrid;

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
			wpMediaGrid.initKeyboardNav();

			// Live search of viewable items
			wpMediaGrid.initLiveSearch();

			// Enable Picker Mode
			$( '.toggle-picker-mode' ).on( 'click', function(event) {
				$( 'body' ).toggleClass( 'picker-mode' );
				wpMediaGrid.clearSelectedItems();
			} );

			// Pick Item
			$( '.media-grid' ).on( 'click', '.item-pick', function(event) {
				event.stopPropagation();
				var checkbox = $( this ),
					media_item = checkbox.closest( '.media-item' ),
					item_id = media_item.data( 'id' ),
					picked_sidebar = $( '#media-picked' ),
					picked_list = picked_sidebar.find( '.picked-list' ),
					picked_count = picked_sidebar.find( '.picked-count' );

				// Check if the item is already picked
				if ( media_item.hasClass( 'picked' ) ) {
					media_item.removeClass( 'picked' );
					picked_list.find( '#picked-' + item_id ).remove();
				} else {
					var square_url = media_item.data( 'square' ),
						square_img = $( '<img>' ),
						picked_list_item = $( '<li></li>' );

					picked_list_item.attr( 'id', 'picked-' + item_id );

					// Highlight the item in the grid
					media_item.addClass( 'picked' );

					// Create the thumbnail image
					square_img.attr( 'src', square_url );

					// Add the thumbnail to the list item
					picked_list_item.append( square_img );

					// Drop it all into the sidebar list
					picked_list.append( picked_list_item );
				}

				// Updated the counter
				picked_count.html( picked_list.find('li').size() );
			} );

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
			$( '#media-sidebar' ).on( 'click', '.selected-thumb', function(event) {
				wpMediaGrid.viewFullItem();
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
				full_url = item.data( 'url' ),
				thumb = item.find( '.media-thumb' ).clone(),
				thumb_url = thumb.find( 'img' ).attr( 'src' ),
				sidebar_background = sidebar.find( '.sidebar-background' ),
				item_details = item.find( '.media-details' ).clone();

			thumb.removeClass( 'media-thumb' ).addClass( 'selected-thumb' );
			item_details.removeClass( 'media-details' ).addClass( 'selected-details' );

			wpMediaGrid.clearSelectedItems();
			item.toggleClass( 'selected' );
			sidebar.css( 'background-image', 'url(' + thumb_url + ')' );
			sidebar.append( thumb, item_details );
		},

		viewFullItem: function() {
			var sidebar = $( '#media-sidebar' ),
				thumb = sidebar.find( '.selected-thumb img' ),
				selected_item = $( '.media-grid .selected' ),
				selected_full_url = selected_item.data( 'url' );

			sidebar.toggleClass( 'view-full-size' );
			thumb.attr( 'src', selected_full_url );

			/*
			if ( sidebar.hasClass( 'view-full-size' ) ) {
				thumb.attr( 'height', '' );
				thumb.attr( 'width', '' );
				sidebar.removeClass( 'view-full-size' );
				thumb.attr( 'src', thumb.data( 'thumb' ) );
			} else {
				var full_height = selected_item.data( 'height' ),
					full_width = selected_item.data( 'width' );

				sidebar.addClass( 'view-full-size' );
				thumb.attr( 'height', full_height );
				thumb.attr( 'width', full_width );
				thumb.data( 'thumb', thumb.attr( 'src' ) );
				thumb.attr( 'src', selected_full_url );
			}
			*/
		},

		clearSidebar: function() {
			var sidebar = $( '#media-sidebar' );

			sidebar.find( '.selected-thumb' ).remove();
			sidebar.find( '.selected-details' ).remove();
			sidebar.css( 'background-image', 'none' );
		},

		initKeyboardNav: function() {
			$(document).keydown(function(e){
				var grid = $( '.media-grid' ),
					sidebar = $( '#media-sidebar' ),
					current_item = grid.find( '.selected' ),
					current_item_id = current_item.data( 'id' )
					prev_item = current_item.prev( '.media-item' ),
					next_item = current_item.next( '.media-item' ),
					above_item = current_item.prev( '.media-item' ).prev( '.media-item' ).prev( '.media-item' ).prev( '.media-item' ),
					below_item = current_item.next( '.media-item' ).next( '.media-item' ).next( '.media-item' ).next( '.media-item' );

				if (e.keyCode == 37) { // Left
					e.preventDefault();
					prev_item.find( '.media-thumb' ).trigger( 'click' );
				} else if (e.keyCode == 39) { // Right
					e.preventDefault();
					next_item.find( '.media-thumb' ).trigger( 'click' );
				} else if (e.keyCode == 38) { // Up
					e.preventDefault();
					above_item.find( '.media-thumb' ).trigger( 'click' );
				} else if (e.keyCode == 40) { // Down
					e.preventDefault();
					below_item.find( '.media-thumb' ).trigger( 'click' );
				} else if (e.keyCode == 27) { // Esc
					if ( sidebar.hasClass( 'view-full-size' ) ) {
						e.preventDefault();
						sidebar.find( '.media-description img' ).trigger( 'click' );
					}
				} else if (e.keyCode == 32) { // Spacebar
					e.preventDefault();
					wpMediaGrid.viewFullItem();
				}
			});
		},
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);