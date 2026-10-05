function mfn_field_text_shadow(field, rwd) {
	let horizontal = '';
	let vertical = '';
	let blur = '';
	let color = '';
	let value = field.obj_val;
	let value_splited = !_.isEmpty(value) ? value.split(" ") : [];
	let classes = ['form-group', 'multiple-inputs', 'pseudo', 'equal-full-inputs'];

	if( !_.isEmpty(value) && value_splited.length ) { 
		horizontal = value_splited.length ? value_splited[0] : '';
		vertical = value_splited.length > 1 ? value_splited[1] : '';
		blur = value_splited.length > 2 ? value_splited[2] : '';
		color = value_splited.length > 3 ? value_splited[3] : '';
	}
	
	let html = `<div class="form-content"><div class="${classes.join(' ')}">
		<div class="form-control">
			<input class="pseudo-field mfn-field-value mfn-form-control" type="hidden" name="${field.id}" value="${value}" autocomplete="off"/>

			<div class="field numeral" data-key="horizontal">
				<input type="text" class="mfn-form-control mfn-form-input numeral mfn-group-field-horizontal" data-key="horizontal" value="${horizontal}" autocomplete="off" />
			</div>

			<div class="field numeral" data-key="vertical">
				<input type="text" class="mfn-form-control mfn-form-input numeral mfn-group-field-vertical" data-key="vertical" value="${vertical}" autocomplete="off" />
			</div>

			<div class="field numeral" data-key="blur">
				<input type="text" class="mfn-form-control mfn-form-input numeral mfn-group-field-blur" data-key="blur" value="${blur}" autocomplete="off" />
			</div>

		</div>

		${mfn_field_color(color)}

	</div></div>`;

	return html;
}