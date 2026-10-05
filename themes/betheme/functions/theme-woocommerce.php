<?php
/**
 * WooCommerce functions.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */


/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

/**
* WooCommerce | Theme support & actions
*/

function mfn_woo_support() {

	$theme_support_attr = false;

	// featured image

	$shop_images = mfn_opts_get( 'shop-images' );
	if( 'plugin' !== $shop_images ){

		// use Customizer instead

		$thumbnail_image_width = mfn_opts_get( 'shop-image-width', 800 );
		$single_image_width = mfn_opts_get( 'single-product-main-image-size', 800 );

		$theme_support_attr = [
			'thumbnail_image_width' => $thumbnail_image_width,
			'single_image_width' => $single_image_width,
		];
	}

	// add theme support

	add_theme_support( 'woocommerce', $theme_support_attr );

	// single product gallery thumbnails

	add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size) {

		$gallery_image_width = mfn_opts_get( 'single-product-thumbnails-size');

		if( !empty($gallery_image_width) ) {
			return array(
				'width'  => $gallery_image_width,
				'height' => $gallery_image_width,
				'crop'   => 1,
			);
		}

		return $size;

  });

}
add_action( 'after_setup_theme', 'mfn_woo_support' );
add_action( 'wp_footer', 'woocommerce_demo_store', 10 );

// WooCommerce 2.7+ single product gallery

add_theme_support('wc-product-gallery-zoom');
add_theme_support('wc-product-gallery-lightbox');
add_theme_support('wc-product-gallery-slider');

/**
 * WooCommerce | Actions | Remove
 */

if( get_option('woocommerce_enable_ajax_add_to_cart') == 'yes' ) {
	add_filter( 'wc_add_to_cart_message_html', '__return_false' );
}

remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_main_content', 'WC_Structured_Data::generate_website_data', 30);

remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);

remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

if ( mfn_opts_get('shop-catalogue') ) {
	// add_filter( 'woocommerce_is_purchasable', '__return_false');
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}

/**
 * WooCommerce | Actions | Add
 */

function mfn_woocommerce_product_reviews_tab_title( $title ){
	$title = str_replace( '(', '<span class="number">', $title );
	$title = str_replace( ')', '</span>', $title );
	return $title;
}
add_filter( 'woocommerce_product_reviews_tab_title', 'mfn_woocommerce_product_reviews_tab_title' );

function mfn_woocommerce_before_quantity_input_field(){
	echo '<a href="#" class="quantity-change minus" aria-label="decrease quantity"><i class="icon-minus"></i></a>';
}
add_action( 'woocommerce_before_quantity_input_field', 'mfn_woocommerce_before_quantity_input_field' );

function mfn_woocommerce_after_quantity_input_field(){
	echo '<a href="#" class="quantity-change plus" aria-label="increase quantity"><i class="icon-plus"></i></a>';
}
add_action( 'woocommerce_after_quantity_input_field', 'mfn_woocommerce_after_quantity_input_field' );

add_filter( 'woocommerce_product_description_heading', '__return_false' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );

/**
 * SVG icons in notices
 */

function mfn_woocommerce_kses_notice_allowed_tags( $allowed_tags ){

	$svg_args = [
		'svg' => [
			'viewbox' => true,
		],
		'defs' => true,
		'style' => true,
		'g' => true,
		'circle' => [
			'cx' => true,
			'cy' => true,
			'r' => true,
			'class' => true,
		],
		'line' => [
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
			'class' => true,
		],
		'path' => [
			'd' => true,
			'class' => true,
		],
		'polyline' => [
			'points' => true,
			'class' => true,
		],
	];

	$allowed_tags = array_merge( $allowed_tags, $svg_args );

	return $allowed_tags;
}
add_filter( 'woocommerce_kses_notice_allowed_tags', 'mfn_woocommerce_kses_notice_allowed_tags' );

/**
 * Action | Empty cart message
 */

if (! function_exists('mfn_wc_empty_cart_message')) {
	function mfn_wc_empty_cart_message()
	{ ?>
			<div class="cart-empty">
				<p class="cart-empty-icon"><svg width="26" viewBox="0 0 26 26" aria-hidden="true"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"></polygon><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"></path></svg></p>
				<p><?php _e('Your cart is currently empty.', 'woocommerce'); ?></p>
			</div>
		<?php
	}
}
// add_action('woocommerce_cart_is_empty', 'mfn_wc_empty_cart_message', 10);

/**
 * Filter | Not enough stock already in cart
 */

function mfn_woocommerce_cart_product_not_enough_stock_already_in_cart_message( $message, $product_data, $stock_quantity, $stock_quantity_in_cart ){

	$message = sprintf(
		'%s <a href="%s" class="separated">%s</a> ',
		/* translators: 1: quantity in stock 2: current quantity */
		sprintf( __( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_quantity, $product_data ), wc_format_stock_quantity_for_display( $stock_quantity_in_cart, $product_data ) ),
		wc_get_cart_url(),
		__( 'View cart', 'woocommerce' )
	);

	return $message;

}
add_filter('woocommerce_cart_product_not_enough_stock_already_in_cart_message','mfn_woocommerce_cart_product_not_enough_stock_already_in_cart_message', 10, 4 );

/**
 * WooCommerce | Styles
 */

if (! function_exists('mfn_woo_styles')) {
	function mfn_woo_styles() {

		$min_css = '';
		$min_js = '';

		$performance_minify_css = mfn_opts_get('minify-css','');
		$performance_minify_js = mfn_opts_get('minify-js','');

		if( $performance_minify_css ) {
			$min_css = '.min';
		}

		if( $performance_minify_js ) {
			$min_js = '.min';
		}

		$lightbox_options = mfn_opts_get('prettyphoto-options');
		if ( ! isset( $lightbox_options[ 'disable-swiper' ] ) ) {
			wp_enqueue_script('mfn-swiper', get_theme_file_uri('/js/swiper.js'), array('jquery'), MFN_THEME_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
	    wp_enqueue_style('mfn-swiper', get_theme_file_uri('/css/scripts/swiper.css'), false, MFN_THEME_VERSION, false);
		}

		wp_enqueue_script( 'wc-cart-fragments' );

		wp_enqueue_style('mfn-woo', get_theme_file_uri('/css/woocommerce'. $min_css .'.css'), 'woocommerce-general-css', MFN_THEME_VERSION, 'all');

		wp_enqueue_script('mfn-imagesloaded', get_theme_file_uri('/js/plugins/imagesloaded.min.js'), ['jquery'], MFN_THEME_VERSION, true);
		wp_enqueue_script('mfn-slick', get_theme_file_uri('/js/plugins/slick.min.js'), ['jquery'], MFN_THEME_VERSION, true);
		wp_enqueue_script('mfn-woojs', get_theme_file_uri('/js/woocommerce'. $min_js .'.js'), ['jquery'], MFN_THEME_VERSION, true);

		if( mfn_opts_get('shop-quick-view') == 1 ) wp_enqueue_script('wc-add-to-cart-variation');

		if( isset($_GET['mfn-demo-product-gallery-overlay']) ) {
			$gallery_overlay = 'mfn-thumbnails-'. $_GET['mfn-demo-product-gallery-overlay']; // demo only
		} else {
			$gallery_overlay = mfn_opts_get('shop-product-gallery-overlay');
		}

		if( isset($_GET['mfn-demo-product-gallery-overlay']) && 'overlay' == $_GET['mfn-demo-product-gallery-overlay'] ) {
			$thumbnails_margin = '15px'; // demo only
			$main_margin = 'mfn-mim-15';
		} else {
			$thumbnails_margin = mfn_opts_get( 'shop-product-thumbnails-margin', 0, ['unit'=>'px'] );
			$main_margin = mfn_opts_get( 'shop-product-main-image-margin', 'mfn-mim-0' );
		}

		wp_localize_script( 'mfn-woojs', 'mfnwoovars',
      array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'wpnonce' => wp_create_nonce( 'mfn-woo-nonce' ),
        'rooturl' => get_home_url(null, '', 'relative'),
        'productthumbsover' => $gallery_overlay,
        'productthumbs' => $thumbnails_margin,
        'mainimgmargin' => $main_margin,
        'myaccountpage' => get_permalink( get_option('woocommerce_myaccount_page_id') ) ?? '/',
				'groupedQuantityErrori18n' => esc_html__( 'Please choose the quantity of items you wish to add to your cart…', 'betheme' ),
      )
    );

	}
}
add_action('wp_enqueue_scripts', 'mfn_woo_styles');

function mfn_recaptcha_enqueue_script() {
	wp_enqueue_script( 'mfn-google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
}

function mfn_recaptcha_enqueue_style() {
	$min_css = '';
	$performance_minify_css = mfn_opts_get('minify-css','');
	if( $performance_minify_css ){
		$min_css = '.min';
	}
	wp_enqueue_style('mfn-login-styles', get_theme_file_uri('/css/login-page'. $min_css .'.css'), 'login-page', MFN_THEME_VERSION, 'all');
}

function mfn_admin_scripts() {
	if( is_admin() && function_exists('is_woocommerce') ) {
        wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
 		wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, 1 );
    }
}
add_action( 'admin_enqueue_scripts', 'mfn_admin_scripts' );

// WooCommerce 3.0+ | Image size

if (! function_exists('mfn_woocommerce_get_image_size_gallery_thumbnail')) {
	function mfn_woocommerce_get_image_size_gallery_thumbnail()
	{
		return array(
			'width' => 300,
			'height' => 300,
			'crop' => 1,
		);
	}
}
add_filter('woocommerce_get_image_size_gallery_thumbnail', 'mfn_woocommerce_get_image_size_gallery_thumbnail');

/**
 *	WooCommerce | Products per line/page
 */

function mfn_woo_loop_shop_columns()
{
	return 3;
}

add_filter('loop_shop_columns', 'mfn_woo_loop_shop_columns', 20);

/**
 *	WooCommerce | Overrides Elementor
 */

function mfn_theme_needs_template_override( $need_override_location, $location ) {
	$tmp_id = mfn_ID();

	if ( isset($tmp_id) && is_numeric($tmp_id) && get_post_type($tmp_id) == 'template' && ( get_post_status($tmp_id) == 'publish' || !empty( $_GET['visual'] ) ) ) {
		$need_override_location = false;
	}
	return $need_override_location;
}
add_filter( 'elementor/theme/need_override_location', 'mfn_theme_needs_template_override', 11, 2 );

/**
 *	WooCommerce | Woo classess if preview template
 */

