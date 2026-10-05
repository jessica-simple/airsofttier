<?php
/**
 * Header functions
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Html classes
 */

if (! function_exists('mfn_html_classes')) {
	function mfn_html_classes()
	{
		$classes = [];

		// ios check | for background position fixed in responsive.css

		if( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ){
			$user_agent = $_SERVER['HTTP_USER_AGENT']; // context is safe and necessary
			if (strpos($user_agent, 'iPad;') || strpos($user_agent, 'iPhone;')) {
				$classes[] = 'ios';
			}
		}

		// demo

		if( ! empty( $_GET['mfn-html-class'] ) ){
			$classes[] = $_GET['mfn-html-class'];
		}

		// preloader

		if( mfn_opts_get('preloader') ){
			$classes[] = 'preloading';
		}

		// return

		return implode(' ', $classes);
	}
}

/**
 * Page title
 */

if (! function_exists('mfn_title')) {
	function mfn_title($title) {

		$mfn_ID = mfn_ID('perfect_match');
		$shop_id = false;

		if( function_exists('is_woocommerce') ) $shop_id = wc_get_page_id('shop');

		if( $shop_id && function_exists('is_shop') && is_shop() ) {
			$title = get_the_title($shop_id) .' &#8211 '. get_bloginfo('name');
		}

		if ( mfn_opts_get('mfn-seo') && mfn_ID() ) {
			if ($seo_title = trim(get_post_meta($mfn_ID, 'mfn-meta-seo-title', true))) {
				$title = esc_html($seo_title);
			}
		}

		if( $shop_id && function_exists('is_woocommerce') && function_exists('is_shop') && is_shop() ) {
			if ($seo_title = trim(get_post_meta($shop_id, 'mfn-meta-seo-title', true))) {
				$title = esc_html($seo_title);
			}
		}

		return $title;
	}
}

add_filter('pre_get_document_title', 'mfn_title');

/**
 * Serach page title fix for custom search query mfn_search()
 */

if (! function_exists('mfn_search_title')) {
	function mfn_search_title( $title ) {

		if( ! empty( $_GET['s'] ) ){
			$title['title'] = str_replace( '&#8220;&#8221;', '&#8220;'. $_GET['s'] .'&#8221;', $title['title'] );
		}

		return $title;
	}
}
add_filter( 'document_title_parts', 'mfn_search_title' );

/**
 * Built-in SEO Fields
 */

if (! function_exists('mfn_seo')) {
	function mfn_seo() {

		$mfn_ID = mfn_ID('perfect_match');

		if( mfn_opts_get('mfn-seo') ) {

			if( $mfn_ID ){

				$shop_id = false;
				if( function_exists('is_woocommerce') ) $shop_id = wc_get_page_id('shop');

				// keywords

				if( $shop_id && function_exists('is_woocommerce') && function_exists('is_shop') && is_shop() && !empty( get_post_meta($shop_id, 'mfn-meta-seo-keywords', true) ) ) {
					echo '<meta name="keywords" content="'. esc_attr( get_post_meta($shop_id, 'mfn-meta-seo-keywords', true)) .'"/>'."\n";
				}elseif (get_post_meta($mfn_ID, 'mfn-meta-seo-keywords', true)) {
					echo '<meta name="keywords" content="'. esc_attr(get_post_meta($mfn_ID, 'mfn-meta-seo-keywords', true)) .'"/>'."\n";
				} elseif (mfn_opts_get('meta-keywords')) {
					echo '<meta name="keywords" content="'. esc_attr(mfn_opts_get('meta-keywords')) .'"/>'."\n";
				}

				// description

				$description = false;

				if( $shop_id && function_exists('is_woocommerce') && function_exists('is_shop') && is_shop() && !empty( get_post_meta($shop_id, 'mfn-meta-seo-description', true) ) ) {
					$description = get_post_meta($shop_id, 'mfn-meta-seo-description', true);
				}elseif (get_post_meta($mfn_ID, 'mfn-meta-seo-description', true)) {
					$description = get_post_meta($mfn_ID, 'mfn-meta-seo-description', true);
				} elseif (mfn_opts_get('meta-description')) {
					$description = mfn_opts_get('meta-description');
				}

				if( $description ){
					echo '<meta name="description" content="'. esc_attr( $description ) .'"/>'."\n";
				}

				// og:image

				if( $shop_id && function_exists('is_woocommerce') && function_exists('is_shop') && is_shop() && !empty( get_post_meta($shop_id, 'mfn-meta-seo-og-image', true) ) ) {
					echo '<meta property="og:image" content="'. esc_attr(mfn_skip_hash(get_post_meta($shop_id, 'mfn-meta-seo-og-image', true))) .'"/>'."\n";
				}elseif (get_post_meta($mfn_ID, 'mfn-meta-seo-og-image', true)) {
					echo '<meta property="og:image" content="'. esc_attr(mfn_skip_hash(get_post_meta($mfn_ID, 'mfn-meta-seo-og-image', true))) .'"/>'."\n";
				} elseif (mfn_opts_get('mfn-seo-og-image')) {
					echo '<meta property="og:image" content="'. esc_attr(mfn_skip_hash(mfn_opts_get('mfn-seo-og-image'))) .'"/>'."\n";
				} elseif( is_single($mfn_ID) ) {
					if (has_post_thumbnail($mfn_ID)) {
						echo '<meta property="og:image" content="'. esc_attr(get_the_post_thumbnail_url($mfn_ID,'full')) .'"/>'."\n";
					}
				}

				// og:url, og:type, og:title, og:description, fb:app_id

				if( is_single($mfn_ID) ) {

					$excerpt = get_the_excerpt($mfn_ID);
					if( ! $excerpt && $description ) {
						$excerpt = $description;
					}

					echo '<meta property="og:url" content="'. esc_url(mfn_current_URL()) .'"/>'."\n";
					echo '<meta property="og:type" content="article"/>'."\n";
					echo '<meta property="og:title" content="'. esc_html(get_the_title($mfn_ID)) .'"/>'."\n";
					echo '<meta property="og:description" content="'. wp_strip_all_tags($excerpt) .'"/>'."\n";

					$fb_app_id = mfn_opts_get('seo-fb-app-id');
					if( $fb_app_id ){
						echo '<meta property="fb:app_id" content="'. esc_attr($fb_app_id) .'"/>'."\n";
					}

				}

			}

			// hreflang | only if WMPL is not active

			if (! function_exists('icl_object_id')) {
				$format_locale = str_replace('_', '-', get_locale());
				echo '<link rel="alternate" hreflang="'. esc_attr($format_locale) .'" href="'. esc_url(mfn_current_URL()) .'"/>'."\n";
			}

		}

		// google analytics

		if (mfn_opts_get('google-analytics')) {
			echo mfn_opts_get('google-analytics');
		}

		// facebook pixel

		if (mfn_opts_get('facebook-pixel')) {
			echo "\n";
			echo mfn_opts_get('facebook-pixel');
		}

		// Google Tag Manager (gtag) - JS snippet

		if ( mfn_opts_get('google-gtag-js') && empty(mfn_opts_get('google-gtag-id')) ) {
			echo "\n";
			echo mfn_opts_get('google-gtag-js');
		}

		// Performance | Preload

		if ( ! empty( mfn_opts_get('performance-preload') ) ) {
			echo "\n";
			echo mfn_opts_get('performance-preload');
			echo "\n";
		}

	}
}
add_action('wp_head', 'mfn_seo', 1);

/**
 * Header meta tags
 */

if (! function_exists('mfn_meta')) {
	function mfn_meta()
	{
		// disable auto-formatting for telephone numbers

		echo '<meta name="format-detection" content="telephone=no">'."\n";

		// viewport

		if (mfn_opts_get('responsive')) {
			if (mfn_opts_get('responsive-zoom')) {
				echo '<meta name="viewport" content="width=device-width, initial-scale=1" />'."\n";
			} else {
				echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />'."\n";
			}
		}

		// favicon

		if( mfn_opts_get('favicon-img' ) || ( ! has_site_icon() ) ){
			$favicon = mfn_opts_get('favicon-img', get_theme_file_uri('/images/favicon.ico'));
			$favicon_parts = explode('#', (string) $favicon, 2);
			$favicon_url   = $favicon_parts[0];
			echo '<link rel="shortcut icon" href="' . esc_url($favicon_url) . '" type="image/x-icon" />' . "\n";
		}

		// apple touch icon

		if ( mfn_opts_get('apple-touch-icon') ) {
			$icon = explode('#', (string) mfn_opts_get('apple-touch-icon'), 2)[0];
			if ( $icon !== '' ) {
				echo '<link rel="apple-touch-icon" href="' . esc_url($icon) . '" />' . "\n";
			}
		}

		// safari bar background color

		if ( mfn_opts_get('safari-bar-light-scheme') ) {
			echo '<meta name="theme-color" content="'. mfn_opts_get('safari-bar-light-scheme') .'" media="(prefers-color-scheme: light)">'."\n";
		}

		if ( mfn_opts_get('safari-bar-dark-scheme') ) {
			echo '<meta name="theme-color" content="'. mfn_opts_get('safari-bar-dark-scheme') .'" media="(prefers-color-scheme: dark)">'."\n";
		}

	}
}
add_action('wp_head', 'mfn_meta', 1);

/**
 * Built-in SEO Fields | body tag
 */
if (! function_exists('mfn_seo_body')) {
	function mfn_seo_body(){

		// Google Tag Manager (gtag) - HTML iframe
		if ( mfn_opts_get('google-gtag-html') && empty(mfn_opts_get('google-gtag-id')) ) {
			echo "\n";
			echo mfn_opts_get('google-gtag-html');
		}

	}
}
add_action('mfn_hook_top', 'mfn_seo_body', 0);

/**
 * Google Remarketing Code
 */

if (! function_exists('mfn_google_remarketing')) {
	function mfn_google_remarketing()
	{
		// google remarketing
		if (mfn_opts_get('google-remarketing')) {
			echo mfn_opts_get('google-remarketing');
		}
	}
}
add_action('wp_footer', 'mfn_google_remarketing', 100);

/**
 * Fonts selected in Theme Options & BeBuilder
 */

if (! function_exists('mfn_fonts_selected')) {
	function mfn_fonts_selected( $builder_fonts = false )
	{
		$fonts = array(
			'content' => mfn_opts_get('font-content', 'Poppins'),
			'lead' => mfn_opts_get('font-lead', mfn_opts_get('font-content', 'Poppins')),
			'menu' => mfn_opts_get('font-menu', 'Poppins'),
			'title' => mfn_opts_get('font-title', 'Poppins'),
			'headings' => mfn_opts_get('font-headings', 'Poppins'),
			'headingsSmall' => mfn_opts_get('font-headings-small', 'Poppins'),
			'blockquote' => mfn_opts_get('font-blockquote', 'Poppins'),
			'decorative' => mfn_opts_get('font-decorative', 'Poppins'),
		);

		if( $builder_fonts ){
			$be_builder_fonts = Mfn_Builder_Helper::get_bebuilder_fonts();
			foreach ($be_builder_fonts as $font_bb_key => $font_bb_value) {
				$fonts['bebuilder_'.$font_bb_key] = $font_bb_value;
			}
		}

		return $fonts;
	}
}

/**
 * Disable the emoji's
 */

if ( ! function_exists('mfn_disable_emojis') ) {
	function mfn_disable_emojis()
	{
		$performance_wp_disable = mfn_opts_get('performance-wp-disable');

		if( ! empty( $performance_wp_disable[ 'emoji' ] ) ){
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
		}

	}
}
add_action( 'init', 'mfn_disable_emojis' );

/**
 * Move jQuery into footer
 */

if ( ! function_exists('mfn_move_jquery_into_footer') ) {
	function mfn_move_jquery_into_footer( $wp_scripts ) {

		if( is_admin() || 'footer' !== mfn_opts_get('jquery-location') ) {
			 return;
		}

		$wp_scripts->add_data( 'jquery', 'group', 1 );
		$wp_scripts->add_data( 'jquery-core', 'group', 1 );
		$wp_scripts->add_data( 'jquery-migrate', 'group', 1 );
	}
}
add_action( 'wp_default_scripts', 'mfn_move_jquery_into_footer' );

