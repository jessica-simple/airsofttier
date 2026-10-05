function mfn_field_checkbox_pseudo(field) {
	let classes = ['form-group', 'checkboxes', 'pseudo'];
	let value = field.obj_val;
	let value_splited = !_.isEmpty(value) ? value.split(' ') : [];

	let html = `<div class="form-content"><div class="${classes.join(' ')}">
			<input type="hidden" class="value mfn-field-value">
			<div class="form-control">
				<ul>
				${ _.has(field, 'options') ? _.map(field.options, function(opt, o) {
					if( !['full-screen', 'full-width', 'equal-height', 'equal-height-wrap'].includes(o) || value_splited.includes(o) ) {
					return `<li class="${ value_splited.length && value_splited.includes(o) ? 'active' : ''}">
							<input type="checkbox" class="mfn-form-checkbox" ${ value_splited.length && value_splited.includes(o) ? 'checked' : '' } name="${field.id}" value="${o}" />
							<span class="title">${opt}</span>
						</li>`;
					}
				}).join('') : '' }
				</ul>
			</div>
		</div></div>`;

	return html;
}