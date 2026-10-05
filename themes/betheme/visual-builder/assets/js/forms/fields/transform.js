function mfn_field_transform(field) {
	let html = '';
	let used_fields = [
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'TranslateX',
			'key': 'translateX',
			'param': {
				'min': '-1040',
				'max': '1040',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'title': 'TranslateY',
			'responsive': true,
			'key': 'translateY',
			'param': {
				'min': '-1040',
				'max': '1040',
				'step': '1',
				'unit': '%',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'Rotate',
			'key': 'rotate',
			'param': {
				'min': '-360',
				'max': '360',
				'step': '1',
				'unit': 'deg',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'ScaleX',
			'key': 'scaleX',
			'param': {
				'min': '0',
				'max': '3',
				'step': '0.05',
				'unit': '',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'ScaleY',
			'key': 'scaleY',
			'param': {
				'min': '0',
				'max': '3',
				'step': '0.05',
				'unit': '',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'SkewX',
			'key': 'skewX',
			'param': {
				'min': '-4',
				'max': '4',
				'step': '0.05',
				'unit': '',
			}
		},
		{
			'id': field.id,
			'old_id': field.old_id,
			'type': 'sliderbar',
			'responsive': true,
			'title': 'SkewY',
			'key': 'skewY',
			'param': {
				'min': '-4',
				'max': '4',
				'step': '0.05',
				'unit': '',
			}
		},
	];

	html += `<div class="form-group multiple-inputs multiple-inputs-with-color has-addons has-addons-append transform_field">`;
		html += `<input class="mfn-field-value mfn-pseudo-val" name="${field.id}" data-key="string" type="hidden">`;
		const mfn_form_transform = new MfnForm( used_fields );
	    html += mfn_form_transform.render();
    html += `</div>`;

	return html;
}