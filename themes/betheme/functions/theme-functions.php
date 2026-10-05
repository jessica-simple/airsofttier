<?php
/**
 * Theme functions
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Dynamic data
 */

function be_dynamic_data($string, $post_id = false){
 	$mfndd = new MfnDynamicData();
 	return $mfndd->render($string, $post_id);
}

/**
 * Allowed HTML elements for wp_kses
 */

if (! function_exists('mfn_allowed_html')) {
	function mfn_allowed_html($type = false)
	{
		switch ($type) {

			case 'button':

				$allowed_html = array(
					'i' => array(
						'class' => array(),
					),
					'del' => array(),
					'span' => array(),
					'strong' => array(),
				);
				break;

			case 'caption':

				$allowed_html = array(
					'a' => array(
						'href' => array(),
						'target' => array(),
					),
					'b' => array(),
					'br' => array(),
					'em' => array(),
					'span' => array(),
					'strong' => array(),
					'u' => array(),
				);
				break;

			case 'desc':

				$allowed_html = array(
					'a' => array(
						'href' => array(),
						'target' => array(),
					),
					'b' => array(),
					'br' => array(),
					'em' => array(),
					'i' => array(
						'class'  => array(),
					),
					'li' => array(),
					'span' => array(),
					'strong' => array(),
					'u' => array(),
					'ul' => array(),
				);
				break;

			default:

				$allowed_html = array(
					'b' => array(),
					'br' => array(),
					'em' => array(),
					'i' => array(
						'class'  => array(),
					),
					'span' => array(
						'id' => array(),
						'class' => array(),
						'style' => array(),
					),
					'strong' => array(),
					'u' => array(),
				);

		}

		return $allowed_html;
	}
}

/**
 * Allowed HTML title tags
 * prevent Cross-Site Scripting (title_tag="h2 onmouseover=alert(1)")
 */

if (! function_exists('mfn_allowed_title_tag')) {
	function mfn_allowed_title_tag( $tag )
	{
    $allowed = ['h1','h2','h3','h4','h5','h6','p','span','div'];

    if( ! in_array( $tag, $allowed ) ){
      return 'p';
    }

    return $tag;
	}
}

/**
 * Image Size | Add
 * TIP: add_image_size ( string $name, int $width, int $height, bool|array $crop = false )
 */

if (! function_exists('mfn_add_image_size')) {
	function mfn_add_image_size()
	{

    $disable = mfn_opts_get('performance-image-size-disable');

    // general theme thumbnail with crop / the same as default WP thumbnail size to avoid duplication

    if( empty( $disable['be_thumbnail'] ) ){
      add_image_size('be_thumbnail', 150, 150, true);
    }

		// clients & clients slider elements

    if( empty( $disable['be_clients'] ) ){
  		add_image_size('be_clients', 150, 75, false);
    }

		// slider (builder items)

    if( empty( $disable['slider-content'] ) ){
  		add_image_size('slider-content', 1630, 860, true);
    }

		// portfolio | style: masonry flat

    if( empty( $disable['portfolio-mf'] ) ){
  		add_image_size('portfolio-mf', 1280, 1000, true);
    }
    if( empty( $disable['portfolio-mf-w'] ) ){
  		add_image_size('portfolio-mf-w', 1280, 500, true);	/* Wide */
    }
    if( empty( $disable['portfolio-mf-t'] ) ){
  		add_image_size('portfolio-mf-t', 768, 1200, true);	/* Tall	*/
    }

		// portfolio | style: list

    if( empty( $disable['portfolio-list'] ) ){
  		add_image_size('portfolio-list', 1920, 750, true);
    }

		// blog & portfolio: dynamic sizes

    $archives = [
      'width' => mfn_opts_get('featured-blog-portfolio-width', 960),
      'height' => mfn_opts_get('featured-blog-portfolio-height', 750),
      'crop' => 'resize' == mfn_opts_get('featured-blog-portfolio-crop', 'crop') ? false : true,
    ];

    if( empty( $disable['blog-portfolio'] ) ){
  		add_image_size('blog-portfolio', $archives['width'], $archives['height'], $archives['crop']);
    }

    $single = [
      'width' => mfn_opts_get('featured-single-width', 1200),
      'height' => mfn_opts_get('featured-single-height', 480),
      'crop' => 'resize' == mfn_opts_get('featured-single-crop', 'crop') ? false : true,
    ];

    if( empty( $disable['blog-single'] ) ){
  		add_image_size('blog-single', $single['width'], $single['height'], $single['crop']);
    }

	}
}
add_action('after_setup_theme', 'mfn_add_image_size', 11);

/**
 * Calculate images srcset
 */

function mfn_calculate_image_sizes($sizes, $dimensions) {

 	if( empty($dimensions[0]) ){
 		return $sizes;
 	}

  // Maximum mobile images srcset width
 	$mobile_image_max_width = mfn_opts_get('mobile-images-max-srcset', mfn_opts_get('mobile-grid-width', 480));

 	// current image width
 	$width = $dimensions[0];

 	$mobile_width = min([$mobile_image_max_width, $width]);

 	// images smaller than mobile grid
 	if( $width < 768 ){
 		return '(max-width:767px) '. $mobile_width .'px, '. $width .'px';
 	}

 	return '(max-width:767px) '. $mobile_width .'px, (max-width:'. $width .'px) 100vw, '. $width .'px';
}
add_filter('wp_calculate_image_sizes', 'mfn_calculate_image_sizes', 10, 2);

/**
 * Add mobile images custom size
 */

function mfn_add_mobile_image_size() {

  // Maximum mobile images srcset width
 	$mobile_image_max_width = mfn_opts_get('mobile-images-max-srcset', mfn_opts_get('mobile-grid-width', 480));

 	add_image_size('mobile-srcset', $mobile_image_max_width, $mobile_image_max_width * 2, false);

}
add_action('after_setup_theme', 'mfn_add_mobile_image_size', 11);

/**
 * Limit maximum image srcset to selected image size
 */

function mfn_max_srcset_image_width( $max_width, $size_array ) {

  if( ! mfn_opts_get('srcset-limit') ){
    return $max_width;
  }

  $width = $size_array[0];

  if ( $width > 400 ) {
    $max_width = $width;
  }

  return $max_width;
}
add_filter( 'max_srcset_image_width', 'mfn_max_srcset_image_width', 10, 2 );

/**
 * Image size | Get size dimensions
 */

if (! function_exists('mfn_get_image_sizes')) {
	function mfn_get_image_sizes($size, $string = false)
	{
		$sizes = array();

		$sizes['width'] = get_option("{$size}_size_w");
		$sizes['height'] = get_option("{$size}_size_h");
		$sizes['crop'] = (bool) get_option("{$size}_crop");

		if ($string) {
			$crop = $sizes['crop'] ? ', crop' : '';
			return 'max width: '. esc_attr($sizes['width']) .', max height: '. esc_attr($sizes['height']) . esc_attr($crop);
		}

		return $sizes;
	}
}

/**
 * Skip #[image_ID] from the url
 */

if (! function_exists('mfn_skip_hash')) {
  function mfn_skip_hash($url = ''){
    return explode('#', $url)[0];
  }
}

/**
 * SVG, ICO, TTF, WOFF, JSON files upload
 */

if (! function_exists('mfn_mimes_support')) {
 	function mfn_mimes_support( $file_types )
 	{
		$theme_disable = mfn_opts_get('theme-disable');

		if( empty($theme_disable['svg-allow']) && current_user_can('edit_theme_options') ){
			$file_types['svg'] = 'image/svg+xml';
			$file_types['svgz'] = 'image/svg+xml';
			$file_types['ico'] = 'image/x-icon';
			$file_types['ttf'] = 'application/font-sfnt';
			$file_types['woff'] = 'application/octet-stream';
		}

		if( empty($theme_disable['json-allow']) ){
			$file_types['json'] = 'application/json';
		}

		return $file_types;
 	}
}
add_action('upload_mimes', 'mfn_mimes_support');

/**
 * ICO, TTF, WOFF files upload
 */

function mfn_check_filetype_and_ext_ico( $types, $file, $filename, $mimes ) {

  $theme_disable = mfn_opts_get('theme-disable');
  if ( empty($theme_disable['svg-allow']) && false !== strpos( $filename, '.ico' ) ) {
    $types['ext'] = 'ico';
    $types['type'] = 'image/ico';
  }

  return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'mfn_check_filetype_and_ext_ico', 10, 4 );

function mfn_check_filetype_and_ext_ttf( $types, $file, $filename, $mimes ) {

  $theme_disable = mfn_opts_get('theme-disable');
  if ( empty($theme_disable['svg-allow']) && false !== strpos( $filename, '.ttf' ) ) {
    $types['ext'] = 'ttf';
    $types['type'] = 'application/font-sfnt';
  }

  return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'mfn_check_filetype_and_ext_ttf', 10, 4 );

function mfn_check_filetype_and_ext_woff( $types, $file, $filename, $mimes ) {

  $theme_disable = mfn_opts_get('theme-disable');
  if ( empty($theme_disable['svg-allow']) && false !== strpos( $filename, '.ttf' ) ) {
    $types['ext'] = 'woff';
    $types['type'] = 'application/octet-stream';
  }

  return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'mfn_check_filetype_and_ext_woff', 10, 4 );

/**
 * JSON files upload
 */

if (! function_exists('mfn_json_support')) {
 	function mfn_json_support( $types, $file, $filename, $mimes )
 	{

		$theme_disable = mfn_opts_get('theme-disable');
		if( ! empty($theme_disable['json-allow']) ){
			return $types;
		}

		if ( $types['ext'] && $types['type'] ) {
			return $types;
		}

		$filetype = wp_check_filetype( $filename );

		if ( $filetype['ext'] === 'json' ) {
			$types['ext'] = 'json';
			$types['type'] = 'application/json';
		}

		return $types;
 	}
}
add_action('wp_check_filetype_and_ext', 'mfn_json_support', 10, 4);

/**
 * SVH upload sanitization
 */

function mfn_sanitize_svg_file_during_upload($file) {
  if ($file['type'] === 'image/svg+xml') {
    $file_path = $file['tmp_name'];

    // Read the SVG content
    $svg_content = file_get_contents($file_path);

    // Create a new Sanitizer instance
    $sanitizer = new enshrined\svgSanitize\Sanitizer();

    // Sanitize the SVG content
    $sanitized_svg = $sanitizer->sanitize($svg_content);

    // Save the sanitized content back to the file
    if ($sanitized_svg) {
      file_put_contents($file_path, $sanitized_svg);
    } else {
      // Handle sanitization failure (optional)
      $file['error'] = 'SVG sanitization failed.';
    }
  }

  return $file;
}
add_filter('wp_handle_upload_prefilter', 'mfn_sanitize_svg_file_during_upload');

/**
 * Excerpt | Lenght
 */

if (! function_exists('mfn_excerpt_length')) {
	function mfn_excerpt_length($length)
	{
		return esc_attr(mfn_opts_get('excerpt-length', 26));
	}
}
add_filter('excerpt_length', 'mfn_excerpt_length', 999);

/**
 * Excerpt | Wrap [...] into <span>
 */

if (! function_exists('mfn_trim_excerpt')) {
	function mfn_trim_excerpt($text)
	{
		return '<span class="excerpt-hellip"> […]</span>';
	}
}
add_filter('excerpt_more', 'mfn_trim_excerpt');

/**
 * Excerpt | for Pages
 */

if (! function_exists('mfn_add_excerpts_to_pages')) {
	function mfn_add_excerpts_to_pages()
	{
		add_post_type_support('page', 'excerpt');
	}
}
add_action('init', 'mfn_add_excerpts_to_pages');

/**
 * Slug | Generate
 */

if (! function_exists('mfn_slug')) {
	function mfn_slug($string = false)
	{
		return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
	}
}

/**
 * Blog Page | Order
 */

if ( ! function_exists( 'mfn_archive_query_modifiers' ) ) {
	function mfn_archive_query_modifiers( $query ) {

		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! ( $query->is_home() || $query->is_archive() ) ) {
			return;
		}

		$mfn_front_tmpl = new Mfn_Template_View();
		$arch_tmpl      = $mfn_front_tmpl->get_archive_template();

		$raw_qm = ! empty( $arch_tmpl ) ? get_post_meta( $arch_tmpl, 'mfn-query-modifiers', true ) : false;
		$to_pp  = ! empty( $arch_tmpl ) ? get_post_meta( $arch_tmpl, 'mfn_template_perpage', true ) : false;

		if ( empty( $to_pp ) ) {
			$to_pp = mfn_opts_get( 'shop-products' );
		}

		$qm = array();

		if ( ! empty( $raw_qm ) ) {
			$decoded = json_decode( $raw_qm, true );
			if ( is_array( $decoded ) ) {
				$qm = $decoded;
			}
		}

		$perpage = ! empty( $to_pp ) ? (int) $to_pp : 20;

		if ( isset( $qm['posts_per_page'] ) && '' !== $qm['posts_per_page'] ) {
			$perpage = (int) $qm['posts_per_page'];
			unset( $qm['posts_per_page'] );
		}

		if ( ! empty( $to_pp ) || isset( $_GET['visual'] ) || ! empty( $qm ) ) {
			$query->set( 'posts_per_page', $perpage );
		}

		if ( ! empty( $arch_tmpl ) && ! empty( $qm ) ) {
			foreach ( $qm as $key => $value ) {

				if ( 'base_offset' === $key ) {
					$base_offset    = (int) $value;
					$paged          = max( 1, (int) ( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ?: 1 ) );
					$posts_per_page = (int) $perpage;

					$query->set( 'mfn_base_offset', $base_offset );
					$query->set( 'offset', $base_offset + ( ( $paged - 1 ) * $posts_per_page ) );
					continue;
				}

				$query->set( $key, $value );
			}

			return;
		}

		if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_author() ) {
			$orderby = mfn_opts_get( 'blog-orderby', 'date' );
			$order   = mfn_opts_get( 'blog-order', 'DESC' );
			$blog_per_page = mfn_opts_get( 'blog-posts' );

			if( !empty($blog_per_page) ) $query->set( 'posts_per_page', $blog_per_page );

			if ( 'date' === $orderby && 'DESC' === $order ) {
				return;
			}

			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );
		}

	}
}

add_action( 'pre_get_posts', 'mfn_archive_query_modifiers' );

if ( ! function_exists( 'mfn_archive_offset_found_posts' ) ) {
	function mfn_archive_offset_found_posts( $found_posts, $query ) {
		$base_offset = (int) $query->get( 'mfn_base_offset' );

		if ( $base_offset > 0 ) {
			$found_posts = max( 0, $found_posts - $base_offset );
		}

		return $found_posts;
	}
}
add_filter( 'found_posts', 'mfn_archive_offset_found_posts', 10, 2 );

/**
 * Blog Page | Exclude category
 */

if (! function_exists('mfn_get_excluded_categories')) {
	function mfn_get_excluded_categories()
	{
		$categories = array();

		if ($exclude = mfn_opts_get('exclude-category')) {
			$exclude = str_replace(' ', '', $exclude);
			$exclude = explode(',', $exclude);

			if (is_array($exclude)) {
				$categories = $exclude;
			}
		}

		return $categories;
	}
}

if (! function_exists('mfn_exclude_category')) {
	function mfn_exclude_category($query)
	{
		if (is_home() && $query->is_main_query()) {
			$exclude_ids = array();

			if ($exclude = mfn_get_excluded_categories()) {
				foreach ($exclude as $slug) {
					$category = get_category_by_slug($slug);
					if( ! empty($category->term_id) ){
						$exclude_ids[] = $category->term_id * -1;
					}
				}
			}

			$exclude_ids = implode(',', $exclude_ids);

			$query->set('cat', $exclude_ids);
		}

		return $query;
	}
}
add_filter('pre_get_posts', 'mfn_exclude_category');

/**
 * SSL | Compatibility
*/

if ( !function_exists('mfn_ssl') ) {
	function mfn_ssl($echo = false) {
		$ssl = '';

		if (is_ssl()) {
			$ssl = 's';
		}

		if ($echo) {
			echo esc_attr($ssl);
		}

		return $ssl;
	}
}

/**
 * SSL | Attachments
 */

