<?php
/** Keep store-owned and reseller-owned products separate in buyer-facing catalogs. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Storefront_Product_Sources {
	const OWNER_META_KEY = '_airsoft_reseller_owner';

	public static function init() {
		add_action( 'woocommerce_product_query', array( __CLASS__, 'filter_catalog_query' ), 20, 2 );
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'render_source_links' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 30 );
	}

	private static function is_product_catalog() {
		return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) );
	}

	private static function source() {
		$source = isset( $_GET['product_source'] ) ? sanitize_key( wp_unslash( $_GET['product_source'] ) ) : 'store';
		return 'reseller' === $source ? 'reseller' : 'store';
	}

	public static function filter_catalog_query( $query, $wc_query = null ) {
		if ( is_admin() || ! $query->is_main_query() || ! self::is_product_catalog() ) {
			return;
		}

		$source = self::source();
		$source_query = 'reseller' === $source
			? array( 'key' => self::OWNER_META_KEY, 'value' => '0', 'compare' => '>', 'type' => 'NUMERIC' )
			: array(
				'relation' => 'OR',
				array( 'key' => self::OWNER_META_KEY, 'compare' => 'NOT EXISTS' ),
				array( 'key' => self::OWNER_META_KEY, 'value' => '0', 'compare' => '<=', 'type' => 'NUMERIC' ),
			);

		$meta_query = array( 'relation' => 'AND' );
		$existing = $query->get( 'meta_query' );
		if ( is_array( $existing ) && ! empty( $existing ) ) {
			$meta_query[] = $existing;
		}
		$meta_query[] = $source_query;
		$query->set( 'meta_query', $meta_query );
	}

	public static function render_source_links() {
		if ( ! self::is_product_catalog() ) {
			return;
		}

		$source = self::source();
		$base_url = remove_query_arg( array( 'paged', 'product_source' ), get_pagenum_link( 1 ) );
		echo '<nav class="airsoft-product-source-tabs" aria-label="Product source">';
		foreach ( array( 'store' => 'Site products', 'reseller' => 'Reseller products' ) as $key => $label ) {
			$url = add_query_arg( 'product_source', $key, $base_url );
			echo '<a class="airsoft-product-source-tab" href="' . esc_url( $url ) . '"' . ( $source === $key ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
		}
		echo '</nav>';
	}

	public static function assets() {
		if ( ! self::is_product_catalog() ) {
			return;
		}

		$path = AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/css/product-sources.css';
		wp_enqueue_style( 'airsoft-product-sources', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/css/product-sources.css', array(), file_exists( $path ) ? filemtime( $path ) : '1.0.0' );
	}
}

Airsoft_Product_Manager_Storefront_Product_Sources::init();
