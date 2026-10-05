<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

class Mfn_Helper {

	/**
	 * Initialises and connects the WordPress Filesystem
	 */

	public static function filesystem(){

		global $wp_filesystem;

		if( ! defined( 'FS_METHOD' ) ){
			define( 'FS_METHOD', 'direct' );
		}

		if( ! defined( 'FS_CHMOD_DIR' ) ){
			define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
		}

		if( ! defined( 'FS_CHMOD_FILE' ) ){
			define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
		}

		if( empty( $wp_filesystem ) ){
			require_once wp_normalize_path( ABSPATH .'/wp-admin/includes/file.php' );
		}

		WP_Filesystem();

		return $wp_filesystem;
	}

	/**
	 * Prepare local styles and fonts before update
	 */

	public static function preparePostUpdate($object, $post_id = false, $key = false) {

		$devices = array('desktop', 'laptop', 'tablet', 'mobile');

		$return = array();
		$return['custom'] = array();
		$return['dynamic_styles'] = array();
		$return['desktop'] = array();
		$return['tablet'] = array();
		$return['laptop'] = array();
		$return['mobile'] = array();
		$return['query_modifiers'] = array();
		$return['has_main_query'] = false;
		$return['fonts'] = array();
		$return['sidemenus'] = array();
		$return['cart'] = array();
		$return['custom_alert'] = false;

		$additional_css = array(

		    'css_menu_li-submenulia_justify_content' => array(
		        'selector' 	=> '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-header-tmpl-menu-sidebar .mfn-header-menu li .mfn-submenu li a',
		        'style' 	=> 'text-align',
		        'rewrites'  => array(
		            'flex-start' => 'left',
		            'flex-end' => 'right',
		            'center' => 'center',
		        )
		    ),

		    'css_product_text_align' => array(
		        'selector' 	=> '.mcb-section .mcb-wrap .mcb-item-mfnuidelement ul.products li.product',
		        'style' 	=> 'align-items',
		        'rewrites'  => array(
		            'left' => 'flex-start',
		            'right' => 'flex-end',
		            'center' => 'center',
		        )
		    ),

		    'css_banner-box_text_align' => array(
		        'selector' 	=> '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-banner-box .banner-wrapper',
		        'style' 	=> 'align-items',
		        'rewrites'  => array(
		            'left' => 'flex-start',
		            'right' => 'flex-end',
		            'center' => 'center',
		        )
		    ),

		    'css_grid_item_vertical_align' => array(
		        'selector' 	=> '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-grid-item .mfn-grid-item-desc',
		        'style' 	=> 'text-align',
		        'rewrites'  => array(
		            'flex-start' => 'left',
		            'flex-end' => 'right',
		            'center' => 'center',
		        )
		    ),

		);

		/*echo $post_id."\r\n";
		print_r($object);
		echo "\r\n"."\r\n";*/

		$tmpl_type = get_post_meta($post_id, 'mfn_template_type', true);

		$meta_key = $key ? $key : 'mfn-page-local-style';

		$lang_postfix = '';

		if( !empty($post_id) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$post_lang = apply_filters( 'wpml_post_language_details', NULL, $post_id ) ;
			$default_lang = apply_filters('wpml_default_language', NULL );

			if( ! is_wp_error($post_lang) && is_string($default_lang) && is_string($post_lang) && $default_lang != $post_lang ){
				$lang_postfix = '_'. $post_lang;
			}
		}

		if( !empty($object) && count($object) > 0 ) {
			foreach ($object as $i => $item) {

				// woo alerts
				if( !empty($item['jsclass']) && $item['jsclass'] == 'woo_alert' ) {
					$return['custom_alert'] = true;
				}

				if( !empty($item['attr']) ) {

					// fonts
					if( !empty($item['attr']['used_fonts']) ) {
						$fonts_arr = explode(',', $item['attr']['used_fonts']);
						$return['fonts'] = array_unique(array_merge($return['fonts'],$fonts_arr));
					}

					// query modifiers

					if ( strpos($tmpl_type, 'archive-') === 0 || in_array($tmpl_type, array('blog', 'portfolio')) ) {

					    if ( isset($item['jsclass']) && in_array($item['jsclass'], array('blog', 'portfolio')) ) {
					        if (!empty($item['attr']['count'])) $return['query_modifiers']['posts_per_page'] = $item['attr']['count'];
					        if (!empty($item['attr']['orderby'])) $return['query_modifiers']['orderby'] = $item['attr']['orderby'];
					        if (!empty($item['attr']['order'])) $return['query_modifiers']['order'] = $item['attr']['order'];
					    }
					    
					    if( isset( $item['jsclass'] ) && in_array( $item['jsclass'], array( 'wrap', 'section' ) ) && !empty( $item['attr']['type'] ) && $item['attr']['type'] == 'query' && empty( $item['attr']['query_type'] ) ) {

							if ( ! empty( $item['attr']['query_post_per_page'] ) ) {
								$return['query_modifiers']['posts_per_page'] = (int) $item['attr']['query_post_per_page'];
							}

							if ( ! empty( $item['attr']['query_post_order'] ) ) {
								$return['query_modifiers']['order'] = $item['attr']['query_post_order'];
							}

							if ( isset( $item['attr']['query_post_offset'] ) && $item['attr']['query_post_offset'] !== '' ) {
								$return['query_modifiers']['base_offset'] = (int) $item['attr']['query_post_offset'];
							}

						}

						$return['has_main_query'] = true;

					}

					if( !empty($item['attr']['sidebar_type']) ) {
						$return['sidemenus'][] = $item['attr']['sidebar_type'];
					}

					// cart
					if( !empty($item['jsclass']) && in_array($item['jsclass'], array('cart_totals', 'cart_table')) ) {

						$cth = 'cart_totals_heading'.$lang_postfix;
						$pch = 'proceed_checkout_label'.$lang_postfix;
						$cshst = 'continue_shopping_string'.$lang_postfix;

						$ucl = 'update_cart_label'.$lang_postfix;
						$ccp = 'coupon_code_placeholder'.$lang_postfix;
						$acl = 'apply_coupon_label'.$lang_postfix;

						// cart totals labels
						if( !empty($item['attr']['cart_totals_heading']) ) $return['cart']['cart_totals'][$cth] = $item['attr']['cart_totals_heading'];
						if( !empty($item['attr']['proceed_checkout_label']) ) $return['cart']['cart_totals'][$pch] = $item['attr']['proceed_checkout_label'];
						if( !empty($item['attr']['continue_shopping_string']) ) $return['cart']['cart_totals'][$cshst] = $item['attr']['continue_shopping_string'];

						// cart table labels
						if( !empty($item['attr']['update_cart_label']) ) $return['cart']['cart_table'][$ucl] = $item['attr']['update_cart_label'];
						if( !empty($item['attr']['coupon_code_placeholder']) ) $return['cart']['cart_table'][$ccp] = $item['attr']['coupon_code_placeholder'];
						if( !empty($item['attr']['apply_coupon_label']) ) $return['cart']['cart_table'][$acl] = $item['attr']['apply_coupon_label'];

					}


					/* TMP - while generating css */
					if( !empty($item['jsclass']) && $item['jsclass'] == 'video' ) {
						if( !empty($item['attr']['css_video_width']['val']['desktop']) && is_numeric($item['attr']['css_video_width']['val']['desktop']) ) $item['attr']['css_video_width']['val']['desktop'] .= 'px';
						if( !empty($item['attr']['css_video_width']['val']['laptop']) && is_numeric($item['attr']['css_video_width']['val']['laptop']) ) $item['attr']['css_video_width']['val']['laptop'] .= 'px';
						if( !empty($item['attr']['css_video_width']['val']['tablet']) && is_numeric($item['attr']['css_video_width']['val']['tablet']) ) $item['attr']['css_video_width']['val']['tablet'] .= 'px';
						if( !empty($item['attr']['css_video_width']['val']['mobile']) && is_numeric($item['attr']['css_video_width']['val']['mobile']) ) $item['attr']['css_video_width']['val']['mobile'] .= 'px';
						if( !empty($item['attr']['css_video_height']['val']['desktop']) && is_numeric($item['attr']['css_video_height']['val']['desktop']) ) $item['attr']['css_video_height']['val']['desktop'] .= 'px';
						if( !empty($item['attr']['css_video_height']['val']['laptop']) && is_numeric($item['attr']['css_video_height']['val']['laptop']) ) $item['attr']['css_video_height']['val']['laptop'] .= 'px';
						if( !empty($item['attr']['css_video_height']['val']['tablet']) && is_numeric($item['attr']['css_video_height']['val']['tablet']) ) $item['attr']['css_video_height']['val']['tablet'] .= 'px';
						if( !empty($item['attr']['css_video_height']['val']['mobile']) && is_numeric($item['attr']['css_video_height']['val']['mobile']) ) $item['attr']['css_video_height']['val']['mobile'] .= 'px';
					}

					// product images
					if( !empty($item['jsclass']) && $item['jsclass'] == 'product_images' ) {
						if( !empty($item['attr']['zoom']) ) {
							update_post_meta( $post_id, 'mfn_product_image_zoom', '1' );
						}else{
							delete_post_meta( $post_id, 'mfn_product_image_zoom' );
						}
					}

					// shop products
					if( !empty($item['jsclass']) && $item['jsclass'] == 'shop_products' ) {

						if( !empty($item['attr']['products']) ) update_post_meta( $post_id, 'mfn_template_perpage', $item['attr']['products'] );

						if( !empty($item['attr']['shop-list-active-filters']) ) { update_post_meta( $post_id, 'mfn-shop-list-active-filters', 'visible' ); }else{ update_post_meta( $post_id, 'mfn-shop-list-active-filters', 'hidden' ); }
						if( !empty($item['attr']['shop-list-perpage']) ) { update_post_meta( $post_id, 'mfn-shop-list-perpage', 'visible' ); }else{ update_post_meta( $post_id, 'mfn-shop-list-perpage', 'hidden' ); }
						if( !empty($item['attr']['shop-list-layout']) ) { update_post_meta( $post_id, 'mfn-shop-list-layout', 'visible' ); }else{ update_post_meta( $post_id, 'mfn-shop-list-layout', 'hidden' ); }
						if( !empty($item['attr']['shop-list-sorting']) ) { update_post_meta( $post_id, 'mfn-shop-list-sorting', 'visible' ); }else{ update_post_meta( $post_id, 'mfn-shop-list-sorting', 'hidden' ); }
						if( !empty($item['attr']['shop-list-results-count']) ) { update_post_meta( $post_id, 'mfn-shop-list-results-count', 'visible' ); }else{ update_post_meta( $post_id, 'mfn-shop-list-results-count', 'hidden' ); }

						if( !empty($item['attr']['ordering']) ) { update_post_meta( $post_id, 'mfn_default_order', $item['attr']['ordering'] ); }else{ delete_post_meta( $post_id, 'mfn_default_order' ); }

					}

					// single product tmpl cart button label
					if( !empty($item['jsclass']) && $item['jsclass'] == 'product_cart_button' && !empty($item['attr']['cart_button_text']) ) {
						update_post_meta( $post_id, 'mfn_cart_button', $item['attr']['cart_button_text'] );
					}

					foreach ($item['attr'] as $a => $attr) {


						if( (empty($attr['selector']) || empty($attr['val'])) && $a != 'hotspots' ) continue; // no style attribute

						$uid = $item['uid'];

						$selector = '';
						$style_name = '';
						$value = '';

						/*echo $a."\r\n";
						print_r($attr);
						echo "\r\n";*/

						if( $a != 'hotspots' ){

							$selector = $attr['selector'];
							$style_name = $attr['style'];
							$value = $attr['val'];

							if( is_array($value) && !empty($value) ) {

								// val is array

								foreach( $value as $va=>$valu ) {

									if( is_array( $valu ) && is_iterable($valu) ) {
										foreach($valu as $v => $val) {

											if( is_array( $val ) && !empty($val) ) {

												foreach( $val as $x=>$y ) {

													if( strpos( $style_name, 'gradient' ) !== false && $x != 'string' ) continue;
													if( strpos( $style_name, 'transform' ) !== false && $x != 'string' ) continue;
													// if( strpos( $style_name, 'filter' ) !== false && $x != 'string' ) continue;
													if( strpos( $style_name, 'backdrop-filter' ) !== false && $x != 'string' ) continue;

													if( strpos( $x, 'font-family' ) !== false && !empty($y)) {
														if( !in_array($y, $return['fonts']) ) $return['fonts'][] = $y;
														$y = "'".$y."'";
													}

													if( $style_name == 'background-image' && strpos($y, '{') !== false ){
														$pm_slug = '--mfn-'.sanitize_title(str_replace('_', '-', $y));
														$return['dynamic_styles'][$pm_slug] = $y;
														$y = 'var('.$pm_slug.')';
													}

													if( in_array($v, array('desktop', 'laptop', 'tablet', 'mobile')) ) {
														$return[$v] = array_merge_recursive($return[$v], self::mfnLocalStyle($selector, $x, $y, $uid, $post_id));
													}else{
														$sn = $v.'-'.$x;
														$return['desktop'] = array_merge_recursive($return['desktop'], self::mfnLocalStyle($selector, $sn, $y, $uid, $post_id));
													}

												}

											}else{

												if( strpos( $style_name, 'gradient' ) !== false && $v != 'string' ) continue;
												if( strpos( $style_name, 'transform' ) !== false && $v != 'string' ) continue;
												// if( strpos( $style_name, 'filter' ) !== false && $v != 'string' ) continue;
												if( strpos( $style_name, 'backdrop-filter' ) !== false && $v != 'string' ) continue;

												if( strpos( $v, 'font-family' ) !== false && !empty($val)) {
													if( !in_array($val, $return['fonts']) ) $return['fonts'][] = $val;
													$val = "'".$val."'";
												}

												if( $style_name == 'background-image' && strpos($val, '{') !== false ){
													$pm_slug = '--mfn-'.sanitize_title(str_replace('_', '-', $val));
													$return['dynamic_styles'][$pm_slug] = $val;
													$val = 'var('.$pm_slug.')';
												}

												$sn = $style_name.'-'.$v;

												if( in_array($va, array('desktop', 'laptop', 'tablet', 'mobile')) ){
													$return[$va] = array_merge_recursive($return[$va], self::mfnLocalStyle($selector, $sn, $val, $uid, $post_id));
												}else{
													$return['desktop'] = array_merge_recursive($return['desktop'], self::mfnLocalStyle($selector, $sn, $val, $uid, $post_id));
												}


											}

										}
									}else{

										if( strpos( $style_name, 'gradient' ) !== false && $va != 'string' ) continue;
										if( strpos( $style_name, 'transform' ) !== false && $va != 'string' ) continue;
										// if( strpos( $style_name, 'filter' ) !== false && $va != 'string' ) continue;
										if( strpos( $style_name, 'backdrop-filter' ) !== false && $va != 'string' ) continue;

										if( strpos( $va, 'font-family' ) !== false && !empty($valu)) {
											if( !in_array($valu, $return['fonts']) ) $return['fonts'][] = $valu;
											$valu = "'".$valu."'";
										}

										if( strpos( $style_name, 'background-attachment' ) !== false && $valu == 'parallax' ) continue;

										if( $style_name == 'background-image' && strpos($valu, '{') !== false ){
											$pm_slug = '--mfn-'.sanitize_title(str_replace('_', '-', $valu));
											$return['dynamic_styles'][$pm_slug] = $valu;
											$valu = 'var('.$pm_slug.')';
										}

										if( in_array($va, array('desktop', 'laptop', 'tablet', 'mobile')) ){
											$return[$va] = array_merge_recursive($return[$va], self::mfnLocalStyle($selector, $style_name, $valu, $uid, $post_id));
										}else{
											$sn = $style_name.'-'.$va;
											$return['desktop'] = array_merge_recursive($return['desktop'], self::mfnLocalStyle($selector, $sn, $valu, $uid, $post_id));
										}

										if( !empty($additional_css[$a]) && !empty($additional_css[$a]['rewrites'][$valu]) ) $return[$va] = array_merge_recursive($return[$va], self::mfnLocalStyle($additional_css[$a]['selector'], $additional_css[$a]['style'], $additional_css[$a]['rewrites'][$valu], $uid, $post_id));

									}

								}


							}else{

								// val is string

								if( strpos( $style_name, 'background-attachment' ) !== false && $value == 'parallax' ) continue;

								if( $style_name == 'background-image' && strpos($value, '{') !== false ){
									$pm_slug = '--mfn-'.sanitize_title(str_replace('_', '-', $value));
									$return['dynamic_styles'][$pm_slug] = $value;
									$value = 'var('.$pm_slug.')';
								}

								if( !empty($value) && strpos( $value, 'font-family' ) !== false ) {
									if( !in_array(!empty($value), $return['fonts']) ) $return['fonts'][] = !empty($value);
									$value = "'".!empty($value)."'";
								}

								if( strpos($style_name, '_custom') !== false ){
									$return['custom'] = array_merge_recursive($return['custom'], self::mfnLocalStyle($selector, $style_name, $value, $uid, $post_id));
								}else{
									$return['desktop'] = array_merge_recursive($return['desktop'], self::mfnLocalStyle($selector, $style_name, $value, $uid, $post_id));
								}

							}


						}else if( $a == 'hotspots' ) {

							foreach ($attr as $s => $style) {

								if( is_array($style) &&  !empty($style['val']) ) {

									foreach($style['val'] as $h=>$ht) {

										$selector = $ht['selector'];
										$style_name = $ht['style'];

										if( is_array($ht['val']) && !empty($ht['val']) ) {
											foreach( $ht['val'] as $x=>$y ) {

												if( in_array($x, array('desktop', 'laptop', 'tablet', 'mobile')) ) {
													$return[$x] = array_merge_recursive($return[$x], self::mfnLocalStyle($selector, $style_name, $y, $uid, $post_id));
												}else{
													$sn = $h.'-'.$x;
													$return['desktop'] = array_merge_recursive($return['desktop'], self::mfnLocalStyle($selector, $sn, $y, $uid, $post_id));
												}

											}
										}
									}
								}
							}
						}

					}
				}
			}
		}

		if( $post_id && is_numeric($post_id) ) {
			if( !empty($return['fonts']) && count($return['fonts']) > 0 ){
				update_post_meta( $post_id, 'mfn-page-fonts', json_encode($return['fonts']) );
			}else{
				delete_post_meta( $post_id, 'mfn-page-fonts' );
			}
		}

		if( $post_id && is_numeric($post_id) && !empty($return['sidemenus']) && count($return['sidemenus']) > 0 ) {
			update_post_meta( $post_id, 'mfn-template-sidemenu', json_encode( $return['sidemenus']) );
		}else{
			delete_post_meta( $post_id, 'mfn-template-sidemenu' );
		}

		if( $post_id && is_numeric($post_id) && get_post_type($post_id) == 'template' && strpos($tmpl_type, 'archive-') !== false /*in_array($tmpl_type, array('blog', 'portfolio'))*/ ){

			if( !empty($return['query_modifiers']) ){
				update_post_meta( $post_id, 'mfn-query-modifiers', json_encode( array_unique($return['query_modifiers'])) );
			}else{
				delete_post_meta( $post_id, 'mfn-query-modifiers' );
			}

			if( $return['has_main_query'] ){
				update_post_meta( $post_id, 'mfn-tmpl-has-main-query', '1' );
			}else{
				delete_post_meta( $post_id, 'mfn-tmpl-has-main-query' );
			}

			unset( $return['has_main_query'] );
			unset( $return['query_modifiers'] );
		}

		if( !empty($return['custom_alert']) ) {
			update_post_meta( $post_id, 'mfn_template_alert', '1' );
		}else{
			delete_post_meta( $post_id, 'mfn_template_alert' );
		}


		if( !empty($return['dynamic_styles']) ) {
			update_post_meta( $post_id, 'mfn_dynamic_styles', json_encode( array_unique($return['dynamic_styles']) ) );
		}else{
			delete_post_meta( $post_id, 'mfn_dynamic_styles' );
		}

		if( $post_id && is_numeric($post_id) && !empty($return['cart']) ) {
			update_post_meta( $post_id, 'mfn-cart-template-data', json_encode( $return['cart'], JSON_UNESCAPED_UNICODE) );
		}else{
			delete_post_meta( $post_id, 'mfn-cart-template-data' );
		}

		unset($return['custom_alert']);
		unset($return['cart']);

		if( $post_id && is_numeric($post_id) ){
			update_post_meta( $post_id, $meta_key, json_encode($return) );
		}

		$preview = $meta_key == 'mfn-builder-preview-local-style' ? true : false;

		Mfn_Helper::generate_css($return, $post_id, $preview);

		return $return;

	}

