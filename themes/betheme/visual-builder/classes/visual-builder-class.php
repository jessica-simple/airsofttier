<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

class MfnVisualBuilder {

	public $post_type = false;
	public $ui_mode = 'dev';
	public $template_type = false;
	public $page_options = false;
	public $theme_options = false;
	public $options = array();
	public $widgets = array();
	public $scripts = array();
	public $styles = array();
	public $post_id = false;
	public $sample_content = false;
	public $view = 'demo';
	public $user = false;

	public function __construct() {
		global $post;

		if( !isset($post->ID) && apply_filters('is_bebuilder_demo', false) ) {
			$this->post_id = get_the_ID();
		}else if(isset($post->ID) ) {
			$this->post_id = $post->ID;
		}

		if( is_admin() ) $this->view = 'admin';

		$this->user = get_current_user_id();

		if( $this->post_id ) {

			$this->post_type = get_post_type($this->post_id);

		  if($this->post_type == 'template') $this->template_type = get_post_meta($post->ID, 'mfn_template_type', true);

		  // deprecated compability
		  if( $this->template_type == 'shop-archive' ) $this->template_type = 'archive-product';
		  if( $this->template_type == 'portfolio' ) $this->template_type = 'archive-portfolio';
		  if( $this->template_type == 'blog' ) $this->template_type = 'archive-post';

			if($this->post_type == 'post') { 
			  $po_class = new Mfn_Post_Type_Post();
			}elseif($this->post_type == 'portfolio') {
			  $po_class = new Mfn_Post_Type_Portfolio();
			}elseif($this->post_type == 'template') {
			  $po_class = new Mfn_Post_Type_Template();
			}elseif($this->post_type == 'product') {
			  $po_class = new Mfn_Post_Type_Product();
			}else{
			  $po_class = new Mfn_Post_Type_Page();
			}

			if( $this->template_type == 'header' ){
				$this->page_options = $po_class->set_header_fields();
			}elseif( $this->template_type == 'footer' ){
				$this->page_options = $po_class->set_footer_fields();
			}elseif( $this->template_type == 'megamenu' ){
				$this->page_options = $po_class->set_megamenu_fields();
			}elseif( $this->template_type == 'popup' ){
				$this->page_options = $po_class->set_popup_fields();
			}elseif( $this->template_type == 'sidemenu' ){
				$this->page_options = $po_class->set_sidemenu_fields();
			}else{
				$this->page_options = $po_class->set_fields();
			}

	  }

  }

  public function mfn_add_admin_beglobalsections_class($classes){
		return $classes.' mfn-template-section';
  }

  public function mfn_add_admin_beglobalwraps_class($classes){
    return $classes.' mfn-template-wrap';
  }

  public function mfn_add_admin_beheader_class($classes){
  	return $classes.' mfn-preview-mode mfn-be-header-builder';
  }

  public function mfn_add_admin_bemegamenu_class($classes){
  	return $classes.' mfn-preview-mode mfn-be-megamenu-builder';
  }

  public function mfn_add_admin_becart_class($classes){
  	return $classes.' mfn-preview-mode mfn-be-cart-builder';
  }

  public function mfn_add_admin_befooter_class($classes){
  	return $classes.' mfn-preview-mode mfn-be-megamenu-builder';
  }

  public function mfn_add_admin_bepopup_class($classes){
  	return $classes.'mfn-be-popup-builder';
  }

	public function mfn_required_scripts() {
		$this->scripts = array(
  		'wp-auth-check',
  		'heartbeat',
  		'jquery',
  		'jquery-core',
  		'jquery-migrate',
  		'jquery-ui-core',
  		'jquery-ui-tabs',
  		'mediaelement',
  		'mediaelement-core',
  		'mediaelement-migrate',
  		'mediaelement-vimeo',
  		'wp-mediaelement',
  		//'media-upload',
  		'media-models',
  		'media-views',
  		'media-editor',
  		'media-audiovideo',
  		'media-widgets',
  		'media-audio-widget',
  		'media-image-widget',
  		'media-gallery-widget',
  		'media-video-widget',
  		'media-grid',
  		'media',
  		'media-gallery',
  		'wp-media-utils'
		);
	}

	public function mfn_required_styles() {
		$this->styles = array(
			'colors',
			'common',
			'forms',
			'admin-menu',
			'dashboard',
			'list-tables',
			'edit',
			'revisions',
			'media',
			'themes',
			'about',
			'nav-menus',
			'widgets',
			'site-icon',
			'l10n',
			'code-editor',
			'site-health',
			'wp-admin',
			'login',
			'tabs',
			'install',
			'wp-color-picker',
			'customize-controls',
			'customize-widgets',
			'customize-nav-menus',
			'buttons',
			'dashicons',
			'admin-bar',
			'wp-auth-check',
			'editor-buttons',
			'media-views',
			'wp-pointer',
			'customize-preview',
			'wp-embed-template-ie',
			'imgareaselect',
			'wp-jquery-ui-dialog',
			'mediaelement',
			'wp-mediaelement',
		);
	}



	public function mfn_append_vb_header() {
		wp_enqueue_style('mfn-vbreset', get_theme_file_uri('/visual-builder/assets/css/reset.css'), false, MFN_THEME_VERSION, 'all');
    wp_enqueue_style('mfn-vbstyle', get_theme_file_uri('/visual-builder/assets/css/style.css'), false, time(), false);

    wp_enqueue_style('wp-codemirror');

    // icons
		wp_enqueue_style('mfn-icons', get_theme_file_uri('/fonts/mfn/icons.css'), false, time());
		wp_enqueue_style('mfn-font-awesome', get_theme_file_uri('/fonts/fontawesome/fontawesome.css'), false, time());

		// VB styles & scripts
		wp_enqueue_style('mfn-vbcolorpickerstyle', get_theme_file_uri('/visual-builder/assets/css/nano.min.css'), false, time(), false);

		wp_enqueue_style('mfn-codemirror-dark', get_theme_file_uri('/visual-builder/assets/css/codemirror-dark.css'), false, MFN_THEME_VERSION, 'all');

	}

	public function mfn_append_vb_footer() {
		global $wp_scripts;
		global $wp_styles;

		if( wp_script_is( 'mfn-vbscripts', 'enqueued') ) {
 			return; // prevent localize script more than once
 		}

    $create_bebuilder_fields = true;

    $mfn_beform_ver = get_option('betheme_form_uid') ? get_option('betheme_form_uid') : MFN_THEME_VERSION;

    if( is_admin() && ( !file_exists( self::bebuilderFilePath() ) || ( defined('MFN_DEBUG') && MFN_DEBUG == 1 ) ) ) {
    	$create_bebuilder_fields = Mfn_Helper::generate_bebuilder_items();
    	$mfn_beform_ver = time();
    }

    if( $create_bebuilder_fields ) {
    	wp_enqueue_script( 'mfn-bebuilder-fields', self::bebuilderFilePath(true), false, $mfn_beform_ver, true );
    }else{
    	echo '<script id="mfn-bebuilder-fields-live">'.$this->fieldsToJS().'</script>';
    }

		wp_enqueue_script( 'mfn-plugins', get_theme_file_uri('/js/plugins.js'), array('jquery'), MFN_THEME_VERSION, true );

		wp_enqueue_script( 'wp-theme-plugin-editor' );

		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-slider' );

		/*wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'wplink' );*/

		wp_enqueue_script( 'wp-auth-check' );
		wp_enqueue_script( 'heartbeat' );

	  // Add the color picker

	  wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
		wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, 1 );

