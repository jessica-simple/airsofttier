<?php
/**
 * Muffin Builder | Front
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

if( ! class_exists('Mfn_Builder_Front') )
{
  class Mfn_Builder_Front {

    public $post_id = false;
    public $content_field = false; // use post field instead of the_content()
    public $template_type = false;
    public static $is_bebuilder = false;

    public static $updated_version = false;

    public static $post_id2 = false;

    public static $is_tabber = false;
    public static $is_ql_section = false;
    public static $is_ql_wrap = false;

		public $blocks_fields = false;

    /* For Dynamic Data */

    public static $item_type = false;
    public static $item_id = false;

    public static $item_obj = false;

		public $classes = array(
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

		public $tablet_classes = array(
			'divider' => '',
			'1/6' => 'tablet-one-sixth',
			'1/5' => 'tablet-one-fifth',
			'1/4' => 'tablet-one-fourth',
			'1/3' => 'tablet-one-third',
			'2/5' => 'tablet-two-fifth',
			'1/2' => 'tablet-one-second',
			'3/5' => 'tablet-three-fifth',
			'2/3' => 'tablet-two-third',
			'3/4' => 'tablet-three-fourth',
			'4/5' => 'tablet-four-fifth',
			'5/6' => 'tablet-five-sixth',
			'1/1' => 'tablet-one'
		);

		public $laptop_classes = array(
			'divider' => '',
			'1/6' => 'laptop-one-sixth',
			'1/5' => 'laptop-one-fifth',
			'1/4' => 'laptop-one-fourth',
			'1/3' => 'laptop-one-third',
			'2/5' => 'laptop-two-fifth',
			'1/2' => 'laptop-one-second',
			'3/5' => 'laptop-three-fifth',
			'2/3' => 'laptop-two-third',
			'3/4' => 'laptop-three-fourth',
			'4/5' => 'laptop-four-fifth',
			'5/6' => 'laptop-five-sixth',
			'1/1' => 'laptop-one'
		);

		public $mobile_classes = array(
			'divider' => '',
			'1/6' => 'mobile-one-sixth',
			'1/5' => 'mobile-one-fifth',
			'1/4' => 'mobile-one-fourth',
			'1/3' => 'mobile-one-third',
			'2/5' => 'mobile-two-fifth',
			'1/2' => 'mobile-one-second',
			'3/5' => 'mobile-three-fifth',
			'2/3' => 'mobile-two-third',
			'3/4' => 'mobile-three-fourth',
			'4/5' => 'mobile-four-fifth',
			'5/6' => 'mobile-five-sixth',
			'1/1' => 'mobile-one'
		);

		public $be_classes = array();

    /**
     * Constructor
     */

    public function __construct($post_id, $content_field = false) {

			if( mfn_is_blocks() ) {
				$this->blocks_fields = new Mfn_Builder_Fields( null, 'items' );
			}

			$this->post_id = $post_id;
			self::$post_id2 = $post_id;
 			$this->content_field = $content_field;
 			self::$updated_version = !empty(get_option('mfn-css-db-update')) && get_option('mfn-css-db-update') == '1' ? true : false;

 			if( get_post_type($post_id) == 'template' ) $this->template_type = get_post_meta($post_id, 'mfn_template_type', true);

 			self::$is_bebuilder = apply_filters('bebuilder_preview', true);

 			if( wp_doing_ajax() ) self::$is_bebuilder = true;

 			$get_be_classes = !empty(get_option('be_classes')) ? json_decode( get_option('be_classes'), true ) : array();

 			if( !empty($get_be_classes['builder']) && count($get_be_classes['builder']) > 0 ){
 				foreach ($get_be_classes['builder'] as $bc) {
 					if( !empty($bc['attr']) ) $this->be_classes[$bc['uid']] = $bc['attr'];
 				}
 			}

 			/*echo '<pre>';
 			print_r($this->be_classes);
 			echo '</pre>';*/

    }

  	/**
  	 * Show WordPress Editor Content
  	 */

  	public function the_content() {

			// FIX: Elementor - prevent showing first post content on blog page

			if( ( 'post' == get_post_type() ) && ( ! is_singular() ) ) {
				return false;
			}

			// single product - hide wp editor content

			if( function_exists('is_product') && is_product() && mfn_opts_get('shop-hide-content') ) {
				return false;
			}

      // check if editor content exists

			$content = get_post_field( 'post_content', $this->post_id );
  		$class = $content ? 'has_content' : 'no_content' ;
			$is_elementor = mfn_is_elementor( $this->post_id ) ? 'is-elementor' : false;

  		// output -----

  		echo '<section class="section mcb-section the_content '. esc_attr( $class ) .'">';
  			if ( ! get_post_meta( $this->post_id, 'mfn-post-hide-content', true ) ) {
  				echo '<div class="section_wrapper">';
  					echo '<div class="the_content_wrapper '. esc_attr( $is_elementor ) .'">';
  						if ( $this->content_field ) {
  							echo apply_filters( 'the_content', $content );
  						} else {
  							the_content();
  						}
  					echo '</div>';
  				echo '</div>';
  			}
  		echo '</section>';

  	}

		/**
  	 * Enqueue BeBuilder local style css
		 * @param $skip_preview - skip templates preview on another pages preview
  	 */

		public function enqueue_local_style( $enqueue = true, $skip_preview = false ) {

			if( mfn_is_blocks() ){
				return;
			}

			$path = '';
			$handle = 'mfn-post-local-styles';
			$preview_string = apply_filters('betheme_slug', 'mfn').'-preview';

			if( !self::$is_bebuilder ){
				$classes_path = wp_upload_dir()['basedir'] . '/betheme/css/be_classes.css';

				if( file_exists($classes_path) ){

						$classes_handle = 'mfn-be-classes-styles-' . time();
						$classes_url = wp_upload_dir()['baseurl'] . '/betheme/css/be_classes.css';
						wp_enqueue_style($classes_handle, $classes_url, false, time(), 'all');
				}
			}

			if( ( ! empty($_GET[$preview_string]) || ! empty($_GET['preview']) ) && get_post_meta($this->post_id, 'mfn-builder-preview-local-style') && ! $skip_preview ){

				// preview

				$path = '/betheme/css/post-'. $this->post_id .'-preview.css';
				$handle = 'mfn-builder-preview-local-style-'. Mfn_Builder_Helper::unique_ID();

			}else{

				$css_file_path = '/betheme/css/post-'. $this->post_id .'.css';
				$dir = wp_upload_dir()['basedir'] . $css_file_path;

				if( get_post_meta($this->post_id, 'mfn-page-local-style', true) && file_exists($dir) ){

					// frontend

					$path = '/betheme/css/post-'. $this->post_id .'.css';
					$handle = 'mfn-post-local-styles-'. $this->post_id . time();

				} elseif( defined( 'ICL_SITEPRESS_VERSION' ) ){

					// FIX: WPML | Use native language page styles

					$default_language = apply_filters( 'wpml_default_language', null );
					$post_type = get_post_type($this->post_id);

					$native_id = apply_filters( 'wpml_object_id', $this->post_id, $post_type, false, $default_language );

					if( get_post_meta($native_id, 'mfn-page-local-style', true) ){
						$path = '/betheme/css/post-'. $native_id .'.css';
						$handle = 'mfn-post-local-styles-WPML-'. $this->post_id .'-'. Mfn_Builder_Helper::unique_ID();
					}

				}

			}

			if( $path ) {

				if( $enqueue ) {

					$url = wp_upload_dir()['baseurl'] . $path;

					if( is_ssl() ){
						$url = str_replace( 'http://', 'https://', $url );
					}

					wp_enqueue_style($handle, $url, false, time(), 'all');

				} else {

					$path = wp_upload_dir()['basedir'] . $path;

					if( file_exists($path) ){
						return $path;
					}

				}

			}

		}

		/**
  	 * Show BeBuilder Content
  	 */

    public function show( $items = false, $vbtoolsoff = false ) {

    	$preview_string = apply_filters('betheme_slug', 'mfn').'-preview';

    	//echo get_the_title($this->post_id).' - '.$this->post_id;

			// GET builder items

  		if( isset( $items ) && is_array( $items ) ){
				// ajax
  			$mfn_items = $items;
  		} elseif( ( (empty($_GET['preview']) && empty($_GET[$preview_string]) ) || ( self::$is_bebuilder && !empty($_GET['preview'] ) ) ) ) {
				$mfn_items = get_post_meta($this->post_id, 'mfn-page-items', true);
			} else {
				if( get_post_type($this->post_id) == 'template' ){
					$mfn_items = get_post_meta($this->post_id, 'mfn-page-items', true);
				}else{
					$mfn_items = get_post_meta($this->post_id, 'mfn-builder-preview', true);
				}
			}

  		// FIX | Muffin builder 2 compatibility

  		if ( $mfn_items && ! is_array( $mfn_items ) ) {
  			$mfn_items = unserialize(call_user_func('base'.'64_decode', $mfn_items), ['allowed_classes' => false]);
  		}

			// apply filters

			$mfn_items = apply_filters( 'mfn_builder_items_show', $mfn_items );

			// debug

			// if( !$this->template_type ){
			// 	echo '<pre>';
			// 	print_r( $mfn_items );
			// 	echo '</pre>';
			// }

			// exit;

			// CSS local styles

			if( $vbtoolsoff || ( empty( $_GET['visual'] ) && ( !mfn_opts_get('local-styles-location') || ( $this->template_type && in_array($this->template_type, array('megamenu', 'footer', 'popup', 'sidemenu')) ) ) ) ) {

				if( $this->template_type != 'header' ) {
					$this->enqueue_local_style();
				}

			}

  		// WordPress Editor | before builder

  		if ( 1 == mfn_opts_get('display-order') && ( !isset($items) || get_post_type( $this->post_id ) != 'template' ) ) {
  			$this->the_content();
  		}

  		// Muffin Builder

  		$main_div_classes = array('mfn-builder-content');

  		if( !$items ) {
	  		if( $this->template_type ) {
	  			
	  			$main_div_classes[] = 'mfn-'.$this->template_type.'-tmpl-builder';

	  			if( $this->template_type == 'cart' ) {
	  				$main_div_classes[] = 'woocommerce';
	  				if ( function_exists('is_woocommerce') && is_cart() && WC()->cart->is_empty() ) {$main_div_classes[] = 'mfn-cart-tmpl-empty';}else{$main_div_classes[] = 'mfn-cart-tmpl-not-empty';}
	  				echo '<div class="'.implode(' ', $main_div_classes).'">';
	  			} elseif( $this->template_type == 'thanks' ) {
	  				$main_div_classes[] = 'woocommerce-order';
	  				echo '<div class="woocommerce">';
	  				echo '<div class="'.implode(' ', $main_div_classes).'">';
	  			}elseif( $this->template_type == 'megamenu' ) {
	  				$main_div_classes[] = 'container';
	  				echo '<div class="'.implode(' ', $main_div_classes).'">';
	  			}elseif( $this->template_type == 'popup' ) {
	  				if( self::$is_bebuilder ) $main_div_classes[] = 'mfn-popup-tmpl-content';
	  				echo '<div class="'.implode(' ', $main_div_classes).'">';
	  				if( self::$is_bebuilder ) echo '<a href="#" class="exit-mfn-popup exit-mfn-popup-abs">&#10005;</a><div class="mfn-popup-tmpl-content-wrapper">';
	  			}else{

	  				if( $this->template_type == 'archive-product' ) {
	  					if( !empty( get_post_meta($this->post_id, 'mfn-shop-list-results-count', true) ) ) $main_div_classes[] = 'mfn-shop-list-results-count-'.get_post_meta($this->post_id, 'mfn-shop-list-results-count', true);
	  					if( !empty( get_post_meta($this->post_id, 'mfn-shop-list-layout', true) ) ) $main_div_classes[] = 'mfn-shop-list-layout-'.get_post_meta($this->post_id, 'mfn-shop-list-layout', true);
	  					if( !empty( get_post_meta($this->post_id, 'mfn-shop-list-perpage', true) ) ) $main_div_classes[] = 'mfn-shop-list-perpage-'.get_post_meta($this->post_id, 'mfn-shop-list-perpage', true);
	  					if( !empty( get_post_meta($this->post_id, 'mfn-shop-list-active-filters', true) ) ) $main_div_classes[] = 'mfn-shop-list-active-filters-'.get_post_meta($this->post_id, 'mfn-shop-list-active-filters', true);
	  					if( !empty( get_post_meta($this->post_id, 'mfn-shop-list-sorting', true) ) ) $main_div_classes[] = 'mfn-shop-list-sorting-'.get_post_meta($this->post_id, 'mfn-shop-list-sorting', true);
	  				}

	  				echo '<div data-id="'.$this->post_id.'" class="'.implode(' ', $main_div_classes).'">';
	  				if( $this->template_type == 'sidemenu' ) echo '<a href="#" class="mfn-sidemenu-closebutton">&#10005;</a>';
	  			}

	  		}else{
	  			 //echo '<div class="mfn-builder-content mfn-default-content-buider">';
	  			$main_div_classes[] = 'mfn-default-content-buider';
	  			echo '<div data-id="'.$this->post_id.'" class="'.implode(' ', $main_div_classes).'">';
	  		}

	  	}

  		if ( post_password_required() && !self::$is_bebuilder && ( !$this->template_type || !in_array($this->template_type, array('header', 'footer', 'megamenu', 'sidemenu')) ) ) {

  			// password protected page

  			if ( get_post_meta( $this->post_id, 'mfn-post-hide-content', true ) ) {
  				echo '<div class="section the_content">';
  					echo '<div class="section_wrapper">';
  						echo '<div class="the_content_wrapper">';
  							echo get_the_password_form();
  						echo '</div>';
  					echo '</div>';
  				echo '</div>';
  			}

			} elseif ( function_exists('wc_memberships') && ( ! current_user_can('wc_memberships_view_restricted_post_content', $this->post_id) ) ) {

				// do not show builder if wc memberships active do not allow current user

  		} elseif ( ! empty($mfn_items) && is_array($mfn_items) ) {

  			// SECTIONS -----
  			$this->show_sections($mfn_items, $vbtoolsoff);

  		}

  		if( !$items ) echo '</div>';
  		if( !$items && $this->template_type == 'thanks' ) echo '</div>'; // end thanks tmpl
  		if( self::$is_bebuilder && $this->template_type && $this->template_type == 'popup' ) echo '</div>';

  		// WordPress Editor | after builder

  		if ( 0 == mfn_opts_get('display-order') && ( !isset($items) || get_post_type( $this->post_id ) != 'template' ) ) {
  			$this->the_content();
  		}


  		$this->post_id = false;
			self::$post_id2 = false;

  	}

  	public function show_sections($mfn_items, $vbtoolsoff = false) {

  		if( !is_array($mfn_items) ) return;

  		foreach ($mfn_items as $s => $section) {

  			if( class_exists('Sitepress') && !empty($section['mfn_global_section_id'] ) ) {
          $section['mfn_global_section_id'] = apply_filters( 'wpml_object_id', $section['mfn_global_section_id'], get_post_type($section['mfn_global_section_id']) , TRUE  );
        }

  			$section_class = [];

	  			if( !empty( $section['attr']['conditions'] ) ) {
		  			if( !self::$is_bebuilder ){
			  			$mfnConditions = new MfnConditionalLogic();
			  			if( !$mfnConditions->verify( $section['attr']['conditions'] ) ){
			  				continue;
			  			}
			  		}else{
			  			$section_class[] = 'mfn-conditional-logic';
			  		}
		  		}

		  		if( !empty( $section['be_classes']) && is_array($section['be_classes']) ){
  					foreach($section['be_classes'] as $bc) {
  						$section_class[] = $bc;
  						if( isset($this->be_classes[$bc]) ){
  							//$section_class = array_merge($section_class, $this->get_classes($this->be_classes[$bc], 'section', true));
  							if( isset($section['attr']['width_switcher']) && $section['attr']['width_switcher'] == 'default' ) unset($section['attr']['width_switcher']);
  							$section['attr'] = array_merge($this->be_classes[$bc], $section['attr']);
  						}
  					}
  				}

					// unique ID

					if( empty( $section['uid'] ) ) {
						$section['uid'] = Mfn_Builder_Helper::unique_ID();
					}

					if( !empty($section['mfn_global_section_id']) && ( get_post_status($section['mfn_global_section_id']) != 'publish' || empty(get_post_meta($section['mfn_global_section_id'], 'mfn-page-items', true)) ) ) continue;
  				if( $this->template_type && $this->template_type == 'header' && !empty($section['ver']) && $section['ver'] == 'header-sticky' && !empty( get_post_meta($this->post_id, 'header_sticky', true) ) && get_post_meta($this->post_id, 'header_sticky', true) == 'disabled' ) continue;
  				if( $this->template_type && $this->template_type == 'header' && !empty($section['ver']) && $section['ver'] == 'header-mobile' && !empty( get_post_meta($this->post_id, 'header_mobile', true) ) && get_post_meta($this->post_id, 'header_mobile', true) == 'disabled' ) continue;

					$inner_section_class_uid = 'mcb-section-inner-'. $section['uid'] .'';

					$refresh_content = '';
					$global_section_id = '';

					// be global sections
					if( !empty($section['mfn_global_section_id']) && is_numeric($section['mfn_global_section_id']) && get_post_status($section['mfn_global_section_id']) == 'publish' ){
						$refresh_content = get_post_meta($section['mfn_global_section_id'], 'mfn-page-items', true);

						if( !is_array($refresh_content) ) {
							$refresh_content = unserialize( call_user_func('base'.'64_decode', $refresh_content), ['allowed_classes' => false] );
							/*echo '<pre>';
							print_r($refresh_content);
							echo '</pre>';*/
							if( !empty( $refresh_content[0]['attr']['conditions'] ) ) {
				  			if( !self::$is_bebuilder ){
					  			$mfnConditions = new MfnConditionalLogic();
					  			if( !$mfnConditions->verify( $refresh_content[0]['attr']['conditions'] ) ){
					  				continue;
					  			}
					  		}else{
					  			$section_class[] = 'mfn-conditional-logic';
					  		}
				  		}
						}

						//refresh content
						$section['attr']  = $refresh_content[0]['attr'];
						$section['wraps'] = $refresh_content[0]['wraps'];
						$section_class[] = 'mfn-global-section';

						if( !empty( $refresh_content[0]['be_classes']) && is_array($refresh_content[0]['be_classes']) ){
	  					foreach($refresh_content[0]['be_classes'] as $bc) {
	  						$section_class[] = $bc;
	  						if( isset($this->be_classes[$bc]) ){
	  							//$section_class = array_merge($section_class, $this->get_classes($this->be_classes[$bc], 'section', true));
	  							if( isset($refresh_content[0]['attr']['width_switcher']) && $refresh_content[0]['attr']['width_switcher'] == 'default' ) unset($refresh_content[0]['attr']['width_switcher']);
	  							if( isset($refresh_content[0]['attr']['height_switcher']) && $refresh_content[0]['attr']['height_switcher'] == 'default' ) unset($refresh_content[0]['attr']['height_switcher']);
	  							$section['attr'] = array_merge($this->be_classes[$bc], $section['attr']);
	  						}
	  					}
	  				}

						$global_section_id = ' data-mfn-global="' . $section['mfn_global_section_id'] .'"';

						//styles
						if( !mfn_is_blocks() ){
							$path = wp_upload_dir()['baseurl'] .'/betheme/css/post-'. $section['mfn_global_section_id'] .'.css';
							wp_enqueue_style('mfn-global-section-styles-'. Mfn_Builder_Helper::unique_ID(), $path, false, time(), 'all');
						}
					}

					$section_id = false;
					$parallax = false;
					$closeable = false;

  				// hidden sections

  				if ( ! empty( $section['attr']['hide'] ) ) {

						// visual builder

            if( wp_doing_ajax() || self::$is_bebuilder ){
              $section_class[] = 'hide';
            } else {
              continue;
            }

					}

					//if( empty($_GET['visual']) && isset($_COOKIE['mfn_closed_section']) && $_COOKIE['mfn_closed_section'] == $section['uid'] ) continue; // closeable section

  				// section attributes

  				// classes ---

  				if( !empty($section['ver']) ){
  					$section_class[] = 'mfn-'.$section['ver'].'-section';
  				}else{
  					$section_class[] = 'mfn-default-section';
  				}

  				// unique ID

					if( empty( $section['uid'] ) ) {
						$section['uid'] = Mfn_Builder_Helper::unique_ID();
					}

					if( !empty($section['mfn_global_section_id']) && is_numeric($section['mfn_global_section_id']) && get_post_status($section['mfn_global_section_id']) == 'publish') {
						//set the original uid of the section. global sections
						$section_class[] = 'mcb-section-'. $refresh_content[0]['uid'];
						$inner_section_class_uid = 'mcb-section-inner-'.$refresh_content[0]['uid'];
					} else {
						$section_class[] = 'mcb-section-'. $section['uid'];
					}

  				if( $this->template_type && $this->template_type == 'header' ) $section_class[] = 'mcb-header-section';

  				// custom style & class

  				// if( empty( $section['attr'] ) ) continue;

					if( ! empty($section['attr']['style']) ) {
						$style_ex = explode(' ', $section['attr']['style']);
						if( in_array('full-width', $style_ex) ){
							$style_key = array_search('full-width', $style_ex);
							$style_ex[$style_key] = 'full-width full-width-deprecated';
							//$section['attr']['style'] = str_replace('full-width', 'full-width full-width-deprecated', $section['attr']['style']); // old full width class
						}
						$section_class[] = implode(' ', $style_ex);
  				}

  				if( ! empty($section['attr']['class']) ) {
  					$section_class[] = $section['attr']['class'];
  				}

  				if( ! empty($section['attr']['classes']) ) {
  					$section_class[] = $section['attr']['classes'];
  				}

  				// visibility

  				$hide_label = 'Hide section';

  				if( ! empty($section['attr']['visibility']) ) {
  					$section_class[] = $section['attr']['visibility'];
  					$hide_label = 'Show section';
  				}

  				if( ! empty($section['attr']['query_slider_arrows_visibility']) ) {
  					$section_class[] = $section['attr']['query_slider_arrows_visibility'];
  				}

  				if( ! empty($section['attr']['query_slider_dots_visibility']) ) {
  					$section_class[] = $section['attr']['query_slider_dots_visibility'];
  				}

  				// background video

					if( ! empty($section['attr']['bg_video_mp4']) ) {
  					$section_class[] = 'has-video';
  				}
					if( ! empty($section['attr']['bg_video_dots']) ) {
  					$section_class[] = 'hide-video-dots';
  				}

  				// query loop

  				if( isset($section['attr']['type']) && $section['attr']['type'] == 'query' ){
  					$section_class[] = 'mfn-looped-items';

  					if( !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ){
							$section_class[] = 'mfn-looped-items-slider-wrapper';

							if( !empty($section['attr']['query_slider_arrows']) ){
								if( !empty($section['attr']['query_slider_arrows_style']) ){
	  							$section_class[] = 'mfn-arrows-'.$section['attr']['query_slider_arrows_style'];
	  						}else{
	  							$section_class[] = 'mfn-arrows-standard';
	  						}
	  					}else{
	  						$section_class[] = 'mfn-arrows-hidden';
	  					}

	  					if( !empty( $section['attr']['query_slider_dots_count'] ) ){
	  						$section_class[] = 'mfn-dots-count-dynamic';
	  					}

	  					if( !empty($section['attr']['query_slider_dots']) ){
								if( !empty($section['attr']['query_slider_dots_style']) ){
	  							$section_class[] = 'mfn-dots-'.$section['attr']['query_slider_dots_style'];
	  						}else{
	  							$section_class[] = 'mfn-dots-standard';
	  						}
	  					}else{
	  						$section_class[] = 'mfn-dots-hidden';
	  					}

	  					if( !empty($section['attr']['query_slider_centered']) && $section['attr']['query_slider_centered'] == '2' ){
								$section_class[] = 'mfn-ql-slider-wrapper-offset';
							}

						}

						if( !empty($section['attr']['query_display_style']) && $section['attr']['query_display_style'] == 'masonry' ){
							$section_class[] = 'mfn-looped-items-masonry';
						}

  				}else if( isset($section['attr']['type']) && $section['attr']['type'] == 'tabber' ){
  					$section_class[] = 'mfn-section-tabber';

  					$section_class[] = !empty( $section['attr']['tabber_nav_open_opt'] ) ? 'mfn-tabber-open-on-'.$section['attr']['tabber_nav_open_opt'] : 'mfn-tabber-open-on-click';

  					if( isset( $section['attr']['tabber_nav_position']['desktop'] ) ){
				 			$section_class[] = 'mfn-tabber-nav-'. esc_attr( $section['attr']['tabber_nav_position']['desktop'] );
				 		}else{
				 			$section_class[] = 'mfn-tabber-nav-left';
				 		}

				 		if( isset( $section['attr']['tabber_nav_position']['laptop'] ) ) $section_class[] = 'mfn-tabber-nav-laptop-'. esc_attr( $section['attr']['tabber_nav_position']['laptop'] );
				 		if( isset( $section['attr']['tabber_nav_position']['tablet'] ) ) $section_class[] = 'mfn-tabber-nav-tablet-'. esc_attr( $section['attr']['tabber_nav_position']['tablet'] );
				 		if( isset( $section['attr']['tabber_nav_position']['mobile'] ) ) $section_class[] = 'mfn-tabber-nav-mobile-'. esc_attr( $section['attr']['tabber_nav_position']['mobile'] );


				 		if( isset( $section['attr']['tabber_icon_position']['desktop'] ) ) {
				 			$section_class[] = 'mfn-tabber-icon-'. esc_attr( $section['attr']['tabber_icon_position']['desktop'] );
				 		}else{
				 			$section_class[] = 'mfn-tabber-icon-left';
				 		}

				 		if( isset( $section['attr']['tabber_icon_position']['laptop'] ) ) $section_class[] = 'mfn-tabber-icon-laptop-'. esc_attr( $section['attr']['tabber_icon_position']['laptop'] );
				 		if( isset( $section['attr']['tabber_icon_position']['tablet'] ) ) $section_class[] = 'mfn-tabber-icon-tablet-'. esc_attr( $section['attr']['tabber_icon_position']['tablet'] );
				 		if( isset( $section['attr']['tabber_icon_position']['mobile'] ) ) $section_class[] = 'mfn-tabber-icon-mobile-'. esc_attr( $section['attr']['tabber_icon_position']['mobile'] );

				 		wp_enqueue_style('mfn-tabber', get_theme_file_uri('/css/elements/tabber.css'), null, MFN_THEME_VERSION);

  					self::$is_tabber = true;
  				}

  				// navigation arrows

					if( ! empty($section['attr']['navigation']) ) {
  					$section_class[] = 'has-navi';
  				}

  				if( ! empty($section['attr']['height_switcher']) && $section['attr']['height_switcher'] == 'full-screen' ) {
  					$section_class[] = 'full-screen';
  				}

  				if( $this->template_type && $this->template_type == 'header' && ! empty($section['attr']['closeable-x']) ) {
  					$section_class[] = 'close-button-'.$section['attr']['closeable-x'];
  				}

  				if( $this->template_type && $this->template_type == 'header' && ! empty($section['attr']['closeable']) ) {
  					$section_class[] = 'closeable-active';
  				}

  				if( ! empty($section['attr']['width_switcher']) ) {
  					$section_class[] = $section['attr']['width_switcher'].'-width';
  				}

  				if( $this->template_type && $this->template_type == 'header' && ! empty($section['attr']['scroll-visibility']) ) {
  					$section_class[] = $section['attr']['scroll-visibility'].'-on-scroll';
  				}

					// reverse order on mobile

					if( ! empty($section['attr']['reverse_order']) ) {
						if( $section['attr']['reverse_order'] == 1 ) {
							$section_class[] = 'wrap-reverse';
						}elseif( $section['attr']['reverse_order'] == 2 ) {
							$section_class[] = 'wrap-reverse-rows';
						}
					}

  				// background size

  				if( isset($section['attr']['bg_size']) && ($section['attr']['bg_size'] != 'auto') ) {
  					$section_class[] = 'bg-'. $section['attr']['bg_size'];
  				}

  				$section_class = implode(' ', $section_class);

  				// styles ---

  				$section_style = $section_bg = array();

  				// ACM new input name
  				if( ! empty($section['attr']['custom_css']) ) {
  					$section_style[] = $section['attr']['custom_css'];
  				}

					if( ! empty($section['attr']['padding_top']) ) {
						$section_style[] = 'padding-top:'. intval($section['attr']['padding_top']) .'px';
					}

					if( ! empty($section['attr']['padding_bottom']) ) {
						$section_style[] = 'padding-bottom:'. intval($section['attr']['padding_bottom']) .'px';
					}

					if( ! empty($section['attr']['padding_horizontal']) ) {
						if( is_numeric($section['attr']['padding_horizontal']) ) {
							$section['attr']['padding_horizontal'] .= 'px';
						}
						$section_style[] = 'padding-left:'. esc_attr($section['attr']['padding_horizontal']);
						$section_style[] = 'padding-right:'. esc_attr($section['attr']['padding_horizontal']);
					}

					if( ! empty($section['attr']['bg_color']) ) {
						$section_style[] = 'background-color:'. $section['attr']['bg_color'];
					}

  				// background image attributes

  				if( ! empty( $section['attr']['bg_image'] ) ) {

  					$section_bg['image'] = 'background-image:url('. $section['attr']['bg_image'] .')';

  					if( !empty($section['attr']['bg_position']) && empty($_GET['visual']) ) {

							$section_bg_attr = explode(';', $section['attr']['bg_position']);

							if( isset($section_bg_attr[0]) ) {
		  					$section_bg['repeat'] = 'background-repeat:'. $section_bg_attr[0];
							}
							if( isset($section_bg_attr[1]) ) {
	  						$section_bg['position'] = 'background-position:'. $section_bg_attr[1];
							}
							if( isset($section_bg_attr[2]) ) {
		  					$section_bg['attachment'] = 'background-attachment:'. $section_bg_attr[2];
							}
							if( isset($section_bg_attr[3]) ) {
	  						$section_bg['size'] = 'background-size:'. $section_bg_attr[3];
							}

						}
  				}

					if( empty( $_GET['visual'] ) || ! isset( $items ) ){

						// parallax for Muffin Builder

	  				if ( ! empty( $section['attr']['bg_image'] ) && !empty($section_bg_attr[2]) &&  $section_bg_attr[2] == 'fixed' ) {

							if ( empty( $section_bg_attr[4] ) || $section_bg_attr[4] != 'still') {

	  						$parallax = mfn_parallax_data();
								$parallax_bg_image = be_dynamic_data($section['attr']['bg_image']);

								if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

	  						if ( mfn_parallax_plugin() == 'translate3d' ) {
	  							if ( mfn_is_mobile() ) {
	  								$section_bg['attachment'] = 'background-attachment:scroll';
	  							} else {
	  								$section_bg = array();
	  							}
	  						}

	  					} else {

	  						// cover
	  						$section_class .= ' bg-cover';

	  					}

	  				}

						// parallax for BeBuilder

	  				if ( empty( $_GET['visual'] ) && ! empty( $section['attr']['css_advanced_background_image']['val']['desktop'] ) && ! empty( $section['attr']['css_advanced_background_attachment']['val']['desktop'] ) && ( $section['attr']['css_advanced_background_attachment']['val']['desktop'] == 'parallax' ) ) {

  						$parallax = mfn_parallax_data();
							$parallax_bg_image = be_dynamic_data($section['attr']['css_advanced_background_image']['val']['desktop']);

							if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

  						if ( mfn_parallax_plugin() == 'translate3d' ) {
  							if ( mfn_is_mobile() ) {
  								$section_bg['attachment'] = 'background-attachment:scroll';
  							} else {
  								$section_bg = array();
  							}
  						}

	  				}

	  				if( !self::$updated_version ){
	  					if ( empty( $_GET['visual'] ) && ! empty( $section['attr']['style:.mcb-section-mfnuidelement:background-image'] ) && ! empty( $section['attr']['style:.mcb-section-mfnuidelement:background-attachment'] ) && ( $section['attr']['style:.mcb-section-mfnuidelement:background-attachment'] == 'parallax' ) ) {

	  						$parallax = mfn_parallax_data();
								$parallax_bg_image = be_dynamic_data($section['attr']['style:.mcb-section-mfnuidelement:background-image']);

								if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

	  						if ( mfn_parallax_plugin() == 'translate3d' ) {
	  							if ( mfn_is_mobile() ) {
	  								$section_bg['attachment'] = 'background-attachment:scroll';
	  							} else {
	  								$section_bg = array();
	  							}
	  						}

		  				}

		  				if ( empty( $_GET['visual'] ) && ! empty( $section['attr']['css_advanced_background_image']['val'] ) && ! empty( $section['attr']['css_advanced_background_attachment']['val'] ) && ( $section['attr']['css_advanced_background_attachment']['val'] == 'parallax' ) ) {

	  						$parallax = mfn_parallax_data();
								$parallax_bg_image = be_dynamic_data($section['attr']['css_advanced_background_image']['val']);

								if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

	  						if ( mfn_parallax_plugin() == 'translate3d' ) {
	  							if ( mfn_is_mobile() ) {
	  								$section_bg['attachment'] = 'background-attachment:scroll';
	  							} else {
	  								$section_bg = array();
	  							}
	  						}

		  				}
	  				}

					}

  				// visual builder

  				if( isset( $items ) && is_array( $items ) ){
  					$section_class .= ' blink';
  				}

  				$section_style = array_merge($section_style, $section_bg);
  				$section_style = implode(';', $section_style);

  				// custom section ID

  				if( ! empty($section['attr']['section_id']) && $section['attr']['section_id'] ) {
  					$section_id = 'id="'. $section['attr']['section_id'] .'"';
  				} elseif( ! empty($section['attr']['custom_id']) && $section['attr']['custom_id']) {
						$section_id = 'id="'. $section['attr']['custom_id'] .'"';
					}

  				if( !empty($section['attr']['style:.mcb-section-mfnuidelement:background-size']) && $section['attr']['style:.mcb-section-mfnuidelement:background-size'] == 'cover-ultrawide' ) {
  					$section_class .= ' bg-cover-ultrawide';
  				}

  				if( !empty($section['attr']['css_advanced_background_size']['val']['desktop']) && $section['attr']['css_advanced_background_size']['val']['desktop'] == 'cover-ultrawide' ) {
  					$section_class .= ' bg-cover-ultrawide';
  				}

  				// output SECTION -----

					if( mfn_is_blocks() ) {
						$section_style = '';
						$parallax = '';
					}

  				if( !$vbtoolsoff && ( self::$is_bebuilder || ( isset( $items ) && is_array( $items ) ) ) ){

  					if ( $this->template_type && $this->template_type == 'header' && isset( $section['wraps'] ) && is_array($section['wraps']) && count($section['wraps']) >= 3 ) $section_class .= ' mfn-new-wraps-disabled';


  					echo '<section class="section vb-item mcb-section '. $section_class .'" '. $section_id .' data-order="'. $s .'" data-uid="'. $section['uid'] .'"  '. $global_section_id .' style="'. $section_style .'" '. $parallax .'>'; // 100%
  					echo Mfn_Builder_Helper::sectionTools($section);


					  // Global Section edit button
					  if( !empty($section['mfn_global_section_id']) ){
						echo '<a href="'.get_site_url().'/wp-admin/post.php?post='.$section['mfn_global_section_id'].'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" target="_blank" data-tooltip="Edit Global Section" class="btn-edit-section" data-position="before">Edit Global Section</a>';
					  }

					} else {

						if( $this->template_type && $this->template_type == 'header' && !empty($section['attr']['closeable']) ) {
							$closeable = 'data-uid="'.$section['uid'].'" data-close-days="'.( !empty($section['attr']['closeable-time']) ? $section['attr']['closeable-time'] : '0' ).'"';
							$section_class .= ' mfn-temporary-hidden';
						}

  					echo '<section class="section mcb-section '. $section_class .'" '. $section_id .' '. $closeable .' style="'. $section_style .'" '. $parallax .'>'; // 100%

  				}

					// shape divider

					if( $this->template_type != 'header' ){

						foreach (array('top', 'bottom') as $position) {

							if ( ! empty($section['attr']['shape_divider_type_'.$position]) ){

								$shape_name = $section['attr']['shape_divider_type_'.$position];

								$is_inverted = !empty($section['attr']['shape_divider_invert_'.$position]) ? 1 : 0;
								$is_flipped = !empty($section['attr']['shape_divider_flip_'.$position]) ? 1 : 0;
								$bring_front = !empty($section['attr']['shape_divider_bring_front_'.$position]) ? 1 : 0;

								echo Mfn_Builder_Helper::shapedDivider( $shape_name, $position, $is_inverted, $is_flipped, $bring_front );

							} elseif( self::$is_bebuilder || ( isset( $items ) && is_array( $items ) ) ){

								echo Mfn_Builder_Helper::shapedDivider( 'empty', $position );

							}

						}

					}

					// background: parallax | translate3d background image

					if ( $parallax && ! mfn_is_mobile() && 'translate3d' == mfn_parallax_plugin() ) {
						$parallax_alt = mfn_get_attachment_data($parallax_bg_image, 'alt');
						echo '<img class="mfn-parallax" src="'. $parallax_bg_image .'" alt="'. esc_attr($parallax_alt) .'" style="opacity:0" />';
					}

					// background: video

					if (!empty($section['attr']['bg_video_mp4']) && ($mp4 = $section['attr']['bg_video_mp4'])) {
						echo '<div class="section_video">';

							echo '<div class="mask"></div>';

							$poster = '';

							if( !empty($section['attr']['css_advanced_background_image']['val']['desktop']) ){
								$poster = $section['attr']['css_advanced_background_image']['val']['desktop'];
							}

							if( self::$is_bebuilder ){

							echo '<div class="mfn-vb-video-lazy"><!--';
							echo '<video poster="'. $poster .'" autoplay="true" loop="true" muted="muted" playsinline="true">';
								echo '<source type="video/mp4" src="'. $mp4 .'" />';
								if (key_exists('bg_video_ogv', $section['attr']) && $ogv = $section['attr']['bg_video_ogv']) {
									echo '<source type="video/ogg" src="'. $ogv .'" />';
								}

							echo '</video>';
							echo '--></div>';

							}else{
								echo '<video poster="'. $poster .'" autoplay="true" loop="true" muted="muted" playsinline="true">';
									echo '<source type="video/mp4" src="'. $mp4 .'" />';

									if (key_exists('bg_video_ogv', $section['attr']) && $ogv = $section['attr']['bg_video_ogv']) {
										echo '<source type="video/ogg" src="'. $ogv .'" />';
									}

								echo '</video>';
							}

						echo '</div>';

					}

					// Background Overlay

					if( ! mfn_is_blocks() ){
						echo '<div class="mcb-background-overlay"></div>';
					}

					// shape divider

					foreach (array('top', 'bottom') as $position) {

						if ( ! empty($section['attr']['shape_divider_type_'.$position]) ){

							$shape_name = $section['attr']['shape_divider_type_'.$position];

							$is_inverted = !empty($section['attr']['shape_divider_invert_'.$position]) ? 1 : 0;
							$is_flipped = !empty($section['attr']['shape_divider_flip_'.$position]) ? 1 : 0;
							$bring_front = !empty($section['attr']['shape_divider_bring_front_'.$position]) ? 1 : 0;

							echo Mfn_Builder_Helper::shapedDivider( $shape_name, $position, $is_inverted, $is_flipped, $bring_front );

						} elseif( self::$is_bebuilder || ( isset( $items ) && is_array( $items ) ) ){

							echo Mfn_Builder_Helper::shapedDivider( 'empty', $position );

						}

					}

					// decoration: SVG

					if ( !empty($section['attr']['divider']) && $divider = $section['attr']['divider']) {
						echo '<div class="section-divider '. $divider .'"></div>';
					}

					// decoration: image top

					if ( !empty($section['attr']['decor_top']) && $decor_top = $section['attr']['decor_top']) {
						echo '<div class="section-decoration top" style="background-image:url('. $decor_top .');height:'. mfn_get_attachment_data($decor_top, 'height') .'px"></div>';
					}

					// navigation arrows

					if ( !empty($section['attr']['navigation']) && $section['attr']['navigation']) {
						echo '<div class="section-nav prev"><i class="icon-up-open-big" aria-label="previous section"></i></div>';
						echo '<div class="section-nav next"><i class="icon-down-open-big" aria-label="next section"></i></div>';
					}

					echo '<div class="section_wrapper mfn-wrapper-for-wraps mcb-section-inner '.$inner_section_class_uid.'">';

						if( isset($section['attr']['type']) && $section['attr']['type'] == 'tabber' ){

							$tabber_ul_classes = array('mfn-tabber-nav');
							if(!empty( $section['attr']['tabber_nav_items_hover_type'] )) $tabber_ul_classes[] = 'mfn-tabber-nav-hover-'.$section['attr']['tabber_nav_items_hover_type'];

							echo '<ul class="'.implode(' ', $tabber_ul_classes).'">';

								if( !empty( $section['attr']['tabber_nav_items_hover_type'] ) ) echo '<span class="mfn-tabber-nav-slide-helper"></span>';

								if( !empty($section['wraps']) ) {
									foreach ($section['wraps'] as $w => $wrap) {
										echo '<li class="mfn-nav-tab-'.$wrap['uid'].' '.($w == 0 ? 'active' : '').'" data-uid="'.$wrap['uid'].'"><a href="'.( !empty($wrap['attr']['tab_link']) ? be_dynamic_data($wrap['attr']['tab_link']) : '#' ).'" data-tab=".mcb-wrap-'.$wrap['uid'].'" class="mfn-tabber-nav-li-wrapper">';
											if( !empty($wrap['attr']['tab_image']) ) {
												echo '<div class="mfn-tabber-nav-li-icon"><img src="'.$wrap['attr']['tab_image'].'" alt="" /></div>';
											}else if( !empty($wrap['attr']['tab_icon']) ) {
												echo '<div class="mfn-tabber-nav-li-icon"><i class="'.$wrap['attr']['tab_icon'].'"></i></div>';
											}

											if( !empty($wrap['attr']['tab_desc']) || !empty($wrap['attr']['tab_title']) || self::$is_bebuilder ){
												echo '<div class="mfn-tabber-nav-li-desc">';
													if( !empty($wrap['attr']['tab_title']) ) echo '<span class="mfn-tabber-nav-title">'.be_dynamic_data($wrap['attr']['tab_title']).'</span>';
													if( !empty($wrap['attr']['tab_desc']) ) echo '<span class="mfn-tabber-nav-desc">'.be_dynamic_data($wrap['attr']['tab_desc']).'</span>';


													if( empty($wrap['attr']['tab_title']) && empty($wrap['attr']['tab_icon']) && empty($wrap['attr']['tab_image']) ) {
															echo '<span class="mfn-tabber-nav-title">'.'Wrap #'.($w+1).'</span>';
													}
												echo '</div>';
											}
										echo '</a></li>';
									}
								}

							echo '</ul>';
						}

						// WRAPS -----

						// FIX | Muffin Builder 2 compatibility
						// there were no wraps inside section in Muffin Builder 2

						if ( !isset( $section['wraps'] ) && ! empty( $section['items'] ) ) {
							$fix_wrap = array(
								'size' => '1/1',
								'uid' => Mfn_Builder_Helper::unique_ID(),
								'items'	=> $section['items'],
							);
							$section['wraps'] = array( $fix_wrap );
						}

						$vb = false;
            if( !$vbtoolsoff && ( self::$is_bebuilder || ( isset( $items ) && is_array( $items ) ) ) ) $vb = true;

						// print inside wraps

            /*if( wp_doing_ajax() ){
            echo '<pre>';
            print_r($section);
            echo '</pre>';
          	}*/

						if(isset($section['wraps']) && key_exists('wraps', $section) && is_array($section['wraps'])) {
              // visual builder
              ksort( $section['wraps'] );

              /**
               *
               * QUERY loop
               *
               * */

              if( /*!self::$is_bebuilder &&*/ isset($section['attr']['type']) && $section['attr']['type'] == 'query' ) {

              	$s_wrapper_params = false;

								if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {

										wp_enqueue_script('mfn-swiper', get_theme_file_uri('/js/swiper.js'), array('jquery'), MFN_THEME_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
              			wp_enqueue_style('mfn-swiper', get_theme_file_uri('/css/scripts/swiper.css'), false, MFN_THEME_VERSION, false);

              			$s_desktop_columns = !empty($section['attr']['query_slider_columns']['desktop']) ? $section['attr']['query_slider_columns']['desktop'] : 1;
            				$s_laptop_columns = !empty($section['attr']['query_slider_columns']['laptop']) ? $section['attr']['query_slider_columns']['laptop'] : $s_desktop_columns;
            				$s_tablet_columns = !empty($section['attr']['query_slider_columns']['tablet']) ? $section['attr']['query_slider_columns']['tablet'] : $s_laptop_columns;
            				$s_mobile_columns = !empty($section['attr']['query_slider_columns']['mobile']) ? $section['attr']['query_slider_columns']['mobile'] : 1;

            				if( !self::$updated_version ) {
            					$s_desktop_columns = !empty($section['attr']['query_slider_columns']) ? $section['attr']['query_slider_columns'] : 1;
	              			$s_laptop_columns = !empty($section['attr']['query_slider_columns_laptop']) ? $section['attr']['query_slider_columns_laptop'] : $s_desktop_columns;
	              			$s_tablet_columns = !empty($section['attr']['query_slider_columns_tablet']) ? $section['attr']['query_slider_columns_tablet'] : $s_laptop_columns;
	              			$s_mobile_columns = !empty($section['attr']['query_slider_columns_mobile']) ? $section['attr']['query_slider_columns_mobile'] : 1;
            				}

			  						$s_wrapper_params = 'data-columns="'.$s_desktop_columns.'"';
			  						$s_wrapper_params .= ' data-columns-tablet="'.$s_tablet_columns.'"';
			  						$s_wrapper_params .= ' data-columns-laptop="'.$s_laptop_columns.'"';
										$s_wrapper_params .= ' data-columns-mobile="'.$s_mobile_columns.'"';
			  						$s_wrapper_params .= ' data-animationtype="'.(!empty($section['attr']['query_slider_animation']) ? $section['attr']['query_slider_animation'] : 'slide').'"';
			  						$s_wrapper_params .= ' data-dots="'.(!empty($section['attr']['query_slider_dots']) ? $section['attr']['query_slider_dots'] : '0').'"';
			  						$s_wrapper_params .= ' data-dots-count="'.(!empty($section['attr']['query_slider_dots_count']) ? $section['attr']['query_slider_dots_count'] : '0').'"';
			  						$s_wrapper_params .= ' data-arrows="'.(!empty($section['attr']['query_slider_arrows']) ? $section['attr']['query_slider_arrows'] : '0').'"';
			  						$s_wrapper_params .= ' data-autoplay="'.(!empty($section['attr']['query_slider_autoplay']) ? $section['attr']['query_slider_autoplay'] : '0').'"';
			  						$s_wrapper_params .= ' data-speed="'.(!empty($section['attr']['query_slider_speed']) ? $section['attr']['query_slider_speed'] : '300').'"';
			  						$s_wrapper_params .= ' data-mousewheel="'.(!empty($section['attr']['query_slider_mousewheel']) ? $section['attr']['query_slider_mousewheel'] : '0').'"';
			  						$s_wrapper_params .= ' data-centered="'.(!empty($section['attr']['query_slider_centered']) ? $section['attr']['query_slider_centered'] : '0').'"';
			  						$s_wrapper_params .= ' data-infinity="'.(!empty($section['attr']['query_slider_infinity']) ? $section['attr']['query_slider_infinity'] : '0').'"';
			  						$s_wrapper_params .= ' data-pausehover="'.(!empty($section['attr']['query_slider_pause_hover']) ? $section['attr']['query_slider_pause_hover'] : '0').'"';
			  						$s_wrapper_params .= ' data-arrownext="'.(!empty($section['attr']['query_display_slider_arrow_next']) ? $section['attr']['query_display_slider_arrow_next'] : 'icon-right-open-big').'"';
			  						$s_wrapper_params .= ' data-arrowprev="'.(!empty($section['attr']['query_display_slider_arrow_prev']) ? $section['attr']['query_display_slider_arrow_prev'] : 'icon-left-open-big').'"';

		  							$qlslm_left = 12;
		  							$qlslm_right = 12;

		  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['desktop']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['desktop']['left']);
		  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['desktop']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['desktop']['right']);

		  							$qlslm_left_mobile = $qlslm_left;
		  							$qlslm_right_mobile = $qlslm_right;

		  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['mobile']['left']) ) $qlslm_left_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['mobile']['left']);
		  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['mobile']['right']) ) $qlslm_right_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['mobile']['right']);

		  							if( !self::$updated_version ){

		  								if( !empty($section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:margin']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:margin']['left']);
			  							if( !empty($section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:margin']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:margin']['right']);

			  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['left']);
			  							if( !empty($section['attr']['css_queryloop_item_margin']['val']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin']['val']['right']);

			  							if( !empty($section['attr']['css_queryloop_item_margin_mobile']['val']['left']) ) $qlslm_left_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin_mobile']['val']['left']);
			  							if( !empty($section['attr']['css_queryloop_item_margin_mobile']['val']['right']) ) $qlslm_right_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $section['attr']['css_queryloop_item_margin_mobile']['val']['right']);

		  							}

		  							$s_wrapper_params .= ' data-space_desktop="'.($qlslm_left + $qlslm_right).'"';
		  							$s_wrapper_params .= ' data-space_mobile="'.($qlslm_left_mobile + $qlslm_right_mobile).'"';

			  						$s_wrapper_classes = array('swiper', 'mfn-looped-items-slider');

			  						if( !empty($section['attr']['query_slider_linear']) ) $s_wrapper_classes[] = 'mfn-slider-linear';

			  						echo '<div class="'.implode(' ', $s_wrapper_classes).'" '.$s_wrapper_params.'><div class="swiper-wrapper">';
			  				}else if( !empty($section['attr']['query_display_style']) && $section['attr']['query_display_style'] == 'masonry' ){
			  					wp_enqueue_script('mfn-imagesloaded', get_theme_file_uri('/js/plugins/imagesloaded.min.js'), ['jquery'], MFN_THEME_VERSION, true);
			  					wp_enqueue_script('mfn-isotope', get_theme_file_uri('/js/plugins/isotope.min.js'), array('jquery'), MFN_THEME_VERSION, true);
									echo '<div class="mfn-query-loop-masonry">';
								}else if( self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ){
									echo '<div class="swiper mfn-looped-items-slider">';
								}

              	$q_args = array();

              	if( !empty( $section['attr']['query_type'] ) && $section['attr']['query_type'] == 'terms' ) {

              		$q_args['orderby'] = $section['attr']['query_terms_orderby'] ?? 'none';
              		$q_args['order'] = $section['attr']['query_terms_order'] ?? 'ASC';
              		$q_args['hide_empty'] = !empty($section['attr']['query_terms_hide_empty']) ? true : false;
              		$q_args['number'] = $section['attr']['query_terms_number'] ?? '0';

              		if( self::$is_bebuilder ) {

              			if( !empty($section['attr']['query_terms_number']) ) $q_args['number'] = $section['attr']['query_terms_number'] > 8 ? 8 : $section['attr']['query_terms_number'];

              			if( !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {

              				if( empty($section['attr']['query_slider_columns']['desktop']) ) $section['attr']['query_slider_columns']['desktop'] = 1;
	              			if( !empty($section['attr']['query_slider_centered']) && $section['attr']['query_slider_centered'] == '2' ) {
	            					$q_args['number'] = $section['attr']['query_slider_columns']['desktop'] + 2;
	            				}else{
	            					$q_args['number'] = $section['attr']['query_slider_columns']['desktop'];
	            				}

		            			if( !self::$updated_version ) {
		            				if( empty($section['attr']['query_slider_columns']) ) $section['attr']['query_slider_columns'] = 1;
		              			if( !empty($section['attr']['query_slider_centered']) && $section['attr']['query_slider_centered'] == '2' ) {
		            					$q_args['number'] = $section['attr']['query_slider_columns'] + 2;
		            				}else{
		            					$q_args['number'] = $section['attr']['query_slider_columns'];
		            				}
		            			}

	            			}

              		}

              		if( !empty($section['attr']['query_terms_taxonomy']) ){

              			$choosed_terms = $section['attr']['query_terms_taxonomy'];

              		}else{
              			$choosed_terms = 'category';
              		}

              		$q_args['taxonomy'] = $choosed_terms;

              		$excl_var = 'query_terms_excludes_'.$section['attr']['query_terms_taxonomy'];

              		if( !empty( $section['attr'][$excl_var] ) && is_array( $section['attr'][$excl_var] ) ){
              			$arr_helper = array();
              			foreach( $section['attr'][$excl_var] as $el ) {
              				if( !empty($el['key']) ) $arr_helper[] = $el['key'];
              			}
              			$q_args['exclude'] = $arr_helper;
              		}

              		$incl_var = 'query_terms_includes_'.$section['attr']['query_terms_taxonomy'];

              		if( !empty( $section['attr'][$incl_var] ) && is_array( $section['attr'][$incl_var] ) ){
              			$arr_helper = array();
              			foreach( $section['attr'][$incl_var] as $el ) {
              				if( !empty($el['key']) && $el['key'] != '0-current' ) {
              					$arr_helper[] = $el['key'];
              				}else{

              					if( is_singular() ){

              						$cp_id = !empty($section['attr']['vb_postid']) ? $section['attr']['vb_postid'] : get_the_ID();
              						$cats = get_the_terms( $cp_id, $choosed_terms );
              						if( !empty($cats) ) {
		              					foreach ($cats as $cat) {
		              						$arr_helper[] = $cat->term_id;
		              					}
		              				}else{
		              					$arr_helper[] = 1;
		              				}



		            					/*if( is_singular('product') ){
		            						$product_id = !empty($section['attr']['vb_postid']) && !is_singular('product') ? $section['attr']['vb_postid'] : get_the_ID();
			              					$cats = get_the_terms( $product_id, 'product_cat' );
			              					foreach ($cats as $cat) {
			              						$arr_helper[] = $cat->term_id;
			              					}
		            					}*/

		            				}else{
		            					$mfn_queried_object = get_queried_object();
              						if( isset($mfn_queried_object->term_id) ) $arr_helper[] = $mfn_queried_object->term_id;
		            				}

              				}
              			}
              			$q_args['include'] = $arr_helper;
              		}

              		$mfn_queried_object = get_queried_object();
              		$child_of = false;

              		if( !empty($section['attr']['query_terms_child_of_product_cat']) ) $child_of = $section['attr']['query_terms_child_of_product_cat'];

              		if( !empty( $child_of ) ){
              			if( $child_of != '0-current' ) {
              				$get_term = get_term_by('slug', $section['attr']['query_terms_child_of_product_cat'], 'product_cat');
            					if( isset($get_term->term_id) ) $q_args['child_of'] = $get_term->term_id;
            				}elseif( isset($mfn_queried_object->term_id) ) {
            					$q_args['child_of'] = $mfn_queried_object->term_id;
            				}
              		}

              		//$q_terms = get_terms( $choosed_terms, $q_args );
              		$q_terms = get_terms( $q_args );

              		/*echo '<pre>';
              		print_r( $q_args );
              		echo '</pre>';*/

              		if ( !empty($q_terms) && ! is_wp_error( $q_terms ) ) :

										foreach( $q_terms as $t=>$term ) {

											self::$item_type = 'term';
											self::$item_id = $term->term_id;
											if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {
												echo '<div class="swiper-slide">';
												echo '<div class="mfn-queryloop-item-wrapper" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
											}else{
												echo '<div class="mfn-queryloop-item-wrapper mfn-ql-item-default" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
											}

											foreach( $section['wraps'] as $w => $wrap ) {
              					$this->show_wraps($wrap, $w, $vb, $t);
              				}

              				self::$is_ql_section = true;

											echo '</div>';
											if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) echo '</div>';
											self::$item_type = false;
											self::$item_id = false;

										}

										self::$is_ql_section = false;

									/*else:
										foreach ($section['wraps'] as $w => $wrap) {
		              		$this->show_wraps($wrap, $w, $vb);
		              	}
									*/
									endif;

              	}else{

              		$q_args['post_type'] = $section['attr']['query_post_type'] ?? 'post';

              		if ( empty($section['attr']['query_type']) && self::$is_bebuilder && ! empty( $this->template_type ) && strpos( $this->template_type, 'archive-' ) !== false ) {
										$pt_helper = str_replace( 'archive-', '', $this->template_type );

										if ( 'post' === $pt_helper ) {
											$q_args['post_type'] = 'post';
										} else {
											$q_args['post_type'] = $pt_helper;
										}
									}

              		if( function_exists('is_woocommerce') && !empty( $section['attr']['query_post_type_product_order'] ) ) {

              			if( $section['attr']['query_post_type_product_order'] == 'on_sale' ) {

              				$product_on_sale = array();

              				global $wpdb;
              				$products_on_sale_query = $wpdb->get_results( "SELECT `post_id` FROM {$wpdb->prefix}postmeta WHERE meta_key = '_sale_price' " );

              				if( !empty($products_on_sale_query) ){
              					foreach($products_on_sale_query as $prod){

              						if( get_post_status($prod->post_id) != 'publish' ) continue;

              						if( get_post_type($prod->post_id) == 'product' ){
              							$product_on_sale[] = $prod->post_id;
              						}elseif( get_post_type($prod->post_id) == 'product_variation' ){
              							$product_on_sale[] = wp_get_post_parent_id($prod->post_id);
              						}
              					}
              				}

              				$q_args['post__in'] = $product_on_sale;

	              		}else if( $section['attr']['query_post_type_product_order'] == 'top_rated' ) {
              				$q_args['meta_key'] = '_wc_average_rating';
	      							$q_args['orderby'] = 'meta_value_num';
	      							$q_args['order'] = 'DESC';
              			}else{
              				$q_args['meta_key'] = 'total_sales';
	      							$q_args['orderby'] = 'meta_value_num';
	      							$q_args['order'] = 'DESC';
              			}

              		}else{
              			$q_args['orderby'] = $section['attr']['query_post_orderby'] ?? 'date';
              			$q_args['order'] = $section['attr']['query_post_order'] ?? 'DESC';
              		}


              		if( self::$is_bebuilder ) {

              			if( !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' && !empty($section['attr']['query_slider_columns']['desktop']) ) {

              				$qs_columns = $section['attr']['query_slider_columns']['desktop'] ?? 1;

              				if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'mobile' ){
              					$qs_columns = $section['attr']['query_slider_columns']['mobile'] ?? $qs_columns;
              				}else if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'laptop' ){
              					$qs_columns = $section['attr']['query_slider_columns']['laptop'] ?? $qs_columns;
              				}else if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'tablet' ){
              					$qs_columns = $section['attr']['query_slider_columns']['tablet'] ?? $qs_columns;
              				}

              				if( !empty($section['attr']['query_slider_centered']) && $section['attr']['query_slider_centered'] == '2' ){
              					$q_args['posts_per_page'] = $qs_columns + 2;
              				}else{
              					$q_args['posts_per_page'] = $qs_columns;
              				}

              			}else{
              				$q_args['posts_per_page'] = !empty($section['attr']['query_post_per_page']) && $section['attr']['query_post_per_page'] < 8 ? $section['attr']['query_post_per_page'] : '8';
              			}


              			if( !self::$updated_version ) {
              				if( !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' && !empty($section['attr']['query_slider_columns']) ){

	              				$qs_columns = $section['attr']['query_slider_columns'] ?? 1;

	              				if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'mobile' ){
	              					$qs_columns = $section['attr']['query_slider_columns_mobile'] ?? $qs_columns;
	              				}else if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'laptop' ){
	              					$qs_columns = $section['attr']['query_slider_columns_laptop'] ?? $qs_columns;
	              				}else if( !empty( $section['attr']['rwd'] ) && $section['attr']['rwd'] == 'tablet' ){
	              					$qs_columns = $section['attr']['query_slider_columns_tablet'] ?? $qs_columns;
	              				}

	              				if( !empty($section['attr']['query_slider_centered']) && $section['attr']['query_slider_centered'] == '2' ){
	              					$q_args['posts_per_page'] = $qs_columns + 2;
	              				}else{
	              					$q_args['posts_per_page'] = $qs_columns;
	              				}
	              			}else{
	              				$q_args['posts_per_page'] = !empty($section['attr']['query_post_per_page']) && $section['attr']['query_post_per_page'] < 8 ? $section['attr']['query_post_per_page'] : '8';
	              			}
              			}

              		}else{
              			$q_args['posts_per_page'] = !empty($section['attr']['query_post_per_page']) ? $section['attr']['query_post_per_page'] : get_option( 'posts_per_page' );
              		}

              		// if( !empty($section['attr']['query_post_offset']) ) $q_args['offset'] = $section['attr']['query_post_offset'];

              		if( !empty($q_args['post_type']) ) {
              			$tax_q = $this->getTaxQuery($q_args, $section);
              			if( !empty($tax_q) ) $q_args = array_merge($q_args, $tax_q);
              		}

              		$q_args['post_status'] = 'publish';
              		if( !empty($section['attr']['query_post_ignore_sticky_posts']) ) $q_args['ignore_sticky_posts'] = true;

              		/*print_r($q_args);
              		echo '<br />';
              		print_r($tax_q);*/

              		if( (!empty($section['attr']['query_type']) || !empty($section['attr']['query_loop_is_main'])) && in_array($this->template_type, array('portfolio', 'blog')) && (is_home() || is_category() || is_tag() || is_author() || is_tax( 'portfolio-types' ) ) ) {

              			global $wp_query;
              			$section_posts_query = $wp_query;

              		}else if( ( (!empty($section['attr']['query_type']) && empty( get_post_meta($this->post_id, 'mfn-tmpl-has-main-query', true) )) || !empty($section['attr']['query_loop_is_main'])) && is_archive() && strpos($this->template_type, 'archive-') !== false ) {

              			global $wp_query;
              			$section_posts_query = $wp_query;

              		}else{

              			/*if( !empty($section['attr']['query_post_pagination']) && !empty($section['attr']['type']) && $section['attr']['type'] == 'query' ) {
              				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
              				$q_args['paged'] = $paged;
              			}*/

              			if ( ! empty( $section['attr']['query_post_pagination'] ) && ! empty( $section['attr']['type'] ) && $section['attr']['type'] === 'query' ) {

											$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1 );
											$q_args['paged'] = max( 1, $paged );

											$base_offset = ! empty( $section['attr']['query_post_offset'] ) ? (int) $section['attr']['query_post_offset'] : 0;
											$ppp         = ! empty( $q_args['posts_per_page'] ) ? (int) $q_args['posts_per_page'] : (int) get_option( 'posts_per_page' );

											$q_args['offset'] = $base_offset + ( ( $q_args['paged'] - 1 ) * $ppp );
											$q_args['mfn_base_offset'] = $base_offset;

										} elseif ( ! empty( $section['attr']['query_post_offset'] ) ) {
											$q_args['offset'] = (int) $section['attr']['query_post_offset'];
										}

										$url_filters = mfn_get_filters_from_url();

										if ( ! empty( $url_filters['tax_query'] ) ) {
											$q_args['tax_query'] = array_merge(
												$url_filters['tax_query'],
												$q_args['tax_query'] ?? []
											);
										}

										if ( ! empty( $url_filters['meta_query'] ) ) {
											$q_args['meta_query'] = array_merge(
												$url_filters['meta_query'],
												$q_args['meta_query'] ?? []
											);
										}

										/*echo '<pre>';
										print_r($q_args);
										echo '</pre>';*/

										$section_posts_query = new WP_Query( $q_args );
              		}

              		if ( $section_posts_query->have_posts() ) :

              			$s_iterate = 0;

              			while ( $section_posts_query->have_posts() ) : $section_posts_query->the_post();
              				self::$item_type = 'post';
											self::$item_id = get_the_ID();

											$sectionqi_inner_inline_styles = false;

              				if( !self::$updated_version ) {

              					if ( !empty( $section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:background-image'] ) && strpos($section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:background-image'], '{featured_image') !== false ) {
													$sectionqi_bg_dd = be_dynamic_data($section['attr']['style:.mcb-section-mfnuidelement .mcb-section-inner .mfn-queryloop-item-wrapper:background-image']);
													if( is_numeric($sectionqi_bg_dd) ) $sectionqi_bg_dd = wp_get_attachment_image_src( $sectionqi_bg_dd, 'full' )[0];
													$sectionqi_inner_inline_styles = 'style="background-image: url('.$sectionqi_bg_dd.')"';
												}

												if ( !empty( $section['attr']['css_queryloop_item_bg_image']['val'] ) && strpos($section['attr']['css_queryloop_item_bg_image']['val'], '{featured_image') !== false ) {
													$sectionqi_bg_dd = be_dynamic_data($section['attr']['css_queryloop_item_bg_image']['val']);
													if( is_numeric($sectionqi_bg_dd) ) $sectionqi_bg_dd = wp_get_attachment_image_src( $sectionqi_bg_dd, 'full' )[0];
													$sectionqi_inner_inline_styles = 'style="background-image: url('.$sectionqi_bg_dd.')"';
												}

	              				if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {
	              					echo '<div class="swiper-slide">';
	              					echo '<div '.$sectionqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
	              				}else{
	              					echo '<div '.$sectionqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper mfn-ql-item-default" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
              					}

              				}else{


              					$bg_vals = $section['attr']['css_queryloop_item_bg_image']['val'] ?? array();
												$bg_vals_hover = $section['attr']['css_queryloop_item_bg_image_hover']['val'] ?? array();

												if ( ! empty(array_filter($bg_vals)) ) {
													$style_prop = $section['attr']['css_queryloop_item_bg_image']['style'] ?? 'background-image';
													$selector  = 'html body .mcb-section-'.$section['uid'].' .mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"]';
													$css_output = '';
													foreach( $bg_vals as $device => $bg ) {
														if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
														$get_bg = be_dynamic_data($bg);
														if( !empty($get_bg) && is_numeric($get_bg) ) {
															$get_bg = wp_get_attachment_image_url($get_bg, 'full');
														}
														if( empty($get_bg) ) continue;
														$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
														if( $device === 'desktop' ) {
															$css_output .= $rule;
														} elseif( $device === 'laptop' ) {
															$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
														} elseif( $device === 'tablet' ) {
															$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
														} elseif( $device === 'mobile' ) {
															$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
														}
													}
													if( !empty($css_output) ) {
														echo '<style>' . $css_output . '</style>';
													}
												}

												if ( ! empty(array_filter($bg_vals_hover)) ) {
													$style_prop = $section['attr']['css_queryloop_item_bg_image_hover']['style'] ?? 'background-image';
													$selector  = 'html body .mcb-section-'.$section['uid'].' .mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"]:hover';
													$css_output = '';
													foreach( $bg_vals_hover as $device => $bg ) {
														if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
														$get_bg = be_dynamic_data($bg);
														if( !empty($get_bg) && is_numeric($get_bg) ) {
															$get_bg = wp_get_attachment_image_url($get_bg, 'full');
														}
														if( empty($get_bg) ) continue;
														$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
														if( $device === 'desktop' ) {
															$css_output .= $rule;
														} elseif( $device === 'laptop' ) {
															$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
														} elseif( $device === 'tablet' ) {
															$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
														} elseif( $device === 'mobile' ) {
															$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
														}
													}
													if( !empty($css_output) ) {
														echo '<style>' . $css_output . '</style>';
													}
												}



              					/*if ( !empty( $section['attr']['css_queryloop_item_bg_image']['val']['desktop'] ) && strpos($section['attr']['css_queryloop_item_bg_image']['val']['desktop'], '{featured_image') !== false ) {
													$sectionqi_bg_dd = be_dynamic_data($section['attr']['css_queryloop_item_bg_image']['val']['desktop']);
													if( is_numeric($sectionqi_bg_dd) ) $sectionqi_bg_dd = wp_get_attachment_image_src( $sectionqi_bg_dd, 'full' )[0];
													$sectionqi_inner_inline_styles = 'style="background-image: url('.$sectionqi_bg_dd.')"';
												}
*/
	              				if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {
	              					echo '<div class="swiper-slide">';
	              					echo '<div '.$sectionqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
	              				}else{
	              					echo '<div '.$sectionqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper mfn-ql-item-default" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
	              				}

              				}

              				foreach ($section['wraps'] as $w => $wrap) {
              					$this->show_wraps($wrap, $w, $vb, $s_iterate);
              				}

              				self::$is_ql_section = true;

              				echo '</div>';
              				if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) echo '</div>';
              				self::$item_type = false;
											self::$item_id = false;
											$s_iterate++;

              			endwhile;

              			self::$is_ql_section = false;
              			wp_reset_postdata();

              		elseif(self::$is_bebuilder) :

              			foreach ($section['wraps'] as $w => $wrap) {
		              		$this->show_wraps($wrap, $w, $vb);
		              	}

              		else:

              			echo '<div class="alert alert_warning"><div class="alert_wrapper">'.__('There are no posts on the list.', 'betheme').'</div></div>';

              		endif;

              	}

              	if( !self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {
              		echo '</div></div>';
              	}else if( !empty($section['attr']['query_display_style']) && $section['attr']['query_display_style'] == 'masonry' ) {
              		wp_enqueue_script('mfn-imagesloaded', get_theme_file_uri('/js/plugins/imagesloaded.min.js'), ['jquery'], MFN_THEME_VERSION, true);
              		wp_enqueue_script('mfn-isotope', get_theme_file_uri('/js/plugins/isotope.min.js'), array('jquery'), MFN_THEME_VERSION, true);
									echo '</div>';
								}else if( self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' ) {
									echo '</div>';
								}

								if( self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' && !empty($section['attr']['query_slider_arrows']) ) {
              		echo '<div class="swiper-button-next mfn-swiper-arrow" tabindex="0" role="button" aria-label="Next slide" aria-disabled="false"><i class="'.( !empty($section['attr']['query_display_slider_arrow_next']) ? $section['attr']['query_display_slider_arrow_next'] : "icon-right-open-big" ).'"></i></div>';
              		echo '<div class="swiper-button-prev mfn-swiper-arrow" tabindex="0" role="button" aria-label="Previous slide" aria-disabled="false"><i class="'.( !empty($section['attr']['query_display_slider_arrow_prev']) ? $section['attr']['query_display_slider_arrow_prev'] : "icon-left-open-big" ).'"></i></div>';
              	}

              	if( self::$is_bebuilder && !empty($section['attr']['query_display']) && $section['attr']['query_display'] == 'slider' && !empty($section['attr']['query_slider_dots']) ) {
              		echo '<div class="swiper-pagination swiper-pagination-bullets"><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span></div>';
              	}

              }else{
              	//if( self::$is_bebuilder && isset($section['attr']['type']) && $section['attr']['type'] == 'query' ) { echo '<div class="mfn-queryloop-item-wrapper mfn-ql-item-default">'; }
              	foreach ($section['wraps'] as $w => $wrap) {
              		$this->show_wraps($wrap, $w, $vb);
              	}
              	//if( self::$is_bebuilder && isset($section['attr']['type']) && $section['attr']['type'] == 'query' ) { echo '</div>'; }
              }
						}

					echo '</div>';

					if ( isset($section_posts_query) && $section_posts_query->have_posts() ) :

						if( !empty($section['attr']['query_post_pagination']) && !empty($section['attr']['type']) && $section['attr']['type'] == 'query' ) {

      				if( self::$is_bebuilder ) {
      					$mfnQPagination = new MfnQueryPagination($section, true);
      					$mfnQPagination->bebuilderHtml();
      				}else{
      					$mfnQPagination = new MfnQueryPagination($section, $section_posts_query);
      					$mfnQPagination->render();
      				}

						}

					endif;

					// decoration: image top

					if( ! empty($section['attr']['decor_bottom']) ) {
						$decor_bottom = $section['attr']['decor_bottom'];
						echo '<div class="section-decoration bottom" style="background-image:url('. $decor_bottom .');height:'. mfn_get_attachment_data($decor_bottom, 'height') .'px"></div>';
					}


  				if( !$vbtoolsoff && self::$is_bebuilder || ( isset( $items ) && is_array( $items ) ) ){
  					echo '<a href="#" data-tooltip="Add new section" class="btn-section-add mfn-icon-add-light mfn-section-add siblings next" data-position="after">Add section</a>';
  				}

  				// closeable
  				if( $this->template_type && $this->template_type == 'header' && ! empty($section['attr']['closeable']) && $section['attr']['closeable'] == '1' ) {
  					echo '<span class="close-closeable-section mfn-close-icon"><span class="icon">&#10005;</span></span>';
  				}

  				echo '</section>';
  			}

  			self::$item_id = false;
  			self::$item_type = false;
  			self::$is_tabber = false;
  	}


  	public function show_wraps($wrap, $w, $vb, $s_iterate = false) {

  		$wrap_class = array('wrap mcb-wrap');

  		if( !empty($wrap['attr']['grid']) ) {
  			$wrap_class[] = 'mcb-wrap-grid';
  			if( !empty($wrap['attr']['grid_columns_switcher']) ) $wrap_class[] = 'mcb-wrap-grid-col-custom';
  		}

  		if( !empty( $wrap['attr']['conditions'] ) ) {
  			if( !self::$is_bebuilder ){
	  			$mfnConditions = new MfnConditionalLogic();
	  			if( !$mfnConditions->verify( $wrap['attr']['conditions'] ) ){
	  				return;
	  			}
	  		}else{
	  			$wrap_class[] = 'mfn-conditional-logic';
	  		}
  		}

  		if( !empty( $wrap['be_classes']) && is_array($wrap['be_classes']) ){
				foreach($wrap['be_classes'] as $bc) {
					$wrap_class[] = $bc;
					if( isset($this->be_classes[$bc]) ){
						if( isset($wrap['attr']['width_switcher']) && $wrap['attr']['width_switcher'] == 'default' ) unset($wrap['attr']['width_switcher']);
						$wrap['attr'] = array_merge($this->be_classes[$bc], $wrap['attr']);
					}

				}
			}

			if( self::$is_tabber && empty($wrap['item_is_wrap']) ) {
				$wrap_class[] = 'mfn-tabber-content';
				if( !$w ) $wrap_class[] = 'active';
			}

  		// Muffin Builder ACM compatibility
			if( empty($wrap['tablet_size']) ){
				$wrap['tablet_size'] = isset($wrap['size']) ? $wrap['size'] : '1/1';
				$wrap['mobile_size'] = '1/1';
			}

			if( empty($wrap['laptop_size']) ){
				$wrap['laptop_size'] = !empty($wrap['size']) ? $wrap['size'] : '1/1';
			}

			// unique ID

			if( empty( $wrap['uid'] ) ) {
				$wrap['uid'] = Mfn_Builder_Helper::unique_ID();
			}

			// wrap attributes

			$original_uid = $wrap['uid'];
			$global_wrap_attr = '';
			$is_global_wrap = isset($wrap['attr']['global_wraps_select']) && intval($wrap['attr']['global_wraps_select']);
			$global_wrap_id = '';
			$wrap_inner_inline_styles = false;

			// be global sections
			if( $is_global_wrap ){
				$global_wrap_id = $wrap['attr']['global_wraps_select'];
				$global_wrap_attr = 'data-mfn-global="'.$global_wrap_id.'"';
				$refresh_content = get_post_meta($global_wrap_id, 'mfn-page-items', true);

				if( !is_array($refresh_content) ) {
					$refresh_content = unserialize( call_user_func('base'.'64_decode', $refresh_content), ['allowed_classes' => false] );
				}

				if( isset($refresh_content[0]['wraps']) ){
				foreach ($refresh_content[0]['wraps'] as $r_wrap) {
    			if (!is_null($r_wrap)) {

    				$wrap['items'] = $r_wrap['items'];
						$wrap['uid'] = $r_wrap['uid'];
						//$original_uid = $r_wrap['uid'];
						$wrap['attr'] = $r_wrap['attr'];

						$wrap['size'] = $r_wrap['size'];
						$wrap['laptop_size'] = !empty($r_wrap['laptop_size']) ? $r_wrap['laptop_size'] : $r_wrap['size'];
						$wrap['tablet_size'] = $r_wrap['tablet_size'];
						$wrap['mobile_size'] = $r_wrap['mobile_size'];

						$wrap_class[] = ' mfn-global-wrap';

						if( !empty($r_wrap['attr']['grid']) ) {
			  			$wrap_class[] = 'mcb-wrap-grid';
			  			if( !empty($r_wrap['attr']['grid_columns_switcher']) ) $wrap_class[] = 'mcb-wrap-grid-col-custom';
			  		}

						if( !empty( $r_wrap['be_classes']) && is_array($r_wrap['be_classes']) ) {
							foreach($r_wrap['be_classes'] as $bc) {
								$wrap_class[] = $bc;
								if( isset($this->be_classes[$bc]) ) {
									if( isset($r_wrap['attr']['width_switcher']) && $r_wrap['attr']['width_switcher'] == 'default' ) unset($r_wrap['attr']['width_switcher']);
									$r_wrap['attr'] = array_merge($this->be_classes[$bc], $refresh_content[0]['attr']);
								}

							}
						}

    			}
    		}
    		}

				//styles
				if( !mfn_is_blocks() ){
					$path = wp_upload_dir()['baseurl'] .'/betheme/css/post-'. $global_wrap_id.'.css';
					wp_enqueue_style('mfn-global-wrap-styles-'. Mfn_Builder_Helper::unique_ID(), $path, false, time(), 'all');
				}
			}

			// FIX: LUK empty wrap created in error
			if(!isset($wrap['size']) || empty($wrap['size'])){
				return;
			}

			$wrap_class[] = 'mcb-wrap-'. $wrap['uid'];

			if( $this->template_type && $this->template_type == 'header' ) $wrap_class[] = 'mcb-header-wrap';

			// classes ---

			$wrap_class[] = $this->classes[ $wrap['size'] ];

			if( !empty($wrap['tablet_size']) ){
				$wrap_class[] = $this->tablet_classes[ $wrap['tablet_size'] ];
			}else{
				$wrap_class[] = $this->tablet_classes[ $wrap['size'] ];
			}

			if( !empty($wrap['laptop_size']) ){
				$wrap_class[] = $this->laptop_classes[ $wrap['laptop_size'] ];
			}else{
				$wrap_class[] = $this->laptop_classes[ $wrap['size'] ];
			}

			if( !empty($wrap['mobile_size']) ){
				$wrap_class[] = $this->mobile_classes[ $wrap['mobile_size'] ];
			}else{
				$wrap_class[] = 'mobile-one';
			}

			// query loop

			if( isset($wrap['attr']['type']) && $wrap['attr']['type'] == 'query' ){
				$wrap_class[] = 'mfn-looped-items';

				if( !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){
					$wrap_class[] = 'mfn-looped-items-slider-wrapper';

					if( !empty($wrap['attr']['query_slider_arrows']) ){
						if( !empty($wrap['attr']['query_slider_arrows_style']) ){
							$wrap_class[] = 'mfn-arrows-'.$wrap['attr']['query_slider_arrows_style'];
						}else{
							$wrap_class[] = 'mfn-arrows-standard';
						}
					}else{
						$wrap_class[] = 'mfn-arrows-hidden';
					}

					if( !empty($wrap['attr']['query_slider_dots']) ){
						if( !empty($wrap['attr']['query_slider_dots_style']) ){
							$wrap_class[] = 'mfn-dots-'.$wrap['attr']['query_slider_dots_style'];
						}else{
							$wrap_class[] = 'mfn-dots-standard';
						}
					}else{
						$wrap_class[] = 'mfn-dots-hidden';
					}

					if( !empty($wrap['attr']['query_slider_dots_count']) ){
						$wrap_class[] = 'mfn-dots-count-dynamic';
					}

					if( !empty($wrap['attr']['query_slider_centered']) && $wrap['attr']['query_slider_centered'] == '2' ){
						$wrap_class[] = 'mfn-ql-slider-wrapper-offset';
					}
				}
			}

			$wrap_style = $wrap_bg = array();
			$wrap_data = array();
			$parallax = false;
			$animate = '';
			$wrap_id = false;

			if( key_exists('attr', $wrap) ) {

				if( ! empty($wrap['attr']['class']) ){
					$wrap_class[] = $wrap['attr']['class'];
				}

				if( ! empty($wrap['attr']['classes']) ){
					$wrap_class[] = $wrap['attr']['classes'];
				}

				// items margin

				if( ! empty($wrap['attr']['column_margin']) ) {
					$wrap_class[] = 'column-margin-'. $wrap['attr']['column_margin'];
				}

				if( !empty($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-size']) && $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-size'] == 'cover-ultrawide' ){
  					$wrap_class[] = 'bg-cover-ultrawide';
  			}

				// items vertical align

				if( ! empty($wrap['attr']['vertical_align']) ) {
					$wrap_class[] = 'valign-'. $wrap['attr']['vertical_align'];
				}

				// reverse order on mobile

				if( ! empty($wrap['attr']['reverse_order']) ) {
					if( $wrap['attr']['reverse_order'] == 1 ){
						$wrap_class[] = 'column-reverse';
					}else if( $wrap['attr']['reverse_order'] == 2 ){
						$wrap_class[] = 'column-reverse-rows';
					}
				}

				// background size

				if( ! empty($wrap['attr']['bg_size']) && ($wrap['attr']['bg_size'] != 'auto') ) {
					$wrap_class[] = 'bg-'. $wrap['attr']['bg_size'];
				}

				if ( ! empty($wrap['attr']['visibility']) ) {
					$wrap_class[] = $wrap['attr']['visibility'];
				}

				if ( ! empty($wrap['attr']['query_slider_arrows_visibility']) ) {
					$wrap_class[] = $wrap['attr']['query_slider_arrows_visibility'];
				}

				if ( ! empty($wrap['attr']['query_slider_dots_visibility']) ) {
					$wrap_class[] = $wrap['attr']['query_slider_dots_visibility'];
				}

				// sticky



				if( ! empty( $wrap['attr']['sticky'] ) ) {

					if( self::$updated_version ){

						if( !empty( $wrap['attr']['sticky']['desktop'] ) ) {
							$wrap_class[] = 'sticky-desktop';

							if( !empty($wrap['attr']['sticky_offset']['desktop']) ) {
								$wrap_style[] = '--sticky-offset-desktop:'. $wrap['attr']['sticky_offset']['desktop'] .'px';
							}
						}

						if( !empty( $wrap['attr']['sticky']['laptop'] ) ) {
							$wrap_class[] = 'sticky-laptop';

							if( !empty($wrap['attr']['sticky_offset']['laptop']) ){
								$wrap_style[] = '--sticky-offset-laptop:'. $wrap['attr']['sticky_offset']['laptop'] .'px';
							}
						}

						if( !empty( $wrap['attr']['sticky']['tablet'] ) ) {
							$wrap_class[] = 'sticky-tablet';

							if( !empty($wrap['attr']['sticky_offset']['tablet']) ){
								$wrap_style[] = '--sticky-offset-tablet:'. $wrap['attr']['sticky_offset']['tablet'] .'px';
							}
						}

						if( !empty( $wrap['attr']['sticky']['mobile'] ) ) {
							$wrap_class[] = 'sticky-mobile';

							if( !empty($wrap['attr']['sticky_offset']['mobile']) ){
								$wrap_style[] = '--sticky-offset-mobile:'. $wrap['attr']['sticky_offset']['mobile'] .'px';
							}
						}
					}

					if( !self::$updated_version ){
						if( ! empty( $wrap['attr']['sticky'] ) ) {
							$wrap_class[] = 'sticky-desktop';

							if( !empty($wrap['attr']['sticky_offset']) && is_string($wrap['attr']['sticky_offset']) ){
								$wrap_style[] = '--sticky-offset-desktop:'. $wrap['attr']['sticky_offset'] .'px';
								// $wrap_data[] = 'data-stickyoffset="'.$wrap['attr']['sticky_offset'].'"';
							}
						}

						if( ! empty( $wrap['attr']['sticky_laptop'] ) ) {
							$wrap_class[] = 'sticky-laptop';

							if( !empty($wrap['attr']['sticky_offset_laptop']) ){
								$wrap_style[] = '--sticky-offset-laptop:'. $wrap['attr']['sticky_offset_laptop'] .'px';
								// $wrap_data[] = 'data-stickyoffsetlaptop="'.$wrap['attr']['sticky_offset_laptop'].'"';
							}
						}

						if( ! empty( $wrap['attr']['sticky_tablet'] ) ) {
							$wrap_class[] = 'sticky-tablet';

							if( !empty($wrap['attr']['sticky_offset_tablet']) ){
								$wrap_style[] = '--sticky-offset-tablet:'. $wrap['attr']['sticky_offset_tablet'] .'px';
								// $wrap_data[] = 'data-stickyoffsettablet="'.$wrap['attr']['sticky_offset_tablet'].'"';
							}
						}

						if( ! empty( $wrap['attr']['sticky_mobile'] ) ) {
							$wrap_class[] = 'sticky-mobile';

							if( !empty($wrap['attr']['sticky_offset_mobile']) ){
								$wrap_style[] = '--sticky-offset-mobile:'. $wrap['attr']['sticky_offset_mobile'] .'px';
								// $wrap_data[] = 'data-stickyoffsetmobile="'.$wrap['attr']['sticky_offset_mobile'].'"';
							}
						}
					}

				}

				// styles ---

				if ( !empty($wrap['attr']['width_switcher']) && $wrap['attr']['width_switcher'] == 'custom' ) {
					$wrap_class[] = 'mfn-item-custom-width';
				}

				// padding

				if( isset($wrap['attr']['padding']) ) {
					$wrap_style[] = 'padding:'. $wrap['attr']['padding'];
				}

				// background color

				if( isset($wrap['attr']['bg_color']) ){
					$wrap_style[] = 'background-color:'. $wrap['attr']['bg_color'];
				}

				// move up

				if( ! empty($wrap['attr']['move_up']) ) {
					$wrap_class[] = 'move-up';
					$wrap_style[] = 'margin-top:-'. intval($wrap['attr']['move_up']) .'px';

					if ($moveup = mfn_opts_get('builder-wrap-moveup')) {
						if ('no-tablet' == $moveup) {
							$wrap_data[] = 'data-tablet="no-up"';
						}
						$wrap_data[] = 'data-mobile="no-up"';
					}
				}

				// background image attributes

				if( ! empty($wrap['attr']['bg_image']) ){

					$wrap_bg[] = 'background-image:url('. $wrap['attr']['bg_image'] .')';

					if( ! empty($wrap['attr']['bg_position']) && empty($_GET['visual']) ){

						$wrap_bg_attr = explode(';', $wrap['attr']['bg_position']);

						if( ! empty($wrap_bg_attr[0]) ) {
							$wrap_bg[] = 'background-repeat:'. $wrap_bg_attr[0];
						}
						if( ! empty($wrap_bg_attr[1]) ) {
							$wrap_bg[] = 'background-position:'. $wrap_bg_attr[1];
						}
						if( ! empty($wrap_bg_attr[2]) ) {
							$wrap_bg['attachment'] = 'background-attachment:'. $wrap_bg_attr[2];
						}
						if( ! empty($wrap_bg_attr[3]) ) {
							$wrap_bg[] = 'background-size:'. $wrap_bg_attr[3];
						}

					}

				}

				// parallax

				if( empty( $_GET['visual'] ) || ! isset( $items ) ){

					// parallax for Muffin Builder

					if ( ! empty( $wrap['attr']['bg_image'] ) && ! empty($wrap_bg_attr[2]) && $wrap_bg_attr[2] == 'fixed' ) {
						if ( empty( $wrap_bg_attr[4] ) || $wrap_bg_attr[4] != 'still' ) {

							$parallax = mfn_parallax_data();
							$parallax_bg_image = be_dynamic_data($wrap['attr']['bg_image']);

							if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

							if (mfn_parallax_plugin() == 'translate3d') {
								if (mfn_is_mobile()) {
									$wrap_bg['attachment'] = 'background-attachment:scroll';
								} else {
									$wrap_bg = array();
								}
							}

						}
					}



					// wrap bg get

					$bg_vals = $wrap['attr']['css_advanced_background_image']['val'] ?? array();
					$bg_vals_hover = $wrap['attr']['css_advanced_background_image_hover']['val'] ?? array();

					if ( ! empty(array_filter($bg_vals)) ) {
						$style_prop = $wrap['attr']['css_advanced_background_image']['style'] ?? 'background-image';
						$selector  = '.mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"] .mcb-wrap-'.$wrap['uid'].' > .mcb-wrap-inner';
						$css_output = '';
						foreach( $bg_vals as $device => $bg ) {
							if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
							$get_bg = be_dynamic_data($bg);
							if( !empty($get_bg) && is_numeric($get_bg) ) {
								$get_bg = wp_get_attachment_image_url($get_bg, 'full');
							}
							if( empty($get_bg) ) continue;
							$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
							if( $device === 'desktop' ) {
								$css_output .= $rule;
							} elseif( $device === 'laptop' ) {
								$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
							} elseif( $device === 'tablet' ) {
								$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
							} elseif( $device === 'mobile' ) {
								$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
							}
						}
						if( !empty($css_output) ) {
							echo '<style>' . $css_output . '</style>';
						}
					}

					if ( ! empty(array_filter($bg_vals_hover)) ) {
						$style_prop = $wrap['attr']['css_advanced_background_image_hover']['style'] ?? 'background-image';
						$selector  = '.mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"] .mcb-wrap-'.$wrap['uid'].':hover > .mcb-wrap-inner';
						$css_output = '';
						foreach( $bg_vals_hover as $device => $bg ) {
							if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
							$get_bg = be_dynamic_data($bg);
							if( !empty($get_bg) && is_numeric($get_bg) ) {
								$get_bg = wp_get_attachment_image_url($get_bg, 'full');
							}
							if( empty($get_bg) ) continue;
							$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
							if( $device === 'desktop' ) {
								$css_output .= $rule;
							} elseif( $device === 'laptop' ) {
								$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
							} elseif( $device === 'tablet' ) {
								$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
							} elseif( $device === 'mobile' ) {
								$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
							}
						}
						if( !empty($css_output) ) {
							echo '<style>' . $css_output . '</style>';
						}
					}


					/*if ( !empty( $wrap['attr']['css_advanced_background_image']['val']['desktop'] ) && strpos($wrap['attr']['css_advanced_background_image']['val']['desktop'], '{featured_image') !== false ) {
						$wrap_bg_dd = be_dynamic_data($wrap['attr']['css_advanced_background_image']['val']['desktop']);
						if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
						$wrap_inner_inline_styles = 'style="background-image: url('.$wrap_bg_dd.'); color: inherit;"';
					}

					if ( !empty( $wrap['attr']['css_advanced_background_image_hover']['val']['desktop'] ) && is_string($wrap['attr']['css_advanced_background_image_hover']['val']['desktop']) && strpos($wrap['attr']['css_advanced_background_image_hover']['val']['desktop'], '{featured_image') !== false ) {
						$wrap_bg_dd = be_dynamic_data($wrap['attr']['css_advanced_background_image_hover']['val']['desktop']);
						if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
						$tmp_w_class = 'mfn-wrap-tmp-'.rand(0, 99999);
						$wrap_class[] = $tmp_w_class;
						echo '<style>.mcb-wrap-'.$wrap['uid'].'.'.$tmp_w_class.':hover > .mcb-wrap-inner{background-image: url('.$wrap_bg_dd.') !important}</style>';
					}*/

					// parallax for BeBuilder

					if( empty( $_GET['visual'] ) && ! empty( $wrap['attr']['css_advanced_background_image']['val']['desktop'] ) && ! empty( $wrap['attr']['css_advanced_background_attachment']['val']['desktop'] ) && ( $wrap['attr']['css_advanced_background_attachment']['val']['desktop'] == 'parallax' ) ) {

						$parallax = mfn_parallax_data();
						$parallax_bg_image = be_dynamic_data($wrap['attr']['css_advanced_background_image']['val']['desktop']);

						if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

						if ( mfn_parallax_plugin() == 'translate3d' ) {
							if ( mfn_is_mobile() ) {
								$wrap_bg['attachment'] = 'background-attachment:scroll';
							} else {
								$wrap_bg = array();
							}
						}

					}



					if( !self::$updated_version ) {

						if ( !empty( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-image'] ) && strpos($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-image'], '{featured_image') !== false ){
							$wrap_bg_dd = be_dynamic_data($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-image']);
							if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
							$wrap_inner_inline_styles = 'style="background-image: url('.$wrap_bg_dd.')"';
						}

						if ( !empty( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner|hover:background-image'] ) && strpos($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner|hover:background-image'], '{featured_image') !== false ){
							$wrap_bg_dd = be_dynamic_data($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner|hover:background-image']);
							if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
							//$wrap_inner_inline_styles = 'style="background-image: url('.$wrap_bg_dd.')"';
							echo '<style>.mcb-wrap-'.$wrap['uid'].':hover > .mcb-wrap-inner{background-image: url('.$wrap_bg_dd.') !important}</style>';
						}

						if ( !empty( $wrap['attr']['css_advanced_background_image']['val'] ) && strpos($wrap['attr']['css_advanced_background_image']['val'], '{featured_image') !== false ){
							$wrap_bg_dd = be_dynamic_data($wrap['attr']['css_advanced_background_image']['val']);
							if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
							$wrap_inner_inline_styles = 'style="background-image: url('.$wrap_bg_dd.')"';
						}

						if ( !empty( $wrap['attr']['css_advanced_background_image_hover']['val'] ) && strpos($wrap['attr']['css_advanced_background_image_hover']['val'], '{featured_image') !== false ){
							$wrap_bg_dd = be_dynamic_data($wrap['attr']['css_advanced_background_image_hover']['val']);
							if( is_numeric($wrap_bg_dd) ) $wrap_bg_dd = wp_get_attachment_image_src( $wrap_bg_dd, 'full' )[0];
							//$wrap_inner_inline_styles = 'style="background-image: url('.$wrap_bg_dd.')"';
							echo '<style>.mcb-wrap-'.$wrap['uid'].':hover > .mcb-wrap-inner{background-image: url('.$wrap_bg_dd.') !important}</style>';
						}

						// parallax for BeBuilder

						if ( empty( $_GET['visual'] ) && ! empty( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-image'] ) && ! empty( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-attachment'] ) && ( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-attachment'] == 'parallax' ) ) {

							$parallax = mfn_parallax_data();
							$parallax_bg_image = be_dynamic_data($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner:background-image']);

							if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

							if ( mfn_parallax_plugin() == 'translate3d' ) {
								if ( mfn_is_mobile() ) {
									$wrap_bg['attachment'] = 'background-attachment:scroll';
								} else {
									$wrap_bg = array();
								}
							}

						}


						if ( empty( $_GET['visual'] ) && ! empty( $wrap['attr']['css_advanced_background_image']['val'] ) && ! empty( $wrap['attr']['css_advanced_background_attachment']['val'] ) && ( $wrap['attr']['css_advanced_background_attachment']['val'] == 'parallax' ) ) {

							$parallax = mfn_parallax_data();
							$parallax_bg_image = be_dynamic_data($wrap['attr']['css_advanced_background_image']['val']);

							if( is_numeric($parallax_bg_image) ) $parallax_bg_image = wp_get_attachment_image_url($parallax_bg_image, 'full');

							if ( mfn_parallax_plugin() == 'translate3d' ) {
								if ( mfn_is_mobile() ) {
									$wrap_bg['attachment'] = 'background-attachment:scroll';
								} else {
									$wrap_bg = array();
								}
							}

						}

					}

				}

				// ACM new input name

				if(key_exists('custom_id', $wrap['attr']) && $wrap['attr']['custom_id']) {
					$wrap_id = 'id="'. $wrap['attr']['custom_id'] .'"';
				}
			}

			// ACM new input name

			if( ! empty( $wrap['attr']['custom_css'] ) ){
				$wrap_style[] = $wrap['attr']['custom_css'];
			}

			// animate

			if ( ! empty( $wrap['attr']['animate'] ) ) {
				$wrap_class[] = 'animate';
				$animate = 'data-anim-type="'. $wrap['attr']['animate'] .'"';
			}


			if( !empty($wrap['item_is_wrap']) ) $wrap_class[] = 'mfn-nested-wrap';

			// classes

			$wrap_class	= implode(' ', $wrap_class);

			$wrap_style = array_merge($wrap_style, $wrap_bg);
			$wrap_style = implode( ';', $wrap_style );

			if( ! empty( $wrap['attr']['style'] ) ) {
				$wrap_style .= ';'. $wrap['attr']['style'];
			}

			$desktop_size = $wrap['size'];
			$laptop_size = !empty($wrap['laptop_size']) ? $wrap['laptop_size'] : $desktop_size;
			$tablet_size = !empty($wrap['tablet_size']) ? $wrap['tablet_size'] : $desktop_size;
			$mobile_size = !empty($wrap['mobile_size']) ? $wrap['mobile_size'] : '1/1';

			$desktop_size_col = !empty($this->classes[ $desktop_size ]) ? $this->classes[ $desktop_size ] : '';
			$tablet_size_col = !empty($this->tablet_classes[ $tablet_size ]) ? $this->tablet_classes[ $tablet_size ] : '';
			$laptop_size_col = !empty($this->laptop_classes[ $laptop_size ]) ? $this->laptop_classes[ $laptop_size ] : '';
			$mobile_size_col = !empty($this->mobile_classes[ $mobile_size ]) ? $this->mobile_classes[ $mobile_size ] : '';

			$wrap_data = implode( ' ', $wrap_data );

			// output WRAP -----

			if( mfn_is_blocks() ) {
				$wrap_style = '';
				$parallax = '';
			}

			if( $vb ) {
				if( !self::$is_ql_section && !self::$is_ql_wrap ) $wrap_class .= ' vb-item';
				echo '<div '. $wrap_id .' class="'. $wrap_class .' clearfix mfn-module" '. $global_wrap_attr .' data-desktop-col="'. $desktop_size_col .'" data-laptop-col="'. $laptop_size_col .'" data-tablet-col="'. $tablet_size_col .'" data-mobile-col="'. $mobile_size_col .'" data-desktop-size="'. $desktop_size .'" data-laptop-size="'. $laptop_size .'" data-tablet-size="'. $tablet_size .'" data-mobile-size="'. $mobile_size .'" data-order="'. $w .'"  data-uid="'. $original_uid .'" style="'. $wrap_style .'" '. $parallax .' '. $wrap_data .'>';

				if( empty($wrap['attr']['type']) ) echo '<div class="mfn-drag-helper mfn-dh-before placeholder-wrap"></div><div class="mfn-drag-helper mfn-dh-after placeholder-wrap"></div>';

				// Global Section edit button
				if( $is_global_wrap ) {
					echo '<a href="'.get_site_url().'/wp-admin/post.php?post='.$global_wrap_id.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" target="_blank" data-tooltip="Edit Global Wrap" class="btn-edit-wrap" data-position="before">Edit Global Wrap</a>';
				}
			} else {
				if( !$vb ) $wrap_class .= ' vb-item';
				echo '<div '. $wrap_id .' class="'. $wrap_class .' clearfix" data-desktop-col="'. $desktop_size_col .'" data-laptop-col="'. $laptop_size_col .'" data-tablet-col="'. $tablet_size_col .'" data-mobile-col="'. $mobile_size_col .'" style="'. $wrap_style .'" '. $parallax .' '. $animate .' '. $wrap_data .'>';
			}


				// parallax | translate3d background image

				if ( $parallax && ! mfn_is_mobile() && 'translate3d' == mfn_parallax_plugin() ) {
					echo '<img class="mfn-parallax" src="'. $parallax_bg_image .'" alt="parallax background" style="opacity:0" />';
				}

				$w_wrapper_params = false;
				$inner_wrap_class_uid = 'mcb-wrap-inner-'.$wrap['uid'];


				echo '<div class="mcb-wrap-inner '.$inner_wrap_class_uid.' mfn-module-wrapper mfn-wrapper-for-wraps" '.$w_wrapper_params.' '.$wrap_inner_inline_styles.'>';

					if( $vb && !$s_iterate ){
						echo Mfn_Builder_Helper::wrapTools($wrap);
					}

					// Background Overlay

					echo '<div class="mcb-wrap-background-overlay"></div>';

					// ITEMS -----

					if ( isset($wrap['items'] ) && is_array( $wrap['items'] )) {
            // visual builder
            ksort($wrap['items']);

            // loop items

            	/**
               *
               * QUERY loop
               *
               * */

              if( /*!self::$is_bebuilder &&*/ isset($wrap['attr']['type']) && $wrap['attr']['type'] == 'query' ){

              	if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){

              		wp_enqueue_script('mfn-swiper', get_theme_file_uri('/js/swiper.js'), array('jquery'), MFN_THEME_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
              		wp_enqueue_style('mfn-swiper', get_theme_file_uri('/css/scripts/swiper.css'), false, MFN_THEME_VERSION, false);

              		$w_desktop_columns = !empty($wrap['attr']['query_slider_columns']['desktop']) ? $wrap['attr']['query_slider_columns']['desktop'] : 1;
            			$w_laptop_columns = !empty($wrap['attr']['query_slider_columns']['laptop']) ? $wrap['attr']['query_slider_columns']['laptop'] : $w_desktop_columns;
            			$w_tablet_columns = !empty($wrap['attr']['query_slider_columns']['tablet']) ? $wrap['attr']['query_slider_columns']['tablet'] : $w_laptop_columns;
            			$w_mobile_columns = !empty($wrap['attr']['query_slider_columns']['mobile']) ? $wrap['attr']['query_slider_columns']['mobile'] : 1;

            			if( !self::$updated_version ){
            				$w_desktop_columns = !empty($wrap['attr']['query_slider_columns']) ? $wrap['attr']['query_slider_columns'] : 1;
	            			$w_laptop_columns = !empty($wrap['attr']['query_slider_columns_laptop']) ? $wrap['attr']['query_slider_columns_laptop'] : $w_desktop_columns;
	            			$w_tablet_columns = !empty($wrap['attr']['query_slider_columns_tablet']) ? $wrap['attr']['query_slider_columns_tablet'] : $w_laptop_columns;
	            			$w_mobile_columns = !empty($wrap['attr']['query_slider_columns_mobile']) ? $wrap['attr']['query_slider_columns_mobile'] : 1;
            			}

									$w_wrapper_params = 'data-columns="'.$w_desktop_columns.'"';
									$w_wrapper_params .= ' data-columns-tablet="'.$w_tablet_columns.'"';
									$w_wrapper_params .= ' data-columns-laptop="'.$w_laptop_columns.'"';
									$w_wrapper_params .= ' data-columns-mobile="'.$w_mobile_columns.'"';
									$w_wrapper_params .= ' data-animationtype="'.(!empty($wrap['attr']['query_slider_animation']) ? $wrap['attr']['query_slider_animation'] : 'slide').'"';
									$w_wrapper_params .= ' data-dots="'.(!empty($wrap['attr']['query_slider_dots']) ? $wrap['attr']['query_slider_dots'] : '0').'"';
									$w_wrapper_params .= ' data-dots-count="'.(!empty($wrap['attr']['query_slider_dots_count']) ? $wrap['attr']['query_slider_dots_count'] : '0').'"';
									$w_wrapper_params .= ' data-arrows="'.(!empty($wrap['attr']['query_slider_arrows']) ? $wrap['attr']['query_slider_arrows'] : '0').'"';
									$w_wrapper_params .= ' data-autoplay="'.(!empty($wrap['attr']['query_slider_autoplay']) ? $wrap['attr']['query_slider_autoplay'] : '0').'"';
									$w_wrapper_params .= ' data-speed="'.(!empty($wrap['attr']['query_slider_speed']) ? $wrap['attr']['query_slider_speed'] : '300').'"';
									$w_wrapper_params .= ' data-mousewheel="'.(!empty($wrap['attr']['query_slider_mousewheel']) ? $wrap['attr']['query_slider_mousewheel'] : '0').'"';
									$w_wrapper_params .= ' data-centered="'.(!empty($wrap['attr']['query_slider_centered']) ? $wrap['attr']['query_slider_centered'] : '0').'"';
									$w_wrapper_params .= ' data-infinity="'.(!empty($wrap['attr']['query_slider_infinity']) ? $wrap['attr']['query_slider_infinity'] : '0').'"';
									$w_wrapper_params .= ' data-pausehover="'.(!empty($wrap['attr']['query_slider_pause_hover']) ? $wrap['attr']['query_slider_pause_hover'] : '0').'"';
									$w_wrapper_params .= ' data-arrownext="'.(!empty($wrap['attr']['query_display_slider_arrow_next']) ? $wrap['attr']['query_display_slider_arrow_next'] : 'icon-right-open-big').'"';
			  					$w_wrapper_params .= ' data-arrowprev="'.(!empty($wrap['attr']['query_display_slider_arrow_prev']) ? $wrap['attr']['query_display_slider_arrow_prev'] : 'icon-left-open-big').'"';

									$qlslm_left = 12;
		  						$qlslm_right = 12;

		  						$qlslm_left_mobile = 12;
		  						$qlslm_right_mobile = 12;

		  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['desktop']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['desktop']['left']);
		  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['desktop']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['desktop']['right']);

		  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['mobile']['left']) ) $qlslm_left_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['mobile']['left']);
		  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['mobile']['right']) ) $qlslm_right_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['mobile']['right']);

		  						if( !self::$updated_version ){
		  							if( !empty($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:margin']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:margin']['left']);
			  						if( !empty($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:margin']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:margin']['right']);

			  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['left']) ) $qlslm_left = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['left']);
			  						if( !empty($wrap['attr']['css_queryloop_item_margin']['val']['right']) ) $qlslm_right = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin']['val']['right']);

			  						if( !empty($wrap['attr']['css_queryloop_item_margin_mobile']['val']['left']) ) $qlslm_left_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin_mobile']['val']['left']);
			  						if( !empty($wrap['attr']['css_queryloop_item_margin_mobile']['val']['right']) ) $qlslm_right_mobile = str_replace(array('px', '%', 'em', 'rem', 'vw'), '', $wrap['attr']['css_queryloop_item_margin_mobile']['val']['right']);
		  						}

		  						$w_wrapper_params .= ' data-space_desktop="'.($qlslm_left + $qlslm_right).'"';
		  						$w_wrapper_params .= ' data-space_mobile="'.($qlslm_left_mobile + $qlslm_right_mobile).'"';

									$w_wrapper_classes = array('swiper', 'mfn-looped-items-slider');

									if( !empty($wrap['attr']['query_slider_linear']) ) $w_wrapper_classes[] = 'mfn-slider-linear';

									echo '<div class="'.implode(' ', $w_wrapper_classes).'" '.$w_wrapper_params.'><div class="swiper-wrapper">';
								}else if( !empty($wrap['attr']['query_display_style']) && $wrap['attr']['query_display_style'] == 'masonry' ){
									wp_enqueue_script('mfn-imagesloaded', get_theme_file_uri('/js/plugins/imagesloaded.min.js'), ['jquery'], MFN_THEME_VERSION, true);
									wp_enqueue_script('mfn-isotope', get_theme_file_uri('/js/plugins/isotope.min.js'), array('jquery'), MFN_THEME_VERSION, true);
									echo '<div class="mfn-query-loop-masonry">';
								}else if( self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){
									echo '<div class="swiper mfn-looped-items-slider">';
								}

	              $q_args = array();
	              if( !empty( $wrap['attr']['query_type'] ) && $wrap['attr']['query_type'] == 'terms' ){

              		$q_args['orderby'] = $wrap['attr']['query_terms_orderby'] ?? 'none';
              		$q_args['order'] = $wrap['attr']['query_terms_order'] ?? 'ASC';
              		$q_args['hide_empty'] = !empty($wrap['attr']['query_terms_hide_empty']) ? true : false;
              		$q_args['number'] = $wrap['attr']['query_terms_number'] ?? '0';

              		if( self::$is_bebuilder ){
              			if( !empty($wrap['attr']['query_terms_number']) ) $q_args['number'] = $wrap['attr']['query_terms_number'] > 8 ? 8 : $wrap['attr']['query_terms_number'];

              			if( !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){

              				if( empty($wrap['attr']['query_slider_columns']['desktop']) ) $wrap['attr']['query_slider_columns']['desktop'] = 1;
	              			if( !empty($wrap['attr']['query_slider_centered']) && $wrap['attr']['query_slider_centered'] == '2' ){
	            					$q_args['number'] = $wrap['attr']['query_slider_columns']['desktop'] + 2;
	            				}else{
	            					$q_args['number'] = $wrap['attr']['query_slider_columns']['desktop'];
	            				}

	            				if( !self::$updated_version ){

	            					if( empty($wrap['attr']['query_slider_columns']) ) $wrap['attr']['query_slider_columns'] = 1;
		              			if( !empty($wrap['attr']['query_slider_centered']) && $wrap['attr']['query_slider_centered'] == '2' ){
		            					$q_args['number'] = $wrap['attr']['query_slider_columns'] + 2;
		            				}else{
		            					$q_args['number'] = $wrap['attr']['query_slider_columns'];
		            				}

	            				}
	            			}

              		}

              		if( !empty($wrap['attr']['query_terms_taxonomy']) ) {
              			$choosed_terms = $wrap['attr']['query_terms_taxonomy'];
              		}else{
              			$choosed_terms = 'category';
              		}

              		$q_args['taxonomy'] = $choosed_terms;

              		$excl_var = 'query_terms_excludes_'.$choosed_terms;

              		if( !empty( $wrap['attr'][$excl_var] ) && is_array( $wrap['attr'][$excl_var] ) ){
              			$arr_helper = array();
              			foreach( $wrap['attr'][$excl_var] as $el ) {
              				if( !empty($el['key']) ) $arr_helper[] = $el['key'];
              			}
              			$q_args['exclude'] = $arr_helper;
              		}

              		$incl_var = 'query_terms_includes_'.$choosed_terms;

              		if( !empty( $wrap['attr'][$incl_var] ) && is_array( $wrap['attr'][$incl_var] ) ){
              			$arr_helper = array();
              			foreach( $wrap['attr'][$incl_var] as $el ) {
              				if( !empty($el['key']) && $el['key'] != '0-current' ) {
              					$arr_helper[] = $el['key'];
              				}else{

              					if( is_singular() ){

              						$cp_id = !empty($wrap['attr']['vb_postid']) ? $wrap['attr']['vb_postid'] : get_the_ID();
              						$cats = get_the_terms( $cp_id, $choosed_terms );

              						if( !empty($cats) ){
		              					foreach ($cats as $cat) {
		              						$arr_helper[] = $cat->term_id;
		              					}
		              				}else{
		              					$arr_helper[] = 1;
		              				}

		            					/*if( is_singular('product') ){
		            						$product_id = !empty($wrap['attr']['vb_postid']) && !is_singular('product') ? $wrap['attr']['vb_postid'] : get_the_ID();
			              					$cats = get_the_terms( $product_id, 'product_cat' );
			              					foreach ($cats as $cat) {
			              						$arr_helper[] = $cat->term_id;
			              					}
		            					}*/

		            				}else{
		            					$mfn_queried_object = get_queried_object();
              						if( isset($mfn_queried_object->term_id) ) $arr_helper[] = $mfn_queried_object->term_id;
		            				}


              				}
              			}
              			$q_args['include'] = $arr_helper;
              		}



              		$mfn_queried_object = get_queried_object();
              		$child_of = false;

              		if( !empty($wrap['attr']['query_terms_child_of_product_cat']) ) $child_of = $wrap['attr']['query_terms_child_of_product_cat'];

              		if( !empty( $child_of ) ){
              			if( $child_of != '0-current' ) {
              				$get_term = get_term_by('slug', $wrap['attr']['query_terms_child_of_product_cat'], 'product_cat');
            					if( isset($get_term->term_id) ) $q_args['child_of'] = $get_term->term_id;
            				}elseif( isset($mfn_queried_object->term_id) ) {
            					$q_args['child_of'] = $mfn_queried_object->term_id;
            				}
              		}

              		/*echo '<pre>';
              		print_r($q_args);
              		echo '</pre>';*/


              		$q_terms = get_terms( $q_args );

              		if ( !empty($q_terms) ) :
										foreach( $q_terms as $t=>$term ) {
											self::$item_type = 'term';
											self::$item_id = $term->term_id;
											if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ) {
												echo '<div class="swiper-slide">';
												echo '<div class="mfn-queryloop-item-wrapper" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
											}else{
												echo '<div class="mfn-queryloop-item-wrapper mfn-ql-item-default" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
											}

											foreach ($wrap['items'] as $i => $item) {
              					$this->show_items($item, $i, $vb, $t);
              				}

              				self::$is_ql_wrap = true;

											echo '</div>';
											if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ) echo '</div>';
											self::$item_type = false;
											self::$item_id = false;

										}

										self::$is_ql_wrap = false;

									/*else:
										foreach ($wrap['items'] as $i => $item) {
		              		$this->show_items($item, $i, $vb);
		              	}*/
									endif;

              	}else{

	              		$q_args['post_type'] = $wrap['attr']['query_post_type'] ?? 'post';

	              		if( function_exists('is_woocommerce') && !empty( $wrap['attr']['query_post_type_product_order'] ) ) {

	              			if( $wrap['attr']['query_post_type_product_order'] == 'on_sale' ){

	              				$product_on_sale = array();

	              				global $wpdb;
	              				$products_on_sale_query = $wpdb->get_results( "SELECT `post_id` FROM {$wpdb->prefix}postmeta WHERE meta_key = '_sale_price' " );

	              				if( !empty($products_on_sale_query) ){
	              					foreach($products_on_sale_query as $prod){

	              						if( get_post_status($prod->post_id) != 'publish' ) continue;

	              						if( get_post_type($prod->post_id) == 'product' ){
	              							$product_on_sale[] = $prod->post_id;
	              						}elseif( get_post_type($prod->post_id) == 'product_variation' ){
	              							$product_on_sale[] = wp_get_post_parent_id($prod->post_id);
	              						}
	              					}
	              				}


	              				$q_args['post__in'] = $product_on_sale;

	              			}else if( $wrap['attr']['query_post_type_product_order'] == 'top_rated' ){
	              				$q_args['meta_key'] = '_wc_average_rating';
		      							$q_args['orderby'] = 'meta_value_num';
		      							$q_args['order'] = 'DESC';
	              			}else{
	              				$q_args['meta_key'] = 'total_sales';
		      							$q_args['orderby'] = 'meta_value_num';
		      							$q_args['order'] = 'DESC';
	              			}

	              		}else{
	              			$q_args['orderby'] = $wrap['attr']['query_post_orderby'] ?? 'date';
	              			$q_args['order'] = $wrap['attr']['query_post_order'] ?? 'DESC';
	              		}

	              		if( self::$is_bebuilder ){


		              			if( !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' && !empty($wrap['attr']['query_slider_columns']['desktop']) ){

		              				$qs_columns = $wrap['attr']['query_slider_columns']['desktop'] ?? 1;

		              				if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'mobile' ){
		              					$qs_columns = $wrap['attr']['query_slider_columns']['mobile'] ?? $qs_columns;
		              				}else if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'laptop' ){
		              					$qs_columns = $wrap['attr']['query_slider_columns']['laptop'] ?? $qs_columns;
		              				}else if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'tablet' ){
		              					$qs_columns = $wrap['attr']['query_slider_columns']['tablet'] ?? $qs_columns;
		              				}

		              				if( !empty($wrap['attr']['query_slider_centered']) && $wrap['attr']['query_slider_centered'] == '2' ){
		              					$q_args['posts_per_page'] = $qs_columns + 2;
		              				}else{
		              					$q_args['posts_per_page'] = $qs_columns;
		              				}

		              			}else{
		              				$q_args['posts_per_page'] = !empty($wrap['attr']['query_post_per_page']) && $wrap['attr']['query_post_per_page'] < 8 ? $wrap['attr']['query_post_per_page'] : '8';
		              			}


		              			if( !self::$updated_version ) {

			              			if( !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' && !empty($wrap['attr']['query_slider_columns']) ){

			              				$qs_columns = $wrap['attr']['query_slider_columns'] ?? 1;

			              				if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'mobile' ){
			              					$qs_columns = $wrap['attr']['query_slider_columns_mobile'] ?? $qs_columns;
			              				}else if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'laptop' ){
			              					$qs_columns = $wrap['attr']['query_slider_columns_laptop'] ?? $qs_columns;
			              				}else if( !empty( $wrap['attr']['rwd'] ) && $wrap['attr']['rwd'] == 'tablet' ){
			              					$qs_columns = $wrap['attr']['query_slider_columns_tablet'] ?? $qs_columns;
			              				}

			              				if( !empty($wrap['attr']['query_slider_centered']) && $wrap['attr']['query_slider_centered'] == '2' ){
			              					$q_args['posts_per_page'] = $qs_columns + 2;
			              				}else{
			              					$q_args['posts_per_page'] = $qs_columns;
			              				}
			              			}else{
			              				$q_args['posts_per_page'] = !empty($wrap['attr']['query_post_per_page']) && $wrap['attr']['query_post_per_page'] < 8 ? $wrap['attr']['query_post_per_page'] : '8';
			              			}

			              		}

	              		}else{
	              			$q_args['posts_per_page'] = !empty($wrap['attr']['query_post_per_page']) ? $wrap['attr']['query_post_per_page'] : get_option( 'posts_per_page' );
	              		}

	              		$q_args['offset'] = $wrap['attr']['query_post_offset'] ?? '0';

	              		if( !empty($q_args['post_type']) ) {
	              			$tax_q = $this->getTaxQuery($q_args, $wrap);
	              			if( !empty($tax_q) ) $q_args = array_merge($q_args, $tax_q);
	              		}

	              		$q_args['post_status'] = 'publish';
	              		if( !empty($wrap['attr']['query_post_ignore_sticky_posts']) ) $q_args['ignore_sticky_posts'] = true;


	              		if( (!empty($wrap['attr']['query_type']) || !empty($wrap['attr']['query_loop_is_main'])) && in_array($this->template_type, array('portfolio', 'blog')) && (is_home() || is_category() || is_tag() || is_author() || is_tax( 'portfolio-types' ) ) ) {

	              			global $wp_query;
	              			$wrap_posts_query = $wp_query;

	              		}else if( ( (!empty($wrap['attr']['query_type']) && empty( get_post_meta($this->post_id, 'mfn-tmpl-has-main-query', true) )) || !empty($wrap['attr']['query_loop_is_main'])) && is_archive() && strpos($this->template_type, 'archive-') !== false ) {

	              			global $wp_query;
	              			$wrap_posts_query = $wp_query;

	              		}else{

		              		/*if( (!empty($wrap['attr']['query_type']) || !empty($wrap['attr']['query_loop_is_main'])) && in_array($this->template_type, array('portfolio', 'blog')) && (is_home() || is_category() || is_tag() || is_author() ) ) {
		              			global $wp_query;

		              			$wrap_posts_query = $wp_query;

	              			}else if( (!empty($wrap['attr']['query_type']) || !empty($wrap['attr']['query_loop_is_main'])) && (is_home() || is_home() || is_category() || is_tag() || is_author() || is_archive()) && strpos($this->template_type, 'archive-') !== false ) {
		              			global $wp_query;

		              			$wrap_posts_query = $wp_query;

											}else{*/

											$q_args['offset'] = $wrap['attr']['query_post_offset'] ?? '0';

		              		/*if( !empty($q_args['post_type']) ) {
		              			$tax_q = $this->getTaxQuery($q_args, $wrap);
		              			if( !empty($tax_q) ) $q_args = array_merge($q_args, $tax_q);
		              		}*/

		              		$q_args['post_status'] = 'publish';

	              			$wrap_posts_query = new WP_Query( $q_args );

              			}

	              		if ( $wrap_posts_query->have_posts() ) :

	              			$w_iterate = 0;
	              			while ( $wrap_posts_query->have_posts() ) : $wrap_posts_query->the_post();
	              				self::$item_type = 'post';
												self::$item_id = get_the_ID();

												$wrapqi_inner_inline_styles = false;

												if( !self::$updated_version ) {
													if ( !empty( $wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:background-image'] ) && strpos($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:background-image'], '{featured_image') !== false ) {
														$wrapqi_bg_dd = be_dynamic_data($wrap['attr']['style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner .mfn-queryloop-item-wrapper:background-image']);
														if( is_numeric($wrapqi_bg_dd) ) $wrapqi_bg_dd = wp_get_attachment_image_src( $wrapqi_bg_dd, 'full' )[0];
														$wrapqi_inner_inline_styles = 'style="background-image: url('.$wrapqi_bg_dd.')"';
													}

													if ( !empty( $wrap['attr']['css_queryloop_item_bg_image']['val'] ) && strpos($wrap['attr']['css_queryloop_item_bg_image']['val'], '{featured_image') !== false ) {
														$wrapqi_bg_dd = be_dynamic_data($wrap['attr']['css_queryloop_item_bg_image']['val']);
														if( is_numeric($wrapqi_bg_dd) ) $wrapqi_bg_dd = wp_get_attachment_image_src( $wrapqi_bg_dd, 'full' )[0];
														$wrapqi_inner_inline_styles = 'style="background-image: url('.$wrapqi_bg_dd.')"';
													}
												}else{



													$bg_vals = $wrap['attr']['css_queryloop_item_bg_image']['val'] ?? array();
													$bg_vals_hover = $wrap['attr']['css_queryloop_item_bg_image_hover']['val'] ?? array();

													if ( ! empty(array_filter($bg_vals)) ) {
														$style_prop = $wrap['attr']['css_queryloop_item_bg_image']['style'] ?? 'background-image';
														$selector  = 'html body .mcb-section .mcb-wrap-'.$wrap['uid'].' .mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"]';
														$css_output = '';
														foreach( $bg_vals as $device => $bg ) {
															if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
															$get_bg = be_dynamic_data($bg);
															if( !empty($get_bg) && is_numeric($get_bg) ) {
																$get_bg = wp_get_attachment_image_url($get_bg, 'full');
															}
															if( empty($get_bg) ) continue;
															$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
															if( $device === 'desktop' ) {
																$css_output .= $rule;
															} elseif( $device === 'laptop' ) {
																$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
															} elseif( $device === 'tablet' ) {
																$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
															} elseif( $device === 'mobile' ) {
																$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
															}
														}
														if( !empty($css_output) ) {
															echo '<style>' . $css_output . '</style>';
														}
													}

													if ( ! empty(array_filter($bg_vals_hover)) ) {
														$style_prop = $wrap['attr']['css_queryloop_item_bg_image_hover']['style'] ?? 'background-image';
														$selector  = 'html body .mcb-section .mcb-wrap-'.$wrap['uid'].' .mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"]:hover';
														$css_output = '';
														foreach( $bg_vals_hover as $device => $bg ) {
															if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
															$get_bg = be_dynamic_data($bg);
															if( !empty($get_bg) && is_numeric($get_bg) ) {
																$get_bg = wp_get_attachment_image_url($get_bg, 'full');
															}
															if( empty($get_bg) ) continue;
															$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
															if( $device === 'desktop' ) {
																$css_output .= $rule;
															} elseif( $device === 'laptop' ) {
																$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
															} elseif( $device === 'tablet' ) {
																$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
															} elseif( $device === 'mobile' ) {
																$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
															}
														}
														if( !empty($css_output) ) {
															echo '<style>' . $css_output . '</style>';
														}
													}




													/*if ( !empty( $wrap['attr']['css_queryloop_item_bg_image']['val']['desktop'] ) && strpos($wrap['attr']['css_queryloop_item_bg_image']['val']['desktop'], '{featured_image') !== false ) {
														$wrapqi_bg_dd = be_dynamic_data($wrap['attr']['css_queryloop_item_bg_image']['val']['desktop']);
														if( is_numeric($wrapqi_bg_dd) ) $wrapqi_bg_dd = wp_get_attachment_image_src( $wrapqi_bg_dd, 'full' )[0];
														$wrapqi_inner_inline_styles = 'style="background-image: url('.$wrapqi_bg_dd.'); color: inherit;"';
													}*/

												}

	              				if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ) {
	              					echo '<div class="swiper-slide">';
	              					echo '<div '.$wrapqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
	              				}else{
	              					echo '<div '.$wrapqi_inner_inline_styles.' class="mfn-queryloop-item-wrapper mfn-ql-item-default" data-post="'.(!empty(self::$item_id) ? self::$item_id : $this->post_id).'">';
	              				}

	              				foreach ($wrap['items'] as $i => $item) {
	              					$this->show_items($item, $i, $vb, $w_iterate);
	              				}

	              				self::$is_ql_wrap = true;

	              				echo '</div>';
	              				if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ) { echo '</div>'; }
	              				self::$item_type = false;
												self::$item_id = false;
												$w_iterate++;
	              			endwhile;
	              			self::$is_ql_wrap = false;
	              			wp_reset_postdata();

	              		else:
	              			foreach ($wrap['items'] as $i => $item) {
			              		$this->show_items($item, $i, $vb);
			              	}
	              		endif;

              	}

              if( !self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){
              	echo '</div></div>';
              }else if( !empty($wrap['attr']['query_display_style']) && $wrap['attr']['query_display_style'] == 'masonry' ){
								echo '</div>';
							}else if( self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' ){
								echo '</div>';
							}

							if( self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' && !empty($wrap['attr']['query_slider_arrows']) ) {
            		echo '<div class="swiper-button-next mfn-swiper-arrow" role="button" aria-label="Next slide" aria-disabled="false"><i class="'.( !empty($wrap['attr']['query_display_slider_arrow_next']) ? $wrap['attr']['query_display_slider_arrow_next'] : "icon-right-open-big" ).'"></i></div>';
            		echo '<div class="swiper-button-prev mfn-swiper-arrow" role="button" aria-label="Previous slide" aria-disabled="false"><i class="'.( !empty($wrap['attr']['query_display_slider_arrow_prev']) ? $wrap['attr']['query_display_slider_arrow_prev'] : "icon-left-open-big" ).'"></i></div>';
            	}

            	if( self::$is_bebuilder && !empty($wrap['attr']['query_display']) && $wrap['attr']['query_display'] == 'slider' && !empty($wrap['attr']['query_slider_dots']) ) {
            		echo '<div class="swiper-pagination swiper-pagination-bullets"><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span></div>';
            	}

              }else{
              	//if( self::$is_bebuilder && isset($wrap['attr']['type']) && $wrap['attr']['type'] == 'query' ) echo '<div class="mfn-queryloop-item-wrapper mfn-ql-item-default">';
              	foreach ($wrap['items'] as $i => $item) {
              		$this->show_items($item, $i, $vb, $s_iterate);
              	}
              	//if( self::$is_bebuilder && isset($wrap['attr']['type']) && $wrap['attr']['type'] == 'query' ) echo '</div>';
              }

            	//$this->show_items($item, $i, $vb);

					}

				echo '</div>';

			echo '</div>';

			/*if ( isset($wrap_posts_query) && $wrap_posts_query->have_posts() ) :

					if( !empty($wrap['attr']['query_post_pagination']) ){
	  				if( self::$is_bebuilder ){
	  					$mfnQPagination = new MfnQueryPagination($wrap, true);
	  					$mfnQPagination->bebuilderHtml();
	  				}else{
	  					$mfnQPagination = new MfnQueryPagination($wrap, $wrap_posts_query);
	  					$mfnQPagination->render();
	  				}

					}

				endif;*/

  	}

  	public function show_items($item, $i, $vb, $w_iterate = false){

  		/*echo '<pre>';
  		print_r($item);
  		echo '</pre>';*/

  		if( !empty($item['jsclass']) && $item['jsclass'] == 'woo_alert' && !$vb && ( wc_notice_count() == 0 || !function_exists('is_woocommerce') ) ){
  			return;
  		}

  		if( !empty($item['jsclass']) && $item['jsclass'] == 'product_rating' && !$vb && is_singular('product')) {
  			if( !wc_review_ratings_enabled() || !function_exists('is_woocommerce') ) return;

  			$product_id = false;

  			if( !empty(self::$item_id) && get_post_type(self::$item_id) == 'product' ) {
  				$product_id = self::$item_id;
  			}else if( is_singular() ) {
  				$product_id = get_the_ID();
  			}

  			if( !empty($product_id) && get_post_type($product_id) == 'product' ) {
  				$product = wc_get_product($product_id);

  				if ( ! $product ) {
			        return; // invalid ID or not a product
			    }

  				$rating_count = $product->get_rating_count();

  				if( empty($rating_count) ) return;
  			}


  		}



  		self::$item_obj = $item;

  		$inner_class = array();
  		$item_class = array();

  		if( !empty($item['item_is_wrap']) ) {
  			//if( self::$is_ql_section ) self::$is_ql_section = false;
  			//if( self::$is_ql_wrap ) self::$is_ql_wrap = true;
  			$this->show_wraps($item, $i, $vb);
  			return;
  		}

  		if( !empty( $item['be_classes']) && is_array($item['be_classes']) ){
				foreach($item['be_classes'] as $bc) {
					$item_class[] = $bc;
					if( isset($this->be_classes[$bc]) ){
						if( isset($item['attr']['width_switcher']) && $item['attr']['width_switcher'] == 'default' ) unset($item['attr']['width_switcher']);
						$item['attr'] = array_merge($this->be_classes[$bc], $item['attr']);
					}
				}
			}

			/*print_r($item);*/

  		if( !empty( $item['attr']['conditions'] ) ) {
  			if( !self::$is_bebuilder ){
	  			$mfnConditions = new MfnConditionalLogic();
	  			if( !$mfnConditions->verify( $item['attr']['conditions'] ) ){
	  				return;
	  			}
	  		}else{
	  			$inner_class[] = 'mfn-conditional-logic';
	  		}
  		}

  		if( empty($item['laptop_size']) ){
    		$item['laptop_size'] = !empty($item['size']) ? $item['size'] : '1/1';
    	}

  		if( empty($item['tablet_size']) ){
    		$item['tablet_size'] = !empty($item['size']) ? $item['size'] : '1/1';
    		$item['mobile_size'] = '1/1';
    	}

    	if( !empty($item['type']) && $item['type'] == 'shop_products' && function_exists('is_woocommerce') ) $item_class[] = 'woocommerce';

			$type = 'item_'. $item['type'];

			if ( method_exists( 'Mfn_Builder_Items', $type ) ) {

				$animate = '';

				// FIX: LUK empty wrap created in error

				if( empty( $item['size'] ) ){
        	return;
        }

				if( ! isset( $item['attr'] ) ){
					$item['attr'] = isset( $item['fields'] ) && is_array($item['fields']) ? $item['fields'] : array();
				}

				// unique ID

				if( empty( $item['uid'] ) ) {
					$item['uid'] = Mfn_Builder_Helper::unique_ID();
				}

				$item_class[] = 'mcb-item-'. $item['uid'];

				/*echo '<pre>';
				print_r($item);
				echo '</pre>';*/

				// WPML Workaround for compsupp-7547
				if( ! empty($item['attr']['content']) ){
					$item['attr']['content'] = apply_filters( 'wpml_translate_link_targets', $item['attr']['content'] );
				}

				if( empty(self::$item_id) && !empty($item['attr']['vb_postid']) ){
					self::$item_id = $item['attr']['vb_postid'];
				}

				// size

				if( isset( $this->classes[$item['size']] ) ){
					$item_class[] = $this->classes[$item['size']];
				}

				if( isset( $item['laptop_size'] ) ){
					$item_class[] = $this->laptop_classes[$item['laptop_size']];
				}else{
					$item_class[] = $this->laptop_classes[$item['size']];
				}

				if( isset( $item['tablet_size'] ) ){
					$item_class[] = $this->tablet_classes[$item['tablet_size']];
				}else{
					$item_class[] = $this->tablet_classes[$item['size']];
				}

				if( isset( $item['mobile_size'] ) ){
					$item_class[] = $this->mobile_classes[$item['mobile_size']];
				}else{
					$item_class[] = '1/1';
				}

				if( !empty($item['attr']['css_line_clamp']) ){
					$item_class[] = 'mfn-line-clamp';
				}

				// type

				$item_class[] = 'column_'. $item['type'];

				// animate

				if ( ! empty( $item['attr']['animate'] ) ) {
					$item_class[] = 'animate';
					$animate = 'data-anim-type="'. $item['attr']['animate'] .'"';
				}

				// custom classes

				if ( ! empty($item['attr']['classes']) ) {
					$item_class[] = $item['attr']['classes'];
				}

				if ( ! empty($item['attr']['width_switcher']) ) {
					if( $item['attr']['width_switcher'] == 'inline' ){
						$item_class[] = 'mfn-item-inline';
					}else if( $item['attr']['width_switcher'] == 'custom' ){
						$item_class[] = 'mfn-item-custom-width';
					}
				}

				if ( ! empty($item['attr']['visibility']) ) {
					$item_class[] = $item['attr']['visibility'];
				}

				// margin bottom

				if ($item['type'] == 'column' && (! empty($item['attr']['margin_bottom']))) {
					$item_class[] = 'column-margin-'. $item['attr']['margin_bottom'];
				}

				// pricing item

				if( 'pricing_item' == $item['type'] && ! empty($item['attr']['style']) ) {
					$item_class[] = 'pricing_item-style-'. $item['attr']['style'];
				}

				// position absolute

				if( !empty($item['attr']['css_advanced_position']) && !empty($item['attr']['css_advanced_position']['val']['desktop']) && $item['attr']['css_advanced_position']['val']['desktop'] == 'absolute' ){
					$item_class[] = 'mfn-column-absolute';
				}

				if( !self::$updated_version ) {
					if( !empty($item['attr']['css_advanced_position']) && $item['attr']['css_advanced_position']['val'] == 'absolute' ){
						$item_class[] = 'mfn-column-absolute';
					}
				}

				// custom id

				if(key_exists('custom_id', $item['attr']) && $item['attr']['custom_id']) {
					$item_id = 'id="'. $item['attr']['custom_id'] .'"';
				} else {
					$item_id = false;
				}

				$item_style = '';
				$item_inline_style = false;

				// ACM new input name
				if( ! empty( $item['attr']['custom_css'] ) ){
					$item_style .= $item['attr']['custom_css'];
				}

				$bg_vals = $item['attr']['css_advanced_background_image']['val'] ?? array();
				$bg_vals_hover = $item['attr']['css_advanced_background_image_hover']['val'] ?? array();

				if ( ! empty(array_filter($bg_vals)) ) {
					$style_prop = $item['attr']['css_advanced_background_image']['style'] ?? 'background-image';
					$selector  = '.mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"] .mcb-item-'.$item['uid'].' > .mcb-column-inner';
					$css_output = '';
					foreach( $bg_vals as $device => $bg ) {
						if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
						$get_bg = be_dynamic_data($bg);
						if( !empty($get_bg) && is_numeric($get_bg) ) {
							$get_bg = wp_get_attachment_image_url($get_bg, 'full');
						}
						if( empty($get_bg) ) continue;
						$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
						if( $device === 'desktop' ) {
							$css_output .= $rule;
						} elseif( $device === 'laptop' ) {
							$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
						} elseif( $device === 'tablet' ) {
							$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
						} elseif( $device === 'mobile' ) {
							$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
						}
					}
					if( !empty($css_output) ) {
						echo '<style>' . $css_output . '</style>';
					}
				}

				if ( ! empty(array_filter($bg_vals_hover)) ) {
					$style_prop = $item['attr']['css_advanced_background_image_hover']['style'] ?? 'background-image';
					$selector  = '.mfn-queryloop-item-wrapper[data-post="' . self::$item_id . '"] .mcb-item-'.$item['uid'].':hover > .mcb-column-inner';
					$css_output = '';
					foreach( $bg_vals_hover as $device => $bg ) {
						if( ! is_string($bg) || strpos($bg, '{') === false ) continue;
						$get_bg = be_dynamic_data($bg);
						if( !empty($get_bg) && is_numeric($get_bg) ) {
							$get_bg = wp_get_attachment_image_url($get_bg, 'full');
						}
						if( empty($get_bg) ) continue;
						$rule = $selector . '{' . $style_prop . ':url(' . esc_url($get_bg) . ');}';
						if( $device === 'desktop' ) {
							$css_output .= $rule;
						} elseif( $device === 'laptop' ) {
							$css_output .= '@media only screen and (max-width: 1440px){' . $rule . '}';
						} elseif( $device === 'tablet' ) {
							$css_output .= '@media only screen and (max-width: 959px){' . $rule . '}';
						} elseif( $device === 'mobile' ) {
							$css_output .= '@media only screen and (max-width: 767px){' . $rule . '}';
						}
					}
					if( !empty($css_output) ) {
						echo '<style>' . $css_output . '</style>';
					}
				}


				/*if ( !empty( $item['attr']['css_advanced_background_image']['val']['desktop'] ) && !empty($item['attr']['css_advanced_background_image']['val']['desktop']) && strpos($item['attr']['css_advanced_background_image']['val']['desktop'], '{featured_image') !== false ){
					$item_bg_dd = be_dynamic_data($item['attr']['css_advanced_background_image']['val']['desktop']);
					if( is_numeric($item_bg_dd) ) $item_bg_dd = wp_get_attachment_image_src( $item_bg_dd, 'full' )[0];
					$item_inline_style = 'style="background-image: url('.$item_bg_dd.')"';
				}

				if ( !empty( $item['attr']['css_advanced_background_image_hover']['val']['desktop'] ) && strpos($item['attr']['css_advanced_background_image_hover']['val']['desktop'], '{featured_image') !== false ){
					$item_bg_dd = be_dynamic_data($item['attr']['css_advanced_background_image_hover']['val']['desktop']);
					if( is_numeric($item_bg_dd) ) $item_bg_dd = wp_get_attachment_image_src( $item_bg_dd, 'full' )[0];
					echo '<style>.mcb-section .mcb-wrap .mcb-item-'.$item['uid'].' .mcb-column-inner:hover{background-image: url('.$item_bg_dd.') !important}</style>';
				}*/


				if( !self::$updated_version ) {

					if ( !empty( $item['attr']['css_advanced_background_image']['val'] ) && strpos($item['attr']['css_advanced_background_image']['val'], '{featured_image') !== false ){
						$item_bg_dd = be_dynamic_data($item['attr']['css_advanced_background_image']['val']);
						if( is_numeric($item_bg_dd) ) $item_bg_dd = wp_get_attachment_image_src( $item_bg_dd, 'full' )[0];
						$item_inline_style = 'style="background-image: url('.$item_bg_dd.')"';
					}

					if ( !empty( $item['attr']['css_advanced_background_image_hover']['val'] ) && strpos($item['attr']['css_advanced_background_image_hover']['val'], '{featured_image') !== false ){
						$item_bg_dd = be_dynamic_data($item['attr']['css_advanced_background_image_hover']['val']);
						if( is_numeric($item_bg_dd) ) $item_bg_dd = wp_get_attachment_image_src( $item_bg_dd, 'full' )[0];
						echo '<style>.mcb-section .mcb-wrap .mcb-item-'.$item['uid'].' .mcb-column-inner:hover{background-image: url('.$item_bg_dd.') !important}</style>';
					}

				}

				$desktop_size = $item['size'];
				$laptop_size = !empty($item['laptop_size']) ? $item['laptop_size'] : $desktop_size;
				$tablet_size = !empty($item['tablet_size']) ? $item['tablet_size'] : $desktop_size;
				$mobile_size = !empty($item['mobile_size']) ? $item['mobile_size'] : '1/1';

				if (!empty($item['attr']['css_advanced_transform']) || !empty($item['attr']['css_advanced_transform_hover'])) {
					$item_class[] = 'mfn-transformed';
				}

				if( $item['type'] == 'product_images' && !empty($item['attr']['thumbnail_arrows']) ){
					$item_class[] = 'mfn-thumbnails-arrows-active';
				}

				$inner_class[] = 'mcb-column-inner mfn-module-wrapper mcb-column-inner-'.$item['uid'].' mcb-item-'.$item['type'].'-inner';

				$item_class	= implode(' ', $item_class);

				// output -----

				if( mfn_is_blocks() ) {
					$item_style = '';
					$parallax = '';
				}

				if( $vb ) {
					$tooltip = false;

					if( !self::$is_ql_section && !self::$is_ql_wrap ) $item_class .= ' vb-item';

					echo '<div '.$tooltip.' '.$item_id.' data-order="'. $i .'"  data-uid="'. $item['uid'] .'" data-minsize="'.$item['size'].'" data-desktop-size="'.$desktop_size.'" data-laptop-size="'.$laptop_size.'" data-tablet-size="'.$tablet_size.'" data-mobile-size="'.$mobile_size.'" class="column mcb-column '. $item_class .' mfn-module" style="'.$item_style.'">';
					// echo Mfn_Builder_Helper::itemTools($item['size']);
					echo '<div class="mfn-drag-helper mfn-dh-before placeholder-column"></div><div class="mfn-drag-helper mfn-dh-after placeholder-column"></div>';
				} else {
					if( !$vb ) $item_class .= ' vb-item';
					echo '<div '. $item_id .' class="column mcb-column '. $item_class .'" style="'. $item_style .'" '. $animate .'>';
				}

					// Transforms UI --- visible only when transformed an item
					if( $vb && !$w_iterate && !mfn_is_blocks($vb) ) {
						echo '<div class="mfn-header-transform">';
							echo Mfn_Builder_Helper::itemTools($desktop_size);
						echo '</div>';
					}

					echo '<div class="'.implode(' ', $inner_class).'">';
						if( $vb && !$w_iterate ){
							echo Mfn_Builder_Helper::itemTools($desktop_size);
						}

						if( mfn_is_blocks($vb) ){
							echo Mfn_Builder_Items::blocks( $item, $this->blocks_fields );
						} else {
							echo Mfn_Builder_Items::$type( $item['attr'], $vb );
						}
						/*echo '<pre>';
						print_r($item['attr']);
						echo '</pre>';*/
					echo '</div>';

				echo '</div>';

				if( $vb && !$w_iterate && !mfn_is_blocks($vb) && ( (!empty( $item['attr']['link_type'] ) && $item['attr']['link_type'] == 'mfn-read-more') || (!empty( $item['attr']['button_function'] ) && $item['attr']['button_function'] == 'mfn-read-more') ) ) {
					echo '<span class="mfn-read-more-line"></span>';
				}

				// if( $item_id_from_vb ) self::$item_id = false;
			}


			self::$item_obj = false;

  	}


  	public function getTaxQuery($q_args, $obj) {

	  	$tax_filter = array();
	  	$tax_filter_excl = array();
	  	$tax_q = array('tax_query' => array('relation' => 'AND'));

	  	$post_tax = 'query_post_type_'.$q_args['post_type'];
			$post_tax_excl = 'query_post_type_'.$q_args['post_type'].'_exclude';


			if( !empty($obj['attr'][$post_tax]) && is_iterable($obj['attr'][$post_tax]) ) {

				foreach( $obj['attr'][$post_tax] as $tax_obj ) {

					if( !empty($tax_obj['key']) ) {
						if( in_array($tax_obj['key'], array('0-current', 'undefined')) ) {
							if( is_singular() ) {
								$post_id = get_the_ID();
								$mfn_taxonomies = get_object_taxonomies( get_post_type( $post_id ) );
								foreach ( $mfn_taxonomies as $tx ) {
										if( $tx == 'product_type' ) continue;
										if( $tx == 'translation_priority' ) continue;
								    $terms = get_the_terms( $post_id, $tx );
								    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								        foreach ( $terms as $term ) {
								            $tax_filter[$tx][] = $term->term_id;
								        }
								    }
								}
								$tax_q['post__not_in'] = array($post_id);
							}else{
								$mfn_queried_object = get_queried_object();
								if( !empty($mfn_queried_object->term_id) ) $tax_filter[$mfn_queried_object->taxonomy][] = $mfn_queried_object->term_id;
							}
						}else{
							$term = get_term($tax_obj['key']);

							if (!is_wp_error($term)) {
								$taxonomy_slug = $term->taxonomy;
								if( !isset($tax_filter[$taxonomy_slug]) ) $tax_filter[$taxonomy_slug] = array();
								$tax_filter[$taxonomy_slug][] = $tax_obj['key'];
							}
						}
					}
				}

				if( !empty($tax_filter) ) {
					foreach( $tax_filter as $t=>$tx ) {
						$tax_q['tax_query'][] = array('taxonomy' => $t, 'field' => 'term_id', 'operator' => 'IN', 'terms' => $tx);
					}
				}

			}

			if( !empty($obj['attr'][$post_tax_excl]) && is_iterable($obj['attr'][$post_tax_excl]) ) {
				foreach( $obj['attr'][$post_tax_excl] as $tax_obj) {
					if( !empty($tax_obj['key']) ) {
						if( in_array($tax_obj['key'], array('0-current', 'undefined')) ){
							if( is_singular() ) {
								$post_id = get_the_ID();
								$tax_q['post__not_in'] = array($post_id);
								$mfn_taxonomies = get_object_taxonomies( get_post_type( $post_id ) );
								foreach ( $mfn_taxonomies as $tx ) {
										if( $tx == 'product_type' ) continue;
										if( $tx == 'translation_priority' ) continue;
								    $terms = get_the_terms( $post_id, $tx );
								    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								        foreach ( $terms as $term ) {
								            $tax_filter_excl[$tx][] = $term->term_id;
								        }
								    }
								}
							}else{
								$mfn_queried_object = get_queried_object();
								if( !empty($mfn_queried_object->term_id) ) $tax_filter_excl[$mfn_queried_object->taxonomy][] = $mfn_queried_object->term_id;
							}
						}else{
							$term = get_term($tax_obj['key']);
							if (!is_wp_error($term)) {
								$taxonomy_slug = $term->taxonomy;
								if( !isset($tax_filter_excl[$taxonomy_slug]) ) $tax_filter_excl[$taxonomy_slug] = array();
								$tax_filter_excl[$taxonomy_slug][] = $tax_obj['key'];
							}
						}
					}
				}

				if( !empty($tax_filter_excl) ) {
					foreach( $tax_filter_excl as $t=>$tx ){
						$tax_q['tax_query'][] = array('taxonomy' => $t, 'field' => 'term_id', 'operator' => 'NOT IN', 'terms' => $tx);
					}
				}

			}

			return $tax_q;

  	}

  }

}
