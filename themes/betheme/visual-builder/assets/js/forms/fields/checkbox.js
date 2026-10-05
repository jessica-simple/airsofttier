function mfn_field_checkbox(field) {
	let classes = ['form-group', 'checkboxes'];
	let value = field.obj_val;

	if( _.has(field, 'invert') ){
		classes.push('invert');
	}

	let html = `<div class="form-content"><div class="${classes.join(' ')}">
			<div class="form-control">
				<ul>
				${ _.has(field, 'options') ? _.map(field.options, function(opt, o) {
					var checked = false;

					if( typeof value === 'object' ){
						if( Object.values(value).includes(o) ){
							checked = true;
						}
					}else if( value.includes(o) ){
						checked = true;
					}

					return `<li class="${ checked ? 'active' : ''}">
							<input type="checkbox" class="mfn-form-checkbox" ${ checked ? 'checked' : ''} data-key="${o}" name="${field.id}" value="${o}" />
							<span class="title">${opt}</span>
						</li>`;

				}).join('') : '' }
				</ul>
			</div>
		</div></div>`;

	return html;
}