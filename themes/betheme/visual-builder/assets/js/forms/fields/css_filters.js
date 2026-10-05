function mfn_field_css_filters(field) {
	let html = '<div class="mfn-toggle-fields-wrapper css_filters_form">';
	let value = field.obj_val;

	if( _.has(field, 'placeholder') && _.has(field.placeholder, 'string') && Array.isArray(field.placeholder['string']) && field.placeholder['string'].length > 0 ) {
		let the_last = field['placeholder']['string'].at(-1);
		if( _.has( the_last, 'val' ) && typeof the_last['val'] == 'string' ) {
			value = the_last['val'];
		}
	}

	let used_fields = [
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-blur',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Blur',
			'key': 'blur',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '20',
				'step': '0.1',
				'unit': 'px',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-brightness',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Brightness',
			'key': 'brightness',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '200',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-contrast',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Contrast',
			'key': 'contrast',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '200',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-grayscale',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Grayscale',
			'key': 'grayscale',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '100',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-saturate',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Saturate',
			'key': 'saturate',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '200',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'field_class': 'css_filters-hue-rotate',
			'class': 'object-css-input filter',
			'on_change': 'object',
			'type': 'sliderbar',
			'title': 'Hue',
			'key': 'hue-rotate',
			'default_value': '0',
			'param': {
				'min': '0',
				'max': '360',
				'step': '1',
				'unit': 'deg',
			}
		},
	];

	html += `<input data-key="string" type="hidden" class="pseudo-field css_filters-hidden mfn-field-value" name="${field.id}" value="${value}">`;

	const mfn_form_gradient = new MfnForm( used_fields );
    html += mfn_form_gradient.render();
    html += '</div>';
	return html;
}