	/**
	 * Local style
	 */

	public static function mfnLocalStyle($selector, $style_name, $val, $uid, $post_id = false) {

		if( empty($val) || $val == 'cover-ultrawide' || $val == 'custom' ) {
			return array();
		}

		$style_arr = array();

		if( $uid && strpos($uid, 'be_') === false ) {
			$selector = str_replace('.mcb-section-mfnuidelement', 'section.mcb-section-mfnuidelement', $selector);
			$selector = str_replace('mfnuidelement', $uid, $selector);
			$selector = str_replace('mcb-section-inner', 'mcb-section-inner-'.$uid, $selector);
			$selector = str_replace('section_wrapper', 'mcb-section-inner-'.$uid, $selector);
			$selector = str_replace('mcb-wrap-mfnuidelement', 'wrap.mcb-wrap-mfnuidelement'.$uid, $selector);
			$selector = str_replace('mcb-wrap-inner', 'mcb-wrap-inner-'.$uid, $selector);
			$selector = str_replace('mcb-column-inner', 'mcb-column-inner-'.$uid, $selector);
		}else{

			//.replaceAll('mcb-item-mfnuidelement', 'mcb-column.'+class_name).replaceAll('mcb-wrap-mfnuidelement', 'mcb-wrap.'+class_name).replaceAll('mcb-section-mfnuidelement', 'mcb-section.'+class_name);

			$selector = str_replace('mcb-item-mfnuidelement', $uid, $selector);
			$selector = str_replace('mcb-wrap-mfnuidelement', $uid, $selector);
			$selector = str_replace('mcb-section-mfnuidelement', $uid, $selector);

			//$selector = 'html '.$selector;
		}

		$values_prefixes = array(
			'flex' => '0 0 ',
			'background-image' => 'url(',
			'-webkit-mask-image' => 'url(',
			'transformtranslatex' => 'translateX(',
			'transformtranslatey' => 'translateY(',
			'transform-string' => 'matrix('
		);

		$values_postfixes = array(
			'background-image' => ')',
			'-webkit-mask-image' => ')',
			'transformtranslatex' => ')',
			'transformtranslatey' => ')',
			'transform-string' => 'deg)'
		);

		$selector = str_replace('|', ':', $selector);

		$style_name = str_replace(array('_laptop', '_mobile', '_tablet', 'typography-', 'translatex', 'translatey', '_v2'), '', $style_name);

		$style_value = str_replace('gradient-string', 'background-image', $style_name).':';
		$style_value = str_replace('filter-string', 'filter', $style_value);
		$style_value = str_replace('transform-string', 'transform', $style_value);

		if( isset($values_prefixes[$style_name]) && $val != 'none' && strpos($val, 'var(') === false ){
			$style_value .= $values_prefixes[$style_name];
		}

		if ( $style_name === 'transform-string' ) {
			$val = preg_replace("/(\,+)(?!.*,)/", ") rotate(", $val);
		}

		$style_value .= $val;

		if( isset($values_postfixes[$style_name]) && $val != 'none' && strpos($val, 'var(') === false ){
			$style_value .= $values_postfixes[$style_name];
		}

		/*
		depracated
		if( !empty($val) && strpos( $val, '{featured_image' ) !== false ) $style_value = 'background-image: var(--mfn-featured-image)';
		if( !empty($val) && strpos( $val, '{postmeta:mfn-post-header-bg' ) !== false ) $style_value = 'background-image: var(--mfn-header-intro-image)';
		if( !empty($val) && strpos( $val, '{postmeta:mfn-post-subheader-image' ) !== false ) $style_value = 'background-image: var(--mfn-subheader-image)';
		*/

		$style_value .= ';';

		$style_arr[$selector] = $style_value;
		return $style_arr;
	}

