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
  add_action( 'admin_post_airsoft_reseller_release', array( __CLASS__, 'release_payout' ) );
  add_action( 'admin_post_airsoft_reseller_withdrawal_review', array( __CLASS__, 'review_withdrawal' ) );
 }
 private static function table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_payouts'; }
 private static function wallet_table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_wallet'; }
 public static function install() {
  if ( '2' === get_option( 'airsoft_reseller_ledger_version' ) ) { return; }
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
  $wallet = self::wallet_table();
  dbDelta( "CREATE TABLE $wallet (
   id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   reseller_id bigint(20) unsigned NOT NULL,
   item_id bigint(20) unsigned DEFAULT NULL,
   amount decimal(26,8) NOT NULL,
   currency varchar(10) NOT NULL,
   state varchar(20) NOT NULL,
   created_at datetime NOT NULL,
   admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
   PRIMARY KEY  (id),
   UNIQUE KEY item_id (item_id),
   KEY reseller_currency (reseller_id,currency)
  ) $charset;" );
  if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wallet ) ) ) === $wallet ) { update_option( 'airsoft_reseller_ledger_version', '2', false ); }
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
 private static function credited( $id ) {
  global $wpdb;
  return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::wallet_table() . ' WHERE item_id = %d', $id ) );
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
 private static function rows( $owner = 0, $page = null ) {
  global $wpdb;
  $page = max( 1, absint( null === $page ? ( $_GET['orders_page'] ?? 1 ) : $page ) );
  $where = "m.meta_key = '_airsoft_reseller_owner'";
  if ( $owner ) { $where .= $wpdb->prepare( ' AND m.meta_value = %s', (string) $owner ); }
  // Order item tables are shared by legacy storage and HPOS. Order data uses CRUD.
  return $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT i.order_id, i.order_item_id FROM {$wpdb->prefix}woocommerce_order_items i INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m ON i.order_item_id = m.order_item_id WHERE $where ORDER BY i.order_item_id DESC LIMIT %d OFFSET %d", 21, ( $page - 1 ) * 20 ) );
 }

 /** Data for the signed-in seller only; purchase orders are handled separately. */
 private static function portal_entry( $order, $item, $paid = null ) {
  if ( ! $order || 'shop_order' !== $order->get_type() || ! $item || (int) $item->get_order_id() !== $order->get_id() || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { return false; }
  if ( null === $paid ) { $paid = self::paid( $item->get_id() ); }
  $earned = self::earning( $order, $item );
  $tracking = $item->get_meta( '_airsoft_tracking' );
  $hold = self::needs_reconciliation( $order );
  $quantity = (float) $item->get_quantity() - abs( (float) $order->get_qty_refunded_for_item( $item->get_id() ) );
  $refunded = (float) $item->get_total() > 0 && abs( (float) $order->get_total_refunded_for_item( $item->get_id() ) ) >= (float) $item->get_total();
  $product = $item->get_product();
  $physical = ! $product || $product->needs_shipping();
  return array( 'order' => $order, 'item' => $item, 'earned' => $earned, 'paid' => $paid, 'hold' => $hold, 'tracking' => $tracking, 'seller_status' => $item->get_meta( '_airsoft_seller_status' ), 'payout_review' => $item->get_meta( '_airsoft_payout_review' ), 'wallet_credited' => self::credited( $item->get_id() ), 'eligible' => ! $hold && $order->is_paid() && $order->has_status( 'completed' ) && in_array( $item->get_meta( '_airsoft_seller_status' ), array( 'complete', 'completed' ), true ) && 'rejected' !== $item->get_meta( '_airsoft_payout_review' ), 'can_ship' => $physical && $quantity > 0 && ! $refunded && $order->is_paid() && $order->has_status( array( 'processing', 'completed' ) ), 'needs_shipment' => $physical && $quantity > 0 && ! $refunded && ! $tracking && $order->is_paid() && $order->has_status( 'processing' ) );
 }

 public static function portal_page( $page = 1 ) {
  $result = array( 'items' => array(), 'more' => false );
  if ( ! Airsoft_Product_Manager_Resellers::approved() || ! get_current_user_id() ) { return $result; }
  $rows = (array) self::rows( get_current_user_id(), $page );
  $result['more'] = count( $rows ) > 20;
  foreach ( array_slice( $rows, 0, 20 ) as $row ) {
   $order = wc_get_order( $row->order_id );
   $entry = self::portal_entry( $order, $order ? $order->get_item( $row->order_item_id ) : false );
   if ( $entry ) { $result['items'][] = $entry; }
  }
  return $result;
 }

 public static function portal_order( $id ) {
  $entries = array();
  if ( ! Airsoft_Product_Manager_Resellers::approved() || ! get_current_user_id() ) { return $entries; }
  $order = wc_get_order( absint( $id ) );
  if ( ! $order || 'shop_order' !== $order->get_type() ) { return $entries; }
  foreach ( $order->get_items() as $item ) {
   $entry = self::portal_entry( $order, $item );
   if ( $entry ) { $entries[] = $entry; }
  }
  return $entries;
 }

 public static function portal_summary() {
  $summary = array( 'to_ship' => 0, 'eligible' => array(), 'pending' => array(), 'paid' => array(), 'adjustments' => array(), 'currency_review' => 0, 'attention' => array() );
  $owner = get_current_user_id();
  if ( ! $owner || ! Airsoft_Product_Manager_Resellers::approved() ) { return $summary; }
  global $wpdb;
  $cursor = PHP_INT_MAX;
  $pending_orders = array();
  $last_order = null;
  // Keyset batches keep memory bounded. Use shared item tables and order CRUD
  // for HPOS compatibility; load payout records in the same batch query.
  do {
   $table = self::table();
   $rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT i.order_id, i.order_item_id, p.item_id AS payout_item_id, p.amount AS payout_amount, p.currency AS payout_currency, p.reference AS payout_reference, p.paid_at AS payout_date FROM {$wpdb->prefix}woocommerce_order_items i INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m ON i.order_item_id = m.order_item_id LEFT JOIN $table p ON p.item_id = i.order_item_id WHERE m.meta_key = '_airsoft_reseller_owner' AND m.meta_value = %s AND i.order_item_id < %d ORDER BY i.order_item_id DESC LIMIT 200", (string) $owner, $cursor ) );
   foreach ( $rows as $row ) {
    $cursor = min( $cursor, (int) $row->order_item_id );
    if ( ! $last_order || $last_order->get_id() !== (int) $row->order_id ) { $last_order = wc_get_order( $row->order_id ); }
    $paid = ! empty( $row->payout_item_id ) ? (object) array( 'amount' => $row->payout_amount, 'currency' => $row->payout_currency, 'reference' => $row->payout_reference, 'paid_at' => $row->payout_date ) : false;
    $entry = self::portal_entry( $last_order, $last_order ? $last_order->get_item( $row->order_item_id ) : false, $paid );
    if ( ! $entry ) { continue; }
    $currency = $last_order->get_currency();
    if ( $entry['needs_shipment'] ) {
     $pending_orders[$row->order_id] = true;
     if ( count( $summary['attention'] ) < 5 ) { $summary['attention'][] = $entry; }
    }
    if ( $paid ) {
     $summary['paid'][$paid->currency] = ( $summary['paid'][$paid->currency] ?? 0 ) + (float) $paid->amount;
     if ( $paid->currency !== $currency ) { $summary['currency_review']++; }
     else {
      $delta = $entry['earned'] - (float) $paid->amount;
      if ( abs( $delta ) > 0.00001 ) { $summary['adjustments'][$currency] = ( $summary['adjustments'][$currency] ?? 0 ) + $delta; }
     }
    } elseif ( ! $entry['wallet_credited'] && $entry['earned'] > 0 && $last_order->is_paid() ) {
     $bucket = $entry['eligible'] ? 'eligible' : 'pending';
     $summary[$bucket][$currency] = ( $summary[$bucket][$currency] ?? 0 ) + $entry['earned'];
    }
   }
  } while ( count( $rows ) === 200 );
  $summary['to_ship'] = count( $pending_orders );
  return $summary;
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
   echo '<p>Reseller status: ' . esc_html( $item->get_meta( '_airsoft_seller_status' ) ? $item->get_meta( '_airsoft_seller_status' ) : 'Not set' ) . '</p>';
   if ( $admin && in_array( $item->get_meta( '_airsoft_seller_status' ), array( 'complete', 'completed' ), true ) && ! self::credited( $item->get_id() ) && ! $paid && 'rejected' !== $item->get_meta( '_airsoft_payout_review' ) ) {
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_release"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '">';
    wp_nonce_field( 'airsoft_release_' . $item->get_id() );
    echo '<button name="decision" value="release">Release payout to wallet</button> <button name="decision" value="reject">Reject payout</button></form>';
   }
   if ( $admin && 'rejected' === $item->get_meta( '_airsoft_payout_review' ) ) { echo '<p>Payout rejected. The reseller can update the status and submit again.</p>'; }
   if ( ! $admin && $order->is_paid() && $order->has_status( array( 'processing', 'completed' ) ) ) {
    echo '<p>Deliver to:<br>' . wp_kses_post( $order->get_formatted_shipping_address() ) . '</p><form method="post">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '">';
    Airsoft_Product_Manager_Resellers::field( 'Carrier and tracking number', 'tracking', $tracking, 'text', 'required maxlength="200"' );
    echo '<button name="airsoft_action" value="ship">Save shipment</button></form>';
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
  if ( ! $order || ! $item || (int) $item->get_order_id() !== $order->get_id() || ! $order->is_paid() || ! $order->has_status( array( 'processing', 'completed' ) ) || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { throw new Exception( 'You cannot update this order.' ); }
  $tracking = sanitize_text_field( wp_unslash( $_POST['tracking'] ?? '' ) );
  if ( ! $tracking || strlen( $tracking ) > 200 ) { throw new Exception( 'Enter a carrier and tracking number (maximum 200 characters).' ); }
  $item->update_meta_data( '_airsoft_tracking', $tracking );
  $item->save();
  $order->add_order_note( 'Shipment for ' . $item->get_name() . ': ' . $tracking, true );
  wc_add_notice( 'Shipment saved. Tracking is available in the customer’s order.', 'success' );
 }
 public static function request_status() {
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( absint( $_POST['item_id'] ?? 0 ) ) : false;
  $status = sanitize_key( wp_unslash( $_POST['seller_status'] ?? '' ) );
  $valid_statuses = array_map( static function( $registered_status ) { return preg_replace( '/^wc-/', '', $registered_status ); }, array_keys( wc_get_order_statuses() ) );
  foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'names' ) as $registered_status ) {
   if ( 0 === strpos( $registered_status, 'wc-' ) ) { $valid_statuses[] = substr( $registered_status, 3 ); }
  }
  if ( ! $order || 'shop_order' !== $order->get_type() || ! $item || (int) $item->get_order_id() !== $order->get_id() || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) || ! in_array( $status, $valid_statuses, true ) || self::paid( $item->get_id() ) || self::credited( $item->get_id() ) ) { throw new Exception( 'This order status cannot be updated.' ); }
  $item->update_meta_data( '_airsoft_seller_status', $status );
  $item->update_meta_data( '_airsoft_payout_review', '' );
  $item->save();
  $order->add_order_note( 'Reseller set item #' . $item->get_id() . ' status: ' . $status . '.' );
  wc_add_notice( 'Order status updated.', 'success' );
 }
 public static function release_payout() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['item_id'] ?? 0 ); check_admin_referer( 'airsoft_release_' . $id );
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( $id ) : false;
  $decision = sanitize_key( wp_unslash( $_POST['decision'] ?? '' ) );
  if ( ! $order || 'shop_order' !== $order->get_type() || ! $item || (int) $item->get_order_id() !== $order->get_id() || ! $item->get_meta( '_airsoft_reseller_owner' ) || ! in_array( $item->get_meta( '_airsoft_seller_status' ), array( 'complete', 'completed' ), true ) || self::credited( $id ) || self::paid( $id ) || 'rejected' === $item->get_meta( '_airsoft_payout_review' ) || ! in_array( $decision, array( 'release', 'reject' ), true ) ) { wp_die( 'Invalid payout review.' ); }
  if ( 'reject' === $decision ) {
   $item->update_meta_data( '_airsoft_payout_review', 'rejected' ); $item->save();
   $order->add_order_note( 'Admin rejected reseller item #' . $id . ' payout.' );
   wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
  }
  if ( ! $order->is_paid() || ! $order->has_status( 'completed' ) || self::needs_reconciliation( $order ) ) { wp_die( 'Payout cannot be released until the paid order is complete and refunds are reconciled.' ); }
  $amount = self::earning( $order, $item );
  if ( $amount <= 0 ) { wp_die( 'No earnings to release.' ); }
  global $wpdb;
  if ( ! $wpdb->insert( self::wallet_table(), array( 'reseller_id' => absint( $item->get_meta( '_airsoft_reseller_owner' ) ), 'item_id' => $id, 'amount' => $amount, 'currency' => $order->get_currency(), 'state' => 'credit', 'created_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%d' ) ) ) { wp_die( 'Payout was already released or could not be saved.' ); }
  $order->add_order_note( 'Admin released reseller item #' . $id . ' earnings to wallet: ' . $amount . ' ' . $order->get_currency() . '.' );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
 public static function wallet_balances( $reseller_id ) {
  global $wpdb;
  $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT currency, SUM(CASE WHEN state = %s THEN amount WHEN state IN (%s,%s) THEN -amount ELSE 0 END) AS balance FROM ' . self::wallet_table() . ' WHERE reseller_id = %d GROUP BY currency', 'credit', 'requested', 'paid', $reseller_id ) );
  $balances = array(); foreach ( (array) $rows as $row ) { $balances[$row->currency] = max( 0, (float) $row->balance ); } return $balances;
 }
 public static function wallet_withdrawals( $reseller_id ) {
  global $wpdb;
  return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT amount, currency, state, created_at FROM ' . self::wallet_table() . ' WHERE reseller_id = %d AND item_id IS NULL ORDER BY id DESC LIMIT 20', $reseller_id ) );
 }
 public static function withdraw() {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { throw new Exception( 'Reseller approval is required.' ); }
  $currency = strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? '' ) ) );
  $amount = wc_format_decimal( wp_unslash( $_POST['amount'] ?? '' ) );
  if ( ! preg_match( '/^[A-Z]{3}$/', $currency ) || ! is_numeric( $amount ) || (float) $amount <= 0 || (float) $amount != round( (float) $amount, wc_get_price_decimals() ) ) { throw new Exception( 'Enter a valid withdrawal amount and currency.' ); }
  global $wpdb; $owner = get_current_user_id();
  $lock = 'airsoft_wallet_' . $owner;
  if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock ) ) ) { throw new Exception( 'Wallet is busy. Please retry.' ); }
  try {
   $balances = self::wallet_balances( $owner );
   if ( (float) $amount > ( $balances[$currency] ?? 0 ) + 0.000001 ) { throw new Exception( 'Insufficient wallet balance.' ); }
   if ( ! $wpdb->insert( self::wallet_table(), array( 'reseller_id' => $owner, 'amount' => $amount, 'currency' => $currency, 'state' => 'requested', 'created_at' => current_time( 'mysql', true ), 'admin_id' => 0 ), array( '%d', '%f', '%s', '%s', '%s', '%d' ) ) ) { throw new Exception( 'Withdrawal request could not be saved.' ); }
  } finally { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); }
  wc_add_notice( 'Withdrawal requested. The administrator will arrange payment.', 'success' );
 }
 public static function review_withdrawal() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['wallet_id'] ?? 0 ); check_admin_referer( 'airsoft_withdrawal_' . $id );
  $decision = sanitize_key( wp_unslash( $_POST['decision'] ?? '' ) );
  if ( ! in_array( $decision, array( 'paid', 'rejected' ), true ) ) { wp_die( 'Invalid withdrawal decision.' ); }
  global $wpdb;
  $updated = $wpdb->update( self::wallet_table(), array( 'state' => $decision, 'admin_id' => get_current_user_id() ), array( 'id' => $id, 'state' => 'requested', 'item_id' => null ), array( '%s', '%d' ), array( '%d', '%s', '%s' ) );
  if ( 1 !== $updated ) { wp_die( 'Withdrawal was already reviewed or could not be saved.' ); }
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
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
  self::render( true );
  global $wpdb;
  $requests = $wpdb->get_results( "SELECT * FROM " . self::wallet_table() . " WHERE state = 'requested' AND item_id IS NULL ORDER BY id DESC LIMIT 100" );
  echo '<h2>Withdrawal requests</h2>';
  if ( ! $requests ) { echo '<p>No pending withdrawals.</p>'; }
  foreach ( (array) $requests as $request ) {
   echo '<p>Reseller #' . absint( $request->reseller_id ) . ' · ' . wp_kses_post( wc_price( $request->amount, array( 'currency' => $request->currency ) ) ) . ' · requested ' . esc_html( $request->created_at ) . ' UTC</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_withdrawal_review"><input type="hidden" name="wallet_id" value="' . absint( $request->id ) . '">';
   wp_nonce_field( 'airsoft_withdrawal_' . $request->id );
   echo '<button name="decision" value="paid">Mark externally paid</button> <button name="decision" value="rejected">Reject and return to wallet</button></form>';
  }
  echo '</div>';
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
  if ( ! $order || ! $item || (int) $item->get_order_id() !== $order->get_id() || ! $item->get_meta( '_airsoft_reseller_owner' ) || $item->get_meta( '_airsoft_seller_status' ) || self::credited( $id ) || ! $order->is_paid() || ! $order->has_status( 'completed' ) || self::needs_reconciliation( $order ) || ! $reference || strlen( $reference ) > 200 ) { wp_die( 'Invalid payout or refund reconciliation required.' ); }
  $amount = self::earning( $order, $item );
  if ( $amount <= 0 || abs( $amount - (float) ( $_POST['amount'] ?? -1 ) ) > 0.00001 ) { wp_die( 'Earnings changed. Refresh and review this order before recording payment.' ); }
  global $wpdb;
  $saved = $wpdb->insert( self::table(), array( 'item_id' => $id, 'reseller_id' => absint( $item->get_meta( '_airsoft_reseller_owner' ) ), 'amount' => $amount, 'currency' => $order->get_currency(), 'reference' => $reference, 'paid_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%d' ) );
  if ( ! $saved ) { wp_die( 'Payment was already recorded, or the payout record could not be saved. Check the payout list.' ); }
  $order->add_order_note( 'Reseller payout recorded for item #' . $id . ': ' . $amount . ' ' . $order->get_currency() . '. Reference: ' . $reference );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
}
