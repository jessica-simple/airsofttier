class MfnForm {

	constructor(fields, formrow = true) {
		this.fields = fields;
		this.formrow = formrow;
		this.field = {};
		this.html = '';
		this.tab_content = false;
		this.devices = ['desktop', 'laptop', 'tablet', 'mobile'];
	}

	responsive(active) {
 		return `<ul class="responsive-switcher">
 			<li class="${ active == 'desktop' ? 'active' : '' }" data-device="desktop" data-tooltip="Desktop">
 				<span data-device="desktop" class="mfn-icon mfn-icon-desktop"></span>
 			</li>
 			<li class="${ active == 'laptop' ? 'active' : '' }" data-device="laptop" data-tooltip="Laptop">
 				<span data-device="laptop" class="mfn-icon mfn-icon-laptop"></span>
 			</li>
 			<li class="${ active == 'tablet' ? 'active' : '' }" data-device="tablet" data-tooltip="Tablet">
 				<span data-device="tablet" class="mfn-icon mfn-icon-tablet"></span>
 			</li>
 			<li class="${ active == 'mobile' ? 'active' : '' }" data-device="mobile" data-tooltip="Mobile">
 				<span data-device="mobile" class="mfn-icon mfn-icon-mobile"></span>
 			</li>
 		</ul>`;
	}

	render() {

		let that = this;

		if( _.has(edited_item, 'be_classes') && mfn.classes.length ) {
			_.map( edited_item.be_classes, function(clas, c) {
                let nm = mfn.classes.filter( (c) => c.uid == clas )[0];
                if( nm && _.has(nm, 'attr') ) {
                	_.map(that.fields, (field, f) => {
						if( _.has(field, 'id') ) that.values(nm, field, true);
					}).join('');
                }
            }).join('');
		}

		_.map(that.fields, (field, f) => {
			this.field = JSON.parse(JSON.stringify(field));
			
			if( _.has(this.field, 'id') && this.field.id.includes('{mfn.current_post_type}') ) this.field.id = this.field.id.replace('{mfn.current_post_type}', mfn['current_post_type']);
			if( _.has(this.field, 'id') && this.field.id.includes('{mfn.current_tax}') ) this.field.id = this.field.id.replace('{mfn.current_tax}', mfn['current_tax']);
			if( _.has(field, 'id') ) that.values(edited_item, field, false);
			
			this.display(f);
		}).join('');

		if( this.tab_content ) {
			this.html += `</div>`;
			this.tab_content = false;
		}

		return this.html;
	}

	display(f) {

		let field_name = _.has(this.field, 'type') ? 'mfn_field_'+this.field.type : 'mfn_field_header';
		let classes = ['mfn-form-row'];
		let label_classes = ['form-label'];
		let isModified = false;

		if( _.has(this.field, 'type') ) classes.push('mfn-field-'+this.field.type);

		// responsive
		if( _.has(this.field, 'responsive') ) {
			classes.push(`mfn_field_rwd`);

			if( this.field['responsive'] == 'tablet' ) {
				classes.push(`mfn_field_rwd_tablet`);
			}else if( this.field['responsive'] == 'mobile' ) {
				classes.push(`mfn_field_rwd_mobile`);
			}else if( this.field['responsive'] == 'desktop' ) {
				classes.push(`mfn_field_rwd_desktop`);
			}
		}

		if( _.has(this.field, 're_render_sidebar') ){
			classes.push(`mfn_field_render_sidebar_form`);
		}

		if( _.has(this.field, 'themeoptions') ) {
			let to_split = this.field.themeoptions.split(':');
			if( to_split.length > 0 ){
				if( ( !_.isEmpty(mfn.themeoptions[to_split[0]]) && mfn.themeoptions[to_split[0]] != to_split[1]) || ( _.isEmpty(mfn.themeoptions[to_split[0]]) && !_.isEmpty(to_split[1]) ) ) {
					return;
				}else{
					if( !_.isEmpty(mfn.themeoptions['style']) ){
						classes.push('theme-simple-style');
					}else{
						classes.push('theme-classic-style');
					}
				}
			}
		}
 
		// classes
		if( _.has(this.field, 'class') ) {

			if( field_name != 'mfn_field_header' && this.field.class.includes('mfn-deprecated') && ( !_.has(edited_item['attr'], this.field.id) || ( _.has(this.field, 'std') && edited_item['attr'][this.field.id] == this.field.std ) ) ) {
				return;
			}

			classes.push(this.field.class);
		}

		if( field_name == 'mfn_field_header' ) classes.push('row-header');

		if( _.has(edited_item, 'jsclass') ){
			let element_type = edited_item.jsclass;
			if( element_type == 'button' || element_type == 'chart' || element_type == 'code' || element_type == 'sliderbar' ){
				element_type = 'widget-'+element_type;
			}
			classes.push(element_type)
		}

		if( _.has(edited_item, 'uid') && edited_item.uid == 'pageoptions' ){
			classes.push('option');
		}

		// slider input for dimensional inputs
		if( _.has(this.field, 'type') && this.field.type == 'dimensions' ){
			classes.push('mfn-slider-input');
		}

		if( field_name == 'mfn_field_html' ){

			if( this.field.html.includes('modalbox-card') && this.tab_content ){
				this.html += `</div>`;
				this.tab_content = false;
			}

			// no form-row field
			this.html += window[field_name](this.field);

		}else if( field_name == 'mfn_field_info' ){

			// no form-row field
			this.html += `<div class="${classes.join(' ')}">${window[field_name](this.field)}</div>`;

		}else{

			let id = _.has(this.field, 'attr_id') ? `id="${this.field.attr_id.replace('rwd', screen)}"` : '';
			let data_attr = [];
			let label = _.has(this.field, 'title') ? this.field.title : '';
			let field_wrapper_classes = [];
			let field_data_attr = [];
			

			// responsive
			if( _.has(this.field, 'responsive') ) {
				label += `${this.responsive(screen)}`;
			}

			// label after
			if( _.has(this.field, 'label_tools') ) {
				if( _.has(edited_item, 'attr') && _.has(edited_item['attr'], this.field.id) ) label += '<a href="#" class="mfn-option-btn mfn-option-blank btn-medium reset-big-field reset-backdrop-filter" data-tooltip="Reset"><span class="mfn-icon mfn-icon-reset"></span></a>';
				label += '<a href="#" title="Edit" data-tooltip="Edit" class="mfn-option-btn mfn-option-blank btn-medium mfn-vb-label-button"><span class="mfn-icon mfn-icon-edit"></span></a>';
			}

			// desc switcher
			if( _.has(this.field, 'desc') ){
				label_classes.push('form-label-wrapper');
				label += '<a class="mfn-option-btn mfn-option-blank mfn-fr-help-icon" data-tooltip="Toggle description" href="#"><span class="mfn-icon mfn-icon-desc"></span></a>';
			}

			if( _.has(this.field, 'role_restricted') ) {
				classes.push('mfn-editor-min-access');
			}

			// conditions 
			if( _.has(this.field, 'condition') ) {
				classes.push(`activeif`);
				if( this.field.condition.length > 1 ) {
				 _.map(this.field.condition, (c) => {
				 	if( typeof c == 'object' ) classes.push(`activeif-${c.id.replace('rwd', screen)}`);
				 });
				}else{
					classes.push(`activeif-${this.field.condition.id.replace('rwd', screen)}`);
				}
				data_attr.push(`data-condition=${ JSON.stringify(this.field.condition).replace('rwd', screen) }`);
			}

			if( _.has(this.field, 'dynamic_data') ) {
				classes.push('is_dynamic_data');
				data_attr.push(`data-dynamic="${this.field.dynamic_data}"`);
			}

			// edit text
			if( _.has(this.field, 'edit_tag') ) {
				classes.push(`content-txt-edit`);
				data_attr.push(`data-edittag="${this.field['edit_tag']}"`);

				if( _.has(this.field, 'edit_tagchild') ){
					data_attr.push(`data-edittagchild="${this.field['edit_tagchild']}"`);
				}
				if( _.has(this.field, 'edit_position') ){
					data_attr.push(`data-tagposition="${this.field['edit_position']}"`);
				}
				if( _.has(this.field, 'edit_tag_var') ){
					data_attr.push(`data-edittagvar="${this.field['edit_tag_var']}"`);
				}
			}

			if( _.has(this.field, 'id') ) {
				data_attr.push(`data-id="${this.field.id}"`);
				if( !_.has(this.field, 'selector') ) {
					classes.push(this.field.id);
					data_attr.push(`data-name="${this.field.id}"`);
					this.field['input_class'] = 'preview-'+this.field.id+'input';
				}
			}

			// style
			if( _.has(this.field, 'id') && _.has(this.field, 'selector') ) {

				let style_tag, style_name;

				if( _.has(this.field, 'selector') ) {
					style_tag = this.field.selector;
					style_name = this.field.style;
					classes.push('object-css-input');
				}

				if( _.has(this.field, 'key') ) {
					data_attr.push(`data-name="${this.field.key}"`);
				}else{
					data_attr.push(`data-name="${style_name}"`);
				}

				data_attr.push(`data-csspath="${style_tag}"`);
				if( _.has(this.field, 'dynamic_value') ) {
					data_attr.push(`data-dynamicvalue="${this.field.dynamic_value}"`);
				}
				classes.push('inline-style-input');
				classes.push( style_name.replace('--', '') );
				//this.field['input_class'] = 'preview-'+style_name+'input';
			}

			if( _.has(this.field, 'key') ) {
				classes.push(this.field.key);
			}

			if( _.has(this.field, 'data_attr') ) {
				data_attr.push(this.field['data_attr']);
			}

			if( _.has(this.field, 're_render') ) {
				classes.push('re_render');
			}

			if( _.has(this.field, 're_render_if') ) {
				let explode_rrf = this.field['re_render_if'].split('|');
				if( explode_rrf.length == 2 ) {
					data_attr.push(`data-retype="${explode_rrf[0]}"`);
					data_attr.push(`data-reelement="${explode_rrf[1]}"`);
				}
				classes.push('re_render_if')
			}

			if( _.has(window, field_name) ) {

				if( field_name == 'mfn_field_header' ) {
					if( this.tab_content ) this.html += `</div>`;
					this.tab_content = true;
					this.html += `<div data-id="${f}" class="mfn-component-wrapper mfn-component-wrapper-${f}">`;
				}

				this.field['obj_val'] = '';
				this.field['placeholder'] = [];
				this.assignPlaceholders();

				// set placeholder
				if( _.has(this.field, 'id') ) {

					//if( this.field.id == 'css_advanced_margin' ) { console.log('************************'); console.log(this.field['placeholder']); }

					if( Array.isArray(this.field['placeholder']) && this.field['placeholder'].length > 0 ) {
						// simple field
						let the_last = this.field['placeholder'].at(-1);
						if( _.has( the_last, 'val' ) ) {
							this.field['obj_val'] = the_last['val'];
							if( the_last['type'] == 'placeholder' || the_last['type'] == 'class' || ( _.has(the_last, 'screen') && the_last['screen'] != screen )) classes.push('mfn-placeholder-inherited-'+the_last['type']);
						}
						let allow_reset = this.field['placeholder'].filter( x => x.type == 'class' || x.type == 'rwd' || x.type == 'val' ).length;
						if( edited_item.uid != 'pageoptions' && allow_reset > 1 && (this.field.type == 'switch' || this.field.type == 'select') ) {
							label_classes.push('form-label-wrapper');
							label += '<a href="#" class="mfn-option-btn mfn-option-blank mfn-reset-val-icon"><span class="mfn-icon mfn-icon-reset"></span></a>'; // reset has to be the last one
						}
					}else if( _.has(this.field, 'key') && typeof this.field.placeholder == 'object' && _.has(this.field.placeholder, this.field.key) ) {
						let the_last = this.field['placeholder'][this.field.key].at(-1);
						if( _.has( the_last, 'val' ) ) {
							this.field['obj_val'] = the_last['val'];
							if( the_last['type'] == 'placeholder' || the_last['type'] == 'class' || ( _.has(the_last, 'screen') && the_last['screen'] != screen )) classes.push('mfn-placeholder-inherited-'+the_last['type']);
						}
						let allow_reset = this.field['placeholder'][this.field.key].filter( x => x.type == 'class' || x.type == 'rwd' || x.type == 'val' ).length;
						if( edited_item.uid != 'pageoptions' && allow_reset > 1 && (this.field.type == 'switch' || this.field.type == 'select') ) {
							label_classes.push('form-label-wrapper');
							label += '<a href="#" class="mfn-option-btn mfn-option-blank mfn-reset-val-icon"><span class="mfn-icon mfn-icon-reset"></span></a>'; // reset has to be the last one
						}
					}
				}

				if( this.formrow ) this.html += `<div ${id} class="${classes.join(' ')}" ${data_attr.join(' ')}>`;

				this.html += `
					${ field_name != 'mfn_field_info' && field_name != 'mfn_field_header' && field_name != 'mfn_field_subheader' && field_name != 'mfn_field_helper' ? `<label class="${label_classes.join(' ')}">${label}</label>` : '' }
					${ _.has(this.field, 'desc') ? `<div class="desc-group"><span class="description">${this.field.desc}</span></div>` : '' }
				`;
 	
				// responsive
				if( _.has(this.field, 'responsive') ){
					field_data_attr.push(`data-rwd="${screen}"`);
					field_wrapper_classes.push(`mfn_field_${screen}`);
					this.html += `<div ${field_data_attr.join(' ')} class="mfn_input_wrapper ${field_wrapper_classes.join(' ')}">${window[field_name](this.field, screen)}</div>`;
				}else{
					this.html += `<div ${field_data_attr.join(' ')} class="mfn_input_wrapper ${field_wrapper_classes.join(' ')}">${window[field_name](this.field, 'desktop')}</div>`;
				}

			}else{
				this.html += ` * ${field_name}`;
			}

			if( this.formrow ) this.html += `</div>`;

		}

	}

	assignPlaceholders() {

		if( edited_item.jsclass != 'themeoption' && _.has(this.field, 'responsive') ) {

			if(  _.has(mfn.placeholders[this.field.id], 'desktop') ) {
				if( Array.isArray(mfn.placeholders[this.field.id]['desktop']) ){
					Array.prototype.push.apply(this.field['placeholder'], mfn.placeholders[this.field.id]['desktop']);
				}else if( typeof mfn.placeholders[this.field.id]['desktop'] == 'object' ){
					if( Array.isArray(this.field['placeholder']) ) this.field['placeholder'] = {};
					let that = this;
					_.map( mfn.placeholders[this.field.id]['desktop'], function(v, k) {
						if( !_.has(that.field['placeholder'], k) ) that.field['placeholder'][k] = [];
						Array.prototype.push.apply(that.field['placeholder'][k], v);
					});
				}				
			}

			if( screen == 'laptop' || screen == 'tablet' || screen == 'mobile' ) {
				if( _.has(mfn.placeholders[this.field.id], 'laptop') ) {
					if( Array.isArray(mfn.placeholders[this.field.id]['laptop']) ){
						Array.prototype.push.apply(this.field['placeholder'], mfn.placeholders[this.field.id]['laptop']);
					}else if( typeof mfn.placeholders[this.field.id]['laptop'] == 'object' ){
						if( Array.isArray(this.field['placeholder']) ) this.field['placeholder'] = {};
						let that = this;
						_.map( mfn.placeholders[this.field.id]['laptop'], function(v, k) {
							if( !_.has(that.field['placeholder'], k) ) that.field['placeholder'][k] = [];
							Array.prototype.push.apply(that.field['placeholder'][k], v);
						});
					}
				}				
			}

			if( screen == 'tablet' || screen == 'mobile' ) {
				if( _.has(mfn.placeholders[this.field.id], 'tablet') ) {
					if( Array.isArray(mfn.placeholders[this.field.id]['tablet']) ){
						Array.prototype.push.apply(this.field['placeholder'], mfn.placeholders[this.field.id]['tablet']);
					}else if( typeof mfn.placeholders[this.field.id]['tablet'] == 'object' ){
						if( Array.isArray(this.field['placeholder']) ) this.field['placeholder'] = {};
						let that = this;
						_.map( mfn.placeholders[this.field.id]['tablet'], function(v, k) {
							if( !_.has(that.field['placeholder'], k) ) that.field['placeholder'][k] = [];
							Array.prototype.push.apply(that.field['placeholder'][k], v);
						});
					}
				}				
			}

			if( screen == 'mobile' ) {

				if( _.has(mfn.placeholders[this.field.id], 'mobile') ) {
					if( Array.isArray(mfn.placeholders[this.field.id]['mobile']) ) {
						Array.prototype.push.apply(this.field['placeholder'], mfn.placeholders[this.field.id]['mobile']);
					}else if( typeof mfn.placeholders[this.field.id]['mobile'] == 'object' ){
						if( Array.isArray(this.field['placeholder']) ) this.field['placeholder'] = {};
						let that = this;
						_.map( mfn.placeholders[this.field.id]['mobile'], function(v, k) {

							if( !_.has(that.field['placeholder'], k) ) {
								that.field['placeholder'][k] = [];
								Array.prototype.push.apply(that.field['placeholder'][k], v);
							}else{
								Array.prototype.push.apply(that.field['placeholder'][k], v);
							}

							/*if( !_.has(that.field['placeholder'], k) ) that.field['placeholder'][k] = [];
							Array.prototype.push.apply(that.field['placeholder'][k], v);*/
							
							
						});
					}
				}
			}

		}else{

			if( Array.isArray(mfn.placeholders[this.field.id]) && mfn.placeholders[this.field.id].length > 0 ) {
				this.field['placeholder'] = mfn.placeholders[this.field.id];
			}else if( typeof mfn.placeholders[this.field.id] == 'object' ){
				if( Array.isArray(this.field['placeholder']) ) this.field['placeholder'] = {};
				let that = this;
				_.map( mfn.placeholders[this.field.id], function(v, k) {
					if( !_.has(that.field['placeholder'], k) ) that.field['placeholder'][k] = [];
					Array.prototype.push.apply(that.field['placeholder'][k], v);
				});
			}
		}

	}

	values( obj, field, is_class = false ) {

		let type = is_class ? 'class' : 'val';
		let tmp_object = _.has(obj, 'attr') ? obj['attr'] : obj;
		if( !_.has(tmp_object, field.id) && !_.has(field, 'std') ) return;

		// values top
		if( _.has(field, 'std') && _.has(field, 'required') ) {

			let this_std = _.has( field, 'std_'+mfn.builder_type ) ? field['std_'+mfn.builder_type] : field.std;

			if( obj.jsclass != 'themeoption' && _.has(field, 'responsive') /*&& !_.has(tmp_object, field.id)*/ ) {
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = {};
				if( typeof this_std == 'object' ) {
					//if( !_.has(tmp_object, field.id) ) {
						let std_obj = JSON.parse( JSON.stringify( this_std ) );
						//mfn.placeholders[field.id] = std_obj;
						_.map( std_obj, (obj, k) => {
							if( !_.has(mfn.placeholders[field.id], k) ) mfn.placeholders[field.id][k] = obj;
						});
					//}
				}else if( !_.has(mfn.placeholders[field.id], 'desktop') /*|| !_.has(tmp_object, field.id)*/ ) {
					mfn.placeholders[field.id]['desktop'] = [];
					mfn.placeholders[field.id]['desktop'].push({'val': this_std, 'type': 'rwd', 'screen': 'desktop'});
				}
			}else if(!_.has(tmp_object, field.id)) {
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = [];
				mfn.placeholders[field.id].push({'val': this_std, 'type': 'std'});
			}

		}

		/**
		 * exceptions
		 * */ 

		if( field.id == 'sticky' && (screen != 'desktop' && ( !_.has(edited_item, 'attr') || !_.has(edited_item['attr'], field.id) || !_.has(edited_item['attr'][field.id], screen))) ) return;
		if( field.id == 'sticky_offset' && (screen != 'desktop' && ( !_.has(edited_item, 'attr') || !_.has(edited_item['attr'], field.id) || !_.has(edited_item['attr'][field.id], screen))) ) return;

		/**
		 * / exceptions
		 * */ 
		
		if( obj.jsclass != 'themeoption' && _.has(field, 'responsive') ) {
			if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = {};
			if( type != 'class' ) type = 'rwd';
			
			_.map(this.devices, (device) => {
				if( _.has(tmp_object[field.id], 'val') && _.has(tmp_object[field.id]['val'], device) ) {
					if( typeof tmp_object[field.id]['val'][device] == 'object' ) {

						if( !_.has(mfn.placeholders[field.id], device) ) mfn.placeholders[field.id][device] = {};

						if( _.has(field, 'key') && _.has(tmp_object[field.id]['val'][device], field.key) ) {
							// typo field 
							if( !_.has(mfn.placeholders[field.id][device], field.key) ) mfn.placeholders[field.id][device][field.key] = [];
							mfn.placeholders[field.id][device][field.key].push({ 'val': tmp_object[field.id]['val'][device][field.key], 'type': type, 'screen': device });
						}else{
							// dimension field
							_.map(tmp_object[field.id]['val'][device], (dv, d) => {
								if( !_.has(mfn.placeholders[field.id][device], d) ) mfn.placeholders[field.id][device][d] = [];
								mfn.placeholders[field.id][device][d].push({ 'val': dv, 'type': type, 'screen': device });
							});
						}
					}else{
						if( !_.has(mfn.placeholders[field.id], device) || !Array.isArray(mfn.placeholders[field.id][device]) ) mfn.placeholders[field.id][device] = [];
						mfn.placeholders[field.id][device].push({ 'val': tmp_object[field.id]['val'][device], 'type': type, 'screen': device });
					}
				}else if( _.has(tmp_object[field.id], device) ) {

					if( !_.has(mfn.placeholders[field.id], device) ) mfn.placeholders[field.id][device] = [];
					if( typeof tmp_object[field.id][device] == 'object' ) {
						_.map(tmp_object[field.id][device], (dv, d) => {
							if( !_.has(mfn.placeholders[field.id][device], d) ) mfn.placeholders[field.id][device][d] = [];
							mfn.placeholders[field.id][device][d].push({ 'val': dv, 'type': type, 'screen': device });
						});
					}else{
						mfn.placeholders[field.id][device].push({'val': tmp_object[field.id][device], 'type': type, 'screen': device });
					}
				}
			});

		}else if( _.has(tmp_object[field.id], 'val') /*&& !_.isEmpty( tmp_object[field.id]['val'] )*/ ) {

			console.log({'test 1': tmp_object[field.id]['val'] });

			if( typeof tmp_object[field.id]['val'] == 'string' ) {
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = [];
				mfn.placeholders[field.id].push({ 'val': tmp_object[field.id]['val'], 'type': type });
			}else if( (field.id.includes('typography') || field.id.includes('gradient') || field.id.includes('_filter')) && typeof tmp_object[field.id]['val'] == 'object' ) {
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = {};

				if( _.has(field, 'key') && _.has(tmp_object[field.id]['val'], field.key) ){
					if( !_.has(mfn.placeholders[field.id], field.key) ) mfn.placeholders[field.id][field.key] = [];
					mfn.placeholders[field.id][field.key].push({ 'val': tmp_object[field.id]['val'][field.key], 'type': type });
				}
			}
		}else if( _.has(tmp_object, field.id) ) {

			if( _.has(tmp_object[field.id], screen) ) {
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = {};
				if( !_.has(mfn.placeholders[field.id], screen) ) mfn.placeholders[field.id][screen] = [];
				mfn.placeholders[field.id][screen].push({ 'val': tmp_object[field.id][screen], 'type': type });
			}else if( !_.isEmpty(tmp_object[field.id]) && typeof tmp_object[field.id] != 'undefined' && tmp_object[field.id] != null ){
				if( field.id.includes('typography') ) return;
				if( !_.has(mfn.placeholders, field.id) ) mfn.placeholders[field.id] = [];
				mfn.placeholders[field.id].push({ 'val': tmp_object[field.id], 'type': type });
			}
		
		}
	}

}