function mfn_field_font_select(field, rwd) {
	let value = field.obj_val;
	let html = '';
	let name_attr = '';
	let data_attr = '';
	let classes = ['form-control'];

	if( _.has(field, 'key') ){
		data_attr = `data-key="${field.key}"`;
	}

	if( _.has(field, 'id') ) {
		name_attr = `name="${field.id}"`;
	}

	html += `<div class="form-content">
		<div class="form-group font-family-select">
			<div class="${classes.join(' ')}">
				<select ${name_attr} ${data_attr} class="mfn-field-value mfn-form-control mfn-form-select" data-value="${value}" autocomplete="off">
					<optgroup label="System">
						${ _.has(mfn.fonts, 'system') ? _.map(mfn.fonts.system, function(font) {
							return `<option value="${font}">${ font != '' ? font : 'Default' }</option>`;
						}).join('') : '' }
					</optgroup>

					${ _.has(mfn.fonts, 'custom') && mfn.fonts.custom.length ? 
					`<optgroup label="Custom Fonts">
						${ _.map(mfn.fonts.custom, function(font) {
							return `<option value="${font}">${ font != '' ? font.replace('#', '') : 'Default' }</option>`; 
						}).join('') }
					</optgroup>` : '' }

					<optgroup label="Google Fonts">
						${ _.has(mfn.fonts, 'all') ? _.map(mfn.fonts.all, function(font) {   
							return `<option value="${font}">${ font != '' ? font : 'Default' }</option>`;
						}).join('') : '' }
					</optgroup>
				</select>
			</div>
		</div>
	</div>`;
	return html;
}