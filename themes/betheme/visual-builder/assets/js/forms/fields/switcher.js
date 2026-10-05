function mfn_field_switcher(field, rwd) {
	let html = '';
	let classes = ['mfn-switch'];
	let value = field.obj_val;
	let option = _.has(field, 'option') ? field.option : '1';

	if( _.has(field, 'value') ){
		value = field.value;
	}

	let id_attr = 'switcher-'+field.id+'-'+Math.floor(Math.random() * 1000)+'-'+Math.floor(Math.random() * 1000)+'-'+Math.floor(Math.random() * 1000);

	if( _.has(field, 'invert') ) classes.push('inverted');

	html += `
		<div class="${classes.join(' ')}">
            <input id="${id_attr}" name="${field.id}" ${ option == value ? 'checked' : '' } value="${option}" class="mfn-field-value" type="checkbox">
            <label for="${id_attr}" class="switch"></label>
        </div>
        <span class="mfn-switch-label">
        	${_.has(field, 'title') ? field.title : ''}
			${_.has(field, 'desc') ? `<span class="mfn-switch-label-desc">${field.desc}</span>` : ''}
		</span>
	`;

	return html;
}