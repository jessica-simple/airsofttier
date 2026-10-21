<?php
/**
 * Expose a rolling, date-limited WooCommerce product archive at /new-arrivals/.
 *
 * The route intentionally uses WooCommerce's native product archive query so
 * Betheme's Shop Archive template and the existing product filters continue
 * to handle markup, counts, sorting, pagination, and AJAX refreshes.
 */
class MFN_Child_New_Arrivals_Archive {

	const QUERY_VAR           = 'mfn_new_arrivals_archive';
	const FLUSH_OPTION        = 'mfn_child_new_arrivals_rules_flushed_v1';
	const DEFAULT_PERIOD_DAYS = 30;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ), 5 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 20 );
		add_action( 'after_switch_theme', array( __CLASS__, 'flush_rewrite_rules' ) );

		add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ), 0 );
		add_action( 'woocommerce_product_query', array( __CLASS__, 'limit_product_query_to_recent_products' ), 999, 2 );

		// Keep WordPress from redirecting this virtual archive to the Shop page.
		add_filter( 'redirect_canonical', array( __CLASS__, 'disable_canonical_redirect' ), 10, 2 );

		// Use a useful archive title without changing the existing Shop title.
		add_filter( 'woocommerce_page_title', array( __CLASS__, 'page_title' ) );

		// Give common SEO integrations a stable, crawlable archive canonical.
		add_filter( 'wpseo_canonical', array( __CLASS__, 'canonical_url' ) );
		add_filter( 'rank_math/frontend/canonical', array( __CLASS__, 'canonical_url' ) );
	}

	public static function add_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function register_rewrite_rules() {
		add_rewrite_rule(
			'^new-arrivals/page/([0-9]+)/?$',
			'index.php?post_type=product&' . self::QUERY_VAR . '=1&paged=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^new-arrivals/?$',
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
	 * Whether the current request is the virtual New Arrivals archive.
	 */
	private static function is_new_arrivals_archive() {
		return (bool) get_query_var( self::QUERY_VAR ) && is_post_type_archive( 'product' );
	}

	/**
	 * Return the configured rolling period in days.
	 *
	 * The filter allows a site-specific override without changing this class.
	 */
	private static function period_days() {
		$days = apply_filters( 'mfn_child_new_arrivals_period_days', self::DEFAULT_PERIOD_DAYS );
		return max( 1, absint( $days ) );
	}

	/**
	 * Constrain the native WooCommerce archive query by post publication date.
	 *
	 * date_query uses post_date, which WordPress stores in the site's local
	 * timezone. current_datetime() keeps the rolling cutoff in that same
	 * timezone instead of using post_modified or a UTC-only calculation.
	 */
	public static function limit_product_query_to_recent_products( $query ) {
		if ( ! self::is_new_arrivals_archive() ) {
			return;
		}

		$now    = current_datetime();
		$cutoff = $now->modify( '-' . self::period_days() . ' days' );

		$existing_date_query = $query->get( 'date_query' );
		$date_query          = array( 'relation' => 'AND' );

		// Nest any pre-existing date constraint so it cannot weaken this
		// archive's eligibility rule if it uses an OR relation.
		if ( is_array( $existing_date_query ) && ! empty( $existing_date_query ) ) {
			$date_query[] = $existing_date_query;
		}

		$date_query[] = array(
			'column'    => 'post_date',
			'after'     => $cutoff->format( 'Y-m-d H:i:s' ),
			'before'    => $now->format( 'Y-m-d H:i:s' ),
			'inclusive' => true,
		);

		$query->set( 'date_query', $date_query );

		// WooCommerce and Betheme may provide a configured catalog default. The
		// New Arrivals default is always newest publication date, while an
		// explicit ?orderby= selection remains untouched.
		if ( ! self::has_requested_orderby() ) {
			$query->set( 'orderby', 'date ID' );
			$query->set( 'order', 'DESC' );
			$query->set( 'meta_key', '' );
		}
	}

	private static function has_requested_orderby() {
		if ( ! isset( $_GET['orderby'] ) ) {
			return false;
		}

		$orderby = wp_unslash( $_GET['orderby'] );
		return is_string( $orderby ) && '' !== trim( $orderby );
	}

	public static function disable_canonical_redirect( $redirect_url, $requested_url ) {
		return self::is_new_arrivals_archive() ? false : $redirect_url;
	}

	public static function page_title( $title ) {
		return self::is_new_arrivals_archive() ? __( 'New Arrivals', 'betheme-child' ) : $title;
	}

	public static function canonical_url( $canonical ) {
		if ( ! self::is_new_arrivals_archive() ) {
			return $canonical;
		}

		$paged = max( 1, absint( get_query_var( 'paged' ) ) );
		$url   = home_url( 'new-arrivals/' );

		if ( $paged > 1 ) {
			$url = home_url( 'new-arrivals/page/' . $paged . '/' );
		}

		return esc_url_raw( $url );
	}
}

MFN_Child_New_Arrivals_Archive::init();