add_filter( 'body_class','woo_template_body_classes' );
function woo_template_body_classes( $classes ) {
	$tmp_id = mfn_ID();
	$mfn_front_tmpl = new Mfn_Template_View();

 	if( is_singular('template') && in_array( get_post_meta(get_the_ID(), 'mfn_template_type', true), array('shop-archive', 'archive-product', 'single-product') ) ) {
    $classes[] = 'woocommerce';
  }

  if ( is_product() ) {
  	$product = wc_get_product( get_the_ID() );
  	if(!$product->managing_stock()) $classes[] = 'stock-disabled';
  	if( !comments_open( $product->get_id() ) ) $classes[] = 'reviews-disabled';
	}

	if(mfn_opts_get('shop-wishlist')) {
		$classes[] = 'wishlist-active';
	}

	if( is_woocommerce() ){
		$shop_archive = $mfn_front_tmpl->get_archive_template();

		if( empty(get_post_meta($shop_archive, 'mfn-shop-list-active-filters', true)) && empty($_GET['visual']) && empty(mfn_opts_get('shop-list-active-filters')) && empty(mfn_opts_get('shop-list-perpage')) && empty(mfn_opts_get('shop-list-layout')) && empty(mfn_opts_get('shop-list-sorting')) && empty(mfn_opts_get('shop-list-results-count')) ) {
			$classes[] = 'mfn-all-shop-filters-disabled';
		}
	}

	if( get_theme_support( 'wc-product-gallery-zoom' ) ) {
		$classes[] = 'product-gallery-zoom';
	}

	$wishlist_position = mfn_opts_get('shop-wishlist-position');
	if( isset($wishlist_position[0]) ) {
		$classes[] = 'wishlist-button';
	}

	if(mfn_opts_get('mobile-products-row') == 2) {
		$classes[] = 'mobile-row-2-products';
	}

	if(mfn_opts_get('variable-swatches') == 1) {
		$classes[] = 'mfn-variable-swatches';
	}

	if( mfn_opts_get('shop-catalogue') ) {
		$classes[] = 'shop-catalogue';
		if( 'hide-price' == mfn_opts_get('shop-catalogue') ) {
			$classes[] = 'shop-hide-price';
		}
	}

	if( mfn_opts_get('shop-icon-count-if-zero') == 0 ) {
		$classes[] = 'mfn-hidden-icon-count';
	}

	if( ('disable-zoom' == mfn_opts_get('shop-single-image') ) || (isset($tmp_id) && is_numeric($tmp_id) && get_post_status($tmp_id) == 'publish' && get_post_type($tmp_id) == 'template' && get_post_meta($tmp_id, 'mfn_template_product_image_zoom', true) == 0 ) ) {
		$classes[] = 'product-zoom-disabled';
	}

	if( mfn_opts_get('sticky-shop-menu') == 1 ) {
		$classes[] = 'footer-menu-sticky';
	}

	if( mfn_opts_get('shop-sidecart') == 1 ) {
		$classes[] = 'shop-sidecart-active';
	}

	if( get_option('woocommerce_enable_ajax_add_to_cart') == 'yes') {
		$classes[] = 'mfn-ajax-add-to-cart';
	}

	if( mfn_opts_get('shop-product-cart-button-extra') == 1 ) {
		$classes[] = 'mfn-cart-button-wrap';
	}

  return $classes;
}

add_action( 'mfn_hook_bottom', 'mfn_footer_content' );

function mfn_footer_content(){
	if( mfn_opts_get('sticky-shop-menu') == 1 && function_exists('is_woocommerce') ){
		get_template_part('includes/footer-stickymenu');
	}
}

/**
 *	WooCommerce | Change number of related products on product page
 */

if (! function_exists('mfn_woo_related_products_args')) {
	function mfn_woo_related_products_args($args)
	{
		$args['posts_per_page'] = intval(mfn_opts_get('shop-related', 3));
		return $args;
	}
}
add_filter('woocommerce_output_related_products_args', 'mfn_woo_related_products_args');

/**
 *	WooCommerce | Ensure cart contents update when products are added to the cart via AJAX
 */

if ( ! function_exists( 'woocommerce_header_add_to_cart_fragment' ) ) {
	function woocommerce_header_add_to_cart_fragment( $fragments ) {
		$total = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

		// Header count
		ob_start();
		echo '<span class="header-cart-count mfn-header-icon-' . esc_attr( $total ) . '">' . esc_html( $total ) . '</span>';
		$fragments['.header-cart-count'] = ob_get_clean();

		// Header subtotal
		ob_start();
		echo '<p class="header-cart-total">' . wp_kses_post( WC()->cart->get_cart_subtotal() ) . '</p>';
		$fragments['.header-cart-total'] = ob_get_clean();

		// Sidecart content
		ob_start();
		mfn_get_woo_sidecart_content();
		$fragments['.mfn-cart-holder .mfn-ch-content'] = ob_get_clean();

		// Sidecart footer/totals
		ob_start();
		mfn_get_woo_sidecart_footer();
		$fragments['.mfn-cart-holder .mfn-ch-footer .mfn-ch-footer-totals'] = ob_get_clean();

		// Optional: a dedicated count hook if your theme uses count in more than one place
		ob_start();
		echo esc_html( $total );
		$fragments['.mfn-cart-count-value'] = ob_get_clean();

		return $fragments;
	}
}

add_filter('woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');



/**
 *	WooCommerce | Excerpt in loop
 */

add_action( 'woocommerce_after_shop_loop_item_title', 'mfn_append_excerpt_loop', 5 );

function mfn_append_excerpt_loop(){
	global $product;
	$excerpt = mfn_opts_get( 'shop-excerpt' );
	if( $excerpt ){
		echo '<div class="excerpt excerpt-'. esc_attr($excerpt) .'">'. apply_filters( 'woocommerce_short_description', get_the_excerpt( $product->get_id() ) ) .'</div>';
	}
}

/**
 *	WooCommerce | Wishlist
 */

$wishlist_position = mfn_opts_get('shop-wishlist-position');

if( mfn_opts_get('shop-wishlist') && isset($wishlist_position[0]) ) {
	add_action( 'woocommerce_after_add_to_cart_button', 'mfn_append_wishlist_button' );
	add_action( 'woocommerce_after_shop_loop_item', 'mfn_append_wishlist_button' );
	add_action( 'woocommerce_after_add_to_cart_quantity', 'mfn_wishlist_button_wrapper_open');
}

function mfn_append_wishlist_button() {
	global $product;

	$translate['translate-add-to-wishlist'] = mfn_opts_get('translate') ? mfn_opts_get('translate-add-to-wishlist', 'Add to wishlist') : __('Add to wishlist', 'betheme');

	$wish_type = mfn_opts_get('shop-wishlist-type');
	$w_classes = array('mfn-wish-button');
	$w_b_label = false;

	if( !empty( Mfn_Builder_Front::$item_obj['jsclass'] ) && Mfn_Builder_Front::$item_obj['jsclass'] == 'product_cart_button' && is_product() && !empty($wish_type) && $wish_type == 'button' ) {
		$w_classes[] = 'button';
		$w_b_label = !empty(mfn_opts_get('shop-button-label-wishlist')) ? mfn_opts_get('shop-button-label-wishlist') : $translate['translate-add-to-wishlist'];
	}

	echo '<a href="#" data-id="'.$product->get_id().'" class="'.implode(' ', $w_classes).'"><svg width="26" viewBox="0 0 26 26" aria-label="'.$translate['translate-add-to-wishlist'].'"><defs><style>.path{fill:none;stroke:#333;stroke-width:1.5px;}</style></defs><path class="path" d="M16.7,6a3.78,3.78,0,0,0-2.3.8A5.26,5.26,0,0,0,13,8.5a5,5,0,0,0-1.4-1.6A3.52,3.52,0,0,0,9.3,6a4.33,4.33,0,0,0-4.2,4.6c0,2.8,2.3,4.7,5.7,7.7.6.5,1.2,1.1,1.9,1.7H13a.37.37,0,0,0,.3-.1c.7-.6,1.3-1.2,1.9-1.7,3.4-2.9,5.7-4.8,5.7-7.7A4.3,4.3,0,0,0,16.7,6Z"></path></svg>'.( !empty( Mfn_Builder_Front::$item_obj['jsclass'] ) && Mfn_Builder_Front::$item_obj['jsclass'] == 'product_cart_button' && is_product() && in_array('button', $w_classes) && $w_b_label ? $w_b_label : '').'</a>';

	if( !empty( Mfn_Builder_Front::$item_obj['jsclass'] ) && Mfn_Builder_Front::$item_obj['jsclass'] == 'product_cart_button' && is_product() && in_array('button', $w_classes) && $w_b_label ) echo '</div>'; // close div wrapper opened before addto cert button

}

function mfn_wishlist_button_wrapper_open() {
	$wish_type = mfn_opts_get('shop-wishlist-type');
	if( !empty( Mfn_Builder_Front::$item_obj['jsclass'] ) && Mfn_Builder_Front::$item_obj['jsclass'] == 'product_cart_button' && is_product() && !empty($wish_type) && $wish_type == 'button' ) {
		echo '<div class="mfn-add-to-cart-buttons-wrapper">';
	}
}




/**
 * WooCommerce | Additional Attributes Fields
 */

function mfn_woo_attr_types() {
	return array( 'select', 'label', 'color', 'image' );
}

/**
 * WooCommerce | Additional Attributes Fields
 */

function mfn_action_woocommerce_after_attr_form() {
	if( mfn_opts_get('variable-swatches') == 0 ){
		return;
	}
	$value = 'select';
	$types = mfn_woo_attr_types();
	$field_name = 'mfn_attr_display_type';
	$field_label = 'Display Type';
	if( !empty($_GET['edit']) ){
		$taxonomies = wc_get_attribute_taxonomies();
		if(isset($taxonomies) && count($taxonomies) > 0){
			foreach($taxonomies as $tx){
				if($tx->attribute_id == $_GET['edit']) $value = $tx->attribute_type;
			}
		}
		$show_in_loop = get_option('attr_loop_'.$_GET['edit']);
		echo '<tr class="form-field"><th valign="top" scope="row"><label for="mfn_attr_display">'.$field_label.'</label></th><td><select id="mfn_attr_display" name="'.$field_name.'">';
	    foreach($types as $t){ echo '<option '.( isset($value) && $value == $t ? "selected" : null ).' value="'.$t.'">'.ucfirst($t).'</option>'; }
	    echo '</select></td></tr>';

	    echo '<tr class="form-field"><th valign="top" scope="row"><label for="mfn_attribute_showloop"><input name="mfn_attribute_showloop" id="mfn_attribute_showloop" '.( $show_in_loop && $show_in_loop == 1 ? "checked" : null ).' type="checkbox" value="1"> Show in loop?</label></th><td><p class="description">Enable this if you want to display this attribute in products archives.</p></td></tr>';
	}else{
    	echo '<div class="form-field"><label for="mfn_attr_display">'.$field_label.'</label><select id="mfn_attr_display" name="'.$field_name.'">';
        foreach($types as $t){ echo '<option '.( isset($value) && $value == $t ? "selected" : null ).' value="'.$t.'">'.ucfirst($t).'</option>'; }
       	echo '</select></div>';
       	echo '<div class="form-field"><label for="mfn_attribute_showloop"><input name="mfn_attribute_showloop" id="mfn_attribute_showloop" type="checkbox" value="1"> Show in loop?</label><p class="description">Enable this if you want to display this attribute in products archives.</p></div>';
	}
}

add_action( 'woocommerce_after_edit_attribute_fields', 'mfn_action_woocommerce_after_attr_form', 10, 0 );
add_action( 'woocommerce_after_add_attribute_fields', 'mfn_action_woocommerce_after_attr_form' );

/**
 * WooCommerce | Additional Attributes Fields Save
 */

function mfn_save_attr_display_type( $id ) {

	if( mfn_opts_get('variable-swatches') == 0 ){
		return;
	}

	global $wpdb;
    if ( is_admin() && isset( $_POST['mfn_attr_display_type'] ) && in_array( $_POST['mfn_attr_display_type'], array('select', 'label', 'color', 'image') ) ) {
        $wpdb->update(
        	$wpdb->prefix . 'woocommerce_attribute_taxonomies',
        	array( 'attribute_type' => $_POST['mfn_attr_display_type'] ),
        	array( 'attribute_id' => $id ),
        	array('%s'),
        	array('%d')
        );
        if( !empty($_POST['mfn_attribute_showloop']) ){
        	update_option( 'attr_loop_'.$id, '1');
        }else{
        	delete_option( 'attr_loop_'.$id );
        }
    }
}