	public static function generate_css($mfn_styles, $post_id, $preview = false){

	  	$wp_filesystem = self::filesystem();

		$upload_dir = wp_upload_dir();
		$path_be = wp_normalize_path( $upload_dir['basedir'] .'/betheme' );
		$path_css = wp_normalize_path( $path_be .'/css' );

		$file_name = 'post-'.$post_id;

		if( $preview ) $file_name = 'post-'.$post_id.'-preview';

		if( !is_numeric($post_id) ) $file_name = $post_id;

		$path = wp_normalize_path( $path_css .'/'.$file_name.'.css' );

		if( ! file_exists( $path_be ) ) {
			wp_mkdir_p( $path_be );
		}

		if( ! file_exists( $path_css ) ) {
			wp_mkdir_p( $path_css );
		}

		$css = "";

		if( !empty($mfn_styles['desktop']) ) {
			foreach($mfn_styles['desktop'] as $sel=>$st) {
				if(is_array($st)){
					$css .= $sel.'{';
					foreach($st as $style){
						$css .= $style;
					}
					$css .= '}';
				}else{
					$css .= $sel.'{'.$st.'}';
				}
			}
		}

		if( !empty($mfn_styles['laptop']) ) {
			$css .= '@media(max-width: 1440px){';
			foreach($mfn_styles['laptop'] as $sel=>$st) {
				if(is_array($st)){
					$css .= $sel.'{';
					foreach($st as $style){
						$css .= $style;
					}
					$css .= '}';
				}else{
					$css .= $sel.'{'.$st.'}';
				}
			}
			$css .= '}';
		}

		if( !empty($mfn_styles['tablet']) ) {
			$css .= '@media(max-width: 959px){';
			foreach($mfn_styles['tablet'] as $sel=>$st) {
				if(is_array($st)){
					$css .= $sel.'{';
					foreach($st as $style){
						$css .= $style;
					}
					$css .= '}';
				}else{
					$css .= $sel.'{'.$st.'}';
				}
			}
			$css .= '}';
		}

		if( !empty($mfn_styles['mobile']) ) {
			$css .= '@media(max-width: 767px){';
			foreach($mfn_styles['mobile'] as $sel=>$st) {
				if(is_array($st)){
					$css .= $sel.'{';
					foreach($st as $style){
						$css .= $style;
					}
					$css .= '}';
				}else{
					$css .= $sel.'{'.$st.'}';
				}
			}
			$css .= '}';
		}

		if( !empty($mfn_styles['custom']) ) {
			foreach($mfn_styles['custom'] as $sel=>$st) {

				if(is_array($st)){
					foreach($st as $style){
						$mq = str_replace( array('show-under-custom', 'hide-under-custom', 'show_under_custom', 'hide_under_custom', ':', ';'), '', $style );
						if( strpos( $style, 'hide' ) !== false ){
							$css .= '@media(max-width: '.$mq.'){ '.$sel.'{display: none;}}';
						}else if( strpos( $style, 'show' ) !== false ){
							$css .= $sel.'{display: none;}';
							$css .= '@media(max-width: '.$mq.'){ '.$sel.'{display: block;}}';
						}
					}
				}else{
					$mq = str_replace( array('show-under-custom', 'hide-under-custom', 'show_under_custom', 'hide_under_custom', ':', ';'), '', $st );
					if( strpos( $st, 'hide' ) !== false ){
						$css .= '@media(max-width: '.$mq.'){ '.$sel.'{display: none;}}';
					}else if( strpos( $st, 'show' ) !== false ){
						$css .= $sel.'{display: none;}';
						$css .= '@media(max-width: '.$mq.'){ '.$sel.'{display: block;}}';
					}
				}

			}
		}

		//echo $css;
		$wp_filesystem->put_contents( $path, $css, FS_CHMOD_FILE );

	}