if (! function_exists('mfn_ssl_attachments')) {
	function mfn_ssl_attachments($url)
	{
		if (is_ssl()) {
			return str_replace('http://', 'https://', $url);
		}
		return $url;
	}
}
add_filter('wp_get_attachment_url', 'mfn_ssl_attachments');

/**
 * White Label | Admin Body Class
 */

if (! function_exists('mfn_white_label_class')) {
	function mfn_white_label_class($classes)
	{
		if (WHITE_LABEL) {
			$classes .= ' white-label ';
		}
		return $classes;
	}
}
add_filter('admin_body_class', 'mfn_white_label_class');

/**
 * Hide Custom Fields meta boxes
 */

function mfn_hide_custom_fields() {
  if ( ! mfn_bebuilder_access() ) { // administrator
    remove_meta_box('postcustom', 'post', 'normal');
    remove_meta_box('postcustom', 'page', 'normal');
  }
}
add_action('admin_menu', 'mfn_hide_custom_fields');

/**
 * Prevents duplicate price, rate in templates
 */

if (! function_exists('remove_standard_woo_actions_archive')) {
	function remove_standard_woo_actions_archive(){

    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

    remove_action( 'woocommerce_after_shop_loop_item_title', 'mfn_append_excerpt_loop', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );

		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	}
}

/**
 * Get Real Post ID
 */

if (! function_exists('mfn_ID')) {
	function mfn_ID( $param = false )
	{
		global $post;
		global $mfn_global;

		// 404

		if( is_404() ) {
			return false;
		}

    // custom under construction page

    if( mfn_is_under_construction() && mfn_opts_get('construction-page') ) {
      return mfn_opts_get('construction-page');
    }

    // post ID

		$post_id = get_the_ID();

		// force template

		if( !empty($_GET['mfn-template-id']) && is_numeric( $_GET['mfn-template-id'] ) && get_post_type( $_GET['mfn-template-id'] ) == 'template' && ( get_post_status( $_GET['mfn-template-id'] ) == 'publish' || (!empty($_GET['visual']) && $_GET['visual'] == 'iframe' ) ) ) {
			return $_GET['mfn-template-id'];
		}

		// woocommerce

		if (function_exists('is_woocommerce') && is_woocommerce() && !is_admin() ) {

			if( is_product() ) {

				if( !empty($mfn_global['singular_template']) ){
          return $mfn_global['singular_template'];
        }

        // for full width site/content in page options
        if( 'perfect_match' == $param ) {
					return $post_id;
				}

				// return false;
				return wc_get_page_id('shop'); // FIX: sidebar inherited from shop page

			}else if( !empty($mfn_global['archive_template']) && is_numeric($mfn_global['archive_template']) && get_post_status($mfn_global['archive_template']) == 'publish' ) {

				return $mfn_global['archive_template'];

			}

			return wc_get_page_id('shop');
		}

		if( function_exists('is_woocommerce') && is_cart() && !empty( $mfn_global['cart'] ) && !is_admin() ) {
			return $mfn_global['cart'];
		}

		if( function_exists('is_woocommerce') && is_checkout() && empty( is_wc_endpoint_url('order-received') ) && !empty( $mfn_global['checkout'] ) && !is_admin() ) {
			return $mfn_global['checkout'];
		}

		if( function_exists('is_woocommerce') && is_checkout() && !empty( is_wc_endpoint_url('order-received') ) && !empty($mfn_global['thank_you']) && !is_admin() ) {
			return $mfn_global['thank_you'];
		}


		if( is_singular() && !empty($mfn_global['singular_template']) ) {

			// singulars template

      if( 'perfect_match' == $param ) {
        return $post_id;
      }

			if( !empty($mfn_global['singular_template']) && get_post_status($mfn_global['singular_template']) == 'publish' ) {
				return $mfn_global['singular_template'];
			}

		}

		// search

		/*if( is_search() ){
			return false;
		}*/

		if( is_search() ) {
			if( !( isset($_GET['mfn_livesearch']) && !isset($_GET['searchpage']) ) && empty($_GET['post_type']) && !empty($mfn_global['search']) && get_post_status($mfn_global['search']) == 'publish' && !is_admin() ) {
				return $mfn_global['search'];
			}else{
				return false;
			}
		}

		// taxonomy

		$portfolio_page = mfn_opts_get( 'portfolio-page' );
		$portfolio_page = apply_filters( 'wpml_object_id', $portfolio_page, get_post_type($portfolio_page), TRUE );

		if( is_tax('portfolio-types') || ( is_page() && get_the_ID() == $portfolio_page ) ) {
			//$portfolio_tmpl = mfn_archive_template_id('portfolio');
			if( !empty($mfn_global['archive_template']) && !empty($mfn_global['archive_template']) && get_post_status($mfn_global['archive_template']) == 'publish' ) {
				return $mfn_global['archive_template'];
			}else{
				return mfn_opts_get('portfolio-page');
			}
		}

		if( !empty($mfn_global['archive_template']) && function_exists('is_woocommerce') && mfn_opts_get('shop-wishlist-page') && is_page() && mfn_opts_get('shop-wishlist-page') == get_the_ID() ) {
		  return is_array($mfn_global['archive_template']) ? $mfn_global['archive_template'][0] : $mfn_global['archive_template'];
		}

		// archive

		/*if( ! is_singular() && !empty($mfn_global['archive_template']) ) {
			if( is_post_type_archive() || in_array( get_post_type(), array( 'post', 'tribe_events' ) ) ) {
				$blog_tmpl = $mfn_global['archive_template'];
				if( !empty($blog_tmpl) && get_post_status($blog_tmpl) == 'publish' ) {
					return $blog_tmpl;
				}else{
					return mfn_get_blog_ID();
				}
			}
		}*/

		if( is_archive() || is_home() ) {
			if( !empty($mfn_global['archive_template']) ) {
				return $mfn_global['archive_template'];
			}else if ( !empty( mfn_get_blog_ID() ) ) {
				return mfn_get_blog_ID();
			}

		}

		return get_the_ID();

	}
}




/**
 * Cart, Checkout, Thank You Template
 * */
if (! function_exists('mfn_endpoint_tmpl')) {
	function mfn_endpoint_tmpl( $type ) {

		if( !function_exists('is_woocommerce') ) return false;
		if( !is_cart() && !is_checkout() && empty( is_wc_endpoint_url('order-received') ) ) return false;

		if( !empty($_GET['mfn-template-id']) && mfn_verify_tmpl($_GET['mfn-template-id'], $type) ) {
			return $_GET['mfn-template-id'];
		}

		$lang_postfix = '';

		$tmpl_id = get_option('mfn_'.$type.'_template'.$lang_postfix);
		if( empty($tmpl_id) || ( !empty($tmpl_id) && !is_numeric($tmpl_id) ) ) $tmpl_id = get_option('mfn_'.$type.'_template_used'.$lang_postfix);

		$tmpl_id = apply_filters( 'wpml_object_id', $tmpl_id , get_post_type($tmpl_id) , TRUE  );

		if( $tmpl_id && mfn_verify_tmpl($tmpl_id, $type) ) {
				return $tmpl_id;
		}

		return false;

	}
}

/**
 * Template Part ID
 */