/**
 * Styles
 */

if (! function_exists('mfn_styles')) {
	function mfn_styles()
	{
		global $mfn_global;

		$min = '';

		$performance_assets_disable = mfn_opts_get('performance-assets-disable');
		$performance_minify_css = mfn_opts_get('minify-css','');

		if( $performance_minify_css ){
			$min = '.min';
		}

		// wp_enqueue_style

		/*if( mfn_is_blocks() ){
			wp_enqueue_style('mfn-icons', get_theme_file_uri('/fonts/mfn/icons'. $min .'.css'), false, MFN_THEME_VERSION);
		} else {
			wp_enqueue_style('mfn-be', get_theme_file_uri('/css/be'. $min .'.css'), false, MFN_THEME_VERSION);
		}*/

		if( mfn_is_blocks() ){
			wp_enqueue_style('mfn-icons', get_theme_file_uri('/fonts/mfn/icons'. $min .'.css'), false, MFN_THEME_VERSION);
		}

		wp_enqueue_style('mfn-be', get_theme_file_uri('/css/be'. $min .'.css'), false, MFN_THEME_VERSION);

		// plugins

		if ( ! isset( $performance_assets_disable[ 'entrance-animations' ] ) ) {
			wp_enqueue_style('mfn-animations', get_theme_file_uri('/assets/animations/animations.min.css'), false, MFN_THEME_VERSION);
		}

		if ( ! isset( $performance_assets_disable[ 'font-awesome' ] ) || !empty($_GET['visual']) ) {
			wp_enqueue_style('mfn-font-awesome', get_theme_file_uri('/fonts/fontawesome/fontawesome'. $min .'.css'), false, MFN_THEME_VERSION);
		}

		// responsive

		if ( mfn_opts_get( 'responsive' ) ) {
			wp_enqueue_style('mfn-responsive', get_theme_file_uri('/css/responsive'. $min .'.css'), false, MFN_THEME_VERSION);
		} else {
			wp_enqueue_style('mfn-responsive-off', get_theme_file_uri('/css/responsive-off'. $min .'.css'), false, MFN_THEME_VERSION);
		}

		// setup wizard preview

		if( isset($_GET['mfn-setup-preview']) ){
			wp_enqueue_style('mfn-setup-wizard', get_theme_file_uri('/functions/admin/setup/assets/iframe.css'), false, MFN_THEME_VERSION);
		}

		// custom Theme Options styles

		if ( ! mfn_opts_get( 'static-css' ) ) {

			// predefined skins

			if ($layoutID = mfn_layout_ID()) {
				$skin = get_post_meta($layoutID, 'mfn-post-skin', true);
			} else {
				$skin = mfn_opts_get('skin', 'custom');
			}

			if( ( 'custom' != $skin ) && ( 'one' != $skin ) ){
				wp_enqueue_style('mfn-skin-'. $skin, get_theme_file_uri('/css/skins/'. $skin .'/style'. $min .'.css'), false, MFN_THEME_VERSION);
			}

		}

		// Google Fonts

		$google_fonts_mode = mfn_opts_get('google-font-mode');

		if( 'disabled' == $google_fonts_mode ){
			return false;
		}

		$fonts = mfn_fonts_selected();
		$google_fonts = mfn_fonts('all');
		$google_array = array();
		$path_fonts = mfn_uploads_dir('baseurl', 'fonts');

		/*$headerID = $mfn_global['header'] ?? false;
		$footerID = $mfn_global['footer'] ?? false;
		$blogID = $mfn_global['archive_template'] ?? false;*/
		$classes_fonts = !empty(get_option('be_classes_fonts')) ? json_decode(get_option('be_classes_fonts'), true) : false;

		global $wpdb;
		$all_used_fonts = $wpdb->get_results( "SELECT m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on p.ID = m.post_id WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn-page-fonts'" );

		if( isset($all_used_fonts) && is_iterable($all_used_fonts) ){
			foreach($all_used_fonts as $u=>$usf){
				if( !empty($usf->meta_value) ) $fonts = array_merge( $fonts, json_decode($usf->meta_value) );
			}
		}

		// luk - items fonts

		if( ! empty($_GET['mfn-preview']) || ! empty($_GET['preview']) ) {
			$page_fonts = get_post_meta(mfn_ID(), 'mfn-builder-preview-fonts', true);
		} else {
			$page_fonts = get_post_meta(mfn_ID(), 'mfn-page-fonts', true);
		}

		if( !empty($page_fonts) ){
			$fonts = array_merge($fonts, json_decode($page_fonts));
		}

		// classes fonts

		if( !empty($classes_fonts) ) {
			$fonts = array_merge($fonts, $classes_fonts);
		}

		// style & weight

		if ($weight = mfn_opts_get('font-weight')) {
			$weight = ':'. implode(',', $weight);
		}

		if( is_array($fonts) ){
			foreach ($fonts as $font) {
				if (in_array($font, $google_fonts)) {
					$font_slug = str_replace(' ', '+', $font);
					$google_array[$font_slug] = $font_slug . $weight;
				}
			}
		}

		if ( $google_array ) {

			if ( 'local' === $google_fonts_mode ) {
				wp_enqueue_style('mfn-local-fonts', wp_normalize_path($path_fonts.'/mfn-local-fonts.css'), '', true);
			} else  {
				$google_array = implode('|', $google_array);
				wp_enqueue_style('mfn-fonts', 'https://fonts.googleapis.com/css?family='. $google_array .'&display=swap');
			}

		}

		// button custom | font

		if( ! $google_fonts_mode ){

			$button_font_family = mfn_opts_get( 'button-font-family' );
			$button_font = shortcode_atts( array(
				'weight_style' => 400,
			), mfn_opts_get( 'button-font' ));

			if ( in_array( $button_font_family, $google_fonts ) ) {
				$button_font_family = str_replace( ' ', '+', $button_font_family );
				$button_font_weight = str_replace( 'italic', '', $button_font['weight_style'] );
				wp_enqueue_style('mfn-font-button', 'https://fonts.googleapis.com/css?family='. $button_font_family . ':400,' . $button_font['weight_style'] .'&display=swap');
			}

		}

	}
}
if( 'footer' == mfn_opts_get('css-location') ){
	add_action('mfn_wp_footer_before', 'mfn_styles', 11);
} else {
	add_action('wp_enqueue_scripts', 'mfn_styles');
}

/**
 * Disable styles | Performance
 */

if (! function_exists('mfn_styles_disable')) {
	function mfn_styles_disable()
	{
		$performance_wp_disable = mfn_opts_get('performance-wp-disable');

		// block library styles

		if( ! empty( $performance_wp_disable[ 'wp-block-library' ] ) ){
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'wc-blocks-vendors-style' );
		}

		// dashicons

		if( ! empty( $performance_wp_disable[ 'dashicons' ] ) ){
			if ( ! is_user_logged_in() ) {
        wp_dequeue_style('dashicons');
    	}
		}

		// WooCommerce assets

    if( mfn_opts_get( 'woocommerce-assets' ) && function_exists( 'is_woocommerce' ) ){

			// get list of additional IDs

			$woocommerce_pages = mfn_opts_get( 'woocommerce-assets-id' ) ?? '';
			$woocommerce_pages = str_replace( ' ', '', $woocommerce_pages );

			$ids = [];
			if( $woocommerce_pages ){
				$ids = explode(',', $woocommerce_pages);
			}

			// remove assets

      if( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() && ! in_array( mfn_ID(), $ids ) ) {

        ## Dequeue WooCommerce styles
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-smallscreen' );

        ## Dequeue WooCommerce scripts
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );

        wp_deregister_script( 'jquery-blockui' );
        wp_dequeue_script( 'jquery-blockui' );

				// Notice: Header Cart requires 'wc-cart-fragments, js-cookie'
      }
    }

	}
}
add_action('wp_enqueue_scripts', 'mfn_styles_disable', 99);

/*
 * RTL Tester
 */

if (! function_exists('mfn_rtl_tester')) {
	function mfn_rtl_tester() {
		global $wp_locale, $wp_styles;

		$wp_locale->text_direction = 'rtl';
		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
		}
		$wp_styles->text_direction = 'rtl';

	}
}

if( isset( $_GET['mfn-rtl'] ) ){
	add_action( 'init', 'mfn_rtl_tester' );
}

/*
 * Styles | Static
 */

if (! function_exists('mfn_styles_static')) {
	function mfn_styles_static()
	{
		if( mfn_opts_get( 'static-css' ) ){

			$upload_dir = wp_upload_dir();
			$url = $upload_dir['baseurl'] .'/betheme/css/static.css';
			wp_enqueue_style('mfn-static', $url, false, MFN_THEME_VERSION);

		}
	}
}
if( 'footer' == mfn_opts_get('css-location') ){
	add_action('mfn_wp_footer_before', 'mfn_styles_static', 11);
} else {
	add_action('wp_enqueue_scripts', 'mfn_styles_static', 11);
}

/**
 * Styles | Inline HTML styles
 */

if (! function_exists('mfn_styles_html')) {
	function mfn_styles_html() {

		$css = '';

		// form submit buttons hidden
		$id_dd = false;
		global $mfn_global;

		$css .= 'form input.display-none{display:none!important}';

		if( (is_singular() || (function_exists('is_woocommerce') && is_product()) ) ) {


			if( !empty($mfn_global['singular_template']) ){
				$id_dd = $mfn_global['singular_template'];
			}else{
				$id_dd = get_the_ID();
			}

			if( has_post_thumbnail( get_the_ID() ) ) $css .= 'body{--mfn-featured-image: url('.get_the_post_thumbnail_url(get_the_ID(), 'full').');}';
			if( !empty( get_post_meta(get_the_ID(), 'mfn-post-header-bg', true) ) ) $css .= 'body{--mfn-header-intro-image: url('.get_post_meta(get_the_ID(), 'mfn-post-header-bg', true).');}';
			if( !empty( get_post_meta(get_the_ID(), 'mfn-post-subheader-image', true) ) ) $css .= 'body{--mfn-subheader-image: url('.get_post_meta(get_the_ID(), 'mfn-post-subheader-image', true).');}';

		}else if( function_exists('is_woocommerce') && is_archive() ){
			$qo = get_queried_object();
			if( is_shop() && wc_get_page_id('shop')){
				$css .= 'body{--mfn-featured-image: url('.get_the_post_thumbnail_url(wc_get_page_id('shop'), 'full').');}';
				$id_dd = wc_get_page_id('shop');
			}else if( is_product_category() && get_term_meta( $qo->term_id, 'thumbnail_id', true ) ){
				$css .= 'body{--mfn-featured-image: url('.wp_get_attachment_url( get_term_meta( $qo->term_id, 'thumbnail_id', true ), 'full' ).');}';
			}
		}else if( is_home() && get_option( 'page_for_posts' ) ){
			$css .= 'body{--mfn-featured-image: url('.get_the_post_thumbnail_url(get_option( 'page_for_posts' ), 'full').');}';
		}else if( is_archive() ){
			$qo = get_queried_object();
			if( !empty($qo->term_id) && get_term_meta( $qo->term_id, 'thumbnail_id', true ) ){
				$css .= 'body{--mfn-featured-image: url('.wp_get_attachment_url( get_term_meta( $qo->term_id, 'thumbnail_id', true ), 'full' ).');}';
			}

		}


		if( $id_dd ) {
			$dynamic_styles_get = get_post_meta($id_dd, 'mfn_dynamic_styles', true);

			if( !empty($dynamic_styles_get) ) {
				$dynamic_styles = json_decode($dynamic_styles_get, true);

				if( !empty($dynamic_styles) ) {
					foreach($dynamic_styles as $d=>$ds) {
						$parse_dd = be_dynamic_data($ds);
						if( !empty($parse_dd) ) {
							if( is_numeric($parse_dd) ) $parse_dd = wp_get_attachment_image_url($parse_dd, 'full');
						}
						$css .= 'body{'.$d.':url('.$parse_dd.');}';
					}
				}

			}
		}

		if( !has_post_thumbnail( get_the_ID() ) && !empty($_GET['visual']) && $_GET['visual'] == 'iframe' ) {
			$css .= 'body{--mfn-featured-image: url('. get_template_directory_uri('/').'/muffin-options/svg/placeholders/featured-image.png' .');}';
		}

		return $css;
	}
}

