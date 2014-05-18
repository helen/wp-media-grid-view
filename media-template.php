<script type="text/html" id="tmpl-media-search-interface">
	<h4>Title</h4>
	<div class="search"></div>
	<h4>Filesize (kB)</h4>
	<input type="text" class="minimum-filesize" placeholder="<?php _e( 'Min' ); ?>">
	<input type="text" class="maximum-filesize" placeholder="<?php _e( 'Max' ); ?>">
	<h4>Date</h4>
	<input type="text" class="from-date" placeholder="<?php _e( 'From' ); ?>">
	<input type="text" class="to-date" placeholder="<?php _e( 'To' ); ?>">
</script>
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
<script type="text/html" id="tmpl-attachment-details-new">
		<h3>
			<?php _e('Attachment Details'); ?>

			<span class="settings-save-status">
				<span class="spinner"></span>
				<span class="saved"><?php esc_html_e('Saved.'); ?></span>
			</span>
		</h3>
		<div class="attachment-info">
			<div class="thumbnail thumbnail-{{ data.type }}">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( 'image' === data.type ) { #>
					<img src="{{ data.size.url }}" draggable="false" />
				<# } else { #>
					<img src="{{ data.icon }}" class="icon" draggable="false" />
				<# } #>
			</div>
			<div class="details">
				<div class="filename">{{ data.filename }}</div>
				<div class="uploaded">{{ data.dateFormatted }}</div>

				<# if ( data.filesizeHumanReadable ) { #>
					<div class="file-size">{{ data.filesizeHumanReadable }}</div>
				<# } #>

				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions">{{ data.width }} &times; {{ data.height }}</div>
					<# } #>

					<# if ( data.can.save ) { #>
						<a class="edit-attachment" href="{{ data.editLink }}&amp;image-editor" target="_blank"><?php _e( 'Edit Image' ); ?></a>
						<a class="refresh-attachment" href="#"><?php _e( 'Refresh' ); ?></a>
					<# } #>
				<# } #>

				<# if ( data.fileLength ) { #>
					<div class="file-length"><?php _e( 'Length:' ); ?> {{ data.fileLength }}</div>
				<# } #>

				<# if ( ! data.uploading && data.can.remove ) { #>
					<?php if ( MEDIA_TRASH ): ?>
						<a class="trash-attachment" href="#"><?php _e( 'Trash' ); ?></a>
					<?php else: ?>
						<a class="delete-attachment" href="#"><?php _e( 'Delete Permanently' ); ?></a>
					<?php endif; ?>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>
		</div>

		<label class="setting" data-setting="url">
			<span><?php _e('URL'); ?></span>
			<input type="text" value="{{ data.url }}" readonly />
		</label>
		<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
			<label class="setting" data-setting="title">
				<span><?php _e('Title'); ?></span>
				<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
			</label>
			<label class="setting" data-setting="caption">
				<span><?php _e('Caption'); ?></span>
				<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
			</label>
		<# if ( 'image' === data.type ) { #>
			<label class="setting" data-setting="alt">
				<span><?php _e('Alt Text'); ?></span>
				<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
			</label>
		<# } #>
			<label class="setting" data-setting="description">
				<span><?php _e('Description'); ?></span>
				<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
			</label>
	</script>