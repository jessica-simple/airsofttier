<?php
/**
 * My Account navigation.
 *
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );

$csw_icons = array(
	'dashboard'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
	'orders'          => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/></svg>',
	'downloads'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
	'edit-address'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
	'edit-account'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
	'payment-methods' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
	'customer-logout' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
	'wishlist'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
	'coupons'         => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="22" height="13" rx="2"/><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg>',
	'change-password' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
);

$account_items = wc_get_account_menu_items();
$wishlist_url  = function_exists( 'YITH_WCWL' ) ? YITH_WCWL()->get_wishlist_url() : wc_get_page_permalink( 'shop' );
$csw_items     = array(
	'dashboard'       => array( 'label' => $account_items['dashboard'] ?? __( 'Dashboard', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'dashboard' ), 'active' => wc_is_current_account_menu_item( 'dashboard' ) ),
	'orders'          => array( 'label' => $account_items['orders'] ?? __( 'Orders', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'orders' ), 'active' => wc_is_current_account_menu_item( 'orders' ) ),
	'wishlist'        => array( 'label' => __( 'Wishlist', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'wishlist' ), 'active' => wc_is_current_account_menu_item( 'wishlist' ) ),
	'coupons'         => array( 'label' => __( 'Coupons', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'coupons' ), 'active' => wc_is_current_account_menu_item( 'coupons' ) ),
	'edit-address'    => array( 'label' => $account_items['edit-address'] ?? __( 'Addresses', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'edit-address' ), 'active' => wc_is_current_account_menu_item( 'edit-address' ) ),
	'edit-account'    => array( 'label' => $account_items['edit-account'] ?? __( 'Account Details', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'edit-account' ), 'active' => wc_is_current_account_menu_item( 'edit-account' ) ),
	'change-password' => array( 'label' => __( 'Change Password', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'edit-account' ), 'active' => false ),
	'customer-logout' => array( 'label' => $account_items['customer-logout'] ?? __( 'Log Out', 'woocommerce' ), 'url' => wc_get_account_endpoint_url( 'customer-logout' ), 'active' => false ),
);
?>

<nav class="woocommerce-MyAccount-navigation csw-account-sidebar" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<ul>
		<?php foreach ( $csw_items as $endpoint => $item ) : ?>
			<li class="<?php echo esc_attr( $item['active'] ? 'is-active' : '' ); ?>">
				<a class="csw-sidebar-item" href="<?php echo esc_url( $item['url'] ); ?>" <?php echo $item['active'] ? 'aria-current="page"' : ''; ?>>
					<?php echo $csw_icons[ $endpoint ] ?? $csw_icons['dashboard']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
