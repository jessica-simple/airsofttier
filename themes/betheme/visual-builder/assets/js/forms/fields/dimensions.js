function mfn_field_dimensions(field, rwd) {
	let value = field.obj_val;
	let test_the_same = 4;
	let html = '';
	let splited_value = [];
	let classes = ['form-group','multiple-inputs','has-addons','has-addons-append'];
	let inputs = _.has(field, 'style') && field.style.includes('border-radius') ? ['&#8598;', '&#8599;', '&#8600;', '&#8601;'] : ['top', 'right', 'bottom', 'left'];

	if( !_.isEmpty(value) && typeof value == 'string' ) splited_value = value.split(' ');

	if( splited_value.length == 4 ) test_the_same = _.uniq(splited_value);

	if( _.has(field, 'version') ){
		classes.push(field.version);
		let tmp_dim_obj = {};
	    if( _.has(field, 'placeholder') && mfnAreAllArraysEqual(field.placeholder) ) {
	    	classes.push('isLinked');
	   		test_the_same = 1;
	    }
    	inputs = ['top', 'right', 'bottom', 'left']; // override border radius fields
	}else{
		classes.push('pseudo');
	}

	if( test_the_same.length == 1 ) classes.push('isLinked');

	html += `<div class="form-content"><div class="${classes.join(' ')}">
		<div class="form-control">

			${ !_.has(field, 'version') ? `<input type="hidden" class="mfn-field-value pseudo-field" name="${field.id}" value="${value}" autocomplete="off">` : '' }

			${ inputs && inputs.length ? _.map(inputs, function(input, i) {
				let input_classes = ['mfn-form-control mfn-form-input numeral'];
				let input_row_classes = ['field'];
				let name_attr = '';
				let key = input;
				let val = '';

				if( _.has(field, 'version') ) {

					input_classes.push('mfn-field-value');
					name_attr = `name="${field.id}"`;

					if( _.has(field, 'placeholder') && _.has(field.placeholder, key) && Array.isArray(field.placeholder[key]) && field.placeholder[key].length > 0 ) {
						let the_last = field['placeholder'][key].at(-1);

						if( _.has( the_last, 'val' ) && typeof the_last['val'] == 'string' ) {

							if( screen != 'desktop' && the_last.type == 'std' && field['placeholder'][key].filter( (x) => x.type != 'std' ).length ){
								let helepr = field['placeholder'][key].filter( (x) => x.type != 'std' );
								the_last = helepr.at(-1);
							}

							if( screen != 'desktop' && the_last.type == 'class' && field['placeholder'][key].filter( (x) => x.type == 'rwd' ).length ){
								let helepr = field['placeholder'][key].filter( (x) => x.type == 'rwd' );
								the_last = helepr.at(-1);
							}
							
							val = the_last['val'];
							if( the_last['type'] == 'std' || the_last['type'] == 'class' || (_.has(the_last, 'screen') && the_last['screen'] != screen ) ) input_row_classes.push('mfn-placeholder-inherited-'+the_last['type']);
						}
					}else if( _.has(field, 'placeholder') && Array.isArray(field.placeholder) && field.placeholder.length > 0 ) {
						let the_last = field['placeholder'].at(-1);
						if( _.has( the_last, 'val' ) && _.has(the_last['val'], key) ) {
							val = the_last['val'][key];
						}
					}

				}else{

					if( field.id.includes('border-radius') ){
						input_classes.push('field-'+i);
						key = i;
					}else{
						input_classes.push('field-'+input);
					}

					if( splited_value.length && _.has(splited_value, i) && splited_value[i].length ){
						val = splited_value[i];
					}

				}

				if( input != 'top' && input != '&#8598;' ) {
					input_row_classes.push('disableable');
				}

				if( input != 'top' && input != '&#8598;' && (test_the_same == 1 || test_the_same.length == 1) ) {
					input_classes.push('readonly');
				}

				return `<div class="${input_row_classes.join(' ')}" data-key="${input}">
					<input type="text" class="${input_classes.join(' ')}" ${name_attr} data-key="${key}" value="${val}" autocomplete="off">
				</div>`;


			}).join('') : '' }

		</div>

		<div class="form-addon-append">
			<a href="#" class="link">
				<span class="label"><i class="icon-link"></i></span>
			</a>
		</div>
	</div></div>`;
	return html;
}

function mfnAreAllArraysEqual(obj) {
    const values = Object.values(obj).map(arr => JSON.stringify(arr));
    if( values.length < 4 ) return false; 
    return values.every(v => v === values[0]);
}
