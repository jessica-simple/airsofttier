<?php
/**
 * Plugin Name: Product Manager
 * Description: Manage WooCommerce product categories and group-buy deals.
 * Version: 1.0.0
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
Airsoft_Product_Manager_Cart_Followups::init();
Airsoft_Product_Manager_Invalid_Cart::init();
Airsoft_Product_Manager_Notifications::init();

register_activation_hook( AIRSOFT_PRODUCT_MANAGER_FILE, array( 'Airsoft_Product_Manager_Cart_Followups', 'activate' ) );
register_deactivation_hook( AIRSOFT_PRODUCT_MANAGER_FILE, array( 'Airsoft_Product_Manager_Cart_Followups', 'deactivate' ) );