add_action( 'woocommerce_attribute_deleted', 'mfn_woo_attribute_deleted', 10, 3 );

function mfn_woo_attribute_deleted( $attribute_id ) {
    delete_option( 'attr_loop_'.$attribute_id );
};

add_action( 'woocommerce_attribute_added', 'mfn_save_attr_display_type' );
add_action( 'woocommerce_attribute_updated', 'mfn_save_attr_display_type' );

/**
 * WooCommerce | Display Attributes
*/

if ( ! mfn_opts_get('shop-catalogue') ) {
	add_action( 'woocommerce_after_shop_loop_item_title',  'mfn_display_custom_attributes_loop', 5 );
}

add_action( 'woocommerce_before_variations_form', 'mfn_display_custom_attributes_single' );

function mfn_display_custom_attributes_single(){
	global $product;
	mfn_display_custom_attributes($product, true);
}

function mfn_display_custom_attributes_loop($p = false){
	global $product;
	/*if($p){
		$product = wc_get_product( $p );
	}else{
		$product = wc_get_product( get_the_ID() );
	}*/
	mfn_display_custom_attributes($product, false);
}

function mfn_display_custom_attributes($p, $show = false){
	if( !mfn_opts_get('variable-swatches') || mfn_opts_get('shop-catalogue') ) return;

	$product = wc_get_product( $p );
	$product_attributes = $product->get_attributes();

	if ( $product->is_type( 'variable' ) ):

	// prevents empty variations
	if( isset($product_attributes) && is_iterable($product_attributes) ){
		foreach ($product_attributes as $prodatr) {
			if( isset( $prodatr['options'] ) && count($prodatr['options']) == 0 ){
				return false;
			}
		}
	}

	$taxonomies = wc_get_attribute_taxonomies();

	$class = 'mfn-variations-wrapper-loop';
	if( $show ) {
		$class = 'mfn-variations-wrapper';
	}

	$display_arr = get_post_meta( $product->get_id(), '_product_attributes', true );

	echo '<div class="'.$class.'">';

	if(isset($display_arr) && is_iterable($display_arr)){

	foreach($display_arr as $a=>$atr){

		if( !$atr['is_variation'] ) continue;

		if( !is_product() && empty($atr['is_taxonomy']) ) continue;

		$loop_enabled = 0;
		$display_type = 'select';


		$atr_slug = str_replace('attribute_', '', $a);
		$atr_id = wc_attribute_taxonomy_id_by_name( $atr_slug );

		if( $atr['is_taxonomy'] == 1 ){

			if( !$atr_id ) continue;

			// if not custom
			if(isset($taxonomies) && count($taxonomies) > 0) {
				foreach($taxonomies as $tx) {
					if($tx->attribute_id == $atr_id) {
						$display_type = $tx->attribute_type;
						$loop_enabled = get_option( 'attr_loop_'.$tx->attribute_id );
					}
				}
			}

			if( !$show && $loop_enabled == 0 ) continue;

			if( empty($atr[0]) ){
				$atr = wc_get_product_terms( $product->get_id(), $atr['name'], array( 'fields' => 'names' ));
			}

		}else if( isset($atr['value']) && !empty($atr['value']) ) {
			$atr = explode('|', $atr['value']);
		}

		echo '<div class="mfn-vr">';
			echo '<label>'.wc_attribute_label($atr_slug, $product).'</label>';
			switch ($display_type) {
				case 'label':
					echo '<ul class="mfn-vr-options attribute_'.$atr_slug.' mfn-vr-labels" data-atr="'.$atr_slug.'">';
						foreach($atr as $item) {
							$atr_item = get_term_by('slug', $item, $atr_slug);
							if( !isset($atr_item->name) ) $atr_item = get_term_by('name', $item, $atr_slug);

							if(isset($atr_item->name)) {
							echo '<li class="attr_'.esc_attr($atr_item->slug).'"><a href="'.get_the_permalink($product->get_id()).'?'.$a.'='.$atr_item->slug.'" data-id="'.esc_attr($atr_item->slug).'">'.esc_html($atr_item->name).'</a></li>';
							}
						}
					echo '</ul>';
					break;
				case 'color':
					echo '<ul class="mfn-vr-options attribute_'.$atr_slug.' mfn-vr-color" data-atr="'.$atr_slug.'">';
						foreach($atr as $i=>$item){
							$attr_helper = array();
							if( $i == 0 && !empty(Mfn_Builder_Front::$item_obj['attr']['heading_variations_tooltip_first_pos']) ) {
								$attr_helper[] = 'data-position="'.Mfn_Builder_Front::$item_obj['attr']['heading_variations_tooltip_first_pos'].'"';
							}
							$atr_item = get_term_by('slug', $item, $atr_slug);
							if( !isset($atr_item->name) ) $atr_item = get_term_by('name', $item, $atr_slug);
							if(isset($atr_item->name)){
							$mfn_value = get_term_meta($atr_item->term_id, 'mfn_attr_field', true);
							//if( !isset($mfn_value) || empty($mfn_value) || ( isset($mfn_value) && strpos('#', $mfn_value) === false ) ) $mfn_value = ''; // no color
							echo '<li class="attr_'.esc_attr($atr_item->slug).' tooltip tooltip-txt" '.implode(' ', $attr_helper).' data-tooltip="'.esc_html($atr_item->name).'"><a href="'.get_the_permalink($product->get_id()).'?'.$a.'='.$atr_item->slug.'" data-id="'.$atr_item->slug.'"><span style="background-color: '.$mfn_value.';"></span></a></li>';
							}
						}
					echo '</ul>';
					break;
				case 'image':
					echo '<ul class="mfn-vr-options attribute_'.$atr_slug.' mfn-vr-image" data-atr="'.$atr_slug.'">';
						foreach($atr as $i=>$item){
							$attr_helper = array();
							if( $i == 0 && !empty(Mfn_Builder_Front::$item_obj['attr']['heading_variations_tooltip_first_pos']) ) {
								$attr_helper[] = 'data-position="'.Mfn_Builder_Front::$item_obj['attr']['heading_variations_tooltip_first_pos'].'"';
							}
							$atr_item = get_term_by('slug', $item, $atr_slug);
							if( !isset($atr_item->name) ) $atr_item = get_term_by('name', $item, $atr_slug);
							if(isset($atr_item->name)){
							$mfn_value = get_term_meta($atr_item->term_id, 'mfn_attr_field', true);
							echo '<li class="attr_'.esc_attr($atr_item->slug).' tooltip tooltip-txt" '.implode(' ', $attr_helper).' data-tooltip="'.esc_html($atr_item->name).'"><a href="'.get_the_permalink($product->get_id()).'?'.$a.'='.$atr_item->slug.'" data-id="'.$atr_item->slug.'">'.wp_get_attachment_image($mfn_value, 'thumbnail').'</a></li>';
							}
						}
					echo '</ul>';
					break;
				default:
					echo '<select class="mfn-vr-select attribute_'.$atr_slug.'" data-atr="'.$atr_slug.'">';
						echo '<option data-link="" value="">'.__('Choose an option', 'woocommerce').'</option>';
						foreach($atr as $item){
							$atr_item = get_term_by('slug', $item, $atr_slug);
							if( !isset($atr_item->name) ) $atr_item = get_term_by('name', $item, $atr_slug);
							if(isset($atr_item->name)){
								echo '<option data-link="'.get_the_permalink($product->get_id()).'?'.$a.'='.$atr_item->slug.'" value="'.esc_attr($atr_item->slug).'">'.esc_html($atr_item->name).'</option>';
							}else{
								echo '<option data-link="'.get_the_permalink($product->get_id()).'?'.$a.'='.$item.'" value="'.trim(esc_attr($item)).'">'.esc_html($item).'</option>';
							}
						}
					echo '</select>';
					break;
			}
		echo '</div>';
	}

	if( $show ) {
		echo '<a href="#" class="mfn-reset-variations" style="display: none;">'.__("Reset", "woocommerce").'</a>';
	}

	}
	echo '</div>';

	endif;
}

/**
 * WooCommerce | Configure Terms
 */

add_action('admin_init', 'mfn_add_product_taxonomy_meta');

function mfn_add_product_taxonomy_meta(){
	if( mfn_opts_get('variable-swatches') == 0 ){
		return;
	}
	$attr_taxonomies = wc_get_attribute_taxonomies();
	if(count($attr_taxonomies) > 0){
		foreach($attr_taxonomies as $attr){
			if( in_array($attr->attribute_type, array('color', 'image') )){
				add_action( 'pa_'.$attr->attribute_name.'_edit_form_fields', 'mfn_edit_tax_attr_form_fields' );
				add_action( 'pa_'.$attr->attribute_name.'_add_form_fields', 'mfn_edit_tax_attr_form_fields' );

				add_action( 'saved_pa_'.$attr->attribute_name, 'mfn_saved_product_attr' );
				add_action( "create_pa_".$attr->attribute_name, 'mfn_saved_product_attr' );
			}
		}
	}
}

function mfn_edit_tax_attr_form_fields ($tag) {

	if( mfn_opts_get('variable-swatches') == 0 ) {
		return;
	}

	$current_value = '';

	if(isset( $tag->taxonomy )) {
		$current = $tag->taxonomy;
		$current_value = get_term_meta($tag->term_id, 'mfn_attr_field', true);
	}else{
		$current = $tag;
	}

	$placeholder_url = get_theme_file_uri( '/muffin-options/svg/placeholders/image.svg' );
	wp_enqueue_media();

    $attr_taxonomies = wc_get_attribute_taxonomies();
	if(count($attr_taxonomies) > 0){ foreach($attr_taxonomies as $attr){ if( $attr->attribute_name == str_replace('pa_', '', $current) ){ $current_obj = $attr; } } }

    $field_label = 'Choose '.$current_obj->attribute_type;
    $field_name = 'mfn_tax_field_'.$current_obj->attribute_type;

    if(isset( $tag->taxonomy )){ ?>
	<tr class="form-field mfn-tax-image">
        <th valign="top" scope="row"><label for="mfn_tax_field"><?php echo $field_label; ?></label></th>
        <td><input type="<?php echo $current_obj->attribute_type == 'color' ? 'text' : 'hidden'; ?>" id="mfn_tax_field" value="<?php echo $current_value; ?>" name="mfn_tax_field" class="<?php echo $field_name; ?>" required>
        	<?php if($current_obj->attribute_type == 'image'){
        		$current_value = wp_get_attachment_url($current_value); ?>
				<div class="mfn-custom-img-container">
				    <img data-src="<?php echo $placeholder_url; ?>" src="<?php if ( $current_value ) : echo $current_value; else: echo $placeholder_url; endif; ?>" alt="" style="max-width:100%;" />
					<a class="upload-custom-img button" href="#"><?php _e('Set custom image') ?></a>
					<a class="delete-custom-img button <?php if ( ! $current_value ) { echo 'hidden'; } ?>" href="#"><?php _e('Remove image') ?></a>
				</div>
			<?php } ?>
        </td>
    </tr>
    <?php
	}else{ ?>
		<div class="form-field mfn-tax-image">
	        <label for="mfn_tax_field"><?php echo $field_label; ?></label>
	        <input type="<?php echo $current_obj->attribute_type == 'color' ? 'text' : 'hidden'; ?>" id="mfn_tax_field" value="<?php echo $current_value; ?>" name="mfn_tax_field" class="<?php echo $field_name; ?>" required>
	        <?php if($current_obj->attribute_type == 'image'){
	        	$current_value = wp_get_attachment_url($current_value); ?>
				<div class="mfn-custom-img-container">
				    <img data-src="<?php echo $placeholder_url; ?>" src="<?php if ( $current_value ) : echo $current_value; else: echo $placeholder_url; endif; ?>" alt="" style="max-width:100%;" />
					<a class="upload-custom-img button <?php if ( $current_value  ) { echo 'hidden'; } ?>" href="#"><?php _e('Set custom image') ?></a>
					<a class="delete-custom-img button <?php if ( ! $current_value ) { echo 'hidden'; } ?>" href="#"><?php _e('Remove image') ?></a>
				</div>
			<?php } ?>
    	</div>
	<?php }
}

