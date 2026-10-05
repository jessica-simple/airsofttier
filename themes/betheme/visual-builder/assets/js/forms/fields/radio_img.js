function mfn_field_radio_img(field, rwd) {
	let classes = ['form-group','visual-options','checkboxes-list'];
	let alias = field.id;
	let value = field.obj_val;

	if( _.has(field, 'alias') ){
		alias = field.alias;
	}

	if( _.has(field, 'extra_classes') ){
		classes.push(field.extra_classes);
	}

	if( _.isEmpty(value) && _.has(field, 'placeholder') && !_.isEmpty(field.placeholder.val) ) {
		value = field.placeholder.val;
		classes.push('mfn-placeholder-inherited-'+field.placeholder.type);
	}

	let html = `<div class="form-content"><div class="${classes.join(' ')}">
			<div class="form-control">
				<ul>
				${ _.has(field, 'options') ? _.map(field.options, function(opt, o) {

					let img = o;

					if( !img.length ) {
						img = '_default';
					}else{
						img = img.replaceAll(',', '-').replaceAll(';', '-').replaceAll('+', '-').replaceAll(' ', '-');
					}

					return `<li class="${ value == o ? 'active' : '' }">
						<input type="checkbox" ${ value == o ? 'checked' : '' } class="mfn-form-checkbox mfn-field-value" name="${field.id}" value="${o}" />
						<a href="#">
							<div class="mfn-icon" data-tooltip="${opt.replace('<span>', '').replace('</span>', '').replace('<br>', '')}">
								<img src="${mfn.themepath+'/muffin-options/svg/select/'+alias+'/'+img+'.svg'}" alt="${opt}" />
							</div>
							<span class="label">${opt}</span>
						</a>
					</li>`;

				}).join('') : '' }
				</ul>
			</div>
		</div></div>`;

	return html;
}