if (! function_exists('mfn_template_part_ID')) {
	function mfn_template_part_ID( $type, $id = false ) {
		global $post;
		global $wp_query;

		$mfn_front_tmpl = new Mfn_Template_View();

		$return = false;
		$lang_postfix = '';
		$term = false;

		if( !empty($_GET['mfn-'.$type.'-template']) && is_numeric( $_GET['mfn-'.$type.'-template'] ) && $_GET['mfn-'.$type.'-template'] != '0' && get_post_type( $_GET['mfn-'.$type.'-template'] ) == 'template' && get_post_meta($_GET['mfn-'.$type.'-template'], 'mfn_template_type', true) && get_post_meta($_GET['mfn-'.$type.'-template'], 'mfn_template_type', true) == $type && ( get_post_status( $_GET['mfn-'.$type.'-template'] ) == 'publish' || (!empty($_GET['visual']) && $_GET['visual'] == 'iframe' ) ) ) {
			return $_GET['mfn-'.$type.'-template'];
		}

		// set in single product || shop arhive template
		if( function_exists('is_woocommerce') ) {
			if( is_checkout() && !is_order_received_page() ) { // dedicated header from theme options for gutenberg checkout
				$tmpl_d_id = get_the_ID();
				$tmpl_d_part = get_post_meta( $tmpl_d_id, 'mfn_dedicated_'.$type.'_template', true );
				if( !empty($tmpl_d_part) && get_post_type($tmpl_d_part) == 'template' && get_post_status($tmpl_d_part) == 'publish' ) return $tmpl_d_part;
			}
		}


		// set in single product || shop arhive template
		if( function_exists('is_woocommerce') && is_woocommerce() ) {
			$tmpl_id = mfn_ID();
			if( !empty($tmpl_id) && get_post_type($tmpl_id) == 'template' && get_post_status($tmpl_id) == 'publish' ){
				$tmpl_part = get_post_meta( $tmpl_id, 'mfn_'.$type.'_template', true );
				if( !empty($tmpl_part) && get_post_type($tmpl_part) == 'template' && get_post_status($tmpl_part) == 'publish' ) return $tmpl_part;
			}
		}

		// wpml fix
		if( defined( 'ICL_SITEPRESS_VERSION' ) ){
			$default_lang = apply_filters('wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $lang_postfix = '_'.$current_lang;
		}else if( function_exists( 'pll_the_languages' ) ) {
			if( pll_default_language() != pll_current_language() ) $lang_postfix = '_'.pll_current_language();
		}

		// always 1 newest template is active
		// if excluded we check this one only

		if( is_search() ){
			$seachpage_part_tmpl = get_option( 'mfn_'.$type.$lang_postfix.'_search_page' );
			if( !empty($seachpage_part_tmpl) && is_numeric( $seachpage_part_tmpl ) && get_post_status( $seachpage_part_tmpl ) == 'publish' && get_post_type( $seachpage_part_tmpl ) == 'template' ){
				return $seachpage_part_tmpl;
			}
		}

		if( $id || is_singular() || is_search() ){

			//echo 'is singular';
			$post_id = $id ? $id : get_the_ID();
			$post_type = get_post_type($post_id);

			if( empty($post_type) || (!empty($post_type) && !in_array($post_type, array('page', 'post', 'offer', 'portfolio', 'product', 'template'))) ) {
				$post_type = 'page';
			}

			// single post header | set in single post/page edit
			$setin_postedit = get_post_meta( $post_id, 'mfn_'.$type.'_template', true );
			if( $setin_postedit && is_numeric( $setin_postedit ) && get_post_status( $setin_postedit ) == 'publish' && get_post_type( $setin_postedit ) == 'template' )
				return $setin_postedit;

			$verify_id = mfn_ID();

			if( !empty($verify_id) && $post_id != $verify_id ) {
				// verify if there is a template
				$setin_postedit = get_post_meta( $verify_id, 'mfn_'.$type.'_template', true );
				if( $setin_postedit && is_numeric( $setin_postedit ) && get_post_status( $setin_postedit ) == 'publish' && get_post_type( $setin_postedit ) == 'template' )
					return $setin_postedit;
			}

			if( !empty($mfn_front_tmpl->get_singular_template()) ) {
				$arch_tmpl = $mfn_front_tmpl->get_singular_template();
				$setin_postedit = get_post_meta( $arch_tmpl, 'mfn_'.$type.'_template', true );
				if( $setin_postedit && is_numeric( $setin_postedit ) && get_post_status( $setin_postedit ) == 'publish' && get_post_type( $setin_postedit ) == 'template' )
					return $setin_postedit;
			}

			// post type header
			$single = get_post_meta( $post_id, 'mfn_'.$type.$lang_postfix.'_post', true );
			if( !get_post_meta( $post_id, 'mfn_'.$type.$lang_postfix.'_post_excluded', true ) && $single && is_numeric( $single ) && get_post_status( $single ) == 'publish' && get_post_type( $single ) == 'template' ){
				return $single;
			}

			// post type all
			$posttype = get_option( 'mfn_'.$type.$lang_postfix.'_'.$post_type.'_single' );
			if( !get_option( 'mfn_'.$type.$lang_postfix.'_'.$post_type.'_single_excluded' ) && $posttype && is_numeric( $posttype ) && get_post_status( $posttype ) == 'publish' && get_post_type( $posttype ) == 'template' ){
				return $posttype;
			}

			// portfolio is page
			if( !empty(mfn_opts_get('portfolio-page')) && mfn_opts_get('portfolio-page') == $post_id ) {
				$arch_tmpl = $mfn_front_tmpl->get_archive_template();
				if( !empty($arch_tmpl) ) {
					$setin_portfolioedit = get_post_meta( $arch_tmpl, 'mfn_'.$type.'_template', true );
					if( !empty($setin_portfolioedit) && get_post_status($setin_portfolioedit) == 'publish' ){
						return $setin_portfolioedit;
					}
				}

				$posttype = get_option( 'mfn_'.$type.$lang_postfix.'_portfolio_arch' );
				if( $posttype && is_numeric( $posttype ) && get_post_status( $posttype ) == 'publish' ) {
					return $posttype;
				}
			}

			$entire_site = get_option('mfn_'.$type.$lang_postfix.'_entire_site');
			if( !empty($entire_site) && is_numeric($entire_site) && get_post_status($entire_site) == 'publish' && get_post_type($entire_site) == 'template' ){
				if( !apply_filters('bebuilder_preview', false) || get_post_type($post_id) != 'template' || (get_post_type($post_id) == 'template' && in_array(get_post_meta($post_id, 'mfn_template_type', true), array('shop-archive', 'single-product', 'blog'))) ){
					return $entire_site;
				}
			}

		}else{

			$verify_id = mfn_ID();

			if( !empty($verify_id) ) {
				// verify if there is a template
				$setin_postedit = get_post_meta( $verify_id, 'mfn_'.$type.'_template', true );
				if( $setin_postedit && is_numeric( $setin_postedit ) && get_post_status( $setin_postedit ) == 'publish' && get_post_type( $setin_postedit ) == 'template' )
					return $setin_postedit;
			}


			if( !empty($mfn_front_tmpl->get_archive_template()) ) {
				$arch_tmpl = $mfn_front_tmpl->get_archive_template();
				$setin_postedit = get_post_meta( $arch_tmpl, 'mfn_'.$type.'_template', true );
				if( $setin_postedit && is_numeric( $setin_postedit ) && get_post_status( $setin_postedit ) == 'publish' && get_post_type( $setin_postedit ) == 'template' )
					return $setin_postedit;
			}


			$queried_obj = get_queried_object();

			// by term id
			if( isset($queried_obj->term_id) ){
				$term = get_term_meta( $queried_obj->term_id, 'mfn_'.$type.$lang_postfix.'_term', true );
				if( $term && is_numeric( $term ) && get_post_status( $term ) == 'publish' && get_post_type( $term ) == 'template' && empty(get_term_meta( $queried_obj->term_id, 'mfn_'.$type.$lang_postfix.'_term_excluded', true )) )
					return $term;
			}

			// entire site
			$entire_site = get_option('mfn_'.$type.$lang_postfix.'_entire_site');
			if( !empty($entire_site) && is_numeric($entire_site) && get_post_status($entire_site) == 'publish' && get_post_type($entire_site) == 'template' ){
				$return = $entire_site;
			}

			// by post type

			$posttype = false;
			$post_type_name = $lang_postfix.'_post';

			if( function_exists('is_woocommerce') && is_woocommerce() ){
				$post_type_name = $lang_postfix.'_product';
			}elseif( is_post_type_archive('portfolio') ){
				$post_type_name = $lang_postfix.'_portfolio';
			}elseif( is_post_type_archive('offer') ){
				$post_type_name = $lang_postfix.'_offer';
			}

			$posttype = get_option( 'mfn_'.$type.$post_type_name.'_arch' );
			if( $posttype && is_numeric( $posttype ) && get_post_status( $posttype ) == 'publish' && empty(get_option( 'mfn_'.$type.$post_type_name.'_arch_excluded' )) ){
				$return = $posttype;
			}

			if( $posttype && is_numeric( $posttype ) && get_post_status( $posttype ) == 'publish' && !empty($queried_obj->term_id) && get_term_meta( $queried_obj->term_id, 'mfn_'.$type.$lang_postfix.'_term_excluded', true ) ) $return = false;

		}

		return $return;
	}
}


/**
 * Addons ID
 */
if (! function_exists('mfn_addons_ID')) {
	function mfn_addons_ID( $type, $id = false ) {
		global $post;
		global $wp_query;

		$return = array();
		$langfix = '';

		// wpml fix
		if( defined( 'ICL_SITEPRESS_VERSION' ) ){
			$default_lang = apply_filters( 'wpml_default_language', NULL );
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			if( !empty($default_lang) && !empty($current_lang) && $current_lang != $default_lang ) $langfix = '_'.$current_lang;
		}else if( function_exists( 'pll_the_languages' ) ) {
			if( pll_default_language() != pll_current_language() ) $langfix = '_'.pll_current_language();
		}

		if( $id || is_singular() || is_search() || is_404() ){

			$post_id = $id ? $id : get_the_ID();
			$post_type = get_post_type($post_id);

			// seting from page option
			if( !empty( get_post_meta($post_id, 'mfn_popup_included', true) ) ){
				$return[] = get_post_meta($post_id, 'mfn_popup_included', true);
			}

			$verify_id = mfn_ID();

			if( !empty($verify_id) && $post_id != $verify_id ) {
				// verify if there is a template
				if( !empty( get_post_meta($verify_id, 'mfn_popup_included', true) ) ){
					$return[] = get_post_meta($verify_id, 'mfn_popup_included', true);
				}
			}

			$addons = get_option('mfn_'.$type.'_addons_singular'.$langfix);

			if( $addons ){

				if( empty($post_type) || (!empty($post_type) && !in_array($post_type, array('page', 'post', 'offer', 'portfolio', 'product', 'template'))) ) {
					$post_type = 'page';
				}

				// for post type
				if( !empty($addons[$post_type]['all']) ) $return = array_merge($return, $addons[$post_type]['all']);

				$tax = 'category';

				if( $post_type == 'product' && function_exists('is_woocommerce') ){
					$tax = 'product_cat';
				}elseif( $post_type == 'portfolio' ){
					$tax = 'portfolio-types';
				}elseif( $post_type == 'offer' ){
					$tax = 'offer-types';
				}

				$terms = get_the_terms( $post_id, $tax );

				if ( isset($terms) && $terms && ! is_wp_error( $terms ) ){
					foreach($terms as $term) {

						if( !empty($addons[$term->term_id]) && is_array($addons[$term->term_id]) ) {
							foreach($addons[$term->term_id] as $a=>$at) {
								$return[] = $at;
							}

						}

						if( isset($addons[$term->term_id]['exclude']) && is_array($addons[$term->term_id]['exclude']) ) {

							// remove
							foreach( $addons[$term->term_id]['exclude'] as $ex ){

								foreach( $return as $r=>$ret ){
									if( $ex == $ret ) unset($return[$r]);
								}

							}

						}

					}
				}

			}

		}else{

			$verify_id = mfn_ID();

			if( !empty($verify_id) ) {
				// verify if there is a template
				if( !empty( get_post_meta($verify_id, 'mfn_popup_included', true) ) ){
					$return[] = get_post_meta($verify_id, 'mfn_popup_included', true);
				}
			}

			$addons = get_option('mfn_'.$type.'_addons_archives'.$langfix);

			if( $addons ){

				if( !empty( $addons['post']['all'] ) && ( is_home() || is_category() || is_author() || is_date() ) ){
					//echo 'blog';
					$return = array_merge($return, $addons['post']['all']);
				}elseif( !empty($addons['product']['all']) && function_exists('is_woocommerce') && is_woocommerce() ){
					//echo 'product';
					$return = array_merge($return, $addons['product']['all']);
				}elseif( !empty($addons['portfolio']['all']) && is_post_type_archive('portfolio') ){
					//echo 'portfolio';
					$return = array_merge($return, $addons['portfolio']['all']);
				}elseif( !empty($addons['offer']['all']) && is_post_type_archive('offer') ){
					//echo 'offer';
					$return = array_merge($return, $addons['offer']['all']);
				}

				$queried_obj = get_queried_object();

				if( isset($queried_obj->term_id) ){

					if( !empty($addons[$queried_obj->term_id]) && is_array($addons[$queried_obj->term_id]) ) {
						foreach($addons[$queried_obj->term_id] as $a=>$at) {
							if( $a != 'excluded' ) {
								//$return = array_merge($return, $at);
								$return[] = $at;
							}
						}

					}

					if( isset($addons[$queried_obj->term_id]['exclude']) && is_array($addons[$queried_obj->term_id]['exclude']) ) {

						// remove
						foreach( $addons[$queried_obj->term_id]['exclude'] as $ex ) {

							foreach( $return as $r=>$ret ){
								if( $ex == $ret ) unset($return[$r]);
							}

						}

					}

				}

			}

		}

		return array_unique($return);

	}
}

/**
 * Addons ID
 */

if (! function_exists('mfn_global_sidemenu_id')) {
	function mfn_global_sidemenu_id() {

		$sm = get_posts( array(
				'post_type' => 'template',
		    'meta_key'   => 'mfn_sidemenu_visibility',
		    'meta_value' => 'always-visible',
		    'post_status' => 'publish',
		    'posts_per_page' => 1
		) );

		if( !empty($sm) && !empty($sm[0]->ID) ) return $sm[0]->ID;

		//print_r($sm);

		return false;
	}
}


/**
 * Get blog page ID
 */

if (! function_exists('mfn_get_blog_ID')) {
	function mfn_get_blog_ID(){

		$id = get_option('page_for_posts');

		if( ! $id ){
			$id = mfn_opts_get('blog-page');
		}

		return $id;
	}
}

/**
 * Get Layout ID
 */

if (! function_exists('mfn_layout_ID')) {
	function mfn_layout_ID()
	{
		$layoutID = false;

		if (mfn_ID()) {

			if (is_single() && get_post_type() == 'post') {

				// Theme Options | Single Post
				$layoutID = mfn_opts_get('blog-single-layout');

			} elseif (is_single() && get_post_type() == 'portfolio') {

				if (get_post_meta(mfn_ID(), 'mfn-post-custom-layout', true)) {

					// Page Options | Single Portfolio
					$layoutID = get_post_meta(mfn_ID(), 'mfn-post-custom-layout', true);

				} else {

					// Theme Options | Single Portfolio
					$layoutID = mfn_opts_get('portfolio-single-layout');

				}

			} else {

				// Page Options | Page
				$layoutID = get_post_meta(mfn_ID(), 'mfn-post-custom-layout', true);

			}

		}

		return $layoutID;
	}
}

/**
 * Slider | Isset
 */

if (! function_exists('mfn_slider_isset')) {
	function mfn_slider_isset($id = false) {
		$slider = false;

		// global slider shortcode

		if (is_page() && mfn_opts_get('slider-shortcode')) {
			return 'global';
		}

		if ($id || is_home() || is_category() || is_tax() || get_post_type() == 'post' || get_post_type() == 'page' || (get_post_type(mfn_ID()) == 'portfolio' && get_post_meta(mfn_ID(), 'mfn-post-slider-header', true))) {

			if (! $id) {
				$id = mfn_ID();

			} // do NOT move it before IF

			if (get_post_meta($id, 'mfn-post-slider', true)) {

				// Revolution Slider
				$slider = 'rev';

			} elseif (get_post_meta($id, 'mfn-post-slider-layer', true)) {

				// Layer Slider
				$slider = 'layer';

			} elseif (get_post_meta($id, 'mfn-post-slider-shortcode', true)) {

				// Custom Slider
				$slider = 'custom';

			}

		}

		if( $slider ) return $slider;

		$mfn_front_tmpl = new Mfn_Template_View();


		if( is_archive() ){
			$arch_tmpl = $mfn_front_tmpl->get_archive_template();
			if (!empty($arch_tmpl) && get_post_meta($arch_tmpl, 'mfn-post-slider', true)) {
				return 'rev';
			}

			if(!empty($arch_tmpl) &&  !empty(get_post_meta($arch_tmpl, 'mfn-post-slider-layer', true)) ){
				return 'layer';
 			}
		}

		if( is_singular() ){
			$sngl_tmpl = $mfn_front_tmpl->get_singular_template();
			if (!empty($sngl_tmpl) && get_post_meta($sngl_tmpl, 'mfn-post-slider', true)) {
				return 'rev';
			}

			if(!empty($sngl_tmpl) &&  !empty(get_post_meta($sngl_tmpl, 'mfn-post-slider-layer', true)) ){
				return 'layer';
 			}
		}

		return $slider;

	}
}

/**
 * Slider | Get
 */

if (! function_exists('mfn_slider')) {
	function mfn_slider($id = false)
	{
		$slider = '';
		$slider_type = mfn_slider_isset($id);

		if (! $id) {
			$id = mfn_ID();
		} // do NOT move it before IF

		switch ($slider_type) {

			case 'global':
				$slider = '<div class="mfn-main-slider" id="mfn-global-slider">';
					$slider .= do_shortcode(mfn_opts_get('slider-shortcode'));
				$slider .= '</div>';
				break;

      case 'rev':

        if( class_exists('RevSliderFront') ){

          global $wpdb;

          $alias = get_post_meta($id, 'mfn-post-slider', true);
          $table_name = $wpdb->prefix . 'revslider_sliders';
          $result = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE alias = %s", $alias));

          // if( $result ){ // RS7 has different database structure
            $slider = '<div class="mfn-main-slider mfn-rev-slider">';
      				$slider .= do_shortcode('[rev_slider '. esc_attr($alias) .']');
      			$slider .= '</div>';
          // }

        }
				break;

			case 'layer':
				$slider = '<div class="mfn-main-slider mfn-layer-slider">';
					$slider .= do_shortcode('[layerslider id="'. get_post_meta($id, 'mfn-post-slider-layer', true) .'"]');
				$slider .= '</div>';
				break;

			case 'custom':
				$slider = '<div class="mfn-main-slider" id="mfn-custom-slider">';
					$slider .= do_shortcode(get_post_meta($id, 'mfn-post-slider-shortcode', true));
				$slider .= '</div>';
				break;

		}

		return $slider;
	}
}

/**
 * Share
 */

if (! function_exists('mfn_share')) {
	function mfn_share($container = false)
	{
		$type = false;
		$class = false;

		if ( ! mfn_opts_get('share') && 'item' !== $container ) {
			return false;
		}

		$style = mfn_opts_get('share-style', 'classic');

		// type

		if (($container == 'header') && ($style == 'classic')) {
			$type = 'classic';
		}

		if ($container == 'intro') {
			if ($style == 'simple') {
				$type = 'simple';
			} else {
				$type = 'classic';
			}
		}

		if (($container == 'footer') && ($style == 'simple')) {
			$type = 'simple';
		}

		if ($container == 'item') {
			$type = $style;
			$class = 'share_item';
		}

		// output

		$output = '';

		if ($type == 'simple') {

			// simple

			$translate['share'] = mfn_opts_get('translate') ? mfn_opts_get('translate-share', 'Share') : __('Share', 'betheme');

			$output .= '<div class="share-simple-wrapper '. esc_attr($class) .'">';

				$output .= '<span class="share-label">'. esc_html($translate['share']) .'</span>';

				$output .= '<div class="icons">';
					$output .= '<a target="_blank" class="facebook" href="https://www.facebook.com/sharer/sharer.php?u='. urlencode(esc_url(get_permalink())) .'"><i class="icon-facebook" aria-label="facebook icon"></i></a>';
					$output .= '<a target="_blank" class="twitter" href="https://twitter.com/intent/tweet?text='. urlencode( esc_attr(wp_get_document_title()) .'. '. esc_url(get_permalink()) ) .'"><i class="icon-x-twitter" aria-label="x twitter icon"></i></a>';
					$output .= '<a target="_blank" class="linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url='. urlencode(esc_url(get_permalink())) .'"><i class="icon-linkedin" aria-label="linkedin icon"></i></a>';
					$output .= '<a target="_blank" class="pinterest" href="https://pinterest.com/pin/find/?url='. urlencode(esc_url(get_permalink())) .'"><i class="icon-pinterest" aria-label="pinterest icon"></i></a>';
				$output .= '</div>';

				if ($container != 'item') {
					$output .= '<div class="button-love">'. mfn_love() .'</div>';
				}

			$output .= '</div>';

		} elseif ($type == 'classic') {

			// classic

			wp_enqueue_script('share-this', 'https://ws.sharethis.com/button/buttons.js', false, null, true);
			$share_this_inline = 'stLight.options({publisher:"1390eb48-c3c3-409a-903a-ca202d50de91",doNotHash:false,doNotCopy:false,hashAddressBar:false});';
			wp_add_inline_script('share-this', $share_this_inline);

			$output .= '<div class="share_wrapper '. esc_attr($class) .'">';

				$output .= '<span class="st_facebook_vcount"></span>';
				$output .= '<span class="st_twitter_vcount"></span>';
				$output .= '<span class="st_pinterest_vcount"></span>';

			$output .= '</div>';
		}

		return $output;
	}
}

/**
 * WP Mobile Detect | Quick FIX: parallax on mobile
 */

if (! function_exists('mfn_is_mobile')) {
	function mfn_is_mobile()
	{
		$mobile = wp_is_mobile();

		if (mfn_opts_get('responsive-parallax')) {
			$mobile = false;
		}

		return $mobile;
	}
}

/**
 * User OS
 * @deprecated Be 25.1
 */

if (! function_exists('mfn_user_os')) {
	function mfn_user_os() {
		// use mfn_html_classes() instead
		return mfn_html_classes();
	}
}

/**
 * User Agent | For: Prallax - Safari detect & future use
 */

if (! function_exists('mfn_user_agent')) {
	function mfn_user_agent()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT']; // context is safe and necessary

		if (strpos($user_agent, 'Chrome/') !== false) {
			$user_agent = 'chrome';
		} elseif ((strpos($user_agent, 'Safari/') !== false) && (strpos($user_agent, 'Mobile/') !== false)) {
			$user_agent = 'safari mobile';
		} elseif (strpos($user_agent, 'Safari/') !== false) {
			$user_agent = 'safari';
		} else {

			// for future use
			$user_agent = false;
		}

		return $user_agent;
	}
}

/**
 * Show user icon
 */

if (! function_exists('mfn_user_icon')) {
	function mfn_user_icon( $user_icon = false ){
		if( $user_icon ){
			echo '<i class="'. $user_icon .'" aria-label="user icon"></i>';
		} else {
			echo '<svg width="26" viewBox="0 0 26 26" aria-label="user icon"><defs><style>.path{fill:none;stroke:#333333;stroke-width:1.5px;}</style></defs><circle class="path" cx="13" cy="9.7" r="4.1"/><path class="path" d="M19.51,18.1v2.31h-13V18.1c0-2.37,2.92-4.3,6.51-4.3S19.51,15.73,19.51,18.1Z"/></svg>';
		}
	}
}

/**
 * Paralllax | Plugin
 */

if (! function_exists('mfn_parallax_plugin')) {
	function mfn_parallax_plugin()
	{
		$parallax = mfn_opts_get('parallax');

		if ($parallax == 'translate3d no-safari') {
			if (mfn_user_agent() == 'safari') {
				$parallax = 'enllax';
			} else {
				$parallax = 'translate3d';
			}
		}

		return $parallax;
	}
}

/**
 * Paralllax | Code - Section & wrapper background
 */

if (! function_exists('mfn_parallax_data')) {
	function mfn_parallax_data()
	{
		$parallax = mfn_parallax_plugin();

		if ($parallax == 'translate3d') {
			$parallax = 'data-parallax="3d"';
		} elseif ($parallax == 'stellar') {
			$parallax = 'data-stellar-background-ratio="0.5"';
		} else {
			$parallax = 'data-enllax-ratio="-0.3"';
		}

		return $parallax;
	}
}

/**
 * Pagination for Blog and Portfolio
 */