/**
 * Styles | Inline wp_head dynamic styles
 */

if (! function_exists('mfn_styles_inline')) {
	function mfn_styles_inline()
	{
		wp_register_style( 'mfn-dynamic', false );
		wp_enqueue_style( 'mfn-dynamic' );

		wp_register_style( 'mfn-custom', false );
		wp_enqueue_style( 'mfn-custom' );

		// custom fonts
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_custom_font() ?? '' );

		// backgrounds
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_background() ?? '' );

		// dynamic .php styles
		if( ! mfn_opts_get('static-css')){
			wp_add_inline_style( 'mfn-dynamic', mfn_styles_dynamic() ?? '' );
		}

		// html inline styles
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_html() ?? '' );

		// local builder styles
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_local() ?? '' );

		// local header builder styles
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_header() ?? '' );

		// local sidemenu builder styles
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_sidemenu() ?? '' );

		// local template builder styles
		wp_add_inline_style( 'mfn-dynamic', mfn_styles_templates() ?? '' );

		// custom CSS
		if( ! mfn_opts_get('static-css')){
			wp_add_inline_style( 'mfn-custom', mfn_styles_custom() ?? '' );
		}
		wp_add_inline_style( 'mfn-custom', mfn_styles_page_custom() ?? '' );

	}
}

if( 'footer' == mfn_opts_get('css-location') ){
	add_action('mfn_wp_footer_before', 'mfn_styles_inline', 100);
} else {
	add_action('wp_enqueue_scripts', 'mfn_styles_inline', 100);
}

/**
 * Styles | sidemenu Builder local
 */

function mfn_styles_sidemenu() {

	if( 'inline' == mfn_opts_get('local-styles-location') && empty( $_GET['visual'] ) ){
		global $mfn_global;

		$header_tmp_id = $mfn_global['sidemenu'] ?? false;


		if( $header_tmp_id ){
			$mfn_builder_header = new Mfn_Builder_Front( $header_tmp_id );
			$path_header = $mfn_builder_header->enqueue_local_style( false );

			ob_start();

			include_once $path_header;
			$css = "/* Local Sidemenu Style ". $header_tmp_id ." */\n";
			$css .= ob_get_clean();

			return $css;
		}

	}

	return false;
}

/**
 * Styles | Header Builder local
 */

function mfn_styles_header(){

	global $mfn_global;

	$header_tmp_id = $mfn_global['header'] ?? false;

	if( $header_tmp_id ){

		// skip header preview on another pages preview
		$skip_preview = empty($_GET['mfn-header-template']) ? true : false;

		$mfn_builder_header = new Mfn_Builder_Front( $header_tmp_id );
		$path_header = $mfn_builder_header->enqueue_local_style( false, $skip_preview );

		ob_start();

		include_once $path_header;

		// $css = "/* Local Header Style ". $header_tmp_id . " - " .get_the_ID(). " - " .$path_header. " */\n";
		$css = "/* Local Header Style ". $header_tmp_id ." */\n";
		$css .= ob_get_clean();

		return $css;
	}

	return false;
}

/**
 * Styles | Builder local
 */

function mfn_styles_local(){

 	if( 'inline' == mfn_opts_get('local-styles-location') && empty( $_GET['visual'] ) ){

 		$id = get_the_ID();

 		// custom 404 page

 		if( is_404() && mfn_opts_get('error404-page') ){
 			$id = mfn_opts_get('error404-page');
 		}

		// custom under construction

		if( mfn_opts_get('construction') && mfn_opts_get('construction-page') ){
			if( ! is_user_logged_in() && ! is_admin() ){
				$id = mfn_opts_get('construction-page');
			}
		}

		// blog page

		if( !is_front_page() && is_home() ){
			$id =  get_option( 'page_for_posts' );
		}

 		$mfn_builder = new Mfn_Builder_Front($id);
 		$path = $mfn_builder->enqueue_local_style( false );

 		if( ! $path ){
 			return false;
 		}

 		ob_start();

 		include_once $path;

 		$css = "/* Local Page Style ". esc_attr($id) ." */\n";
 		$css .= ob_get_clean();

 		return $css;

 	}

 	return false;

}

/**
 * Styles | Templates local
 */

function mfn_styles_templates() {

 	if( 'inline' == mfn_opts_get('local-styles-location') && empty( $_GET['visual'] ) ) {

 		global $mfn_global;

 		$id = false;

 		if( is_singular() ) {

 			if( function_exists('is_woocommerce') && is_cart() && !empty($mfn_global['cart']) && get_post_status($mfn_global['cart']) == 'publish'  ) {
 				$id = $mfn_global['cart'];
 			}else if( function_exists('is_woocommerce') && is_checkout() && empty( is_wc_endpoint_url('order-received') ) && !empty( $mfn_global['checkout'] ) && get_post_status($mfn_global['checkout']) == 'publish' ) {
 				$id = $mfn_global['checkout'];
 			}else if( function_exists('is_woocommerce') && is_checkout() && !empty( is_wc_endpoint_url('order-received') ) && !empty($mfn_global['thank_you']) && get_post_status($mfn_global['thank_you']) == 'publish' ) {
 				$id = $mfn_global['thank_you'];
 			}else if( is_page() && !empty($mfn_global['archive_template']) && ( is_page_template('template-portfolio.php') || mfn_opts_get('portfolio-page') == get_the_ID() || ( function_exists('is_woocommerce') && is_shop() ) || (mfn_opts_get('shop-wishlist-page') && mfn_opts_get('shop-wishlist-page') == get_the_ID()) ) ) {
 				$id = is_array($mfn_global['archive_template']) ? $mfn_global['archive_template'][0] : $mfn_global['archive_template'];
 			}else{
 				$id = $mfn_global['singular_template'];
 			}

 		}else if( is_archive() || is_home() ) {

 			$id = $mfn_global['archive_template'];

 		}else if( is_search() && !( isset($_GET['mfn_livesearch']) && !isset($_GET['searchpage']) ) && empty($_GET['post_type']) && !empty($mfn_global['search']) && get_post_status($mfn_global['search']) == 'publish' ) {
			$id = $mfn_global['search'];
		}

 		if( !$id ) return false;

 		$mfn_builder = new Mfn_Builder_Front($id);
 		$path = $mfn_builder->enqueue_local_style( false );

 		if( ! $path ){
 			return false;
 		}

 		ob_start();

 		include_once $path;

 		$css = "/* Local Template Style ". esc_attr($id) ." */\n";
 		$css .= ob_get_clean();

 		return $css;

 	}

 	return false;

}

/**
 * Styles | Custom Font
 */

if (! function_exists('mfn_styles_custom_font')) {
	function mfn_styles_custom_font() {
		$output = '';
		$fonts = array();

		if( $font1 = mfn_opts_get('font-custom') ) {
			$fonts[$font1] = array(
				'woff' => mfn_opts_get('font-custom-woff'),
				'ttf' => mfn_opts_get('font-custom-ttf'),
			);
		}

		if( $font2 = mfn_opts_get('font-custom2') ){
			$fonts[$font2] = array(
				'woff' => mfn_opts_get('font-custom2-woff'),
				'ttf' => mfn_opts_get('font-custom2-ttf'),
			);
		}

		// custom dynamicaly uploaded font
		// we start from 2, bcuz of fonts 1 and 2 above

		if( $fonts_custom_dynamic = intval(mfn_opts_get('font-custom-fields')) ) {
			$x = $fonts_custom_dynamic + 2; //we start from 2, bcuz of fonts 1 and 2 above

			for( $x; $x > 2; $x-- ) {
				$dynamic_font_name = mfn_opts_get('font-custom'.$x);

				$fonts[$dynamic_font_name] = array(
					'woff' => mfn_opts_get('font-custom'. $x .'-woff'),
					'ttf' => mfn_opts_get('font-custom'. $x .'-ttf')
				);
			}
		}

		foreach( $fonts as $font_k => $font ){
			$output .= '@font-face{';
				$output .= 'font-family:"'. esc_attr($font_k) .'";';
					$output .= 'src:';
					if ($font['woff']) {
						$output .= 'url("'. esc_url($font['woff']) .'") format("woff")';
					}
					if ($font['woff'] && $font['ttf']) {
						$output .= ',';
					}
					if ($font['ttf']) {
						$output .= 'url("'. esc_url($font['ttf']) .'") format("truetype")';
					}
					$output .= ';';
				$output .= 'font-weight:normal;';
				$output .= 'font-style:normal;';
				$output .= 'font-display:swap';
			$output .= '}';
		}

		return $output;
	}
}

/**
 * Styles | Background
 */

