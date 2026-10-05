<?php

/**
 * Theme Functions
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

define('MFN_THEME_VERSION', '28.4.4');

// theme related filters

add_filter('widget_text', 'do_shortcode');

add_filter('the_excerpt', 'shortcode_unautop');
add_filter('the_excerpt', 'do_shortcode');

/**
 * White Label
 * IMPORTANT: We recommend the use of Child Theme to change this
 */

defined('WHITE_LABEL') or define('WHITE_LABEL', false);

/**
 * textdomain
 */

function mfn_load_theme_textdomain() {
	// WPML: use action to allow String Translation to modify text
	load_theme_textdomain('betheme', get_template_directory() .'/languages'); // frontend
	load_theme_textdomain('mfn-opts', get_template_directory() .'/languages'); // admin panel
}
add_action('after_setup_theme', 'mfn_load_theme_textdomain');

/**
 * theme options
 */

require_once(get_theme_file_path('/muffin-options/theme-options.php'));

/**
 * theme functions
 */

$theme_disable = mfn_opts_get('theme-disable');

require_once(get_theme_file_path('/functions/modules/class-mfn-dynamic-data.php'));

require_once(get_theme_file_path('/functions/theme-functions.php'));
require_once(get_theme_file_path('/functions/theme-head.php'));
require_once(get_theme_file_path('/visual-builder/classes/front-template-view.php'));

/**
 * Global settings
 * */

function mfn_global() {

  global $mfn_global;

  $header_global = mfn_template_part_ID('header');
  $header_global = apply_filters( 'wpml_object_id', $header_global, get_post_type($header_global), TRUE );

  $footer_global = mfn_template_part_ID('footer');
  $footer_global = apply_filters( 'wpml_object_id', $footer_global , get_post_type($footer_global), TRUE );

  $sidebar_id = mfn_global_sidemenu_id();
  $sidebar_id = apply_filters( 'wpml_object_id', $sidebar_id , get_post_type($sidebar_id), TRUE );

  $mfn_front_tmpl = new Mfn_Template_View();

  $mfn_global = array(

  	'header' => $header_global,
  	'footer' => $footer_global,
  	'cart' => mfn_endpoint_tmpl('cart'),
  	'checkout' => mfn_endpoint_tmpl('checkout'),
  	'thank_you' => mfn_endpoint_tmpl('thanks'),
		'sidemenu' => $sidebar_id,
		'search' => get_option('mfn_search_template_used'),

		'singular_template' => $mfn_front_tmpl->get_singular_template(),
		'archive_template' => $mfn_front_tmpl->get_archive_template(),

  	/*'single_product' => mfn_single_product_tmpl(),
  	'shop_archive' => mfn_shop_archive_tmpl(),
  	'single_post' => mfn_single_post_ID( 'single-post' ),
  	'single_portfolio' => mfn_single_post_ID( 'single-portfolio' ),
  	//'blog' => mfn_archive_template_id('blog'),
  	'portfolio' => mfn_archive_template_id('portfolio')*/

  );

}
add_action( 'wp', 'mfn_global' );

if( is_admin() || apply_filters('is_bebuilder_demo', false)  ) {
	require_once(get_theme_file_path('/functions/admin/class-mfn-api.php'));
}

// menu

require_once(get_theme_file_path('/functions/theme-menu.php'));

if (! isset($theme_disable['mega-menu'])) {
	require_once(get_theme_file_path('/functions/theme-mega-menu.php'));
}

// builder

require_once(get_theme_file_path('/functions/builder/class-mfn-builder.php'));

// post types

$post_types_disable = mfn_opts_get('post-type-disable');

require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type.php'));

if (! isset($theme_disable['custom-icons'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-icons.php'));
}
if (! isset($post_types_disable['template'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-template.php'));
}
if (! isset($post_types_disable['client'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-client.php'));
}
if (! isset($post_types_disable['offer'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-offer.php'));
}
if (! isset($post_types_disable['portfolio'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-portfolio.php'));
}
if (! isset($post_types_disable['slide'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-slide.php'));
}
if (! isset($post_types_disable['testimonial'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-testimonial.php'));
}

if (! isset($post_types_disable['layout'])) {
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-layout.php'));
}

if(function_exists('is_woocommerce')){
	require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-product.php'));
}

require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-page.php'));
require_once(get_theme_file_path('/functions/post-types/class-mfn-post-type-post.php'));

// includes

require_once(get_theme_file_path('/includes/content-post.php'));
require_once(get_theme_file_path('/includes/content-portfolio.php'));

// shortcodes

require_once(get_theme_file_path('/functions/theme-shortcodes.php'));

// hooks

require_once(get_theme_file_path('/functions/theme-hooks.php'));

// sidebars

require_once(get_theme_file_path('/functions/theme-sidebars.php'));

// widgets

require_once(get_theme_file_path('/functions/widgets/class-mfn-widgets.php'));

// TinyMCE

require_once(get_theme_file_path('/functions/tinymce/tinymce.php'));

// plugins

require_once(get_theme_file_path('/functions/class-mfn-love.php'));
require_once(get_theme_file_path('/functions/plugins/visual-composer.php'));
require_once(get_theme_file_path('/functions/plugins/elementor/class-mfn-elementor.php'));

// gdpr

require_once(get_theme_file_path('/functions/modules/class-mfn-gdpr.php'));

// popup

require_once(get_theme_file_path('/functions/modules/class-mfn-popup.php'));

// sidemenu

require_once(get_theme_file_path('/functions/modules/class-mfn-sidemenu.php'));

// svg sanitizer

if( empty($theme_disable['svg-allow']) && ! class_exists('enshrined\svgSanitize\Sanitizer') ){
	require_once(get_theme_file_path('/functions/modules/svg-sanitizer/load.php'));
}

// conditional logic

require_once(get_theme_file_path('/visual-builder/classes/conditional-logic.php'));

// WooCommerce functions

if (function_exists('is_woocommerce')) {
	require_once(get_theme_file_path('/functions/theme-woocommerce.php'));
}

// pagination

require_once(get_theme_file_path('/functions/modules/class-mfn-query-pagination.php'));

// dashboard

if ( is_admin() || apply_filters('is_bebuilder_demo', false) ) {

	$bebuilder_access = apply_filters('bebuilder_access', false);

	require_once(get_theme_file_path('/functions/admin/class-mfn-helper.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-update.php'));

	require_once(get_theme_file_path('/functions/admin/class-mfn-dashboard.php'));
	$mfn_dashboard = new Mfn_Dashboard();

	require_once(get_theme_file_path('/functions/importer/class-mfn-importer-helper.php'));

	require_once(get_theme_file_path('/functions/admin/setup/class-mfn-setup.php'));
	require_once(get_theme_file_path('/functions/importer/class-mfn-importer.php'));

	require_once(get_theme_file_path('/functions/admin/tgm/class-mfn-tgmpa.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-plugins.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-status.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-support.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-changelog.php'));
	require_once(get_theme_file_path('/functions/admin/class-mfn-tools.php'));

	if( $bebuilder_access ){
		require_once(get_theme_file_path('/visual-builder/visual-builder.php'));
	}

}

/**
 * @deprecated 21.0.5
 * Below constants are deprecated and will be removed soon
 * Please check if you use these constants in your Child Theme
 */

define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

define('THEME_NAME', 'betheme');
define('THEME_VERSION', MFN_THEME_VERSION);

define('LIBS_DIR', get_template_directory() .'/functions');
define('LIBS_URI', get_template_directory() .'/functions');