if (! function_exists('mfn_pagination')) {
	function mfn_pagination($query = false, $load_more = false)
	{
		global $wp_query;
		$paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);

		// default $wp_query

		if (! $query) {
			$query = $wp_query;
		}

		$translate['prev'] = mfn_opts_get('translate') ? mfn_opts_get('translate-prev', '&lsaquo; Prev page') : __('Prev page', 'betheme');
		$translate['next'] = mfn_opts_get('translate') ? mfn_opts_get('translate-next', 'Next page &rsaquo;') : __('Next page', 'betheme');
		$translate['load-more'] = mfn_opts_get('translate') ? mfn_opts_get('translate-load-more', 'Load more') : __('Load more', 'betheme');

		//isset($query->query_vars['paged']) && $query->query_vars['paged'] > 1 ? $current = $query->query_vars['paged'] : $current = 1;
		$current = max( 1, (int) $query->get('paged'), (int) $query->get('page'), (int) $paged ); // LUK fix

		if (empty($paged)) {
			$paged = 1;
		}

		$prev = $paged - 1;
		$next = $paged + 1;

		$end_size = 1;
		$mid_size = 2;
		$show_all = mfn_opts_get('pagination-show-all');
		$dots = false;

		if (! $total = $query->max_num_pages) {
			$total = 1;
		}

		$output = '';

		if ($total > 1) {

			if ($load_more) {

				// load more

				if ($paged < $total) {
					$output .= '<div class="column one pager_wrapper pager_lm">';
						$output .= '<a rel="next" class="pager_load_more button has-icon" href="'. esc_url(get_pagenum_link($next) ).'">';
							$output .= '<span class="button_icon"><i class="icon-layout" aria-hidden="true"></i></span>';
							$output .= '<span class="button_label">'. esc_html($translate['load-more']) .'</span>';
						$output .= '</a>';
					$output .= '</div>';
				}

			} else {

				// default

				$output .= '<div class="column one pager_wrapper">';
					$output .= '<div class="pager">';

						if ($paged >1) {
							$output .= '<a rel="prev" class="prev_page" href="'. esc_url(get_pagenum_link($prev)) .'"><i class="icon-left-open" aria-hidden="true"></i>'. esc_html($translate['prev']) .'</a>';
						}

						$output .= '<div class="pages">';
						for ($i=1; $i <= $total; $i++) {
							if ($i == $current) {
								$output .= '<a href="'. esc_url(get_pagenum_link($i)) .'" class="page active">'. esc_html($i) .'</a>';
								$dots = true;
							} else {
								if ($show_all || ($i <= $end_size || ($current && $i >= $current - $mid_size && $i <= $current + $mid_size) || $i > $total - $end_size)) {
									$output .= '<a href="'. esc_url(get_pagenum_link($i)) .'" class="page">'. esc_html($i) .'</a>';
									$dots = true;
								} elseif ($dots && ! $show_all) {
									$output .= '<span class="page">...</span>';
									$dots = false;
								}
							}
						}
						$output .= '</div>';

						if ($paged < $total) {
							$output .= '<a rel="next" class="next_page" href="'. esc_url(get_pagenum_link($next)) .'">'. esc_html($translate['next']) .'<i class="icon-right-open" aria-hidden="true"></i></a>';
						}

					$output .= '</div>';
				$output .= '</div>'."\n";

			}

		}
		return $output;
	}
}

/**
 * Current page URL
 */

if (! function_exists('mfn_current_URL')) {
	function mfn_current_URL()
	{
		$env = $_SERVER; // context is safe and necessary

		$pageURL = 'http';
		if (is_ssl()) {
			$pageURL .= 's';
		}

		$pageURL .= '://';

		if( in_array( $env['SERVER_PORT'], array(80, 443) ) ){
			$pageURL .= $env['SERVER_NAME'].$env['REQUEST_URI'];
		} else {
			$pageURL .= $env['SERVER_NAME'] .':'. $env['SERVER_PORT'].$env['REQUEST_URI'];
		}

		return $pageURL;
	}
}

/**
 * Subheader | Page Title
 */

if (! function_exists('mfn_page_title')) {
	function mfn_page_title($echo = false)
	{
		if (is_home()) {

			// blog
			$title = get_the_title(mfn_get_blog_ID());

		} elseif ( is_category() ) {

			$title = single_cat_title( '', false );

		} elseif ( is_tag() ) {

			$title = single_tag_title( '', false );

		} elseif ( is_author() ) {

			$title = get_the_author();

		} elseif ( is_year() ) {

			$title = get_the_time( 'Y' );

		} elseif ( is_month() ) {

			$title = get_the_time( 'F Y' );

		} elseif ( is_day() ) {

			$title = get_the_time( 'F j, Y' );

		} elseif ( is_post_type_archive() ) {

			$title = post_type_archive_title( '', false );

		} elseif ( is_page() && get_the_ID() == mfn_opts_get('portfolio-page') ) {

			$title = get_the_title(get_the_ID());

		} elseif ( is_single() || is_page() ) {

			$tmp_id = mfn_ID();
			if( get_post_type($tmp_id) == 'template' ) $tmp_id = get_the_ID();

			$title = get_the_title($tmp_id);

		} elseif (get_post_taxonomies()) {

			$title = single_cat_title('', false);

		} elseif (function_exists('tribe_is_month') && (tribe_is_event_query() || tribe_is_month() || tribe_is_event() || tribe_is_day() || tribe_is_venue())) {

			// The Events Calendar
			$title = tribe_get_events_title();

		} else {

			$title = get_the_title(mfn_ID());

		}

		if ($echo) {
			echo wp_kses($title, mfn_allowed_html());
		}

		return $title;
	}
}

/**
 * Breadcrumbs
 */

if (! function_exists('mfn_breadcrumbs')) {
	function mfn_breadcrumbs($params = false)
	{
		global $post;

		$breadcrumbs = array();
		$separator = ' <span class="mfn-breadcrumbs-separator"><i class="icon-right-open"></i></span>';

		$class = 'no-link';

		if( !empty($params['classes']) ) $class = $params['classes'];

		if( !empty($params['separator']) ) $separator = '<span class="mfn-breadcrumbs-separator">'.$params['separator'].'</span>';

		// translate

		$translate['home'] = mfn_opts_get('translate') ? mfn_opts_get('translate-home', 'Home') : __('Home', 'betheme');

		// plugin: bbPress

		if(function_exists('is_bbpress') && is_bbpress()) {
			bbp_breadcrumb( array(
				'before' => '<ul class="breadcrumbs">',
				'after' => '</ul>',
				'sep' => '<i class="icon-right-open" aria-label="breadcrumbs separator"></i>',
				'crumb_before' => '<li>',
				'crumb_after' => '</li>',
				'home_text' => esc_html($translate['home']),
			) );
			return true; // exit
		}

		// home prefix

		if( !isset($params['include_home']) || empty($params['include_home']) ) $breadcrumbs[] = '<a href="'. esc_attr(home_url()) .'">'. htmlentities($translate['home']) .'</a>';

		// blog

		if ( 'post' == get_post_type() ) {

			$blogID = false;

			if (get_option('page_for_posts')) {
				$blogID = get_option('page_for_posts');	// Setings / Reading
			}

			if ($blogID) {
				$blog_post = get_post($blogID);

				// blog page has parent

				if ($blog_post && $blog_post->post_parent) {

					$parent_id  = $blog_post->post_parent;
					$parents = array();

					while ($parent_id) {
						$page = get_page($parent_id);
						$parents[] = '<a href="'. get_permalink($page->ID) .'">'. wp_kses(get_the_title($page->ID), mfn_allowed_html()) .'</a>';
						$parent_id  = $page->post_parent;
					}

					$parents = array_reverse($parents);
					$breadcrumbs = array_merge_recursive($breadcrumbs, $parents);
				}

				$breadcrumbs[] = '<a href="'. esc_url(get_permalink($blogID)) .'">'. wp_kses(get_the_title($blogID), mfn_allowed_html()) .'</a>';
			}
		}

		if ( is_front_page() || is_home() ) {

			// do nothing

    } elseif (function_exists('tribe_is_event') && (tribe_is_event_query() || tribe_is_event() || tribe_is_venue())) {

			// plugin: Events Calendar

			if (function_exists('tribe_get_events_link')) {
				$breadcrumbs[] = '<a href="'. esc_url(tribe_get_events_link()) .'">'. htmlentities(tribe_get_events_title()) .'</a>';
			}

		} elseif ( function_exists('is_woocommerce') && is_woocommerce() ) {

			$qo = get_queried_object();

			if( is_product() ){

				$p_terms = get_the_terms( $post->ID, 'product_cat' );

				if( !empty( wc_get_page_id( 'shop' ) ) ){
          $breadcrumbs[] = '<a href="'. esc_url(get_permalink(wc_get_page_id( 'shop' ))) .'">'. htmlentities(get_the_title(wc_get_page_id( 'shop' ))) .'</a>';
        }

				$terms = get_the_terms(get_the_ID(), 'product_cat');
				if (! empty($terms) && ! is_wp_error($terms)) {
					$breadcrumbs[] = get_term_parents_list($terms[0], 'product_cat', array('separator' => $separator ));
				}

				$breadcrumbs[] = '<a href="'. esc_url(get_permalink()) .'">'. wp_kses(get_the_title(), mfn_allowed_html()) .'</a>';

      }elseif( is_shop() ){

				if( !empty( wc_get_page_id( 'shop' ) ) ){
          $breadcrumbs[] = '<a href="'. esc_url(get_permalink(wc_get_page_id( 'shop' ))) .'">'. htmlentities(get_the_title(wc_get_page_id( 'shop' ))) .'</a>';
        }

      }else if( isset($qo->term_id) && (is_product_category() || is_product_tag()) ) {

        if( !empty( wc_get_page_id( 'shop' ) ) ){
          $breadcrumbs[] = '<a href="'. esc_url(get_permalink(wc_get_page_id( 'shop' ))) .'">'. htmlentities(get_the_title(wc_get_page_id( 'shop' ))) .'</a>';
        }

        if( is_product_category() ){
	        $ancestors = get_ancestors($qo->term_id, 'product_cat');

					if (!empty($ancestors)) {
					    $ancestors = array_reverse($ancestors);
					    foreach ($ancestors as $ancestor_id) {
					        $ancestor = get_term($ancestor_id, 'product_cat');
					        $breadcrumbs[] = '<a href="'. esc_url(get_term_link($ancestor_id)) .'">'. htmlentities($ancestor->name) .'</a>';
					    }
					}
				}

				$breadcrumbs[] = '<a href="'. esc_url(get_term_link($qo->term_id)) .'">'. htmlentities($qo->name) .'</a>';

      }

		} elseif ( is_category() ) {

			$cat = get_term_by('name', single_cat_title('', false), 'category');
			if ($cat && $cat->parent) {
				$breadcrumbs[] = get_category_parents($cat->parent, true, $separator);
			}

			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities(single_cat_title('', false)) .'</a>';

		} elseif ( is_tag() ) {

			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities(single_tag_title('', false)) . '</a>';

		} elseif ( is_author() ) {

			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities(get_the_author()) .'</a>';

		} elseif ( is_year() ) {

			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities(get_the_time('Y')) .'</a>';

		} elseif ( is_month() ) {

			$breadcrumbs[] = '<a href="'. esc_url(get_year_link(get_the_time('Y'))) .'">' . htmlentities(get_the_time('Y')) . '</a>';
			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities(get_the_time('F')) .'</a>';

		} elseif ( is_day() ) {

			$breadcrumbs[] = '<a href="'. esc_url(get_year_link(get_the_time('Y'))) . '">'. htmlentities( get_the_time('Y') ) .'</a>';
			$breadcrumbs[] = '<a href="'. esc_url(get_month_link(get_the_time('Y'), get_the_time('m'))) .'">'. htmlentities( get_the_time('F') ) .'</a>';
			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities( get_the_time('d') ) .'</a>';

		} elseif ( is_post_type_archive() ) {

			$breadcrumbs[] = '<a href="'. esc_url( mfn_current_URL() ) .'">'. htmlentities( post_type_archive_title( '', false ) ) .'</a>';

		} elseif (is_single() && ! is_attachment()) {

			if ( 'post' != get_post_type() ) {

				// portfolio

				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				$portfolio_page_id = mfn_wpml_ID(mfn_opts_get('portfolio-page'));

				// portfolio page

				if ($slug && $slug['slug'] == mfn_opts_get('portfolio-slug', 'portfolio-item') && $portfolio_page_id) {
					$breadcrumbs[] = '<a href="'. esc_url(get_page_link($portfolio_page_id)) .'">'. htmlentities(get_the_title($portfolio_page_id)) .'</a>';
				}

				// category

				if ($portfolio_page_id) {
					$terms = get_the_terms(get_the_ID(), 'portfolio-types');
					if (! empty($terms) && ! is_wp_error($terms)) {
						$breadcrumbs[] = get_term_parents_list($terms[0], 'portfolio-types', array('separator' => $separator ));
					}
				}

        // single custom post archives | ACF fix

				if( ! in_array( get_post_type(), ['post','page','portfolio','product'] ) ){
					$post_type = get_post_type_object(get_post_type());

	        if ($post_type && !empty($post_type->has_archive)) {
	          $archive_link = get_post_type_archive_link($post_type->name);
	          $breadcrumbs[] = '<a href="'. esc_url($archive_link) .'">'. wp_kses($post_type->label, mfn_allowed_html()) .'</a>';
	        }

				}

				// single

				$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities( get_the_title() ) .'</a>';

			} else {

				// blog single

				$cat = get_the_category();
				if (! empty($cat)) {
					$breadcrumbs[] = get_category_parents($cat[0], true, $separator);
				}

				$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities( get_the_title() ) .'</a>';
			}

		} elseif (! is_page() && get_post_taxonomies()) {

			// taxonomy portfolio

			$post_type = get_post_type_object(get_post_type());
			if ($post_type->name == 'portfolio' && $portfolio_page_id = mfn_wpml_ID(mfn_opts_get('portfolio-page'))) {
				$breadcrumbs[] = '<a href="'. esc_url(get_page_link($portfolio_page_id)) .'">'. htmlentities(get_the_title($portfolio_page_id)) .'</a>';
			}

			$breadcrumbs[] = '<a href="'. esc_url(mfn_current_URL()) .'">'. htmlentities( single_cat_title('', false) ?? '' ) .'</a>';

		} elseif (is_page() && $post->post_parent) {

			// page with parent

			$parent_id = $post->post_parent;
			$parents = array();

			while ($parent_id) {
				$page = get_page($parent_id);
				$parents[] = '<a href="'. esc_url(get_permalink($page->ID)) .'">'. str_replace( '&amp;', '&', htmlentities( get_the_title($page->ID) ) ) .'</a>';
				$parent_id = $page->post_parent;
			}

			$parents = array_reverse($parents);
			$breadcrumbs = array_merge_recursive($breadcrumbs, $parents);

			$breadcrumbs[] = '<a href="'. esc_url(get_permalink($post->ID)) .'">'. str_replace( '&amp;', '&', htmlentities( get_the_title(mfn_ID()) ) ) .'</a>';

    } elseif( $post ) {

			// default

			$breadcrumbs[] = '<a href="'. esc_url(get_permalink($post->ID)) .'">'. str_replace( '&amp;', '&', htmlentities( get_the_title(mfn_ID()) ) ) .'</a>';

		}

		// output -----

		echo '<ul class="breadcrumbs '. esc_attr($class) .'">';

			$count = count($breadcrumbs);
			$i = 1;

			foreach ($breadcrumbs as $bk => $bc) {

				if (strpos($bc, $separator)) {

					// category parent

					echo '<li>'. $bc .'</li>';

				} else {

					if ($i == $count) {
						$separator = '';
					}
					echo '<li>'. $bc . $separator .'</li>';

				}

				$i++;
			}

		echo '</ul>';
	}
}

/**
 * Hex 2 rgba
 */

if (! function_exists('mfn_hex2rgba')) {
	function mfn_hex2rgba($hex, $alpha = 1, $echo = false)
	{
		if( strpos($hex, 'rgb') !== false ){
			return $hex;
		}

		if( strpos($hex, 'var(') !== false ){
			return $hex;
		}

		$hex = str_replace("#", "", $hex);

		if (strlen($hex) == 3) {
			$r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
		} else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		$rgba = 'rgba('. $r.','. $g .','. $b .','. $alpha .')';

		if ($echo) {
			echo esc_attr($rgba);
			return true;
		}

		return $rgba;
	}
}