if (! function_exists('mfn_styles_background')) {
	function mfn_styles_background()
	{
		$output = $output_ultrawide = '';

		// HTML

		if ($layoutID = mfn_layout_ID()) {
			$htmlB = get_post_meta($layoutID, 'mfn-post-bg', true);
			$htmlP = get_post_meta($layoutID, 'mfn-post-bg-pos', true);
		} else {
			$htmlB = mfn_opts_get('img-page-bg');
			$htmlP = mfn_opts_get('position-page-bg');
		}

		if ($htmlB) {
			$aBg = array();
			$aBg[] = 'background-image:url('. esc_url($htmlB) .')';

			if ($htmlP) {
				$background_attr = explode(';', $htmlP);
				if ($background_attr[0]) {
					$aBg[] = 'background-repeat:'. esc_attr($background_attr[0]);
				}
				if ($background_attr[1]) {
					$aBg[] = 'background-position:'. esc_attr($background_attr[1]);
				}
				if ($background_attr[2]) {
					$aBg[] = 'background-attachment:'. esc_attr($background_attr[2]);
				}
				if ($background_attr[3]) {
					$aBg[] = 'background-size:'. esc_attr($background_attr[3]);
				} elseif (mfn_opts_get('size-page-bg')) {
					if (in_array(mfn_opts_get('size-page-bg'), array( 'cover', 'contain' ))) {
						$aBg[] = 'background-size:'. esc_attr(mfn_opts_get('size-page-bg'));
					} elseif (mfn_opts_get('size-page-bg') == 'cover-ultrawide') {
						$output_ultrawide .= 'html{background-size:cover}';
					}
				}
			}
			$background = implode(';', $aBg);

			$output .= 'html{'. $background. '}';
		}

		// Header wrapper

		$headerB = false;

		if (mfn_opts_get('img-subheader-bg')) {
			$headerB = mfn_opts_get('img-subheader-bg');
		}

		if (mfn_ID() && ! is_search()) {

			if (((mfn_ID() == get_option('page_for_posts')) || (get_post_type(mfn_ID()) == 'page')) && has_post_thumbnail(mfn_ID())) {

				// Pages & Blog Page ---

				$post_thumbnail_ID = get_post_thumbnail_id(mfn_ID());
				if( ! empty($post_thumbnail_ID) && $post_thumbnail_ID > 0 ){
					$headerB = wp_get_attachment_image_src($post_thumbnail_ID, 'full');
					if( ! empty($headerB[0]) ){
						$headerB = $headerB[0];
					}
				}

			} elseif (get_post_meta(mfn_ID(), 'mfn-post-header-bg', true)) {

				// Single Post ---

				$headerB = get_post_meta(mfn_ID(), 'mfn-post-header-bg', true);

			}

		}

		$headerP = mfn_opts_get('img-subheader-attachment');

		if ($headerB) {
			$aBg = array();
			$aBg[] = 'background-image:url('. esc_url($headerB) .')';

			if ($headerP == "fixed") {
				$aBg[] = 'background-attachment:fixed';
			} elseif ($headerP == "parallax") {
				// do nothing
			} elseif ($headerP) {
				$background_attr = explode(';', $headerP);
				if ($background_attr[0]) {
					$aBg[] = 'background-repeat:'. esc_attr($background_attr[0]);
				}
				if ($background_attr[1]) {
					$aBg[] = 'background-position:'. esc_attr($background_attr[1]);
				}
				if ($background_attr[2]) {
					$aBg[] = 'background-attachment:'. esc_attr($background_attr[2]);
				}
				if ($background_attr[3]) {
					$aBg[] = 'background-size:'. esc_attr($background_attr[3]);
				} elseif (mfn_opts_get('size-subheader-bg')) {
					if (in_array(mfn_opts_get('size-subheader-bg'), array( 'cover', 'contain' ))) {
						$aBg[] = 'background-size:'. esc_attr(mfn_opts_get('size-subheader-bg'));
					} elseif (mfn_opts_get('size-subheader-bg') == 'cover-ultrawide') {
						$output_ultrawide .= 'body:not(.template-slider) #Header_wrapper{background-size:cover}';
					}
				}
			}

			$background = implode(';', $aBg);

			$output .= 'body:not(.template-slider) #Header_wrapper{'. $background. '}';
		}

		// Top Bar

		$topbarB = mfn_opts_get('top-bar-bg-img');
		$topbarP = mfn_opts_get('top-bar-bg-position');

		if ($topbarB) {
			$aBg = array();
			$aBg[] = 'background-image:url('. esc_url($topbarB) .')';

			if ($topbarP) {
				$background_attr = explode(';', $topbarP);
				if ($background_attr[0]) {
					$aBg[] = 'background-repeat:'. esc_attr($background_attr[0]);
				}
				if ($background_attr[1]) {
					$aBg[] = 'background-position:'. esc_attr($background_attr[1]);
				}
				if ($background_attr[2]) {
					$aBg[] = 'background-attachment:'. esc_attr($background_attr[2]);
				}
				if ($background_attr[3]) {
					$aBg[] = 'background-size:'. esc_attr($background_attr[3]);
				} elseif (mfn_opts_get('topbar-bg-img-size')) {
					if (in_array(mfn_opts_get('topbar-bg-img-size'), array( 'cover', 'contain' ))) {
						$aBg[] = 'background-size:'. esc_attr(mfn_opts_get('topbar-bg-img-size'));
					}
				}
			}

			$background = implode(';', $aBg);

			$output .= '#Top_bar,#Header_creative{'. $background. '}';
		}

		// Subheader

		if (is_singular() && !empty(get_post_meta(get_the_ID(), 'mfn-post-subheader-image', true))) {
			$subheaderB = get_post_meta(get_the_ID(), 'mfn-post-subheader-image', true);
		}else if (get_post_meta(mfn_ID(), 'mfn-post-subheader-image', true)) {
			$subheaderB = get_post_meta(mfn_ID(), 'mfn-post-subheader-image', true);
		} else {
			$subheaderB = mfn_opts_get('subheader-image');
		}

		$subheaderP = mfn_opts_get('subheader-position');

		if ($subheaderB) {
			$aBg = array();
			$aBg[] = 'background-image:url('. esc_url($subheaderB) .')';

			if ($subheaderP) {
				$background_attr = explode(';', $subheaderP);
				if ($background_attr[0]) {
					$aBg[] = 'background-repeat:'. esc_attr($background_attr[0]);
				}
				if ($background_attr[1]) {
					$aBg[] = 'background-position:'. esc_attr($background_attr[1]);
				}
				if ($background_attr[2]) {
					$aBg[] = 'background-attachment:'. esc_attr($background_attr[2]);
				}
				if ($background_attr[3]) {
					$aBg[] = 'background-size:'. esc_attr($background_attr[3]);
				} elseif (mfn_opts_get('subheader-size')) {
					if (in_array(mfn_opts_get('subheader-size'), array( 'cover', 'contain' ))) {
						$aBg[] = 'background-size:'. esc_attr(mfn_opts_get('subheader-size'));
					} elseif (mfn_opts_get('subheader-size') == 'cover-ultrawide') {
						$output_ultrawide .= '#Subheader{background-size:cover}';
					}
				}
			}

			$background = implode(';', $aBg);

			$output .= '#Subheader{'. $background. '}';
		}

		// Footer

		$footerB = mfn_opts_get('footer-bg-img');
		$footerP = mfn_opts_get('footer-bg-img-position');

		if ($footerB) {
			$aBg = array();
			$aBg[] = 'background-image:url('. esc_url($footerB) .')';

			if ($footerP) {
				$background_attr = explode(';', $footerP);
				if ($background_attr[0]) {
					$aBg[] = 'background-repeat:'. esc_attr($background_attr[0]);
				}
				if ($background_attr[1]) {
					$aBg[] = 'background-position:'. esc_attr($background_attr[1]);
				}
				if ($background_attr[2]) {
					$aBg[] = 'background-attachment:'. esc_attr($background_attr[2]);
				}
				if ($background_attr[3]) {
					$aBg[] = 'background-size:'. esc_attr($background_attr[3]);
				} elseif (mfn_opts_get('footer-bg-img-size')) {
					if (in_array(mfn_opts_get('footer-bg-img-size'), array( 'cover', 'contain' ))) {
						$aBg[] = 'background-size:'. esc_attr(mfn_opts_get('footer-bg-img-size'));
					} elseif (mfn_opts_get('footer-bg-img-size') == 'cover-ultrawide') {
						$output_ultrawide .= '#Footer{background-size:cover}';
					}
				}
			}

			$background = implode(';', $aBg);

			$output .= '#Footer{'. $background. '}';
		}

		// output -----

		if ($output_ultrawide) {
			$output .= '@media only screen and (min-width: 1921px){'. $output_ultrawide .'}';
		}

		return $output;
	}
}

/**
 * Styles | Minify
 */

if (! function_exists('mfn_styles_minify')) {
	function mfn_styles_minify($css)
	{
		// remove comments and whitespaces
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/|(\n|\t)!', '', $css);

		// replace ' { ' -> '{'
		$css = preg_replace('!\s*{\s*!', '{', $css);

		// replace ' ; ' -> ';'
		$css = preg_replace('!\s*;\s*!', ';', $css);

		// replace ' , ' -> ','
		$css = preg_replace('!\s*,\s*!', ',', $css);

		// replace ' : ' -> ':'
		$css = preg_replace('!\s*:\s*!', ':', $css);

		// replace ' ;} ' -> '}'
		$css = preg_replace('!;}!', '}', $css);

		return $css;
	}
}

/**
 * Styles | Dynamic
 */

if ( ! function_exists( 'mfn_styles_dynamic' ) ) {
	function mfn_styles_dynamic()
	{
		ob_start();

		// style

		include_once get_theme_file_path( '/style.php' );

		// responsive

		if ( mfn_opts_get( 'responsive' ) ) {
			include_once get_theme_file_path( '/style-responsive.php' );
		}

		// colors

		if ( $layoutID = mfn_layout_ID() ) {
			$skin = get_post_meta( $layoutID, 'mfn-post-skin', true );
		} else {
			$skin = mfn_opts_get( 'skin', 'custom' );
		}

		if ( 'custom' == $skin ) {
			include_once get_theme_file_path( '/style-colors.php' );
		} elseif ( 'one' == $skin ) {
			include_once get_theme_file_path( '/style-one.php' );
		}

		$css = ob_get_clean();

		return mfn_styles_minify( $css );
	}
}

/**
 * Styles | Custom Styles
 */

if (! function_exists('mfn_styles_custom')) {
	function mfn_styles_custom()
	{
		// Theme Options | Custom CSS

		if( !empty( $_GET['visual'] ) && $_GET['visual'] == 'iframe' ) return '';

		$css = mfn_opts_get('custom-css');

		// Layouts | Custom colors

		if ($layoutID = mfn_layout_ID()){

			$layout_css = '';

			if (get_post_meta($layoutID, 'mfn-post-background-subheader', true)) {

				$layout_css .= '#Subheader{background-color:'. get_post_meta($layoutID, 'mfn-post-background-subheader', true) .'}';

			}

			if (get_post_meta($layoutID, 'mfn-post-color-subheader', true)) {

				$layout_css .= '#Subheader .title{color:'. get_post_meta($layoutID, 'mfn-post-color-subheader', true) .'}';
				$layout_css .= '#Subheader ul.breadcrumbs li, #Subheader ul.breadcrumbs li a{color:'. mfn_hex2rgba(get_post_meta($layoutID, 'mfn-post-color-subheader', true), .6) .'}';

			}

			$css .= $layout_css;
		}

		return $css;
	}
}

/**
 * Styles | Custom Styles | Page Options
 */

if (! function_exists('mfn_styles_page_custom')) {
	function mfn_styles_page_custom()
	{
		// Page Options | Custom CSS

		if( !empty( $_GET['visual'] ) && $_GET['visual'] == 'iframe' ) return '';

		$css = get_post_meta(mfn_ID(), 'mfn-post-css', true);

		return $css;
	}
}

/**
 * Scripts
 */

