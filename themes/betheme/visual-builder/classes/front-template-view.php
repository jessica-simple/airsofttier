<?php 

/**
 *  Front template view
*/

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

class Mfn_Template_View
{
	public $post_id = false;
	public $tmpl_id = false;

	public $lang_postfix = '';
	
	public function __construct() {

		$this->lang_postfix = '';

		// wpml fix
		if( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$default_lang = apply_filters('wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $this->lang_postfix = '_'.$current_lang;
		}else if( function_exists( 'pll_the_languages' ) ) {
		// polylang fix
			if( pll_default_language() != pll_current_language() ) $this->lang_postfix = '_'.pll_current_language();
		}

		if( is_singular() ) {
			$this->post_id = get_the_ID();
		}

	}

	public function get_singular_template($id = false) {

		if( !is_singular() || is_admin() ) return false;

		$return = array();
		$id = $id ? $id : get_the_ID();
		$post_type = get_post_type($id);
		$post_taxonomies = get_post_taxonomies($id);

		// template_id from url
		if( !empty($_GET['mfn-template-id']) && is_numeric( $_GET['mfn-template-id'] ) && get_post_type( $_GET['mfn-template-id'] ) == 'template' && get_post_meta($_GET['mfn-template-id'], 'mfn_template_type', true) && ( get_post_status( $_GET['mfn-template-id'] ) == 'publish' || ( !empty($_GET['visual']) && $_GET['visual'] == 'iframe' ) ) ) {
			return $_GET['mfn-template-id'];
		}

		if( get_post_meta( $id, 'mfn_template_id', true ) && get_post_status( get_post_meta( $id, 'mfn_template_id', true ) ) == 'publish' ) {
			return get_post_meta( $id, 'mfn_template_id', true );
		}
	
		$template_type = 'mfn_single-'.$post_type.'_template'.$this->lang_postfix;

		if( !empty(get_post_meta($id, 'mfn_single_'.$post_type.'_template', true)) && get_post_status(get_post_meta($id, 'mfn_single_'.$post_type.'_template', true)) == 'publish' ){
			return get_post_meta( $id, 'mfn_single_'.$post_type.'_template', true );
		}

		$opt = get_option($template_type);

		if( $opt ) {
			if( !empty($opt['all']) ) $return = array_merge($return, $opt['all']);

			if( !empty($post_taxonomies) ) {
				foreach( $post_taxonomies as $p_tax) {
					if( !empty($opt[$p_tax]) ) {

						if( !empty($opt[$p_tax]['all']) ) $return = array_merge($return, $opt[$p_tax]['all']);

						$terms = get_the_terms($id, $p_tax);

						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach($terms as $term) {
								if( !empty($opt[$p_tax][$term->term_id]) ) $return = array_merge($return, $opt[$p_tax][$term->term_id]);
							}
						}

					}
				}
			}

			/*echo '<pre>';
			print_r($return);
			echo '</pre>';*/

			if( !empty($return['exclude']) ){
				foreach($return['exclude'] as $ex) {
					foreach( $return as $r=>$ret ) {
						if( $ex == $ret ) unset($return[$r]);
					}
				}
				unset( $return['exclude'] );
			}

			if (!empty($return) && is_array($return)) {

				  // Flatten (any nesting) -> array of ints
				  $flat = [];
				  $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($return));
				  foreach ($it as $v) {
				    $id = (int) $v;
				    if ($id > 0) $flat[] = $id;
				  }

				  // Optional: remove duplicates + reindex
				  $flat = array_values(array_unique($flat));

				  // Your original logic: iterate from the end
				  for ($i = count($flat) - 1; $i >= 0; $i--) {
				    $id = $flat[$i];
				    if ($id && get_post_status($id) === 'publish') {
				      return $id; // return published post ID
				    }
				  }
			}
			
			return false;

		}else{

			if( $post_type == 'product' ) $depracated = $this->mfn_single_product_tmpl();
			if( $post_type == 'post' ) $depracated = $this->mfn_single_post_ID('single-post');
			if( $post_type == 'portfolio' ) $depracated = $this->mfn_single_post_ID('single-portfolio');

			if( !empty($depracated) ) return $depracated;
		}

		
		return false;

	}

	public function get_archive_template() {

		if( is_admin() ) return false;

		// template_id from url
		if( !empty($_GET['mfn-template-id']) && is_numeric( $_GET['mfn-template-id'] ) && get_post_type( $_GET['mfn-template-id'] ) == 'template' && get_post_meta($_GET['mfn-template-id'], 'mfn_template_type', true) && ( get_post_status( $_GET['mfn-template-id'] ) == 'publish' || ( !empty($_GET['visual']) && $_GET['visual'] == 'iframe' ) ) ) {
			return $_GET['mfn-template-id'];
		}

		$return = array();
		$post_type = get_query_var( 'post_type' );
		$queried_object = get_queried_object();

		if( empty($post_type) && isset($queried_object->taxonomy) ) {
			$post_types = get_post_types(['public' => true]);
			foreach ( $post_types as $pt ) {
	            $taxonomies = get_object_taxonomies($pt);
	            if ( in_array($queried_object->taxonomy, $taxonomies) ) {
	                $post_type = $pt;
	                break;
	            }
	        }
		}

		if( empty( $post_type ) ) $post_type = 'post';
		
		$wishlist_page = mfn_opts_get( 'shop-wishlist-page' );
		$portfolio_page = mfn_opts_get( 'portfolio-page' );
		$portfolio_page = apply_filters( 'wpml_object_id', $portfolio_page , get_post_type($portfolio_page), TRUE );

		if(is_page() ) {

			if(get_the_ID() == $portfolio_page){
				$post_type = 'portfolio'; // portfolio page technically is singular page
			}else if( !empty($wishlist_page) && function_exists('is_woocommerce') && get_the_ID() == $wishlist_page ) {
				$post_type = 'product';
			}
		}

		if( function_exists('is_woocommerce') && is_woocommerce() /*&& is_shop()*/ ) {
			$post_type = 'product';
		}

		if( is_array($post_type) ) $post_type = 'page';

		$template_type = 'mfn_archive-'.$post_type.'_template'.$this->lang_postfix;

		$opt = get_option($template_type);

		/*echo '<pre style="margin-top: 100px;">';
		print_r($opt);
		echo '</pre>';*/

		if( $opt ) {

			// all posts
			if( !empty($opt['all']) ) $return = array_merge($return, $opt['all']);

			// all posts from taxonomy
			if( !empty($queried_object->term_id) && isset( $opt[$queried_object->taxonomy]['all'] ) ) {
				$return = array_merge($return, $opt[$queried_object->taxonomy]['all']);
			}

			// all posts with term
			if( !empty($queried_object->term_id) && isset( $opt[$queried_object->taxonomy][$queried_object->term_id] ) ) {
				$return = array_merge($return, $opt[$queried_object->taxonomy][$queried_object->term_id]);
			}

			if( !empty($wishlist_page) && function_exists('is_woocommerce') && get_the_ID() == $wishlist_page ) {
				if( !empty($opt['wishlist']) ) {
					$return = array_merge($return, $opt['wishlist']);
				}else{
					return false;
				}
			}

			if ( function_exists('is_woocommerce') && is_search() && get_query_var('post_type') === 'product' && !empty($opt['products-search']) ) {
				$return = array_merge($return, $opt['products-search']);
			}

			if (!empty($return) && is_array($return)) {

				  // Flatten (any nesting) -> array of ints
				  $flat = [];
				  $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($return));
				  foreach ($it as $v) {
				    $id = (int) $v;
				    if ($id > 0) $flat[] = $id;
				  }

				  // Optional: remove duplicates + reindex
				  $flat = array_values(array_unique($flat));

				  // Your original logic: iterate from the end
				  for ($i = count($flat) - 1; $i >= 0; $i--) {
				    $id = $flat[$i];
				    if ($id && get_post_status($id) === 'publish') {
				      return $id; // return published post ID
				    }
				  }
			}

			return false;

		}else{

			if( $post_type == 'product' ) $depracated = $this->mfn_shop_archive_tmpl();
			if( $post_type == 'post' ) $depracated = $this->mfn_archive_template_id('blog');
			if( $post_type == 'portfolio' ) $depracated = $this->mfn_archive_template_id('portfolio');

			if( !empty($depracated) ) return $depracated;

		}

		return false;

	}




























	/**
	 * 
	 * Depraceted
	 * Backup of theme-functions for old saves 
	 * 
	 * */

	public function mfn_shop_archive_tmpl() {

		if( !function_exists('is_woocommerce') ) return false;

		if( !is_woocommerce() || is_admin() ) return false;

		// wpml fix
		$lang_postfix = '';
		if( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$default_lang = apply_filters('wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $lang_postfix = '_'.$current_lang;
		} else if ( function_exists( 'pll_the_languages' ) ) {
			// polylang
			$current_lang = pll_current_language();
			$default_lang = pll_default_language();
			if( $default_lang != $current_lang ) $lang_postfix = '_'.$current_lang;
		}

		$qo = get_queried_object();

		if( isset($qo->term_id) && (is_product_category() || is_product_tag()) ) {
			$term_tmpl = get_term_meta($qo->term_id, 'mfn_shop_template'.$lang_postfix, true);
			if( !empty($term_tmpl) && is_numeric($term_tmpl) && get_post_status( $term_tmpl ) == 'publish' && get_post_type( $term_tmpl ) == 'template' ) {
				return $term_tmpl;
			}

			if( is_product_category() ){
				$allcats_tmpl = get_option('mfn_shop_archive_tmpl_all_cats'.$lang_postfix);
				if( !empty($allcats_tmpl) && is_numeric($allcats_tmpl) && get_post_status( $allcats_tmpl ) == 'publish' && get_post_type( $allcats_tmpl ) == 'template' ) {
					return $allcats_tmpl;
				}
			}

			if( is_product_tag() ) {
				$alltags_tmpl = get_option('mfn_shop_archive_tmpl_all_tags'.$lang_postfix);
				if( !empty($alltags_tmpl) && is_numeric($alltags_tmpl) && get_post_status( $alltags_tmpl ) == 'publish' && get_post_type( $alltags_tmpl ) == 'template' ) {
					return $alltags_tmpl; 
				}
			}

		}

		$shop_id = wc_get_page_id('shop');

		// wpml fix
		if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang && !empty(apply_filters( 'wpml_object_id', wc_get_page_id('shop'), 'page', null, $current_lang )) && !empty( get_post_meta(apply_filters( 'wpml_object_id', wc_get_page_id('shop'), 'page', null, $current_lang ), 'mfn_shop_template'.'_'.$current_lang, true) ) ){
			return get_post_meta( apply_filters( 'wpml_object_id', wc_get_page_id('shop'), 'page', null, $current_lang ), 'mfn_shop_template'.'_'.$current_lang, true);
		}else if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang && !empty(get_post_meta($shop_id, 'mfn_shop_template'.$lang_postfix)) && get_post_status( get_post_meta($shop_id, 'mfn_shop_template'.$lang_postfix, true) ) == 'publish' ){
			return get_post_meta($shop_id, 'mfn_shop_template'.$lang_postfix, true);
		}else if( !empty(get_post_meta($shop_id, 'mfn_shop_template')) && get_post_status( get_post_meta($shop_id, 'mfn_shop_template', true) ) == 'publish' ){
			return get_post_meta($shop_id, 'mfn_shop_template', true);
		}

		if( !empty(mfn_opts_get('shop-template')) && get_post_status( mfn_opts_get('shop-template') ) == 'publish' ){
			return mfn_opts_get('shop-template');
		}

		return false;

	}


	public function mfn_single_product_tmpl() {

		if( !function_exists('is_woocommerce') ) return false;

		$post_id = get_the_ID();

		if( is_product() ) {

			// wpml fix
			$lang_postfix = '';
			if( defined( 'ICL_SITEPRESS_VERSION' ) ){
				$default_lang = apply_filters('wpml_default_language', NULL );
				$current_lang = apply_filters( 'wpml_current_language', NULL );
				if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $lang_postfix = '_'.$current_lang;
			} else if ( function_exists( 'pll_the_languages' ) ) {
				// polylang
				//if( pll_default_language() != pll_get_post_language( $post_id ) ) $lang_postfix = '_'.pll_get_post_language( $post_id );
				$current_lang = pll_current_language();
				$default_lang = pll_default_language();
				if( $default_lang != $current_lang ) $lang_postfix = '_'.$current_lang;
			}

			// single product
			if( get_post_meta( $post_id, 'mfn_single_product_template', true ) && get_post_status( get_post_meta( $post_id, 'mfn_single_product_template', true ) ) == 'publish' ){
				return get_post_meta( $post_id, 'mfn_single_product_template', true ); // single product template
			}

			// cat template
			$cat_tmpl = get_post_meta($post_id, 'mfn_product_cat_template'.$lang_postfix, true);
			if( !empty($cat_tmpl) && is_numeric($cat_tmpl) && get_post_status($cat_tmpl) == 'publish' ){
				return $cat_tmpl;
			}

			$tag_tmpl = get_post_meta($post_id, 'mfn_product_tag_template'.$lang_postfix, true);
			if( !empty($tag_tmpl) && is_numeric($tag_tmpl) && get_post_status($tag_tmpl) == 'publish' ){
				return $tag_tmpl;
			}

			/**
			 *
			 * NEW BASED ON OPTIONS
			 * for entire shop, all cats, all tags
			 *
			 * */

			if( get_option('mfn_sinle_product_tmpl_all_cats'.$lang_postfix) && get_post_status( get_option('mfn_sinle_product_tmpl_all_cats'.$lang_postfix) ) == 'publish' ) {
				return get_option('mfn_sinle_product_tmpl_all_cats'.$lang_postfix);
			}

			if( get_option('mfn_sinle_product_tmpl_all_tags'.$lang_postfix) && get_post_status( get_option('mfn_sinle_product_tmpl_all_tags'.$lang_postfix) ) == 'publish' ) {
				return get_option('mfn_sinle_product_tmpl_all_tags'.$lang_postfix);
			}

			if( get_option('mfn_sinle_product_tmpl_entire_shop'.$lang_postfix) && get_post_status( get_option('mfn_sinle_product_tmpl_entire_shop'.$lang_postfix) ) == 'publish' ) {
				return get_option('mfn_sinle_product_tmpl_entire_shop'.$lang_postfix);
			}

			/**
			 *
			 * END
			 *
			 * */

			$product_tmpl = get_post_meta($post_id, 'mfn_product_template'.$lang_postfix, true);
			if( $product_tmpl && is_numeric($product_tmpl) && get_post_status( $product_tmpl ) == 'publish' ){
					return $product_tmpl; // shop product template
			}

			// theme option product template

			if( mfn_opts_get('shop-product-template') && get_post_status( mfn_opts_get('shop-product-template') ) == 'publish' ) {
				return mfn_opts_get('shop-product-template');
			}

			return false;

		}

		return false;

	}



	public function mfn_archive_template_id($type = false) {
		
		$return = array();

		$lang_postfix = '';

		if( defined( 'ICL_SITEPRESS_VERSION' ) ){
			$default_lang = apply_filters('wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $lang_postfix = '_'.$current_lang;
		} else if ( function_exists( 'pll_the_languages' ) ) {
			// polylang
			if( pll_default_language() != pll_current_language() ) $lang_postfix = '_'.pll_current_language();
		}

		// conditions
		if( !empty(get_option('mfn_'.$type.'_template'.$lang_postfix)) ){
			$sp_tmpl = get_option('mfn_'.$type.'_template'.$lang_postfix);

			// All singulars
			if( !empty($sp_tmpl['all']) && is_array($sp_tmpl['all']) ) {
				$return = array_merge($return, $sp_tmpl['all']);
			}

			$taxoms = array(
				'blog' => array('category', 'post_tag'),
				'portfolio' => array('portfolio-types')
			);

			$queried_obj = get_queried_object();

			if( isset($queried_obj->term_id) ){

				if( !empty($taxoms[$type]) && is_array($taxoms[$type]) ){
					foreach($taxoms[$type] as $tax){

						if( !empty($sp_tmpl[$tax][$queried_obj->term_id]) && is_array($sp_tmpl[$tax][$queried_obj->term_id]) ) {
							foreach ($sp_tmpl[$tax][$queried_obj->term_id] as $t => $te) {
								if( $t != 'exclude' ) $return[] = $te;
							}
						}

						if( !empty($sp_tmpl[$tax]['all']) && is_array($sp_tmpl[$tax]['all']) ) {
							foreach ($sp_tmpl[$tax]['all'] as $t => $te) {
								if( $t != 'exclude' ) $return[] = $te;
							}
						}

						if( isset($sp_tmpl[$tax][$queried_obj->term_id]['exclude']) && is_array($sp_tmpl[$tax][$queried_obj->term_id]['exclude']) ) {

							// remove
							foreach( $sp_tmpl[$tax][$queried_obj->term_id]['exclude'] as $ex ){

								foreach( $return as $r=>$ret ){
									if( $ex == $ret ) unset($return[$r]);
								}

							}

						}

					}
				}

			}

		}

		if( is_array($return) && count($return) > 0 ){
			$return = array_unique($return);
			$last = array_key_last($return);
			if( get_post_status($return[$last]) == 'publish' ){
				return $return[$last];
			}elseif( count($return) > 1 ){
				foreach($return as $r) if( get_post_status($r) == 'publish' ) return $r;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}



	public function mfn_single_post_ID($type) {
		$post_id = get_the_ID();

		$lang_postfix = '';

		// wpml fix
		if( defined( 'ICL_SITEPRESS_VERSION' ) ){
			$default_lang = apply_filters('wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $lang_postfix = '_'.$current_lang;
		}else if( function_exists( 'pll_the_languages' ) ) {
			if( pll_default_language() != pll_current_language() ) $lang_postfix = '_'.pll_current_language();
		}

		// set in post options
		$set_in_postopt = get_post_meta($post_id, 'mfn_single-post_template', true);
		if( !empty( $set_in_postopt ) && is_numeric($set_in_postopt) && get_post_status($set_in_postopt) == 'publish' && get_post_type($set_in_postopt) == 'template' ){
			return $set_in_postopt;
		}

		$return = array();

		// conditions
		if( !empty(get_option('mfn_'.$type.'_template'.$lang_postfix)) ){
			$sp_tmpl = get_option('mfn_'.$type.'_template'.$lang_postfix);
			//$post_type = get_post_type($post_id);

			/*echo '<pre>';
			print_r($sp_tmpl);
			echo '</pre>';*/

			/*if( !empty($sp_tmpl[$post_type]['all']) && is_array($sp_tmpl[$post_type]['all']) ){
				$return = array_merge($return, $sp_tmpl[$post_type]['all']);
			}*/

			// All singulars
			if( !empty($sp_tmpl['all']) && is_array($sp_tmpl['all']) ) {
				$return = array_merge($return, $sp_tmpl['all']);
			}

			$taxoms = array(
				'single-post' => array('category', 'post_tag'),
				'single-portfolio' => array('portfolio-types')
			);

			if( !empty($taxoms[$type]) && is_array($taxoms[$type]) ){
				foreach($taxoms[$type] as $tax){

					// any taxonomy
					if( !empty($sp_tmpl[$tax]['all']) && is_array($sp_tmpl[$tax]['all']) ) {
						$return = array_merge($return, $sp_tmpl[$tax]['all']);
					}

					$terms = get_the_terms( $post_id, $tax );

					if ( isset($terms) && $terms && !is_wp_error( $terms ) ){
						foreach($terms as $term) {

							if( !empty($sp_tmpl[$tax][$term->term_id]) && is_array($sp_tmpl[$tax][$term->term_id]) ) {
								foreach ($sp_tmpl[$tax][$term->term_id] as $t => $te) {
									if( !empty($te) && is_numeric($te) ) $return[] = $te;
								}
							}

							if( isset($sp_tmpl[$tax][$term->term_id]['exclude']) && is_array($sp_tmpl[$tax][$term->term_id]['exclude']) ) {

								// remove
								foreach( $sp_tmpl[$tax][$term->term_id]['exclude'] as $ex ){

									foreach( $return as $r=>$ret ){
										if( $ex == $ret ) unset($return[$r]);
									}

								}

							}

						}
					}

				}
			}

		}

		/*echo '<pre>';
		print_r($return);
		echo '</pre>';*/

		if( !empty($return) && is_array($return) ){
			$return = array_unique($return, SORT_REGULAR);
			return $return[array_key_last($return)];
		}else{
			return false;
		}


	}

}
