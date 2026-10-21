<?php
/**
 * Show GB countdowns in product loops and custom single-product layouts.
 * The plugin remains responsible for timer markup, ticking, and cache refresh.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'mfn_child_enqueue_gb_product_countdown', 20 );
add_action( 'wp_footer', 'mfn_child_gb_product_countdown_template', 5 );
add_action( 'woocommerce_after_shop_loop_item', 'mfn_child_gb_loop_countdown', 15 );

function mfn_child_gb_product_countdown_id() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! shortcode_exists( 'gb_deal_banner' ) ) {
		return 0;
	}

	$product_id = get_queried_object_id();
	return mfn_child_gb_has_countdown( $product_id ) ? $product_id : 0;
}

/**
 * Shared eligibility for single-product pages and each product in a list.
 */
function mfn_child_gb_has_countdown( $product_id ) {
	if ( ! $product_id || ! shortcode_exists( 'gb_deal_banner' ) ) {
		return false;
	}

	if ( 'yes' !== get_post_meta( $product_id, '_gb_enabled', true ) || post_password_required( $product_id ) ) {
		return false;
	}

	$expiry = trim( (string) get_post_meta( $product_id, '_gb_expiry', true ) );
	if ( '' === $expiry ) {
		return false;
	}

	try {
		$end = new DateTimeImmutable( $expiry, wp_timezone() );
	} catch ( Exception $e ) {
		return false;
	}

	return $end->getTimestamp() > time();
}

function mfn_child_gb_loop_countdown() {
	global $product;

	if ( ! is_a( $product, 'WC_Product' ) || ! mfn_child_gb_has_countdown( $product->get_id() ) ) {
		return;
	}

	echo '<div class="mfn-child-product-countdown mfn-child-loop-countdown">';
	echo '<strong>' . esc_html__( 'Offer Ends In:', 'betheme-child' ) . '</strong>';
	echo do_shortcode( '[gb_deal_banner id="' . absint( $product->get_id() ) . '" show="countdown"]' );
	echo '</div>';
}

function mfn_child_enqueue_gb_product_countdown() {
	// Product lists also need positioning, including AJAX-inserted cards.
	if ( ! shortcode_exists( 'gb_deal_banner' ) ) {
		return;
	}

	wp_enqueue_script(
		'betheme-child-gb-product-countdown',
		get_stylesheet_directory_uri() . '/js/gb-product-countdown.js',
		array(),
		filemtime( __DIR__ . '/../js/gb-product-countdown.js' ),
		true
	);
}

function mfn_child_gb_product_countdown_template() {
	$product_id = mfn_child_gb_product_countdown_id();
	if ( ! $product_id ) {
		return;
	}

	// Template contents stay inert until placed, before the plugin initializes.
	echo '<template id="mfn-child-gb-countdown" data-product-id="' . esc_attr( $product_id ) . '">';
	echo '<div class="mfn-child-product-countdown">';
	echo '<strong>' . esc_html__( 'Offer Ends In:', 'betheme-child' ) . '</strong>';
	echo do_shortcode( '[gb_deal_banner id="' . absint( $product_id ) . '" show="countdown"]' );
	echo '</div></template>';
}
