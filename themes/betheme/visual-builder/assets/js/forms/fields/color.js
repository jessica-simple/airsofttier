function mfn_field_color(field = false, rwd) {
	let placeholder = '';
	let value = field.obj_val;
	let classes = ['mfn-form-control mfn-form-input color-picker-vb'];
	let wrapper_classes = ['form-group color-picker has-addons has-addons-prepend'];
	let name_attr = '';
	let data_attr = '';
	let html = '';

	if( _.has(field, 'std') ){
		placeholder = field.std;
	}

	if( _.has(field, 'field_class') ){
		classes.push(field.field_class);
	}

	if( _.has(field, 'id') ){
		name_attr = `name="${field.id}"`;
		html += `<div class="form-content">`;
	}

	if( _.has(field, 'on_change') ){
		classes.push('field-to-object'); // object updater only
	}else if( _.has(field, 'id') ){
		classes.push('mfn-field-value'); // all on change actions
	}

	if( typeof field === 'string' ){
		value = field; // for text shadow
	}

	if( _.has(field, 'key') ){
		data_attr = `data-key="${field.key}"`;
	}

	if( _.has(field, 'point_key') ) {
		data_attr = `data-pointobj="${field.point_key}"`;
	}

	if( value == '#fff' ) value = '#ffffff';

	// console.log(value);

	html += `<div class="${wrapper_classes.join(' ')}">
		<div class="color-picker-group">
			<div class="form-addon-prepend"><a href="#" class="color-picker-open"><span ${ !_.isEmpty(value) ? `style="background-color: ${value}; border-color: ${value};"` : '' } class="label ${ !_.isEmpty(value) && value.length ? getContrastYIQ( value ) : 'light' }"><i class="icon-bucket"></i></span></a></div>
			<div class="form-control has-icon has-icon-right field">
				<input ${data_attr} class="${classes.join(' ')}" type="text" placeholder="${placeholder}" ${name_attr} value="${value}" autocomplete="off" />
				<a class="mfn-option-btn mfn-option-text color-picker-clear" href="#"><span class="text">Clear</span></a>
			</div>
		</div>
	</div>`;

	if( _.has(field, 'id') ){
		html += `</div>`;
	}

	return html;
}