function mfn_saved_product_attr($term_id){
	if( mfn_opts_get('variable-swatches') == 0 ){
		return;
	}

	if( isset( $_POST['mfn_tax_field']) ){
		update_term_meta( $term_id, 'mfn_attr_field', $_POST['mfn_tax_field'] );
	}
}






function mfn_get_woo_sidecart_content() {

	if ( ! WC()->cart ) {
		return;
	}

	echo '<div class="mfn-ch-row mfn-ch-content">';

	if ( WC()->cart->get_cart() ) {

		do_action( 'mfn_get_woo_sidecart_before_content' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$classes = array( 'mfn-ch-product' );
			if ( isset( $cart_item['mnm_container'] ) ) {
				$classes[] = 'mfn-sidecart-subproduct';
			}

			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-row-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<div class="mfn-chp-col mfn-chp-image">
					<?php
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					echo '<a href="' . esc_url( $_product->get_permalink() ) . '">' . $thumbnail . '</a>';
					?>
				</div>
				<div class="mfn-chp-col mfn-chp-info">
					<p class="mfn-chp-name"><a href="<?php echo esc_url( $_product->get_permalink() ); ?>"><?php echo esc_html( $_product->get_name() ); ?></a></p>
					<?php
					do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
					echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<p class="price">
						<?php esc_html_e( 'Price', 'woocommerce' ); ?>:
						<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
				</div>
				<div class="mfn-chp-col align_right mfn-chp-price">
					<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="mfn-chp-footer">
					<div class="mfn-chpf-col mfn-chpf-left">
						<div class="mfn-chp-quantity">
							<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf(
									'1 <input type="hidden" name="cart[%s][qty]" value="1" />',
									esc_attr( $cart_item_key )
								);
							} else {
								$product_quantity = woocommerce_quantity_input(
									array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $_product->get_max_purchase_quantity(),
										'min_value'    => '0',
										'product_name' => $_product->get_name(),
									),
									$_product,
									false
								);
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					</div>
					<div class="mfn-chpf-col mfn-chpf-right">
						<a href="#" data-id="<?php echo esc_attr( $product_id ); ?>" class="mfn-chp-remove">
							<i class="icon-trash-line" aria-hidden="true"></i> <?php esc_html_e( 'Remove', 'woocommerce' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php
		}
	} else {
		?>
		<div class="cart-empty">
			<p class="cart-empty-icon">
				<?php
				if ( mfn_opts_get( 'shop-cart' ) ) {
					echo '<i class="' . esc_attr( mfn_opts_get( 'shop-cart' ) ) . '"></i>';
				} else {
					echo '<svg width="26" viewBox="0 0 26 26" aria-hidden="true"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"/><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"/></svg>';
				}
				?>
			</p>
			<p><?php esc_html_e( 'Your cart is currently empty.', 'woocommerce' ); ?></p>
		</div>
		<?php
	}

	echo '</div>';
}





function mfn_get_woo_sidecart_footer() {

	if ( ! WC()->cart ) {
		return;
	}

	echo '<div class="mfn-ch-footer-totals">';

	$is_translatable = mfn_opts_get( 'translate' );
	$translate['translate-side-cart-shipping-free'] = $is_translatable
		? mfn_opts_get( 'translate-side-cart-shipping-free', 'Free!' )
		: __( 'Free!', 'woocommerce' );

	do_action( 'mfn_get_woo_sidecart_footer_header' );

	if ( wc_coupons_enabled() && WC()->cart->get_cart() ) {

		echo '<div class="mfn-chft-row mfn-chft-apply-coupon-wrapper">';
			echo '<a href="/" class="mfn-chft-apply-coupon-switcher">' . esc_html__( 'Apply coupon', 'woocommerce' ) . ' <span class="icon-down-open"></span></a>';

			echo '<div class="mfn-chft-apply-coupon"><div class="mfn-sidecart-apply-coupon-wrapper"><input type="text" name="coupon_code" class="mfn-sidecart-apply-coupon-input" placeholder="' . esc_attr__( 'Coupon code', 'woocommerce' ) . '" /> <a href="/" class="mfn-sidecart-apply-coupon"><span class="icon-plus"></span></a></div></div>';

			echo '<div class="mfn-chft-coupons-list">';
			foreach ( WC()->cart->get_coupons() as $code => $coupon ) {

				$wc_coupon = new WC_Coupon( $code );

				if ( ! $wc_coupon->is_valid() ) {
					WC()->cart->remove_coupon( $code );
					continue;
				}

				echo '<div class="mfn-chft-coupons-list-single">';
					echo '<span class="mfn-chft-coupons-list-single-label">';
						echo '<span>' . esc_html__( 'Coupon', 'woocommerce' ) . ': <strong>' . esc_html( $code ) . '</strong></span>';
						echo '<a href="/" data-code="' . esc_attr( $code ) . '" class="mfn-sidecart-remove-coupon"><span class="icon-cancel"></span></a>';
					echo '</span>';
					echo '<span>';
						wc_cart_totals_coupon_html( $coupon );
					echo '</span>';
				echo '</div>';
			}
			echo '</div>';

		echo '</div>';
	}

	echo '<div class="mfn-chft-row mfn-chft-subtotal">' . esc_html__( 'Subtotal', 'woocommerce' ) . ': <div class="mfn-chft-subtotal-amount">';
	wc_cart_totals_subtotal_html();
	echo '</div></div>';

	if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) {

		$total = $translate['translate-side-cart-shipping-free'];

		if ( 0 < WC()->cart->get_shipping_total() ) {
			if ( WC()->cart->display_prices_including_tax() ) {
				$total = wc_price( WC()->cart->shipping_total + WC()->cart->shipping_tax_total );

				if ( WC()->cart->shipping_tax_total > 0 && ! wc_prices_include_tax() ) {
					$total .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$total = wc_price( WC()->cart->shipping_total );

				if ( WC()->cart->shipping_tax_total > 0 && wc_prices_include_tax() ) {
					$total .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		}

		echo '<div class="mfn-chft-row mfn-chft-row-shipping">' . esc_html__( 'Shipping', 'woocommerce' ) . ': <span>' . wp_kses_post( $total ) . '</span></div>';
	}

	if ( wc_tax_enabled() ) {
		echo '<div class="mfn-chft-row mfn-chft-vat">' . esc_html( WC()->countries->tax_or_vat() ) . ': ';
		echo wp_kses_post( wc_cart_totals_taxes_total_html() );
		echo '</div>';
	}

	do_action( 'mfn_get_woo_sidecart_footer_content' );

	echo '<div class="mfn-chft-row mfn-chft-total">' . esc_html__( 'Total', 'woocommerce' ) . ': ';
	wc_cart_totals_order_total_html();
	echo '</div>';

	do_action( 'mfn_get_woo_sidecart_footer_footer' );

	echo '</div>';
}











// sidecart apply coupon
add_action( 'wp_ajax_nopriv_mfnapplycoupon', 'mfn_apply_coupon' );
add_action( 'wp_ajax_mfnapplycoupon', 'mfn_apply_coupon' );
function mfn_apply_coupon() {
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );

	$coupon_code = esc_html($_POST['code']);

	$return = array();

	WC()->cart->apply_coupon( $coupon_code );

	ob_start();
	mfn_get_woo_sidecart_content();
	$return['content'] = ob_get_clean();

	ob_start();
	mfn_get_woo_sidecart_footer();
	$return['footer'] = ob_get_clean();

	$return['notice'] = wc_get_notices();

	wc_clear_notices();

	wp_send_json($return);

	wp_die();
}

add_action( 'wp_ajax_mfndeletecoupon', 'mfn_delete_coupon' );
add_action( 'wp_ajax_nopriv_mfndeletecoupon', 'mfn_delete_coupon' );
function mfn_delete_coupon() {
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );

	$coupon_code = esc_html($_POST['code']);

	if ( WC()->cart->has_discount( $coupon_code ) ) {
		WC()->cart->remove_coupon( $coupon_code );
	}

	wp_die();

}


// fix for ajax & wcml
add_filter( 'wcml_multi_currency_ajax_actions', 'mfn_add_action_to_multi_currency_ajax', 10, 1 );
function mfn_add_action_to_multi_currency_ajax( $ajax_actions ) {
    $ajax_actions[] = 'mfnrefreshcart'; // Add a AJAX action to the array
    $ajax_actions[] = 'mfnproductquickview'; // WPML | Workaround for compsupp-7826
    return $ajax_actions;
}

/*add_action( 'wp_ajax_mfnrefreshcart', 'mfn_refreshsidecart' );
add_action( 'wp_ajax_nopriv_mfnrefreshcart', 'mfn_refreshsidecart' );

function mfn_refreshsidecart(){
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );
	$return = array();

	WC()->cart->calculate_totals();

	ob_start();
	mfn_get_woo_sidecart_content();
	$return['content'] = ob_get_clean();

	ob_start();
	mfn_get_woo_sidecart_footer();
	$return['footer'] = ob_get_clean();

	$return['total'] = WC()->cart->get_cart_contents_count();

	wp_send_json($return);
	wp_die();
}*/

add_action( 'wp_ajax_mfnremovewooproduct', 'mfn_removefromcart' );
add_action( 'wp_ajax_nopriv_mfnremovewooproduct', 'mfn_removefromcart' );

function mfn_removefromcart() {
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );
	$id = $_POST['pid'];
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	     if ( $cart_item_key == $id ) {
	          WC()->cart->remove_cart_item( $cart_item_key );
	     }
	}
	wp_die();
}

add_action( 'wp_ajax_mfnchangeqtyproduct', 'mfn_qtyproductcart' );
add_action( 'wp_ajax_nopriv_mfnchangeqtyproduct', 'mfn_qtyproductcart' );

function mfn_qtyproductcart() {
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );
	$id = $_POST['pid'];
	$qty = $_POST['qty'];

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( $cart_item_key == $id ) {
			WC()->cart->set_quantity($cart_item_key, $qty);
		}
	}

	wp_die();
}

add_action( 'wp_ajax_mfnproductquickview', 'mfn_quickview' );
add_action( 'wp_ajax_nopriv_mfnproductquickview', 'mfn_quickview' );

function mfn_quickview() {
	check_ajax_referer( 'mfn-woo-nonce', 'mfn-woo-nonce' );
	$id = $_POST['id'];

	ob_start();
	get_template_part( 'includes/quickview', '', array('id' => $id) );
	$return = ob_get_clean();

	wp_send_json($return);
	wp_die();
}


