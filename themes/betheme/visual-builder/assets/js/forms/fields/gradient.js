function mfn_field_gradient(field) {
	let html = '<div class="gradient-form">';
	let rand = Math.random().toString(36).substring(4);
	let cond_id = 'gradient-type-'+rand;
	let cond_source = 'gradient-source-'+rand;
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
			'attr_id': cond_source,
			'class': 'object-css-input',
			'field_class': 'gradient-source',
			'on_change': 'object',
			'key': 'source',
			'type': 'switch',
			'title': 'Type',
			'options': {
				'': 'Default',
				'custom': 'Custom',
			}
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'class': 'object-css-input gradient-custom',
			//'field_class': 'gradient-custom',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': 'custom' },
			'on_change': 'object',
			'key': 'custom_string',
			'type': 'textarea',
			'title': 'Custom gradient',
			'desc': 'Place only the style value, for example: linear-gradient(90deg, rgba(36, 91, 115, 1) 0%, rgba(87, 199, 133, 1) 50%, rgba(237, 221, 83, 1) 100%)',
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'class': 'object-css-input',
			'field_class': 'gradient-color',
			'on_change': 'object',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': '' },
			'key': 'color',
			'type': 'color',
			'title': 'Color',
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'class': 'object-css-input',
			'field_class': 'gradient-location',
			'on_change': 'object',
			'type': 'sliderbar',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': '' },
			'title': 'Location',
			'key': 'location',
			'default_value': '0%',
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
			'class': 'object-css-input',
			'field_class': 'gradient-color2',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': '' },
			'on_change': 'object',
			'key': 'color2',
			'type': 'color',
			'title': 'Second color',
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'class': 'object-css-input',
			'field_class': 'gradient-location2',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': '' },
			'type': 'sliderbar',
			'on_change': 'object',
			'default_value': '100%',
			'key': 'location2',
			'title': 'Second location',
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
			'class': 'object-css-input',
			'field_class': 'gradient-type',
			'condition': { 'id': cond_source, 'opt': 'is', 'val': '' },
			'on_change': 'object',
			'attr_id': cond_id,
			'type': 'select',
			'title': 'Type',
			'key': 'type',
			'default_value': 'linear-gradient',
			'options': {
				'linear-gradient': 'Linear gradient',
				'radial-gradient': 'Radial gradient',
			}
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'class': 'object-css-input',
			'field_class': 'gradient-angle',
			'on_change': 'object',
			'type': 'sliderbar',
			'key': 'angle',
			'condition': [ 'AND', {'id': cond_id, 'opt': 'is', 'val': 'linear-gradient'}, {'id': cond_source, 'opt': 'is', 'val': ''} ],
			'title': 'Angle',
			'default_value': '0deg',
			'param': {
				'min': '0',
				'max': '359',
				'step': '1',
				'unit': 'deg',
			}
		},

		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'select',
			'on_change': 'object',
			'class': 'object-css-input',
			'field_class': 'gradient-position',
			'default_value': 'center center',
			'key': 'position',
			'condition': [ 'AND', {'id': cond_id, 'opt': 'is', 'val': 'radial-gradient' }, {'id': cond_source, 'opt': 'is', 'val': ''} ],
			'title': 'Position',
			'options': {
				'center center': 'Center Center',
				'center left': 'Center Left',
				'center right': 'Center Right',
				'top center': 'Top Center',
				'top left': 'Top Left',
				'top right': 'Top Right',
				'bottom center': 'Bottom Center',
				'bottom left': 'Bottom Left',
				'bottom right': 'Bottom Right',
			}
		},

	];

	html += `<input type="hidden" data-key="string" class="pseudo-field gradient-hidden mfn-field-value" name="${field.id}" value="${value}">`;
	const mfn_form_gradient = new MfnForm( used_fields );
    html += mfn_form_gradient.render();
	html += '</div>';

	return html;
}