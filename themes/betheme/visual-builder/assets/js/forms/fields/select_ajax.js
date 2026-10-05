function mfn_field_select_ajax(field, rwd) {
	let value = field.obj_val;
	let html = '';

	if( _.has(field, 'value') ){
		value = field.value;
	}

	if( !value.length && _.has(field, 'std') ){
		value = field.std;
	}

	html += `<div class="form-group">
			<div class="form-control">

				<div class="mfn-select-ajax">
					<input type="hidden" name="${field.id}" value="${value}" class="mfn-field-value"><input data-selecttype="${field.select_type ?? 'posts'}" data-posttype="${field.select_post_type ?? ''}" type="text" value="${value}" placeholder="Search..." class="mfn-form-control mfn-select-ajax-input">
				</div>

			</div>
		</div>
	`;

	return html;
}