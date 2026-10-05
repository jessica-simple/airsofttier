function mfn_field_select(field, rwd) {
	let classes = ['mfn-form-control','mfn-form-select'];
	let wrapper_classes = ['form-content'];
	let data_attr = '';
	let value = field.obj_val;
	let html = '';
	let name_attr = '';
	let extra_select_attr = '';

	if( _.has(field, 'key') ) {
		data_attr = `data-key="${field.key}"`;
	}

	if( _.has(field, 'preview') ){
		classes.push(field.preview);
	}

	if( _.has(field, 'input_class') ){
		classes.push(field.input_class);
	}

	if( _.has(field, 'field_class') ){
		classes.push(field.field_class);
	}

	if( _.has(field, 'id') ){
		html += `<div class="${wrapper_classes.join(' ')}">`;
		name_attr = `name="${field.id}"`;
	}

	if( _.has(field, 'on_change') ){
		classes.push('field-to-object');
	}else{
		classes.push('mfn-field-value');
	}

	if( _.has(field, 'value') ){
		value = field.value;
	}

	if( _.has(field, 'mfn_taxonomies') ) {
		extra_select_attr += ` data-lazy-taxonomy="${field.mfn_taxonomies}" data-loaded="0"`;
	}

	html += `
		<select ${data_attr} ${name_attr} ${extra_select_attr} class="${classes.join(' ')}" autocomplete="off">

		${ _.has(field, 'js_hierarchical_options') ? '<option value="">All</option>' : '' }

		${ _.has(field, 'options') ? _.map( field.options, function(opt, i) {
			let html = '';
			
			if( i.length && i.includes('#optgroup') ) {
				if( opt != '' ) {
					html += `<optgroup label="${opt}">`;
				}else{
					html += `</optgroup>`;
				}
			}else{
				html += '<option '+( value == i ? 'selected' : '' )+' value="'+i+'">'+opt+'</option>';
			}

			return html;
		}).join('') : '' }

		${ _.has(field, 'js_options') ? _.map( mfn[field.js_options], (opt, o) => '<option '+( value == o ? 'selected' : '' )+' value="'+o+'">'+opt+'</option>' ).join('') : '' }
		${ _.has(field, 'js_hierarchical_options') ? _.map( mfn[field.js_hierarchical_options], (opt) => '<option '+( value == opt.slug ? 'selected' : '' )+' value="'+opt.slug+'">'+opt.name+'</option>' ).join('') : '' }
		${ _.has(field, 'kl_options') ? _.map( field.kl_options, (opt) => '<option '+( value == opt.key ? 'selected' : '' )+' value="'+opt.key+'">'+opt.label+'</option>' ).join('') : '' }
		${ _.has(field, 'opt_append') ? _.map( field.opt_append, (opta, o) => '<option '+( value == o ? 'selected' : '' )+' value="'+o+'">'+opta+'</option>' ).join('') : '' }
	`;

	/*if( _.has(field, 'mfn_options') ) {
		html += `<option value="">- Select -</option>`;

		let loop_options = mfn[field.mfn_options];

		if( _.has(field, 'mfn_post_types') ) {
			loop_options = loop_options.filter( (l) => l.publicly_queryable == true );
		}

		html += `${_.map( loop_options, (opt, o) => '<option '+( value == opt['name'] ? 'selected' : '' )+' value="'+opt['name']+'">'+opt['label']+'</option>' ).join('')}`;
	}*/

	if (_.has(field, 'mfn_options')) {
		html += `<option value="">- Select -</option>`;

		let loop_options = [];

		if (field.mfn_options == 'taxonomies') {
			_.each(mfn.post_types, function(pt) {
				if (_.has(pt, 'mfn_tax') && pt.mfn_tax) {
					let taxes = Array.isArray(pt.mfn_tax) ? pt.mfn_tax : _.values(pt.mfn_tax);
					loop_options = loop_options.concat(taxes);
				}
			});

			loop_options = loop_options.filter(function(t) {
				return t && t.name && t.show_ui;
			});

			loop_options = _.uniq(loop_options, false, function(t) {
				return t.name;
			});
		} else {
			loop_options = mfn[field.mfn_options] || [];
		}

		if (_.has(field, 'mfn_post_types')) {
			loop_options = loop_options.filter(function(l) {
				return l.publicly_queryable == true;
			});
		}

		html += _.map(loop_options, function(opt) {
			let val = opt.name || '';
			let label = opt.label || opt.name || '';
			return `<option ${value == val ? 'selected' : ''} value="${val}">${label}</option>`;
		}).join('');
	}

	if( _.has(field, 'post_tax') ) {
		html += `<option value="">- Select -</option>`;
		let find_pt = mfn['post_types'].filter( (p) => p.name == mfn.current_post_type )[0];
		if( find_pt && _.has(find_pt, 'mfn_tax') ) {
			_.map(find_pt['mfn_tax'], function(el,k) {
				html += `<option ${value == el.name ? 'selected' : ''} value="${el.name}">${el.label}</option>`;
			});
		}
	}

	if( _.has(field, 'mfn_taxonomies') ) {
		html += `<option value="">- Select -</option>`;
		//html += `<option ${ value == '0-current' ? 'selected' : '' } value="0-current">Current</option>`;

		html += `<option selected value="${value}">${value}</option>`;
		
		/*if( value && value !== '0-current' ) {
			html += `<option selected value="${value}">${value}</option>`;
		}*/
	}

	html += `</select>`;

	if( _.has(field, 'id') ){
		html += `</div>`;
	}

	return html;
}