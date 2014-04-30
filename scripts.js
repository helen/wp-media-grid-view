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

			// Keyboard Nav
			wpMediaGrid.initKeyboardNav();

			// Live search of viewable items
			wpMediaGrid.initLiveSearch();

			// Close modal
			$( '#media-modal .close-button' ).on( 'click', function() {
				wpMediaGrid.closeModal();
			});

			// Highlight Filepath on click
			$( '.modal-details' ).on( 'click', '.mm-filepath input', function() {
				$( this ).select();
			} );

			// View Item
			$( '.media-grid' ).on( 'click', '.media-thumb', function(event) {
				var item = $( this ).closest( '.media-item' );
				wpMediaGrid.openModal( item );
			} );

			$( '#media-modal' ).on( 'mouseenter mouseleave', '.star', function() {
				var rating = $(this),
					prev_stars = rating.prevAll();

				prev_stars.toggleClass( 'hover' );
			} );
		},

		initLiveSearch: function() {
			$( '.media-grid' ).liveFilter('.live-search input', 'li', {
				filterChildSelector: '.media-details'
			});
		},

		openModal: function( item ) {
			var modal = $( '#media-modal' ),
				media_compare = modal.find( '.compare-items' ),
				media_details = modal.find( '.modal-details' ),
				media_actions = modal.find( '.modal-actions' ),
				media_view_button = media_actions.find( '.full-size' ),
				item_id = item.attr( 'id' ),
				url = item.data( 'url' ),
				title = item.find( '.media-details h3' ).html(),
				meta = item.find( '.media-meta' ).clone();

			modal.find( '#media-id' ).val( item_id );
			media_view_button.attr( 'href', url );

			media_compare.append( '<li><img src="' + url + '"></li>' );
			media_details.append( '<h3>' + title + '</h3>' );
			media_details.append( meta );

			var current_item_id = modal.find( '#media-id' ).val(),
				current_item = $( '#' + current_item_id ),
				prev_item = current_item.prev( '.media-item' ),
				next_item = current_item.next( '.media-item' ),
				prev_item_thumb = prev_item.find( '.media-details .attachment-thumbnail' ).clone(),
				next_item_thumb = next_item.find( '.media-details .attachment-thumbnail' ).clone();

			modal.find( '.nav-prev' ).append( prev_item_thumb );
			modal.find( '.nav-next' ).append( next_item_thumb );

			if ( modal.is( ':hidden' ) ) {
				$( 'body' ).addClass( 'blurred' );
				modal.fadeIn(200);
			}
		},

		closeModal: function() {
			$( 'body' ).removeClass( 'blurred' );
			$( '#media-modal' ).fadeOut(200);
			wpMediaGrid.clearModal();
		},

		clearModal: function() {
			var modal = $( '#media-modal' );
			modal.find( '.compare-items' ).empty();
			modal.find( '.modal-details' ).empty();
			modal.find( '.nav-prev' ).empty();
			modal.find( '.nav-next' ).empty();
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
	}

	$(document).ready(function($){ wpMediaGrid.init(); });
})(jQuery);