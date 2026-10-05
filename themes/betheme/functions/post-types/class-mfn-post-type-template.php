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

if (! class_exists('Mfn_Post_Type_Template')) {
	class Mfn_Post_Type_Template extends Mfn_Post_Type
	{
		/**
		 * Mfn_Post_Type_Template constructor
		 */

		public function __construct(){

			if ( ! class_exists('Sitepress') ) {

				if( !apply_filters('bebuilder_access', false) ) {
					return false;
				}

				if( !current_user_can('editor') && !current_user_can('administrator') ){
					return false;
				}

			}

			parent::__construct();

			// fires after WordPress has finished loading but before any headers are sent
			add_action('init', array($this, 'register'));

			// admin only methods

			if( is_admin() ) {
				$this->builder = new Mfn_Builder_Admin();
				$this->fields = $this->set_fields();

				$post_id = false;
				$tmpl_type = $this->getReferer();

				if( !empty($_GET['post']) ) {
					$post_id = $_GET['post'];
					$tmpl_type = get_post_meta($post_id, 'mfn_template_type', true);
				}

				if( in_array($tmpl_type, array('header', 'footer', 'megamenu', 'sidemenu')) ){
					$this->fields = $this->set_bebuilder_only($post_id);
				}

				add_filter( 'admin_body_class', array($this, 'adminClass') );
				add_filter('post_class', array( $this, 'mfn_set_row_post_class'), 10, 3);

				add_filter('views_edit-template', array( $this, 'list_tabs_wrapper' ));
				add_action('pre_get_posts', array( $this, 'filter_by_tab'));

  				add_filter( 'manage_template_posts_columns', array( $this, 'mfn_set_template_columns' ) );
    			add_action( 'manage_template_posts_custom_column' , array( $this, 'mfn_template_column'), 10, 2 );

				add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'mfn_menu_item_icon_field') );
				add_action( 'wp_update_nav_menu_item', array( $this, 'mfn_save_menu_item_icon'), 10, 2 );

				add_action('admin_footer-nav-menus.php', array( $this, 'mfn_append_icons_modal') );
				add_action( "admin_print_scripts-nav-menus.php", array($this, 'mfn_admin_menus') );

				if( $GLOBALS['pagenow'] == 'post-new.php' ){
					add_filter('admin_footer_text', array($this, 'templateStartPopup'));
				}

				if( $GLOBALS['pagenow'] == 'edit.php' ){
					add_action('all_admin_notices', array($this, 'dashboardHeader'));
					add_action('admin_enqueue_scripts', array($this, 'dashboardEnqueue'));
				}

				//add_action('admin_footer-post-new.php', array($this, 'templateStartPopup'));
			}
		}

		public function dashboardHeader() {

			$post_type = filter_input(INPUT_GET, 'post_type');
	    	$screen = get_current_screen();

		    if( $screen->id == 'edit-template' && !empty($post_type) && $post_type == 'template' ){
				echo '<div class="mfn-ui mfn-templates" data-page="templates">';
					echo '<input type="hidden" name="mfn-builder-nonce" value="'. wp_create_nonce( 'mfn-builder-nonce' ) .'"/>';
					include_once get_theme_file_path('/functions/admin/templates/parts/header.php');
				echo '</div>';
		    }

		}

		public function dashboardEnqueue() {

			$post_type = filter_input(INPUT_GET, 'post_type');
	    	$screen = get_current_screen();

		    if( $screen->id == 'edit-template' && !empty($post_type) && $post_type == 'template' ) {
				wp_enqueue_style( 'mfn-dashboard', get_theme_file_uri('/functions/admin/assets/dashboard.css'), array(), MFN_THEME_VERSION );
				wp_enqueue_script('mfn-dashboard', get_theme_file_uri('/functions/admin/assets/dashboard.js'), false, MFN_THEME_VERSION, true);
		    }

		}

		public function templateStartPopup() {

			$post_type = filter_input(INPUT_GET, 'post_type');
	    $screen = get_current_screen();

	    if( $screen->id == 'template' && !empty($post_type) && $post_type == 'template' ){
	    	echo '<div class="mfn-ui">';
					require_once(get_theme_file_path('/visual-builder/partials/template-type-modal.php'));
				echo '</div>';
	    }
		}

		public function mfn_set_row_post_class($classes, $class, $post_id){
		    if (!is_admin()) return $classes;

		    $screen = get_current_screen();

		    if (!empty($screen->post_type) && 'template' != $screen->post_type && 'edit' != $screen->base) return $classes;

		    $tmpl_type = get_post_meta($post_id, 'mfn_template_type', true);
		    $tmpl_type_label = $tmpl_type;

			if( in_array($tmpl_type_label, array('single-product', 'shop-archive', 'archive-product', 'cart', 'checkout', 'thanks')) ){
				$tmpl_type_label = 'woocommerce';
			}else if( in_array($tmpl_type_label, array('single-post', 'blog', 'archive-post')) ){
				$tmpl_type_label = 'blog';
			}else if( in_array($tmpl_type_label, array('section', 'wrap')) ){
				$tmpl_type_label = 'global';
			}else if( in_array($tmpl_type_label, array('single-portfolio', 'portfolio', 'archive-portfolio')) ){
				$tmpl_type_label = 'portfolio';
			}

		    $classes[] = 'mfn-tmpl-type-'.$tmpl_type_label;

		    return $classes;
		}

		public function adminClass($classes){

			$tmpl_type = false;

			if( !empty($_GET['post']) ){
				$tmpl_type = get_post_meta($_GET['post'], 'mfn_template_type', true);
			}else{
				$tmpl_type = $this->getReferer();
			}

			$screen = get_current_screen();

			if( empty($tmpl_type) ) $tmpl_type = 'default';

			if( strpos($classes, 'mfn-template-builder') === false ) $classes .= ' mfn-template-builder mfn-template-builder-'.$tmpl_type;

			if( $screen->post_type == 'template' && $screen->base == 'edit' ) $classes .= ' mfn-admin-template-list';

			return $classes;
		}

		public function mfn_append_icons_modal() {
			echo '<div class="mfn-ui">';
				require_once(get_theme_file_path('/visual-builder/partials/modal-icons.php'));
			echo '</div>';
		}

		public function mfn_admin_menus(){
			wp_enqueue_script('mfnadmin', get_theme_file_uri('/functions/admin/assets/admin.js'), array('jquery'), time(), true);
			wp_enqueue_media();
		}

		/**
		 * HEADER TEMPLATE: Icon field
		 * */

		public function mfn_menu_item_icon_field($item_id) {

			$menu_item_icon = get_post_meta( $item_id, 'mfn_menu_item_icon', true );
			$menu_item_icon_img = get_post_meta( $item_id, 'mfn_menu_item_icon_img', true );
			$menu_item_mm = get_post_meta( $item_id, 'mfn_menu_item_megamenu', true );
			$menu_item_mm_display = get_post_meta( $item_id, 'mfn_menu_item_megamenu_display', true );
			$mfn_mega_menus = mfna_templates('megamenu');

			echo '<div class="mfn-ui"><div class="mfn-form">';

		    echo '<div class="field-mfn-icon description description-wide">
		    	Item icon<br>
			    <div class="form-group browse-icon has-addons has-addons-prepend '.( $menu_item_icon ? "not-empty" : "empty" ).'">
			    	<div class="form-addon-prepend">
						<a href="#" class="mfn-button-upload">
							<span class="label">
								<span class="text">'. esc_html__( 'Browse', 'mfn-opts' ) .'</span>
								<i class="'. esc_attr( $menu_item_icon ) .'"></i>
							</span>
						</a>
					</div>
					<div class="form-control has-icon has-icon-right">
						<input type="text" name="mfn_menu_item_icon['.$item_id.']" class="widefat mfn-form-control mfn-field-value mfn-form-input preview-icon" id="mfn-menu-item-icon-'.$item_id.'" value="'.$menu_item_icon.'" />
						<a class="mfn-option-btn mfn-button-delete" title="Delete" href="#"><span class="mfn-icon mfn-icon-delete"></span></a>
					</div>
				</div>
			</div>';

			echo '<div class="field-mfn-icon-img description description-wide">
		    	Item image icon<br>
			    <div class="form-group browse-image has-addons has-addons-append '.( $menu_item_icon_img ? "not-empty" : "empty" ).'">
					<div class="form-control has-icon has-icon-right">
						<input type="text" name="mfn_menu_item_icon_img['.$item_id.']" class="widefat mfn-form-control mfn-field-value mfn-form-input preview-icon" id="mfn-menu-item-icon-'.$item_id.'" value="'.$menu_item_icon_img.'" />
						<a class="mfn-option-btn mfn-button-delete" title="Delete" href="#"><span class="mfn-icon mfn-icon-delete"></span></a>
					</div>
					<div class="form-addon-append">
						<a href="#" class="mfn-button-upload"><span class="label">'. esc_html__( 'Browse', 'mfn-opts' ) .'</span></a>
					</div>

					<div class="selected-image">
						<img src="'. esc_attr( $menu_item_icon_img ) .'" alt="" />
					</div>
				</div>
			</div>';

			echo '<div class="field-mfn-mm description description-wide">
		    	Mega menu<br>
			    <select id="mfn_menu_item_megamenu-'.$item_id.'" name="mfn_menu_item_megamenu['.$item_id.']" class="widefat mfn-form-control mfn-field-value mfn-form-input">';
			    if( is_iterable($mfn_mega_menus) ){
			    	foreach ($mfn_mega_menus as $m=>$mm) {
			    		echo '<option '. ( $menu_item_mm && $menu_item_mm == $m ? "selected" : "" ) .' value="'.$m.'">'.$mm.'</option>';
			    	}
			    }
			echo '</select>

			<span class="description">'.esc_html__( 'Custom Mega Menu\'s works with Header Templates only', 'mfn-opts' ).'</span>

			</div>';

			echo '<div class="field-mfn-mm-display description description-wide">
		    	Mega menu display<br>
			    <select id="mfn_menu_item_megamenu-display-'.$item_id.'" name="mfn_menu_item_megamenu_display['.$item_id.']" class="widefat mfn-form-control mfn-field-value mfn-form-input">';
			    echo '<option '. ( empty($menu_item_mm_display) ? "selected" : "" ) .' value=""> - Default - </option>';
			    echo '<option '. ( !empty($menu_item_mm_display) && $menu_item_mm_display == '1' ? "selected" : "" ) .' value="1">Open on Front Page desktop</option>';
			    echo '<option '. ( !empty($menu_item_mm_display) && $menu_item_mm_display == '2' ? "selected" : "" ) .' value="2">Always Open on desktop</option>';
			echo '</select>

			</div>';

			echo '</div></div>';

		}

		/**
		 * HEADER TEMPLATE: Save icon field
		 * */

		function mfn_save_menu_item_icon( $menu_id, $menu_item_db_id ) {

			if ( !empty( $_POST['mfn_menu_item_icon'][$menu_item_db_id]  ) ) {
				$sanitized_data = sanitize_text_field( $_POST['mfn_menu_item_icon'][$menu_item_db_id] );
				update_post_meta( $menu_item_db_id, 'mfn_menu_item_icon', $sanitized_data );
			} else {
				delete_post_meta( $menu_item_db_id, 'mfn_menu_item_icon' );
			}

			if ( !empty( $_POST['mfn_menu_item_icon_img'][$menu_item_db_id] ) ) {
				$sanitized_data = sanitize_text_field( $_POST['mfn_menu_item_icon_img'][$menu_item_db_id] );
				update_post_meta( $menu_item_db_id, 'mfn_menu_item_icon_img', $sanitized_data );
			} else {
				delete_post_meta( $menu_item_db_id, 'mfn_menu_item_icon_img' );
			}

			if ( !empty( $_POST['mfn_menu_item_megamenu_display'][$menu_item_db_id] ) ) {
				$sanitized_data = sanitize_text_field( $_POST['mfn_menu_item_megamenu_display'][$menu_item_db_id] );
				update_post_meta( $menu_item_db_id, 'mfn_menu_item_megamenu_display', $sanitized_data );
			} else {
				delete_post_meta( $menu_item_db_id, 'mfn_menu_item_megamenu_display' );
			}

			if ( !empty( $_POST['mfn_menu_item_megamenu'][$menu_item_db_id] ) ) {
				$sanitized_data = sanitize_text_field( $_POST['mfn_menu_item_megamenu'][$menu_item_db_id] );

				if( $sanitized_data == 'enabled' ){
					update_post_meta($menu_item_db_id, 'menu-item-mfn-megamenu', 'enabled'); // automatic mega menu
				}else{
					delete_post_meta( $menu_item_db_id, 'menu-item-mfn-megamenu' );
				}

				update_post_meta( $menu_item_db_id, 'mfn_menu_item_megamenu', $sanitized_data );

			} else {
				delete_post_meta( $menu_item_db_id, 'mfn_menu_item_megamenu' );
				delete_post_meta( $menu_item_db_id, 'menu-item-mfn-megamenu' );
			}
		}

		/**
		 * Templates list view display conditions
		 */

		public function mfn_set_template_columns($columns) {

			$columns['tmpltype'] = esc_html__('Type', 'mfn-opts');
			$columns['conditions'] = esc_html__('Conditions', 'mfn-opts');

    	return $columns;
		}

		public function mfn_template_column($column, $post_id) {
			$tmpl_type = get_post_meta($post_id, 'mfn_template_type', true);
			$tmpl_type_label = $tmpl_type;

			if($column == 'tmpltype') {

				if( in_array($tmpl_type_label, array('single-product', 'archive-product', 'shop-archive', 'cart', 'checkout', 'thanks')) ) {
					$tmpl_type_label = 'woocommerce';
				}else if( in_array($tmpl_type_label, array('single-post', 'blog', 'archive-post')) ) {
					$tmpl_type_label = 'blog';
				}else if( in_array($tmpl_type_label, array('section', 'wrap')) ) {
					$tmpl_type_label = 'global';
				}else if( in_array($tmpl_type_label, array('single-portfolio', 'portfolio', 'archive-portfolio')) ) {
					$tmpl_type_label = 'portfolio';
				}

				echo '<span class="mfn-label-table-list mfn-label-'.$tmpl_type_label.'">';
				if( $tmpl_type == 'default' ) $tmpl_type = 'Page template';
				if( $tmpl_type == 'sidemenu' ) $tmpl_type = 'Sidebar menu';
				echo ucfirst(str_replace('-', ' ', $tmpl_type)).'</span>';
			}elseif($column == 'conditions') {
				$conditions = (array) json_decode( get_post_meta($post_id, 'mfn_template_conditions', true) );
				//print_r($conditions);
				if(!empty($conditions) && count($conditions) > 0) {
					foreach($conditions as $c=>$con) {
						if($con->rule == 'include'){ echo '<span class="mfn-tmpl-conditions-incude">+ '; }else{ echo '<span class="mfn-tmpl-conditions-exclude">- '; }

						//print_r($con);

						if($con->var == 'everywhere') {
							echo 'Entire Site';
						}elseif($con->var == 'archives') {
							if( empty($con->archives) ) {
								echo 'All archives';
							}else{

								if( strpos($con->archives, ':') !== false){
									$expl = explode(':', $con->archives);
									$pt = get_post_type_object( $expl[0] );
									$term = get_term( $expl[1] );
								}elseif( !empty($con->archives) ){
									$pt = get_post_type_object( $con->archives );
								}

								echo 'Archive: '.$pt->label;

								if( !empty($term->name) ) echo '/'.$term->name;

							}
						}elseif($con->var == 'singular'){
							if( empty($con->singular) ){
								echo 'All singulars';
							}else{

								if( strpos($con->singular, ':') !== false){
									$expl = explode(':', $con->singular);
									$pt = get_post_type_object( $expl[0] );
									$term = get_term( $expl[1] );
								}elseif( !empty($con->singular) && $con->singular == 'front-page' ){
									echo 'Front Page</span><br>';
									continue;
								}elseif( !empty($con->singular) ){
									$pt = get_post_type_object( $con->singular );
								}

								echo 'Singular: '.$pt->label;

								if( !empty($term->name) ) echo '/'.$term->name;

							}
						}elseif($con->var == 'shop' || $con->var == 'archive-product'){
							if( get_post_meta($post_id, 'mfn_template_type', true) == 'single-product' ){
								echo 'All products';
							}else{
								echo 'Shop';
							}
						}elseif($con->var == 'productcategory' || $con->var == 'product_cat'){
							$hlpr_pc = isset( $con->productcategory ) ? $con->productcategory : $con->product_cat;
							if($hlpr_pc == 'all'){
								echo 'All categories';
							}else{
								$term = get_term_by('term_id', $hlpr_pc, 'product_cat');
								echo 'Category: '.$term->name;
							}
						}elseif($con->var == 'producttag' ||$con->var == 'product_tag'){
							$hlpr_pt = isset( $con->producttag ) ? $con->producttag : $con->product_tag;
							if($hlpr_pt == 'all'){
								echo 'All tags';
							}else{
								$term = get_term_by('term_id', $hlpr_pt, 'product_tag');
								echo 'Tag: '.$term->name;
							}
						}elseif($con->var == 'all'){
							if( $tmpl_type == 'blog' || $tmpl_type == 'archive-post' ){
								echo 'Entire blog';
							}elseif( $tmpl_type == 'portfolio' || $tmpl_type == 'archive-portfolio' ){
								echo 'Entire portfolio';
							}else{
								if( strpos($tmpl_type, 'archive-') !== false ){
									echo 'All archives';
								}else{
									echo 'All singulars';
								}

							}
						}elseif($con->var == 'category'){
							if( !empty($con->category) && $con->category == 'all' ){
								echo 'All categories';
							}else{
								$term = get_term_by('term_id', $con->category, 'category');
								if( !empty($term->name) ) echo 'Category: '.$term->name;
							}
						}elseif($con->var == 'post_tag'){
							if( !empty($con->post_tag) && $con->post_tag == 'all' ){
								echo 'All tags';
							}else{
								$term = get_term_by('term_id', $con->post_tag, 'post_tag');
								if( !empty($term->name) ) echo 'Tag: '.$term->name;
							}
						}elseif($con->var == 'portfolio-types'){
							if( !empty($con->{'portfolio-types'}) && $con->{'portfolio-types'} == 'all' ){
								echo 'All categories';
							}else{
								$term = get_term_by('term_id', $con->{'portfolio-types'}, 'portfolio-types');
								if( !empty($term->name) ) echo 'Category: '.$term->name;
							}
						}elseif($con->var == 'other'){
							if($con->other == 'search-page'){
								echo 'Search page';
							}
						}elseif($con->var == 'products-search') {
							echo 'Products search page';
						}else{
							echo $con->var;
							if( isset( $con->{$con->var} ) ) {
								$term = get_term( $con->{$con->var}, $con->var );
								if ( ! is_wp_error( $term ) ) echo ': '.$term->name;
							}
						}
						echo '</span><br>';
					}
				}else if( function_exists('is_woocommerce') && in_array($tmpl_type, array('thanks', 'checkout', 'cart', 'search')) ) {
					if( !empty( get_option('mfn_'.$tmpl_type.'_template_used') ) && get_option('mfn_'.$tmpl_type.'_template_used') == $post_id ){
						echo '<span class="mfn-tmpl-conditions-incude">Currently in use</span>';
					}else{
						echo '<span class="mfn-tmpl-conditions-na">n/a</span>';
					}
				}else{
					echo '<span class="mfn-tmpl-conditions-na">n/a</span>';
				}
			}
		}

		/**
		 * Set post type fields
		 */

		public function set_fields(){

			$type = $this->getReferer();

			$template_types = array(
				'default' => 'Page template',
				'section' => 'Sections template',
				'wrap' => 'Wraps template',
			);

			if(function_exists('is_woocommerce')){
				$template_types['shop-archive'] = 'Shop archive';
				$template_types['archive-product'] = 'Shop archive';
				$template_types['single-product'] = 'Single product';
				$template_types['cart'] = 'Cart';
				$template_types['checkout'] = 'Checkout';
				$template_types['thanks'] = 'Thank you';
			}

			$template_types['popup'] = 'Popup';
			$template_types['header'] = 'Header';
			$template_types['megamenu'] = 'Mega menu';
			$template_types['sidemenu'] = 'Sidebar menu';
			$template_types['single-post'] = 'Single post';
			$template_types['blog'] = 'Blog';
			$template_types['archive-post'] = 'Blog';
			$template_types['portfolio'] = 'Portfolio';
			$template_types['archive-portfolio'] = 'Portfolio';
			$template_types['single-portfolio'] = 'Single portfolio';
			$template_types['footer'] = 'Footer';
			$template_types['search'] = 'Search';

			$cpt_s = Mfn_Builder_Helper::get_post_types( array('public' => true, '_builtin' => false, 'skip_mfn_cpt' => true) );

			if( count($cpt_s) > 0 ){
				foreach( $cpt_s as $cpt ){
					$singular = 'single-'.$cpt->name;
					$archive = 'archive-'.$cpt->name;

					$template_types[$singular] = 'Single '.$cpt->labels->singular_name;
					$template_types[$archive] = 'Archive of '.$cpt->labels->name;
				}
			}

			return array(

				'id' => 'mfn-meta-template',
				'title' => esc_html__('Template Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

				array(
  					'id' => 'mfn_template_type',
  					'type' => 'select',
  					'class' => 'mfn_template_type mfn-hidden-field',
  					'title' => __('Template type', 'mfn-opts'),
  					'options' => $template_types,
  					'std' => $type,
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

  				array(
  					'type' => 'header',
  					'class' => 'mfn-field-visible-shop-archive-tmpl',
  					'title' => __('Woocommerce', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn_woo_cat_desc',
  					'type' => 'switch',
  					'class' => 'mfn-field-visible-shop-archive-tmpl',
  					'title' => __('Category description', 'mfn-opts'),
  					'desc' => __('Category description before template content', 'mfn-opts'),
  					'options'	=> array(
							'1' => __('Show', 'mfn-opts'),
							'' => __('Hide', 'mfn-opts'),
						),
  					'required' => true,
  					'std' => ''
  				),

  				array(
  					'id' => 'mfn_woo_cat_desc_top',
  					'type' => 'switch',
  					'class' => 'mfn-field-visible-shop-archive-tmpl',
  					'title' => __('Category top content', 'mfn-opts'),
  					'desc' => __('Category Top Content before template content', 'mfn-opts'),
  					'options'	=> array(
							'' => __('Show', 'mfn-opts'),
							'1' => __('Hide', 'mfn-opts'),
						),
  					'required' => true,
  					'std' => ''
  				),

  				array(
  					'id' => 'mfn_woo_cat_desc_bottom',
  					'type' => 'switch',
  					'class' => 'mfn-field-visible-shop-archive-tmpl',
  					'title' => __('Category bottom content', 'mfn-opts'),
  					'desc' => __('Category Bottom Content after template content', 'mfn-opts'),
  					'options'	=> array(
							'' => __('Show', 'mfn-opts'),
							'1' => __('Hide', 'mfn-opts'),
						),
  					'required' => true,
  					'std' => ''
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
							'' => __('Use page options', 'mfn-opts'),
							'no-sidebar' => __('Full width', 'mfn-opts'),
							'left-sidebar' => __('Left sidebar', 'mfn-opts'),
							'right-sidebar' => __('Right sidebar', 'mfn-opts'),
							'both-sidebars' => __('Both sidebars', 'mfn-opts'),
							'offcanvas-sidebar' => __('Off-canvas sidebar', 'mfn-opts'),
						),
						'required' => true,
						'std' => mfn_opts_get('sidebar-layout'),
						'alias' => 'sidebar',
						'class' => 'form-content-full-width small',
					),

  				array(
  					'id' => 'mfn-post-sidebar',
  					'type' => 'select',
  					'title' => __('Sidebar', 'mfn-opts'),
  					'desc' => __('Shows only if layout with sidebar is selected', 'mfn-opts'),
						'php_options' => is_array(mfn_opts_get('sidebars')) ? array_merge(array( '' => __('-- Default --', 'mfn-opts')), mfn_opts_get('sidebars')) : array('' => __('-- Default --', 'mfn-opts')),
  					'js_options' => 'sidebars',
  				),

  				array(
  					'id' => 'mfn-post-sidebar2',
  					'type' => 'select',
  					'title' => __('Sidebar 2nd', 'mfn-opts'),
  					'desc' => __('Shows only if layout with both sidebars is selected', 'mfn-opts'),
						'php_options' => is_array(mfn_opts_get('sidebars')) ? array_merge(array( '' => __('-- Default --', 'mfn-opts')), mfn_opts_get('sidebars')) : array('' => __('-- Default --', 'mfn-opts')),
  					'js_options' => 'sidebars',
  				),

					// media

  				array(
  					'type' => 'header',
  					'title' => __('Media', 'mfn-opts'),
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
  					'id' => 'mfn-post-slider-shortcode',
  					'type' => 'text',
  					'title' => __('Slider shortcode', 'mfn-opts'),
  					'desc' => __('Paste slider shortcode if you use other slider plugin', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-post-subheader-image',
  					'type' => 'upload',
  					'title' => __('Subheader image', 'mfn-opts'),
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
						'1' => __('Hide', 'mfn-opts'),
						'0' => __('Show', 'mfn-opts'),
					),
  					'required' => true,
  					'std' => '0'
  				),

  				array(
  					'id' => 'mfn-post-remove-padding',
  					'type' => 'switch',
  					'title' => __('Content top padding', 'mfn-opts'),
  					'options' => array(
						'1' => __('Hide', 'mfn-opts'),
						'0' => __('Show', 'mfn-opts'),
					),
  					'required' => true,
  					'std' => '0'
  				),

  				array(
  					'id' => 'mfn-post-custom-layout',
  					'type' => 'select',
  					'title' => __('Custom layout', 'mfn-opts'),
  					'desc' => __('Custom layout overwrites Theme Options', 'mfn-opts'),
  					'php_options' => $this->get_layouts(),
  					'js_options' => 'layouts',
  				),

  				array(
  					'id' => 'mfn-post-menu',
  					'type' => 'select',
  					'title' => __('Custom menu', 'mfn-opts'),
  					'desc' => __('Does not work with Split Menu', 'mfn-opts'),
  					'php_options' => mfna_menu(),
  					'js_options' => 'menus',
  				),

					// custom css

  				array(
  					'type' => 'header',
  					'title' => __('Custom CSS', 'mfn-opts'),
  				),

  				array(
  					'id' => 'mfn-post-css',
  					'type' => 'textarea',
  					'title' => __('Custom CSS', 'mfn-opts'),
  					'desc' => __('Custom CSS code for this page', 'mfn-opts'),
  					'class' => 'form-content-full-width',
						'cm' => 'css',
  				),

				),
			);
		}

		public function set_header_fields(){

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Header Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
						'type' => 'header',
	  					'title' => __('Default header', 'mfn-opts'),
	  				),

					array(
	  					'id' => 'header_position',
	  					'attr_id' => 'header_position',
	  					'type' => 'select',
	  					'title' => __('Position', 'mfn-opts'),
	  					'options' => array(
	  						'default' => __('Default', 'mfn-opts'),
	  						'absolute' => __('Absolute', 'mfn-opts'),
	  						'fixed' => __('Fixed', 'mfn-opts')
	  					),
	  					'std' => 'default',
  					),

  					array(
	  					'id' => 'body_offset_header',
	  					'type' => 'select',
	  					'condition' => array( 'id' => 'header_position', 'opt' => 'isnt', 'val' => 'default' ),
	  					'class' => 'body_offset_header',
	  					'title' => __('Body offset for header', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('No', 'mfn-opts'),
	  						'active' => __('Yes', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'header_width',
	  					'condition' => array( 'id' => 'header_position', 'opt' => 'is', 'val' => 'fixed' ),
	  					'type' => 'select',
	  					'title' => __('Width', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Full width', 'mfn-opts'),
	  						'inherited' => __('Inherited from Layout', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'header_content_on_submenu',
	  					'attr_id' => 'header_content_on_submenu',
	  					'type' => 'select',
	  					'title' => __('Content overlay', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default', 'mfn-opts'),
	  						'blur' => __('Blur', 'mfn-opts'),
	  						'gray' => __('Gray out', 'mfn-opts'),
	  						'overlay' => __('Overlay', 'mfn-opts')
	  					),
	  					'std' => '',
  					),

  					array(
						'type' => 'helper',
						'title' => __('Need help', 'mfn-opts'),
						'link' => 'https://support.muffingroup.com/video-tutorials/menu-content-overlay/',
						),

  					array(
						'id' => 'header_content_on_submenu_color',
						'condition' => array( 'id' => 'header_content_on_submenu', 'opt' => 'is', 'val' => 'blur,overlay' ),
						'type' => 'color',
						'title' => __('Overlay color', 'mfn-opts'),
						'std' => 'rgba(0,0,0,0.5)'
					),

					array(
						'id' => 'header_content_on_submenu_blur',
						'condition' => array( 'id' => 'header_content_on_submenu', 'opt' => 'is', 'val' => 'blur' ),
						'type' => 'sliderbar',
						'title' => __('Blur', 'mfn-opts'),
						'param' => array(
							'min' => '0',
							'max' => '20',
							'step' => '1',
						),
						'required' => true,
						'std' => '2'
					),

  					array(
  						'type' => 'header',
	  					'title' => __('Sticky header', 'mfn-opts'),
	  				),

  					array(
	  					'id' => 'header_sticky',
	  					'attr_id' => 'header_sticky',
	  					'type' => 'select',
	  					'title' => __('Status', 'mfn-opts'),
	  					'options' => array(
	  						'disabled' => __('Disabled', 'mfn-opts'),
	  						'enabled' => __('Enabled', 'mfn-opts'),
	  					),
	  					'required' => true,
	  					'std' => 'disabled',
  					),

  					array(
	  					'id' => 'header_sticky_width',
	  					'condition' => array( 'id' => 'header_sticky', 'opt' => 'is', 'val' => 'enabled' ),
	  					'type' => 'select',
	  					'title' => __('Width', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Full width', 'mfn-opts'),
	  						'inherited' => __('Inherited from Layout', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
						'type' => 'header',
	  					'title' => __('Mobile header', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'header_mobile',
	  					'attr_id' => 'header_mobile',
	  					'type' => 'select',
	  					'title' => __('Status', 'mfn-opts'),
	  					'options' => array(
	  						'disabled' => __('Disabled', 'mfn-opts'),
	  						'enabled' => __('Enabled', 'mfn-opts'),
	  					),
	  					'std' => 'disabled',
  					),

	  				array(
	  					'id' => 'mobile_header_position',
	  					'type' => 'select',
	  					'condition' => array( 'id' => 'header_mobile', 'opt' => 'is', 'val' => 'enabled' ),
	  					'title' => __('Position', 'mfn-opts'),
	  					'options' => array(
	  						'default' => __('Default', 'mfn-opts'),
	  						'absolute' => __('Absolute', 'mfn-opts'),
	  						'fixed' => __('Fixed', 'mfn-opts')
	  					),
	  					'required' => true,
	  					'std' => 'fixed',
  					),

  					array(
	  					'id' => 'mobile_body_offset_header',
	  					'type' => 'select',
	  					'condition' => array( 'id' => 'header_mobile', 'opt' => 'is', 'val' => 'enabled' ),
	  					'class' => 'mobile_body_offset_header',
	  					'title' => __('Body offset for header', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('No', 'mfn-opts'),
	  						'active' => __('Yes', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  				),
			);
		}

		public function set_popup_fields() {

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Popup Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
	  					'title' => __('Settings', 'mfn-opts'),
	  				),

					array(
	  					'id' => 'popup_position',
	  					'attr_id' => 'popup_position',
	  					'type' => 'radio_img',
	  					'title' => __('Position', 'mfn-opts'),
	  					'options' => array(
	  						'top-left' => __('Top Left', 'mfn-opts'),
	  						'top-center' => __('Top Center', 'mfn-opts'),
	  						'top-right' => __('Top Right', 'mfn-opts'),
	  						'center-left' => __('Center Left', 'mfn-opts'),
	  						'center' => __('Center', 'mfn-opts'),
	  						'center-right' => __('Center Right', 'mfn-opts'),
	  						'bottom-left' => __('Bottom Left', 'mfn-opts'),
	  						'bottom-center' => __('Bottom Center', 'mfn-opts'),
	  						'bottom-right' => __('Bottom Right', 'mfn-opts'),
	  					),
	  					'required' => true,
	  					'std' => 'center',
  					),

  					array(
	  					'id' => 'popup_display',
	  					'attr_id' => 'popup_display',
	  					'type' => 'select',
	  					'title' => __('Display trigger', 'mfn-opts'),
	  					'options' => array(
	  						'on-start' => __('On start', 'mfn-opts'),
	  						'start-delay' => __('On start with delay', 'mfn-opts'),
	  						'on-exit' => __('On exit', 'mfn-opts'),
	  						'on-scroll' => __('After scroll', 'mfn-opts'),
	  						'scroll-to-element' => __('After scroll to element', 'mfn-opts'),
	  						'on-click' => __('On click', 'mfn-opts'),
	  					),
	  					'required' => true,
	  					'std' => 'on-start',
  					),

					array(
						'condition' => array( 'id' => 'popup_display', 'opt' => 'is', 'val' => 'on-click' ),
						'type' => 'html',
						'condition' => array( 'id' => 'query_display', 'opt' => 'is', 'val' => 'slider' ),
						'html' => '<div class="mfn-form-row mfn-vb-formrow mfn-alert activeif activeif-popup_display conditionally-hide" data-condition="{&quot;id&quot;:&quot;popup_display&quot;,&quot;opt&quot;:&quot;is&quot;,&quot;val&quot;:&quot;on-click&quot;}"><div class="alert-content"><p>Use <span class="mfn-copytoclipboard" data-tooltip="Click to copy to clipboard" data-clipboard="#mfn-popup-template-postid" style="color: #72a5d8;">#mfn-popup-template-postid</span> to open this popup with an external button</p></div></div>',
					),

  					array(
	  					'id' => 'popup_display_delay',
	  					'condition' => array( 'id' => 'popup_display', 'opt' => 'is', 'val' => 'start-delay' ),
	  					'type' => 'text',
	  					'title' => __('Delay in seconds', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'after' => 's',
	  					'std' => '5',
  					),

  					array(
	  					'id' => 'popup_display_scroll',
	  					'condition' => array( 'id' => 'popup_display', 'opt' => 'is', 'val' => 'on-scroll' ),
	  					'type' => 'text',
	  					'title' => __('Scroll offset', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'after' => 'px',
	  					'std' => '100',
  					),

  					array(
	  					'id' => 'popup_display_scroll_element',
	  					'condition' => array( 'id' => 'popup_display', 'opt' => 'is', 'val' => 'scroll-to-element' ),
	  					'type' => 'text',
	  					'title' => __('Element ID or Class', 'mfn-opts'),
	  					'std' => '#elementID',
  					),

  					array(
	  					'id' => 'popup_entrance_animation',
	  					'type' => 'select',
	  					'title' => __('Entrance animation', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('None', 'mfn-opts'),
	  						'fade-in' => __('Fade-in', 'mfn-opts'),
	  						'zoom-in' => __('Zoom-in', 'mfn-opts'),
	  						'fade-in-up' => __('Fade-in Up', 'mfn-opts'),
	  						'fade-in-down' => __('Fade-in Down', 'mfn-opts'),
	  						'fade-in-left' => __('Fade-in Left', 'mfn-opts'),
	  						'fade-in-right' => __('Fade-in Right', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_display_visibility',
	  					'attr_id' => 'popup_display_visibility',
	  					'type' => 'select',
	  					'title' => __('Display rules', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Everytime', 'mfn-opts'),
	  						'one' => __('Only one time', 'mfn-opts'),
	  						'cookie-based' => __('Once every few days', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_display_visibility_cookie_days',
	  					'condition' => array( 'id' => 'popup_display_visibility', 'opt' => 'is', 'val' => 'cookie-based' ),
	  					'type' => 'text',
	  					'title' => __('Days until popup shows again', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'after' => 'days',
	  					'std' => '3',
  					),

  					array(
	  					'id' => 'popup_display_referer',
	  					'type' => 'select',
	  					'title' => __('Referer rules', 'mfn-opts'),
	  					'desc' => __('Display based on Referer', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default', 'mfn-opts'),
	  						'google' => __('Users from Google', 'mfn-opts'),
	  						'facebook' => __('Users from Facebook', 'mfn-opts'),
	  						'instagram' => __('Users from Instagram', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_hide',
	  					'attr_id' => 'popup_hide',
	  					'type' => 'select',
	  					'title' => __('Close rules', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default (on user close)', 'mfn-opts'),
	  						'automatically-delay' => __('Automatically after few seconds', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_hide_delay',
	  					'condition' => array( 'id' => 'popup_hide', 'opt' => 'is', 'val' => 'automatically-delay' ),
	  					'type' => 'text',
	  					'title' => __('Delay in seconds', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'after' => 's',
	  					'std' => '10',
  					),

  					array(
	  					'id' => 'popup_close_button_active',
	  					'attr_id' => 'popup_close_button_active',
	  					'type' => 'select',
	  					'title' => __('Close button', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Hidden', 'mfn-opts'),
	  						'1' => __('Visible', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

	  				array(
	  					'id' => 'popup_close_button_display',
	  					'attr_id' => 'popup_close_button_display',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'type' => 'select',
	  					'title' => __('Close button appear rules', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default', 'mfn-opts'),
	  						'delay' => __('Display with delay', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_close_button_display_delay',
	  					'condition' => array( 'id' => 'popup_close_button_display', 'opt' => 'is', 'val' => 'delay' ),
	  					'type' => 'text',
	  					'title' => __('Delay in seconds', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'after' => 's',
	  					'std' => '3',
  					),

  					array(
	  					'id' => 'popup_close_on_overlay_click',
	  					'type' => 'select',
	  					'title' => __('Close on overlay click', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Disable', 'mfn-opts'),
	  						'overlay-click' => __('Enable', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'popup_body_scroll',
	  					'type' => 'switch',
	  					'title' => __('Browser scroll', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Disable', 'mfn-opts'),
	  						'1' => __('Enable', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'title' => __('Design', 'mfn-opts'),
	  				),

	  				array(
						'class' => 'mfn-builder-subheader',
						'title' => __('Popup', 'mfn-opts'),
					),

	  				array(
	  					'id' => 'popup_width',
	  					'attr_id' => 'popup_width',
	  					'type' => 'select',
	  					'title' => __('Width', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default', 'mfn-opts'),
	  						'full-width' => __('Full width', 'mfn-opts'),
	  						'custom-width' => __('Custom', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid.mfn-popup-tmpl-custom-width .mfn-popup-tmpl-content:width',
	  					'id' => 'css_width',
						'selector' => '#mfn-popup-template-postid.mfn-popup-tmpl-custom-width .mfn-popup-tmpl-content',
						'style' => 'width',
						'type' => 'color',
	  					'condition' => array( 'id' => 'popup_width', 'opt' => 'is', 'val' => 'custom-width' ),
	  					'type' => 'text',
	  					'title' => __('Custom width', 'mfn-opts'),
						'default_unit' => 'px',
						'after' => 'px',
	  					'std' => '640px',
  					),

  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:height',
	  					'id' => 'css_height',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => 'height',
						'type' => 'color',
	  					'type' => 'text',
	  					'title' => __('Height', 'mfn-opts'),
						'default_unit' => 'px',
						'responsive' => true,
						'after' => 'px',
  					),

  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:--mfn-popup-tmpl-offset',
	  					'id' => 'css_offset',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => '--mfn-popup-tmpl-offset',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'title' => __('Offset', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'responsive' => true,
						'param' => array(
							'min' => '0',
							'max' => '200',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '30px',
  					),

  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content .mfn-popup-tmpl-content-wrapper:padding',
	  					'id' => 'css_padding',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content .mfn-popup-tmpl-content-wrapper',
						'style' => 'padding',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'title' => __('Padding', 'mfn-opts'),
	  					'responsive' => true,
	  					'param' => 'number',
	  					'param' => array(
							'min' => '0',
							'max' => '200',
							'step' => '1',
							'unit' => 'px',
						),
						'preview' => 'number',
						'after' => 'px',
	  					'std' => '30px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:border-radius',
	  					'id' => 'css_border_radius',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => 'border-radius',
	  					'type' => 'sliderbar',
	  					'title' => __('Border radius', 'mfn-opts'),
	  					'responsive' => true,
	  					'param' => 'number',
	  					'param' => array(
							'min' => '0',
							'max' => '300',
							'step' => '1',
							'unit' => 'px',
						),
						'preview' => 'number',
						'after' => 'px',
	  					'std' => '3px',
  					),


  					array(
						'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:background-color',
						'id' => 'css_bg_color',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'std' => '#fff',
						'title' => __('Background', 'mfn-opts'),
					),

					array(
		  				'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:box-shadow',
		  				'id' => 'css_box_shadow',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => 'box-shadow',
						'type' => 'color',
		  				'type' => 'box_shadow',
		  				'title' => __('Box shadow', 'mfn-opts'),
						'css_attr' => 'box-shadow',
		  			),

					array(
	  					'old_id' => 'style:#mfn-popup-template-postid:z-index',
	  					'id' => 'css_z_index',
						'selector' => '#mfn-popup-template-postid',
						'style' => 'z-index',
						'type' => 'color',
	  					'type' => 'text',
	  					'title' => __('z-index', 'mfn-opts'),
  					),

					array(
						'class' => 'mfn-builder-subheader',
						'title' => __('Overlay', 'mfn-opts'),
					),

					array(
						'old_id' => 'style:#mfn-popup-template-postid|before:background-color',
						'id' => 'css_overlay_bg_color',
						'selector' => '#mfn-popup-template-postid:before',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background overlay', 'mfn-opts'),
					),

					array(
						'id' => 'popup_overlay_blur',
						'type' => 'sliderbar',
						'title' => __('Blur', 'mfn-opts'),
						'param' => array(
							'min' => '0',
							'max' => '20',
							'step' => '1',
						),
						'std' => '0'
					),

					array(
						'class' => 'mfn-builder-subheader',
						'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
						'title' => __('Close button', 'mfn-opts'),
					),


					array(
	  					'id' => 'popup_close_button_align',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'type' => 'select',
	  					'title' => __('Align', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Right', 'mfn-opts'),
	  						'left' => __('Left', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:--mfn-exitbutton-size',
	  					'id' => 'css_exit_size',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => '--mfn-exitbutton-size',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Button size', 'mfn-opts'),
	  					'responsive' => true,
	  					'param' => 'number',
						'preview' => 'number',
						'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '30px',
  					),



  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .mfn-popup-tmpl-content:--mfn-exitbutton-font-size',
	  					'id' => 'css_exit_font_size',
						'selector' => '#mfn-popup-template-postid .mfn-popup-tmpl-content',
						'style' => '--mfn-exitbutton-font-size',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Icon size', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
						'preview' => 'number',
						'param' => array(
							'min' => '0',
							'max' => '50',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '16px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs:top',
	  					'id' => 'css_exit_top',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs',
						'style' => 'top',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Vertical offset', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
						'preview' => 'number',
						'param' => array(
							'min' => '-100',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '0px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs:--mfn-exitbutton-offset-horizontal',
	  					'id' => 'css_exit_offset',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs',
						'style' => '--mfn-exitbutton-offset-horizontal',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Horizontal offset', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'responsive' => true,
						'param' => array(
							'min' => '-100',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '0px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs:border-radius',
	  					'id' => 'css_exit_border_radius',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs',
						'style' => 'border-radius',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Border radius', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
	  					'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'preview' => 'number',
						'after' => 'px',
	  					'std' => '3px',
  					),


  					array(
						'type' => 'html',
						'condition' => array( 'id' => 'popup_close_button_active', 'opt' => 'is', 'val' => '1' ),
						'html' => '<div class="mfn-form-row mfn-sidebar-fields-tabs mfn-vb-formrow mfn-vb-mfnuidhere"><ul class="mfn-sft-nav"><li><a href="#normal" data-tab="normal">Normal</a></li><li><a href="#hover" data-tab="hover">Hover</a></li></ul><div class="mfn-sft mfn-sft-normal">',
					),

  					array(
						'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs:color',
						'id' => 'css_exit_color',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs',
						'style' => 'color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Color', 'mfn-opts'),
					),

  					array(
						'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs:background-color',
						'id' => 'css_exit_bg',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
					),

					array(
						'type' => 'html',
						'html' => '</div><div class="mfn-sft mfn-sft-hover mfn-tabs-fields">',
					),

					array(
						'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs|hover:color',
						'id' => 'css_exit_color_hover',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs:hover',
						'style' => 'color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Color', 'mfn-opts'),
					),

  					array(
						'old_id' => 'style:#mfn-popup-template-postid .exit-mfn-popup-abs|hover:background-color',
						'id' => 'css_exit_bg_hover',
						'selector' => '#mfn-popup-template-postid .exit-mfn-popup-abs:hover',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
					),


					array(
						'type' => 'html',
						'html' => '</div></div>',
					),


  				),
			);
		}


		public function set_sidemenu_fields() {

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Sidebar Menu Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
						'type' => 'header',
	  					'title' => __('Settings', 'mfn-opts'),
	  				),

	  				array(
	  					'id' => 'mfn_sidemenu_visibility',
	  					'attr_id' => 'mfn_sidemenu_visibility',
	  					'type' => 'select',
	  					'title' => __('Visibility ', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('On click', 'mfn-opts'),
	  						'always-visible' => __('Always visible', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'mfn_sidemenu_visibility_header',
	  					'condition' => array( 'id' => 'mfn_sidemenu_visibility', 'opt' => 'is', 'val' => 'always-visible' ),
	  					'type' => 'switch',
	  					'title' => __('Header visibility', 'mfn-opts'),
	  					'desc' => __('Optionally, hide the header template (above 1240px width) when sidebar is visible', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Visible', 'mfn-opts'),
	  						'1' => __('Hidden', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

	  				array(
	  					'id' => 'sidemenu_entrance_animation',
	  					'type' => 'select',
	  					'title' => __('Entrance animation', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Default', 'mfn-opts'),
	  						'move-content' => __('Move content off the screen', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'sidemenu_close_button_active',
	  					'attr_id' => 'sidemenu_close_button_active',
	  					'type' => 'select',
	  					'title' => __('Close button', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Hidden', 'mfn-opts'),
	  						'1' => __('Visible', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

	  				array(
	  					'id' => 'sidemenu_close_on_overlay_click',
	  					'type' => 'switch',
	  					'title' => __('Close on overlay click', 'mfn-opts'),
	  					'options' => array(
	  						'1' => __('Enable', 'mfn-opts'),
	  						'' => __('Disable', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'id' => 'sidemenu_body_scroll',
	  					'type' => 'switch',
	  					'title' => __('Browser scroll when sidemenu is open', 'mfn-opts'),
	  					'options' => array(
	  						'1' => __('Enable', 'mfn-opts'),
	  						'' => __('Disable', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

					array(
	  					'title' => __('Design', 'mfn-opts'),
	  				),

	  				array(
						'class' => 'mfn-builder-subheader',
						'title' => __('Sidebar', 'mfn-opts'),
					),

					array(
	  					'id' => 'sidemenu_position',
	  					'attr_id' => 'popup_position',
	  					'type' => 'radio_img',
	  					'title' => __('Position', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Left', 'mfn-opts'),
	  						'right' => __('Right', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),

  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid:--mfn-sidemenu-width',
	  					'id' => 'css_width',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => '--mfn-sidemenu-width',
						'type' => 'color',
	  					'type' => 'text',
	  					'title' => __('Width', 'mfn-opts'),
						'default_unit' => 'px',
	  					'std' => '400px',
	  					'class' => 'mfn-slider-input',
	  					'responsive' => true,
  					),


  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-tmpl-builder:justify-content',
	  					'id' => 'css_justify_content',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-tmpl-builder',
						'style' => 'justify-content',
						'type' => 'color',
	  					'type' => 'radio_img',
	  					'alias' => 'sidemenu_content_position',
	  					'responsive' => true,
	  					'title' => __('Content position', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Top', 'mfn-opts'),
	  						'center' => __('Center', 'mfn-opts'),
	  						'flex-end' => __('Bottom', 'mfn-opts'),
	  						'space-between' => __('Space between', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),



  					array(
						'old_id' => 'style:#mfn-sidemenu-tmpl-postid:background-color',
						'id' => 'css_bg',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
						'std' => '#fff'
					),

					array(
		  				'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-tmpl-builder:padding',
		  				'id' => 'css_padding',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-tmpl-builder',
						'style' => 'padding',
						'type' => 'color',
		  				'type' => 'dimensions',
		  				'version' => 'separated-fields',
		  				'title' => __('Padding', 'mfn-opts'),
						'responsive' => true,
		  			),


					array(
		  				'old_id' => 'style:#mfn-sidemenu-tmpl-postid:box-shadow',
		  				'id' => 'css_box_shadow',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => 'box-shadow',
						'type' => 'color',
		  				'type' => 'box_shadow',
		  				'title' => __('Box shadow', 'mfn-opts'),
						'css_attr' => 'box-shadow',
		  			),

					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid:z-index',
	  					'id' => 'css_z_index',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => 'z-index',
						'type' => 'color',
	  					'type' => 'text',
	  					'title' => __('z-index', 'mfn-opts'),
  					),

  					array(
  						'class' => 'mfn-builder-subheader',
  						'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
						'title' => __('Close button', 'mfn-opts'),
					),

					array(
	  					'id' => 'sidemenu_close_button_align',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'type' => 'select',
	  					'title' => __('Align', 'mfn-opts'),
	  					'options' => array(
	  						'' => __('Right', 'mfn-opts'),
	  						'left' => __('Left', 'mfn-opts'),
	  					),
	  					'std' => '',
  					),
















					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid:--mfn-sidemenu-closebutton-size',
	  					'id' => 'css_exit_size',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => '--mfn-sidemenu-closebutton-size',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Button size', 'mfn-opts'),
	  					'responsive' => true,
	  					'param' => 'number',
						'preview' => 'number',
						'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '30px',
  					),

  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid:--mfn-sidemenu-closebutton-font-size',
	  					'id' => 'css_exit_font_size',
						'selector' => '#mfn-sidemenu-tmpl-postid',
						'style' => '--mfn-sidemenu-closebutton-font-size',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Icon size', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
						'preview' => 'number',
						'param' => array(
							'min' => '0',
							'max' => '50',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '16px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:top',
	  					'id' => 'css_exit_top',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton',
						'style' => 'top',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Vertical offset', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
						'preview' => 'number',
						'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '0px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:--mfn-sidemenu-closebutton-offset-horizontal',
	  					'id' => 'css_exit_offset',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton',
						'style' => '--mfn-sidemenu-closebutton-offset-horizontal',
						'type' => 'color',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Horizontal offset', 'mfn-opts'),
	  					'param' => 'number',
						'preview' => 'number',
						'responsive' => true,
						'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'after' => 'px',
	  					'std' => '0px',
  					),


  					array(
	  					'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:border-radius',
	  					'id' => 'css_exit_border_radius',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton',
						'style' => 'border-radius',
	  					'type' => 'sliderbar',
	  					'condition' => array( 'id' => 'sidemenu_close_button_active', 'opt' => 'is', 'val' => '1' ),
	  					'title' => __('Border radius', 'mfn-opts'),
	  					'param' => 'number',
	  					'responsive' => true,
	  					'param' => array(
							'min' => '0',
							'max' => '100',
							'step' => '1',
							'unit' => 'px',
						),
						'preview' => 'number',
						'after' => 'px',
	  					'std' => '3px',
  					),

  					array(
						'type' => 'html',
						'html' => '<div class="mfn-form-row activeif activeif-sidemenu_close_button_active mfn-sidebar-fields-tabs mfn-vb-formrow" data-condition="{&quot;id&quot;:&quot;sidemenu_close_button_active&quot;,&quot;opt&quot;:&quot;is&quot;,&quot;val&quot;:&quot;1&quot;}"><ul class="mfn-sft-nav"><li><a href="#normal" data-tab="normal">Normal</a></li><li><a href="#hover" data-tab="hover">Hover</a></li></ul><div class="mfn-sft mfn-sft-normal">',
					),

  					array(
						'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:color',
						'id' => 'css_exit_color',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton',
						'style' => 'color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Color', 'mfn-opts'),
					),

  					array(
						'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:background-color',
						'id' => 'css_exit_bg',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
					),

					array(
						'type' => 'html',
						'html' => '</div><div class="mfn-sft mfn-sft-hover mfn-tabs-fields">',
					),

					array(
						'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton|hover:color',
						'id' => 'css_exit_color_hover',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:hover',
						'style' => 'color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Color', 'mfn-opts'),
					),

  					array(
						'old_id' => 'style:#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton|hover:background-color',
						'id' => 'css_exit_bg_hover',
						'selector' => '#mfn-sidemenu-tmpl-postid .mfn-sidemenu-closebutton:hover',
						'style' => 'background-color',
						'type' => 'color',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
					),


					array(
						'type' => 'html',
						'html' => '</div></div>',
					),























  					array(
  						'class' => 'mfn-builder-subheader',
						'title' => __('Overlay', 'mfn-opts'),
					),

					array(
						'id' => 'sidemenu_overlay_background',
						'type' => 'color',
						'title' => __('Background color', 'mfn-opts'),
					),

					array(
						'id' => 'sidemenu_overlay_blur',
						'type' => 'sliderbar',
						'title' => __('Blur', 'mfn-opts'),
						'param' => array(
							'min' => '0',
							'max' => '20',
							'step' => '1'
						),
						'std' => '0'
					),

				)
			);
		}

		public function set_megamenu_fields(){

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Mega menu Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
						'type' => 'header',
	  					'title' => __('Settings', 'mfn-opts'),
	  				),

					array(
	  					'id' => 'megamenu_width',
	  					'attr_id' => 'megamenu_width',
	  					'type' => 'select',
	  					'title' => __('Type', 'mfn-opts'),
	  					'options' => array(
	  						'full-width' => __('Full width', 'mfn-opts'),
	  						'grid' => __('Grid', 'mfn-opts'),
	  						'custom-width' => __('Custom', 'mfn-opts')
	  					),
	  					'required' => true,
	  					'std' => 'full-width',
  					),

  					array(
	  					'id' => 'megamenu_custom_width',
	  					'condition' => array( 'id' => 'megamenu_width', 'opt' => 'is', 'val' => 'custom-width' ),
	  					'type' => 'text',
	  					'title' => __('Custom width', 'mfn-opts'),
	  					'desc' => __('Works with Custom type', 'mfn-opts'),
	  					'default_unit' => 'px',
	  					'std' => '220px',
  					),

  					array(
	  					'id' => 'megamenu_custom_position',
	  					'condition' => array( 'id' => 'megamenu_width', 'opt' => 'is', 'val' => 'custom-width' ),
	  					'type' => 'select',
	  					'title' => __('Position', 'mfn-opts'),
	  					'options' => array(
	  						'left' => __('Left', 'mfn-opts'),
	  						'right' => __('Right', 'mfn-opts')
	  					),
	  					'std' => 'left',
  					),

  					array(
	  					'title' => __('Design', 'mfn-opts'),
	  				),

  					array(
	  					'old_id' => 'style:#mfn-megamenu-postid:padding',
	  					'id' => 'css_padding',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'padding',
	  					'type' => 'sliderbar',
	  					'title' => __('Padding', 'mfn-opts'),
	  					'param' => 'number',
	  					'param' => array(
							'min' => '0',
							'max' => '200',
							'step' => '1',
							'unit' => 'px',
						),
						'preview' => 'number',
						'after' => 'px',
  					),

  					array(
						'old_id' => 'style:#mfn-megamenu-postid:border-style',
						'id' => 'css_border_style',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'border-style',
						'attr_id' => 'border_style_mm',
						'type' => 'select',
						'title' => __('Border style', 'mfn-opts'),
						'options' => [
							'none' => __('None', 'mfn-opts'),
							'solid' => __('Solid', 'mfn-opts'),
							'dashed' => __('Dashed', 'mfn-opts'),
							'dotted' => __('Dotted', 'mfn-opts'),
							'double' => __('Double', 'mfn-opts'),
						],
					),

					array(
						'old_id' => 'style:#mfn-megamenu-postid:border-color',
						'id' => 'css_border_color',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'border-color',
						'type' => 'color',
						'condition' => array( 'id' => 'border_style_mm', 'opt' => 'isnt', 'val' => 'none' ),
						'title' => __('Border color', 'mfn-opts'),
					),

					array(
		  				'old_id' => 'style:#mfn-megamenu-postid:border-width',
		  				'id' => 'css_border_width',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'border-width',
		  				'condition' => array( 'id' => 'border_style_mm', 'opt' => 'isnt', 'val' => 'none' ),
		  				'type' => 'dimensions',
		  				'title' => __('Border width', 'mfn-opts'),
						'css_attr' => 'border-width',
		  			),

  					array(
		  				'old_id' => 'style:#mfn-megamenu-postid:border-radius',
		  				'id' => 'css_border_radius',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'border-radius',
						'type' => 'dimensions',
		  				'title' => __('Border radius', 'mfn-opts'),
						'css_attr' => 'border-radius',
		  			),

  					array(
						'old_id' => 'style:#mfn-megamenu-postid:background-color',
						'id' => 'css_bg',
						'selector' => '#mfn-megamenu-postid',
						'style' => 'background-color',
						'type' => 'color',
						'std' => '#fff',
						'title' => __('Background', 'mfn-opts'),
					),



  				),
			);
		}

		public function set_footer_fields(){

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Footer Options', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
						'type' => 'header',
	  					'title' => __('Settings', 'mfn-opts'),
	  				),

					array(
	  					'id' => 'footer_type',
	  					'type' => 'select',
	  					'title' => __('Style', 'mfn-opts'),
							'desc' => __( '<b>Sliding style</b> does not work with <b>sticky wraps</b> and <b>transparent content</b>.', 'mfn-opts' ),
	  					'options' => array(
	  						'default' => __('Default', 'mfn-opts'),
	  						'fixed' => __('Fixed (covers content)', 'mfn-opts'),
	  						'sliding' => __('Sliding (under content)', 'mfn-opts'),
	  						'stick' => __('Stick to bottom if content is too short', 'mfn-opts'),
	  					),
	  					'required' => true,
	  					'std' => 'full-width',
  					),

  				),
			);
		}

		public function set_bebuilder_only($post_id){

			$type = $this->getReferer();

			return array(
				'id' => 'mfn-meta-template',
				'title' => esc_html__('Edit with '. apply_filters('betheme_label', "Be") .'Builder', 'mfn-opts'),
				'page' => 'template',
				'fields' => array(

					array(
	  					'id' => 'mfn_template_type',
	  					'type' => 'text',
	  					'class' => 'mfn_template_type mfn-hidden-field',
	  					'title' => __('Template type', 'mfn-opts'),
	  					'std' => $type,
  					),

					array(
						'id' => 'go-live',
						'type' => 'redirect_button',
						'html' => '<div class="mfn-admin-button-box"><a href="link_here" class="mfn-btn mfn-switch-live-editor button-hero mfn-btn-green button button-primary">Edit with '. apply_filters('betheme_label', "Be") .'Builder</a></div>',
					),

  			),
			);
		}

		/**
		 * Register new post type and related taxonomy
		 */

		public function register()
		{
			$labels = array(
				'name' => esc_html__('Templates', 'mfn-opts'),
				'singular_name' => esc_html__('Template', 'mfn-opts'),
				'add_new' => esc_html__('Add New', 'mfn-opts'),
				'add_new_item' => esc_html__('Add New Template', 'mfn-opts'),
				'edit_item' => esc_html__('Edit Template', 'mfn-opts'),
				'new_item' => esc_html__('New Template', 'mfn-opts'),
				'view_item' => esc_html__('View Template', 'mfn-opts'),
				'search_items' => esc_html__('Search Template', 'mfn-opts'),
				'not_found' => esc_html__('No templates found', 'mfn-opts'),
				'not_found_in_trash' => esc_html__('No templates found in Trash', 'mfn-opts'),
				'parent_item_colon' => ''
			);

			$args = array(
				'labels' => $labels,
				'menu_icon' => 'dashicons-layout',
				'public' => false,
				'publicly_queryable' => true,
				'exclude_from_search' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => 3,
				'rewrite' => array('slug'=>'template-item', 'with_front'=>true),
				'supports' => array( 'title', 'author' ),
			);

			register_post_type('template', $args);

			function fix_custom_menu_order( $menu_ord ) {

		    if ( ! $menu_ord ) return true;

		    $new_order = array(
		        'index.php',
		         apply_filters('betheme_dynamic_slug', 'betheme'),
		        'edit.php?post_type=template',
		        'elementor-home',
		    );

		    foreach ( $new_order as $item ) {
	        $key = array_search( $item, $menu_ord );
	        if ( $key !== false ) {
            unset( $menu_ord[$key] );
	        }
		    }

	    	return array_merge( $new_order, $menu_ord );
			}

			add_filter( 'custom_menu_order', '__return_true' );
			add_filter( 'menu_order', 'fix_custom_menu_order' );
		}

		public function filter_by_tab($query){

			$tab = '';

      if ( is_admin() && $query->get('post_type') == 'template' && ( !$query->get('post_status') || empty($query->get('post_status')) ) && !empty($_GET['tab']) ) {

		  	if( ! function_exists('is_woocommerce')){
				$meta_query = array(
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'single-product',
						'compare'=> '!=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'shop-archive',
						'compare'=> '!=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'cart',
						'compare'=> '!=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'checkout',
						'compare'=> '!=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'thanks',
						'compare'=> '!=',
					),
				);
				$query->set('meta_query',$meta_query);
			}

        if( !empty($_GET['tab']) ) {

        	$tab = $_GET['tab'];

        	if( in_array($tab, array('blog', 'archive-post')) ) {

        		$meta_query = array(
        			'relation' => 'OR',
					array(
						'key'=> 'mfn_template_type',
						'value'=> $tab,
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'archive-post',
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'blog',
						'compare'=> '=',
					),
				);
        	}elseif( in_array($tab, array('shop-archive', 'archive-product')) ) {

        		$meta_query = array(
        			'relation' => 'OR',
					array(
						'key'=> 'mfn_template_type',
						'value'=> $tab,
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'archive-product',
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'shop-archive',
						'compare'=> '=',
					),
				);
        	}elseif( in_array($tab, array('portfolio', 'archive-portfolio')) ){

        		$meta_query = array(
        			'relation' => 'OR',
					array(
						'key'=> 'mfn_template_type',
						'value'=> $tab,
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'archive-portfolio',
						'compare'=> '=',
					),
					array(
						'key'=> 'mfn_template_type',
						'value'=> 'portfolio',
						'compare'=> '=',
					),
				);
        	}else{
        		$meta_query = array(
					array(
						'key'=> 'mfn_template_type',
						'value'=> $tab,
						'compare'=> '=',
					),
				);
        	}

        	$query->set('meta_query',$meta_query);

	      }

	    }

		}

		public function list_tabs_wrapper($actions) {
			global $post_ID;
			global $wpdb;

			$post_types_disable = mfn_opts_get('post-type-disable');

			$screen = get_current_screen();

			$tab = null;

			if( isset($screen->post_type) && $screen->post_type == 'template' ) :

			if( !empty($_GET['tab']) && ( empty($_GET['post_status']) ) ) $tab = $_GET['tab'];

			$headers = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'header'" );
			$footers = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'footer'" );
			$searchs = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'search'" );
			$megamenus = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'megamenu'" );
			$popups = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'popup'" );

			$sidebars = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'sidemenu'" );
			$blogs = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value IN ('blog', 'archive-post')" );
			$posts = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'single-post'" );

			if( !isset($post_types_disable['portfolio']) ) {
				$portfolios = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value IN ('portfolio', 'archive-portfolio')" );
				$singleportfolios = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'single-portfolio'" );
			}

			if(function_exists('is_woocommerce')) {
				$shops = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value IN ('shop-archive', 'archive-product')" );
				$products = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'single-product'" );
				$carts = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'cart'" );
				$checkouts = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'checkout'" );
				$thanks = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'thanks'" );
			}

			$globals = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND ( pm.meta_value = 'section' OR pm.meta_value = 'wrap' )" );

			$sections = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'section'" );
			$wraps = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'wrap'" );

			$pages = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = 'default'" );

			$cpt_s = Mfn_Builder_Helper::get_post_types( array('public' => true, '_builtin' => false, 'skip_mfn_cpt' => true ) );

			?>

			<ul class="mfn-template-filters">
				<li><a href="?post_type=template" class="nav-tab <?php if(empty($tab)):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('All', 'mfn-opts'); ?></a></li>
				<li><a href="?post_type=template&tab=header" class="nav-tab mfn-label-nav-tab mfn-label-header <?php if($tab == 'header'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Header', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($headers); ?></span></a></li>

				<li><a href="?post_type=template&tab=megamenu" class="nav-tab mfn-label-nav-tab mfn-label-megamenu <?php if($tab == 'megamenu'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Mega menu', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($megamenus); ?></span></a></li>
				<li><a href="?post_type=template&tab=sidemenu" class="nav-tab mfn-label-nav-tab mfn-label-sidemenu <?php if($tab == 'sidemenu'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Sidebar menu', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($sidebars); ?></span></a></li>

				<li><a href="?post_type=template&tab=footer" class="nav-tab mfn-label-nav-tab mfn-label-footer <?php if($tab == 'footer'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Footer', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($footers); ?></span></a></li>
				<li><a href="?post_type=template&tab=popup" class="nav-tab mfn-label-nav-tab mfn-label-popup <?php if($tab == 'popup'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Popup', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($popups); ?></span></a></li>

				<li class="mfn-template-filter-dropdown">
					<a href="?post_type=template&tab=archive-post" class="nav-tab mfn-label-nav-tab mfn-label-blog <?php if(in_array($tab, array('blog', 'single-post', 'archive-post'))):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Blog', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo (count($blogs)+count($posts)); ?></span> <span class="mfn-icon mfn-icon-arrow-down"></span></a>
					<ul>
						<li><a href="?post_type=template&tab=archive-post" class="nav-tab mfn-label-nav-tab mfn-label-blog <?php if($tab == 'blog' || $tab == 'archive-post'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Archive', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($blogs); ?></span></a></li>
						<li><a href="?post_type=template&tab=single-post" class="nav-tab mfn-label-nav-tab mfn-label-blog <?php if($tab == 'single-post'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Single post', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($posts); ?></span></a></li>
					</ul>
				</li>

				<?php if( !isset($post_types_disable['portfolio']) ) { ?>
				<li class="mfn-template-filter-dropdown">
					<a href="?post_type=template&tab=archive-portfolio" class="nav-tab mfn-label-nav-tab mfn-label-portfolio <?php if(in_array($tab, array('archive-portfolio', 'portfolio', 'single-portfolio'))):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Portfolio', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo (count($portfolios)+count($singleportfolios)); ?></span> <span class="mfn-icon mfn-icon-arrow-down"></span></a>
					<ul>
						<li><a href="?post_type=template&tab=archive-portfolio" class="nav-tab mfn-label-nav-tab mfn-label-portfolio <?php if($tab == 'portfolio' || $tab == 'archive-portfolio'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Archive', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($portfolios); ?></span></a></li>
						<li><a href="?post_type=template&tab=single-portfolio" class="nav-tab mfn-label-nav-tab mfn-label-portfolio <?php if($tab == 'single-portfolio'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Single post', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($singleportfolios); ?></span></a></li>
					</ul>
				</li>
				<?php } ?>

				<?php if(function_exists('is_woocommerce')): ?>
				<li class="mfn-template-filter-dropdown">
					<a href="?post_type=template&tab=archive-product" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if(in_array($tab, array('archive-product', 'single-product', 'cart', 'checkout', 'thanks'))):?>nav-tab-active<?php endif; ?>">WooCommerce <span class="mfn-label-counter"><?php echo (count($products) + count($shops) + count($carts) + count($checkouts) + count($thanks)); ?></span> <span class="mfn-icon mfn-icon-arrow-down"></span></a>
					<ul>
						<li><a href="?post_type=template&tab=archive-product" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if($tab == 'archive-product'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Shop', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($shops); ?></span></a></li>
						<li><a href="?post_type=template&tab=single-product" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if($tab == 'single-product'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Single product', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($products); ?></span></a></li>
						<li><a href="?post_type=template&tab=cart" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if($tab == 'cart'):?>nav-tab-active<?php endif; ?>">Cart <span class="mfn-label-counter"><?php echo count($carts); ?></span></a></li>
						<li><a href="?post_type=template&tab=checkout" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if($tab == 'checkout'):?>nav-tab-active<?php endif; ?>">Checkout <span class="mfn-label-counter"><?php echo count($checkouts); ?></span></a></li>
						<li><a href="?post_type=template&tab=thanks" class="nav-tab mfn-label-nav-tab mfn-label-woocommerce <?php if($tab == 'thanks'):?>nav-tab-active<?php endif; ?>">Thank you <span class="mfn-label-counter"><?php echo count($thanks); ?></span></a></li>
					</ul>
				</li>
				<?php endif; ?>

				<li class="mfn-template-filter-dropdown">
					<a href="?post_type=template&tab=section" class="nav-tab mfn-label-nav-tab mfn-label-global <?php if(in_array($tab, array('section', 'wrap'))):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Global', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo (count($sections)+count($wraps)); ?></span> <span class="mfn-icon mfn-icon-arrow-down"></span></a>
					<ul>
						<li><a href="?post_type=template&tab=section" class="nav-tab mfn-label-nav-tab mfn-label-global <?php if($tab == 'section'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Sections', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($sections); ?></span></a></li>
						<li><a href="?post_type=template&tab=wrap" class="nav-tab mfn-label-nav-tab mfn-label-global <?php if($tab == 'wrap'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Wraps', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($wraps); ?></span></a></li>
					</ul>
				</li>
				<li><a href="?post_type=template&tab=search" class="nav-tab mfn-label-nav-tab mfn-label-search <?php if($tab == 'search'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Search', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($searchs); ?></span></a></li>
				<li><a href="?post_type=template&tab=default" class="nav-tab mfn-label-nav-tab mfn-label-default <?php if($tab == 'default'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Page', 'mfn-opts'); ?> <span class="mfn-label-counter"><?php echo count($pages); ?></span></a></li>
			</ul>

			<?php if( !empty($cpt_s) ) { ?>
			<ul class="mfn-template-filters mfn-cpt-template-filters">
				<li class="mfn-cpt-template-filters-label"><span><?php esc_html_e('Custom post types', 'mfn-opts'); ?>:</span></li>
				<?php
				foreach ($cpt_s as $cpt) {
					$singular = 'single-'.$cpt->name;
					$archive = 'archive-'.$cpt->name;
					$cpt_single_amount = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = '{$singular}'" );
					$cpt_archive_amount = $wpdb->get_results( "SELECT `meta_id` FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->posts} po ON po.ID = pm.post_id WHERE po.post_status IN ('publish','draft') AND pm.meta_key = 'mfn_template_type' AND pm.meta_value = '{$archive}'" );

					echo '<li class="mfn-template-filter-dropdown">
					<a href="?post_type=template&tab='.$archive.'" class="nav-tab mfn-label-nav-tab mfn-label-nav-cpt mfn-label-'.$cpt->name.' '.(in_array($tab, array($singular , 'wrap')) ? 'nav-tab-active' : '').'"> '.$cpt->label.' <span class="mfn-label-counter">'.(count($cpt_single_amount)+count($cpt_archive_amount)).'</span> <span class="mfn-icon mfn-icon-arrow-down"></span></a>';
					echo '<ul>';
						echo '<li><a href="?post_type=template&tab='.$singular.'" class="nav-tab mfn-label-nav-tab mfn-label-nav-cpt mfn-label-nav-cpt-single mfn-label-global '.($tab == $singular ? 'nav-tab-active' : '').'">Single '.$cpt->labels->singular_name.' <span class="mfn-label-counter">'.count($cpt_single_amount).'</span></a></li>';
						echo '<li><a href="?post_type=template&tab='.$archive.'" class="nav-tab mfn-label-nav-tab mfn-label-nav-cpt mfn-label-nav-cpt-archive mfn-label-global '.($tab == $archive ? 'nav-tab-active' : '').'">Archive of '.$cpt->labels->name.' <span class="mfn-label-counter">'.count($cpt_archive_amount).'</span></a></li>';
					echo '</ul>';
				echo '</li>';
				} ?>
			</ul>

			<?php } endif;


			if( $tab == 'search' && function_exists('is_woocommerce') ){

				echo '<div class="notice notice-info mfn-admin-notice"><h4>The Search Results page template is used for default search results</h4><p>However, if you are searching for products only using the "Search shop products only" option, the WooCommerce Shop template will be applied instead. You can also create a dedicated Shop template specifically for search results by configuring the Display Conditions that appear after saving your Shop template in BeBuilder.</p></div>';
			}

			return $actions;

		}

		public function getReferer(){

			$type = 'default';

			if( !empty($_GET['post_type']) && ('template' == $_GET['post_type']) && !empty($_GET['tab']) ){

				$type = $_GET['tab'];

			} else {

				$ref = parse_url(wp_get_referer());
				if( isset($ref['query']) && $ref['query'] ){
					$ex_ref = explode('post_type=template&tab=', $ref['query']);
					if(isset($ex_ref[1])){
						$type = $ex_ref[1];
					}
				}

			}

			return $type;
		}

	}
}

new Mfn_Post_Type_Template();
