<script type="text/html" id="tmpl-media-selection-bulk-edit">
	<div class="selection-info">
		<span class="count"></span>
		<# if ( data.clearable ) { #>
			<a class="clear-selection" href="#"><?php _e('Clear'); ?></a>
		<# } #>
		<a class="delete-selection" href="#"><?php _e('Delete'); ?></a>
	</div>
	<div class="selection-view"></div>
</script>