<?php
/**
 * Plugin Name: WooCommerce Group Buy & Tiered Pricing (Legacy Compatibility)
 * Description: Compatibility loader for group-buy features now managed by Product Manager.
 * Version: 1.0.0
 * Author: Airsoft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Retain existing installations and settings while the migrated plugin is enabled.
if ( ! defined( 'AIRSOFT_PRODUCT_MANAGER_FILE' ) ) {
	define( 'AIRSOFT_PRODUCT_MANAGER_FILE', __DIR__ . '/../product-manager/product-manager.php' );
}
require_once AIRSOFT_PRODUCT_MANAGER_FILE;