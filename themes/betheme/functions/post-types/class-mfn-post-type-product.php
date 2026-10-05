<?php
/**
 * Custom post type: Template
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (! class_exists('Mfn_Post_Type_Product')) {
	class Mfn_Post_Type_Product extends Mfn_Post_Type
	{

		/**
		 * Mfn_Post_Type_Product constructor
		 */

		public function __construct()
		{
			parent::__construct();

			// fires after WordPress has finished loading but before any headers are sent
			//add_action('init', array($this, 'defaults'));

			// admin only methods

			if( is_admin() ){
				$this->fields = $this->set_fields();
				$this->builder = new Mfn_Builder_Admin();

			}

		}

		/*public function defaults() {
			remove_post_type_support( 'product', 'editor' );
		}*/

		/**
		 * Set post type fields
		 */

		public function set_fields(){

			$ref = parse_url(wp_get_referer());

			$type = 'default';

			return array(
				'id' => 'mfn-meta-product',
				'title' => esc_html__('Product Options', 'mfn-opts'),
				'page' => 'product',
				'fields' => array(

					array(
	  					'type' => 'header',
	  					'title' => __('Media', 'mfn-opts'),
	  				),

					array(
	  					'id' => '_thumbnail_id',
	  					'type' => 'upload',
	  					'admin_hidden' => true,
	  					'title' => __('Featured image', 'mfn-opts'),
	  					'js_options' => 'featured_image',
	  					'format' => 'id'
	  				),

					array(
	  					'type' => 'header',
	  					'title' => __('Header & Footer', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_header_template',
	  					'type' => 'select',
	  					'title' => __('Custom Header Template', 'mfn-opts'),
	  					'desc' => __('To overwrite template set with conditions in <a target="_blank" href="edit.php?post_type=template&tab=header">Templates</a> section, please select appropriate template from dropdown select. Afterwards, please reload the page to refresh the options.', 'mfn-opts'),
	  					'php_options' => mfna_templates('header'),
	  					'js_options' => 'headers',
	  				),

	  				array(
	  					'id' => 'mfn_footer_template',
	  					'type' => 'select',
	  					'title' => __('Custom Footer Template', 'mfn-opts'),
	  					'desc' => __('To overwrite template set with conditions in <a target="_blank" href="edit.php?post_type=template&tab=footer">Templates</a> section, please select appropriate template from dropdown select. Afterwards, please reload the page to refresh the options.', 'mfn-opts'),
	  					'php_options' => mfna_templates('footer'),
	  					'js_options' => 'footers',
	  				),

	  				array(
	  					'type' => 'header',
	  					'title' => __('Template', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_single_product_template',
	  					'type' => 'select',
	  					'title' => __('Custom Single Product Template', 'mfn-opts'),
	  					'php_options' => mfna_templates('single-product'),
	  					'js_options' => 'singleproducts',
	  				),

	  				array(
	  					'type' => 'header',
	  					'title' => __('Labels', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_product_labels',
	  					'type' => 'text',
	  					'title' => __('Extra label', 'mfn-opts'),
	  					'placeholder' =>  __('Special offer!', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_product_labels_color',
	  					'type' => 'color',
	  					'title' => __('Extra label color', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_product_labels_bg_color',
	  					'type' => 'color',
	  					'title' => __('Extra label background', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_product_labels_border_color',
	  					'type' => 'color',
	  					'title' => __('Extra label border color', 'mfn-opts'),
	  				),
					
					array(
	  					'id' => 'mfn-post-js',
	  					'type' => 'textarea',
	  					'title' => __('Custom JS', 'mfn-opts'),
	  					'desc' => __('Custom JS code for this page. Use with &lt;script&gt; tag', 'mfn-opts'),
	  					'class' => 'form-content-full-width',
	  					'role_restricted' => true,
						'cm' => 'js',
	  				),

				),
			);
		}


	}
}

new Mfn_Post_Type_Product();
