<?php
/**
 * Plugin Name: Product Manager
 * Description: Manage WooCommerce product categories and group-buy deals.
 * Version: 1.6.0
 * Author: Airsoft
 * Text Domain: product-manager
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'AIRSOFT_PRODUCT_MANAGER_FILE' ) ) {
	define( 'AIRSOFT_PRODUCT_MANAGER_FILE', __FILE__ );
}

if ( ! defined( 'AIRSOFT_PRODUCT_MANAGER_DIR' ) ) {
	define( 'AIRSOFT_PRODUCT_MANAGER_DIR', plugin_dir_path( AIRSOFT_PRODUCT_MANAGER_FILE ) );
}
if ( ! defined( 'AIRSOFT_PRODUCT_MANAGER_URL' ) ) {
	define( 'AIRSOFT_PRODUCT_MANAGER_URL', plugin_dir_url( AIRSOFT_PRODUCT_MANAGER_FILE ) );
}

require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-category-page.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/group-buy.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-gb-archive.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-gb-countdown.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-cart-followups.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-invalid-cart.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-notifications.php';

Airsoft_Product_Manager_Category_Page::init();

require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-emails.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-resellers.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-portal.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-pricing.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-products.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-storefront-product-sources.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-admin-products.php';
require_once AIRSOFT_PRODUCT_MANAGER_DIR . 'includes/class-product-manager-reseller-orders.php';
Airsoft_Product_Manager_Resellers::init();
Airsoft_Product_Manager_Reseller_Emails::init();
Airsoft_Product_Manager_Reseller_Portal::init();
Airsoft_Product_Manager_Reseller_Pricing::init();
Airsoft_Product_Manager_Reseller_Products::init();
Airsoft_Product_Manager_Reseller_Admin_Products::init();
Airsoft_Product_Manager_Reseller_Orders::init();
Airsoft_Product_Manager_Cart_Followups::init();
Airsoft_Product_Manager_Invalid_Cart::init();
Airsoft_Product_Manager_Notifications::init();

add_action( 'restrict_manage_posts', static function ( $post_type, $which ) {
	if ( 'product' !== $post_type || 'top' !== $which || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}
	?>
	<label class="screen-reader-text" for="airsoft-brand-status"><?php esc_html_e( 'Filter by brand assignment', 'product-manager' ); ?></label>
	<select name="airsoft_brand_status" id="airsoft-brand-status">
		<option value=""><?php esc_html_e( 'Any brand status', 'product-manager' ); ?></option>
		<option value="none" <?php selected( $_GET['airsoft_brand_status'] ?? '', 'none' ); ?>><?php esc_html_e( 'No brand', 'product-manager' ); ?></option>
	</select>
	<?php
}, 10, 2 );

add_action( 'pre_get_posts', static function ( $query ) {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query()
		|| 'product' !== $query->get( 'post_type' ) || ! taxonomy_exists( 'product_brand' )
		|| 'none' !== ( $_GET['airsoft_brand_status'] ?? '' ) ) {
		return;
	}

	$tax_query = array(
		'relation' => 'AND',
		array( 'taxonomy' => 'product_brand', 'operator' => 'NOT EXISTS' ),
	);
	$existing = $query->get( 'tax_query' );
	if ( ! empty( $existing ) ) {
		$tax_query[] = $existing;
	}
	$query->set( 'tax_query', $tax_query );
}, 20 );

register_activation_hook( AIRSOFT_PRODUCT_MANAGER_FILE, array( 'Airsoft_Product_Manager_Cart_Followups', 'activate' ) );
register_deactivation_hook( AIRSOFT_PRODUCT_MANAGER_FILE, array( 'Airsoft_Product_Manager_Cart_Followups', 'deactivate' ) );