if (! function_exists('mfn_scripts')) {
	function mfn_scripts()
	{

		$min = '';

		$performance_assets_disable = mfn_opts_get('performance-assets-disable');
		$prettyphoto_options = mfn_opts_get('prettyphoto-options');
		$performance_minify_js = mfn_opts_get('minify-js','');

		if( $performance_minify_js ){
			$min = '.min';
		}

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');

		wp_enqueue_script('mfn-debouncedresize', get_theme_file_uri('/js/plugins/debouncedresize.min.js'), array('jquery'), MFN_THEME_VERSION, true);

		if ( ! isset( $prettyphoto_options['disable'] ) ) {
			wp_enqueue_script('mfn-magnificpopup', get_theme_file_uri('/js/plugins/magnificpopup.min.js'), array('jquery'), MFN_THEME_VERSION, true);
		}

		wp_enqueue_script('mfn-menu', get_theme_file_uri('/js/menu'. $min .'.js'), array('jquery'), MFN_THEME_VERSION, true);
		wp_enqueue_script('mfn-visible', get_theme_file_uri('/js/plugins/visible.min.js'), array('jquery'), MFN_THEME_VERSION, true);

		if ( ! isset( $performance_assets_disable[ 'entrance-animations' ] ) ) {
			wp_enqueue_script('mfn-animations', get_theme_file_uri('/assets/animations/animations.min.js'), array('jquery'), MFN_THEME_VERSION, true);
		}

		// Theme Options -> Sidebar Sticky
		if ( mfn_opts_get('sidebar-sticky') ) {
			wp_enqueue_script('mfn-stickysidebar', get_theme_file_uri('/js/plugins/stickysidebar.min.js'), array('jquery'), MFN_THEME_VERSION, true);
		}

		$parallax = mfn_parallax_plugin();

		wp_enqueue_script('mfn-enllax', get_theme_file_uri('/js/plugins/enllax.min.js'), array('jquery'), MFN_THEME_VERSION, true);

		if ( ! isset( $performance_assets_disable[ 'parallax' ] ) ) {
			if ( 'translate3d' == $parallax ) {
				wp_enqueue_script('mfn-parallax', get_theme_file_uri('/js/parallax/translate3d'. $min .'.js'), array('jquery'), MFN_THEME_VERSION, true);
			} elseif ( 'stellar' == $parallax ) {
				wp_enqueue_script('mfn-stellar', get_theme_file_uri('/js/parallax/stellar.min.js'), array('jquery'), MFN_THEME_VERSION, true);
			}
		}

		if( mfn_opts_get('header-search-live') ) {
			wp_enqueue_script('mfn-livesearch', get_theme_file_uri('js/live-search'. $min .'.js'), array('underscore'), MFN_THEME_VERSION, true);
		}

		wp_enqueue_script('mfn-scripts', get_theme_file_uri('/js/scripts'. $min .'.js'), array('jquery'), MFN_THEME_VERSION, true);

		if( mfn_opts_get('keyboard-support') || mfn_opts_get('warning-open-links') ) {
			wp_enqueue_script('mfn-accessibility', get_theme_file_uri('/js/accessibility'. $min .'.js'), array('jquery'), MFN_THEME_VERSION, true);
		}

		// single post | reply comment

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Live search, get categories name without problem of spaces
		if ( mfn_opts_get('header-search-live') ) {
			wp_localize_script( 'mfn-scripts', 'mfn_livesearch_categories', mfn_list_categories() );
		}

		// Load nicescroll only for Header Creative
		if ( false !== strpos(mfn_header_style(), 'header-creative') ){
			wp_enqueue_script('mfn-nicescroll', get_theme_file_uri('/js/plugins/nicescroll.min.js'), array('jquery'), MFN_THEME_VERSION, true);
		}

		// scripts config

		$lightbox_options = mfn_opts_get('prettyphoto-options');
		$is_translation_on = mfn_opts_get('translate');

		$config = array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'mobileInit' => mfn_opts_get('mobile-menu-initial', 1240),
			'parallax' => mfn_parallax_plugin(),
			'responsive' => intval(mfn_opts_get('responsive', 0)),
			'sidebarSticky' => mfn_opts_get('sidebar-sticky') ? true : false,
			'lightbox' => array(
				'disable' => isset($lightbox_options['disable']) ? true : false,
				'disableMobile' => isset($lightbox_options['disable-mobile']) ? true : false,
				'title' => isset($lightbox_options['title']) ? true : false,
			),
			'view' => array(
				'grid_width' => intval(mfn_opts_get('grid-width', 1200))
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
					'portfolio' =>  $is_translation_on ? mfn_opts_get('translate-livesearch-portfolio', 'Portfolio') : __('Portfolio','betheme'),
					'post' => $is_translation_on ? mfn_opts_get('translate-livesearch-posts', 'Posts') : __('Posts','betheme'),
					'products' => $is_translation_on ? mfn_opts_get('translate-livesearch-products', 'Products') : __('Products','betheme'),
				),
			),
			'accessibility' => array(
				'translation' => array(
					'headerContainer' => __('Header container', 'betheme'),
					'toggleSubmenu' => __('Toggle submenu', 'betheme'),
				)
			),
			'home_url' => get_home_url( null, '', 'relative' ),
			'home_url_lang' => mfn_opts_get('header-search-live-wpml_search_in_language') ? get_home_url() : get_site_url(),
			'site_url' => get_site_url(),
			'translation' => array(
				'success_message' => $is_translation_on ? mfn_opts_get('translate-success-message', 'Link copied to the clipboard.') : __('Link copied to the clipboard.','betheme'),
				'error_message' => $is_translation_on ? mfn_opts_get('translate-error-message', 'Something went wrong. Please try again later!') : __('Something went wrong. Please try again later!','betheme'),
			)
		);

		wp_localize_script( 'mfn-scripts', 'mfn', $config );
	}
}
add_action('wp_enqueue_scripts', 'mfn_scripts');

/**
 * Scripts | GDPR 2.0
 */

function mfn_scripts_gdpr2() {
	if( mfn_opts_get('gdpr2') ){
		$gdpr2 = "
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('consent', 'default', {
	    'ad_storage': 'denied',
	    'ad_user_data': 'denied',
	    'ad_personalization': 'denied',
	    'analytics_storage': 'denied'
	  });";
		wp_add_inline_script('mfn-scripts', $gdpr2);
	}
}
add_action('wp_enqueue_scripts', 'mfn_scripts_gdpr2');

/**
 * Scripts | Custom JS
 */

function mfn_scripts_custom() {

	// Google Tag ID

	if( $gtag_ID = mfn_opts_get('google-gtag-id') ){

		$gtag_js = "document.addEventListener('DOMContentLoaded', () => { setTimeout(initGTM, 3000); });
document.addEventListener('scroll', initGTMOnEvent);
document.addEventListener('mousemove', initGTMOnEvent);
document.addEventListener('touchstart', initGTMOnEvent);

function initGTMOnEvent(event) { initGTM(); event.currentTarget.removeEventListener(event.type, initGTMOnEvent); }

function initGTM() {
	if (window.gtmDidInit) { return false; }
	window.gtmDidInit = true;

	const script = document.createElement('script');
	script.src = 'https://www.googletagmanager.com/gtag/js?id=". esc_attr($gtag_ID) ."';

	const script2 = document.createElement('script');
	const script2Content = document.createTextNode(\"window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '". esc_attr($gtag_ID) ."');\");

	script2.appendChild(script2Content);

	document.head.appendChild(script);
	document.head.appendChild(script2);
}";

		wp_add_inline_script('mfn-scripts', $gtag_js);
	}

	// Custom JS field

	if ( $custom_js = mfn_opts_get('custom-js') ) {
		wp_add_inline_script('mfn-scripts', $custom_js);
	}

}
add_action('wp_enqueue_scripts', 'mfn_scripts_custom');

/**
 * Body classes | Header
 * Adds classes to the array of body classes.
 */

if (! function_exists('mfn_header_style')) {
	function mfn_header_style($firstPartOnly = false)
	{

		$header_layout = false;

		// plugin: Muffin Header Builder

		if (class_exists('Mfn_HB_Front') && get_option('mfn_header_builder')) {
			return 'mhb';
		}

		// header styles

		if ($_GET && key_exists('mfn-h', $_GET)) {
			$header_layout = esc_html($_GET['mfn-h']); // demo
		} elseif ($layoutID = mfn_layout_ID()) {
			$header_layout = get_post_meta($layoutID, 'mfn-post-header-style', true);
		} elseif (mfn_opts_get('header-style')) {
			$header_layout =  mfn_opts_get('header-style');
		}

		if (strpos($header_layout, ',')) {

			// multiple header parameters

			$a_header_layout = explode(',', $header_layout);

			// return ONLY first parameter

			if ($firstPartOnly) {
				return 'header-'.$a_header_layout[0];
			}

			foreach ((array)$a_header_layout as $key => $val) {
				$a_header_layout[$key] = 'header-'. $val;
			}
			$header = implode(' ', $a_header_layout);

		} else {

			// one parameter
			$header = 'header-'. $header_layout;
		}

		return $header;
	}
}

/**
 * Convert sidebar name
 */

if (! function_exists('mfn_sidebar_convert_name')) {
	function mfn_sidebar_convert_name( $sidebar_name ){
		return 'sidebar-'. str_replace('+', '-', urlencode(strtolower(trim($sidebar_name ?? ''))));
	}
}

/**
 * Converts sidebar ID to name (slug)
 */

if (! function_exists('mfn_sidebar_id_name')) {
	function mfn_sidebar_id_name($sidebar_id){

		$sidebar_name = false;

		if ($sidebar_id >= 0) {

			$dynamic_sidebars = mfn_opts_get('sidebars');

			if (isset($dynamic_sidebars[$sidebar_id])) {
				$sidebar_name = mfn_sidebar_convert_name($dynamic_sidebars[$sidebar_id]);
			}

		}

		return $sidebar_name;
	}
}

/**
 * Get sidebar data for single or both sidebars
 */

if (! function_exists('mfn_sidebar_one_or_both')) {
	function mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2 = false, $ofcs = false){

		// demo | off canvas sidebar
		if( isset($_GET['mfn-demo-ofcs']) ){
			$layout = 'offcanvas-sidebar';
		}

		// demo | sidebar layout
		if( isset($_GET['mfn-demo-sidebar-layout']) ){
			$layout = $_GET['mfn-demo-sidebar-layout'];
		}

		$result = array();

		if( 'no-sidebar' == $layout ){
			return false;
		}

		if( 'offcanvas-sidebar' == $layout && !$ofcs){
			return false;
		}

		if( 'both-sidebars' == $layout ){

			// both sidebars

			if ($sidebar && is_active_sidebar($sidebar)) {
				$result['layout'] = $layout;
				$result['sidebar']['first'] = $sidebar;
			}

			if ($sidebar2 && is_active_sidebar($sidebar2)) {
				if( !$result['layout'] ){
					$result['layout'] = 'right-sidebar';
				}
				$result['sidebar']['second'] = $sidebar2;
			} elseif( ! empty($result['layout']) ) {
				$result['layout'] = 'left-sidebar';
			}

		} else {

			// single sidebar

			if ($sidebar && is_active_sidebar($sidebar)) {
				$result['layout'] = $layout;
				$result['sidebar']['first'] = $sidebar;
			}

		}

		if( empty($result) ) $result = false;

		return $result;

	}
}

/**
 * Sidebar
 * Get full sidebar data (layout + sidebar(s)) for current page
 */