	public static function generate_bebuilder_items(){

		$bebuilder_access = apply_filters('bebuilder_access', false);
		if( !$bebuilder_access ) return false;

		MfnVisualBuilder::removeBeDataFile();
		$bepath = MfnVisualBuilder::bebuilderFilePath();

		$mfnVidualClass = new MfnVisualBuilder();
		$beitems = $mfnVidualClass->fieldsToJS();

		$wp_filesystem = self::filesystem();
		$folder_path = get_template_directory().'/visual-builder/assets/js/forms';
		if( ! file_exists( $folder_path ) ) wp_mkdir_p( $folder_path );
		$path = wp_normalize_path( $bepath );
		$make = $wp_filesystem->put_contents( $path, $beitems, FS_CHMOD_FILE );
		update_option('betheme_form_uid', Mfn_Builder_Helper::unique_ID());
		return $make;
	}

	public static function bebuilder_data_updater() {

		global $wpdb;

		update_option('mfn-css-db-update', 'pending');
		$progress = get_option('mfn-css-db-update-status');

		//$items = $wpdb->get_results( "SELECT `ID` FROM {$wpdb->prefix}posts WHERE post_status = 'publish' and post_type not like 'attachment'" );

		$items = $wpdb->get_results( "SELECT `post_id` FROM {$wpdb->prefix}postmeta WHERE meta_key = 'mfn-page-items' " );

		if(count($items) > 0) {
			$css = new MfnLocalCssCompability();
			foreach($items as $i=>$item) {
				if( !empty($progress) && $progress > $i ) continue;
				$css->render($item->post_id);
				update_option('mfn-css-db-update-status', $i);
			}
		}

		update_option('mfn-css-db-update', '1');
		delete_option('mfn-css-db-update-status');

	}

