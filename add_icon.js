(function($) {
	$(document).ready(function($){
		var grid_btn = $( '<a href="./upload.php?grid" class="add-new-h2" id="media-grid-button">Media Grid</a>' ),
			add_btn = $( '.add-new-h2' );

		grid_btn.insertAfter( add_btn );
	});
})(jQuery);