if (! function_exists('mfn_sidebar')) {
	function mfn_sidebar($ofcs = false) {

		global $mfn_global;

		// plugin related sidebars -----

		// WooCommerce: disable sidebar for single product in Theme Options

		if ( function_exists('is_product') && is_product() && empty($mfn_global['singular_template']) && ('shop' == mfn_opts_get('shop-sidebar')) ) {

			return false;

		}

		// WooCommerce: shop & categories

		if ( function_exists('is_woocommerce') && is_woocommerce() ) {

			$layout = get_post_meta(mfn_ID(), 'mfn-post-layout', true);

			/*if ( (! $layout) || ('both-sidebars' == $layout) ) {
				$layout = 'right-sidebar';
			}*/

			$sidebar = false;
			$sidebar2 = false;

			if( !empty($layout) && $layout != 'no-sidebar' ) $sidebar = 'shop';

			if( !empty($layout) && $layout != 'no-sidebar' && get_post_meta(mfn_ID(), 'mfn-post-sidebar', true) !== '' ) $sidebar = mfn_sidebar_id_name(get_post_meta(mfn_ID(), 'mfn-post-sidebar', true));
			if( !empty($layout) && $layout != 'no-sidebar' && get_post_meta(mfn_ID(), 'mfn-post-sidebar2', true) !== '' ) $sidebar2 = mfn_sidebar_id_name(get_post_meta(mfn_ID(), 'mfn-post-sidebar2', true));

			if( is_search() && empty( mfn_opts_get('search-sidebar-inherited-woo') ) ){
				$sidebar = 'mfn-search';
			}

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		}

		if( is_singular() && !empty($mfn_global['singular_template']) ) {



			$layout = get_post_meta($mfn_global['singular_template'], 'mfn-post-layout', true);

			$sidebar = false;
			$sidebar2 = false;

			if( !empty($layout) && $layout != 'no-sidebar' && get_post_meta($mfn_global['singular_template'], 'mfn-post-sidebar', true) !== '' ) $sidebar = mfn_sidebar_id_name(get_post_meta($mfn_global['singular_template'], 'mfn-post-sidebar', true));
			if( !empty($layout) && $layout != 'no-sidebar' && get_post_meta($mfn_global['singular_template'], 'mfn-post-sidebar2', true) !== '' ) $sidebar2 = mfn_sidebar_id_name(get_post_meta($mfn_global['singular_template'], 'mfn-post-sidebar2', true));

			if( $sidebar || $sidebar2 ) return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		}


		// bbPress

		if ( function_exists('is_bbpress') && is_bbpress() ) {

			return mfn_sidebar_one_or_both('right-sidebar', 'forum', false, $ofcs);

		}

		// BuddyPress

		if ( function_exists('is_buddypress') && is_buddypress() ) {

			return mfn_sidebar_one_or_both('right-sidebar', 'buddy', false, $ofcs);

		}

		// Easy Digital Downloads

		if ( 'download' == get_post_type() ) {

			return mfn_sidebar_one_or_both('right-sidebar', 'edd', false, $ofcs);

		}

		// Events Calendar

		if ( function_exists('tribe_is_month') ) {
			if ( tribe_is_month() || tribe_is_day() || tribe_is_event() || tribe_is_event_query() || tribe_is_venue() ) {

				return mfn_sidebar_one_or_both('right-sidebar', 'events', false, $ofcs);

			}
		}

		// theme related sidebars -----

		// template blank & under construction

		if ( is_page_template('template-blank.php') || is_page_template('under-construction.php') ) {

			return false;

		}

		// search page

		if ( is_search() ) {

			$layout = mfn_opts_get('search-layout', 'right-sidebar');

			return mfn_sidebar_one_or_both($layout, 'mfn-search', false, $ofcs);

		}

		// exit if page has no ID

		if( ! mfn_ID() ){
			return false;
		}

		// blog category

		if ( is_category() ){

			$blog_page_id = mfn_get_blog_ID();

			if( !empty( $mfn_global['archive_template'] ) ){
				$blog_page_id = $mfn_global['archive_template'];
			}

			if( ! $blog_page_id ){
				return false;
			}

			$layout = get_post_meta($blog_page_id, 'mfn-post-layout', true);

			$category = get_category(get_query_var('cat'));
			$sidebar = 'blog-cat-'. $category->slug;

			if (! is_active_sidebar($sidebar)) {

				$sidebar_id = get_post_meta($blog_page_id, 'mfn-post-sidebar', true);
				$sidebar = mfn_sidebar_id_name($sidebar_id);
			}

			$sidebar2_id = get_post_meta($blog_page_id, 'mfn-post-sidebar2', true);
			$sidebar2 = mfn_sidebar_id_name($sidebar2_id);

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		}

		// portfolio taxonomy

		if( is_tax() ){

			$portfolio_page_id = mfn_opts_get('portfolio-page');

			if( ! $portfolio_page_id ){
				return false;
			}

			$layout = get_post_meta($portfolio_page_id, 'mfn-post-layout', true);
			$sidebar = 'portfolio-cat-'. get_query_var('portfolio-types');

			if (! is_active_sidebar($sidebar)) {

				$sidebar_id = get_post_meta($portfolio_page_id, 'mfn-post-sidebar', true);
				$sidebar = mfn_sidebar_id_name($sidebar_id);
			}

			return mfn_sidebar_one_or_both($layout, $sidebar, false, $ofcs);
		}

		// sidebar set in post meta or forced in theme options

		if ( ('page' == get_post_type()) && ($layout = mfn_opts_get('single-page-layout')) ) {

			// theme options | force sidebar for single page

			$sidebar = mfn_sidebar_convert_name(mfn_opts_get('single-page-sidebar'));
			$sidebar2 = mfn_sidebar_convert_name(mfn_opts_get('single-page-sidebar2'));

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		} elseif ( ('post' == get_post_type()) && is_single() && ($layout = mfn_opts_get('single-layout')) ) {

			// theme options | force sidebar for single post

			$sidebar = mfn_sidebar_convert_name(mfn_opts_get('single-sidebar'));
			$sidebar2 = mfn_sidebar_convert_name(mfn_opts_get('single-sidebar2'));

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		} elseif ( ('portfolio' == get_post_type()) && is_single() && ($layout = mfn_opts_get('single-portfolio-layout'))  ) {

			// theme options | force sidebar for single portfolio

			$sidebar = mfn_sidebar_convert_name(mfn_opts_get('single-portfolio-sidebar'));
			$sidebar2 = mfn_sidebar_convert_name(mfn_opts_get('single-portfolio-sidebar2'));

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		} elseif ($layout = get_post_meta(mfn_ID(), 'mfn-post-layout', true) ) {

			// post meta

			$sidebar_id = get_post_meta(mfn_ID(), 'mfn-post-sidebar', true);
			$sidebar = mfn_sidebar_id_name($sidebar_id);

			$sidebar2_id = get_post_meta(mfn_ID(), 'mfn-post-sidebar2', true);
			$sidebar2 = mfn_sidebar_id_name($sidebar2_id);

			return mfn_sidebar_one_or_both($layout, $sidebar, $sidebar2, $ofcs);

		}

		return false;

	}
}

/**
 * Converts sidebar layout to body classes
 */

if (! function_exists('mfn_sidebar_class')) {
	function mfn_sidebar_class($sidebars){

		if(empty($sidebars['layout'])){
			return false;
		}

		$layout = $sidebars['layout'];

		switch ($layout) {

			case 'left-sidebar':
				$classes = 'with_aside aside_left';
				break;

			case 'right-sidebar':
				$classes = 'with_aside aside_right';
				break;

			case 'offcanvas-sidebar':
				$classes = 'off_canvas_sidebar';
				break;

			case 'both-sidebars':
				$classes = 'with_aside aside_both';
				break;

			default:
				$classes = false;

		}

		return $classes;
	}
}

/**
 * Body classes
 * Adds classes to the array of body classes.
 */

