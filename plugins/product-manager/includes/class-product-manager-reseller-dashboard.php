<?php
/** Reseller portal presentation. All totals and records come from seller-owned data. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Dashboard {
 public static function link( $section, $args = array() ) {
  return add_query_arg( array_merge( $args, array( 'section' => $section ) ), Airsoft_Product_Manager_Resellers::url() );
 }

 private static function icon( $name ) { return '<span class="dashicons dashicons-' . esc_attr( $name ) . '" aria-hidden="true"></span>'; }

 private static function money( $totals ) {
  if ( ! $totals ) { $totals = array( get_woocommerce_currency() => 0 ); }
  $output = array();
  foreach ( $totals as $currency => $amount ) { $output[] = '<span class="airsoft-money">' . wp_kses_post( wc_price( $amount, array( 'currency' => $currency ) ) ) . '</span>'; }
  return implode( ' ', $output );
 }

 public static function render( $section ) {
  if ( ! Airsoft_Product_Manager_Resellers::approved() || ! get_current_user_id() ) { return; }
  $pages = array(
   'overview' => array( 'Overview', 'Your products, orders and earnings at a glance.' ),
   'products' => array( 'My products', 'Manage your listings, stock and prices.' ),
   'product-edit' => array( absint( $_GET['edit_product'] ?? 0 ) ? 'Edit product' : 'Add product', 'Prepare a listing for admin approval.' ),
   'sales' => array( 'Customer orders', 'Prepare shipments and update tracking for your items.' ),
   'earnings' => array( 'Earnings', 'Review your eligible earnings and payout records.' ),
   'purchases' => array( 'My purchases', 'Orders you placed using your reseller account.' ),
   'profile' => array( 'Profile & addresses', 'Manage your reseller account details.' ),
   'billing' => array( 'Billing address', 'Update the billing address for your purchases.' ),
   'shipping' => array( 'Shipping address', 'Update where your reseller purchases are delivered.' ),
   'notifications' => array( 'Notifications', 'Updates for your reseller account.' ),
  );
  if ( ! isset( $pages[$section] ) ) { $section = 'overview'; }
  if ( 'notifications' === $section && ! class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { $section = 'overview'; }
  $summary = in_array( $section, array( 'overview', 'earnings' ), true ) ? Airsoft_Product_Manager_Reseller_Orders::portal_summary() : null;
  $business = get_user_meta( get_current_user_id(), '_airsoft_reseller_business', true );
  $name = get_bloginfo( 'name' );
  $shop = wc_get_page_permalink( 'shop' );
  $heading = 'overview' === $section ? 'Welcome back, ' . ( $business ? $business : 'reseller' ) . '.' : $pages[$section][0];
  echo '<div class="airsoft-portal-shell airsoft-portal-workspace"><div class="airsoft-portal-body"><aside class="airsoft-portal-sidebar"><a class="airsoft-portal-brand" href="' . esc_url( home_url( '/' ) ) . '">' . self::icon( 'store' ) . '<div><strong>' . esc_html( $name ? $name : 'Store' ) . '</strong><span>Reseller workspace</span></div></a><div class="airsoft-sidebar-business">' . self::icon( 'businessperson' ) . '<div><strong>' . esc_html( $business ? $business : 'Reseller account' ) . '</strong><span class="airsoft-approved-status">Approved reseller</span></div></div><nav aria-label="Reseller pages">';
  $groups = array(
   'Selling' => array( 'overview' => array( 'Overview', 'dashboard' ), 'products' => array( 'My products', 'archive' ), 'sales' => array( 'Customer orders', 'clipboard' ), 'earnings' => array( 'Earnings', 'money-alt' ) ),
   'Buying' => array( 'purchases' => array( 'My purchases', 'cart' ) ),
   'Account' => array( 'profile' => array( 'Profile & addresses', 'admin-users' ) ),
  );
  if ( class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { $groups['Account']['notifications'] = array( 'Notifications', 'bell' ); }
  foreach ( $groups as $label => $links ) {
   echo '<div class="airsoft-nav-group"><p class="airsoft-nav-label">' . esc_html( $label ) . '</p>';
   foreach ( $links as $key => $details ) {
    $active = $section === $key || ( 'products' === $key && 'product-edit' === $section ) || ( 'profile' === $key && in_array( $section, array( 'billing', 'shipping' ), true ) );
    echo '<a class="airsoft-nav-link" href="' . esc_url( self::link( $key ) ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>' . self::icon( $details[1] ) . '<span>' . esc_html( $details[0] ) . '</span></a>';
   }
   echo '</div>';
  }
  echo '</nav><div class="airsoft-sidebar-account"><span>Reseller portal</span><a href="' . esc_url( wp_logout_url( Airsoft_Product_Manager_Reseller_Portal::url( 'login' ) ) ) . '">' . self::icon( 'exit' ) . 'Log out</a></div></aside><div class="airsoft-portal-workarea"><header class="airsoft-portal-topbar"><nav class="airsoft-workspace-location" aria-label="Workspace location"><a href="' . esc_url( self::link( 'overview' ) ) . '">Workspace</a><span aria-hidden="true">/</span><span>' . esc_html( $pages[$section][0] ) . '</span></nav><a class="airsoft-visit-shop" href="' . esc_url( $shop ? $shop : home_url( '/' ) ) . '">Visit shop ' . self::icon( 'external' ) . '</a></header><main class="airsoft-portal-main"><div class="airsoft-portal-title"><div><h2>' . esc_html( $heading ) . '</h2><p class="airsoft-muted">' . esc_html( $pages[$section][1] ) . '</p></div>';
  if ( in_array( $section, array( 'overview', 'products' ), true ) ) { echo '<a class="airsoft-button airsoft-button-primary" href="' . esc_url( self::link( 'product-edit' ) ) . '">' . self::icon( 'plus-alt2' ) . 'Add product</a>'; }
  echo '</div>';
  wc_print_notices();
  if ( 'overview' === $section ) { self::overview( $summary ); }
  elseif ( 'products' === $section ) { Airsoft_Product_Manager_Reseller_Products::render(); }
  elseif ( 'product-edit' === $section ) { Airsoft_Product_Manager_Reseller_Products::form(); }
  elseif ( 'sales' === $section ) { self::sales(); }
  elseif ( 'earnings' === $section ) { self::earnings( $summary ); }
  elseif ( 'purchases' === $section ) { Airsoft_Product_Manager_Reseller_Portal::purchases(); }
  elseif ( in_array( $section, array( 'profile', 'billing', 'shipping' ), true ) ) {
   echo '<section class="airsoft-panel"><nav class="airsoft-account-tabs" aria-label="Account settings">';
   foreach ( array( 'profile' => 'Account details', 'billing' => 'Billing address', 'shipping' => 'Shipping address' ) as $key => $label ) { echo '<a href="' . esc_url( self::link( $key ) ) . '"' . ( $section === $key ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>'; }
   echo '</nav>';
   if ( 'profile' === $section ) { WC_Shortcode_My_Account::edit_account(); } else { WC_Shortcode_My_Account::edit_address( $section ); }
   echo '</section>';
  } elseif ( 'notifications' === $section && class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { echo '<section class="airsoft-panel">'; Airsoft_Product_Manager_Notifications::render(); echo '</section>'; }
  echo '</main></div></div></div>';
 }

 private static function stat( $label, $value, $note ) {
  echo '<div class="airsoft-stat"><p class="airsoft-muted">' . esc_html( $label ) . '</p><div class="airsoft-stat-value">' . $value . '</div><p class="airsoft-muted">' . esc_html( $note ) . '</p></div>';
 }

 private static function overview( $summary ) {
  $counts = Airsoft_Product_Manager_Reseller_Products::counts();
  echo '<section class="airsoft-shipping-attention" aria-label="Shipment tasks"><span class="airsoft-attention-icon">' . self::icon( 'car' ) . '</span><div><strong>' . ( $summary['to_ship'] ? absint( $summary['to_ship'] ) . ' order(s) ready to ship' : 'All caught up' ) . '</strong><p class="airsoft-muted">' . ( $summary['to_ship'] ? 'Paid orders waiting for your shipment update' : 'No paid orders currently need shipment' ) . '</p></div><a class="airsoft-button" href="' . esc_url( self::link( 'sales' ) ) . '">View orders</a></section><div class="airsoft-stats">';
  self::stat( 'Live products', esc_html( $counts['publish'] ), $counts['pending'] . ' awaiting approval · ' . $counts['draft'] . ' draft(s)' );
  self::stat( 'Eligible for payout', self::money( $summary['eligible'] ), 'After commission, item refunds and recorded payouts' );
  self::stat( 'Payouts recorded', self::money( $summary['paid'] ), 'Payments recorded by the administrator' );
  echo '</div><section class="airsoft-panel"><div class="airsoft-panel-heading"><h3>Orders needing attention</h3><a href="' . esc_url( self::link( 'sales' ) ) . '">View all orders</a></div>';
  self::order_table( $summary['attention'], 'No orders currently need shipment.' );
  echo '<div class="airsoft-orders-footnote"><span>Only your items are shown</span><span>Tracking is shared with the customer</span></div></section>';
 }

 private static function order_table( $entries, $empty ) {
  echo '<div class="airsoft-table-wrap airsoft-order-list"><table class="airsoft-table airsoft-order-table"><thead><tr><th scope="col">Order / item</th><th scope="col">Item total</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>';
  foreach ( $entries as $entry ) {
   $order = $entry['order']; $item = $entry['item'];
   $label = wc_get_order_status_name( $order->get_status() );
   if ( $entry['needs_shipment'] ) { $label = 'To ship'; }
   elseif ( $entry['tracking'] && $order->has_status( array( 'processing', 'completed' ) ) ) { $label = 'Shipped'; }
   $url = self::link( 'sales', array( 'order_id' => $order->get_id() ) ) . '#reseller-item-' . $item->get_id();
   echo '<tr><td><strong>#' . esc_html( $order->get_order_number() ) . '</strong><span class="airsoft-cell-detail">' . esc_html( $item->get_name() ) . ' × ' . esc_html( $item->get_quantity() ) . '</span></td><td>' . wp_kses_post( wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) ) ) . '</td><td><span class="airsoft-badge ' . ( $entry['needs_shipment'] ? 'airsoft-badge-wait' : '' ) . '">' . esc_html( $label ) . '</span></td><td><a class="airsoft-button" href="' . esc_url( $url ) . '">' . ( $entry['can_ship'] && ! $entry['tracking'] ? 'Ship order' : 'View order' ) . '</a></td></tr>';
  }
  if ( ! $entries ) { echo '<tr><td colspan="4">' . esc_html( $empty ) . '</td></tr>'; }
  echo '</tbody></table></div>';
 }

 private static function sales() {
  $id = absint( $_GET['order_id'] ?? 0 );
  if ( $id ) { self::shipment( $id ); return; }
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $data = Airsoft_Product_Manager_Reseller_Orders::portal_page( $page );
  echo '<section class="airsoft-panel"><div class="airsoft-panel-heading"><h3>Customer orders</h3><span class="airsoft-muted">Only your items are shown</span></div>';
  self::order_table( $data['items'], 'No customer orders yet.' );
  self::pagination( 'sales', $page, $data['more'] );
  echo '</section>';
 }

 private static function shipment( $id ) {
  $entries = Airsoft_Product_Manager_Reseller_Orders::portal_order( $id );
  echo '<p><a href="' . esc_url( self::link( 'sales' ) ) . '">← Back to customer orders</a></p>';
  if ( ! $entries ) { echo '<section class="airsoft-panel"><p>This order is unavailable.</p></section>'; return; }
  $order = $entries[0]['order'];
  foreach ( $entries as $entry ) {
   $item = $entry['item'];
   echo '<section class="airsoft-panel" id="reseller-item-' . absint( $item->get_id() ) . '"><div class="airsoft-panel-heading"><h3>Order #' . esc_html( $order->get_order_number() ) . '</h3><span class="airsoft-badge">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span></div><p>' . esc_html( $item->get_name() ) . ' × ' . esc_html( $item->get_quantity() ) . '</p>';
   if ( $entry['tracking'] ) { echo '<p class="airsoft-panel-note">Tracking: ' . esc_html( $entry['tracking'] ) . '</p>'; }
   if ( $entry['can_ship'] ) {
    echo '<div class="airsoft-address"><h4>Deliver to</h4>' . wp_kses_post( $order->get_formatted_shipping_address() ) . '</div><form class="airsoft-shipment-form" method="post">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '">';
    Airsoft_Product_Manager_Resellers::field( 'Carrier and tracking number', 'tracking', $entry['tracking'], 'text', 'required maxlength="200"' );
    echo '<button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="ship">Save shipment</button></form>';
   } else { echo '<p class="airsoft-muted airsoft-panel-note">Shipment updates are available for paid, shippable items in processing or completed orders.</p>'; }
   echo '</section>';
  }
 }

 private static function earnings( $summary ) {
  echo '<div class="airsoft-stats">';
  self::stat( 'Eligible for payout', self::money( $summary['eligible'] ), 'Paid, completed orders with no refund hold' );
  self::stat( 'Awaiting completion or review', self::money( $summary['pending'] ), 'Earnings from paid orders still in progress or on hold' );
  self::stat( 'Payouts recorded', self::money( $summary['paid'] ), 'Payments recorded by the administrator' );
  echo '</div>';
  if ( $summary['currency_review'] ) { echo '<section class="airsoft-panel"><h3>Currency review needed</h3><p class="airsoft-panel-note">An order currency differs from its recorded payout. Contact the administrator to reconcile the amounts.</p></section>'; }
  if ( $summary['adjustments'] ) { echo '<section class="airsoft-panel"><h3>Refund adjustments</h3><p class="airsoft-panel-note">' . self::money( $summary['adjustments'] ) . '</p><p class="airsoft-muted">Contact the administrator to reconcile refunds or changes after payout.</p></section>'; }
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $data = Airsoft_Product_Manager_Reseller_Orders::portal_page( $page );
  echo '<section class="airsoft-panel"><h3>Earnings and payout records</h3><p class="airsoft-muted airsoft-panel-note">Earnings include item refunds and commission. Tax and shipping are excluded. Amounts in different currencies are shown separately.</p><div class="airsoft-table-wrap"><table class="airsoft-table"><thead><tr><th>Order / item</th><th>Commission</th><th>Earnings</th><th>Payout</th></tr></thead><tbody>';
  foreach ( $data['items'] as $entry ) {
   $item = $entry['item']; $order = $entry['order']; $paid = $entry['paid'];
   echo '<tr><td>#' . esc_html( $order->get_order_number() ) . '<span class="airsoft-cell-detail">' . esc_html( $item->get_name() ) . '</span></td><td>' . esc_html( $item->get_meta( '_airsoft_commission_rate' ) ) . '%</td><td>' . wp_kses_post( wc_price( $entry['earned'], array( 'currency' => $order->get_currency() ) ) ) . '</td><td>';
   if ( $paid ) {
    echo '<span class="airsoft-badge airsoft-badge-good">Payment recorded</span><span class="airsoft-cell-detail">' . wp_kses_post( wc_price( $paid->amount, array( 'currency' => $paid->currency ) ) ) . ' · ' . esc_html( $paid->reference ) . '</span><span class="airsoft-cell-detail">' . esc_html( $paid->paid_at ) . ' UTC</span>';
    if ( $paid->currency !== $order->get_currency() ) { echo '<span class="airsoft-cell-detail">Order currency changed. Admin reconciliation required.</span>'; }
    else {
     $delta = $entry['earned'] - (float) $paid->amount;
     if ( abs( $delta ) > 0.00001 ) { echo '<span class="airsoft-cell-detail">Adjustment: ' . wp_kses_post( wc_price( $delta, array( 'currency' => $order->get_currency() ) ) ) . '</span>'; }
    }
   } else {
    $label = $entry['earned'] <= 0 ? 'No earnings' : ( $entry['hold'] ? 'Refund review' : ( $entry['eligible'] ? 'Eligible' : 'Pending completion' ) );
    echo '<span class="airsoft-badge">' . esc_html( $label ) . '</span>';
   }
   echo '</td></tr>';
  }
  if ( ! $data['items'] ) { echo '<tr><td colspan="4">No earnings yet.</td></tr>'; }
  echo '</tbody></table></div>';
  self::pagination( 'earnings', $page, $data['more'] );
  echo '</section>';
 }

 private static function pagination( $section, $page, $more ) {
  echo '<nav class="airsoft-pagination" aria-label="Order item pages">';
  if ( $page > 1 ) { echo '<a class="airsoft-button" href="' . esc_url( self::link( $section, array( 'orders_page' => $page - 1 ) ) ) . '">Previous</a>'; }
  if ( $more ) { echo '<a class="airsoft-button" href="' . esc_url( self::link( $section, array( 'orders_page' => $page + 1 ) ) ) . '">Next</a>'; }
  echo '</nav>';
 }
}
