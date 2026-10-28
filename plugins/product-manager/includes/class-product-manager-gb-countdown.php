<?php
/** Theme integration for GB countdowns on product cards and product pages. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_GB_Countdown {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'render_template' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item', array( __CLASS__, 'render_loop' ), 15 );
	}

	private static function product_has_countdown( $product_id ) {
		if ( ! $product_id || ! shortcode_exists( 'gb_deal_banner' ) || 'yes' !== get_post_meta( $product_id, '_gb_enabled', true ) || post_password_required( $product_id ) ) {
			return false;
		}

		$expiry = trim( (string) get_post_meta( $product_id, '_gb_expiry', true ) );
		if ( '' === $expiry ) {
			return false;
		}

		try {
			$end = new DateTimeImmutable( $expiry, wp_timezone() );
		} catch ( Exception $exception ) {
			return false;
		}

		return $end->getTimestamp() > time();
	}

	public static function render_loop() {
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) || ! self::product_has_countdown( $product->get_id() ) ) {
			return;
		}

		echo '<div class="mfn-child-product-countdown mfn-child-loop-countdown">';
		echo '<strong>' . esc_html__( 'Offer Ends In:', 'product-manager' ) . '</strong>';
		echo do_shortcode( '[gb_deal_banner id="' . absint( $product->get_id() ) . '" show="countdown"]' );
		echo '</div>';
	}

	public static function enqueue() {
		if ( ! shortcode_exists( 'gb_deal_banner' ) ) {
			return;
		}

		$asset = AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/js/gb-product-countdown.js';
		wp_enqueue_script(
			'product-manager-gb-countdown',
			AIRSOFT_PRODUCT_MANAGER_URL . 'assets/js/gb-product-countdown.js',
			array(),
			file_exists( $asset ) ? filemtime( $asset ) : '1.0.0',
			true
		);
	}

	public static function render_template() {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! self::product_has_countdown( get_queried_object_id() ) ) {
			return;
		}

		$product_id = get_queried_object_id();
		echo '<template id="mfn-child-gb-countdown" data-product-id="' . esc_attr( $product_id ) . '">';
		echo '<div class="mfn-child-product-countdown">';
		echo '<strong>' . esc_html__( 'Offer Ends In:', 'product-manager' ) . '</strong>';
		echo do_shortcode( '[gb_deal_banner id="' . absint( $product_id ) . '" show="countdown"]' );
		echo '</div></template>';
	}
}

Airsoft_Product_Manager_GB_Countdown::init();