/**
 * Is dark color
 */

if (! function_exists('mfn_brightness')) {
	function mfn_brightness( $hex, $tolerance = 169, $oposite_color = false )
	{
		if( ! $hex ){
			return false;
		}

		$hex = str_replace("#", "", $hex);

		if( 6 != strlen( $hex ) ){
			return false;
		}

		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));

		$brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

		if ($brightness > $tolerance) {
			$brightness = 'light';
		} else {
			$brightness = 'dark';
		}

		if ($oposite_color) {
			if ($brightness == 'light') {
				$brightness = 'black';
			} else {
				$brightness = 'white';
			}
		}

		return $brightness;
	}
}

/**
 * jPlayer HTML
 */

if (! function_exists('mfn_jplayer_html')) {
	function mfn_jplayer_html($video_m4v, $poster = false)
	{
		$player_id = mt_rand(0, 999);

		$output = '<div id="jp_container_'. esc_attr($player_id) .'" class="jp-video mfn-jcontainer">';
		$output .= '<div class="jp-type-single">';
		$output .= '<div id="jquery_jplayer_'. esc_attr($player_id) .'" class="jp-jplayer mfn-jplayer" data-m4v="'. esc_url($video_m4v) .'" data-img="'. esc_url($poster) .'" data-swf="'. get_theme_file_uri('/assets/jplayer') .'"></div>';
		$output .= '<div class="jp-gui">';
		$output .= '<div class="jp-video-play">';
		$output .= '<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>';
		$output .= '</div>';
		$output .= '<div class="jp-interface">';
		$output .= '<div class="jp-progress">';
		$output .= '<div class="jp-seek-bar">';
		$output .= '<div class="jp-play-bar"></div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '<div class="jp-current-time"></div>';
		$output .= '<div class="jp-duration"></div>';
		$output .= '<div class="jp-controls-holder">';
		$output .= '<ul class="jp-controls">';
		$output .= '<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>';
		$output .= '</ul>';
		$output .= '<div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div>';
		$output .= '<ul class="jp-toggles">';
		$output .= '<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>';
		$output .= '<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>';
		$output .= '</ul>';
		$output .= '</div>';
		$output .= '<div class="jp-title"><ul><li>jPlayer Video Title</li></ul></div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '<div class="jp-no-solution"><span>Update Required</span>To play the media you will need to either update your browser to a recent version or update your <a href="https://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a></div>';
		$output .= '</div>';
		$output .= '</div>'."\n";

		return $output;
	}
}

/**
 * jPlayer
 */

if (! function_exists('mfn_jplayer')) {
	function mfn_jplayer($postID, $sizeH = 'full')
	{
		// masonry square video fix

		if ( $sizeH == 'blog-masonry' ) {
			$sizeH = 'blog-square';
		}

		$video_m4v	= get_post_meta( $postID, 'mfn-post-video-mp4', true );

		$poster	= wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), $sizeH );

		if( ! empty($poster[0]) ){
			$poster	= $poster[0];
		} else {
			$poster = '';
		}

		$output = '<video preload="metadata" poster="'. esc_url( $poster ) .'" controls="1" style="max-width:100%">';
			$output .= '<source type="video/mp4" src="'. esc_url( $video_m4v ) .'" />';
		$output .= '</video>';

		return $output;
	}
}

/**
 * Post Format
 */

if (! function_exists('mfn_post_format')) {
	function mfn_post_format($postID)
	{
		if (get_post_type($postID) == 'portfolio' && is_single($postID)) {

			// portfolio

			if (get_post_meta(get_the_ID(), 'mfn-post-video', true)) {

				// video: embed
				$format = 'video';

			} elseif (get_post_meta(get_the_ID(), 'mfn-post-video-mp4', true)) {

				// video: HTML5
				$format = 'video';

			} else {

				// image
				$format = false;

			}

		} else {

			// blog
			$format = get_post_format($postID);

		}

		return $format;
	}
}

/**
 * Check if we use lazy load images
 */

if (! function_exists('mfn_is_lazy')) {
	function mfn_is_lazy( $lazy_load = false ){

		if ( ! empty($_GET['visual']) || wp_doing_ajax() ){
			return false; // disable lazy load in Bebuilder
		}

		if( 'disable' == $lazy_load ){
			$lazy = false;
		} elseif( 'lazy' == $lazy_load ){
			$lazy = true;
		} elseif( 'lazy' == mfn_opts_get('lazy-load') ){
			$lazy = true;
		} else {
			$lazy = false;
		}

		return $lazy;
	}
}

/**
 * Attachment | GET attachment
 */

 if (! function_exists('mfn_get_attachment')) {
 	function mfn_get_attachment( $src, $size = false, $lazy_load = false, $attr = NULL ){

 		if( ! $size ){
 			$size = 'full';
 		}

 		if( strpos($src, '#') !== false ){

 			$explode_src = explode('#', $src);

 			if( isset( $explode_src[1] ) && is_numeric( $explode_src[1] ) ){
 				$src = $explode_src[1];
 			}

 		}

 		if( ! is_numeric( $src ) ){

 			$attachment_id = mfn_get_attachment_id_url( $src );
 			if( $attachment_id ){
 				$src = $attachment_id;
 			}

 		}

 		if( is_numeric( $src ) ){

 			$src = apply_filters( 'wpml_object_id', $src, 'attachment', true );

 			if( ! empty($lazy_load) && 'disable' == $lazy_load ){

 				if( apply_filters( 'wp_lazy_loading_enabled', true, NULL, NULL ) ){
 					$lazy_status = '__return_true';
 				} else {
 					$lazy_status = '__return_false';
 				}

 				// disable lazy load for specified image
 				remove_filter( 'wp_lazy_loading_enabled', '__return_true' );
 				add_filter( 'wp_lazy_loading_enabled', '__return_false' );
 					$image_output = wp_get_attachment_image( $src, $size, false, $attr );
 				add_filter( 'wp_lazy_loading_enabled', $lazy_status );

 			}	else {

 				if( empty(mfn_get_attachment_data($src, 'width')) ) $attr['width'] = 0;
 				if( empty(mfn_get_attachment_data($src, 'height')) ) $attr['height'] = 0;

 				$image_output = wp_get_attachment_image( $src, $size, false, $attr );

 			}

 			return $image_output;

 		}

 		return false;

 	}
 }

/**
 * Attachment | GET attachment ID by URL
 */

if ( ! function_exists( 'mfn_get_attachment_id_url' ) ) {
	function mfn_get_attachment_id_url( $image_url ) {

		if ( empty( $image_url ) ) {
			return '';
		}

		$hash_pos = strpos( $image_url, '#' );

		if ( $hash_pos !== false ) {
			$id_part = substr( $image_url, $hash_pos + 1 );
			$id_part = trim( $id_part );

			if ( ctype_digit( $id_part ) ) {
				return (int) $id_part;
			}

			$image_url = substr( $image_url, 0, $hash_pos );
		}

		return attachment_url_to_postid( $image_url );
	}
}


/**
 * Attachment | GET attachment data
 */

if ( ! function_exists( 'mfn_get_attachment_data' ) ) {
 	function mfn_get_attachment_data( $image, $data, $with_key = false )
 	{
 		$return = false;
 		$size = false;

 		if( empty($image) ) return '';

    if( strpos($image, '#') !== false ){

			$explode_src = explode('#', $image);

			if( isset( $explode_src[1] ) && is_numeric( $explode_src[1] ) ){
				$image = $explode_src[1];
			}

		}

 		if ( ! is_numeric( $image ) ) {
 			$image = mfn_get_attachment_id_url( $image );
 		}

 		// WPML workaround

 		$image = apply_filters( 'wpml_object_id', $image, 'attachment', true );

 		// ALT

 		if ( 'alt' == $data ) {
 			$return = get_post_meta( $image, '_wp_attachment_image_alt', true );

 			if ( ! $return ) {
 				$return = get_the_title( $image );
 			}
 		}

 		// WIDTH or HEIGHT

 		if ( ! $return ) {
 			$meta = get_post_meta( $image, '_wp_attachment_metadata', true );

 			if ( ! empty( $meta[$data] ) && $meta[$data] !== 1 ) {
 				$return = $meta[$data];
 			}
 		}

 		if ( $return && $with_key ) {
 			$return = esc_attr( $data ) .'="'. esc_attr( $return ) .'"';
 		}

 		return $return;
 	}
}

/**
 * Srcset for Image
 */

if (! function_exists('mfn_srcset')) {
	function mfn_srcset( $attachment_id, $html_tag = true ){

		if( ! $attachment_id || mfn_opts_get('srcset-featured-image') === '0'){
			return false;
		}

		$image_srcset = '';

		if ( $html_tag ){
			$image_srcset .= 'srcset="';
		}

		$image_srcset .= wp_get_attachment_image_srcset($attachment_id, array(400, 200));

		if ( $html_tag ){
			$image_srcset .= '"';
		}

		return $image_srcset;
	}
}

/**
 * Post Thumbnail | GET post thumbnail type
 */

if (! function_exists('mfn_post_thumbnail_type')) {
	function mfn_post_thumbnail_type($postID)
	{
		$type = 'image';
		$post_format = mfn_post_format($postID);

		if ($post_format == 'image') {
			$type = 'image';
		} elseif ($post_format == 'video' && get_post_meta($postID, 'mfn-post-video', true)) {
			$type = 'video embed';
		} elseif ($post_format == 'video' && get_post_meta($postID, 'mfn-post-video-mp4', true)) {
			$type = 'video html5';
		} elseif (get_post_meta($postID, 'mfn-post-slider', true) || get_post_meta($postID, 'mfn-post-slider-layer', true)) {
			$type = 'slider';
		}

		return $type;
	}
}

/**
 * Post Thumbnail | GET post thumbnail
 */

if (! function_exists('mfn_post_thumbnail')) {
	function mfn_post_thumbnail($postID, $type = false, $style = false, $featured_image = false)
	{
		$output = '';
		$sizeH = 'full';
		$sizeV = 'full';
		$is_srcset_enabled = mfn_opts_get('srcset-featured-image');
		$tooltip = [
			'class' => false,
			'zoom' => false,
			'website' => false,
			'details' => false,
		];

		// tooltips

		if( mfn_opts_get('image-frame-style') == 'modern-overlay' ){
			$data_tooltip_position = 'left';
			$tooltip = [
				'class' => 'tooltip',
				'zoom' => 'data-tooltip="'. esc_html__('Zoom','betheme') .'" data-position="'. $data_tooltip_position .'"',
				'website' => 'data-tooltip="'. esc_html__('Go to website','betheme') .'" data-position="'. $data_tooltip_position .'"',
				'details' => 'data-tooltip="'. esc_html__('Details','betheme') .'" data-position="'. $data_tooltip_position .'"',
			];
		}

		// image size -----

		if ($type == 'portfolio') {

			// portfolio

			if ($style == 'list') {

				// portfolio: list

				$sizeH = 'portfolio-list';

			} elseif ($style == 'masonry-flat') {

				// portfolio: masonry flat

				$size = get_post_meta($postID, 'mfn-post-size', true);
				if ($size == 'wide') {
					$sizeH = 'portfolio-mf-w';
				} elseif ($size == 'tall') {
					$sizeH = 'portfolio-mf-t';
				} else {
					$sizeH = 'portfolio-mf';
				}

			} elseif ($style == 'masonry-minimal') {

				// portfolio: masonry minimal

				$sizeH = 'full';

			} else {

				// portfolio: default

				$sizeH = 'blog-portfolio';

			}

		} elseif( 'blog' == $type && in_array($style, array('photo', 'photo2')) ){

			// blog: photo

			$sizeH = 'blog-single';
			$sizeV = 'blog-single';

		} elseif( in_array( $type, ['blog','related'] ) ){

			// related posts

			$sizeH = 'blog-portfolio';

		} elseif ( is_single( $postID ) ) {

			// blog & portfolio: single

			$sizeH = 'blog-single';

		} else {

			// default

			$sizeH = 'blog-portfolio';

		}

		// link wrap -----

		$large_image_url = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'large');

		if( ! $large_image_url ){
			$large_image_url = [
				0 => false,
			];
		}

		if ( is_single($postID) && 'blog' !== $type ) {

			// single

			$link_before = '<a href="'. esc_url($large_image_url[0]) .'" rel="prettyphoto">';
				$link_before .= '<div class="mask"></div>';

				$link_after = '</a>';
			$link_after .= '<div class="image_links">';
				$link_after .= '<a class="zoom '. esc_attr($tooltip['class']) .'" '. $tooltip['zoom'] .' rel="prettyphoto" href="'. esc_url($large_image_url[0]) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><circle cx="11.35" cy="11.35" r="6" class="path"></circle><line x1="15.59" y1="15.59" x2="20.65" y2="20.65" class="path"></line></svg></a>';
			$link_after .= '</div>';

			// single: post

			if (get_post_type() == 'post') {

				// blog: single - disable image zoom

				if (! mfn_opts_get('blog-single-zoom')) {
					$link_before = '';
					$link_after = '';
				}

				// blog single: structured data

				if ( mfn_opts_get('mfn-seo-schema-type') ) {

					$link_after_schema = '';

					$link_before .= '<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject">';

						$image_url = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'full');

						if( ! empty( $image_url[0] ) ){
							$link_after_schema .= '<meta itemprop="url" content="'. esc_url($image_url[0]) .'"/>';
							$link_after_schema .= '<meta itemprop="width" content="'. esc_attr(mfn_get_attachment_data($image_url[0], 'width')) .'"/>';
							$link_after_schema .= '<meta itemprop="height" content="'. esc_attr(mfn_get_attachment_data($image_url[0], 'height')) .'"/>';
						}

					$link_after_schema .= '</div>';

					$link_after = $link_after_schema . $link_after;
				}
			}

		} elseif ($type == 'portfolio') {

			// portfolio

			if( in_array( $style, array('flat', 'masonry-flat') ) ) {
				$is_srcset_enabled = false;
			}

			$external = $featured_image ? $featured_image : mfn_opts_get('portfolio-external'); // next param, old var name

			// external link to project page

			$image_links = get_post_meta(get_the_ID(), 'mfn-post-link', true);

			// image link

			if ($external == 'popup') {

				// popup

				$link_before = '<a href="'. esc_url($large_image_url[0]) .'" rel="prettyphoto">';
				$link_title = '<a href="'. esc_url($large_image_url[0]) .'" rel="prettyphoto">';

			} elseif ( $external == 'disable' ) {

				// disable details

				$link_before = '<a href="'. esc_url($large_image_url[0]) .'" rel="prettyphoto[portfolio]">';
				$link_title = '<a href="'. esc_url($large_image_url[0]) .'" rel="prettyphoto">';

			} elseif ($external && $image_links) {

				// link to project website

				$link_before = '<a href="'. esc_url($image_links) .'" target="'. esc_attr($external) .'">';
				$link_title = '<a href="'. esc_url($image_links) .'" target="'. esc_attr($external ).'">';

			} else {

				// link to project details

				$link_before = '<a href="'. esc_url(get_permalink()) .'">';
				$link_title = '<a href="'. esc_url(get_permalink()) .'">';

			}

			$link_before .= '<div class="mask"></div>';

			$link_after = '</a>';

			// hover

			if (mfn_opts_get('portfolio-hover-title')) {

				// hover: title

				$link_after .= '<div class="image_links hover-title">';
					$link_after .= $link_title . wp_kses(get_the_title(), mfn_allowed_html()) .'</a>';
				$link_after .= '</div>';

			} elseif ($external != 'disable') {

				// hover: icons

				$link_after .= '<div class="image_links">';
					if (! in_array($external, array( '_self', '_blank' ))) {
						$link_after .= '<a class="zoom '. esc_attr($tooltip['class']) .'" '. $tooltip['zoom'] .' rel="prettyphoto" href="'. esc_url($large_image_url[0]) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><circle cx="11.35" cy="11.35" r="6" class="path"></circle><line x1="15.59" y1="15.59" x2="20.65" y2="20.65" class="path"></line></svg></a>';
					}
					if ($image_links) {
						$link_after .= '<a class="external '. esc_attr($tooltip['class']) .'" '. $tooltip['website'] .' target="_blank" href="'. esc_url($image_links) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><g><polyline points="18 14 18 20 6 20 6 8 12 8" class="path"/><line x1="10.34" y1="15.66" x2="19.71" y2="6.29" class="path"/><polyline points="20 12 20 6 14 6" class="path"/></g></svg></a>';
					}
					$link_after .= '<a class="link '. esc_attr($tooltip['class']) .'" '. $tooltip['details'] .' href="'. esc_url(get_permalink()) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><g><path d="M10.17,8.76l2.12-2.12a5,5,0,0,1,7.07,0h0a5,5,0,0,1,0,7.07l-2.12,2.12" class="path"></path><path d="M15.83,17.24l-2.12,2.12a5,5,0,0,1-7.07,0h0a5,5,0,0,1,0-7.07l2.12-2.12" class="path"></path><line x1="10.17" y1="15.83" x2="15.83" y2="10.17" class="path"></line></g></svg></a>';
				$link_after .= '</div>';
			}

		} else {

			// blog

			$link_before = '<a href="'. esc_url(get_permalink()) .'">';
				$link_before .= '<div class="mask"></div>';

			$link_after = '</a>';
			$link_after .= '<div class="image_links double">';
				$link_after .= '<a class="zoom '. esc_attr($tooltip['class']) .'" '. $tooltip['zoom'] .' rel="prettyphoto" href="'. esc_url($large_image_url[0]) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><circle cx="11.35" cy="11.35" r="6" class="path"></circle><line x1="15.59" y1="15.59" x2="20.65" y2="20.65" class="path"></line></svg></a>';
				$link_after .= '<a class="link '. esc_attr($tooltip['class']) .'" '. $tooltip['details'] .' href="'. esc_url(get_permalink()) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><g><path d="M10.17,8.76l2.12-2.12a5,5,0,0,1,7.07,0h0a5,5,0,0,1,0,7.07l-2.12,2.12" class="path"></path><path d="M15.83,17.24l-2.12,2.12a5,5,0,0,1-7.07,0h0a5,5,0,0,1,0-7.07l2.12-2.12" class="path"></path><line x1="10.17" y1="15.83" x2="15.83" y2="10.17" class="path"></line></g></svg></a>';
			$link_after .= '</div>';
		}

		// post format -----

		$post_format = mfn_post_format($postID);

		// featured image: available types

		// no slider if load more

		if ( 'no_slider' == $featured_image ) {
			$type = 'portfolio';
		}

		// images only option

		if ( 'image' == $featured_image ) {
			if (! in_array($post_format, array( 'quote', 'link', 'image' ))) {
				$post_format = 'image-only';
			}
		}

		// image attributes and srcset

		$image_attrs = [
			'class'=>'scale-with-grid',
		];

		if( $is_srcset_enabled ){
			$image_attrs['srcset'] = mfn_srcset( get_post_thumbnail_id($postID), false );
		}

		// switch

		switch ($post_format) {

			case 'quote':
			case 'link':

				// quote - Quote - without image

				return false;
				break;

			case 'image':

				// image - Vertical Image

				if (has_post_thumbnail()) {
					$output .= $link_before;
						$output .= get_the_post_thumbnail($postID, $sizeV, $image_attrs);
					$output .= $link_after;
				}
				break;

			case 'video':

				// video - Video

				if ($video = get_post_meta($postID, 'mfn-post-video', true)) {
					if (is_numeric($video)) {
						// Vimeo
						$output .= '<iframe class="scale-with-grid" src="https://player.vimeo.com/video/'. esc_attr($video) .'" allowFullScreen></iframe>'."\n";
					} else {
						// YouTube
						$output .= '<iframe class="scale-with-grid" src="https://www.youtube.com/embed/'. esc_attr($video) .'?wmode=opaque&rel=0" allowfullscreen></iframe>'."\n";
					}
				} elseif (get_post_meta($postID, 'mfn-post-video-mp4', true)) {
					$output .= mfn_jplayer($postID);
				}
				break;

			case 'image-only':

				// images only option

				if (has_post_thumbnail()) {
					$output .= $link_before;
						$output .= get_the_post_thumbnail($postID, $sizeH, $image_attrs);
					$output .= $link_after;
				}
				break;

			default:

				// standard - Text, Horizontal Image, Slider

				$rev_slider = get_post_meta($postID, 'mfn-post-slider', true);
				$lay_slider = get_post_meta($postID, 'mfn-post-slider-layer', true);

				if (('portfolio' != $type) && ($rev_slider || $lay_slider)) {

					if ($rev_slider) {
						// Revolution Slider
						$output .= do_shortcode('[rev_slider '. $rev_slider .']');
					} elseif ($lay_slider) {
						// Layer Slider
						$output .= do_shortcode('[layerslider id="'. $lay_slider .'"]');
					}

				} elseif ( has_post_thumbnail() && ( -1 !== get_post_thumbnail_id() ) ) {

					// Image

					$output .= $link_before;
						$output .= get_the_post_thumbnail($postID, $sizeH, $image_attrs);
					$output .= $link_after;

				}
		}

		return $output;
	}
}

