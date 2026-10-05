function mfn_field_filters(field) {
	let html = '';
	let value = field['obj_val'];

	if( value.length ){
		_.map( value, (obj, i) => {html += mfn_field_filters_render(obj, i) }).join('');
		
	}

    html += '<a href="#" class="hotspot_add_new multifield_add_new mfn-btn">Add new</a>';
	
	return html;
}


function mfn_field_filters_render(obj, i) {

	let html = '';

	let used_fields = [

		{
			'id': 'filter_by',
			'attr_id': 'filters'+'_type'+obj.hash,
			'on_change': 'object',
			'type': 'switch',
			're_render': true,
			'title': 'Filter by',
			'std': '',
			'value': _.has(obj, 'filter_by') ? obj['filter_by'] : '',
			'options': {
				'': 'Taxonomy',
				'postmeta': 'Postmeta',
				'search': 'Search'
			}
		},

		{
			'id': 'field_type',
			'attr_id': 'filters_field_type_'+obj.hash,
			'on_change': 'object',
			'type': 'select',
			're_render': true,
			'title': 'Field type',
			'condition': [
				'AND',
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "isnt",
					val: 'search'
				},
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "isnt",
					val: 'rate'
				}
			],
			'value': _.has(obj, 'field_type') ? obj['field_type'] : '',
			'options': {
				'': 'Input',
				'select': 'Select',
				'switch': 'Switch',
				'checkbox': 'Checkbox',
				'radio': 'Radio',
				'color': 'Color',
				'image': 'Image',
				'slider': 'Slider',
				'range-slider': 'Range slider',
			},
			'std': '',
		},


		{
			'id': 'field_slider_layout',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['slider', 'range-slider']
			},
			'on_change': 'object',
			'type': 'select',
			're_render': true,
			'title': 'Slider layout',
			'std': '',
			'value': _.has(obj, 'field_slider_layout') ? obj['field_slider_layout'] : '',
			'options': {
				'': 'Inline',
				'input-above': 'Input above',
				'input-below': 'Input below',
				'hidden-inputs': 'Hidden input',
				'hidden-slider': 'Hidden slider',
			}
		},

		{
			'id': 'field_slider_handler_tooltip',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['slider', 'range-slider']
			},
			'on_change': 'object',
			'type': 'select',
			're_render': true,
			'title': 'Slider handler tooltip',
			'std': '',
			'value': _.has(obj, 'field_slider_handler_tooltip') ? obj['field_slider_handler_tooltip'] : '',
			'options': {
				'': 'Hidden',
				'hover': 'Visible on hover',
				'visible': 'Always visible',
			}
		},

		{
			'id': 'field_slider_placeholder_input',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['slider']
			},
			'on_change': 'object',
			'type': 'text',
			're_render': true,
			'title': 'Input placeholder',
			'value': _.has(obj, 'field_slider_placeholder_input') ? obj['field_slider_placeholder_input'] : '',
		},

		{
			'id': 'field_slider_placeholder_min',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['range-slider']
			},
			'on_change': 'object',
			'type': 'text',
			're_render': true,
			'title': 'From placeholder',
			'value': _.has(obj, 'field_slider_placeholder_min') ? obj['field_slider_placeholder_min'] : '',
		},

		{
			'id': 'field_slider_placeholder_max',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['range-slider']
			},
			'on_change': 'object',
			'type': 'text',
			're_render': true,
			'title': 'Up to placeholder',
			'value': _.has(obj, 'field_slider_placeholder_max') ? obj['field_slider_placeholder_max'] : '',
		},

		{
			'id': 'field_slider_placeholder_floating',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['range-slider', 'slider']
			},
			'on_change': 'object',
			'type': 'switcher',
			'option': '1',
			'title': 'Floating placeholder',
			'value': _.has(obj, 'field_slider_placeholder_floating') ? obj['field_slider_placeholder_floating'] : '',
		},

		{
			'id': 'tax_filter',
			'condition': {
				id: 'filters'+'_type'+obj.hash,
				opt: "is",
				val: ""
			},
			'on_change': 'object',
			'type': 'select',
			're_render': true,
			'title': 'Taxonomy',
			'std': _.has(obj, 'tax_filter') ? obj['tax_filter'] : '',
			'value': _.has(obj, 'tax_filter') ? obj['tax_filter'] : '',
			//'mfn_options': 'taxonomies',
			'post_tax': true,
		},

		/*
		{
			'id': 'tax_filter_key',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['color', 'image'] 
			'on_change': 'object',
			'type': 'text',
			're_render': true,
			'title': 'Custom termmeta',
			'desc': 'Field name',
			'value': _.has(obj, 'tax_filter_key') ? obj['tax_filter_key'] : '',
		},

		not sure it is required

		*/

		{
			'id': 'postmeta_filter_key',
			'attr_id': 'filters_postmeta_filter_key_'+obj.hash,
			'condition': {
				id: 'filters'+'_type'+obj.hash,
				opt: "is",
				val: "postmeta"
			},
			'on_change': 'object',
			'type': 'select_ajax',
			'select_type': 'postmeta',
			'std': '_price',
			'title': 'Custom postmeta',
			'desc': 'Postmeta "meta_key" you want to filter by',
			'value': _.has(obj, 'postmeta_filter_key') ? obj['postmeta_filter_key'] : '',
		},

		{
			'id': 'field_slider_min',
			'condition': [
				'AND',
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "is",
					val: "postmeta"
				},
				{
					id: 'filters_field_type_'+obj.hash,
					opt: "is",
					val: ['range-slider', 'slider']
				},
				{
					id: 'filters_postmeta_filter_key_'+obj.hash,
					opt: "isnt",
					val: '_price'
				}
			],
			'on_change': 'object',
			'type': 'text',
			'title': 'Min slider value',
			'desc': 'Field name',
			'std': 0,
			'value': _.has(obj, 'field_slider_min') ? obj['field_slider_min'] : '',
		},

		{
			'id': 'field_slider_max',
			'condition': [
				'AND',
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "is",
					val: "postmeta"
				},
				{
					id: 'filters_field_type_'+obj.hash,
					opt: "is",
					val: ['range-slider', 'slider']
				},
				{
					id: 'filters_postmeta_filter_key_'+obj.hash,
					opt: "isnt",
					val: '_price'
				}
			],
			'on_change': 'object',
			'type': 'text',
			'title': 'Max slider value',
			'desc': 'Field name',
			'std': 999,
			'value': _.has(obj, 'field_slider_max') ? obj['field_slider_max'] : '',
		},

		{
			'id': 'postmeta_filter_key_options',
			'condition': [
				'AND',
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "is",
					val: "postmeta"
				},
				{
					id: 'filters_field_type_'+obj.hash,
					opt: "is",
					val: ['select', 'switch', 'color', 'image', 'radio', 'checkbox']
				}
			],
			'on_change': 'object',
			'type': 'textarea',
			're_render': true,
			'title': 'Filter options',
			'desc': 'Paste options you want to filter by',
			'std': 'red : Red',
			'value': _.has(obj, 'postmeta_filter_key_options') ? obj['postmeta_filter_key_options'] : '',
		},

		{
			'id': 'field_logic',
			'condition': [
				'AND',
				{
					id: 'filters'+'_type'+obj.hash,
					opt: "is",
					val: "postmeta"
				},
				{
					id: 'filters_field_type_'+obj.hash,
					opt: "is",
					val: ['input', 'slider']
				}
			],
			'on_change': 'object',
			'type': 'select',
			're_render': true,
			'title': 'Logic',
			'std': '',
			'value': _.has(obj, 'field_logic') ? obj['field_logic'] : '',
			'options': {
				'': 'Equals',
				'gt': 'Greater than',
				'lt': 'Less than',
				'between': 'Between'
			}
		},

		{
			'id': 'label',
			'on_change': 'object',
			'type': 'text',
			're_render': true,
			'title': 'Label',
			'std': '',
			'condition': {
				id: 'label_visibility',
				opt: "isnt",
				val: "hidden"
			},
			'value': _.has(obj, 'label') ? obj['label'] : ''
		},

		{
			'id': 'content',
			'on_change': 'object',
			're_render': true,
			'type': 'textarea',
			'rows': 8,
			'dynamic_data': 'content',
			'title': 'Desc',
			'value': _.has(obj, 'content') ? obj['content'] : '',
		},

		{
			'id': 'load_more_limit',
			'on_change': 'object',
			'condition': {
				id: 'filters_field_type_'+obj.hash,
				opt: "is",
				val: ['checkbox', 'radio', 'color', 'image']
			},
			'type': 'text',
			'param': 'number',
			're_render': true,
			'title': 'Load more limit',
			'std': '10',
			'value': _.has(obj, 'load_more_limit') ? obj['load_more_limit'] : ''
		},

	];


	if( mfn.current_post_type == 'product' ) used_fields[0]['options']['rate'] = 'Rate';

	html += '<div id="'+obj.hash+'" class="mfn-multifield-point mfn-multifield-form-'+obj.hash+' '+( _.has(obj, 'filter_by') && obj['filter_by'] == 'postmeta' ? 'mfn-multifield-point-postmeta' : 'mfn-multifield-point-tax' )+'">';
	html += '<div class="mfn-multifield-point-tab-header mfn-multifield-point-header-'+obj.hash+'">';
	html += `<div class="mfn-multifield-point-header-left"><a class="mfn-option-btn mfn-option-blank mfn-tab-toggle" href="#"><span class="mfn-icon mfn-icon-arrow-down"></span></a>
		<h6>${ obj.label ? obj.label : `Filter ${i+1}` }</h6>
		</div>
		<div class="mfn-multifield-point-header-right">
		<a class="mfn-option-btn mfn-option-blue mfn-sort-handle" href="#"><span class="mfn-icon mfn-icon-sort"></span></a>
		<a class="mfn-option-btn mfn-option-blue mfn-tab-delete" href="#"><span class="mfn-icon mfn-icon-delete"></span></a>
		</div>`;
	html += '</div>';
	html += '<div class="mfn-multifield-form-wrapper mfn-multifield-form-wrapper-'+obj.hash+'">';
	const mfn_form_filters = new MfnForm( used_fields );
    html += mfn_form_filters.render();
    html += '</div>';
    html += '</div>';

	return html;

}