function mfn_woo_attr_filter( $query ) {
  if ( !is_admin() && $query->is_main_query() ) {

  	if( ! empty($_GET) ){

			// check if any attributes are set

  		$filters = $_GET;

    	unset($filters['layout']);
    	unset($filters['orderby']);
    	unset($filters['per_page']);

			// check again after default filters removed

    	if( ! empty($filters) ){

	    	$taxquery = array('relation' => 'AND');

	    	foreach($filters as $f=>$filter){

					// explode coma ceparated values
					if( is_string($filter) && false !== strpos( $filter, ',' ) ){
						$filter = explode( ',', $filter );
					}

					$key = 'pa_'. $f;
	    		if( taxonomy_exists( $key ) ){
	        	$taxquery[] = array(
	            'taxonomy' => $key,
	            'field' => 'slug',
	            'terms' => $filter,
	            'operator'=> 'IN',
				    );
	        }

				}

				// print_r($taxquery);
	      $query->set( 'tax_query', $taxquery );

    	}
    }
  }
}

add_action( 'pre_get_posts', 'mfn_woo_attr_filter', 5 );
add_action( 'pre_get_posts', 'mfn_woo_per_page', 99 );

function mfn_woo_per_page( $query ) {
  if ( !is_admin() && $query->is_main_query() ) {

  	if( (is_shop() || is_product_category() || is_product_tag()) && mfn_get_per_page() ) {
  		$query->set( 'posts_per_page', mfn_get_per_page() );
  	}

  }
}

add_action('woocommerce_before_shop_loop', 'mfn_woo_products_list_filter_wrapper_start', 5);
function mfn_woo_products_list_filter_wrapper_start() {
	$class = '';
	if(!empty(mfn_opts_get('shop-list-perpage')) || !empty(mfn_opts_get('shop-list-layout')) || !empty($_GET['visual']) ){
		$class .= ' mfn-additional-shop-options-active';
	}

	$mfn_tmpl_type = !empty(Mfn_Builder_Front::$post_id2) ? get_post_meta(Mfn_Builder_Front::$post_id2, 'mfn_template_type', true) : 'default';
	if( in_array($mfn_tmpl_type, array('shop', 'archive-product', 'default')) ){
		echo '<div class="mfn-woo-filters-wrapper shop-filters'.$class.'">';
	}
}



add_action('woocommerce_before_shop_loop', 'mfn_woo_products_list_options', 20);
function mfn_woo_products_list_options() {
	$mfn_front_tmpl = new Mfn_Template_View();
	$shop_archive = $mfn_front_tmpl->get_archive_template();

	$mfn_tmpl_type = !empty(Mfn_Builder_Front::$post_id2) ? get_post_meta(Mfn_Builder_Front::$post_id2, 'mfn_template_type', true) : 'default';

	if( in_array($mfn_tmpl_type, array('shop', 'archive-product', 'default')) && (!empty(mfn_opts_get('shop-list-perpage')) || !empty(mfn_opts_get('shop-list-layout')) || !empty($_GET['visual']) || !empty( get_post_meta($shop_archive, 'mfn-shop-list-layout', true) ) || !empty( get_post_meta($shop_archive, 'mfn-shop-list-perpage', true) ) ) ) {
		get_template_part('includes/woocommerce-list-options');
	}

}

add_action('woocommerce_before_shop_loop', 'mfn_woo_products_list_filter_wrapper_end', 35);
function mfn_woo_products_list_filter_wrapper_end() {

	$mfn_tmpl_type = !empty(Mfn_Builder_Front::$post_id2) ? get_post_meta(Mfn_Builder_Front::$post_id2, 'mfn_template_type', true) : 'default';
	if( in_array($mfn_tmpl_type, array('shop', 'archive-product', 'default')) ){

	$sidebar = mfn_sidebar(true);
	$mfn_front_tmpl = new Mfn_Template_View();
	$shop_archive = $mfn_front_tmpl->get_archive_template();

	$translate['translate-shop-filters'] = mfn_opts_get('translate') ? mfn_opts_get('translate-shop-filters', 'Filters') : __('Filters', 'woocommerce');

	if( ( mfn_opts_get('mobile-sidebar') == 1 || isset($sidebar['layout']) && $sidebar['layout'] == 'offcanvas-sidebar' ) && ( isset( $sidebar['sidebar']['first'] ) || isset( $sidebar['sidebar']['second'] ) ) ){
		echo '<a class="open-filters mfn-off-canvas-switcher '. ( !isset($sidebar['layout']) || $sidebar['layout'] != 'offcanvas-sidebar' ? 'mfn-only-mobile-ofcs' : null ) .'" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="Offcanvas sidebar icon"><defs><style>.path{fill:none;stroke:#000;stroke-miterlimit:10;}</style></defs><g><line x1="8" y1="11" x2="14" y2="11" class="path"/><line x1="2" y1="11" x2="4" y2="11" class="path"/><line x1="12" y1="5" x2="14" y2="5" class="path"/><line x1="2" y1="5" x2="8" y2="5" class="path"/><circle cx="6" cy="11" r="2" class="path"/><circle cx="10" cy="5" r="2" class="path"/></g></svg>';
			echo $translate['translate-shop-filters'];
		echo '</a>';
	}

	echo '</div>';

	if( !empty(mfn_opts_get('shop-list-active-filters')) || !empty($_GET['visual']) || !empty( get_post_meta($shop_archive, 'mfn-shop-list-active-filters', true) ) ) {
		get_template_part('includes/woocommerce-active-filters');
	}
	}
}

/*if (! function_exists('mfn_woo_per_page')) {
	function mfn_woo_per_page($cols){
		return mfn_get_per_page();
	}
}
add_filter('loop_shop_per_page', 'mfn_woo_per_page', 12);*/

add_filter( 'woocommerce_product_single_add_to_cart_text', 'mfn_template_single_add_to_cart_text', 10, 2 );
function mfn_template_single_add_to_cart_text( $add_to_cart_text, $product ) {
	global $product;
	global $mfn_global;
	$tmp_id = !empty($mfn_global['singular_template']) ? $mfn_global['singular_template'] : false;

	if( !empty($product) && method_exists($product,'get_id') && get_post_meta($product->get_id(), '_button_text', true) ){
		return get_post_meta($product->get_id(), '_button_text', true);
	}elseif( !empty($tmp_id) && !empty(get_post_meta($tmp_id, 'mfn_cart_button', true)) ){
		return get_post_meta($tmp_id, 'mfn_cart_button', true);
	}
	return $add_to_cart_text;
}

add_filter( 'woocommerce_product_tabs', 'mfn_woo_description_tab', 10 );
function mfn_woo_description_tab( $tabs ) {

	global $post;

	$content = get_post_field( 'post_content', $post->ID );
	$content = apply_filters( 'the_content', $content );
	$builder = get_post_meta( $post->ID, 'mfn-page-items', true );

	if( $content || $builder || apply_filters('bebuilder_preview', false) ) {
		$tabs['description']['title'] = __( 'Description', 'woocommerce' );
		$tabs['description']['priority'] = 1;
		$tabs['description']['callback'] = 'mfn_woo_description_callback';
	}

	return $tabs;
}

function mfn_woo_description_callback() {
	wc_get_template( 'single-product/tabs/description.php' );
}

add_action( 'mfn_after_content', 'mfn_display_wishlist' );

function mfn_display_wishlist() {

	global $mfn_global;

	if( empty($mfn_global['archive_template']) && function_exists('is_woocommerce') && mfn_opts_get('shop-wishlist-page') && mfn_opts_get('shop-wishlist-page') == get_the_ID() ) {

	  get_template_part('includes/wishlist');

	}

}


add_filter( 'template_include', 'mfn_conditional_wishlist_template', 99 );
function mfn_conditional_wishlist_template( $template ) {

	global $mfn_global;

  if( !empty($mfn_global['archive_template']) && function_exists('is_woocommerce') && mfn_opts_get('shop-wishlist-page') && mfn_opts_get('shop-wishlist-page') == get_the_ID() ) {
      $new_template = get_stylesheet_directory() . '/includes/wishlist.php';
      if ( file_exists( $new_template ) ) {
          return $new_template;
      }
  }

  return $template; // fallback to normal (page.php, etc.)
}


remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

// before and after div in cart
add_action('mfn_before_content', 'mfn_carts_page_before');
function mfn_carts_page_before() {

	global $mfn_global;

	$classes = array();

	if( is_cart() ){
		$step = 1;
		$classes[] = 'mfn-be-cart';
	}elseif( is_wc_endpoint_url( 'order-received' ) ){
		$step = 3;
		$classes[] = 'mfn-be-thankyou';
	}elseif( is_checkout() ){
		$step = 2;
		$classes[] = 'mfn-be-checkout';
	}

	if( ( is_cart() && empty($mfn_global['cart']) ) || ( is_checkout() && empty($mfn_global['checkout']) ) || ( is_wc_endpoint_url( 'order-received' ) && empty($mfn_global['thank_you']) ) ) {
		$classes[] = 'mfn-cart-step mfn-cart-step-'.$step;
		echo '<div class="'.implode(' ', $classes).'">';
		echo '<div class="section_wrapper clearfix"><div class="the_content_wrapper">
			<ul class="mfn-checkout-steps">
				<li '.( isset($step) && $step >= 1 ? 'class="active"' : null ).'><span class="mfn-step-number">'.($step > 1 ? '<i class="icon-check" aria-hidden="true"></i>' : 1).'</span> '. __( 'Cart', 'woocommerce' ) .'</li>
				<li '.( isset($step) && $step >= 2 ? 'class="active"' : null ).'><span class="mfn-step-number">'.($step > 2 ? '<i class="icon-check" aria-hidden="true"></i>' : 2).'</span> '. __( 'Checkout', 'woocommerce' ) .'</li>
				<li '.( isset($step) && $step == 3 ? 'class="active"' : null ).'><span class="mfn-step-number">'.($step == 3 ? '<i class="icon-check" aria-hidden="true"></i>' : 3).'</span> '. __( 'Order', 'woocommerce' ) .'</li>
			</ul>
		</div></div>';
	}

}

add_action('mfn_after_content', 'mfn_carts_page_after');
function mfn_carts_page_after() {
	global $mfn_global;
	if( ( is_cart() && empty($mfn_global['cart']) ) || ( is_checkout() && empty($mfn_global['checkout']) ) || ( is_wc_endpoint_url( 'order-received' ) && empty($mfn_global['thank_you']) ) ) {
		echo '</div>';
	}
}

add_action('woocommerce_after_cart_totals', 'mfn_continue_shippping_link');
function mfn_continue_shippping_link(){
	echo '<a href="'.get_permalink( wc_get_page_id( 'shop' ) ).'" class="mfn-woo-cart-link">';
	do_action('mfn_woocommerce_continue_shopping_string');
	echo'</a>';
}

add_action('woocommerce_review_order_after_submit', 'mfn_return_cart_link');
function mfn_return_cart_link(){
	echo '<a href="'.get_permalink( wc_get_page_id( 'cart' ) ).'" class="mfn-woo-cart-link">'. __('Return to cart', 'woocommerce') .'</a>';
}

// add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'mfn_woo_ajax_add_to_cart_single');
// add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'mfn_woo_ajax_add_to_cart_single');

function mfn_woo_ajax_add_to_cart_single() {

	_deprecated_function( 'mfn_woo_ajax_add_to_cart_single', '25.1.5', 'WC_AJAX::add_to_cart()' );

	global $woocommerce;
	$before_add = $_POST['current_cart'];
    $product_id = $_POST['product_id'];

    WC()->cart->add_to_cart();

    $after_add = $woocommerce->cart->cart_contents_count;

    if( $after_add == $before_add ){
    	return wp_send_json('error');
    }

    WC_AJAX :: get_refreshed_fragments();

    wp_die();
}