/**
 * FIX: WP sometimes returns 1 as image width & height
 */

function mfn_get_attachment_image_src( $image, $attachment_id, $size, $icon ){

	// width

	if( !empty($image[1]) && $image[1] === 1 ){
		$image[1] = false;
	}

	// height

	if( !empty($image[2]) && $image[2] === 1 ){
		$image[2] = false;
	}

	return $image;

}
add_filter( 'wp_get_attachment_image_src', 'mfn_get_attachment_image_src', 10, 4 );

/**
 * Images optimization
 */

function mfn_images_optimization($upload) {

  if ( mfn_opts_get('images-optimization') && in_array($upload['type'], ['image/jpeg', 'image/png']) ) {

    $file_path = $upload['file'];

    if (extension_loaded('imagick') || extension_loaded('gd')) {

      $image_editor = wp_get_image_editor($file_path);

      if (!is_wp_error($image_editor)) {

        $quality = 80;
        $image_editor->set_quality($quality);

        $file_info = pathinfo($file_path);
        $dirname = $file_info['dirname'];
        $filename = $file_info['filename'];
        $def_filename = wp_unique_filename($dirname, $filename . '.webp');
        $new_file_path = $dirname . '/' . $def_filename;

        $saved_image = $image_editor->save($new_file_path, 'image/webp');

        if (!is_wp_error($saved_image) && file_exists($saved_image['path'])) {
          $upload['file'] = $saved_image['path'];
          $upload['url'] = str_replace(basename($upload['url']), basename($saved_image['path']), $upload['url']);
          $upload['type'] = 'image/webp';

          if( mfn_opts_get('images-optimization-remove') ){
            @unlink($file_path);
          }

        }

      }

    }

  }

  return $upload;
}
add_filter('wp_handle_upload', 'mfn_images_optimization');

/**
 * Single Post Navigation | SET query order
 */

// previous

if (! function_exists('mfn_filter_previous_post_sort')) {
	function mfn_filter_previous_post_sort($sort)
	{
		if (mfn_get_portfolio_order() == 'ASC') {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}
		$sort = "ORDER BY p.". esc_sql(mfn_get_portfolio_orderby()) ." ". $order ." LIMIT 1";
		return $sort;
	}
}

if (! function_exists('mfn_filter_previous_post_where')) {
	function mfn_filter_previous_post_where($where)
	{
		global $post, $wpdb;

		$orderby = mfn_get_portfolio_orderby();
		$where = preg_replace("/(.*)p.post_type/", "AND p.post_type", $where);

		if (mfn_get_portfolio_order() == 'ASC') {
			$where_pre = $wpdb->prepare("WHERE p.". esc_sql($orderby) ." < %s", $post->$orderby);
		} else {
			$where_pre = $wpdb->prepare("WHERE p.". esc_sql($orderby) ." > %s", $post->$orderby);
		}

		$where = $where_pre.' '.$where;
		return $where;
	}
}

// next

if (! function_exists('mfn_filter_next_post_sort')) {
	function mfn_filter_next_post_sort($sort)
	{
		$sort = "ORDER BY p.". esc_sql(mfn_get_portfolio_orderby()) ." ". esc_sql(mfn_get_portfolio_order()) ." LIMIT 1";
		return $sort;
	}
}

if (! function_exists('mfn_filter_next_post_where')) {
	function mfn_filter_next_post_where($where)
	{
		global $post, $wpdb;

		$orderby = mfn_get_portfolio_orderby();
		$where = preg_replace("/(.*)p.post_type/", "AND p.post_type", $where);

		if (mfn_get_portfolio_order() == 'ASC') {
			$where_pre = $wpdb->prepare("WHERE p.". esc_sql($orderby) ." > %s", $post->$orderby);
		} else {
			$where_pre = $wpdb->prepare("WHERE p.". esc_sql($orderby) ." < %s", $post->$orderby);
		}

		$where = $where_pre.' '.$where;
		return $where;
	}
}

// helpers

if (! function_exists('mfn_get_portfolio_order')) {
	function mfn_get_portfolio_order()
	{
		return mfn_opts_get('portfolio-order', 'DESC');
	}
}

if (! function_exists('mfn_get_portfolio_orderby')) {
	function mfn_get_portfolio_orderby()
	{
		$orderby = mfn_opts_get('portfolio-orderby', 'date');
		switch ($orderby) {
			case 'title':
				$orderby = 'post_title';
				break;
			case 'menu_order':
				$orderby = 'menu_order';
				break;
			default:
				$orderby = 'post_date';
		}
		return $orderby;
	}
}

// filters

if (! function_exists('mfn_post_navigation_sort')) {
	function mfn_post_navigation_sort()
	{
		add_filter('get_previous_post_sort', 'mfn_filter_previous_post_sort');
		add_filter('get_previous_post_where', 'mfn_filter_previous_post_where');
		add_filter('get_next_post_sort', 'mfn_filter_next_post_sort');
		add_filter('get_next_post_where', 'mfn_filter_next_post_where');
	}
}

/**
 * Single Post Navigation | GET header navigation
 */

if (! function_exists('mfn_post_navigation_header')) {
	function mfn_post_navigation_header($post_prev, $post_next, $post_home, $translate = array())
	{
		$style = mfn_opts_get('prev-next-style');

		$output = '<div class="column one post-nav '. esc_attr($style) .'">';

		if ($style == 'minimal') {

				// minimal

			if ($post_prev) {
				$output .= '<a class="prev" href="'. esc_url(get_permalink($post_prev)) .'"><i class="icon icon-left-open-big" aria-label="previous post"></i></a>';
			}
			if ($post_next) {
				$output .= '<a class="next" href="'. esc_url(get_permalink($post_next)) .'"><i class="icon icon-right-open-big" aria-label="next post"></i></a>';
			}
			if ($post_home) {
				$output .= '<a class="home" href="'. esc_url(get_permalink($post_home)) .'"><svg class="icon" aria-label="all posts" width="22" height="22" xmlns="https://www.w3.org/2000/svg"><path d="M7,2v5H2V2H7 M9,0H0v9h9V0L9,0z"/><path d="M20,2v5h-5V2H20 M22,0h-9v9h9V0L22,0z"/><path d="M7,15v5H2v-5H7 M9,13H0v9h9V13L9,13z"/><path d="M20,15v5h-5v-5H20 M22,13h-9v9h9V13L22,13z"/></svg></a>';
			}

		} else {

				// default

			$output .= '<ul class="next-prev-nav">';
			if ($post_prev) {
				$output .= '<li class="prev"><a class="button default the-icon" href="'. esc_url(get_permalink($post_prev)) .'"><span class="button_icon"><i class="icon-left-open" aria-label="previous post"></i></span></a></li>';
			}
			if ($post_next) {
				$output .= '<li class="next"><a class="button default the-icon" href="'. esc_url(get_permalink($post_next)) .'"><span class="button_icon"><i class="icon-right-open" aria-label="next post"></i></span></a></li>';
			}
			$output .= '</ul>';

			if ($post_home) {
				$output .= '<a class="list-nav" href="'. esc_url(get_permalink($post_home)) .'"><i class="icon-layout" aria-hidden="true"></i>'. esc_html($translate['all']) .'</a>';
			}
		}

		$output .= '</div>';

		return $output;
	}
}

/**
 * Single Post Navigation | GET sticky navigation
 */

if (! function_exists('mfn_post_navigation_sticky')) {
	function mfn_post_navigation_sticky($post, $next_prev, $icon)
	{
		$has_date = mfn_opts_get('prev-next-date','1');

		$output = '';

		if (is_object($post)) {

			// move this DOM element with JS

			$style = mfn_opts_get('prev-next-sticky-style', 'default');

			$output .= '<a class="fixed-nav fixed-nav-'. esc_attr($next_prev) .' format-'. esc_attr(get_post_format($post)) .' style-'. esc_attr($style) .'" href="'. esc_url(get_permalink($post)) .'">';

				$output .= '<span class="arrow"><i class="'. esc_attr($icon) .'" aria-hidden="true"></i></span>';

				$output .= '<div class="photo">';
					$output .= get_the_post_thumbnail($post->ID, 'be_thumbnail');
				$output .= '</div>';

				$output .= '<div class="desc">';
					$output .= '<p>'. wp_kses(get_the_title($post), array()) .'</p>';
					if( $has_date ){
						$output .= '<span class="date"><i class="icon-clock" aria-hidden="true"></i>'. esc_html(get_the_date(get_option('date_format'), $post->ID)) .'</span>';
					}
				$output .= '</div>';

			$output .= '</a>';
		}

		return $output;
	}
}

/**
 * Search | SET add custom fields to search query
 */