if (! function_exists('mfn_body_classes')) {
	function mfn_body_classes($classes)
	{

		global $mfn_global;
		$is_vb = false;
		$layoutID = mfn_layout_ID();

		$header_tmp_id = $mfn_global['header'] ?? false;
		$footer_tmp_id = $mfn_global['footer'] ?? false;

		if( !empty($mfn_global['sidemenu']) ){
			$classes[] = 'mfn-sidemenu-always-visible-tmpl-active';
			$classes[] = !empty( get_post_meta( $mfn_global['sidemenu'], 'sidemenu_position', true) ) ? 'mfn-sidemenu-always-visible-tmpl-right' : 'mfn-sidemenu-always-visible-tmpl-left';
		}

		// be setup wizard

		if( isset( $_GET['mfn-setup-preview'] ) ){
			$header_tmp_id = false;
			$footer_tmp_id = false;
		}

		// preloader

		if( mfn_opts_get('preloader') ){
			$classes[] = 'preloader';
		}

		// 404

		if( is_404() ){

			// custom 404 page
			if( mfn_opts_get('error404-page') ){

				$classes[] = 'custom-404';

				if( mfn_opts_get('error404-header') ){
					$classes[] = 'custom-404-header-show';
				} else {
					$classes[] = 'custom-404-header-hide';
				}
				if( mfn_opts_get('error404-footer') ){
					$classes[] = 'custom-404-footer-show';
				} else {
					$classes[] = 'custom-404-footer-hide';
				}

			} else {

				$classes[] = 'default-404';

			}

		}

		// under construction

		if( mfn_is_under_construction() ){

			$classes[] = 'under-construction';

			// custom uc page
			if( mfn_opts_get('construction-page') ){

				$classes[] = 'custom-uc';

				if( mfn_opts_get('construction-header') ){
					$classes[] = 'custom-uc-header-show';
				} else {
					$classes[] = 'custom-uc-header-hide';
				}
				if( mfn_opts_get('construction-footer') ){
					$classes[] = 'custom-uc-footer-show';
				} else {
					$classes[] = 'custom-uc-footer-hide';
				}

			} else {

				$classes[] = 'default-uc';

			}

		}

		// buttons | disable WooCommerce button styles

		$classes[] = 'woocommerce-block-theme-has-button-styles';

		// builder blocks

		if( mfn_is_blocks() ){
			$classes[] = 'builder-blocks';
		}

		// header template

		if( $header_tmp_id ){
			$classes[] = 'mfn-header-template';
		}

		if( $footer_tmp_id ){
			$mfn_footer_tmpl_style = get_post_meta($footer_tmp_id, 'footer_type', true);
			if( $mfn_footer_tmpl_style ){
				$classes[] = 'mfn-footer-'.$mfn_footer_tmpl_style;
			}else{
				$classes[] = 'mfn-footer-default';
			}
		}

		// search overlay and blur

		if( mfn_opts_get('search-scroll-disable') ){
			$classes[] = 'search-scroll-disable';
		}
		if( mfn_opts_get('search-overlay') ){
			$classes[] = 'has-search-overlay';
		}
		if( mfn_opts_get('search-overlay-blur') ){
			$classes[] = 'has-search-blur';
		}

		// gdpr 2.0

		if( mfn_opts_get('gdpr2-scroll-disable') ){
			$classes[] = 'gdpr2-scroll-disable';
		}

		// slider

		if (mfn_slider_isset()) {
			if(function_exists('is_woocommerce') && is_woocommerce()) {
				// do nothing
			} else {
				$classes[] = 'template-slider';
			}
		}

		if(mfn_opts_get('mobile-sidebar') == 1){
			$classes[] = 'ofcs-mobile';
		}

		// woocommerce

		if(function_exists('is_woocommerce') && is_woocommerce()) {
			if( mfn_opts_get( 'shop-mobile-review-avatar-hide' ) ) {
				$classes[] = 'shop-mobile-review-avatar-hide';
			}
		}

		$shop_icons_hide = mfn_opts_get('shop-icons-hide');
		$wishlist_position = mfn_opts_get('shop-wishlist-position');

		if( function_exists('is_woocommerce') && !empty($mfn_global['singular_template']) && is_product() && !isset($shop_icons_hide['wishlist']) && isset($wishlist_position[0]) ) {
			$classes[] = 'mfn-wishlist-next-to-cart-button';

			$wish_type = mfn_opts_get('shop-wishlist-type');
			if( !empty($wish_type) && $wish_type == 'button' ) $classes[] = 'mfn-wishlist-is-button';
		}

		if( function_exists('is_woocommerce') && mfn_opts_get('shop-wishlist-page') && mfn_opts_get('shop-wishlist-page') == get_the_ID() ){
			$classes[] = 'woocommerce';
			$classes[] = 'mfn-wishlist-shop-template wishlist';

			$wish_arr = array();

			if( isset($_COOKIE['mfn_wishlist']) ){
			  $wishlist = $_COOKIE['mfn_wishlist'];
			  $wish_arr = explode(',', $wishlist);
			}

			if( empty($wish_arr) ) $classes[] = 'wishlist-page-is-empty';
		}

		// sidebar

		if( ! mfn_is_under_construction() ){
			$classes[] = mfn_sidebar_class(mfn_sidebar());
		}

		// skin

		if ($layoutID) {
			$classes[] = 'color-'. get_post_meta($layoutID, 'mfn-post-skin', true);
		} else {
			$classes[] = 'color-'. mfn_opts_get('skin', 'custom');
		}

		// brightness

		$classes[] = 'content-brightness-'. mfn_brightness(mfn_opts_get('background-body', '#FCFCFC'));
		$classes[] = 'input-brightness-'. mfn_brightness(mfn_opts_get('background-form', '#ffffff'));

		// style: default & simple

		if ($_GET && key_exists('mfn-style', $_GET)) {
			$classes[] = 'style-'. esc_html($_GET['mfn-style']); // demo
		} else {
			$classes[] = 'style-'. mfn_opts_get('style', 'default');
		}

		// accessibility

		if( mfn_opts_get('keyboard-support') ) {
			$classes[] = 'keyboard-support';
		}
		if ( mfn_opts_get('underline-links') ) {
			$classes[] = 'underline-links';
		}
		if ( mfn_opts_get('warning-open-links') ) {
			$classes[] = 'warning-links';
		}

		// button animation

		$button_animation = mfn_opts_get('button-animation', 'fade');
		$button_animation = explode( ' ', $button_animation );
		foreach( $button_animation as $ba ){
			$classes[] = 'button-animation-'. $ba;
		}

		// layout: full width & boxed

		if ( isset( $_GET['mfn-box'] ) ) {
			$classes[] = 'layout-boxed'; // demo
		} elseif ($layoutID) {
			$classes[] = 'layout-'. get_post_meta($layoutID, 'mfn-post-layout', true);
		} else {
			$classes[] = 'layout-'. mfn_opts_get('layout', 'full-width');
		}

		// one page

		if (get_post_meta(mfn_ID(), 'mfn-post-one-page', true)) {
			$classes[] = 'one-page';
		}

		// full width

		if( isset( $_GET['mfn-demo-full-width'] ) ){
			$full_width = $_GET['mfn-demo-full-width']; // demo | full width
		} else {
			$full_width = get_post_meta( mfn_ID('perfect_match'), 'mfn-post-full-width', true );
		}

		if( $full_width ){
			$classes[] = 'full-width-'. $full_width;
		}

		// password protected

		if ( get_post_field( 'post_password', mfn_ID() ) ) {
			$classes[] = 'password-protected';
		}

		// image frame: style

		if ($_GET && key_exists('mfn-if', $_GET)) {
			$classes[] = 'if-'. esc_html($_GET['mfn-if']); // demo
		} elseif (mfn_opts_get('image-frame-style')) {
			$classes[] = 'if-'. mfn_opts_get('image-frame-style');
		}

		// image frame: caption

		if (mfn_opts_get('image-frame-caption')) {
			$classes[] = 'if-caption-on';
		}

		// content padding

		if (mfn_opts_get('content-remove-padding')) {
			$classes[] = 'no-content-padding';
		} elseif (get_post_meta(mfn_ID(), 'mfn-post-remove-padding', true)) {
			$classes[] = 'no-content-padding';
		}

		// single template

		if (get_post_meta(mfn_ID(), 'mfn-post-template', true)) {
			$classes[] = 'single-template-'. get_post_meta(mfn_ID(), 'mfn-post-template', true);
		}

		// love

		if (! mfn_opts_get('love')) {
			$classes[] = 'hide-love';
		}

		// table: hover

		if (mfn_opts_get('table-hover')) {
			$classes[] = 'table-'. mfn_opts_get('table-hover');
		}

		// plugin: Contact Form 7: form error

		if (mfn_opts_get('cf7-error')) {
			$classes[] = 'cf7p-'. mfn_opts_get('cf7-error');
		}

		// advanced | other

		$layout_options = mfn_opts_get('layout-options');
		if (is_array($layout_options)) {
			if (isset($layout_options['no-shadows'])) {
				$classes[] = 'no-shadows';
			}
			if (isset($layout_options['boxed-no-margin'])) {
				$classes[] = 'boxed-no-margin';
			}
		}

		// elementor

		if( mfn_is_elementor( mfn_ID() ) ){
			$classes[] = 'is-elementor';
		}

		if( !empty($_GET['visual']) && $_GET['visual'] == 'iframe' && is_singular() && get_post_type( get_the_ID() ) == 'template' && get_post_meta(get_the_ID(), 'mfn_template_type', true) == 'header' ){
			$is_vb = 'header';
		}

		// header -----

		if( !$header_tmp_id && !$is_vb ){

			$header_options = mfn_opts_get('header-fw') ? mfn_opts_get('header-fw') : false;

			// haeder | layout

			$classes[] = mfn_header_style();

			// header | full width

			if ($_GET && key_exists('mfn-hfw', $_GET)) {
				$classes[] = 'header-fw'; // demo
			} elseif (isset($header_options['full-width'])) {
				$classes[] = 'header-fw';
			}

			// header | boxed

			if (is_array($header_options) && isset($header_options['header-boxed'])) {
				$classes[] = 'header-boxed';
			}

			// header | sticky

			if ($layoutID) {
				if (get_post_meta($layoutID, 'mfn-post-sticky-header', true)) {
					$classes[] = 'sticky-header';
				}
			} elseif (mfn_opts_get('sticky-header')) {
				$classes[] = 'sticky-header';
			}

			// header | sticky: style

			if ($_GET && key_exists('mfn-ss', $_GET)) {
				$classes[] = 'sticky-'. esc_html($_GET['mfn-ss']); // demo
			} elseif ($layoutID) {
				$classes[] = 'sticky-'. get_post_meta($layoutID, 'mfn-post-sticky-header-style', true);
			} else {
				$classes[] = 'sticky-'. mfn_opts_get('sticky-header-style', 'white');
			}

			// action bar

			$action_bar = mfn_opts_get('action-bar');
			if ('1' === $action_bar) {
				// BeTheme < 21.3.3 compatibility
				$classes[] = 'ab-show';
			} elseif( isset($action_bar['show']) ) {
				$classes[] = 'ab-show';
			} else {
				$classes[] = 'ab-hide';
			}


			// menu | style

			if ($_GET && key_exists('mfn-m', $_GET)) {
				$classes[] = 'menu-'. esc_html($_GET['mfn-m']); // demo
			} elseif (mfn_opts_get('menu-style')) {
				$classes[] = 'menu-'. mfn_opts_get('menu-style');
			}

			// menu | options

			$menu_options = mfn_opts_get('menu-options');
			if (is_array($menu_options) && isset($menu_options['align-right'])) {
				$classes[] = 'menuo-right';
			}
			if (is_array($menu_options) && isset($menu_options['menu-arrows'])) {
				$classes[] = 'menuo-arrows';
			}
			if (is_array($menu_options) && isset($menu_options['hide-borders'])) {
				$classes[] = 'menuo-no-borders';
			}
			if (is_array($menu_options) && isset($menu_options['submenu-active'])) {
				$classes[] = 'menuo-sub-active';
			}
			if (is_array($menu_options) && isset($menu_options['submenu-limit'])) {
				$classes[] = 'menuo-sub-limit';
			}
			if (is_array($menu_options) && isset($menu_options['last'])) {
				$classes[] = 'menuo-last';
			}

			// megamenu: style

			if (mfn_opts_get('menu-mega-style')) {
				$classes[] = 'mm-'. mfn_opts_get('menu-mega-style');
			}

			// logo

			if (mfn_opts_get('logo-vertical-align')) {
				$classes[] = 'logo-valign-'. mfn_opts_get('logo-vertical-align');
			}

			$logo_options = mfn_opts_get('logo-advanced');
			if (is_array($logo_options) && isset($logo_options['no-margin'])) {
				$classes[] = 'logo-no-margin';
			}
			if (is_array($logo_options) && isset($logo_options['overflow'])) {
				$classes[] = 'logo-overflow';
			}
			if (is_array($logo_options) && isset($logo_options['no-sticky-padding'])) {
				$classes[] = 'logo-no-sticky-padding';
			}
			if (is_array($logo_options) && isset($logo_options['sticky-width-auto'])) {
				$classes[] = 'logo-sticky-width-auto';
			}

		}

		// subheader | transparent

		$skin = mfn_opts_get('skin', 'custom');
		if ($_GET && key_exists('mfn-subtr', $_GET)) {
			$classes[] = 'subheader-transparent'; // demo
		} elseif (! in_array($skin, array('custom','one'))) {
			if (mfn_opts_get('subheader-transparent') != 100) {
				$classes[] = 'subheader-transparent';
			}
		}

		// subheader | style

		if ($_GET && key_exists('mfn-sh', $_GET)) {
			$classes[] = 'subheader-'. esc_html($_GET['mfn-sh']); // demo
		} else {
			$classes[] = 'subheader-'. mfn_opts_get('subheader-style', 'title-left');
		}

		// footer -----

		// footer | style

		if (!$footer_tmp_id && $_GET && key_exists('mfn-ftr', $_GET)) {
			$classes[] = 'footer-'. esc_html($_GET['mfn-ftr']); // demo
		} elseif (!$footer_tmp_id && mfn_opts_get('footer-style')) {
			$classes[] = 'footer-'. mfn_opts_get('footer-style');
		}

		// footer | copy & social

		if (!$footer_tmp_id && mfn_opts_get('footer-hide') == 'center') {
			$classes[] = 'footer-copy-center';
		}

		// responsive -----

		if (! mfn_opts_get('responsive')) {
			$classes[] = 'responsive-off';
		}

		$responsive_overflow_x = mfn_opts_get('responsive-overflow-x');
		if( ! $responsive_overflow_x ){
			// enable on mobile by default
			$responsive_overflow_x = 'mobile';
		}
		$classes[] = 'responsive-overflow-x-'. $responsive_overflow_x;

		if (mfn_opts_get('mobile-order')) {
			$classes[] = 'mobile-sidebar-first';
		}

		if (mfn_opts_get('responsive-boxed2fw')) {
			$classes[] = 'boxed2fw';
		}
		if (mfn_opts_get('no-hover')) {
			$classes[] = 'no-hover-'. mfn_opts_get('no-hover');
		}
		if (mfn_opts_get('no-section-bg')) {
			$classes[] = 'no-section-bg-'. mfn_opts_get('no-section-bg');
		}
		if (mfn_opts_get('responsive-video')) {
			$classes[] = 'no-section-video-desktop';
		}
		if (mfn_opts_get('responsive-top-bar')) {
			$classes[] = 'mobile-tb-'. mfn_opts_get('responsive-top-bar');
		}
		if (mfn_opts_get('responsive-mobile-menu')) {
			$classes[] = 'mobile-'. mfn_opts_get('responsive-mobile-menu');
		}
		if (mfn_opts_get('mobile-menu')) {
			$classes[] = 'mobile-menu';
		}

		if( 'no-tablet' == mfn_opts_get('builder-section-padding') ) {
			$classes[] = 'no-sec-padding';
		}
		if( 'no-mobile' == mfn_opts_get('builder-section-padding') ) {
			$classes[] = 'no-sec-padding-mob';
		}

		$classes[] = 'mobile-mini-'. mfn_opts_get('responsive-header-minimal', 'mr-ll');

		// responsive | tablet | options

		$responsive_header_mob = mfn_opts_get('responsive-header-tablet');
		if (is_array($responsive_header_mob)) {
			if (isset($responsive_header_mob['sticky'])) {
				$classes[] = 'tablet-sticky';
			}
		}

		// responsive | mobile | options

		$responsive_header_mob = mfn_opts_get('responsive-header-mobile');
		if (is_array($responsive_header_mob)) {
			if (isset($responsive_header_mob['sticky'])) {
				$classes[] = 'mobile-sticky';
			}
			if (isset($responsive_header_mob['transparent'])) {
				$classes[] = 'mobile-tr-header';
			}
		}

		if( ! empty( mfn_opts_get('responsive-header-minimal') ) ){
			$classes[] = 'mobile-header-mini';
		}

		$mobile_icons = ['user','wishlist','cart','search','wpml','action'];
		foreach ( $mobile_icons as $icon ){
			$option = mfn_opts_get( 'mobile-icon-'. $icon );
			if( ! empty( $option ) ){
				$classes[] = 'mobile-icon-'. $icon .'-'. $option;
			}
		}

		// transparent -----

		$transparent_options = mfn_opts_get('transparent');
		if (is_array($transparent_options)) {
			if (isset($transparent_options['header'])) {
				$classes[] = 'tr-header';
			}
			if (isset($transparent_options['menu'])) {
				$classes[] = 'tr-menu';
			}
			if (isset($transparent_options['content'])) {
				$classes[] = 'tr-content';
			}
			if (isset($transparent_options['footer'])) {
				$classes[] = 'tr-footer';
			}
		}

		// live search

		if ( mfn_opts_get('header-search') === 'shop' && mfn_opts_get('header-search-live')) {
			$classes[] = 'mfn-livesearch-product-only';
		}

		// demo / debug

		if ($layoutID) {
			$classes[] = 'dbg-lay-id-'. $layoutID;
		}

		// page id

		$classes[] = 'be-page-'. mfn_ID();

		// builder demo

		if( ! empty( $_GET['demo'] ) ){
			$classes[] = 'bebuilder-demo';
		}

		// registered

		$reg = mfn_is_registered() ? 'reg-' : '';
		$classes[] = 'be-'. $reg . str_replace('.', '', MFN_THEME_VERSION);

		if( function_exists('is_woocommerce') && function_exists('is_checkout') && is_checkout() && !is_order_received_page() ) {

			$checkout_id = wc_get_page_id('checkout');
			$checkout_content = !empty($checkout_id) ? (string) get_post_field( 'post_content', $checkout_id ) : '';

	    if ( $checkout_id && function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', $checkout_content ) && !empty(mfn_opts_get('gutenberg-checkout')) ) {
	        $classes[] = 'mfn-blocks-checkout-summary-bg'; // checkout based on legacy shortcode
	    }

	    $checkout_options = mfn_opts_get('gutenberg-checkout-options');

	    if( !empty($checkout_options) && is_array($checkout_options) ) {
	    	foreach( $checkout_options as $co ) {
	    		if( !is_numeric($co) ) $classes[] = 'mfn-checkout-'.$co;
	    	}
	    }

		}

		return $classes;

	}
}
add_filter('body_class', 'mfn_body_classes');

