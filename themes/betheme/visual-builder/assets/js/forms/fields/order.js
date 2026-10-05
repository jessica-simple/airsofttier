function mfn_field_order(field) {
	let value = field.obj_val;
	let options = [];

	/*if( _.has(field, 'std') ){
		value = field.std;
	}

	if( _.has(edited_item['attr'], field.id) && typeof edited_item['attr'][field.id] === 'string' ){
		value = edited_item['attr'][field.id];
	}*/

	if( value !== '' ){
		options = value.split(',');
	}

	let html = `<div class="form-content"><div class="form-group order-field"><div class="form-control"><ul class="tabs-wrapper">`;

	if( _.has(field, 'options') && field.options == 'this_wraps' ) {

		let find_wraps = $builder.find('.mcb-section-'+edited_item.uid+' .mcb-wrap:not(.mfn-nested-wrap)').length ? $builder.find('.mcb-section-'+edited_item.uid+' .mcb-wrap:not(.mfn-nested-wrap)') : false;

		if (find_wraps) {
			find_wraps.each(function (i, el) {
				let e_uid = jQuery(el).attr('data-uid');
				let e_wrap = mfn.pagedata.filter( (w) => w.uid == e_uid )[0];

			  html += `
			    <li class="tab tab-${e_uid}" data-uid="${e_uid}">
			      <div class="tab-header">
			        <span class="title">${e_wrap.attr?.tab_title || iframe.jQuery('.mfn-builder-active').find('li.mfn-nav-tab-'+e_uid+' a').text()}</span>
			      </div>
			    </li>`;
			});
		}

	}else{	
		html += `${ _.map( options, (opt, i) => '<li class="tab tab-'+opt+'"><div class="tab-header"><span class="title">'+opt+'</span></div></li>' ).join('') }`;
	}

	

	html += `</ul></div>
	<input type="hidden" class="mfn-form-control mfn-field-value order-input-hidden" name="${field.id}" value="${value}" />
	</div></div>`;

	return html;
}