/*add_action( 'mfn_product_image', 'mfn_new_badge_woo_product', 3 );

function mfn_new_badge_woo_product() {
	if(mfn_opts_get('product-badge-new') == 1){
		global $product;
		$newness_days = mfn_opts_get('product-badge-new-days') ? mfn_opts_get('product-badge-new-days') : 14;
		$created = strtotime( $product->get_date_created() );
		$label = mfn_opts_get('product-badge-new-text', __( 'NEW', 'woocommerce' ));
		if ( ( time() - ( 60 * 60 * 24 * $newness_days ) ) < $created ) {
		  echo '<span class="mfn-new-badge onsale-label onsale">' . esc_html( $label ) . '</span>';
		}
	}
}*/

function mfn_get_per_page( $from_panel = false ){
	$mfn_front_tmpl = new Mfn_Template_View();

	$tmp_id = $mfn_front_tmpl->get_archive_template();
	$perpage = filter_input(INPUT_GET, 'per_page', FILTER_SANITIZE_NUMBER_INT);

	if( isset($perpage) && !$from_panel ){
		return absint($perpage);
	}

	return false;
}

function mfn_get_layout( $from_panel = false ){
	if( ! empty( $_GET['layout'] ) ){
		$shop_layout = str_replace('grid4', 'grid col-4', esc_attr($_GET['layout']));
	} else {
		$shop_layout = mfn_opts_get( 'shop-layout', 'grid' );
	}

	return $shop_layout;
}

function mfn_product_cat_content_form_fields( $array ) {
	$field_name_1 = 'mfn_prod_cat_top_content';
	$field_label_1 = 'Top Content';
	$field_name_2 = 'mfn_prod_cat_bottom_content';
	$field_label_2 = 'Bottom Content';

    if( !empty($_GET['taxonomy']) && !empty($_GET['tag_ID']) && $_GET['taxonomy'] == 'product_cat' ) {

    	$val_1 = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_top_content', true);
    	$val_1_switcher = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_top_content_switcher', true);
    	$val_2 = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_bottom_content', true);
    	$val_2_switcher = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_bottom_content_switcher', true);

    	$val_3_get = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_desc_readmore', true);

    	if( !empty($val_3_get) ) {
    		$val_3 = json_decode($val_3_get);
    		$val_3_switcher = !empty($val_3->mfn_product_cat_desc_readmore) ? $val_3->mfn_product_cat_desc_readmore : '';
    		$val_3_height = !empty($val_3->mfn_product_cat_desc_height) ? $val_3->mfn_product_cat_desc_height : '400';
    		$val_3_button = !empty($val_3->mfn_product_cat_desc_button) ? $val_3->mfn_product_cat_desc_button : 'Read more';
    	}

    	$val_0_switcher = get_term_meta($_GET['tag_ID'], 'mfn_product_cat_description_switcher', true);

    	echo '<tr class="form-field"><th valign="top" scope="row"><label for="description_switcher">Description visibility</label></th><td><select id="description_switcher" name="mfn_prod_cat_description_switcher"><option value="">Default</option><option '.(!empty($val_0_switcher) ? "selected" : '').' value="on-1">On first page only</option></select></td></tr>';

			echo '<tr class="form-field"><th valign="top" scope="row"><label for="'.$field_name_1.'">'.$field_label_1.'</label></th><td><textarea rows="5" cols="40" id="'.$field_name_1.'" name="'.$field_name_1.'">'.$val_1.'</textarea><p class="description">Shortcodes are allowed. This will be displayed at the top of the category.</p></td></tr>';

			echo '<tr class="form-field"><th valign="top" scope="row"><label for="'.$field_name_1.'_switcher">Top content visibility</label></th><td><select id="'.$field_name_1.'_switcher" name="'.$field_name_1.'_switcher"><option value="">Default</option><option '.(!empty($val_1_switcher) ? "selected" : '').' value="on-1">On first page only</option></select></td></tr>';

	    echo '<tr class="form-field"><th valign="top" scope="row"><label for="'.$field_name_2.'">'.$field_label_2.'</label></th><td><textarea rows="5" cols="40" id="'.$field_name_2.'" name="'.$field_name_2.'">'.$val_2.'</textarea><p class="description">Shortcodes are allowed. This will be displayed at the bottom of the category.</p></td></tr>';

	    echo '<tr class="form-field"><th valign="top" scope="row"><label for="'.$field_name_2.'_switcher">Bottom content visibility</label></th><td><select id="'.$field_name_2.'_switcher" name="'.$field_name_2.'_switcher"><option value="">Default</option><option '.(!empty($val_2_switcher) ? "selected" : '').' value="on-1">On first page only</option></select></td></tr>';

	    echo '<tr class="form-field mfn-wp-form-multifields"><th valign="top" scope="row"><label>Description read more</label></th><td>
    		<select class="mfn-productcat-readmore-switcher" name="mfn_product_cat_desc_readmore"><option value="">Disabled</option><option '.( !empty($val_3_switcher) && $val_3_switcher == 'enabled' ? 'selected' : '' ).' value="enabled">Enabled</option></select>
    		</td></tr>';

    	echo '<tr class="form-field mfn-wp-form-multifields mfn-desc-readmore-additional-fields" style="display: none;"><th valign="top" scope="row"><label>Content height [px]</label></th><td>
    		<div class="form-group has-addons has-addons-append"><div class="form-control"><input class="mfn-field-value mfn-form-control mfn-form-input" type="number" value="'.( !empty($val_3_height) ? $val_3_height : '400' ).'" name="mfn_product_cat_desc_height" placeholder="400"></div></div>
    		</td></tr>';

    	echo '<tr class="form-field mfn-wp-form-multifields mfn-desc-readmore-additional-fields" style="display: none;"><th valign="top" scope="row"><label>Button label</label></th><td>
    		<div class="form-group"><div class="form-control"><input class="mfn-field-value mfn-form-control mfn-form-input" type="text" value="'.( !empty($val_3_button) ? $val_3_button : 'Read more' ).'" name="mfn_product_cat_desc_button" placeholder="Read more"></div></div>

    		<p>If you want to change color of read more gradient go to <a href="admin.php?page=be-options#colors-shop" target="_blank">theme options</a></p>
    		</td></tr>';

	}else{

			echo '<div class="form-field"><label for="description_switcher">Description visibility</label>';
      echo '<select id="description_switcher" name="mfn_prod_cat_description_switcher"><option value="">Default</option><option value="on-1">On first page only</option></select></div>';

    	echo '<div class="form-field"><label for="'.$field_name_1.'">'.$field_label_1.'</label><textarea rows="5" cols="40" id="'.$field_name_1.'" name="'.$field_name_1.'">';
      echo '</textarea><p>Shortcodes are allowed. This will be displayed at the top of the category.</p></div>';

      echo '<div class="form-field"><label for="'.$field_name_1.'_switcher">Top content visibility</label>';
      echo '<select id="'.$field_name_1.'_switcher" name="'.$field_name_1.'_switcher"><option value="">Default</option><option value="on-1">On first page only</option></select></div>';

      echo '<div class="form-field"><label for="'.$field_name_2.'">'.$field_label_2.'</label><textarea rows="5" cols="40" id="'.$field_name_2.'" name="'.$field_name_2.'">';
      echo '</textarea><p>Shortcodes are allowed. This will be displayed at the bottom of the category.</p></div>';

      echo '<div class="form-field"><label for="'.$field_name_2.'_switcher">Bottom content visibility</label>';
      echo '<select id="'.$field_name_2.'_switcher" name="'.$field_name_2.'_switcher"><option value="">Default</option><option value="on-1">On first page only</option></select></div>';

      echo '<div class="form-field mfn-wp-form-multifields"><label>Description read more</label>
    		<select class="mfn-productcat-readmore-switcher" name="mfn_product_cat_desc_readmore"><option value="">Disabled</option><option value="enabled">Enabled</option></select>

    		</div>';

    	echo '<div class="form-field mfn-wp-form-multifields mfn-desc-readmore-additional-fields" style="display: none;">
    	<label>Content height [px]</label>
    	<div>
    		<div class="form-group has-addons has-addons-append"><div class="form-control"><input class="mfn-field-value mfn-form-control mfn-form-input" type="number" value="400" name="mfn_product_cat_desc_height" placeholder="400"></div></div></div></div>';

    	echo '<div class="form-field mfn-wp-form-multifields mfn-desc-readmore-additional-fields" style="display: none;"><label>Button label</label>
    	<div>
    		<div class="form-group"><div class="form-control"><input class="mfn-field-value mfn-form-control mfn-form-input" type="text" value="Read more" name="mfn_product_cat_desc_button" placeholder="Read more"></div></div></div>

    		<p>If you want to change color of read more gradient go to <a href="admin.php?page=be-options#colors-shop" target="_blank">theme options</a></p></div>';
	}

};

add_action( 'product_cat_add_form_fields', 'mfn_product_cat_content_form_fields');
add_action( 'product_cat_edit_form_fields', 'mfn_product_cat_content_form_fields', 10, 1 );

function mfn_save_product_cat_fields( $id ) {

	if(!empty($_POST['mfn_prod_cat_description_switcher'])) { update_term_meta( $id, 'mfn_product_cat_description_switcher', $_POST['mfn_prod_cat_description_switcher'] ); }else{ delete_term_meta($id, 'mfn_product_cat_description_switcher'); }

	if(!empty($_POST['mfn_prod_cat_top_content'])) { update_term_meta( $id, 'mfn_product_cat_top_content', $_POST['mfn_prod_cat_top_content'] ); }else{ delete_term_meta($id, 'mfn_product_cat_top_content'); }
	if(!empty($_POST['mfn_prod_cat_top_content_switcher'])) { update_term_meta( $id, 'mfn_product_cat_top_content_switcher', $_POST['mfn_prod_cat_top_content_switcher'] ); }else{ delete_term_meta($id, 'mfn_product_cat_top_content_switcher'); }

  if(!empty($_POST['mfn_prod_cat_bottom_content'])) { update_term_meta( $id, 'mfn_product_cat_bottom_content', $_POST['mfn_prod_cat_bottom_content'] ); }else{ delete_term_meta($id, 'mfn_product_cat_bottom_content'); }
  if(!empty($_POST['mfn_prod_cat_bottom_content_switcher'])) { update_term_meta( $id, 'mfn_product_cat_bottom_content_switcher', $_POST['mfn_prod_cat_bottom_content_switcher'] ); }else{ delete_term_meta($id, 'mfn_product_cat_bottom_content_switcher'); }

  if(!empty($_POST['mfn_product_cat_desc_readmore'])) {
  	$helper_array = array();
  	$helper_array['mfn_product_cat_desc_readmore'] = 'enabled';
  	$helper_array['mfn_product_cat_desc_height'] = !empty($_POST['mfn_product_cat_desc_height']) ? $_POST['mfn_product_cat_desc_height'] : '400';
  	$helper_array['mfn_product_cat_desc_button'] = !empty($_POST['mfn_product_cat_desc_button']) ? $_POST['mfn_product_cat_desc_button'] : 'Read more';
  	update_term_meta( $id, 'mfn_product_cat_desc_readmore', json_encode($helper_array) );
  }else{
  	delete_term_meta($id, 'mfn_product_cat_desc_readmore');
  }

};
add_action( 'saved_product_cat', 'mfn_save_product_cat_fields' );
add_action( 'create_product_cat', 'mfn_save_product_cat_fields' );

