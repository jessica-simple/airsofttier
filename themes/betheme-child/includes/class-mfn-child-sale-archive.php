<?php
/**
 * Expose a sale-only WooCommerce product archive at /sale/.
 *
 * The route deliberately uses WooCommerce's normal product archive query so
 * Betheme's Shop Archive template and all existing shop filters continue to
 * render and operate unchanged.
 */
class MFN_Child_Sale_Archive {

	const QUERY_VAR    = 'mfn_sale_archive';
	const FLUSH_OPTION = 'mfn_child_sale_archive_rules_flushed_v1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ), 5 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 20 );
		add_action( 'after_switch_theme', array( __CLASS__, 'flush_rewrite_rules' ) );

		add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ), 0 );
		add_action( 'woocommerce_product_query', array( __CLASS__, 'limit_product_query_to_sale' ), 999, 2 );

		// Keep WordPress from redirecting this virtual archive to the Shop page.
		add_filter( 'redirect_canonical', array( __CLASS__, 'disable_sale_canonical_redirect' ), 10, 2 );

		// Use a useful archive title without changing the existing Shop title.
		add_filter( 'woocommerce_page_title', array( __CLASS__, 'sale_page_title' ) );

		// Give common SEO integrations a stable, crawlable archive canonical.
		add_filter( 'wpseo_canonical', array( __CLASS__, 'sale_canonical_url' ) );
		add_filter( 'rank_math/frontend/canonical', array( __CLASS__, 'sale_canonical_url' ) );
	}

	public static function add_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function register_rewrite_rules() {
		add_rewrite_rule(
			'^sale/page/([0-9]+)/?$',
			'index.php?post_type=product&' . self::QUERY_VAR . '=1&paged=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^sale/?$',
			'index.php?post_type=product&' . self::QUERY_VAR . '=1',
			'top'
		);
	}

	public static function maybe_flush_rewrite_rules() {
		if ( '1' === get_option( self::FLUSH_OPTION ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( self::FLUSH_OPTION, '1' );
	}

	public static function flush_rewrite_rules() {
		self::register_rewrite_rules();
		flush_rewrite_rules( true );
		update_option( self::FLUSH_OPTION, '1' );
	}

	/**
	 * Whether the current request is the virtual sale archive.
	 */
	private static function is_sale_archive() {
		return (bool) get_query_var( self::QUERY_VAR ) && is_post_type_archive( 'product' );
	}

	/**
	 * Add the native WooCommerce on-sale product IDs to the existing archive
	 * query. Intersecting with an existing post__in preserves other product
	 * filters that narrow the query, including AJAX/filter-plugin integrations.
	 */
	public static function limit_product_query_to_sale( $query ) {
		if ( ! self::is_sale_archive() || ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
			return;
		}

		$sale_ids = array_values(
			array_unique(
				array_filter(
					array_map( 'absint', (array) wc_get_product_ids_on_sale() )
				)
			)
		);
		$current_ids = array_values(
			array_filter(
				array_map( 'absint', (array) $query->get( 'post__in' ) )
			)
		);

		if ( empty( $sale_ids ) ) {
			$query->set( 'post__in', array( 0 ) );
			return;
		}

		if ( ! empty( $current_ids ) ) {
			$sale_ids = array_values( array_intersect( $current_ids, $sale_ids ) );
		}

		$query->set( 'post__in', ! empty( $sale_ids ) ? $sale_ids : array( 0 ) );
	}

	public static function disable_sale_canonical_redirect( $redirect_url, $requested_url ) {
		return self::is_sale_archive() ? false : $redirect_url;
	}

	public static function sale_page_title( $title ) {
		return self::is_sale_archive() ? __( 'On Sale', 'betheme-child' ) : $title;
	}

	public static function sale_canonical_url( $canonical ) {
		if ( ! self::is_sale_archive() ) {
			return $canonical;
		}

		$paged = max( 1, absint( get_query_var( 'paged' ) ) );
		$url   = home_url( 'sale/' );

		if ( $paged > 1 ) {
			$url = home_url( 'sale/page/' . $paged . '/' );
		}

		return esc_url_raw( $url );
	}
}

MFN_Child_Sale_Archive::init();
