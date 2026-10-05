<?php
/**
 * Custom My Account dashboard.
 *
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id    = get_current_user_id();
$customer       = new WC_Customer( $customer_id );
$display_name   = $current_user->display_name ?: $current_user->user_login;
$phone          = $customer->get_billing_phone();
$joined         = date_i18n( get_option( 'date_format' ), strtotime( $current_user->user_registered ) );
$myaccount_url  = wc_get_page_permalink( 'myaccount' );
$orders_url     = wc_get_account_endpoint_url( 'orders' );
$address_url    = wc_get_account_endpoint_url( 'edit-address' );
$account_url    = wc_get_account_endpoint_url( 'edit-account' );
$wishlist_url   = wc_get_account_endpoint_url( 'wishlist' );
$coupons_url    = wc_get_account_endpoint_url( 'coupons' );
$recent_orders  = wc_get_orders(
	array(
		'customer' => $customer_id,
		'limit'    => 3,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'status'   => array_keys( wc_get_order_statuses() ),
	)
);
$active_orders  = wc_get_orders(
	array(
		'customer' => $customer_id,
		'limit'    => -1,
		'return'   => 'ids',
		'status'   => array( 'pending', 'processing', 'on-hold' ),
	)
);
$shipping_parts = array_filter( array( $customer->get_shipping_address_1(), $customer->get_shipping_address_2(), $customer->get_shipping_city(), $customer->get_shipping_state(), $customer->get_shipping_postcode(), $customer->get_shipping_country() ) );
$billing_parts  = array_filter( array( $customer->get_billing_address_1(), $customer->get_billing_address_2(), $customer->get_billing_city(), $customer->get_billing_state(), $customer->get_billing_postcode(), $customer->get_billing_country() ) );
?>

<h1 class="csw-page-title"><?php esc_html_e( 'My Account', 'woocommerce' ); ?></h1>

<div class="csw-dashboard">
	<div class="csw-welcome-card">
		<div>
			<div class="csw-welcome-greeting"><?php esc_html_e( 'Welcome back,', 'woocommerce' ); ?></div>
			<div class="csw-welcome-name">
				<?php echo esc_html( $display_name ); ?>
				<!-- <span class="csw-vip-badge"><?php esc_html_e( 'VIP Gold', 'woocommerce' ); ?></span> -->
			</div>
			<div class="csw-welcome-sub"><?php esc_html_e( "Here's what's happening with your account today.", 'woocommerce' ); ?></div>
			<div class="csw-stats-row">
				<div class="csw-stat"><div class="csw-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/></svg></div><div><div class="csw-stat-val"><?php echo esc_html( count( $active_orders ) ); ?></div><div class="csw-stat-label"><?php esc_html_e( 'Active Orders', 'woocommerce' ); ?></div></div></div>
				<div class="csw-stat"><div class="csw-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><div><div class="csw-stat-val">0</div><div class="csw-stat-label"><?php esc_html_e( 'Wishlist Items', 'woocommerce' ); ?></div></div></div>
				<div class="csw-stat"><div class="csw-stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="22" height="13" rx="2"/><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg></div><div><div class="csw-stat-val"><?php echo esc_html( count( betheme_child_get_active_coupons() ) ); ?></div><div class="csw-stat-label"><?php esc_html_e( 'Coupons', 'woocommerce' ); ?></div></div></div>
			</div>
		</div>
	</div>

	<div class="csw-quick-links">
		<a href="<?php echo esc_url( $orders_url ); ?>" class="csw-quick-link"><div class="csw-ql-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/></svg></div><div class="csw-ql-title"><?php esc_html_e( 'My Orders', 'woocommerce' ); ?></div><div class="csw-ql-desc"><?php esc_html_e( 'View and track your orders', 'woocommerce' ); ?></div><div class="csw-ql-arrow">&rsaquo;</div></a>
		<a href="<?php echo esc_url( $wishlist_url ); ?>" class="csw-quick-link"><div class="csw-ql-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><div class="csw-ql-title"><?php esc_html_e( 'Wishlist', 'woocommerce' ); ?></div><div class="csw-ql-desc"><?php esc_html_e( 'Your saved items', 'woocommerce' ); ?></div><div class="csw-ql-arrow">&rsaquo;</div></a>
		<a href="<?php echo esc_url( $coupons_url ); ?>" class="csw-quick-link"><div class="csw-ql-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="22" height="13" rx="2"/><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg></div><div class="csw-ql-title"><?php esc_html_e( 'Coupons', 'woocommerce' ); ?></div><div class="csw-ql-desc"><?php esc_html_e( 'View available coupons', 'woocommerce' ); ?></div><div class="csw-ql-arrow">&rsaquo;</div></a>
		<a href="<?php echo esc_url( $address_url ); ?>" class="csw-quick-link"><div class="csw-ql-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div><div class="csw-ql-title"><?php esc_html_e( 'Addresses', 'woocommerce' ); ?></div><div class="csw-ql-desc"><?php esc_html_e( 'Manage shipping and billing', 'woocommerce' ); ?></div><div class="csw-ql-arrow">&rsaquo;</div></a>
	</div>

	<div class="csw-bottom-row">
		<div class="csw-card">
			<div class="csw-card-header"><div class="csw-card-title"><?php esc_html_e( 'Recent Orders', 'woocommerce' ); ?></div><a href="<?php echo esc_url( $orders_url ); ?>" class="csw-view-all"><?php esc_html_e( 'View all orders', 'woocommerce' ); ?> &rsaquo;</a></div>
			<?php if ( $recent_orders ) : ?>
				<?php foreach ( $recent_orders as $order ) : ?>
					<a class="csw-order-row" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
						<div class="csw-order-img"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
						<div class="csw-order-info"><div class="csw-order-num"><?php echo esc_html( sprintf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ) ); ?></div><div class="csw-order-date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></div><div class="csw-order-items"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $order->get_item_count(), 'woocommerce' ), $order->get_item_count() ) ); ?></div></div>
						<div class="csw-order-status-price"><span class="csw-status-badge csw-status-<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span><span class="csw-order-price"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></div>
						<span class="csw-order-chevron">&rsaquo;</span>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="csw-empty-row"><?php esc_html_e( 'No recent orders yet.', 'woocommerce' ); ?></div>
			<?php endif; ?>
		</div>
		<div class="csw-right-col">
			<div class="csw-card">
				<div class="csw-card-header"><div class="csw-card-title"><?php esc_html_e( 'Account Details', 'woocommerce' ); ?></div><a href="<?php echo esc_url( $account_url ); ?>" class="csw-view-all"><?php esc_html_e( 'Edit', 'woocommerce' ); ?></a></div>
				<div class="csw-account-row"><span class="csw-acct-label"><?php esc_html_e( 'Name', 'woocommerce' ); ?></span><span class="csw-acct-val"><?php echo esc_html( $display_name ); ?></span></div>
				<div class="csw-account-row"><span class="csw-acct-label"><?php esc_html_e( 'Email', 'woocommerce' ); ?></span><span class="csw-acct-val"><?php echo esc_html( $current_user->user_email ); ?></span></div>
				<div class="csw-account-row"><span class="csw-acct-label"><?php esc_html_e( 'Phone', 'woocommerce' ); ?></span><span class="csw-acct-val"><?php echo esc_html( $phone ?: __( 'Not set', 'woocommerce' ) ); ?></span></div>
				<div class="csw-account-row"><span class="csw-acct-label"><?php esc_html_e( 'Joined', 'woocommerce' ); ?></span><span class="csw-acct-val"><?php echo esc_html( $joined ); ?></span></div>
			</div>
			<div class="csw-card">
				<div class="csw-card-header"><div class="csw-card-title"><?php esc_html_e( 'Default Addresses', 'woocommerce' ); ?></div><a href="<?php echo esc_url( $address_url ); ?>" class="csw-view-all"><?php esc_html_e( 'Manage Addresses', 'woocommerce' ); ?></a></div>
				<div class="csw-addr-grid">
					<div class="csw-addr-block"><div class="csw-addr-type"><?php esc_html_e( 'Shipping Address', 'woocommerce' ); ?></div><div class="csw-addr-empty"><?php echo $shipping_parts ? esc_html( implode( ', ', $shipping_parts ) ) : esc_html__( 'No address set', 'woocommerce' ); ?></div><a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping', $myaccount_url ) ); ?>" class="csw-addr-add"><?php esc_html_e( 'Add address', 'woocommerce' ); ?></a></div>
					<div class="csw-addr-block"><div class="csw-addr-type"><?php esc_html_e( 'Billing Address', 'woocommerce' ); ?></div><div class="csw-addr-empty"><?php echo $billing_parts ? esc_html( implode( ', ', $billing_parts ) ) : esc_html__( 'No address set', 'woocommerce' ); ?></div><a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'billing', $myaccount_url ) ); ?>" class="csw-addr-add"><?php esc_html_e( 'Add address', 'woocommerce' ); ?></a></div>
				</div>
			</div>
		</div>
	</div>
	<!-- <div class="csw-refer-banner">
		<div class="csw-refer-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7Z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7Z"/></svg></div>
		<div class="csw-refer-text"><div class="csw-refer-title"><?php esc_html_e( 'Refer a Friend, Get Rewards!', 'woocommerce' ); ?></div><div class="csw-refer-desc"><?php esc_html_e( 'Share the gear you love and earn rewards when your friends make a purchase.', 'woocommerce' ); ?></div></div>
		<a class="csw-refer-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Refer Now', 'woocommerce' ); ?></a>
	</div> -->
</div>

<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
