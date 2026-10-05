<?php
/**
 * Custom post type: Portfolio
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Mfn_Post_Type_Portfolio' ) ) {
	class Mfn_Post_Type_Portfolio extends Mfn_Post_Type
	{

		/**
		 * Mfn_Post_Type_Portfolio constructor
		 */

		public function __construct()
		{
			parent::__construct();

			// fires after WordPress has finished loading but before any headers are sent
			add_action('init', array($this, 'register'));

			// applied to the list of columns to print on the manage posts screen for a custom post type
			add_filter('manage_edit-portfolio_columns', array($this, 'add_columns'));

			// allows to add or remove (unset) custom columns to the list post/page/custom post type pages
			add_action('manage_posts_custom_column', array($this, 'custom_column'));

			// admin only methods

			if( is_admin() ){
				$this->fields = $this->set_fields();
				$this->builder = new Mfn_Builder_Admin();


			}

			add_action( 'admin_init', array($this, 'mfn_add_portfolio_cat_image') );

		}

		/**
		 * Set post type fields
		*/

		public function mfn_add_portfolio_cat_image(){

			add_action( 'portfolio-types_edit_form_fields', array( $this, 'mfn_edit_portfolio_cat_image_field' ) );
			add_action( 'portfolio-types_add_form_fields', array( $this, 'mfn_edit_portfolio_cat_image_field' ) );

			add_action( 'saved_portfolio-types', array( $this, 'mfn_portfolio_cat_save' ) );
			add_action( 'create_portfolio-types', array( $this, 'mfn_portfolio_cat_save' ) );
		}

		public function mfn_edit_portfolio_cat_image_field ($tag) {

			$current_value = '';

			wp_enqueue_media();

			if(isset( $tag->taxonomy )) {
				$current_value = !empty( get_term_meta($tag->term_id, 'thumbnail_id', true) ) ? get_term_meta($tag->term_id, 'thumbnail_id', true) : '';
			}

			$placeholder_url = get_theme_file_uri( '/muffin-options/svg/placeholders/image.svg' );

		    $field_label = 'Choose image';
		    $field_name = 'mfn_category_field_image';

		    if( isset( $tag->taxonomy )) { ?>
			<tr class="form-field mfn-tax-image">
		        <th valign="top" scope="row"><label for="mfn_tax_field"><?php echo $field_label; ?></label></th>
		        <?php $current_value = wp_get_attachment_url($current_value); ?>
				<td><input type="hidden" id="mfn_tax_field" value="<?php echo $current_value; ?>" name="<?php echo $field_name; ?>" class="<?php echo $field_name; ?>">
					<div class="mfn-custom-img-container">
					    <img data-src="<?php echo $placeholder_url; ?>" src="<?php if ( !empty($current_value) ) : echo $current_value; else: echo $placeholder_url; endif; ?>" alt="" style="max-width:100%;" />
						<a class="upload-custom-img button" href="#"><?php _e('Set category image') ?></a>
						<a class="delete-custom-img button <?php if ( ! $current_value ) { echo 'hidden'; } ?>" href="#"><?php _e('Remove image') ?></a>
					</div>
		        </td>
		    </tr>
		    <?php }else{ ?>
				<div class="form-field mfn-tax-image">
			        <label for="mfn_tax_field"><?php echo $field_label; ?></label>
			        <input type="hidden" id="mfn_tax_field" value="" name="<?php echo $field_name; ?>" class="<?php echo $field_name; ?>">
					<div class="mfn-custom-img-container">
					    <img data-src="<?php echo $placeholder_url; ?>" src="<?php if ( !empty($current_value) ) : echo $current_value; else: echo $placeholder_url; endif; ?>" alt="" style="max-width:100%;" />
						<a class="upload-custom-img button <?php if ( $current_value  ) { echo 'hidden'; } ?>" href="#"><?php _e('Set custom image') ?></a>
						<a class="delete-custom-img button <?php if ( ! $current_value ) { echo 'hidden'; } ?>" href="#"><?php _e('Remove image') ?></a>
					</div>
		    	</div>
			<?php }
		}

		public function mfn_portfolio_cat_save($term_id) {

			if( !empty( $_POST['mfn_category_field_image']) ){
				update_term_meta( $term_id, 'thumbnail_id', $_POST['mfn_category_field_image'] );
			}else if( !empty( get_term_meta($term_id, 'thumbnail_id', true) ) ){
				delete_term_meta($term_id, 'thumbnail_id');
			}

		}

		/**
		 * Set post type fields
		 */

		public function set_fields(){

			return array(

				'id' => 'mfn-meta-portfolio',
				'title' => esc_html__('Portfolio Options', 'mfn-opts'),
				'page' => 'portfolio',
				'fields' => array(

				array(
      				'type' => 'header',
  					'title' => __('Custom template', 'mfn-opts'),
  				),

      			array(
  					'id' => 'mfn_single-post_template',
  					'type' => 'select',
  					'title' => __('Post template', 'mfn-opts'),
  					'php_options' => mfna_templates('single-portfolio'),
  					'js_options' => 'single_portfolio_tmpl',
  				),

				array(
						'type' => 'header',
  					'title' => __('Header & Footer', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn_header_template',
  					'type' => 'select',
  					'title' => __('Custom Header', 'mfn-opts'),
  					'php_options' => mfna_templates('header'),
  					'js_options' => 'headers',
  				),

  				array(
  					'id' => 'mfn_footer_template',
  					'type' => 'select',
  					'title' => __('Custom Footer', 'mfn-opts'),
  					'php_options' => mfna_templates('footer'),
  					'js_options' => 'footers',
  				),

  				array(
  					'title' => __('Popup', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn_popup_included',
  					'type' => 'select',
  					'title' => __('Popup', 'mfn-opts'),
  					'desc' => __('Choose popup to display', 'mfn-opts'),
  					'php_options' => mfna_templates('popup'),
  					'js_options' => 'popups',
  				),

					// layout

  				array(
  					'type' => 'header',
  					'title' => __('Layout', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-post-hide-content',
  					'type' => 'switch',
  					'title' => __('The content', 'mfn-opts'),
  					'desc' => __('The content from the WordPress editor', 'mfn-opts'),
  					'options'	=> array(
							'1' => __('Hide', 'mfn-opts'),
							'0' => __('Show', 'mfn-opts'),
						),
  					'required' => true,
  					'std' => '0'
  				),

					array(
						'id' => 'mfn-post-layout',
						'type' => 'radio_img',
						'title' => __('Layout', 'mfn-opts'),
						'desc' => __('Full width sections works only without sidebars', 'mfn-opts'),
						'options' => array(
							'no-sidebar' => __('Full width', 'mfn-opts'),
							'left-sidebar' => __('Left sidebar', 'mfn-opts'),
							'right-sidebar' => __('Right sidebar', 'mfn-opts'),
							'both-sidebars' => __('Both sidebars', 'mfn-opts'),
							'offcanvas-sidebar' => __('Off-canvas sidebar', 'mfn-opts'),
						),
						'required' => true,
						'std' => 'no-sidebar',
						'alias' => 'sidebar',
						'class' => 'form-content-full-width small',
					),

  				array(
  					'id' => 'mfn-post-sidebar',
  					'type' => 'select',
  					'title' => __('Sidebar', 'mfn-opts'),
  					'desc' => __('Shows only if layout with sidebar is selected', 'mfn-opts'),
  					'php_options' => mfn_opts_get('sidebars'),
  					'js_options' => 'sidebars',
  				),

  				array(
  					'id' => 'mfn-post-sidebar2',
  					'type' => 'select',
  					'title' => __('Sidebar 2nd', 'mfn-opts'),
  					'desc' => __('Shows only if layout with both sidebars is selected', 'mfn-opts'),
  					'php_options' => mfn_opts_get('sidebars'),
  					'js_options' => 'sidebars',
  				),

					array(
      			'id' => 'mfn-post-template',
      			'type' => 'radio_img',
      			'title' => __('Style', 'mfn-opts'),
						'desc' => __('(former name: Template)', 'mfn-opts'),
      			'options' => array(
      				'' => __('- Default -', 'mfn-opts'),
      				'builder' => __('Builder', 'mfn-opts'),
      				'intro' => __('Intro Header', 'mfn-opts'),
      			),
						'alias' => 'post-style',
						'class' => 'form-content-full-width small',
      		),

					// media

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
  					'id' => 'mfn-post-slider',
  					'type' => 'select',
  					'title' => __('Slider Revolution', 'mfn-opts'),
  					'php_options' => Mfn_Builder_Helper::get_sliders('rev'),
  					'js_options' => 'rev_slider',
  				),

  				array(
  					'id' => 'mfn-post-slider-layer',
  					'type' => 'select',
  					'title' => __('Layer Slider', 'mfn-opts'),
  					'php_options' => Mfn_Builder_Helper::get_sliders('layer'),
  					'js_options' => 'layer_slider',
  				),

					array(
      			'id' => 'mfn-post-header-bg',
      			'type' => 'upload',
      			'title' => __('Header image', 'mfn-opts'),
      		),

					array(
  					'id' => 'mfn-post-subheader-image',
  					'type' => 'upload',
  					'title' => __('Subheader image', 'mfn-opts'),
  				),

      		array(
      			'id' => 'mfn-post-video',
      			'type' => 'text',
      			'title' => __('Video ID', 'mfn-opts'),
      			'desc' => __('YouTube or Vimeo', 'mfn-opts'),
      		),

      		array(
      			'id' => 'mfn-post-video-mp4',
      			'type' => 'upload',
      			'title' => __('Video MP4', 'mfn-opts'),
      			'data' => 'video',
      		),

					// description

  				array(
  					'type' => 'header',
  					'title' => __('Description', 'mfn-opts'),
  				),

					array(
						'id' => 'mfn-post-client',
						'type' => 'text',
						'title' => __('Client', 'mfn-opts'),
					),

					array(
						'id' => 'mfn-post-link',
						'type' => 'text',
						'title' => __('Website', 'mfn-opts'),
					),

					array(
						'id' => 'mfn-post-task',
						'type' => 'text',
						'title' => __('Task', 'mfn-opts'),
					),

					// options

					array(
						'type' => 'header',
						'title' => __('Options', 'mfn-opts'),
					),

					array(
  					'id' => 'mfn-post-full-width',
  					'type' => 'switch',
  					'title' => __('Full width', 'mfn-opts'),
  					'desc' => __('Set page to full width ignoring <a target="_blank" href="admin.php?page=be-options#general">Site width</a> option. Works for Layout Full width only.', 'mfn-opts'),
  					'options'	=> array(
						'0' => __('Disable', 'mfn-opts'),
						'site' => __('Enable', 'mfn-opts'),
						'content' => __('Content only', 'mfn-opts'),
					),
  					'required' => true,
  					'std' => '0'
  				),

					array(
  					'id' => 'mfn-post-hide-title',
  					'type' => 'switch',
  					'title' => __('Subheader', 'mfn-opts'),
  					'options'	=> array(
							'' => __('Default', 'mfn-opts'),
						'1' => __('Hide', 'mfn-opts'),
						'0' => __('Show', 'mfn-opts'),
					),
  					'required' => true,
  					'std' => ''
  				),

					array(
  					'id' => 'mfn-post-remove-padding',
  					'type' => 'switch',
  					'title' => __('Content top padding', 'mfn-opts'),
  					'options' => array(
						'' => __('Default', 'mfn-opts'),
						'1' => __('Hide', 'mfn-opts'),
						'0' => __('Show', 'mfn-opts'),
					),
  					'required' => true,
  					'std' => '0'
  				),

					array(
						'id' => 'mfn-post-slider-header',
						'type' => 'switch',
						'title' => __('Slider location', 'mfn-opts'),
						'options' => array(
							'0' => __('Content', 'mfn-opts'),
							'1' => __('Header', 'mfn-opts'),
						),
						'required' => true,
						'std' => '0'
					),

					array(
						'id' => 'mfn-post-custom-layout',
						'type' => 'select',
						'title' => __('Custom layout', 'mfn-opts'),
						'desc' => __('Custom Layout overwrites Theme Options', 'mfn-opts'),
						'php_options' => $this->get_layouts(),
						'js_options' => 'layouts',
					),

					// advanced

					array(
						'type' => 'header',
						'title' => __('Advanced', 'mfn-opts'),
					),

					array(
						'id' => 'mfn-post-bg',
						'type' => 'upload',
						'title' => __('Background image', 'mfn-opts'),
						'desc' => __('for Portfolio Layout: List', 'mfn-opts'),
					),

					array(
						'id' => 'mfn-post-bg-hover',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
						'desc' => __('for Portfolio Layout: List & Masonry Hover Details', 'mfn-opts'),
					),



					array(
						'id' => 'mfn-post-size',
						'type' => 'select',
						'title' => __('Item size', 'mfn-opts'),
						'desc' => __('for Portfolio Layout: Masonry Flat', 'mfn-opts'),
						'options' => array(
							'' => __('Default', 'mfn-opts'),
							'wide' => __('Wide', 'mfn-opts'),
							'tall' => __('Tall', 'mfn-opts'),
							'wide tall'	=> __('Big', 'mfn-opts'),
						),
					),

					// intro

					array(
						'type' => 'header',
						'title' => __('Intro header', 'mfn-opts'),
					),

					array(
      			'id' => 'mfn-post-intro',
      			'type' => 'checkbox',
      			'title' => __('Options', 'mfn-opts'),
      			'desc' => __('for Template: Intro', 'mfn-opts'),
      			'options' => array(
      				'light' => __('Light image, dark text', 'mfn-opts'),
      				'full-screen' => __('Full Screen', 'mfn-opts'),
      				'parallax' => __('Parallax', 'mfn-opts'),
      				'cover' => __('Background size: Cover<span>enabled by default in parallax</span>', 'mfn-opts'),
      			),
      		),

					// seo

  				array(
  					'type' => 'header',
  					'title' => __('SEO', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-meta-seo-title',
  					'type' => 'text',
  					'title' => __('Title', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-meta-seo-description',
  					'type' => 'text',
  					'title' => __('Description', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-meta-seo-keywords',
  					'type' => 'text',
  					'title' => __('Keywords', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-meta-seo-og-image',
  					'type' => 'upload',
  					'title' => __('Open Graph image', 'mfn-opts'),
  					'desc' => __('Facebook share image', 'mfn-opts'),
  				),

					// custom css

					array(
						'type' => 'header',
						'role_restricted' => true,
						'title' => __('Custom CSS', 'mfn-opts'),
					),

					array(
						'id' => 'mfn-post-css',
						'type' => 'textarea',
						'role_restricted' => true,
						'title' => __('Custom CSS', 'mfn-opts'),
						'desc' => __('Custom CSS code for this portfolio project', 'mfn-opts'),
						'class' => 'form-content-full-width',
						'cm' => 'css',
					),

					array(
  					'type' => 'header',
  					'role_restricted' => true,
  					'title' => __('Custom JS', 'mfn-opts'),
  				),

					array(
  					'id' => 'mfn-post-js',
  					'type' => 'textarea',
  					'role_restricted' => true,
  					'title' => __('Custom JS', 'mfn-opts'),
  					'desc' => __('Custom JS code for this page. Use with &lt;script&gt; tag', 'mfn-opts'),
  					'class' => 'form-content-full-width',
						'cm' => 'js',
	  			),

				),
			);

		}

		/**
		 * Create ULR friendly slug
		 */

		public function slugify($string) {
	    $string = strtolower(trim($string));
	    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
	    return trim($string, '-');
		}

		/**
		 * Register new post type and related taxonomy
		 */

		public function register()
		{
			$slug_single = $this->slugify(mfn_opts_get('portfolio-slug', 'portfolio-item'));
			$slug_category = $this->slugify(mfn_opts_get('portfolio-tax', 'portfolio-types'));

			// slug sanitization

			if( in_array( $slug_single, ['portfolio', 'portfolio-types'] ) ){
				$slug_single = 'portfolio-item';
			}

			if( in_array( $slug_category, ['portfolio'] ) ){
				$slug_category = 'portfolio-types';
			}

			if( $slug_single === $slug_category ){
				$slug_category = 'portfolio-types';
			}

			// end: slug sanitization

			$labels = array(
				'name' => esc_html__('Portfolio', 'mfn-opts'),
				'singular_name' => esc_html__('Portfolio item', 'mfn-opts'),
				'add_new' => esc_html__('Add New', 'mfn-opts'),
				'add_new_item' => esc_html__('Add New Portfolio item', 'mfn-opts'),
				'edit_item' => esc_html__('Edit Portfolio item', 'mfn-opts'),
				'new_item' => esc_html__('New Portfolio item', 'mfn-opts'),
				'view_item' => esc_html__('View Portfolio item', 'mfn-opts'),
				'search_items' => esc_html__('Search Portfolio items', 'mfn-opts'),
				'not_found' => esc_html__('No portfolio items found', 'mfn-opts'),
				'not_found_in_trash' => esc_html__('No portfolio items found in Trash', 'mfn-opts'),
				'parent_item_colon' => '',
			  );

			$args = array(
				'labels' => $labels,
				'menu_icon' => 'dashicons-portfolio',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => null,
				'show_in_rest' => true,
				'rewrite' => array(
					'slug' => $slug_single,
				),
				'supports' => array('author', 'comments', 'custom-fields', 'editor', 'excerpt', 'page-attributes', 'thumbnail', 'title'),
			);

			register_post_type('portfolio', $args);

			register_taxonomy('portfolio-types', 'portfolio', array(
				'label' => esc_html__('Portfolio categories', 'mfn-opts'),
				'hierarchical' => true,
				'query_var' => true,
				'show_in_rest' => true,
				'rewrite' => array(
					'slug' => $slug_category,
				),
			));
		}

		/**
		 * Add new columns to posts screen
		 */

		public function add_columns($columns)
		{
			$newcolumns = array(
				'cb' => '<input type="checkbox" />',
				'portfolio_thumbnail' => esc_html__('Thumbnail', 'mfn-opts'),
				'title' => esc_html__('Title', 'mfn-opts'),
				'portfolio_types' => esc_html__('Categories', 'mfn-opts'),
				'portfolio_order' => esc_html__('Order', 'mfn-opts'),
  		);
			$columns = array_merge($newcolumns, $columns);

			return $columns;
		}

		/**
		 * Custom column on posts screen
		 */

		public function custom_column($column)
		{
			global $post;

			switch ($column) {
				case 'portfolio_thumbnail':
					if (has_post_thumbnail()) {
						the_post_thumbnail('thumbnail');
					}
					break;
				case 'portfolio_types':
					echo get_the_term_list($post->ID, 'portfolio-types', '', ', ', '');
					break;
				case 'portfolio_order':
					echo esc_attr($post->menu_order);
					break;
			}
		}

	}
}

new Mfn_Post_Type_Portfolio();
