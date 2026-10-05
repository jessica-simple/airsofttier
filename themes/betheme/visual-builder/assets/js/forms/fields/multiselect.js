function mfn_field_multiselect(field) {
	let value = [];
	let options = [];
	let inputType = '';
	let currentTax = '';

	if (_.has(field, 'obj_val') && Array.isArray(field.obj_val)) {
		value = field.obj_val;
	}

	if (
		_.has(edited_item, 'attr') &&
		_.has(edited_item['attr'], field.id) &&
		Array.isArray(edited_item['attr'][field.id]) &&
		edited_item['attr'][field.id].length
	) {
		value = edited_item['attr'][field.id];
	}

	if (_.has(field, 'post_tax')) {
		inputType = 'post_tax';

		// Nie pobieramy już opcji z mfn.taxonomies.
		// Dla post_tax opcje będą pobierane przez AJAX na podstawie mfn.current_post_type.

		if (
			mfn.post_type == 'template' &&
			mfn.builder_type != 'standard' &&
			!options.filter((o) => String(o.id || o.term_id) === '0-current').length
		) {
			options.push({ term_id: '0-current', name: 'Current' });
		}

	} else if (_.has(field, 'tax_cpt')) {
		inputType = 'tax_cpt';
		currentTax = mfn.current_tax || '';
		options = [];

	} else if (_.has(field, 'js_hierarchical_options')) {
		inputType = 'js_hierarchical_options';
		options = mfn[field.js_hierarchical_options] || [];
	}

	let html = `<div class="form-content"><div data-val='${JSON.stringify(value)}' class="form-group mfn-multiselect-field-wrapper">
		<div class="form-control">
			${_.map(value, (val) => {
				let valKey = _.has(val, 'key') ? val.key : '';
				let valValue = _.has(val, 'value') ? val.value : '';
				return `<span data-id="${valKey}">&#10005; ${valValue}</span>`;
			}).join('')}
			<input
				data-type="${inputType}"
				data-tax="${currentTax}"
				data-post-type="${mfn.current_post_type || ''}"
				type="text"
				class="mfn-multiselect-input"
				placeholder="Type..."
			>
		</div>
		<ul class="mfn-multiselect-options">
			${_.has(field, 'opt_append')
				? _.map(field.opt_append, (opta, o) => {
					let selected = Array.isArray(value) && value.filter((item) => String(item.key) === String(o)).length ? 'class="selected"' : '';
					return `<li data-name="${String(o).toLowerCase()}" data-id="${o}" ${selected}>${opta}</li>`;
				}).join('')
				: ''
			}
			${_.map(options, (opt) => {
				let optId = _.has(opt, 'term_id') ? opt.term_id : (_.has(opt, 'id') ? opt.id : '');
				let optName = _.has(opt, 'name') ? String(opt.name) : '';
				let selected = Array.isArray(value) && value.filter((item) => String(item.key) === String(optId)).length ? 'class="selected"' : '';

				return `
					<li data-name="${optName.toLowerCase().replaceAll('&nbsp;', '')}" data-id="${optId}" ${selected}>
						${optName}
					</li>
				`;
			}).join('')}
		</ul>
	</div></div>`;

	return html;
}