		// webfont
		wp_enqueue_script( 'mfn-webfont', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js', array( 'jquery' ), false, true );

		wp_enqueue_media();
		wp_enqueue_editor();
		/*wp_enqueue_code_editor();*/
		wp_enqueue_style( 'wp-components' );


		wp_enqueue_script('mfn-rangy', get_theme_file_uri('/visual-builder/assets/js/rangy-core.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfn-rangy-classapplier', get_theme_file_uri('/visual-builder/assets/js/rangy-classapplier.js'), false, MFN_THEME_VERSION, true);

		wp_enqueue_script('mfn-vbcolorpickerjs', get_theme_file_uri('/visual-builder/assets/js/pickr.min.js'), false, time(), true);
		wp_enqueue_script('mfn-inline-editor-js', get_theme_file_uri('/visual-builder/assets/js/medium-editor.min.js'), false, time(), true);
		// wp_enqueue_script('mfn-vblistjs', get_theme_file_uri('/visual-builder/assets/js/list.min.js'), false, time(), true);


		wp_enqueue_script('mfn-vbace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.31.2/ace.js', false, time(), true);
		wp_enqueue_script('mfn-vbace-lang', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.31.2/ext-language_tools.min.js', false, time(), true);

		wp_add_inline_script( 'mfn-inline-editor-js', 'let mfnajaxurl = "'. admin_url( 'admin-ajax.php' ) . '"; function getContrastYIQ( hexcolor, tolerance ){hexcolor = hexcolor.replace( "#", "" ); tolerance = typeof tolerance !== "undefined" ? tolerance : 169; if( 6 != hexcolor.length ){return false; } var r = parseInt( hexcolor.substr(0,2),16 ); var g = parseInt( hexcolor.substr(2,2),16 ); var b = parseInt( hexcolor.substr(4,2),16 ); var yiq = ( ( r*299 ) + ( g*587 ) + ( b*114 ) ) / 1000; return ( yiq >= tolerance ) ? "light" : "dark"; }' );

		wp_enqueue_script('mfnHelpers', get_theme_file_uri('/visual-builder/assets/js/forms/helpers.js'), false, MFN_THEME_VERSION, true);

		/**
		 *
		 * FIELDS
		 *
		* */

		wp_enqueue_script('mfnFormHeader', get_theme_file_uri('/visual-builder/assets/js/forms/fields/header.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormText', get_theme_file_uri('/visual-builder/assets/js/forms/fields/text.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormBoxShadow', get_theme_file_uri('/visual-builder/assets/js/forms/fields/box_shadow.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormBoxShadowTO', get_theme_file_uri('/visual-builder/assets/js/forms/fields/boxshadow.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormHelper', get_theme_file_uri('/visual-builder/assets/js/forms/fields/helper.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormSwitch', get_theme_file_uri('/visual-builder/assets/js/forms/fields/switch.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormPills', get_theme_file_uri('/visual-builder/assets/js/forms/fields/pills.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormRadioImg', get_theme_file_uri('/visual-builder/assets/js/forms/fields/radio_img.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormUpload', get_theme_file_uri('/visual-builder/assets/js/forms/fields/upload.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormMultiselect', get_theme_file_uri('/visual-builder/assets/js/forms/fields/multiselect.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormSliderbar', get_theme_file_uri('/visual-builder/assets/js/forms/fields/sliderbar.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormCheckbox', get_theme_file_uri('/visual-builder/assets/js/forms/fields/checkbox.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormCheckboxPseudo', get_theme_file_uri('/visual-builder/assets/js/forms/fields/checkbox_pseudo.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormColor', get_theme_file_uri('/visual-builder/assets/js/forms/fields/color.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormSelect', get_theme_file_uri('/visual-builder/assets/js/forms/fields/select.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormFontSelect', get_theme_file_uri('/visual-builder/assets/js/forms/fields/font_select.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormHtml', get_theme_file_uri('/visual-builder/assets/js/forms/fields/html.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormSubheader', get_theme_file_uri('/visual-builder/assets/js/forms/fields/subheader.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormDimensions', get_theme_file_uri('/visual-builder/assets/js/forms/fields/dimensions.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormGradient', get_theme_file_uri('/visual-builder/assets/js/forms/fields/gradient.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormIcon', get_theme_file_uri('/visual-builder/assets/js/forms/fields/icon.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormCssFilters', get_theme_file_uri('/visual-builder/assets/js/forms/fields/css_filters.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTextarea', get_theme_file_uri('/visual-builder/assets/js/forms/fields/textarea.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormInfo', get_theme_file_uri('/visual-builder/assets/js/forms/fields/info.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTabs', get_theme_file_uri('/visual-builder/assets/js/forms/fields/tabs.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTextShadow', get_theme_file_uri('/visual-builder/assets/js/forms/fields/text_shadow.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTransform', get_theme_file_uri('/visual-builder/assets/js/forms/fields/transform.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTypographyVb', get_theme_file_uri('/visual-builder/assets/js/forms/fields/typography_vb.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormTypography', get_theme_file_uri('/visual-builder/assets/js/forms/fields/typography.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormVisual', get_theme_file_uri('/visual-builder/assets/js/forms/fields/visual.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormColorMulti', get_theme_file_uri('/visual-builder/assets/js/forms/fields/color_multi.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormMultiText', get_theme_file_uri('/visual-builder/assets/js/forms/fields/multi_text.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormAjax', get_theme_file_uri('/visual-builder/assets/js/forms/fields/ajax.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormCustom', get_theme_file_uri('/visual-builder/assets/js/forms/fields/custom.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFormSocial', get_theme_file_uri('/visual-builder/assets/js/forms/fields/social.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnDynamicItems', get_theme_file_uri('/visual-builder/assets/js/forms/fields/dynamic_items.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnUploadMulti', get_theme_file_uri('/visual-builder/assets/js/forms/fields/upload_multi.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnOrder', get_theme_file_uri('/visual-builder/assets/js/forms/fields/order.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnAceEditor', get_theme_file_uri('/visual-builder/assets/js/forms/fields/ace.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnLogic', get_theme_file_uri('/visual-builder/assets/js/forms/fields/logic.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnBackdropFilter', get_theme_file_uri('/visual-builder/assets/js/forms/fields/backdrop_filter.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnHotspot', get_theme_file_uri('/visual-builder/assets/js/forms/fields/hotspot.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnSelectAjax', get_theme_file_uri('/visual-builder/assets/js/forms/fields/select_ajax.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnFilters', get_theme_file_uri('/visual-builder/assets/js/forms/fields/filters.js'), false, MFN_THEME_VERSION, true);
		wp_enqueue_script('mfnSwitcher', get_theme_file_uri('/visual-builder/assets/js/forms/fields/switcher.js'), false, MFN_THEME_VERSION, true);

		wp_enqueue_script('mfnForm', get_theme_file_uri('/visual-builder/assets/js/forms/form.js'), false, MFN_THEME_VERSION, true);

		/**
		 *
		 * END FIELDS
		 *
		 * */

		wp_enqueue_script( 'mfn-opts-field-pills-vb', MFN_OPTIONS_URI .'fields/pills/field_pills_vb.js', array( 'jquery' ), MFN_THEME_VERSION, true );

		wp_enqueue_script('mfn-vbscripts', get_theme_file_uri('/visual-builder/assets/js/scripts.js'), false, time(), true);

		//add_filter('script_loader_tag', array($this, 'add_type_attribute' , 10, 3);

		$preview_post_type = $this->post_type;

		$localize_visual = array(
			'mfnsc' => get_theme_file_uri( '/functions/tinymce/plugin.js' ),
		);
		$google_fonts = mfn_fonts('all');

		$post_types_disable = mfn_opts_get('post-type-disable');

		wp_enqueue_script( 'mfn-opts-field-visual-vb', get_theme_file_uri('/muffin-options/fields/visual/field_visual_vb.js'), array( 'jquery' ), MFN_THEME_VERSION, true );
		wp_localize_script( 'mfn-opts-field-visual-vb', 'fieldVisualJS_vb', $localize_visual);

		$permalink = get_preview_post_link($this->post_id).'&visual=iframe';

		if( get_post_status($this->post_id) == 'publish' ){
			$permalink = get_permalink( $this->post_id );
			if( strpos($permalink, '?') !== false){
				$permalink .= '&visual=iframe';
			}else{
				$permalink .= '?visual=iframe';
			}
			if( ! is_admin() ){
				$permalink .= '&demo';
			}
		}

		// override if template shop archive
		if( function_exists('is_woocommerce') ){

			if( $this->post_type == 'template' && ($this->template_type == 'shop-archive' || $this->template_type == 'archive-product' ) ){

				if( !empty(get_option('woocommerce_shop_page_id')) && is_numeric(get_option('woocommerce_shop_page_id')) && get_post_status( get_option('woocommerce_shop_page_id') ) == 'publish' ){
					$permalink = get_permalink( wc_get_page_id( 'shop' ) ).'?mfn-template-id='.$this->post_id.'&visual=iframe';
				}else{
					$permalink = 'shop_page_id';
				}

			}else if( $this->post_type == 'template' && $this->template_type == 'single-product' ){

				$sample = Mfn_Builder_Woo_Helper::sample_item('product');
				$preview_post_type = 'product';

				if( $sample ){
					$product = wc_get_product($sample);
					if( !empty($product->get_id()) ){
						$this->sample_content = $product->get_id();

						$permalink = get_permalink( $product->get_id() );
						if( strpos($permalink, '?') !== false ){
							$permalink .= '&mfn-template-id='.$this->post_id.'&visual=iframe';
						}else{
							$permalink .= '?mfn-template-id='.$this->post_id.'&visual=iframe';
						}
					}
				}else{
					wp_safe_redirect( admin_url().'edit.php?post_type=product&mfn-notice=product-missing' );
				}

	 			$gallery_overlay = mfn_opts_get('shop-product-gallery-overlay');
	 			$thumbnails_margin = mfn_opts_get( 'shop-product-thumbnails-margin', 0, ['unit'=>'px'] );
				$main_margin = mfn_opts_get( 'shop-product-main-image-margin', 'mfn-mim-0' );

	 			wp_localize_script( 'mfn-vbcolorpickerjs', 'mfnwoovars',
			      	array(
			      		'productthumbsover' => $gallery_overlay,
				        'productthumbs' => $thumbnails_margin,
				        'mainimgmargin' => $main_margin
			      	)
	    		);
	 		}else if( $this->post_type == 'template' && $this->template_type == 'thanks' ){
	 			$args = array(
				    'limit' => 1,
				    'type' => 'shop_order',
				);
				$order = wc_get_orders($args);
				if( empty($order) ){
					$permalink = 'shop_order_missing';
				}else{
					$permalink .= '&mfn-order-id='.$order[0]->get_id();
				}


			}
		}

		if( $this->post_type == 'template' && $this->template_type == 'megamenu' ) {
			$permalink .= '&mfn-h=classic';
		}else if( $this->post_type == 'template' && $this->template_type == 'single-post' ){
			$sample = get_posts( array('post_type' => 'post', 'numberposts' => 1 ));
			if( !empty($sample[0]->ID) ){
				$preview_post_type = 'post';
				$this->sample_content = $sample[0]->ID;
				$permalink = get_permalink( $sample[0]->ID ).'?mfn-template-id='.$this->post_id.'&visual=iframe';
			}
	 	}else if( $this->post_type == 'template' && $this->template_type == 'single-portfolio' ){
	 		if (! isset($post_types_disable['portfolio'])) {
				$sample = get_posts( array('post_type' => 'portfolio', 'numberposts' => 1 ));
				if( !empty($sample[0]->ID) ){
					$preview_post_type = 'portfolio';
					$this->sample_content = $sample[0]->ID;
					$permalink = get_permalink( $sample[0]->ID );

					if( strpos($permalink, '?') !== false ){
						$permalink .= '&';
					}else{
						$permalink .= '?';
					}

					$permalink .= 'mfn-template-id='.$this->post_id.'&visual=iframe';
				}
			}else{
				$permalink = 'portfolio_post_type_missing';
			}
	 	}

	 	if( isset($post_types_disable['portfolio']) && $this->post_type == 'template' && $this->template_type == 'single-portfolio' ) {
	 		$permalink = 'portfolio_post_type_missing';
	 	}

	 	if( $this->view == 'demo' && !empty($_GET['ui']) && in_array($_GET['ui'], array('default', 'developer', 'blocks')) ) $permalink .= '&ui='.$_GET['ui'];

	 	if( empty( get_option('mfn-css-db-update') ) ) $permalink = 'mfn_update_db_required';

	 	/*

		mfn-page-object / fixed due to problem with saving large pages

	 	$page_obj = get_post_meta($this->post_id, 'mfn-page-object', true);
	 	if( !empty( json_decode($page_obj) ) ) { $page_obj = json_decode($page_obj); }else{ $page_obj = false; }*/

	 	$page_obj = false;

	 	if( $this->post_type == 'template' && (strpos($this->template_type, 'archive-') !== false || strpos($this->template_type, 'single-') !== false ) ){
	 		$preview_post_type = str_replace('archive-', '', $this->template_type);
	 		$preview_post_type = str_replace('single-', '', $this->template_type);

	 		if( strpos($this->template_type, 'single-') !== false ){

	 			$sample = get_posts( array('post_type' => $preview_post_type, 'numberposts' => 1 ));
				if( !empty($sample[0]->ID) ){
					$this->sample_content = $sample[0]->ID;
					$permalink = get_permalink( $sample[0]->ID );

					if( strpos($permalink, '?') !== false ){
						$permalink .= '&';
					}else{
						$permalink .= '?';
					}

					$permalink .= 'mfn-template-id='.$this->post_id.'&visual=iframe';
					
				}
	 		}
	 	}
		
		$current_user = wp_get_current_user();

	 	$lightboxOptions = mfn_opts_get('prettyphoto-options');
		$is_translation_on = mfn_opts_get('translate');
		$be_classes = !empty( get_option('be_classes') ) ? json_decode( get_option('be_classes'), true ) : false;
		$be_css_variables = !empty( get_option('be_css_variables') ) ? json_decode( get_option('be_css_variables'), true ) : array();
		$sidebars = mfn_opts_get('sidebars') ? mfn_opts_get('sidebars') : array();

		wp_localize_script( 'mfn-vbcolorpickerjs', 'mfn',
	      array(
	        'pageid' => $this->post_id,
	        'sample_content_id' => $this->sample_content,
	        'view_title' => $this->sample_content ? get_the_title($this->sample_content) : get_the_title($this->post_id),
	        'wpnonce' => wp_create_nonce( 'mfn-builder-nonce' ),
	        'rev_slider_id' => get_post_meta($this->post_id, 'mfn-post-slider', true),
	        'rev_slider' => Mfn_Builder_Helper::get_sliders('rev'),
					'layer_slider' => Mfn_Builder_Helper::get_sliders('layer'),
	        'adminurl' => admin_url(),
	        'classes' => !empty( $be_classes['builder'] ) ? (array) $be_classes['builder'] : array(),
	        'css_variables' => $be_css_variables,
	        'placeholders' => (object) array(),
	        'is_woocommerce' => function_exists('is_woocommerce') ? true : false,
	        'themepath' => get_template_directory_uri('/'),
	        'autosave' => mfn_opts_get('builder-autosave'),
	        'rooturl' => get_site_url(),
	        'view' => is_admin() ? 'admin' : 'demo',
	        'current_user_roles' => array_values($current_user->roles),
	        'permalink' => $permalink,
	        'current_post_type' => 'post',
	        'current_tax' => 'category',
	        'menus' => mfna_menu(),
	        'preview_post_type' => $preview_post_type,
	        'post_type' => $this->post_type,
	        'post_types' => Mfn_Builder_Helper::get_post_types(),
	        //'taxonomies' => Mfn_Builder_Helper::get_post_types_tax(),
	        'page_object' => $this->get_page_object(),
	        // 'pagedata' => !empty( $page_obj ) ? $page_obj : $this->loadExistedElements($this->post_id),
	        'elements' => $this->loadEmptyElements($this->post_id),
	        'themeoptions' => $this->getThomeOptionsObject(),
	        'mfn_google_fonts' => $google_fonts,

	        'headers' => mfna_templates('header'),
	        'popups' => mfna_templates('popup'),
					'sidemenus' => mfna_templates('sidemenu'),
					'footers' => mfna_templates('footer'),
					'sidebars' => is_array($sidebars) && !is_null($sidebars) ? array_merge(array('' => __('Default', 'mfn-opts')), $sidebars) : null,
					'singleproducts' => mfna_templates('single-product'),
					'single_post_tmpl' => mfna_templates('single-post'),
					'single_portfolio_tmpl' => mfna_templates('single-portfolio'),
	        'presets' => $this->getPresets(true),
	        'cf7' => mfna_cf7(),
	        'builder_type' => $this->template_type ? $this->template_type : 'standard',
	        'shape_dividers' => Mfn_Builder_Helper::get_shape_divider(false, false, 'mfn-uid-'),
	        'be_slug' => apply_filters('betheme_slug', 'mfn'),
	        'page_options' => $this->get_pageoptions(),
	        'pageoptions' => $this->getPageOptionsForm(),
	        'mobileInit' => mfn_opts_get('mobile-menu-initial', 1240),
					'themecolor' => mfn_opts_get('color-theme'),
					'parallax' => mfn_parallax_plugin(),
					'responsive' => intval(mfn_opts_get('responsive', 0)),
					'sidebarSticky' => mfn_opts_get('sidebar-sticky') ? true : false,
					'post_status' => get_post_status($this->post_id),
					'user_roles' => mfna_user_roles(),
	        'featured_image' => get_the_post_thumbnail_url($this->post_id, 'full'),
					'lightbox' => array(
						'disable' => isset($lightboxOptions['disable']) ? true : false,
						'disableMobile' => isset($lightboxOptions['disable-mobile']) ? true : false,
						'title' => isset($lightboxOptions['title']) ? true : false,
					),
					'slider' => array(
						'blog' => intval(mfn_opts_get('slider-blog-timeout', 0)),
						'clients' => intval(mfn_opts_get('slider-clients-timeout', 0)),
						'offer' => intval(mfn_opts_get('slider-offer-timeout', 0)),
						'portfolio' => intval(mfn_opts_get('slider-portfolio-timeout', 0)),
						'shop' => intval(mfn_opts_get('slider-shop-timeout', 0)),
						'slider' => intval(mfn_opts_get('slider-slider-timeout', 0)),
						'testimonials' => intval(mfn_opts_get('slider-testimonials-timeout', 0)),
					),
					
					'livesearch' => array(
						'minChar' => intval(mfn_opts_get('header-search-live-min-characters', 3)),
						'loadPosts' => intval(mfn_opts_get('header-search-live-load-posts', 10)),
						'translation' => array(
							'pages' => $is_translation_on ? mfn_opts_get('translate-livesearch-pages', 'Pages') : __('Pages','betheme'),
							'categories' => $is_translation_on ? mfn_opts_get('translate-livesearch-categories', 'Categories') : __('Categories','betheme'),
							'portfolio' => $is_translation_on ? mfn_opts_get('translate-livesearch-portfolio', 'Portfolio') : __('Portfolio','betheme'),
							'post' => $is_translation_on ? mfn_opts_get('translate-livesearch-posts', 'Posts') : __('Posts','betheme'),
							'products' => $is_translation_on ? mfn_opts_get('translate-livesearch-products', 'Products') : __('Products','betheme'),
						),
					),

					'site_url' => get_site_url(),

					'accessibility' => array(
						'translation' => array(
							'headerContainer' => __('Header container', 'betheme'),
							'toggleSubmenu' => __('Toggle submenu', 'betheme'),
						),
					),

					'prebuilts' => Mfn_Pre_Built_Sections::get_sections(),
					'global_sections' =>  mfna_templates('section'),
					'fonts' =>  mfn_fonts(),
					'global_wraps' =>  mfna_templates('wrap'), 
					'per_page' => get_option( 'posts_per_page'),

					'media_sizes' => array('full' => __('Full size', 'mfn-opts'),'large' => __('Large', 'mfn-opts') .' | '. mfn_get_image_sizes('large', 1),'medium' => __('Medium', 'mfn-opts') .' | '. mfn_get_image_sizes('medium', 1),'thumbnail' => __('Thumbnail', 'mfn-opts') .' | '. mfn_get_image_sizes('thumbnail', 1))
      	)
	    );


		/*$cm_args = wp_enqueue_code_editor(array(
			'autoRefresh' => true,
			'lint' => true,
			'indentUnit' => 2,
			'tabSize' => 2,
			'lineNumbers' => false
		));

	  $codemirror['css']['codeEditor'] = wp_enqueue_code_editor(array(
			'type' => 'text/css', // required for lint
			'codemirror' => $cm_args,
		));

		$codemirror['html']['codeEditor'] = wp_enqueue_code_editor(array(
			'type' => 'text/html', // required for lint
			'codemirror' => $cm_args,
		));

		$codemirror['javascript']['codeEditor'] = wp_enqueue_code_editor(array(
			'type' => 'javascript', // required for lint
			'codemirror' => $cm_args,
		));

		wp_localize_script('mfn-vbscripts', 'mfn_cm', $cm_args);
		wp_enqueue_script( 'mfn-opts-field-textarea-vb', MFN_OPTIONS_URI .'fields/textarea/field_textarea_vb.js', array( 'jquery' ), MFN_THEME_VERSION, true );*/
	
		$cm_args = array(
		    'autoRefresh' => true,
		    'lint'        => true,
		    'indentUnit'  => 2,
		    'tabSize'     => 2,
		    'lineNumbers' => false,
		    'lineWrapping' => true
		);

		$html_editor_settings = wp_enqueue_code_editor(array(
		    'type'       => 'text/html',
		    'codemirror' => $cm_args,
		));

		wp_enqueue_script('code-editor');
		wp_enqueue_style('code-editor');
		wp_enqueue_script('csslint');
		wp_enqueue_script('jshint');

		wp_localize_script('mfn-vbscripts', 'mfn_cm', array(
		    'html' => $html_editor_settings
		));

		wp_enqueue_script('mfn-opts-field-textarea-vb', MFN_OPTIONS_URI .'fields/textarea/field_textarea_vb.js', array('jquery', 'mfn-vbscripts'), MFN_THEME_VERSION, true);

	}

	public function fieldsToJS() {
		// forms html
		$output = 'let renderMfnFields = {';
			$output .= $this->getSectionForm();
			$output .= $this->getWrapForm();
			$output .= $this->getItemsForm();
			$output .= $this->getItemsAdvancedForm();
			if( current_user_can( 'edit_theme_options' ) && mfn_is_registered() ) $output .= $this->getThomeOptionsForm();
			$output .= 'items: '.$this->getEmptyItems(); // extracted from loadEmptyElements()
		$output .= '}';
		return $output;
	}

	public function getThomeOptionsObject() {
		$themeoptions = get_option('betheme');
		return $themeoptions;
	}

	public function getPageOptionsForm(){

		if( isset($this->page_options) && is_iterable($this->page_options) ){
			return $this->page_options['fields'];
		}

		return array();
	}

	public function getThomeOptionsForm() {
		global $MFN_Options;
		$gdpr = new Mfn_Gdpr();

		$to_fields = 'themeoptions_fields: {';

		$output = 'themeoptions: function() { return \'<div class="vb-themeoptions theme-options-tabs">';
		foreach( $MFN_Options->menu as $vb_o=>$vb_opt ) {

			//if( $vb_o == 'translate' ) continue;

			$output .='<div class="vb-to vb-to-'.$vb_o.'">';
				$output .='<div class="vb-to-header"><ul class="vb-to-ul"><li><a class="vb-themeoptions-link-expander" href="#themeoptions-'.htmlspecialchars($vb_o, ENT_QUOTES ).'"><span class="mfn-icon"></span>'.htmlspecialchars($vb_opt['title'], ENT_QUOTES ).'</a><ul class="vb-to-ul vb-to-subul">';
				foreach( $vb_opt['sections'] as $vb_submenu ) {
					$output .='<li class="vb-to-subli vb-to-subli-'.htmlspecialchars($vb_submenu, ENT_QUOTES ).'"><a class="vb-themeoptions-form-link" href="#themeoptions-'.htmlspecialchars($vb_submenu, ENT_QUOTES ).'"><span class="mfn-icon"></span>'.htmlspecialchars($MFN_Options->sections[$vb_submenu]['title'], ENT_QUOTES ).'</a></li>';
				}
				$output .='</ul></li></ul></div>';

				foreach ($vb_opt['sections'] as $vb_sec) {
					$output .='<div class="vb-to-content" id="themeoptions-'.htmlspecialchars($vb_sec, ENT_QUOTES ).'">';
					$to_fields .= '\''.htmlspecialchars($vb_sec, ENT_QUOTES ) .'\': '.json_encode( $MFN_Options->sections[$vb_sec]['fields'] ).',';
					$output .='</div>';
				}

			$output .='</div>';

		}
		$output .= '</div>\';},';

		// GDPR cookies
		if( !mfn_opts_get('gdpr') ){
			$output .= 'gdpr: function() { return \'';
			$output .= '\';},';
		}

		$to_fields .= '},'."\r\n";

		$output .= "\r\n".$to_fields;

		return $output;
	}

	public function getSectionForm(){

		$mfn_fields = new Mfn_Builder_Fields();
		$items = $mfn_fields->get_section();

		$output = 'section: '.json_encode($items).','."\r\n";

		return $output;
	}

	public function getWrapForm(){
		$mfn_fields = new Mfn_Builder_Fields();
		$items = $mfn_fields->get_wrap();

		$output = 'wrap: '.json_encode($items).','."\r\n";

		return $output;
	}

	public function getItemsAdvancedForm(){
		$mfn_fields = new Mfn_Builder_Fields( true );
		$items = $mfn_fields->get_advanced(true);

		$output = 'advanced: '.json_encode($items).','."\r\n";

		return $output;
	}

	public function getItemsForm(){
		$mfn_fields = new Mfn_Builder_Fields(true);
		$output = '';
		$items = $mfn_fields->get_items();

		foreach($items as $w=>$widget){
			if( isset($widget['fields']) ){
				$output .= $w.': '.json_encode($widget['fields']).','."\r\n";
			}elseif( isset($widget['attr']) ){
				$output .= $w.': '.json_encode($widget['attr']).','."\r\n";
			}
		}

		return $output;
	}

	public function getEmptyItems() {

		$return = array();
		$mfn_fields = new Mfn_Builder_Fields();
		$elements = $mfn_fields->get_items();
		//$advanced = $mfn_fields->get_advanced();

		// elements

		foreach( $elements as $e=>$element ){

			$classes = '';
			$params = array();
			$params_content = '';
			$return[$e]['type'] = $element['type'];
			$return[$e]['jsclass'] = $element['type'];
			$return[$e]['title'] = $element['title'];
			$return[$e]['icon'] = str_replace('_', '-', $element['type']);

			//if( $element['type'] == 'map' || $element['type'] == 'lottie' ){
				$params['vb'] = true;
			//}

			if( isset($element['attr']) ) {
				//$element['attr'] = array_merge($element['attr'], $advanced);
				foreach($element['attr'] as $x=>$field) {

					if( !empty($field['std']) ){

						if( isset($field['type']) && strpos($field['id'], 'css_') === false ) {
							if( !empty($field['responsive']) ) {
								$return[$e]['attr'][$field['id']] = array( 'desktop' => $field['std'] );
							}else{
								$return[$e]['attr'][$field['id']] = $field['std'];
							}
						}else if( isset($field['type']) && strpos($field['id'], 'css_') !== false ) {
							if( is_array($field['std']) ){
								$helper = array( 'val' => $field['std'], 'selector' => $field['selector'], 'style' => $field['style'] );

								/*if( !empty($field['selector']) ) $helper['sselector'] = $field['selector'];
								if( !empty($field['style']) ) $helper['sstyle'] = $field['style'];*/
								
								$return[$e]['attr'][$field['id']] = $helper;
							}else if( !empty($field['responsive']) ) {
								$return[$e]['attr'][$field['id']] = array( 'val' => array( 'desktop' => $field['std'] ), 'selector' => $field['selector'], 'style' => $field['style']);
							}else{
								$return[$e]['attr'][$field['id']] = array( 'val' => $field['std'], 'selector' => $field['selector'], 'style' => $field['style']);
							}
						}

						if( mfn_is_blocks('vb') ) {
							$params[$field['id']] = $field['std'];
						}else{
							$params[$field['id']] = $field['std'];
						}
					}else if( !empty($field['vbstd']) ){
						$return[$e]['attr'][$field['id']] = $field['vbstd'];
						$params[$field['id']] = $field['vbstd'];
						$params_content = $field['vbstd'];
					}

				}
			}

			$params['pageid'] = $this->post_id;

			if( isset($element['preview']) ){
				$fun_name = 'sc_'.$element['type'];
				if($element['type'] == 'visual'){
					$return[$e]['html'] = '<div class="mfn-visualeditor-content mfn-inline-editor clearfix">'.$params_content.'</div>';
				}elseif($element['type'] == 'column'){
					$return[$e]['html'] = '<div class="column_attr mfn-inline-editor clearfix">'.$params_content.'</div>';
				}elseif($element['type'] == 'plain_text'){
					$return[$e]['html'] = '<div class="desc">'.$params_content.'</div>';
				}elseif($element['type'] == 'slider_plugin'){
					$return[$e]['html'] = '<div class="mfn-widget-placeholder mfn-wp-revolution"><img class="item-preview-image" src="'.get_theme_file_uri('/muffin-options/svg/placeholders/slider_plugin.svg').'"></div>';
				}elseif($element['type'] == 'sidebar_widget'){
					$return[$e]['html'] .= '<img src="'.get_theme_file_uri( '/muffin-options/svg/placeholders/sidebar_widget.svg' ).'" alt="">';
				}else{
					$output = $fun_name($params);
					$return[$e]['html'] = $output;
				}
			}
		}

		return json_encode($return);
	}

	public function loadEmptyElements($p){
		$return = array();

		$mfn_fields = new Mfn_Builder_Fields();
		$section = $mfn_fields->get_section();
		$wrap = $mfn_fields->get_wrap();

		// section

		$return['section']['icon'] = "section";
		$return['section']['uid'] = "";
		$return['section']['jsclass'] = "section";
		$return['section']['title'] = "Section";

		foreach ($section as $s => $sec) {
			if( !empty($sec['std']) ) {
				if( !empty($sec['responsive']) ) {
					$return['section']['attr'][$sec['id']]['desktop'] = $sec['std'];
				}else{
					$return['section']['attr'][$sec['id']] = $sec['std'];
				}
			}
		}

		// wrap

		$return['wrap']['icon'] = "wrap";
		$return['wrap']['uid'] = "";
		$return['wrap']['size'] = "1/1";
		$return['wrap']['tablet_size'] = "1/1";
		$return['wrap']['mobile_size'] = "1/1";
		$return['wrap']['jsclass'] = "wrap";

		foreach ($wrap as $w => $wra) {
			if( !empty($wra['std']) ){

				if( !empty($wra['responsive']) ){
					if( isset($wra['id']) && strpos($wra['id'], 'css_') !== false ) {
						$return['wrap']['attr'][$wra['id']] = array( 'val' => array( 'desktop' => $wra['std'] ), 'selector' => $wra['selector'], 'style' => $wra['style'] );
					}else{
						$return['wrap']['attr'][$wra['id']]['desktop'] = $wra['std'];
					}
				}else{
					$return['wrap']['attr'][$wra['id']] = $wra['std'];
				}
				
			}
		}

		return $return;

	}

	public function get_page_object() {

		$mfn_page_items = get_post_meta($this->post_id, 'mfn-page-items', true);

		if($mfn_page_items && ! is_array($mfn_page_items)) {
			$mfn_items = unserialize(call_user_func('base'.'64_decode', $mfn_page_items), ['allowed_classes' => false]);
		} else {
			$mfn_items = $mfn_page_items;
		}

		return $mfn_items;
	}

	public function loadExistedElements($mfn_page_items) {

			// moved to js
			return false;

	    $p_id = false;
	    $detect_old_builder = false;

	    if (is_numeric($mfn_page_items)) {
	        $p_id = (int) $mfn_page_items;
	        $mfn_page_items = get_post_meta($p_id, 'mfn-page-items', true);
	    }

	    $mfn_items = $this->decodeBuilderItems($mfn_page_items);

	    if(empty($mfn_items) || !is_array($mfn_items)) {
	      return [];
	    }

	    $flat = [];

	    foreach ($mfn_items as $s => &$section) {

        if (!is_array($section)) {
          continue;
        }

        $this->normalizeSection($section, $detect_old_builder);
        $this->flattenSection($section, $flat, $detect_old_builder);
        
	    }

	    unset($section);

	    if ($detect_old_builder && $p_id) {
	        $this->saveNormalizedBuilderItems($p_id, $mfn_items);
	    }

	    return $flat;
	}

	private function decodeBuilderItems($mfn_page_items) {
	    if ($mfn_page_items && !is_array($mfn_page_items)) {
	        return unserialize(
	            call_user_func('base' . '64_decode', $mfn_page_items),
	            ['allowed_classes' => false]
	        );
	    }

	    return $mfn_page_items;
	}

	private function saveNormalizedBuilderItems($p_id, array $mfn_items) {
	    if ('encode' === mfn_opts_get('builder-storage')) {
	        $new = call_user_func('base' . '64_encode', serialize($mfn_items));
	    } else {
	        $new = $mfn_items;
	    }

	    update_post_meta($p_id, 'mfn-page-items', $new);
	}

	private function normalizeSection(array &$section, &$detect_old_builder) {
	    if (empty($section['uid'])) {
	        $section['uid'] = Mfn_Builder_Helper::unique_ID();
	        $detect_old_builder = true;
	    }

	    // Old builder: section had items directly, without wraps
	    if (!isset($section['wraps']) && !empty($section['items']) && is_iterable($section['items'])) {
	        $section['wraps'] = [[
	            'size'    => '1/1',
	            'uid'     => Mfn_Builder_Helper::unique_ID(),
	            'items'   => $section['items'],
	            'jsclass' => 'wrap',
	            'title'   => 'Wrap',
	            'icon'    => 'wrap',
	        ]];

	        unset($section['items']);
	        $detect_old_builder = true;
	    }

	    if( !isset($section['jsclass']) ){
		    $section['jsclass'] = 'section';
		    $section['title']   = 'Section';
		    $section['icon']    = 'section';
		  }
	}

	private function normalizeWrap(array &$wrap, &$detect_old_builder) {
	    if (empty($wrap['uid'])) {
	        $wrap['uid'] = Mfn_Builder_Helper::unique_ID();
	        $detect_old_builder = true;
	    }

	    if (!empty($wrap['attr']) && is_array($wrap['attr'])) {
	        foreach ($wrap['attr'] as $k => $v) {
	            if (strpos($k, '>') !== false) {
	                $wrap['attr'][str_replace('>', '/', $k)] = $v;
	                unset($wrap['attr'][$k]);
	                $detect_old_builder = true;
	            }
	        }
	    }

	    if( !isset($wrap['jsclass']) ){
		    $wrap['jsclass'] = 'wrap';
		    $wrap['title']   = 'Wrap';
		    $wrap['icon']    = 'wrap';
		  }
	}

	private function normalizeItem(array &$item, &$detect_old_builder) {
	    if (empty($item['uid'])) {
	        $item['uid'] = Mfn_Builder_Helper::unique_ID();
	        $detect_old_builder = true;
	    }

	    if (!empty($item['item_is_wrap'])) {
	        $item['jsclass'] = 'wrap';
	        $item['title']   = 'Wrap';
	        $item['icon']    = 'wrap';
	        return;
	    }

	    if (isset($item['fields']) && is_iterable($item['fields'])) {
	        $item['attr'] = $item['fields'];
	        unset($item['fields']);
	        $detect_old_builder = true;
	    }

	    if (isset($item['type'])) {
	        $item['jsclass'] = $item['type'];
	        $item['title']   = isset($item['title'])
	            ? $item['title']
	            : ucfirst(str_replace('_', ' ', $item['type']));
	        $item['icon']    = str_replace('_', '-', $item['type']);
	    }
	}

	private function flattenSection(array &$section, array &$flat, &$detect_old_builder) {
	    if (!empty($section['wraps']) && is_iterable($section['wraps'])) {
	        foreach ($section['wraps'] as &$wrap) {
	            if (!is_array($wrap)) {
	                continue;
	            }

	            $this->flattenWrap($wrap, $flat, $detect_old_builder);
	        }
	        unset($wrap);
	    }

	    $sectionCopy = $section;
	    unset($sectionCopy['wraps']);
	    $flat[] = $sectionCopy;
	}

	private function flattenWrap(array &$wrap, array &$flat, &$detect_old_builder) {
	    $this->normalizeWrap($wrap, $detect_old_builder);

	    if (!empty($wrap['items']) && is_iterable($wrap['items'])) {
	        foreach ($wrap['items'] as &$item) {
	            if (!is_array($item)) {
	                continue;
	            }

	            $this->flattenItem($item, $flat, $detect_old_builder);
	        }
	        unset($item);
	    }

	    $wrapCopy = $wrap;
	    unset($wrapCopy['items']);
	    $flat[] = $wrapCopy;
	}

	private function flattenItem(array &$item, array &$flat, &$detect_old_builder) {
	    $this->normalizeItem($item, $detect_old_builder);

	    // Nested wrap
	    if (!empty($item['item_is_wrap'])) {
	        if (!empty($item['items']) && is_iterable($item['items'])) {
	            foreach ($item['items'] as &$child) {
	                if (!is_array($child)) {
	                    continue;
	                }

	                $this->flattenItem($child, $flat, $detect_old_builder);
	            }
	            unset($child);
	        }

	        $wrapCopy = $item;
	        unset($wrapCopy['items']);
	        $flat[] = $wrapCopy;
	        return;
	    }

	    $flat[] = $item;
	}


	public function get_pageoptions() {

		$options = array();
		$options['uid'] = 'pageoptions';
		$options['jsclass'] = 'pageoption';
		$devices = array('laptop', 'tablet', 'mobile');

		// options
		if( is_iterable($this->page_options) ) {
			foreach( $this->page_options as $o=>$opt ) {

				if( is_array($opt) ) {
					foreach ($opt as $t => $tval) {
						if( isset($tval['id']) ) {
							$is_old_style = false;

							$opt_value = get_post_meta( $this->post_id, $tval['id'], true );


							if( !empty($tval['old_id']) && empty($opt_value) && !empty( get_post_meta( $this->post_id, $tval['old_id'], true ) ) ) {
								$opt_value = get_post_meta( $this->post_id, $tval['old_id'], true );
								$is_old_style = true;
							}

							if( !empty($opt_value) ) {

								if( strpos($tval['id'], 'css_') !== false ) {

									if( strpos($opt_value, '{') !== false ) {

										$tmp_val = json_decode($opt_value, true);

										if($is_old_style) {
											if( !empty($tval['responsive']) ) {
												$options[$tval['id']]['val']['desktop'] = isset($tmp_val['val']) ? $tmp_val['val'] : $tmp_val;
											}else{
												$options[$tval['id']]['val'] = isset($tmp_val['val']) ? $tmp_val['val'] : $tmp_val;
											}
											
										}else{
											if( !empty($tval['responsive']) ) {
												if( isset($tmp_val['val']['desktop']) ) {
													$options[$tval['id']] = $tmp_val;
												}else if( isset($tmp_val['val']) ) {
													$options[$tval['id']]['val']['desktop'] = $tmp_val['val'];
												}
												
											}else{
												$options[$tval['id']]['val'] = isset($tmp_val['val']) ? $tmp_val['val'] : $tmp_val;
											}
											
										}

									}else if($is_old_style) {
										if( !empty($tval['responsive']) ) {
											$options[$tval['id']]['val']['desktop'] = $opt_value;
										}else{
											$options[$tval['id']]['val'] = $opt_value;
										}
										
									}

									$options[$tval['id']]['selector'] = $tval['selector'];
									$options[$tval['id']]['style'] = $tval['style'];
									
								}elseif( strpos($tval['id'], 'css_') === false ){

									if( !empty($tval['responsive']) ){
										$options[$tval['id']]['desktop'] = $opt_value;
									}else{
										$options[$tval['id']] = $opt_value;
									}
									
								}elseif( isset($tval['std']) ){
									if( !empty($tval['responsive']) ){
										$options[$tval['id']]['desktop'] = $tval['std'];
									}else{
										$options[$tval['id']] = $tval['std'];
									}
									
								}

							}

							if( !empty($tval['responsive']) ){

								foreach($devices as $device){
									$is_old_style = false;
									$opt_value = false;
									$res_key = $tval['id'].'_'.$device;

									$opt_value = get_post_meta( $this->post_id, $res_key, true );

									if( !empty($tval['old_id']) && empty($opt_value) ){
										$res_old_key = $tval['old_id'].'_'.$device;
										$is_old_style = true;
										$opt_value = get_post_meta( $this->post_id, $res_old_key, true );
									}

									if( isset($opt_value) && $opt_value != '' ) {

										if( strpos($tval['id'], 'css_') !== false ) {

											if( strpos($opt_value, '{') !== false ){

												if($is_old_style){
													$options[$tval['id']]['val'][$device] = json_decode($opt_value, true);
												}else{
													$tmp_val = json_decode($opt_value, true);

													if( isset($tmp_val['val'][$device]) ){
														$options[$tval['id']] = $tmp_val;
													}else if( isset($tmp_val['val']) ){
														$options[$tval['id']]['val'][$device] = $tmp_val['val'];
													}
												}

											}else if($is_old_style){
												$options[$tval['id']]['val'][$device] = $opt_value;
											}

											$options[$tval['id']]['selector'] = $tval['selector'];
											$options[$tval['id']]['style'] = $tval['style'];


										}elseif( strpos($tval['id'], 'css_') === false ){
											$options[$tval['id']][$device] = $tval['std'];
											
										}elseif( isset($tval['std']) ){
											$options[$tval['id']][$device] = $tval['std'];
										}

									}

								}

							}

						}
					}
				}
			}
		}

		return $options;

	}

	public function sizes($size){
		$classes = array(
  			'divider' => 'divider',
  			'1/6' => 'one-sixth',
  			'1/5' => 'one-fifth',
  			'1/4' => 'one-fourth',
  			'1/3' => 'one-third',
  			'2/5' => 'two-fifth',
  			'1/2' => 'one-second',
  			'3/5' => 'three-fifth',
  			'2/3' => 'two-third',
  			'3/4' => 'three-fourth',
  			'4/5' => 'four-fifth',
  			'5/6' => 'five-sixth',
  			'1/1' => 'one'
  		);

  		return $classes[$size];
	}


	public function mfn_load_sidebar(){

		global $wpdb;

		if( !empty( get_post_meta($this->post_id, '_elementor_edit_mode', true) ) ){
			delete_post_meta($this->post_id, '_elementor_edit_mode');
		}

		if( !get_user_meta($this->user, 'rich_editing', true) || get_user_meta($this->user, 'rich_editing', true) == 'false' ){
			update_user_meta($this->user, 'rich_editing', 'true');
		}

		remove_action( 'admin_print_styles', 'print_emoji_styles' );

		$this->mfn_required_scripts();
		$this->mfn_required_styles();

		$this->options = Mfn_Builder_Helper::get_options();

		$builder_class = array();
		$builder_class[] = 'mfn-vb-'.$this->post_type;

		if($this->post_type == 'template' && !empty($this->template_type)){
			$builder_class[] = 'mfn-vb-tmpl-'.$this->template_type;

			if($this->template_type == 'header') {
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_beheader_class') );
			}else if($this->template_type == 'megamenu') {
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_bemegamenu_class') );
			}else if($this->template_type == 'cart') {
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_becart_class') );
			}else if($this->template_type == 'footer') {
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_befooter_class') );
			}else if($this->template_type == 'popup') {
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_bepopup_class') );
			}else if( $this->template_type === 'section' ){
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_beglobalsections_class') );
			}else if( $this->template_type === 'wrap' ){
				add_filter( 'admin_body_class', array( $this, 'mfn_add_admin_beglobalwraps_class') );
			}
		}

		if( is_array( $this->options ) ){
			foreach( $this->options as $option_id => $option_val ){
				if( $option_val == "1" ){
					$builder_class[] = $option_id;
				}elseif( $option_val != "0" ){
					$builder_class[] = $option_val;
				}
			}
		}

		if( (!empty($this->options['user-interface']) && $this->options['user-interface'] == 'default') || (!empty($_GET['ui']) && $_GET['ui'] == 'default') ) $this->ui_mode = 'default';

		if( is_admin() ){
			require_once(get_theme_file_path('/visual-builder/visual-builder-header.php'));
		}else{
			require_once(get_theme_file_path('/visual-builder/bebuilder-demo-header.php'));
		}

		do_action('mfn_yoast');

		$detectUiTheme = false;

		if( in_array( 'mfn-ui-auto', $builder_class) || ( !in_array( 'mfn-ui-auto', $builder_class) && !in_array( 'mfn-ui-dark', $builder_class) && !in_array( 'mfn-ui-light', $builder_class) ) ) {
			$builder_class[] = 'mfn-ui-auto';
			$detectUiTheme = true;
		}

		$builder_class[] = 'mfn-bebuilder-'.( is_admin() ? 'admin' : 'demo' );

		if( function_exists('is_woocommerce') ) {
			$builder_class[] = 'woocommerce-active';
		}

		$builder_class[] = 'modalbox-card-active-content';

		if( $this->view == 'demo' && !empty($_GET['ui']) && $_GET['ui'] == 'blocks' ) $builder_class[] = 'builder-blocks';

		$builder_class = implode( ' ', $builder_class );

		echo '<div class="frameOverlay"></div><div id="mfn-visualbuilder" class="mfn-ui mfn-visualbuilder '.esc_attr( $builder_class ).'" data-tutorial="'. apply_filters('betheme_disable_support', '0') .'">';

		$oMenus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		if( $detectUiTheme ) echo "<script>var mfnuicont = document.getElementById('mfn-visualbuilder'); if( mfnuicont.classList.contains('mfn-ui-auto') && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ){mfnuicont.classList.add('mfn-ui-dark');}</script>";

		require_once(get_theme_file_path('/visual-builder/partials/preloader.php'));

		echo '<div class="mfn-contextmenu mfn-items-list-contextmenu"><ul><li><a href="#" data-action="love-it"><span class="mfn-icon mfn-icon-star"></span><span class="label">Add to favourites</span></a></li></ul></div>';

		require_once(get_theme_file_path('/visual-builder/partials/navigator.php'));

		if( is_admin() ) {

			$edit_lock = wp_check_post_lock($this->post_id);

			if( $edit_lock && $edit_lock != get_current_user_id() ) {
				require_once(get_theme_file_path('/visual-builder/partials/locker.php'));
			}else{
				wp_set_post_lock($this->post_id);
			}

		}

			if( $this->ui_mode == 'dev' ){
				$this->dev_ui_sidebar();
			}else{
				$this->default_ui_sidebar();
			}

		// introduction
	    require_once(get_theme_file_path('/visual-builder/partials/introduction.php'));

	    // shortcuts
    require_once(get_theme_file_path('/visual-builder/partials/shortcuts.php'));

		// dynamic data info
	  require_once(get_theme_file_path('/visual-builder/partials/dynamic-data.php'));

    // modal icons
		require_once(get_theme_file_path('/visual-builder/partials/modal-icons.php'));

		// modal shortcodes
		require_once(get_theme_file_path('/visual-builder/partials/modal-shortcodes.php'));

		// modal dynamic data
		require_once(get_theme_file_path('/visual-builder/partials/modal-dynamic-data.php'));

		// modal conditional logic
		require_once(get_theme_file_path('/visual-builder/partials/modal-conditional-logic.php'));

		if( $this->post_type == 'template' ) require_once(get_theme_file_path('/visual-builder/partials/modal-conditions.php'));

	  if( $this->post_type == 'template' &&  in_array($this->template_type, array('cart', 'checkout', 'thanks', 'search')) ) require_once(get_theme_file_path('/visual-builder/partials/modal-cart-confirmation.php'));

	  echo '</div>';

	    $theme_disable = mfn_opts_get('theme-disable');
	    if ( !isset($theme_disable['custom-icons']) ) Mfn_Post_Type_Icons::load_icons();
	    wp_enqueue_script( 'mfn-opts-field-upload', MFN_OPTIONS_URI .'fields/upload/vb_field_upload.js', array( 'jquery' ), MFN_THEME_VERSION, true );

	    if( is_admin() ){
	    	require_once(get_theme_file_path('/visual-builder/visual-builder-footer.php'));
	    }else{
			require_once(get_theme_file_path('/visual-builder/bebuilder-demo-footer.php'));
		}

		$css_variables = !empty(get_option('be_css_variables')) ? json_decode(get_option('be_css_variables'), true) : array();

			
			echo '<style class="mfn-css-be-variables">';
			if( !empty($css_variables)) {
			echo 'body{';
			  	foreach($css_variables as $cv) {
			  		echo $cv['title'].':'.$cv['value'].';'."\r\n";
			  	}
			  echo '}';
			  }
			echo '</style>';

	}



	public function default_ui_sidebar() {

		// start sidebar
	    echo '<div class="sidebar-wrapper" id="mfn-vb-sidebar">';

	    echo '<div id="mfn-sidebar-resizer"></div>';
	    echo '<div id="mfn-sidebar-switcher"></div>';

	  // sidebar left
	  require_once(get_theme_file_path('/visual-builder/partials/sidebar-menu.php'));

	  // end sidebar left

	  // start sidebar panel
	    echo '<div class="sidebar-panel">';

	    // start sidebar header

	  require_once(get_theme_file_path('/visual-builder/partials/sidebar-header.php'));

	  // end sidebar header

	  // items panel
	    echo '<div class="sidebar-panel-content">';

	    // start items panel
	    require_once(get_theme_file_path('/visual-builder/partials/sidebar-widgets.php'));

	    // end items panel

	   	// start pre build
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-prebuilds.php'));
	   	// end pre build

		// start globals panel
		require_once(get_theme_file_path('/visual-builder/partials/sidebar-globals.php'));
		// end global panel

	   	if( is_admin() ){
		    // start revision
		    require_once(get_theme_file_path('/visual-builder/partials/sidebar-revisions.php'));
		    // end revisions
		}

	    if( is_admin() ){
		    // start export/import
		    require_once(get_theme_file_path('/visual-builder/partials/sidebar-export-import.php'));
		    // end export/import
		}

	    // start settings
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-settings.php'));
	   	// end settings

	   	// start options
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-options.php'));
	   	// end options

	   	// start themeoptions
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-themeoptions.php'));
	   	// end themeoptions

	   	// start yoast
	   	if ( defined( 'WPSEO_FILE' ) ) require_once(get_theme_file_path('/visual-builder/partials/sidebar-yoast.php'));
	   	// end yoast

	   // start edit form

	   echo '<div class="panel panel-edit-item" style="display: none;"><div class="mfn-form"></div></div>';
       // end edit form

        echo '</div>';
        // start footer
        require_once(get_theme_file_path('/visual-builder/partials/sidebar-footer.php'));

        // end panel
        echo '</div>';
        // end sidebar
        echo '</div>';


        // iframe

        echo '<div id="mfn-preview-wrapper-holder" class="preview-wrapper">';
        // preview toolbar
        require_once(get_theme_file_path('/visual-builder/partials/preview-toolbar.php'));
        //echo '<pre style="line-height: 1.6em; display:none;">';print_r($mfn_items);echo '</pre>';
        echo '<div id="mfn-preview-wrapper"></div>';

		echo '</div>';

	}







	public function dev_ui_sidebar() {

		// start sidebar
	    echo '<div class="sidebar-wrapper" id="mfn-vb-sidebar">';

	    echo '<div id="mfn-sidebar-resizer"></div>';
	    echo '<div id="mfn-sidebar-switcher"></div>';

	  // sidebar left
	  //require_once(get_theme_file_path('/visual-builder/partials/sidebar-menu.php'));

	  // end sidebar left

	  // start sidebar panel
	    echo '<div class="sidebar-panel">';

	    // start sidebar header

	  require_once(get_theme_file_path('/visual-builder/partials/sidebar-header.php'));

	  // end sidebar header

	  // items panel
	    echo '<div class="sidebar-panel-content">';

	    // start items panel
	    require_once(get_theme_file_path('/visual-builder/partials/sidebar-widgets.php'));

	    // end items panel

	   	// start pre build
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-prebuilds.php'));
	   	// end pre build

		// start globals panel
		require_once(get_theme_file_path('/visual-builder/partials/sidebar-globals.php'));
		// end global panel

	   	if( is_admin() ){
		    // start revision
		    require_once(get_theme_file_path('/visual-builder/partials/sidebar-revisions.php'));
		    // end revisions
		}

	    if( is_admin() ){
		    // start export/import
		    require_once(get_theme_file_path('/visual-builder/partials/sidebar-export-import.php'));
		    // end export/import
		}

	    // start settings
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-settings.php'));
	   	// end settings

	   	// start options
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-options.php'));
	   	// end options

	   	// start themeoptions
	   	require_once(get_theme_file_path('/visual-builder/partials/sidebar-themeoptions.php'));
	   	// end themeoptions

	   	// start yoast
	   	if ( defined( 'WPSEO_FILE' ) ) require_once(get_theme_file_path('/visual-builder/partials/sidebar-yoast.php'));
	   	// end yoast

	   // start edit form

	   echo '<div class="panel panel-edit-item" style="display: none;"><div class="mfn-form"></div></div>';
       // end edit form

        echo '</div>';
        // start footer
        //require_once(get_theme_file_path('/visual-builder/partials/sidebar-footer.php'));

        // end panel
        echo '</div>';
        // end sidebar
        echo '</div>';


        // dev toolbar
        require_once(get_theme_file_path('/visual-builder/partials/dev-toolbar.php'));

        echo '<div id="mfn-preview-wrapper-holder" class="preview-wrapper">';
        // dev toolbar
        require_once(get_theme_file_path('/visual-builder/partials/dev-toolbar.php'));
        echo '<div id="mfn-preview-wrapper"></div>';

		echo '</div>';

		// another pages
	  require_once(get_theme_file_path('/visual-builder/partials/modal-another-page.php'));

	}

























































	public function getPresets( $both = false ){

		$return = array();

		if( $both ){
			$local = array();
			$jsonfile = get_theme_file_path('/visual-builder/assets/presets.json');
			if( file_exists($jsonfile) ){
				$local = file_get_contents( $jsonfile );
				if( !empty($local) ) $return = json_decode($local);
			}
		}

		$get_opt = get_option('mfn-presets');

		if( !empty($get_opt) ) {
			if( count($return) > 0 ){
				$return = array_merge( $return, json_decode( $get_opt ) ?? [] );
			}else{
				$return = json_decode( $get_opt ) ?? [];
			}
		}

		return $return;
	}

	public function wrapHtml($item_id, $size, $order, $sizeclass){
		$mfn_helper = new Mfn_Builder_Helper();
		$html = '<div data-title="Wrap" data-icon="mfn-icon-wrap" data-order="'.$order.'" data-uid="'.$item_id.'" data-desktop-size="'.$size.'" data-tablet-size="'.$size.'" data-mobile-size="1/1" class="blink wrap mcb-wrap mcb-wrap-new vb-item vb-item-wrap mcb-wrap-'.$item_id.' '.$sizeclass.' tablet-'.$sizeclass.' mobile-one clearfix"><div class="mfn-drag-helper mfn-dh-before placeholder-wrap"></div><div class="mfn-drag-helper mfn-dh-after placeholder-wrap"></div><div class="mcb-wrap-inner empty">'.$mfn_helper->wrapTools($size).'<div class="mfn-wrap-new"><a href="#" class="mfn-item-add mfn-btn btn-icon-left btn-small mfn-btn-blank2"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-add"></span>Add element</span></a></div></div></div>';

		return $html;
	}

	/**
	 * Builder data file - remove
	 */

	public static function removeBeDataFile(){
		if( file_exists( self::bebuilderFilePath()) ) wp_delete_file( self::bebuilderFilePath());

		update_option('betheme_form_uid', Mfn_Builder_Helper::unique_ID());

		return true;
	}

	public static function bebuilderFilePath( $uri = false ){
		$bebuilder_items_file = '/visual-builder/assets/js/forms/bebuilder-'.MFN_THEME_VERSION.'.js';

    $bebuilder_items_path = get_template_directory() . $bebuilder_items_file;

    if( $uri ){
    	$bebuilder_items_path = get_template_directory_uri() . $bebuilder_items_file;
    }

    return $bebuilder_items_path;
	}
}
