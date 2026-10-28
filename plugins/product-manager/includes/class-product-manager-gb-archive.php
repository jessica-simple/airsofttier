<?php
/**
 * Active group-buy products at /gb-deals/, using the native shop archive.
 * BeTheme continues to provide the layout, filters, sorting, and pagination.
 */
class Airsoft_Product_Manager_GB_Deals_Archive {

	const QUERY_VAR    = 'mfn_gb_deals_archive';
	const FLUSH_OPTION = 'mfn_child_gb_deals_rules_flushed_v1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ), 5 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 20 );
		add_action( 'after_switch_theme', array( __CLASS__, 'flush_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ), 0 );

		// Run after WooCommerce and BeTheme have added their catalog filters.
		add_action( 'pre_get_posts', array( __CLASS__, 'limit_product_query_to_deals' ), 999 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'disable_canonical_redirect' ), 10, 2 );
		add_filter( 'woocommerce_page_title', array( __CLASS__, 'page_title' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'canonical_url' ) );
		add_filter( 'rank_math/frontend/canonical', array( __CLASS__, 'canonical_url' ) );
	}

	public static function add_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function register_rewrite_rules() {
		add_rewrite_rule(
			'^gb-deals/page/([0-9]+)/?$',
			'index.php?post_type=product&' . self::QUERY_VAR . '=1&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^gb-deals/?$',
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

	private static function is_deals_archive() {
		return (bool) get_query_var( self::QUERY_VAR ) && is_post_type_archive( 'product' );
	}

	public static function limit_product_query_to_deals( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->get( self::QUERY_VAR ) || ! $query->is_post_type_archive( 'product' ) ) {
			return;
		}

		$existing_meta_query = $query->get( 'meta_query' );
		$meta_query          = array( 'relation' => 'AND' );

		// Nest existing OR filters so they cannot admit products outside GB deals.
		if ( is_array( $existing_meta_query ) && ! empty( $existing_meta_query ) ) {
			$meta_query[] = $existing_meta_query;
		}

		$meta_query[] = array(
			'key'     => '_gb_enabled',
			'value'   => 'yes',
			'compare' => '=',
		);

		// Match the GB plugin's active-deal rules and site-local end dates.
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_gb_expiry',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_gb_expiry',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_gb_expiry',
				'value'   => current_time( 'mysql' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			),
		);

		$query->set( 'meta_query', $meta_query );
	}

	public static function disable_canonical_redirect( $redirect_url, $requested_url ) {
		return self::is_deals_archive() ? false : $redirect_url;
	}

	public static function page_title( $title ) {
		return self::is_deals_archive() ? __( 'GB Deals', 'betheme-child' ) : $title;
	}

	public static function canonical_url( $canonical ) {
		if ( ! self::is_deals_archive() ) {
			return $canonical;
		}

		$paged = max( 1, absint( get_query_var( 'paged' ) ) );
		$url   = home_url( 'gb-deals/' );
		if ( $paged > 1 ) {
			$url = home_url( 'gb-deals/page/' . $paged . '/' );
		}

		return esc_url_raw( $url );
	}
}

Airsoft_Product_Manager_GB_Deals_Archive::init();