if (! function_exists('mfn_search')) {
  function mfn_search( $search_query )
  {
    global $wpdb;

    if ( is_admin() ) {
      return false;
    }

    if ( is_search() && $search_query->is_main_query() && $search_query->is_search() ) {

      $keyword = trim(get_search_query() ?? '');
      $is_search_shop_only = apply_filters( 'mfn_search_is_shop_only', mfn_opts_get('header-search') === 'shop' );

      if ( ! $keyword ) {
          return false;
      }

      // WooCommerce uses default search Query

      if (function_exists('is_woocommerce') && is_woocommerce()) {
        return false;
      }

      $keyword = '%'. $wpdb->esc_like($keyword) .'%';

      $post_ids = [];

      // Title

      if ( apply_filters( 'mfn_search_enable_title', true ) ) {
        $post_ids = array_merge( $post_ids, $wpdb->get_col($wpdb->prepare("
          SELECT DISTINCT `ID` FROM {$wpdb->posts}
          WHERE `post_title` LIKE %s
        ", $keyword)) );
      }

      // Content

      if ( apply_filters( 'mfn_search_enable_content', true ) ) {
        $post_ids = array_merge( $post_ids, $wpdb->get_col($wpdb->prepare("
          SELECT DISTINCT `ID` FROM {$wpdb->posts}
          WHERE `post_content` LIKE %s
        ", $keyword)) );
      }

      // Builder

      if ( apply_filters( 'mfn_search_enable_meta', true ) ) {
        $post_ids = array_merge( $post_ids, $wpdb->get_col($wpdb->prepare("
          SELECT DISTINCT `post_id` FROM {$wpdb->postmeta}
          WHERE `meta_key` = 'mfn-page-items-seo'
          AND `meta_value` LIKE %s
        ", $keyword)) );
      }

      // Hook to add custom search logics (e.g., by tags)

      $post_ids = apply_filters( 'mfn_search_custom_post_ids', $post_ids, $keyword, $is_search_shop_only );

      // Live Search -- category load

      if ( isset($_GET['mfn_livesearch']) ) {

        // Config

        // We need the string(categories), and word array (for posts and page search)
        $words_string = preg_replace('/\s/', ',', $keyword);
        $words_array = explode(',', preg_replace('/\%/', '', $words_string) );
        $posts_array = [];
        // Default queue: Posts/Pages -> Categories -> WooCategories

        // Posts and Page Name query

        foreach ($words_array as $words_key => $words_value){ // relation: OR
          $args_title = array( 's' => $words_value, 'posts_per_page' => -1 );
          $query_title = new WP_Query($args_title);

          if($query_title->have_posts()){
            $posts_array = $query_title->get_posts();
            foreach($posts_array as $post_item_key => $post_item_val){
              $post_ids[] = strval($post_item_val->ID);
            }
          }
        }

        if( ! $is_search_shop_only ){

          // Posts and Page Categories query

          foreach($words_array as $words_key => $words_value){
            $args_category = array( 'category_name' => $words_value, 'posts_per_page' => -1, 'fields' => 'ids'); // replace space => comma
            $query_category = new WP_Query($args_category);

            if($query_category->have_posts()){
              $posts_array = $query_category->get_posts();
              foreach($posts_array as $cat_item_key => $cat_item_val){
                $post_ids[] = strval($cat_item_val);
              }
            }
          }

        }

        // WooCommerce Categories query

        foreach($words_array as $words_key => $words_value){
          $args_category = array( 'product_cat' => $words_value, 'posts_per_page' => -1, 'fields' => 'ids', 'post_type' => 'product'); // replace space => comma
          $query_category = new WP_Query($args_category);

          if($query_category->have_posts()){
            $posts_array = $query_category->get_posts();
            foreach($posts_array as $cat_item_key => $cat_item_val){
              $post_ids[] = strval($cat_item_val);
            }
          }
        }

        if( $is_search_shop_only ){
          $search_query->set('post_type', 'product');
          // limit the search to products, prevent searching for posts/pages
        }

        if ( ! isset($_GET['searchpage']) ) {
          $search_amount_posts = esc_attr(mfn_opts_get('header-search-live-load-posts', 10));
          $search_query->set('posts_per_page', $search_amount_posts);
          // if value above will be < 10, then button of show more in livesearch will not appear!
        }
      }

      if ( ! count($post_ids) ) {
        return false;
      }

      $search_query->set('s', false);
      $search_query->set('post__in', array_unique($post_ids));
      $search_query->set('orderby', 'post__in');
    }
  }
}
add_action('pre_get_posts', 'mfn_search');

/**
 * Refine search to match whole words only.
 * This prevents partial matches like 'taking' when searching for 'king'.
 */
if (! function_exists('mfn_search_exact_word')) {
  function mfn_search_exact_word( $search, $wp_query ) {

    global $wpdb;

    // echo $search;

    if ( is_admin() || ! $wp_query->is_main_query() || ! $wp_query->is_search() ) {
      return $search;
    }

    $search_term = $wp_query->get('s');
    if ( empty( $search_term ) ) {
      return $search;
    }

    $is_mysql8 = version_compare( $wpdb->db_version(), '8.0', '>=' );
    $start_boundary = $is_mysql8 ? '\\b' : '[[:<:]]';
    $end_boundary   = $is_mysql8 ? '\\b' : '[[:>:]]';

    $regexp = $start_boundary . preg_quote( $search_term ) . $end_boundary;

    $search = $wpdb->prepare(
      " AND ({$wpdb->posts}.post_title REGEXP %s OR {$wpdb->posts}.post_content REGEXP %s OR {$wpdb->posts}.post_excerpt REGEXP %s)",
      $regexp,
      $regexp,
      $regexp
    );

    return $search;
  }
}
if( 'exact' === mfn_opts_get('header-search-mode') ){
  add_filter( 'posts_search', 'mfn_search_exact_word', 20, 2 );
}

/**
 * All categories available
 */

if (! function_exists('mfn_list_categories')) {
	function mfn_list_categories()
	{
    $is_search_shop_only = mfn_opts_get('header-search') === 'shop' ? true : false;

		$portfolio_terms = get_terms( array( 'taxonomy' => 'portfolio-types', 'hide_empty' => false ) );
		$blog_terms = get_terms( 'category', array( 'hide_empty' => false ) );
		$shop_terms = get_terms( 'product_cat', array( 'hide_empty' => false ) );

		$all_terms = [];

    if( ! $is_search_shop_only ){

  		if ( $portfolio_terms && empty($portfolio_terms->errors) ) {
  			$all_terms[] = $portfolio_terms;
  		}

  		if ( $blog_terms && empty($blog_terms->errors) ) {
  			$all_terms[] = $blog_terms;
  		}

    }

		if ( $shop_terms && empty($shop_terms->errors) ) {
			$all_terms[] = $shop_terms;
		}

		$all_terms = array_merge( [], ...$all_terms );
		$all_terms = json_decode( json_encode($all_terms), true );

		$categories_found = array();
		foreach($all_terms as $key => $value){
			$categories_found[get_category_link($value['term_id'])] = $value['name'];
		}

		$categories_found = array_unique($categories_found);

		return $categories_found;
	}
}

/**
 * Posts per page & pagination fix
 */

if (! function_exists('mfn_option_posts_per_page')) {
	function mfn_option_posts_per_page($value)
	{
		if (is_tax('portfolio-types')) {
			$posts_per_page = mfn_opts_get('portfolio-posts', 6, ['not_empty' => true]);
		} else {
			$posts_per_page = mfn_opts_get('blog-posts', 5, ['not_empty' => true]);
		}
		return $posts_per_page;
	}
}

if (! function_exists('mfn_posts_per_page')) {
	function mfn_posts_per_page()
	{
		add_filter('option_posts_per_page', 'mfn_option_posts_per_page');
	}
}
add_action('init', 'mfn_posts_per_page', 0);

/**
 *	Comments number with text
 */

if (! function_exists('mfn_comments_number')) {
	function mfn_comments_number()
	{
		$translate['comment'] = mfn_opts_get('translate') ? mfn_opts_get('translate-comment', 'comment') : __('comment', 'betheme');
		$translate['comments'] = mfn_opts_get('translate') ? mfn_opts_get('translate-comments', 'comments') : __('comments', 'betheme');
		$translate['comments-off'] = mfn_opts_get('translate') ? mfn_opts_get('translate-comments-off', 'comments off') : __('comments off', 'betheme');

		$num_comments = get_comments_number(); // get_comments_number returns only a numeric value

		if (comments_open()) {
			if ($num_comments != 1) {
				$comments = '<a href="'. esc_url(get_comments_link()) .'">'. esc_html($num_comments).'</a> '. esc_html($translate['comments']);
			} else {
				$comments = '<a href="'. esc_url(get_comments_link()) .'">1</a> '. esc_html($translate['comment']);
			}
		} else {
			$comments = $translate['comments-off'];
		}
		return $comments;
	}
}

/**
 *	Menu title in selected location
 */

if (! function_exists('mfn_get_menu_name')) {
	function mfn_get_menu_name($location)
	{
		if (! has_nav_menu($location)) {
			return false;
		}

		$menus = get_nav_menu_locations();
		$menu_title = wp_get_nav_menu_object($menus[$location])->name;

		return $menu_title;
	}
}

/**
 *	GET | WordPress Authors
 */

if (! function_exists('mfn_get_authors')) {
	function mfn_get_authors()
	{
		$authors = get_users( array( 'role__in' => array( 'contributor', 'author', 'editor', 'administrator' ) ) );

		if (is_array($authors)) {
			foreach ($authors as $ka => $author) {

				// remove authors without posts

				$posts_count = count_user_posts( $author->ID, 'post', true );

				if( $posts_count < 1 ){
					unset($authors[$ka]);
				}

			}
		}

		return $authors;
	}
}

/**
 * GET Categories
 * Categories for posts or specified taxonomy
 */

if (! function_exists('mfn_get_categories')) {
	function mfn_get_categories($category)
	{
		$categories = get_categories(array(
			'taxonomy' => $category,
			'hide_empty' => false,
		));

		$array = array(
			'' => esc_html__('All', 'mfn-opts'),
		);

		foreach ($categories as $cat) {
			if (is_object($cat)) {
				$array[$cat->slug] = $cat->name;
			}
		}

		return $array;
	}
}

/**
 * GET Hierarchical Taxonomy
 * Categories for posts or specified taxonomy with hierarchy
 */

if (! function_exists('mfn_hierarchical_taxonomy')) {
	function mfn_hierarchical_taxonomy($type){

		// 1st level
		$taxonomy = get_terms( array(
			'taxonomy' => $type,
			'hide_empty' => false,
			'parent' => 0
		) );

		$array = array();

		if( !empty($taxonomy) && is_array($taxonomy) && count($taxonomy) > 0 ){
			foreach ($taxonomy as $t=>$taxo) {
				if (is_object($taxo) && !empty($taxo->name) ) {
					$array[] = (object) array('id' => $taxo->term_id, 'slug' => $taxo->slug, 'name' => $taxo->name);

					// 2nd level
					$childrens = get_terms( array(
						'taxonomy' => $type,
						'hide_empty' => false,
						'parent' => $taxo->term_id
					) );

					if( count($childrens) > 0 ){
						foreach ($childrens as $ch) {
							if(is_object($ch) ) {
								$array[] = (object) array('id' => $ch->term_id, 'slug' => $ch->slug, 'name' => '&nbsp;&nbsp;'.$ch->name);

								// 3rd level
								$childs = get_terms( array(
									'taxonomy' => $type,
									'hide_empty' => false,
									'parent' => $ch->term_id
								) );

								if( count($childs) > 0 ){
									foreach ($childs as $chi) {
										if(is_object($chi) ) {
											$array[] = (object) array('id' => $chi->term_id, 'slug' => $chi->slug, 'name' => '&nbsp;&nbsp;&nbsp;&nbsp;'.$chi->name);

											// 4th level
											$childs4 = get_terms( array(
												'taxonomy' => $type,
												'hide_empty' => false,
												'parent' => $chi->term_id
											) );

											if( count($childs4) > 0 ){
												foreach ($childs4 as $ch4) {
													if(is_object($ch4) ) {
														$array[] = (object) array('id' => $ch4->term_id, 'slug' => $ch4->slug, 'name' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ch4->name);

														// 5th level
														$childs5 = get_terms( array(
															'taxonomy' => $type,
															'hide_empty' => false,
															'parent' => $ch4->term_id
														) );

														if( count($childs5) > 0 ){
															foreach ($childs5 as $ch5) {
																if(is_object($ch5) ) {
																	$array[] = (object) array('id' => $ch5->term_id, 'slug' => $ch5->slug, 'name' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ch5->name);
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
					}

				}
			}
		}

		return $array;
	}
}

/**
 * GET Post Types
 * post types and its categories
 */

if (! function_exists('mfn_get_posttypes')) {
	function mfn_get_posttypes( $nested = false )
	{
		// $nested = posts || tax

		$array = array( '' => esc_html__('All', 'mfn-opts') );

		if( $nested && $nested == 'posts' ){

			$array['page'] = array(
				'label' => esc_html__('Pages', 'mfn-opts'),
				'items' => get_posts( array( 'post_type' => 'page', 'numberposts' => -1 ) )
			);

			if( function_exists('is_woocommerce') ){
				$array['product'] = array(
					'label' => esc_html__('Shop', 'mfn-opts'),
					'items' => get_posts( array( 'post_type' => 'product', 'numberposts' => -1 ) )
				);
			}

			$array['post'] = array(
				'label' => esc_html__('Posts', 'mfn-opts'),
				'items' => get_posts( array( 'post_type' => 'post', 'numberposts' => -1 ) )
			);

			$array['portfolio'] = array(
				'label' => esc_html__('Portfolio', 'mfn-opts'),
				'items' => get_posts( array( 'post_type' => 'portfolio', 'numberposts' => -1 ) )
			);

			$array['offer'] = array(
				'label' => esc_html__('Offer', 'mfn-opts'),
				'items' => get_posts( array( 'post_type' => 'offer', 'numberposts' => -1 ) )
			);

		}else if( $nested && $nested == 'tax' ){

			$array['page'] = array(
				'label' => esc_html__('Pages', 'mfn-opts'),
				'items' => false
			);

			if( function_exists('is_woocommerce') ){
				$array['product'] = array(
					'label' => esc_html__('Shop', 'mfn-opts'),
					'items' => mfn_hierarchical_taxonomy('product_cat')
				);
			}

			$array['post'] = array(
				'label' => esc_html__('Posts', 'mfn-opts'),
				'items' => mfn_hierarchical_taxonomy('category')
			);

			$array['portfolio'] = array(
				'label' => esc_html__('Portfolio', 'mfn-opts'),
				'items' => mfn_hierarchical_taxonomy('portfolio-types')
			);

			$array['offer'] = array(
				'label' => esc_html__('Offer', 'mfn-opts'),
				'items' => mfn_hierarchical_taxonomy('offer-types')
			);

		}else{

			$array['page'] 		= esc_html__('Pages', 'mfn-opts');
			if( function_exists('is_woocommerce') ){
				$array['woocommerce'] = esc_html__('Shop', 'mfn-opts');
			}
			$array['post'] 		= esc_html__('Posts', 'mfn-opts');
			$array['portfolio'] = esc_html__('Portfolio', 'mfn-opts');
			$array['offer'] 	= esc_html__('Offer', 'mfn-opts');

		}

		return $array;
	}
}

/**
 *	Under Construction
 */

if (! function_exists('mfn_is_under_construction')) {
 	function mfn_is_under_construction(){
    if ( mfn_opts_get('construction') ) {

      $server = $_SERVER; // context is safe and necessary

      $basename = basename($server['PHP_SELF']);
      $script_name = $server['SCRIPT_NAME'];

      $allowed = [
        'wp-login.php',
        'wp-cron.php',
        'wp-load.php',
        'xmlrpc.php',
      ];

      if ( class_exists( '\WPS\WPS_Hide_Login\Plugin' ) && get_option( 'whl_page' ) === $script_name ) {

        // do nothing WPS Hide Login plugin

      } elseif ( ! isset($_POST['_wpcf7']) && ! in_array( $basename, $allowed ) ) {

        if ( ! is_user_logged_in() && ! is_admin() ){
					return true;
				}

			}

		}

    return false;
  }
}

if (! function_exists('mfn_under_construction')) {
	function mfn_under_construction()
	{
		if ( mfn_is_under_construction() ) {
			get_template_part('under-construction');
			exit();
		}
	}
}
add_action('wp', 'mfn_under_construction', 30);

/**
 * Repetitive Link | Accessibility PBL
 */

if (! function_exists('mfn_repetitive_link')) {
	function mfn_repetitive_link( $link, $title, $repetitive_link = '' )
	{
		$ready_repetetive_text = false;
		$response = '';

		if( !empty( $repetitive_link ) ) {

			$ready_repetetive_text = $repetitive_link;

		} else {

			$post_id = url_to_postid( esc_url($link) );

			switch ( true ) {
				case $link[0] === '#' && isset($link[1]): // Scroll to section, probably anchor.
					$cleared_section_name = preg_replace('/#*/', '', $link);
					$ready_repetetive_text = 'Scroll to '.$cleared_section_name.' section';
					break;
				case $post_id === 0 || ($link[0] === '#'): // No repetitve text, do nothing
					break;
				case $post_id > 0: // Get the Title
					$ready_repetetive_text = get_the_title( $post_id );
					break;
			}

		}

		if ( ! is_bool( $ready_repetetive_text ) ) {
			$response = $title .'<span class="screen-reader-text"> - '. $ready_repetetive_text .'</span>';
		} else {
			$response = $title;
		}

		return $response;
	}
}

/**
 *	Set Max Content Width
 */

if (! isset($content_width)) {
	$content_width = 1220;
}

/**
 * Unserializes data only if it was serialized
 */

function mfn_maybe_unserialize( $data ) {
	if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
		return @unserialize( trim( $data ), ['allowed_classes' => false] );
	}

	return $data;
}

/**
 *	WPML | Date Format
 */

if (! function_exists('mfn_wpml_date_format')) {
	function mfn_wpml_date_format($format)
	{
		if (function_exists('icl_translate')) {
			$format = icl_translate('Formats', $format, $format);
		}
		return $format;
	}
}
add_filter('option_date_format', 'mfn_wpml_date_format');

/*
 * WPML | Workaround for compsupp-5901
 */

function mfn_wpml_encode_custom_field( $custom_field_val, $custom_field_name ) {
  if ( $custom_field_name === 'mfn-page-items' ) {
    $custom_field_val = mb_convert_encoding( $custom_field_val, 'UTF-8', 'auto' );
    $custom_field_val = base64_encode( serialize( $custom_field_val ) );
  }
  return $custom_field_val;
}
add_filter( 'wpml_encode_custom_field', 'mfn_wpml_encode_custom_field', 10, 2 );

function mfn_wpml_decode_custom_field( $custom_field_val, $custom_field_name ) {
  if ( $custom_field_name === 'mfn-page-items' && is_string( $custom_field_val ) ) {
    $custom_field_val = mb_convert_encoding($custom_field_val, 'UTF-8', 'auto');
    $custom_field_val = mfn_maybe_unserialize( base64_decode( $custom_field_val ) );
  }
  return $custom_field_val;
}
add_filter( 'wpml_decode_custom_field', 'mfn_wpml_decode_custom_field', 10, 2 );

/**
 *	WPML | ID
 *	@param type string – 'post', 'page', 'post_tag' or 'category'
 */

if (! function_exists('mfn_wpml_ID')) {
	function mfn_wpml_ID($id, $type = 'page')
	{
		if (function_exists('icl_object_id')) {
			return icl_object_id($id, $type, true);
		} else {
			return $id;
		}
	}
}

/**
 *	WPML | Term slug
 */

if (! function_exists('mfn_wpml_term_slug')) {
	function mfn_wpml_term_slug($slug, $type, $multi = false) {
		if (function_exists('icl_object_id')) {
			if ($multi) {

				// multiple categories

				$slugs = explode(',', $slug);

				if (is_array($slugs)) {
					foreach ($slugs as $slug_k => $slug) {
						$slug = trim($slug);

						$term = get_term_by('slug', $slug, $type);
						if (! $term || is_wp_error($term)) continue;
						$term = apply_filters('wpml_object_id', $term->term_id, $type, true);
						$slug = get_term_by('term_id', $term, $type)->slug;
						if (! $slug || is_wp_error($slug)) continue;
						$slugs[$slug_k] = $slug;
					}
				}

				$slug = implode(',', $slugs);
			} else {

				// single category

				$term = get_term_by('slug', $slug, $type);

				if( !empty($term) ) {
					$term = apply_filters('wpml_object_id', $term->term_id, $type, true);
					$slug = get_term_by('term_id', $term, $type)->slug;
				}

			}
		}

		return $slug;

	}
}

/**
 * Hubspot Impact leading code
 */

function mfn_get_hubspot_affiliate_code() {
	 return 'oegjYn';
}

add_filter( 'leadin_impact_code', 'mfn_get_hubspot_affiliate_code' );

function disable_redirect() {
  remove_all_actions( 'leadin_redirect' );
}

add_action( 'leadin_activate', 'disable_redirect' );

/**
 *	Schema | Auto Get Schema Type By Post Type
 */

if (! function_exists('mfn_tag_schema')) {
	function mfn_tag_schema()
	{
		$schema = 'https://schema.org/';

		// Is Woocommerce product
		if (function_exists('is_product') && is_product()) {
			$type = false;
		} elseif (is_single() && get_post_type() == 'post') {

			// Single post
			$type = "Article";
		} elseif (is_author()) {

			// Author page
			$type = 'ProfilePage';
		} elseif (is_search()) {

			// Search results
			$type = 'SearchResultsPage';
		} else {

			// Default
			$type = 'WebPage';
		}

		if (mfn_opts_get('mfn-seo-schema-type') && $type) {
			echo ' itemscope itemtype="'. esc_url($schema) . esc_attr($type) .'"';
		}

		return true;
	}
}

/**
 * Uploads Folder
 */

if (! function_exists('mfn_uploads_dir')) {
	function mfn_uploads_dir( $dir = 'baseurl', $depth1 = '', $depth2 = '' ){

		$upload_dir = wp_upload_dir();
		$path_be = $upload_dir[$dir] .'/betheme';

		if ( !empty($depth1) ) {
			$path_be .= '/' . $depth1;
		}

		if ( !empty($depth2) ) {
			$path_be .= '/' . $depth2;
		}

		return wp_normalize_path($path_be);
	}
}

/**
 * Bundled plugins
 */

if (! function_exists('mfn_bundled_plugins')) {
	function mfn_bundled_plugins(){

		if (! mfn_opts_get('plugin-rev')) {
	  	if (function_exists('set_revslider_as_theme')) {
	  		set_revslider_as_theme();
	  	}
	  }

	  if (! mfn_opts_get('plugin-visual')) {
	  	function mfn_vc_set_as_theme(){
	  		vc_set_as_theme();
	  	}
			add_action('vc_before_init', 'mfn_vc_set_as_theme');
	  }

	}
}
mfn_bundled_plugins();

/**
 * Is BeBuilder Blocks
 */

function mfn_is_blocks( $vb = false, $post_id = false ){

	global $post;

  if( empty($post_id) ){
    if( ! empty($post->ID) ){
      $post_id = $post->ID;
    }
  }

	if( !empty($post_id) && get_post_type($post_id) == 'template' && get_post_meta($post_id, 'mfn_template_type', true) && in_array( get_post_meta($post_id, 'mfn_template_type', true), array('header', 'footer', 'megamenu', 'popup') ) ){
		return false;
	}

	if( $vb || wp_doing_ajax() || (!empty($_GET['visual']) && 'iframe' == $_GET['visual']) ){

    // demo
    if( ! empty($_GET['ui']) && 'blocks' === $_GET['ui'] ){
      return true;
    }

		$user_id = get_current_user_id();
		$options = get_site_option( 'betheme_builder_'. $user_id );

		if( !empty($options['builder-blocks']) ){
			return true;
		}

	}

	return false;
}

/**
 * Is Elementor
 */

function mfn_is_elementor( $post_id ){

 	if ( ! did_action( 'elementor/loaded' ) ) {
 		return false;
 	}

 	if ( ! $post_id || wp_doing_ajax()) {
 		return false;
 	}

 	if( is_object( \Elementor\Plugin::$instance->documents->get( $post_id ) ) ){
 		return \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
 	}
}

/**
 * Rank Math SEO | Compatibility of BeTheme Table of Contents
 */

add_filter( 'rank_math/researches/toc_plugins', function( $toc_plugins ) {
  $toc_plugins['seo-by-rank-math/rank-math.php'] = 'Betheme Table Of Contents';
  return $toc_plugins;
});

/**
 *	Registration | Is registered
 */

 function mfn_is_registered()
 {
 	if ( mfn_get_purchase_code() ) {
 		return strlen( mfn_get_purchase_code() );
 	}

 	return false;
 }

/**
 *	Registration | Get purchase code
 */

function mfn_get_purchase_code()
{
 	$code = get_site_option( 'envato_purchase_code_7758048' );

 	if( ! $code ){
 		// BeTheme < 21.0.8 backward compatibility
 		$code = get_site_option( 'betheme_purchase_code' );
 		if( $code ){
 			update_site_option( 'envato_purchase_code_7758048', $code );
 			delete_site_option( 'betheme_purchase_code' );
 			delete_site_option( 'betheme_registered' );
 		}
 	}

	return $code;
}

/**
 *	Registration | Get purchase code with asterisk
 */

function mfn_get_purchase_code_hidden()
{
	$code = mfn_get_purchase_code();

	if ($code) {
		$code = substr($code, 0, 13);
		$code = $code .'-****-****-************';
	}

	return $code;
}

/**
 * Verify template
 */

function mfn_verify_tmpl( $id, $type ){
	if(
		!empty($id) &&
		is_numeric( $id ) &&
		get_post_type( $id ) == 'template' &&
		get_post_meta($id, 'mfn_template_type', true) &&
		get_post_meta($id, 'mfn_template_type', true) == $type &&
		(
			get_post_status( $id ) == 'publish' ||
			(!empty($_GET['visual']) && $_GET['visual'] == 'iframe' )
		)
	) {
		return $id;
	}

	return false;
}

/**
 * WPML ajax support for menu switcher [exclude = include]
 * */

if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
	add_filter( 'wpml_ls_exclude_in_menu', function( $isExcluded ) {
	    if ( isset( $_POST['action'] ) && 'mfnrerendersection' === $_POST['action'] ) return false;
	    return $isExcluded;
	} );
}

/**
 * Theme support
 */

if( ! mfn_opts_get('google-font-mode') ){
	add_editor_style(array('css/editor-styles.min.css','https://fonts.googleapis.com/css?family=Poppins'));
}

add_theme_support( 'automatic-feed-links' );
add_theme_support( 'custom-logo', array('width'=> 145, 'height' => 35, 'flex-height' => true, 'flex-width' => true) );
add_theme_support( 'editor-styles' );
add_theme_support( 'post-formats', array('image', 'video', 'quote', 'link') );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'title-tag' );

/**
 * BeBuilder editor in CPT
**/

add_filter('bebuilder_post_types','mfn_additional_cpt_bebuilder');

function mfn_additional_cpt_bebuilder($post_types) {

	if( !is_admin() ) return;

	$cpts = Mfn_Builder_Helper::get_post_types();

	foreach( $cpts as $cpt ) {
		if( !in_array($cpt->name, $post_types) ) {
			$post_types[] = $cpt->name;
		}
	}

	return $post_types;

}

/**
 *
 * MFN Advanced filters
 *
 * */

add_filter( 'pre_get_posts', 'mfn_advanced_filters' );

function mfn_advanced_filters( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( $query->is_home() || $query->is_archive() || $query->is_tax() || $query->is_category() || $query->is_tag() || $query->is_post_type_archive() ) ) {
		return;
	}

	$tax_query  = (array) $query->get( 'tax_query' );
	$meta_query = (array) $query->get( 'meta_query' );

	$url_filters = mfn_get_filters_from_url();

	if ( ! empty( $url_filters['tax_query'] ) ) {
		$tax_query = array_merge( $tax_query, $url_filters['tax_query'] );
		$query->set( 'tax_query', $tax_query );
	}

	if ( ! empty( $url_filters['meta_query'] ) ) {
		$meta_query = array_merge( $meta_query, $url_filters['meta_query'] );
		$query->set( 'meta_query', $meta_query );
	}

	$raw_search = isset( $_GET['mfn-search'] ) ? trim( wp_unslash( $_GET['mfn-search'] ) ) : '';

	if ( '' !== $raw_search ) {
		$query->set( 'mfn_custom_search', sanitize_text_field( $raw_search ) );
	}

}

add_filter( 'posts_where', 'mfn_advanced_filters_search_where', 10, 2 );

function mfn_advanced_filters_search_where( $where, $query ) {

	$raw = $query->get( 'mfn_custom_search' );

	if ( empty( $raw ) ) {
		return $where;
	}

	global $wpdb;

	$like = '%' . $wpdb->esc_like( $raw ) . '%';

	$where .= $wpdb->prepare(
		" AND ( {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_name LIKE %s )",
		$like,
		$like
	);

	return $where;

}

function mfn_get_filters_from_url() {

	$return = array( 'tax_query' => array(), 'meta_query' => array() );

	if( empty($_GET['mfn-advanced-filters-logic']) ) return $return;

	//$logic = !empty($_GET['mfn-advanced-filters-logic']) && $_GET['mfn-advanced-filters-logic'] == 'OR' ? "OR" : "AND"; // more problems than benefits
	$logic = "AND";

	foreach($_GET as $g=>$get) {

		if( substr($g, 0, 4) === 'tax_' ) {

			$g = substr($g, 4);

			if( is_array($get) && !empty($get) && !empty($get[0]) ) {

				$return['tax_query'][] = array(
		        'taxonomy' => $g,
		        'field'    => 'term_id',
		        'terms'    => $get,
		        'operator' => 'IN'
		    );

			}elseif( !is_array($get) && !empty($get) && is_numeric($get) ) {

				$helper = array();
				$helper[] = $get;
				$return['tax_query'][] = array(
		        'taxonomy' => $g,
		        'field'    => 'term_id',
		        'terms'    => $helper,
		        'operator' => 'IN'
		    );

			}elseif( !is_array($get) && !empty($get) && !is_numeric($get) ) {

				$termsq = get_terms( array(
				  'taxonomy'   => $g,
				  'hide_empty' => false,
				  'search'     => $get,
				) );

				$term_ids = wp_list_pluck( $termsq, 'term_id' );

				$return['tax_query'][] = array(
		        'taxonomy' => $g,
		        'field'    => 'term_id',
		        'terms'    => $term_ids,
		        'operator' => 'IN'
		    );
			}

		}else if( substr($g, 0, 9) === 'postmeta_' ) {

			$g = substr($g, 9);

			if( substr($g, 0, 13) === 'filter_logic_' ) continue;

			if( is_array($get) && !empty($get) ) {

				$compare_helper = 'IN';

				if( !empty( $_GET['postmeta_filter_logic_'.$g] ) ) {
					$compare_helper = $_GET['postmeta_filter_logic_'.$g];

					if( $compare_helper == 'gt' ) $compare_helper = '>=';
					if( $compare_helper == 'lt' ) $compare_helper = '<=';
				}

				if( $compare_helper == 'IN' ) {

					$return['meta_query']['relation'] = 'OR';

					foreach ($get as $ge) {
					 	$return['meta_query'][] = [
					    'key'     => $g,
					    'value'   => sanitize_text_field($ge),
					    'compare' => 'LIKE',
					  ];
					}

				}else{

					$q_arr = [
		        'key' => $g,
		        'value'    => $get,
		        'compare' => $compare_helper
			    ];

			    if( $compare_helper == 'BETWEEN' ) {
			    	$q_arr['type'] = 'NUMERIC';
			    }

			    $return['meta_query'][] = $q_arr;

				}

			}else if(!is_array($get) && !empty($get)) {

				$compare_helper = 'LIKE';

				$was_min_max = false;

				if( preg_match('/_min$/i', $g) ) {
					$g = preg_replace('/_min$/', '', $g);
					if(empty($get) ) $get = 0;

					$was_min_max = 'min';
				}

				if( preg_match('/_max$/i', $g) ) {
					$g = preg_replace('/_max$/', '', $g);
					if(empty($get) ) $get = 99999;

					$was_min_max = 'max';
				}

				if( !empty( $_GET['postmeta_filter_logic_'.$g] ) ) {

					$compare_helper = $_GET['postmeta_filter_logic_'.$g];

					if( $compare_helper == 'gt' ) $compare_helper = '>=';
					if( $compare_helper == 'lt' ) $compare_helper = '<=';
				}

				if( $compare_helper == 'BETWEEN' ) {

					if( $was_min_max == 'min' ){
						$compare_helper = '>=';
					}else{
						$compare_helper = '<=';
					}

				}

				$q_arr = [
		        'key' => $g,
		        'value'   => $get,
		        'compare' => $compare_helper
		    ];

		    if( is_numeric($get) ) {
		    	$q_arr['type'] = 'NUMERIC';
		    	$q_arr['value'] = (float) $get;
		    }

		    $return['meta_query'][] = $q_arr;

			}

		}

	}

	return $return;

}

function mfn_get_slider_price_from_lookup() {
    global $wpdb;

    // nazwa tabeli z prefiksem
    $table = $wpdb->prefix . 'wc_product_meta_lookup';

    // tylko opublikowane produkty (łączymy z posts, żeby wykluczyć szkice itp.)
    $sql = "
        SELECT MIN(l.min_price) AS min_price, MAX(l.max_price) AS max_price
        FROM {$table} l
        INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id
        WHERE p.post_type = 'product'
          AND p.post_status = 'publish'
    ";

    $row = $wpdb->get_row( $sql );
    if ( ! $row ) return [ 'min' => 0, 'max' => 0 ];

    // zaokrąglij do liczby „sklepowej”
    $dec = wc_get_price_decimals();
    return [
        'min' => round( (float) $row->min_price, $dec ),
        'max' => round( (float) $row->max_price, $dec ),
    ];
}



/*function mfn_get_available_attr_terms_in_current_view( array $p_ids, array $excl_ids, string $attr_tax ): array {

    $terms_param = array(
    	'taxonomy'   => $attr_tax,
      'hide_empty' => true,
      'object_ids' => $p_ids
    );

    if( !empty($excl_ids) ){
    	$terms_param['exclude'] = $excl_ids;
    }

    $terms = get_terms($terms_param);

    return is_wp_error( $terms ) ? [] : $terms;
}*/