add_action('woocommerce_before_main_content', 'mfn_before_shop_content');
function mfn_before_shop_content() {
	if( is_product_category() ){
		$tmpl_id = mfn_ID();

		if( !empty($tmpl_id) && get_post_type($tmpl_id) == 'template' && get_post_status($tmpl_id) == 'publish' && !empty(get_post_meta($tmpl_id, 'mfn_woo_cat_desc_top', true)) ){
			return;
		}

		$cat = get_queried_object();
		$top_content = get_term_meta($cat->term_id, 'mfn_product_cat_top_content', true);
		$top_content_v = get_term_meta($cat->term_id, 'mfn_product_cat_top_content_switcher', true);

		if( !empty($top_content_v) && !empty(get_query_var('paged')) ) return;

		if(!empty($top_content)){
			echo do_shortcode($top_content);
		}
	}
}
add_action('woocommerce_after_main_content', 'mfn_after_shop_content', 5);
function mfn_after_shop_content() {
	if( is_product_category() ) {

		$tmpl_id = mfn_ID();

		if( !empty($tmpl_id) && get_post_type($tmpl_id) == 'template' && get_post_status($tmpl_id) == 'publish' && !empty(get_post_meta($tmpl_id, 'mfn_woo_cat_desc_bottom', true)) ){
			return;
		}

		$cat = get_queried_object();
		$bottom_content = get_term_meta($cat->term_id, 'mfn_product_cat_bottom_content', true);
		$bottom_content_v = get_term_meta($cat->term_id, 'mfn_product_cat_bottom_content_switcher', true);
		$is_paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		if( !empty($bottom_content_v) && $is_paged > 1 ) return;

		if(!empty($bottom_content)) {
			echo '<div class="mfn_after_shop_content_wrapper">';

			if( isset($cat->term_id) && !empty(get_term_meta($cat->term_id, 'mfn_product_cat_desc_readmore', true)) ) {
				$rm_get = get_term_meta($cat->term_id, 'mfn_product_cat_desc_readmore', true);
				$rm = json_decode($rm_get);
    		$rm_switcher = !empty($rm->mfn_product_cat_desc_readmore) ? $rm->mfn_product_cat_desc_readmore : '';
    		$rm_height = !empty($rm->mfn_product_cat_desc_height) ? $rm->mfn_product_cat_desc_height : '400';
    		$rm_button = !empty($rm->mfn_product_cat_desc_button) ? $rm->mfn_product_cat_desc_button : 'Read more';
    		echo '<div style="max-height: '.$rm_height.'px" class="mfn-desc-expandable">';
    	}

			echo do_shortcode($bottom_content);

			if( !empty($rm->mfn_product_cat_desc_readmore) ) {
				echo '<div class="read-more"><a class="read-more-button button" href="#">'.$rm_button.' <i class="icon-down-open-mini"></i></a></div>';
				echo '</div>';
			}
			echo '</div>';
		}

	}
}

add_action('wp', 'mfnvb_wp_action');

function mfnvb_wp_action() {
	global $mfn_global;
	if( function_exists('is_woocommerce') ) {
		$mfn_front_tmpl = new Mfn_Template_View();

		if( empty( $_GET['visual'] ) && empty(mfn_opts_get('shop-list-sorting')) && empty( get_post_meta($mfn_front_tmpl->get_archive_template(), 'mfn-shop-list-sorting', true) ) ) {
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		}

		if( empty(mfn_opts_get('shop-list-results-count')) && empty( $_GET['visual'] ) && empty( get_post_meta($mfn_front_tmpl->get_archive_template(), 'mfn-shop-list-results-count', true) ) ) {
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		}

		if( is_product_category() ) {
			$cat = get_queried_object();
			$description_visibility = get_term_meta($cat->term_id, 'mfn_product_cat_description_switcher', true);
			if( !empty($description_visibility) && !empty(get_query_var('paged')) ) remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		}

		$cart_page_id = !empty(get_option('mfn_cart_template')) && is_numeric(get_option('mfn_cart_template')) ? get_option('mfn_cart_template') : false;

		if( !$cart_page_id && !empty( get_option('mfn_cart_template_used') ) ) $cart_page_id = get_option('mfn_cart_template_used');

		require_once( get_theme_file_path('/visual-builder/classes/woocommerce-cart-template.php') );
		$MfnCartClass = new MfnWoocommerceCartTmpl( $cart_page_id );
		$MfnCartClass->echo_all_cart_strings();


		if(!empty($mfn_global['singular_template']) && get_post_status($mfn_global['singular_template']) == 'publish' && empty( get_post_meta($mfn_global['singular_template'], 'mfn_product_image_zoom', true) ) ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}else if( empty($mfn_global['singular_template']) && ( 'disable-zoom' == mfn_opts_get('shop-single-image') || 'disable' == mfn_opts_get('image-frame-style') ) ){
			remove_theme_support( 'wc-product-gallery-zoom' );
		}

	}
}


add_filter( 'woocommerce_default_catalog_orderby', 'mfn_shop_template_orderby' );

function mfn_shop_template_orderby($default) {
	$mfn_front_tmpl = new Mfn_Template_View();
	$shop_archive = $mfn_front_tmpl->get_archive_template();

	if( !empty($shop_archive) && !empty(get_post_meta($shop_archive, 'mfn_default_order', true)) && empty( $_GET['orderby'] ) ) {
		return get_post_meta($shop_archive, 'mfn_default_order', true);
	}

	return $default;
}

add_filter('woocommerce_catalog_orderby', function ($options) {

	$mfn_front_tmpl = new Mfn_Template_View();
	$shop_archive = $mfn_front_tmpl->get_archive_template();

	//if( !empty($shop_archive) ) {

    $new = array(
      'menu_order' => __( 'Default sorting', 'woocommerce' ),
      'popularity' => __( 'Sort by popularity', 'woocommerce' ),
      'rating' => __( 'Sort by average rating', 'woocommerce' ),
      'date' => __( 'Sort by newness', 'betheme' ),
      'title' => __( 'Sort by title', 'betheme' ),
      'price' => __( 'Sort by price: low to high', 'woocommerce' ),
      'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
      'rand' => __( 'Random', 'woocommerce' ),
	 	);

    return $new;
  // }

  return $options;

}, 20);


add_action( 'mfn_get_woo_sidecart_before_content', 'mfn_tell_free_delivery', 10 );
add_action( 'woocommerce_cart_totals_before_shipping', 'mfn_tell_free_delivery', 10 );

function mfn_tell_free_delivery() {

	if( empty(mfn_opts_get('free-delivery-addon')) || empty(mfn_opts_get('free-delivery-sum')) ) return;

	$translate['free-delivery-progress-bar'] = mfn_opts_get('translate') ? mfn_opts_get('translate-free-delivery-progress-bar', 'You are %s short for free delivery.') : __('You are %s short for free delivery.', 'betheme');
	$translate['free-delivery-progress-bar-achieved'] = mfn_opts_get('translate') ? mfn_opts_get('translate-free-delivery-progress-bar-achieved', 'Your order qualifies for free shipping!') : __('Your order qualifies for free shipping!', 'betheme');

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;

	$classes = array('mfn-free-delivery-info');

	$prices_include_tax = get_option( 'woocommerce_prices_include_tax' );

  $total = WC()->cart->subtotal;

  if( ($prices_include_tax == 'no' && empty(mfn_opts_get('free-delivery-addon-tax')) ) || ( !empty(mfn_opts_get('free-delivery-addon-tax')) && mfn_opts_get('free-delivery-addon-tax') == '1' ) ) {
  	$total = WC()->cart->get_subtotal();
  }

  $free = mfn_opts_get('free-delivery-sum');
  $diff_sum = 0;
  $diff = 0;


  if($total < $free):

  	$diff_tmp = (float) $free-$total;
  	$decimals = 2;
  	$dec_sep = '.';
  	$thou_sep = ',';

  	if( !empty(get_option('woocommerce_price_num_decimals')) ) $decimals = get_option('woocommerce_price_num_decimals');
  	if( !empty(get_option('woocommerce_price_decimal_sep')) ) $dec_sep = get_option('woocommerce_price_decimal_sep');
  	if( !empty(get_option('woocommerce_price_thousand_sep')) ) $thou_sep = get_option('woocommerce_price_thousand_sep');

  	$diff_sum = '<span class="mfn-free-delivery-missing-amount">'.number_format( $diff_tmp, $decimals, $dec_sep, $thou_sep).'</span>';

  	// default is right
  	$diff = $diff_sum.get_woocommerce_currency_symbol();

  	if( !empty(get_option('woocommerce_currency_pos')) && get_option('woocommerce_currency_pos') == 'right_space' ) {
  		$diff = $diff_sum.' '.get_woocommerce_currency_symbol();
  	}else if( !empty(get_option('woocommerce_currency_pos')) && get_option('woocommerce_currency_pos') == 'left_space' ) {
  		$diff = get_woocommerce_currency_symbol().' '.$diff_sum;
  	}else if( !empty(get_option('woocommerce_currency_pos')) && get_option('woocommerce_currency_pos') == 'left' ) {
  		$diff = get_woocommerce_currency_symbol().$diff_sum;
  	}

  	$percent = round(((float)$total/(float)$free)*100, 2);

  else:

  	$percent = 100;
  	$classes[] = 'mfn-free-delivery-achieved';

  endif;


  echo '<div class="'.implode(' ', $classes).'" data-threshold="'.$free.'">';
  echo '<p class="mfn-free-delivery-info-desc mfn-free-delivery-info-desc-achieved">'.$translate['free-delivery-progress-bar-achieved'].'</p>';
  echo '<p class="mfn-free-delivery-info-desc">'.sprintf( $translate['free-delivery-progress-bar'], '<strong>'.$diff.'</strong>' ).'</p>';
  echo '<div class="mfn-free-delivery-info-progressbar"><span style="width: '.$percent.'%;"></span></div>';
  echo '<p class="mfn-free-delivery-info-link"><a href="'.get_permalink( wc_get_page_id('shop') ).'">'.__( 'Continue shopping', 'woocommerce' ).'</a></p>';
  echo '</div>';

}


