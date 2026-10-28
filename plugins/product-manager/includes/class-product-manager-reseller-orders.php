<?php
/** Order ownership snapshots, seller fulfilment and manual payout records. */
defined( 'ABSPATH' ) || exit;
class Airsoft_Product_Manager_Reseller_Orders {
 public static function init() {
  add_action( 'init', array( __CLASS__, 'install' ) );
  add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'snapshot' ), 10, 4 );
  add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'snapshot_block_order' ) );
  add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'notify' ) );
  add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'notify' ) );
  add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'customer_tracking' ) );
  add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
  add_action( 'admin_post_airsoft_reseller_payout', array( __CLASS__, 'payout' ) );
  add_action( 'admin_post_airsoft_reseller_commission', array( __CLASS__, 'commission' ) );
 }
 private static function table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_payouts'; }
 public static function install() {
  if ( '1' === get_option( 'airsoft_reseller_ledger_version' ) ) { return; }
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $table = self::table();
  $charset = $wpdb->get_charset_collate();
  dbDelta( "CREATE TABLE $table (
   item_id bigint(20) unsigned NOT NULL,
   reseller_id bigint(20) unsigned NOT NULL,
   amount decimal(26,8) NOT NULL,
   currency varchar(10) NOT NULL,
   reference varchar(200) NOT NULL,
   paid_at datetime NOT NULL,
   admin_id bigint(20) unsigned NOT NULL,
   PRIMARY KEY  (item_id)
  ) $charset;" );
  if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table ) { update_option( 'airsoft_reseller_ledger_version', '1', false ); }
 }
 public static function snapshot( $item, $key = '', $values = array(), $order = null ) {
  if ( $item->get_meta( '_airsoft_reseller_owner' ) ) { return; }
  $product = $item->get_product();
  if ( ! $product ) { return; }
  $owner = absint( $product->get_meta( '_airsoft_reseller_owner' ) );
  if ( ! $owner && $product->get_parent_id() ) { $owner = absint( get_post_meta( $product->get_parent_id(), '_airsoft_reseller_owner', true ) ); }
  if ( $owner ) {
   $item->add_meta_data( '_airsoft_reseller_owner', $owner, true );
   $item->add_meta_data( '_airsoft_commission_rate', self::rate(), true );
  }
 }
 public static function snapshot_block_order( $order ) {
  foreach ( $order->get_items() as $item ) { self::snapshot( $item ); $item->save(); }
 }
 private static function rate() { return min( 100, max( 0, (float) get_option( 'airsoft_reseller_commission', 0 ) ) ); }
 private static function earning( $order, $item ) {
  $net = max( 0, (float) $item->get_total() - abs( (float) $order->get_total_refunded_for_item( $item->get_id() ) ) );
  if ( $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) ) { $net = 0; }
  return round( $net * ( 1 - (float) $item->get_meta( '_airsoft_commission_rate' ) / 100 ), wc_get_price_decimals() );
 }
 private static function paid( $id ) {
  global $wpdb;
  return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE item_id = %d', $id ) );
 }
 private static function needs_reconciliation( $order ) {
  foreach ( $order->get_refunds() as $refund ) {
   $allocated = 0;
   foreach ( $refund->get_items( array( 'line_item', 'shipping', 'fee' ) ) as $line ) {
    $allocated += abs( (float) $line->get_total() ) + abs( (float) $line->get_total_tax() );
   }
   if ( (float) $refund->get_amount() - $allocated > 0.01 ) { return true; }
  }
  return false;
 }
 private static function rows( $owner = 0 ) {
  global $wpdb;
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $where = "m.meta_key = '_airsoft_reseller_owner'";
  if ( $owner ) { $where .= $wpdb->prepare( ' AND m.meta_value = %s', (string) $owner ); }
  // Order item tables are shared by legacy storage and HPOS. Order data uses CRUD.
  return $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT i.order_id, i.order_item_id FROM {$wpdb->prefix}woocommerce_order_items i INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m ON i.order_item_id = m.order_item_id WHERE $where ORDER BY i.order_item_id DESC LIMIT %d OFFSET %d", 21, ( $page - 1 ) * 20 ) );
 }
 public static function notify( $order_id ) {
  $order = wc_get_order( $order_id );
  if ( ! $order || ! $order->is_paid() ) { return; }
  $owners = array();
  foreach ( $order->get_items() as $item ) { $owner = absint( $item->get_meta( '_airsoft_reseller_owner' ) ); if ( $owner ) { $owners[ $owner ] = true; } }
  foreach ( array_keys( $owners ) as $owner ) {
   if ( $order->get_meta( '_airsoft_notified_' . $owner ) ) { continue; }
   $user = get_userdata( $owner );
   if ( $user && Airsoft_Product_Manager_Reseller_Emails::send( $user->user_email, 'New reseller order #' . $order->get_order_number(), 'A paid order is ready. View your items and delivery details in the Reseller Portal: ' . add_query_arg( 'section', 'sales', Airsoft_Product_Manager_Resellers::url() ) ) ) { $order->update_meta_data( '_airsoft_notified_' . $owner, 'yes' ); }
  }
  $order->save();
 }
 public static function render( $admin = false ) {
  $owner = $admin ? 0 : get_current_user_id();
  $rows = self::rows( $owner );
  $more = count( $rows ) > 20;
  echo '<h3>Orders and earnings</h3><p>Earnings exclude tax and shipping and include coupon discounts and item refunds. Payouts require a paid, completed order. Contact the site administrator about refund adjustments after payout.</p>';
  if ( ! $rows ) { echo '<p>No reseller orders yet.</p>'; }
  foreach ( array_slice( $rows, 0, 20 ) as $row ) {
   $order = wc_get_order( $row->order_id );
   if ( ! $order || 'shop_order' !== $order->get_type() ) { continue; }
   $item = $order->get_item( $row->order_item_id );
   if ( ! $item || ( $owner && (int) $item->get_meta( '_airsoft_reseller_owner' ) !== $owner ) ) { continue; }
   $earned = self::earning( $order, $item );
   $hold = self::needs_reconciliation( $order );
   $paid = self::paid( $item->get_id() );
   echo '<hr><h4>Order #' . esc_html( $order->get_order_number() ) . ' — ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</h4><p>' . esc_html( $item->get_name() ) . ' × ' . esc_html( $item->get_quantity() ) . '</p>';
   if ( $admin ) { echo '<p>Reseller account ID: ' . absint( $item->get_meta( '_airsoft_reseller_owner' ) ) . '</p>'; }
   echo '<p>Earnings: ' . wp_kses_post( wc_price( $earned, array( 'currency' => $order->get_currency() ) ) ) . ' · Commission: ' . esc_html( $item->get_meta( '_airsoft_commission_rate' ) ) . '%</p>';
   if ( $paid ) {
    echo '<p>Payout recorded: ' . wp_kses_post( wc_price( $paid->amount, array( 'currency' => $paid->currency ) ) ) . ' · ' . esc_html( $paid->reference ) . ' · ' . esc_html( $paid->paid_at ) . ' UTC</p>';
    if ( abs( $earned - (float) $paid->amount ) > 0.00001 ) { echo '<p>Adjustment requiring admin reconciliation: ' . wp_kses_post( wc_price( $earned - (float) $paid->amount, array( 'currency' => $order->get_currency() ) ) ) . '</p>'; }
   } else { echo '<p>Payout: ' . ( ! $hold && $order->has_status( 'completed' ) && $order->is_paid() ? 'Eligible' : 'Pending completion or refund review' ) . '</p>'; }
   if ( $hold ) { echo '<p>This order has a refund without full item allocation. Admin reconciliation is required before recording a payout.</p>'; }
   $tracking = $item->get_meta( '_airsoft_tracking' );
   if ( $tracking ) { echo '<p>Tracking: ' . esc_html( $tracking ) . '</p>'; }
   if ( ! $admin && $order->is_paid() && $order->has_status( array( 'processing', 'completed' ) ) ) {
    echo '<p>Deliver to:<br>' . wp_kses_post( $order->get_formatted_shipping_address() ) . '</p><form method="post">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '">';
    Airsoft_Product_Manager_Resellers::field( 'Carrier and tracking number', 'tracking', $tracking, 'text', 'required maxlength="200"' );
    echo '<button name="airsoft_action" value="ship">Save shipment</button></form>';
   }
   if ( $admin && ! $hold && ! $paid && $earned > 0 && $order->is_paid() && $order->has_status( 'completed' ) ) {
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_payout"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="amount" value="' . esc_attr( $earned ) . '">';
    wp_nonce_field( 'airsoft_payout_' . $item->get_id() );
    Airsoft_Product_Manager_Resellers::field( 'Payment reference (after paying externally)', 'reference', '', 'text', 'required maxlength="200"' );
    echo '<button>Record payment made</button></form>';
   }
  }
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $url = $admin ? admin_url( 'admin.php?page=airsoft-reseller-payouts' ) : add_query_arg( 'section', 'sales', Airsoft_Product_Manager_Resellers::url() );
  if ( $page > 1 ) { echo '<a href="' . esc_url( add_query_arg( 'orders_page', $page - 1, $url ) ) . '">Previous</a> '; }
  if ( $more ) { echo '<a href="' . esc_url( add_query_arg( 'orders_page', $page + 1, $url ) ) . '">Next</a>'; }
 }
 public static function ship() {
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( absint( $_POST['item_id'] ?? 0 ) ) : false;
  if ( ! $order || ! $item || ! $order->is_paid() || ! $order->has_status( array( 'processing', 'completed' ) ) || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { throw new Exception( 'You cannot update this order.' ); }
  $tracking = sanitize_text_field( wp_unslash( $_POST['tracking'] ?? '' ) );
  if ( ! $tracking || strlen( $tracking ) > 200 ) { throw new Exception( 'Enter a carrier and tracking number (maximum 200 characters).' ); }
  $item->update_meta_data( '_airsoft_tracking', $tracking );
  $item->save();
  $order->add_order_note( 'Shipment for ' . $item->get_name() . ': ' . $tracking, true );
  wc_add_notice( 'Shipment saved. Tracking is available in the customer’s order.', 'success' );
 }
 public static function customer_tracking( $order ) {
  if ( ! $order || ( ! current_user_can( 'manage_woocommerce' ) && ( ! is_user_logged_in() || (int) $order->get_customer_id() !== get_current_user_id() ) ) ) { return; }
  foreach ( $order->get_items() as $item ) {
   if ( $item->get_meta( '_airsoft_tracking' ) ) { echo '<p>' . esc_html( $item->get_name() ) . ' — Tracking: ' . esc_html( $item->get_meta( '_airsoft_tracking' ) ) . '</p>'; }
  }
 }
 public static function menu() {
  add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'Reseller payouts', 'Reseller payouts', 'manage_woocommerce', 'airsoft-reseller-payouts', array( __CLASS__, 'admin' ) );
 }
 public static function admin() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
  echo '<div class="wrap"><h1>Reseller payouts</h1><p>Record payments after sending money externally. Each order item can be paid once. Refunds after payment show an adjustment for manual reconciliation. The site retains checkout tax and shipping amounts.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_commission">';
  wp_nonce_field( 'airsoft_commission' );
  Airsoft_Product_Manager_Resellers::field( 'Commission % for new orders', 'commission', self::rate(), 'number', 'required min="0" max="100" step="0.01"' );
  echo '<button>Save commission</button></form>';
  self::render( true ); echo '</div>';
 }
 public static function commission() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_commission' );
  $rate = wc_format_decimal( wp_unslash( $_POST['commission'] ?? '' ) );
  if ( ! is_numeric( $rate ) || $rate < 0 || $rate > 100 ) { wp_die( 'Commission must be between 0 and 100.' ); }
  update_option( 'airsoft_reseller_commission', $rate );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
 public static function payout() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['item_id'] ?? 0 );
  check_admin_referer( 'airsoft_payout_' . $id );
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( $id ) : false;
  $reference = sanitize_text_field( wp_unslash( $_POST['reference'] ?? '' ) );
  if ( ! $order || ! $item || ! $item->get_meta( '_airsoft_reseller_owner' ) || ! $order->is_paid() || ! $order->has_status( 'completed' ) || self::needs_reconciliation( $order ) || ! $reference || strlen( $reference ) > 200 ) { wp_die( 'Invalid payout or refund reconciliation required.' ); }
  $amount = self::earning( $order, $item );
  if ( $amount <= 0 || abs( $amount - (float) ( $_POST['amount'] ?? -1 ) ) > 0.00001 ) { wp_die( 'Earnings changed. Refresh and review this order before recording payment.' ); }
  global $wpdb;
  $saved = $wpdb->insert( self::table(), array( 'item_id' => $id, 'reseller_id' => absint( $item->get_meta( '_airsoft_reseller_owner' ) ), 'amount' => $amount, 'currency' => $order->get_currency(), 'reference' => $reference, 'paid_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%d' ) );
  if ( ! $saved ) { wp_die( 'Payment was already recorded, or the payout record could not be saved. Check the payout list.' ); }
  $order->add_order_note( 'Reseller payout recorded for item #' . $id . ': ' . $amount . ' ' . $order->get_currency() . '. Reference: ' . $reference );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
}