/**
 * Annoying styles remover
 */

if (! function_exists('mfn_remove_recent_comments_style')) {
	function mfn_remove_recent_comments_style()
	{
		global $wp_widget_factory;
		if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
			remove_action('wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ));
		}
	}
}
add_action('widgets_init', 'mfn_remove_recent_comments_style');

// access to be builder

if (! function_exists('mfn_bebuilder_access')) {
	function mfn_bebuilder_access( $return = false ) {

		$visibility = mfn_opts_get('builder-visibility');

		if( !empty($visibility) && $visibility == 'hide' ) {
			return false;
		}

		$user = wp_get_current_user();

		if( $visibility == 'edit_theme_options' ){
			// if administrator
			$allowed_roles = array('administrator');
		}else if( $visibility == 'edit_pages' ){
			// if editor
			$allowed_roles = array('administrator', 'shop_manager', 'editor');
		}else{
			$allowed_roles = array('editor', 'administrator', 'author', 'shop_manager');
		}

		$allowed_roles = apply_filters('mfn_bebuilder_roles', $allowed_roles);

		if( is_multisite() ){
			if( current_user_can('upload_files') ){
				return true;
			}else{
				return false;
			}
		}else if( !array_intersect($allowed_roles, $user->roles ) ) {
			return false;
		}else if( defined('MFN_DISABLE_LIVE') && MFN_DISABLE_LIVE ){
			return false;
		}

		return true;
	}
}
add_filter( 'bebuilder_access', 'mfn_bebuilder_access', 10 );

// check if is bebuilder iframe
add_filter( 'bebuilder_preview', 'mfn_bebuilder_preview' );
if (! function_exists('mfn_bebuilder_preview') ) {
	function mfn_bebuilder_preview( $return ) {
		if( !empty( $_GET['visual'] ) && $_GET['visual'] == 'iframe' ){
			return true;
		}
		return false;
	}
}

// check if is demo version
add_filter( 'is_bebuilder_demo', 'mfn_bebuilder_demo' );
if (! function_exists('mfn_bebuilder_demo') ) {
	function mfn_bebuilder_demo( $return ) {
		if( !empty( $_GET['visual'] ) && defined( 'BEBUILDER_DEMO_VERSION' ) && $_GET['visual'] == 'demo' ){
			return true;
		}
		return false;
	}
}

/**
 * Visual builder
 */

function mfn_visual_builder(){

	if( !defined( 'BEBUILDER_DEMO_VERSION' ) ){
		$verify = apply_filters('bebuilder_access', false);
		if( !$verify ) return false;
	}

	if( apply_filters('bebuilder_preview', false) ) {



		add_action( 'wp_enqueue_scripts', 'mfnvb_iframe_style' );
		add_action( 'after_setup_theme', 'mfnvb_after_setup_theme' );
		add_action( 'wp', 'mfnvb_wp_iframe_action' );
		add_action( 'mfn_wp_footer_before', 'mfnvb_tmp_data_buidler' );
		add_action( 'mfn_demo_builder', 'mfnvb_preview_styles' );
		add_filter( 'body_class','mfnvb_body_classes' );

		function mfnvb_tmp_data_buidler() {
			echo '<div class="mfn-tmp-breadcrumbs" style="display: none;">'; mfn_breadcrumbs(); echo '</div>';
		}

		function mfnvb_preview_styles() {

			$css_variables = !empty(get_option('be_css_variables')) ? json_decode(get_option('be_css_variables'), true) : array();


			echo '<style class="mfn-be-variables-custom-css">';
			if( !empty($css_variables)) {
			echo 'body{';
			  	foreach($css_variables as $cv){
			  		echo $cv['title'].':'.$cv['value'].';'."\r\n";
			  	}
			  echo '}';
			  }
			echo '</style>';

			echo '<style class="mfn-be-theme-custom-css">';
				if( !empty(mfn_opts_get('custom-css')) ) echo mfn_opts_get('custom-css');
			echo '</style>';

			echo '<style class="mfn-be-page-custom-css">';
				if( !empty(get_post_meta(get_the_ID(), 'mfn-post-css', true)) ) echo get_post_meta(get_the_ID(), 'mfn-post-css', true);
			echo '</style>';


			echo '<div class="mfn-be-local-css-desktop"></div>';
			echo '<div class="mfn-be-local-css-laptop"></div>';
			echo '<div class="mfn-be-local-css-tablet"></div>';
			echo '<div class="mfn-be-local-css-mobile"></div>';
		}

		function mfnvb_iframe_style() {

			if ( !wp_script_is( 'google-maps', 'enqueued') && $api_key = mfn_opts_get('google-maps-api-key') ) {
				$api_key = '?key='. trim($api_key);
				wp_enqueue_script('google-maps', 'https://maps.google.com/maps/api/js'. $api_key . '&callback=mfnInitMap', false, null, true);
				wp_add_inline_script('mfn-scripts', 'function mfnInitMap() { return false; }');
			}

			wp_enqueue_style('mfn-inline-editor-style', get_theme_file_uri('/visual-builder/assets/css/medium-editor.min.css'), false, MFN_THEME_VERSION, false);
			wp_enqueue_style( 'mfn-iframe-vbstyle', get_theme_file_uri('/visual-builder/assets/css/iframe.css'), false, MFN_THEME_VERSION, 'all' );

			if( mfn_is_blocks() ){
				wp_enqueue_style( 'mfn-iframe-blocks', get_theme_file_uri('/visual-builder/assets/css/blocks.css'), false, MFN_THEME_VERSION, 'all' );
			}

			if( function_exists('is_woocommerce') ){
				wp_enqueue_script( 'wc-single-product' );
			}

			wp_enqueue_script('mfn-lottie-player', get_theme_file_uri('/assets/lottie/lottie-player.js'), false, null, true);

			// elements

			wp_enqueue_style('mfn-element-countdown-2', get_theme_file_uri('/css/elements/countdown-2.css'), null, MFN_THEME_VERSION);
			wp_enqueue_style('mfn-element-divider-2', get_theme_file_uri('/css/elements/divider-2.css'), null, MFN_THEME_VERSION);
			wp_enqueue_style('mfn-element-list-2', get_theme_file_uri('/css/elements/list-2.css'), null, MFN_THEME_VERSION);
			wp_enqueue_style('mfn-element-toggle', get_theme_file_uri('/css/elements/toggle.css'), null, MFN_THEME_VERSION);

			wp_enqueue_style('mfn-swiper', get_theme_file_uri('/css/scripts/swiper.css'), false, MFN_THEME_VERSION);

			wp_enqueue_script('mfn-swiper', get_theme_file_uri('/js/swiper.js'), array('jquery'), MFN_THEME_VERSION, true);
			wp_enqueue_script('mfn-isotope', get_theme_file_uri('/js/plugins/isotope.min.js'), array('jquery'), MFN_THEME_VERSION, true);

			wp_enqueue_script('mfn-waypoints', get_theme_file_uri('/js/plugins/waypoints.min.js'), ['jquery'], MFN_THEME_VERSION, true);
			wp_enqueue_script('mfn-countdown', get_theme_file_uri('/js/plugins/countdown.min.js'), ['jquery'], MFN_THEME_VERSION, true);
			wp_enqueue_style('mfn-tabber', get_theme_file_uri('/css/elements/tabber.css'), null, MFN_THEME_VERSION);

		}

		function mfnvb_wp_iframe_action() {
			$post_id = get_the_ID();
			$post_type = get_post_type($post_id);

			// add sample product to cart if is empty while cart template editing
			if( function_exists('is_woocommerce') && $post_type == 'template' && !empty(get_post_meta($post_id, 'mfn_template_type', true)) && in_array(get_post_meta($post_id, 'mfn_template_type', true), array('cart', 'checkout', 'thanks')) ) {

 				if( WC()->cart->is_empty() ) {
					$sample = Mfn_Builder_Woo_Helper::sample_item('product');
					$product = wc_get_product($sample->ID);
					$product_id = $sample->ID;

					if( $product && $product->is_type('variable') ) {
						foreach ( $product->get_available_variations() as $key => $variation ) {
							$product_id = $variation['variation_id'];
						}
					}

					if( !empty($product_id) ){
						WC()->cart->add_to_cart( $product_id );
					}
				}
			}
		}

		function mfnvb_after_setup_theme() {
			show_admin_bar(false);
		}

		function mfnvb_body_classes( $classes ) {
		    $classes[] = 'mfn-ui';
		    $post_id = get_the_ID();
		    $post_type = get_post_type($post_id);

		    if( !empty( $_GET['mfn-template-id'] ) ){
		    	$classes[] = 'mfn-ui-bebuilder-'.get_post_type($_GET['mfn-template-id']);
		    	if( !empty(get_post_meta($_GET['mfn-template-id'], 'mfn_template_type', true)) ) $classes[] = 'mfn-bebuilder-'.get_post_meta($_GET['mfn-template-id'], 'mfn_template_type', true);
		    }else{
		    	$classes[] = 'mfn-ui-bebuilder-'.$post_type;
		    	if( !empty(get_post_meta($post_id, 'mfn_template_type', true)) ) $classes[] = 'mfn-bebuilder-'.get_post_meta($post_id, 'mfn_template_type', true);
		    }


		    $user_id = get_current_user_id();
		    $options = get_site_option( 'betheme_builder_'. $user_id );

				// demo
			  if( ! empty($_GET['ui']) && 'blocks' === $_GET['ui'] ){
			    $classes[] = 'mfn-builder-blocks';
			  }

			if( !empty($options['builder-blocks']) ) {

					// blocks

		    	$classes[] = 'mfn-builder-blocks';

				if( !empty($options['simple-view']) ){
			    	$classes[] = 'simple-view';
			    }

			    if( !empty($options['hover-effects']) ){
			    	$classes[] = 'hover-effects-disable';
			    }

		    } else {

				// visual

			    if( isset( $options['mfn-modern-nav'] ) && $options['mfn-modern-nav'] == '1' ) {
			    	$classes[] = 'mfn-modern-nav';
			    }

			    if( !empty($options['ui-theme']) ) {
			    	$classes[] = $options['ui-theme'];
			    }

			}



		    if( $post_type == 'template' && !empty(get_post_meta($post_id, 'mfn_template_type', true)) && get_post_meta($post_id, 'mfn_template_type', true) == 'checkout' ){
		    	$classes[] = 'woocommerce-checkout';
		    }

		    return $classes;
		}

	}

	// admin bar link
	add_action( 'admin_bar_menu', 'mfnvb_admin_bar_menu', 500 );

	function mfnvb_admin_bar_menu( $admin_bar ){

		$id = false;
		if( is_singular(['page','post','portfolio','product']) ){
			$id = get_the_ID();
			if( !empty( $_GET['mfn-header-template'] ) ){
				$id = $_GET['mfn-header-template'];
			}elseif( !empty( $_GET['mfn-footer-template'] ) ){
				$id = $_GET['mfn-footer-template'];
			}
		}

		if( !$id ) return false;

		$args = array(
		    'id' => ''. apply_filters('betheme_slug', 'mfn') .'-live-builder', // Must be a unique name
		    'title' => 'Edit with '. apply_filters('betheme_label', 'Be') .'Builder', // Label for this item
		    'href' => admin_url('/post.php?post='. $id .'&action='. apply_filters('betheme_slug', 'mfn') .'-live-builder'),
		);
		$admin_bar->add_menu( $args );

	}
}

mfn_visual_builder();

add_action( 'mfn_hook_bottom', 'mfn_ofc_sidebar' );
function mfn_ofc_sidebar(){
	get_template_part('includes/off-canvas-sidebar');
}