add_action( 'mfn_hook_bottom', 'mfn_fake_sale_notification', 10 );
function mfn_fake_sale_notification() {
	if( empty(mfn_opts_get('fake-sale-addon')) ) return;
	global $wpdb;

  $list = array();

  $names = false;
  $return = false;

  $product_limit = !empty(mfn_opts_get('fake-sale-products-limit')) ? mfn_opts_get('fake-sale-products-limit') : 10;
  $closeable = mfn_opts_get('fake-sale-closeable');
  $domain = $_SERVER['HTTP_HOST'];
  $type = mfn_opts_get('fake-sale-type');
  $names_opt = mfn_opts_get('fake-sale-clients-names');
  $position = !empty(mfn_opts_get('fake-sale-clients-position')) ? mfn_opts_get('fake-sale-clients-position') : 'bottom-left';
  $cache = get_option('mfn_fake_sale');

  $translate['fake-sale-notification-someone'] = mfn_opts_get('translate') ? mfn_opts_get('translate-fake-sale-notification-someone', 'Someone') : __('Someone', 'betheme');
  $translate['fake-sale-notification-single'] = mfn_opts_get('translate') ? mfn_opts_get('translate-fake-sale-notification-single', 'bought the product') : __('bought the product', 'betheme');
  $translate['fake-sale-notification-multi'] = mfn_opts_get('translate') ? mfn_opts_get('translate-fake-sale-notification-multi', 'has been bought %s times recently.') : __('has been bought %s times recently.', 'betheme');

  if( !empty($cache) ) $return = json_decode($cache, true);

  if( !$return || ( isset($return['date']) && $return['date'] < date('Y-m-d') ) || ( isset($return['type']) && $return['type'] != $type ) || ( isset($return['names']) && $return['names'] != $names_opt ) || ( isset($return['domain']) && $return['domain'] != $domain ) ) {

  	if( !empty($names_opt) ){
	  	$names = !empty(mfn_opts_get('fake-sale-clients-list')) ? explode(',', mfn_opts_get('fake-sale-clients-list')) : array('John','Linda','Ann','Charles','Noah','Lucas','Henry','Camila','Harper','Elizabeth');
	  	$names_length = count($names);
	  }

	  $products = $wpdb->get_results( "SELECT p.ID, p.post_title, p.post_parent, p.post_type FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID WHERE p.post_type IN ('product','product_variation') and p.post_status = 'publish' and m.meta_key = '_price' and m.meta_value <> '' and m.meta_value > '0' order by RAND() LIMIT {$product_limit}" );

		if( is_iterable($products) ) {
			foreach( $products as $p=>$product ) {

				$featured_img = $product->post_type == 'product_variation' ? get_the_post_thumbnail_url($product->post_parent) : get_the_post_thumbnail_url($product->ID);
				$title = get_the_title($product->ID);

				$string = '<a class="mfn-fake-sale-noti-img" href="'.get_permalink($product->ID).'"><img loading="lazy" src="'.$featured_img.'" alt=""></a><div class="mfn-fake-sale-noti-desc"><p class="mfn-fake-sale-noti-desc-title">';

				if( $type == '2' ){
					if( $p % 2 == 0 ){
						$string .= ($names ? trim( $names[rand(0, $names_length-1)] ) : $translate['fake-sale-notification-someone']) .' '. $translate['fake-sale-notification-single'] . ' <a href="'.get_permalink($product->ID).'">'.$title.'</a>';
					}else{
						$string .= __( 'Product', 'woocommerce' ) . ' <a href="'.get_permalink($product->ID).'">'.$title.'</a> ' .sprintf( $translate['fake-sale-notification-multi'], rand(5, 10) );
					}
				}elseif( $type == '1' ){
					$string .= __( 'Product', 'woocommerce' ) . ' <a href="'.get_permalink($product->ID).'">'.$title.'</a> ' .sprintf( $translate['fake-sale-notification-multi'], rand(5, 10) );
				}else{
					$string .= ($names ? trim( $names[rand(0, $names_length-1)] ) : $translate['fake-sale-notification-someone']) .' '. $translate['fake-sale-notification-single'] . ' <a href="'.get_permalink($product->ID).'">'.$title.'</a>';
				}

				$string .= '</p></div>';

				$list[] = $string;

			}
		}

		$return = array(
			'domain' => $domain,
			'date' => date('Y-m-d'),
			'type' => $type,
			'names' => $names_opt,
			'items' => $list
		);

		update_option('mfn_fake_sale', json_encode($return));

	}

	$return['position'] = $position;
	$return['closeable'] = $closeable;
	$return['delay'] = !empty(mfn_opts_get('fake-sale-start-delay')) ? mfn_opts_get('fake-sale-start-delay') : '5';

	echo '<style>';
		if( !empty( mfn_opts_get('fake-sale-container-background') ) ) echo 'body .mfn-fake-sale-noti{background-color: '.mfn_opts_get('fake-sale-container-background').'}';
		if( !empty( mfn_opts_get('fake-sale-container-color') ) ) echo 'body .mfn-fake-sale-noti{color: '.mfn_opts_get('fake-sale-container-color').'}';
		if( !empty( mfn_opts_get('fake-sale-container-link-color') ) ) echo 'body .mfn-fake-sale-noti a{color: '.mfn_opts_get('fake-sale-container-link-color').'}';
		if( !empty( mfn_opts_get('fake-sale-container-exit-color') ) ) echo 'body .mfn-fake-sale-noti .mfn-fake-sale-noti-close{color: '.mfn_opts_get('fake-sale-container-exit-color').'}';
	echo '</style>';
  echo '<script>var mfn_fake_sale = '.json_encode($return).'</script>';

}

function mfn_init_recaptcha_tool() {

	$mfn_where_recaptcha = mfn_opts_get('recaptcha-display');

	if( !empty($mfn_where_recaptcha['register']) ) {
		add_action( 'woocommerce_register_form', 'mfn_woocommerce_form_recaptcha' );
	}

	if( !empty($mfn_where_recaptcha['register']) && is_checkout() && !is_user_logged_in() && !empty(get_option( 'woocommerce_enable_myaccount_registration' )) && get_option( 'woocommerce_enable_myaccount_registration' ) == 'yes' ) {
		add_action( 'woocommerce_before_order_notes', 'mfn_woocommerce_form_recaptcha' );
		add_action( 'wp_enqueue_scripts', 'mfn_recaptcha_enqueue_script' );
	}

	if( !empty($mfn_where_recaptcha['register']) && is_account_page() ) {
		add_action( 'wp_enqueue_scripts', 'mfn_recaptcha_enqueue_script' );
	}

	if( !empty($mfn_where_recaptcha['login']) ) {
		add_action( 'woocommerce_login_form', 'mfn_woocommerce_form_recaptcha' );
	}

}

function initMfnRecaptcha() {
	$mfn_where_recaptcha = mfn_opts_get('recaptcha-display');
	if( empty(mfn_opts_get('recaptcha-key')) && empty(mfn_opts_get('recaptcha-secret')) ) return;

	if( !empty($mfn_where_recaptcha['login']) ) {
		add_filter( 'wp_authenticate_user', 'mfn_validate_recaptcha_on_login', 10, 3 );
		add_action(	'login_form','mfn_woocommerce_form_recaptcha');
		add_action( 'login_enqueue_scripts', 'mfn_recaptcha_enqueue_script' );
		add_action( 'login_enqueue_scripts', 'mfn_recaptcha_enqueue_style' );
		add_action( 'wp_enqueue_scripts', 'mfn_recaptcha_enqueue_script' );
	}

	if( !empty($mfn_where_recaptcha['register']) ) {
		add_filter( 'woocommerce_registration_errors', 'validate_recaptcha_on_registration', 10, 3 );
	}

	add_action('wp', 'mfn_init_recaptcha_tool');

}

initMfnRecaptcha();

function mfn_woocommerce_form_recaptcha() {
  echo '<p class="form-row"><div class="g-recaptcha" data-sitekey="'.mfn_opts_get('recaptcha-key').'"></div></p>';
}

function validate_recaptcha_on_registration( $validation_errors, $username, $email ) {

	$translate = array();
	$translate['verify'] = mfn_opts_get('translate-recaptcha-error-1') ? mfn_opts_get('translate-recaptcha-error-1', 'Could not verify reCAPTCHA.') : __('Could not verify reCAPTCHA.', 'betheme');
	$translate['complete'] = mfn_opts_get('translate-recaptcha-error-2') ? mfn_opts_get('translate-recaptcha-error-2', 'Please complete the reCAPTCHA.') : __('Please complete the reCAPTCHA.', 'betheme');

	if ( !empty( $_POST['g-recaptcha-response'] ) ) {
	    $recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );
	} else {
	    return new WP_Error( 'recaptcha_error', $translate['complete'] );
	}

	$secret_key = mfn_opts_get('recaptcha-secret');

	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
	    'body' => array(
	        'secret'   => $secret_key,
	        'response' => $recaptcha_response,
	        'remoteip' => $_SERVER['REMOTE_ADDR']
	    )
	) );

	if ( is_wp_error( $response ) ) {
	    $validation_errors->add( 'recaptcha_error', $translate['verify'] );
	    return $validation_errors;
	}

	$response_body = wp_remote_retrieve_body( $response );
	$result = json_decode( $response_body );

	if ( ! isset( $result->success ) || ! $result->success ) {
	    $validation_errors->add( 'recaptcha_error', $translate['complete'] );
	}

	return $validation_errors;

}

function mfn_validate_recaptcha_on_login( $user, $password ) {

	$translate = array();
  $translate['verify'] = mfn_opts_get('translate-recaptcha-error-1') ? mfn_opts_get('translate-recaptcha-error-1', 'Error: Could not verify reCAPTCHA.') : __('Error: Could not verify reCAPTCHA.', 'betheme');
  $translate['complete'] = mfn_opts_get('translate-recaptcha-error-2') ? mfn_opts_get('translate-recaptcha-error-2', 'Error: Please complete the reCAPTCHA.') : __('Error: Please complete the reCAPTCHA.', 'betheme');

  if ( !empty( $_POST['g-recaptcha-response'] ) ) {
      $recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );
  } else {
      return new WP_Error( 'recaptcha_error', $translate['complete'] );
  }

  // Replace 'your_secret_key' with your actual Secret Key from Google reCAPTCHA
  $secret_key = mfn_opts_get('recaptcha-secret');

  // Verify the reCAPTCHA response with Google
  $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
      'body' => array(
          'secret'   => $secret_key,
          'response' => $recaptcha_response,
          'remoteip' => $_SERVER['REMOTE_ADDR']
      )
  ) );

  // Handle errors in the remote request
  if ( is_wp_error( $response ) ) {
      return new WP_Error( 'recaptcha_error', $translate['verify'] );
  }

  $response_body = wp_remote_retrieve_body( $response );
  $result = json_decode( $response_body );

  if ( ! isset( $result->success ) || ! $result->success ) {
      return new WP_Error( 'recaptcha_error', $translate['complete'] );
  }

  return $user;

}


add_filter( 'woocommerce_product_get_rating_html', function ( $html, $rating, $count ) {


    if ( $rating <= 0 ) return $html;

    /*$label_text = sprintf(
        __( 'Rated %s out of 5', 'woocommerce' ),
        wc_format_decimal( $rating, 1 )
    );*/

    $label_text = wc_format_decimal( $rating, 1 );

    if( !empty(Mfn_Builder_Front::$item_obj['attr']['product_star_rating_link']) ){

    	return '<a href="#reviews" class="wc-rating-with-text">' . $html .
           '<span class="wc-rating-text" aria-hidden="true">' . esc_html( $label_text ) . '</span></a>';
    }

    return '<span class="wc-rating-with-text">' . $html .
           '<span class="wc-rating-text" aria-hidden="true">' . esc_html( $label_text ) . '</span></span>';

}, 10, 3 );



if ( ! function_exists( 'mfn_get_cheapest_shipping' ) ) {
	function mfn_get_cheapest_shipping() {
		$return = false;
		$zones = array();

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new WC_Shipping_Zone( 0 );

		foreach ( $zones as $zone ) {
			$zone_shipping_methods = $zone->get_shipping_methods();
			foreach ( $zone_shipping_methods as $index => $method ) {
				$method_is_taxable = $method->is_taxable();
				$method_is_enabled = $method->is_enabled();
				$method_instance_id = $method->get_instance_id();
				$method_title = $method->get_method_title(); // e.g. "Flat Rate"
				$method_description = $method->get_method_description();
				$method_user_title = $method->get_title(); // e.g. whatever you renamed "Flat Rate" into
				$method_rate_id = $method->get_rate_id(); // e.g. "flat_rate:18"
				$instance = $method->instance_settings;

				if( isset($instance['cost']) && ( !$return || $instance['cost'] < $return ) ) {
					$return = $instance['cost'];
				}

			}
		}


		if( !empty($return) && is_numeric($return) ) return wc_price($return);
		return $return;
	}
}


add_action( 'init', function () {
	$dir = get_theme_file_path('/extensions/blocks/free-delivery-progress-bar');
	if ( ! file_exists( $dir . '/block.json' ) ) return;
	$render = include $dir . '/render.php';
	register_block_type( $dir, [ 'render_callback' => $render ] );
} );