	/**
	 * Registration modal
	 */

	public static function the_modal_register(){

		?>

			<div class="mfn-register-now">
				<div class="inner-content">
					<div class="be">
						<img class="be-logo" src="<?php echo get_theme_file_uri( 'muffin-options/svg/others/be-gradient.svg' ); ?>" alt="Be">
					</div>
					<div class="info">
                        <span class="mfn-register-now-icon"></span>
						<h4>Please register the license<br />to get the access to Muffin Options</h4>
						<p class="">This page reload is required after theme registration</p>
						<a class="mfn-btn mfn-btn-green btn-large" href="admin.php?page=betheme" target="_blank"><span class="btn-wrapper">Register now</span></a>
					</div>
				</div>
			</div>

		<?php

	}

	/**
	 * Cache string
	 */

	public static function get_cache_text()
	{
		$content = '
# BEGIN BETHEME';

		$content .= '
<IfModule mod_expires.c>
ExpiresActive On

AddType font/woff2 .woff2

# Images
ExpiresByType image/jpeg "access plus 1 year"
ExpiresByType image/gif "access plus 1 year"
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/webp "access plus 1 year"
ExpiresByType image/svg+xml "access plus 1 year"
ExpiresByType image/x-icon "access plus 1 year"

# Video
ExpiresByType video/webm "access plus 1 year"
ExpiresByType video/mp4 "access plus 1 year"
ExpiresByType video/mpeg "access plus 1 year"

# Fonts
ExpiresByType font/ttf "access plus 1 year"
ExpiresByType font/otf "access plus 1 year"
ExpiresByType font/woff "access plus 1 year"
ExpiresByType font/woff2 "access plus 1 year"

ExpiresByType application/x-font-ttf "access plus 1 year"
ExpiresByType application/font-woff "access plus 1 year"

# CSS, JavaScript
ExpiresByType text/css "access plus 6 months"
ExpiresByType text/javascript "access plus 6 months"
ExpiresByType application/javascript "access plus 6 months"

# Others
ExpiresByType application/pdf "access plus 6 months"
ExpiresByType image/vnd.microsoft.icon "access plus 1 year"

ExpiresDefault "access 1 month"

</IfModule>
';

		$content .= '# END BETHEME';
		return $content;
	}

}
