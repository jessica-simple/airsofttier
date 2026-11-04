<?php
/** Reseller portal presentation. All totals and records come from seller-owned data. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Dashboard {
 public static function link( $section, $args = array() ) {
  return add_query_arg( array_merge( $args, array( 'section' => $section ) ), Airsoft_Product_Manager_Resellers::url() );
 }

 private static function icon( $name ) { return '<span class="dashicons dashicons-' . esc_attr( $name ) . '" aria-hidden="true"></span>'; }

 private static function order_statuses() {
  $statuses = array();
  foreach ( wc_get_order_statuses() as $status => $label ) {
   $statuses[ preg_replace( '/^wc-/', '', $status ) ] = $label;
  }
  foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status => $details ) {
   if ( 0 === strpos( $status, 'wc-' ) ) { $statuses[ substr( $status, 3 ) ] = $details->label; }
  }
  return $statuses;
 }

 private static function seller_status_label( $status ) {
  if ( in_array( $status, array( 'confirmed', 'on-delivery' ), true ) ) { $status = 'processing'; }
  elseif ( 'complete' === $status ) { $status = 'completed'; }
  return $status ? wc_get_order_status_name( $status ) : 'Not set';
 }

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
   'bank-details' => array( 'Bank details', 'Manage the account used for reseller bank payouts.' ),
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
  echo '<div class="airsoft-portal-shell airsoft-portal-workspace"><div class="airsoft-portal-body"><aside class="airsoft-portal-sidebar" id="airsoft-reseller-drawer" aria-label="Reseller navigation"><button class="airsoft-drawer-close" type="button" aria-label="Close menu"><span aria-hidden="true">&times;</span></button><a class="airsoft-portal-brand" href="' . esc_url( home_url( '/' ) ) . '">' . self::icon( 'store' ) . '<div><strong>' . esc_html( $name ? $name : 'Store' ) . '</strong><span>Reseller workspace</span></div></a><div class="airsoft-sidebar-business">' . self::icon( 'businessperson' ) . '<div><strong>' . esc_html( $business ? $business : 'Reseller account' ) . '</strong><span class="airsoft-approved-status">Approved reseller</span></div></div><nav aria-label="Reseller pages">';
  $groups = array(
   'Selling' => array( 'overview' => array( 'Overview', 'dashboard' ), 'products' => array( 'My products', 'archive' ), 'sales' => array( 'Customer orders', 'clipboard' ), 'earnings' => array( 'Earnings', 'money-alt' ) ),
   'Buying' => array( 'purchases' => array( 'My purchases', 'cart' ) ),
   'Account' => array( 'profile' => array( 'Profile & addresses', 'admin-users' ), 'bank-details' => array( 'Bank details', 'bank' ) ),
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
  echo '</nav><div class="airsoft-sidebar-account"><span>Reseller portal</span><a href="' . esc_url( wp_logout_url( Airsoft_Product_Manager_Reseller_Portal::url( 'login' ) ) ) . '">' . self::icon( 'exit' ) . 'Log out</a></div></aside><div class="airsoft-drawer-backdrop" aria-hidden="true"></div><div class="airsoft-portal-workarea"><header class="airsoft-portal-topbar"><button class="airsoft-drawer-toggle" type="button" aria-controls="airsoft-reseller-drawer" aria-expanded="false"><span class="airsoft-drawer-toggle-icon" aria-hidden="true"><span></span><span></span><span></span></span><span>Menu</span></button><nav class="airsoft-workspace-location" aria-label="Workspace location"><a href="' . esc_url( self::link( 'overview' ) ) . '">Workspace</a><span aria-hidden="true">/</span><span>' . esc_html( $pages[$section][0] ) . '</span></nav><a class="airsoft-visit-shop" href="' . esc_url( $shop ? $shop : home_url( '/' ) ) . '">Visit shop ' . self::icon( 'external' ) . '</a></header><main class="airsoft-portal-main"><div class="airsoft-portal-title"><div><h2>' . esc_html( $heading ) . '</h2><p class="airsoft-muted">' . esc_html( $pages[$section][1] ) . '</p></div>';
  if ( in_array( $section, array( 'overview', 'products' ), true ) ) { echo '<a class="airsoft-button airsoft-button-primary" href="' . esc_url( self::link( 'product-edit' ) ) . '">' . self::icon( 'plus-alt2' ) . 'Add product</a>'; }
  echo '</div>';
  wc_print_notices();
  if ( 'overview' === $section ) { self::overview( $summary ); }
  elseif ( 'products' === $section ) { Airsoft_Product_Manager_Reseller_Products::render(); }
  elseif ( 'product-edit' === $section ) { Airsoft_Product_Manager_Reseller_Products::form(); }
  elseif ( 'sales' === $section ) { self::sales(); }
  elseif ( 'earnings' === $section ) { self::earnings( $summary ); }
  elseif ( 'bank-details' === $section ) { self::bank_details(); }
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
  self::stat( 'Awaiting payout release', self::money( $summary['eligible'] ), 'Completed reseller items awaiting admin release' );
  self::stat( 'Payouts recorded', self::money( $summary['paid'] ), 'Payments recorded by the administrator' );
  echo '</div><section class="airsoft-panel"><div class="airsoft-panel-heading"><h3>Orders needing attention</h3><a href="' . esc_url( self::link( 'sales' ) ) . '">View all orders</a></div>';
  self::order_table( $summary['attention'], 'No orders currently need shipment.' );
  echo '<div class="airsoft-orders-footnote"><span>Only your items are shown</span><span>Tracking is shared with the customer</span></div></section>';
 }

 private static function order_table( $entries, $empty ) {
  echo '<div class="airsoft-table-wrap airsoft-order-list"><table class="airsoft-table airsoft-order-table"><thead><tr><th scope="col">Order / item</th><th scope="col">Item total</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>';
  foreach ( $entries as $entry ) {
   $order = $entry['order']; $item = $entry['item'];
   $product = $item->get_product();
   $image = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
   $created = $order->get_date_created();
   $label = Airsoft_Product_Manager_Reseller_Orders::entry_status_label( $entry );
   $url = self::link( 'sales', array( 'order_id' => $order->get_id() ) ) . '#reseller-item-' . $item->get_id();
   $can_update_status = ! $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) && ! $entry['paid'] && ! $entry['wallet_credited'];
   $status_form_id = 'airsoft-order-status-' . absint( $item->get_id() );
   echo '<tr><td data-label="Order item"><div class="airsoft-order-product">';
   if ( $image ) { echo '<span class="airsoft-order-product-image">' . wp_kses_post( $image ) . '</span>'; }
   echo '<div class="airsoft-order-product-copy"><strong>' . esc_html( Airsoft_Product_Manager_Reseller_Orders::item_label( $item ) ) . '</strong><span class="airsoft-cell-detail">Order #' . esc_html( $order->get_order_number() ) . ( $created ? ' · ' . esc_html( wc_format_datetime( $created ) ) : '' ) . '</span><span class="airsoft-cell-detail">Quantity: ' . esc_html( $item->get_quantity() ) . '</span></div></div></td><td data-label="Item total"><strong class="airsoft-order-total">' . wp_kses_post( wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) ) ) . '</strong><span class="airsoft-cell-detail">After discounts · excl. tax</span></td><td data-label="Status">';
   if ( $can_update_status ) {
    echo '<form id="' . esc_attr( $status_form_id ) . '" class="airsoft-order-status-form" method="post"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<p class="form-field form-field-wide wc-order-status"><label for="' . esc_attr( $status_form_id . '-select' ) . '">Status</label><select id="' . esc_attr( $status_form_id . '-select' ) . '" name="seller_status">';
    $current_status = $entry['seller_status'] ? $entry['seller_status'] : $order->get_status();
    $selected_status = in_array( $current_status, array( 'confirmed', 'on-delivery' ), true ) ? 'processing' : ( 'complete' === $current_status ? 'completed' : $current_status );
    foreach ( self::order_statuses() as $status => $text ) { echo '<option value="' . esc_attr( $status ) . '"' . ( $selected_status === $status ? ' selected' : '' ) . '>' . esc_html( $text ) . '</option>'; }
    echo '</select></p></form>';
   } else {
   echo '<span class="airsoft-badge ' . ( in_array( $entry['seller_status'], array( 'complete', 'completed' ), true ) ? 'airsoft-badge-good' : ( $entry['needs_shipment'] ? 'airsoft-badge-wait' : '' ) ) . '">' . esc_html( $label ) . '</span>';
   }
   if ( 'rejected' === $entry['payout_review'] ) { echo '<span class="airsoft-cell-detail airsoft-review-rejected">Payout release declined</span>'; }
   elseif ( $entry['paid'] ) { echo '<span class="airsoft-cell-detail">Bank payment recorded</span>'; }
   elseif ( $entry['wallet_credited'] ) { echo '<span class="airsoft-cell-detail">Released to wallet</span>'; }
   elseif ( $entry['return_hold'] ) { echo '<span class="airsoft-cell-detail">On hold — return review</span>'; }
   elseif ( in_array( $entry['seller_status'], array( 'complete', 'completed' ), true ) ) { echo '<span class="airsoft-cell-detail">Awaiting payout release</span>'; }
   echo '</td><td class="airsoft-order-actions" data-label="Actions">';
   if ( $can_update_status ) { echo '<button class="airsoft-button airsoft-button-primary airsoft-order-save-status" form="' . esc_attr( $status_form_id ) . '" name="airsoft_action" value="order_status">Save</button> '; }
   echo '<a class="airsoft-button airsoft-order-view" href="' . esc_url( $url ) . '">View</a></td></tr>';
  }
  if ( ! $entries ) { echo '<tr><td colspan="4">' . esc_html( $empty ) . '</td></tr>'; }
  echo '</tbody></table></div>';
 }

 private static function sales() {
  $id = absint( $_GET['order_id'] ?? 0 );
  if ( $id ) { self::shipment( $id ); return; }
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $filters = Airsoft_Product_Manager_Reseller_Orders::normalize_filters( $_GET );
  $data = Airsoft_Product_Manager_Reseller_Orders::portal_page( $page, $filters );
  $export = add_query_arg( array_merge( $filters, array( 'action' => 'airsoft_reseller_orders_csv' ) ), admin_url( 'admin-post.php' ) );
  echo '<section class="airsoft-panel"><div class="airsoft-panel-heading"><h3>Customer orders</h3><span class="airsoft-muted">Set each item’s status here. Only your items are shown.</span></div>';
  echo '<form class="airsoft-order-filters" method="get"><input type="hidden" name="section" value="sales">';
  Airsoft_Product_Manager_Resellers::field( 'Order or product', 'search', $filters['search'], 'search', 'maxlength="100"' );
  echo '<label>Status <select name="status"><option value="">All statuses</option>';
  foreach ( Airsoft_Product_Manager_Reseller_Orders::portal_status_options() as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '"' . selected( $filters['status'], $value, false ) . '>' . esc_html( $label ) . '</option>'; }
  echo '</select></label>';
  Airsoft_Product_Manager_Resellers::field( 'From', 'after', $filters['after'], 'date', '' );
  Airsoft_Product_Manager_Resellers::field( 'To', 'before', $filters['before'], 'date', '' );
  echo '<div class="airsoft-order-filter-actions"><button class="airsoft-button airsoft-button-primary">Filter orders</button><a class="airsoft-button" href="' . esc_url( wp_nonce_url( $export, 'airsoft_reseller_orders_csv' ) ) . '">Download CSV</a></div></form>';
  if ( ! empty( $data['limited'] ) ) { echo '<p class="airsoft-panel-note">Results were limited to the most recent ' . absint( Airsoft_Product_Manager_Reseller_Orders::PORTAL_SCAN_LIMIT ) . ' order items. Refine the search or date range to narrow the results.</p>'; }
  self::order_table( $data['items'], 'No customer orders yet.' );
  self::pagination( 'sales', $page, $data['more'], $filters );
  echo '</section>';
 }

 private static function shipment( $id ) {
  $entries = Airsoft_Product_Manager_Reseller_Orders::portal_order( $id );
  echo '<p><a href="' . esc_url( self::link( 'sales' ) ) . '">← Back to customer orders</a></p>';
  if ( ! $entries ) { echo '<section class="airsoft-panel"><p>This order is unavailable.</p></section>'; return; }
  $order = $entries[0]['order'];
  foreach ( $entries as $entry ) {
   $item = $entry['item'];
   $return_requests = Airsoft_Product_Manager_Reseller_Orders::return_requests_for_item( get_current_user_id(), $item->get_id() );
   $product = $item->get_product();
   $image = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
   $created = $order->get_date_created();
   echo '<section class="airsoft-panel airsoft-order-detail" id="reseller-item-' . absint( $item->get_id() ) . '"><div class="airsoft-panel-heading"><div><h3>Order #' . esc_html( $order->get_order_number() ) . '</h3>' . ( $created ? '<span class="airsoft-cell-detail">Placed ' . esc_html( wc_format_datetime( $created ) ) . '</span>' : '' ) . '</div><span class="airsoft-badge">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span></div><div class="airsoft-detail-product">';
   if ( $image ) { echo '<span class="airsoft-order-product-image">' . wp_kses_post( $image ) . '</span>'; }
   echo '<div class="airsoft-detail-product-copy"><strong>' . esc_html( Airsoft_Product_Manager_Reseller_Orders::item_label( $item ) ) . '</strong><span class="airsoft-cell-detail">Quantity: ' . esc_html( $item->get_quantity() ) . '</span></div><strong class="airsoft-order-total">' . wp_kses_post( wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) ) ) . '</strong></div>';
   if ( $entry['tracking'] ) {
    $carrier = $item->get_meta( '_airsoft_tracking_carrier' );
    $tracking_url = esc_url( $item->get_meta( '_airsoft_tracking_url' ) );
    echo '<p class="airsoft-panel-note airsoft-tracking-note"><span class="airsoft-muted">Tracking</span><strong>' . ( $carrier ? esc_html( $carrier ) . ': ' : '' );
    echo $tracking_url ? '<a href="' . $tracking_url . '" target="_blank" rel="noopener noreferrer">' . esc_html( $entry['tracking'] ) . '</a>' : esc_html( $entry['tracking'] );
    echo '</strong></p>';
   }
   if ( 'rejected' === $entry['payout_review'] ) { echo '<p class="airsoft-panel-note">Payout release was rejected. Update the status to submit it again.</p>'; }
   foreach ( $return_requests as $request ) {
    if ( (int) $request->item_id === (int) $item->get_id() ) {
     echo '<p class="airsoft-panel-note">Return request: <strong>' . esc_html( ucfirst( $request->state ) ) . '</strong> · ' . esc_html( $request->created_at ) . ' UTC';
     if ( $request->decision_note ) { echo '<br>' . esc_html( $request->decision_note ); }
     echo '</p>';
    }
   }
   if ( ! $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) && ! $entry['paid'] && ! $entry['wallet_credited'] ) {
    echo '<form class="airsoft-detail-status" method="post">'; wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><label><span>Status</span><select name="seller_status">';
    $current_status = $entry['seller_status'] ? $entry['seller_status'] : $order->get_status();
    $selected_status = in_array( $current_status, array( 'confirmed', 'on-delivery' ), true ) ? 'processing' : ( 'complete' === $current_status ? 'completed' : $current_status );
    foreach ( self::order_statuses() as $status => $text ) { echo '<option value="' . esc_attr( $status ) . '"' . ( $selected_status === $status ? ' selected' : '' ) . '>' . esc_html( $text ) . '</option>'; }
    echo '</select></label> <button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="order_status">Save status</button></form>';
   }
   if ( $entry['can_ship'] ) {
    echo '<div class="airsoft-address"><h4>Deliver to</h4>' . wp_kses_post( $order->get_formatted_shipping_address() ) . '</div><form class="airsoft-shipment-form" method="post">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '">';
    Airsoft_Product_Manager_Resellers::field( 'Carrier', 'tracking_carrier', $item->get_meta( '_airsoft_tracking_carrier' ), 'text', 'maxlength="100"' );
    Airsoft_Product_Manager_Resellers::field( 'Tracking number', 'tracking_number', $entry['tracking'], 'text', 'required maxlength="200"' );
    Airsoft_Product_Manager_Resellers::field( 'Tracking link (optional)', 'tracking_url', $item->get_meta( '_airsoft_tracking_url' ), 'url', 'placeholder="https://"' );
    echo '<button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="ship">Save shipment</button></form>';
   } else { echo '<p class="airsoft-muted airsoft-panel-note">Shipment updates are available for paid, shippable items in processing or completed orders.</p>'; }
   $has_pending_return = false;
   foreach ( $return_requests as $request ) { if ( (int) $request->item_id === (int) $item->get_id() && in_array( $request->state, array( 'requested', 'approved' ), true ) ) { $has_pending_return = true; break; } }
   if ( $order->is_paid() && ! $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) && ! $has_pending_return ) {
    echo '<form class="airsoft-return-request" method="post"><h4>Request return review</h4><p class="airsoft-muted">The site administrator reviews this request. This form does not issue a refund.</p>';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><label>Reason<br><textarea name="return_reason" required maxlength="2000"></textarea></label><button class="airsoft-button" name="airsoft_action" value="return_request">Send request</button></form>';
   }
   echo '</section>';
  }
 }

 private static function earnings( $summary ) {
  if ( Airsoft_Product_Manager_Reseller_Orders::wallet_enabled() ) {
   $balances = Airsoft_Product_Manager_Reseller_Orders::wallet_balances( get_current_user_id() );
   echo '<section class="airsoft-panel"><h3>Wallet</h3><p>Available: ' . self::money( $balances ) . '</p><p class="airsoft-muted">Admin released completed sales are credited here. Withdrawal requests are paid outside the site after admin review.</p><form method="post">';
   wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
   echo '<label>Currency <select name="currency">'; foreach ( $balances as $currency => $amount ) { if ( $amount > 0 ) { echo '<option value="' . esc_attr( $currency ) . '">' . esc_html( $currency ) . '</option>'; } } echo '</select></label> ';
   Airsoft_Product_Manager_Resellers::field( 'Withdrawal amount', 'amount', '', 'number', 'required min="0.01" step="0.01"' );
   echo '<button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="withdraw">Request withdrawal</button></form>';
   echo '</section>';
   $requests = Airsoft_Product_Manager_Reseller_Orders::wallet_withdrawals( get_current_user_id() );
   if ( $requests ) { echo '<section class="airsoft-panel"><h3>Withdrawal history</h3>'; foreach ( $requests as $request ) { echo '<p>' . wp_kses_post( wc_price( $request->amount, array( 'currency' => $request->currency ) ) ) . ' · ' . esc_html( $request->state ) . ' · ' . esc_html( $request->created_at ) . ' UTC</p>'; } echo '</section>'; }
  }
  echo '<div class="airsoft-stats">';
  self::stat( 'Awaiting payout release', self::money( $summary['eligible'] ), 'Paid, completed reseller items with no refund hold' );
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
   echo '<tr><td>#' . esc_html( $order->get_order_number() ) . '<span class="airsoft-cell-detail">' . esc_html( Airsoft_Product_Manager_Reseller_Orders::item_label( $item ) ) . '</span></td><td>' . esc_html( $item->get_meta( '_airsoft_commission_rate' ) ) . '%</td><td>' . wp_kses_post( wc_price( $entry['earned'], array( 'currency' => $order->get_currency() ) ) ) . '</td><td>';
   if ( $paid ) {
    echo '<span class="airsoft-badge airsoft-badge-good">' . ( 'wallet' === ( $paid->destination ?? 'bank' ) ? 'Wallet credited' : 'Bank payment recorded' ) . '</span><span class="airsoft-cell-detail">' . wp_kses_post( wc_price( $paid->amount, array( 'currency' => $paid->currency ) ) ) . ' · ' . esc_html( $paid->reference ) . '</span><span class="airsoft-cell-detail">' . esc_html( $paid->paid_at ) . ' UTC</span>';
    if ( $paid->currency !== $order->get_currency() ) { echo '<span class="airsoft-cell-detail">Order currency changed. Admin reconciliation required.</span>'; }
    else {
     $delta = $entry['earned'] - (float) $paid->amount;
     if ( abs( $delta ) > 0.00001 ) { echo '<span class="airsoft-cell-detail">Adjustment: ' . wp_kses_post( wc_price( $delta, array( 'currency' => $order->get_currency() ) ) ) . '</span>'; }
    }
   } elseif ( $entry['wallet_credited'] ) {
    echo '<span class="airsoft-badge airsoft-badge-good">Credited to wallet</span>';
   } else {
    $label = $entry['earned'] <= 0 ? 'No earnings' : ( $entry['return_hold'] ? 'Return review' : ( $entry['hold'] ? 'Refund review' : ( $entry['eligible'] ? 'Eligible' : 'Pending completion' ) ) );
    echo '<span class="airsoft-badge">' . esc_html( $label ) . '</span>';
   }
   echo '</td></tr>';
  }
  if ( ! $data['items'] ) { echo '<tr><td colspan="4">No earnings yet.</td></tr>'; }
  echo '</tbody></table></div>';
  self::pagination( 'earnings', $page, $data['more'] );
  echo '</section>';
 }

 private static function bank_details() {
  $details = Airsoft_Product_Manager_Reseller_Orders::bank_details( get_current_user_id() );
  echo '<section class="airsoft-panel"><h3>Bank payout details</h3><p class="airsoft-muted">These details are visible to authorized administrators for manual bank payouts. Check them carefully before saving.</p><form method="post">';
  wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
  Airsoft_Product_Manager_Resellers::field( 'Account holder name', 'bank_account_holder', $details['account_holder'], 'text', 'required maxlength="150" autocomplete="name"' );
  Airsoft_Product_Manager_Resellers::field( 'Bank name', 'bank_name', $details['bank_name'], 'text', 'required maxlength="150" autocomplete="organization"' );
  Airsoft_Product_Manager_Resellers::field( 'Account number', 'bank_account_number', $details['account_number'], 'text', 'required maxlength="100" autocomplete="off"' );
  echo '<p><label>Bank or branch address<br><textarea name="bank_address" required maxlength="500" rows="4">' . esc_textarea( $details['bank_address'] ) . '</textarea></label></p><button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="save_bank_details">Save bank details</button></form></section>';
 }

 private static function pagination( $section, $page, $more, $args = array() ) {
  echo '<nav class="airsoft-pagination" aria-label="Order item pages">';
  if ( $page > 1 ) { echo '<a class="airsoft-button" href="' . esc_url( self::link( $section, array_merge( $args, array( 'orders_page' => $page - 1 ) ) ) ) . '">Previous</a>'; }
  if ( $more ) { echo '<a class="airsoft-button" href="' . esc_url( self::link( $section, array_merge( $args, array( 'orders_page' => $page + 1 ) ) ) ) . '">Next</a>'; }
  echo '</nav>';
 }
}
