function mfn_field_upload(field, rwd) {
	let placeholder = '';
	let data = 'image';
	let dynamic_data = '';
	let value = field.obj_val;
	let reset_classes = ['mfn-option-btn', 'mfn-option-blank', 'reset-bg'];
	let input_classes = ['mfn-form-control mfn-field-value mfn-form-input'];
	let classes = ['form-group','browse-image','has-addons','has-addons-append'];
	let img_url = '';

	if( _.has(field, 'std') ) {
		placeholder = field.std;
	}

	if( _.has(field, 'preview') ) {
		input_classes.push('preview-'+field.preview);
	}

	if( _.has(field, 'data') ) {
		data = field.data;
	}

	if( _.has(field, 'input_class') ) {
		input_classes.push(field.input_class);
	}

	if( _.has(field, 'dynamic_data') ) {
		dynamic_data = '<a class="mfn-option-btn mfn-button-dynamic-data" title="Dynamic data" href="#"><span class="mfn-icon mfn-icon-dynamic-data"></span></a>';
	}
	
	if( _.isEmpty(value) ) {
		classes.push('empty');
	}

	if( value == 'none' ) {
		classes.push('empty');
		reset_classes.push('active');
	}

	if( !_.isEmpty(value) && value.includes('/') ){
		img_url = value;
	}else if( _.has(field, 'js_options') && !_.isEmpty(mfn, field.js_options) ){
		img_url = mfn[field.js_options];
	}

	if( _.has(field, 'format') ) {
		classes.push('mfn-upload-field-format-'+field.format);
	}

	let html = `<div class="form-content has-icon has-icon-right">

	${ field.id.includes('css_') && _.has(field, 'responsive') && screen != 'desktop' && !value.includes('{') ? `<a href="#" class="${reset_classes.join(' ')}"><span class="mfn-icon mfn-icon-hide"></span></a>` : '' }

	<div class="${classes.join(' ')}">
		<div class="form-control">
			<input type="text" placeholder="${placeholder}" class="${input_classes.join(' ')}" type="text" name="${field.id}" value="${value}" data-type="${data}"/>
			${dynamic_data ? dynamic_data : ''}
			<a class="mfn-option-btn mfn-button-delete" title="Delete" href="#"><span class="mfn-icon mfn-icon-delete"></span></a>
		</div>

		<div class="form-addon-append browse-image-single">
			<a href="#" class="mfn-button-upload"><span class="label">Browse</span></a>
		</div>

		<div class="break"></div>
		<div class="selected-image">
			${ !_.isEmpty(img_url) && !img_url.includes('{') ? `<img src="${img_url}" alt="">` : '' }
		</div>
	</div></div>`;
	return html;
}