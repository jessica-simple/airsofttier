<?php
/** Identify reseller listings in the WooCommerce admin product list. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Admin_Products {
	const OWNER_META_KEY = '_airsoft_reseller_owner';
	const FILTER_KEY     = 'product_source';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_reseller_products_menu' ) );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'product_name_label' ), 20, 2 );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'source_filter' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_products' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_styles' ) );
	}

	public static function register_reseller_products_menu() {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Reseller Products', 'product-manager' ),
			__( 'Reseller Products', 'product-manager' ),
			'edit_products',
			'edit.php?post_type=product&product_source=reseller'
		);
	}

	public static function product_name_label( $column, $product_id ) {
		if ( 'name' !== $column ) {
			return;
		}
		$owner = absint( get_post_meta( $product_id, self::OWNER_META_KEY, true ) );
		if ( $owner ) {
			$reseller = get_userdata( $owner );
			$name     = $reseller ? $reseller->display_name : sprintf( __( 'Account #%d', 'product-manager' ), $owner );
			echo '<br><span class="airsoft-admin-reseller-label">' . esc_html__( 'Reseller listing', 'product-manager' ) . '</span>';
			echo '<br><span class="airsoft-admin-reseller-owner">' . esc_html( sprintf( __( 'Seller: %s', 'product-manager' ), $name ) ) . '</span>';
		}
	}

	public static function admin_styles() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-product' !== $screen->id ) {
			return;
		}
		echo '<style>.airsoft-admin-reseller-label{display:inline-block;margin-top:3px;padding:2px 7px;border-radius:10px;background:#fff0d6;color:#704600;font-size:11px;font-weight:600;line-height:1.5}.airsoft-admin-reseller-owner{display:inline-block;margin-top:3px;color:#646970;font-size:12px}</style>';
	}

	public static function source_filter( $post_type ) {
		if ( 'product' !== $post_type ) {
			return;
		}
		$selected = isset( $_GET[ self::FILTER_KEY ] ) ? sanitize_key( wp_unslash( $_GET[ self::FILTER_KEY ] ) ) : '';
		echo '<label class="screen-reader-text" for="airsoft-product-source">' . esc_html__( 'Filter by product source', 'product-manager' ) . '</label>';
		echo '<select id="airsoft-product-source" name="' . esc_attr( self::FILTER_KEY ) . '">';
		echo '<option value="">' . esc_html__( 'All product sources', 'product-manager' ) . '</option>';
		echo '<option value="store" ' . selected( $selected, 'store', false ) . '>' . esc_html__( 'Store products', 'product-manager' ) . '</option>';
		echo '<option value="reseller" ' . selected( $selected, 'reseller', false ) . '>' . esc_html__( 'Reseller products', 'product-manager' ) . '</option>';
		echo '</select>';
	}

	public static function filter_products( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'product' !== $query->get( 'post_type' ) ) {
			return;
		}
		$source = isset( $_GET[ self::FILTER_KEY ] ) ? sanitize_key( wp_unslash( $_GET[ self::FILTER_KEY ] ) ) : '';
		if ( 'reseller' === $source ) {
			$meta_query = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'key'     => self::OWNER_META_KEY,
				'value'   => '0',
				'compare' => '>',
				'type'    => 'NUMERIC',
			);
			$query->set( 'meta_query', $meta_query );
		} elseif ( 'store' === $source ) {
			$meta_query = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => self::OWNER_META_KEY,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => self::OWNER_META_KEY,
					'value'   => '0',
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
}
