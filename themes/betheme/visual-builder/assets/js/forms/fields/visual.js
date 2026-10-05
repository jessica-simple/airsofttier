function mfn_field_visual(field) {
	let html = '';
	let value = field.obj_val;
	let classes = ['mfn-form-control editor wp-editor-area'];

	if( _.has(field, 'preview') ){
		classes.push( field.preview ); // object updater only
	}

	html += `<div class="form-group visual-editor">
		<div class="form-control">
			<div class="wp-core-ui wp-editor-wrap tmce-active">

				<div class="wp-editor-tools hide-if-no-js">
					<div class="wp-media-buttons">
						<button type="button" class="button insert-media add_media" data-editor="mfn-editor"><span class="wp-media-buttons-icon"></span> Add Media</button>
					</div>
					<div class="wp-editor-tabs">
						<button type="button" class="wp-switch-editor switch-tmce" data-wp-editor-id="mfn-editor">Visual</button>
						<button type="button" class="wp-switch-editor switch-html" data-wp-editor-id="mfn-editor">Text</button>
					</div>
				</div>

				<div class="wp-editor-container">
					<textarea class="${classes.join(' ')}" name="${field.id}" data-visual="mce" id="mfn-editor" rows="8">${value}</textarea>
				</div>

			</div>
		</div>
	</div>`;

	return html;
}