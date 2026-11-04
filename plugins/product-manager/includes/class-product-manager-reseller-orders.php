<?php
/** Order ownership snapshots, seller fulfilment and manual payout records. */
defined( 'ABSPATH' ) || exit;
class Airsoft_Product_Manager_Reseller_Orders {
 private static $paid_cache = array();
 private static $credited_cache = array();
 private static $return_cache = array();
 const PORTAL_SCAN_LIMIT = 1000;
 public static function init() {
  add_action( 'init', array( __CLASS__, 'install' ) );
  add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'snapshot' ), 10, 4 );
  add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'snapshot_block_order' ) );
  add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'notify' ) );
  add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'notify' ) );
  add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'customer_tracking' ) );
  add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
  add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
  add_action( 'admin_post_airsoft_reseller_payout', array( __CLASS__, 'payout' ) );
  add_action( 'admin_post_airsoft_reseller_commission', array( __CLASS__, 'commission' ) );
  add_action( 'admin_post_airsoft_reseller_payout_settings', array( __CLASS__, 'save_payout_settings' ) );
  add_action( 'admin_post_airsoft_reseller_release', array( __CLASS__, 'release_payout' ) );
  add_action( 'admin_post_airsoft_reseller_withdrawal_review', array( __CLASS__, 'review_withdrawal' ) );
  add_action( 'admin_post_airsoft_reseller_orders_csv', array( __CLASS__, 'export_orders' ) );
  add_action( 'admin_post_airsoft_reseller_return_review', array( __CLASS__, 'review_return' ) );
 }
 private static function table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_payouts'; }
 private static function wallet_table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_wallet'; }
 private static function returns_table() { global $wpdb; return $wpdb->prefix . 'airsoft_reseller_returns'; }

 public static function item_variation_label( $item ) {
  $product = $item && method_exists( $item, 'get_product' ) ? $item->get_product() : false;
  if ( ! $product || ! $product->is_type( 'variation' ) || ! function_exists( 'wc_get_formatted_variation' ) ) { return ''; }
  return trim( wp_strip_all_tags( wc_get_formatted_variation( $product, true, true, false ) ) );
 }

 public static function item_label( $item ) {
  $name = $item ? (string) $item->get_name() : '';
  $product = $item && method_exists( $item, 'get_product' ) ? $item->get_product() : false;
  if ( $product && $product->is_type( 'variation' ) && $product->get_parent_id() ) {
   $parent = wc_get_product( $product->get_parent_id() );
   if ( $parent ) { $name = $parent->get_name(); }
  }
  $variation = self::item_variation_label( $item );
  return $variation ? $name . ' — ' . $variation : $name;
 }

 public static function entry_status( $entry ) {
  $seller = sanitize_key( $entry['seller_status'] ?? '' );
  if ( $seller ) {
   if ( in_array( $seller, array( 'complete', 'completed' ), true ) ) { return 'completed'; }
   if ( in_array( $seller, array( 'confirmed', 'on-delivery' ), true ) ) { return 'processing'; }
   return $seller;
  }
  if ( ! empty( $entry['needs_shipment'] ) ) { return 'to-ship'; }
  if ( ! empty( $entry['tracking'] ) ) { return 'shipped'; }
  return sanitize_key( $entry['order']->get_status() );
 }

 public static function entry_status_label( $entry ) {
  $status = self::entry_status( $entry );
  if ( 'to-ship' === $status ) { return 'To ship'; }
  if ( 'shipped' === $status ) { return 'Shipped'; }
  if ( 'completed' === $status ) { return 'Completed'; }
  return function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $status ) : ucwords( str_replace( '-', ' ', $status ) );
 }

 public static function portal_status_options() {
  $options = array( 'to-ship' => 'To ship', 'shipped' => 'Shipped' );
  foreach ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() as $status => $label ) {
   $key = sanitize_key( preg_replace( '/^wc-/', '', $status ) );
   if ( in_array( $key, array( 'complete', 'completed' ), true ) ) { $key = 'completed'; $label = 'Completed'; }
   if ( ! isset( $options[$key] ) ) { $options[$key] = wp_strip_all_tags( $label ); }
  }
  if ( function_exists( 'get_post_stati' ) ) {
   foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status => $details ) {
    if ( 0 !== strpos( $status, 'wc-' ) ) { continue; }
    $key = sanitize_key( substr( $status, 3 ) );
    if ( in_array( $key, array( 'complete', 'completed' ), true ) ) { $key = 'completed'; }
    if ( ! isset( $options[$key] ) ) { $options[$key] = wp_strip_all_tags( $details->label ); }
   }
  }
  return $options;
 }

 public static function normalize_filters( $input ) {
  $input = is_array( $input ) ? $input : array();
  $scalar = static function( $key ) use ( $input ) { return isset( $input[$key] ) && is_scalar( $input[$key] ) ? (string) $input[$key] : ''; };
  return array(
   'search' => sanitize_text_field( wp_unslash( $scalar( 'search' ) ) ),
   'status' => sanitize_key( wp_unslash( $scalar( 'status' ) ) ),
   'after' => sanitize_text_field( wp_unslash( $scalar( 'after' ) ) ),
   'before' => sanitize_text_field( wp_unslash( $scalar( 'before' ) ) ),
  );
 }

 private static function prime_item_state( $ids ) {
  global $wpdb;
  $ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
  if ( ! $ids ) { return; }
  $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
  $sql = $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE item_id IN (' . $placeholders . ')', ...$ids );
  foreach ( (array) $wpdb->get_results( $sql ) as $row ) { self::$paid_cache[(int) $row->item_id] = $row; }
  $sql = $wpdb->prepare( 'SELECT item_id FROM ' . self::wallet_table() . ' WHERE item_id IN (' . $placeholders . ')', ...$ids );
  foreach ( (array) $wpdb->get_results( $sql ) as $row ) { self::$credited_cache[(int) $row->item_id] = true; }
  $sql = $wpdb->prepare( 'SELECT item_id FROM ' . self::returns_table() . ' WHERE item_id IN (' . $placeholders . ') AND state IN (%s,%s)', ...array_merge( $ids, array( 'requested', 'approved' ) ) );
  foreach ( (array) $wpdb->get_results( $sql ) as $row ) { self::$return_cache[(int) $row->item_id] = true; }
  foreach ( $ids as $id ) {
   if ( ! array_key_exists( $id, self::$paid_cache ) ) { self::$paid_cache[$id] = false; }
   if ( ! array_key_exists( $id, self::$credited_cache ) ) { self::$credited_cache[$id] = false; }
   if ( ! array_key_exists( $id, self::$return_cache ) ) { self::$return_cache[$id] = false; }
  }
 }
 public static function install() {
  if ( '5' === get_option( 'airsoft_reseller_ledger_version' ) ) { return; }
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
   destination varchar(20) NOT NULL DEFAULT 'bank',
   bank_details longtext NULL,
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
  $returns = self::returns_table();
  dbDelta( "CREATE TABLE $returns (
   id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   order_id bigint(20) unsigned NOT NULL,
   item_id bigint(20) unsigned NOT NULL,
   reseller_id bigint(20) unsigned NOT NULL,
   reason text NOT NULL,
   state varchar(20) NOT NULL DEFAULT 'requested',
   created_at datetime NOT NULL,
   admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
   decision_note text NOT NULL,
   resolution_note text NOT NULL,
   resolved_by bigint(20) unsigned NOT NULL DEFAULT 0,
   PRIMARY KEY  (id),
   KEY reseller_state (reseller_id,state),
   KEY item_id (item_id)
  ) $charset;" );
  if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $returns ) ) ) === $returns ) { update_option( 'airsoft_reseller_ledger_version', '5', false ); }
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
 public static function default_destination() { return 'bank' === get_option( 'airsoft_reseller_default_payout_destination', 'wallet' ) ? 'bank' : 'wallet'; }
 public static function wallet_enabled() { return 'wallet' === self::default_destination(); }
 public static function bank_details( $reseller_id ) {
  return array(
   'account_holder' => (string) get_user_meta( $reseller_id, '_airsoft_reseller_bank_account_holder', true ),
   'bank_name' => (string) get_user_meta( $reseller_id, '_airsoft_reseller_bank_name', true ),
   'account_number' => (string) get_user_meta( $reseller_id, '_airsoft_reseller_bank_account_number', true ),
   'bank_address' => (string) get_user_meta( $reseller_id, '_airsoft_reseller_bank_address', true ),
  );
 }
 public static function save_bank_details() {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { throw new Exception( 'Reseller approval is required.' ); }
  $fields = array(
   'account_holder' => sanitize_text_field( wp_unslash( $_POST['bank_account_holder'] ?? '' ) ),
   'bank_name' => sanitize_text_field( wp_unslash( $_POST['bank_name'] ?? '' ) ),
   'account_number' => sanitize_text_field( wp_unslash( $_POST['bank_account_number'] ?? '' ) ),
   'bank_address' => sanitize_textarea_field( wp_unslash( $_POST['bank_address'] ?? '' ) ),
  );
  if ( strlen( $fields['account_holder'] ) > 150 || strlen( $fields['bank_name'] ) > 150 || strlen( $fields['account_number'] ) > 100 || strlen( $fields['bank_address'] ) > 500 ) { throw new Exception( 'Please keep bank details within the field length limits.' ); }
  if ( count( array_filter( $fields, 'strlen' ) ) !== count( $fields ) ) { throw new Exception( 'Complete all bank detail fields before saving.' ); }
  update_user_meta( get_current_user_id(), '_airsoft_reseller_bank_account_holder', $fields['account_holder'] );
  update_user_meta( get_current_user_id(), '_airsoft_reseller_bank_name', $fields['bank_name'] );
  update_user_meta( get_current_user_id(), '_airsoft_reseller_bank_account_number', $fields['account_number'] );
  update_user_meta( get_current_user_id(), '_airsoft_reseller_bank_address', $fields['bank_address'] );
  wc_add_notice( 'Bank details saved.', 'success' );
 }
 private static function earning( $order, $item ) {
  $net = max( 0, (float) $item->get_total() - abs( (float) $order->get_total_refunded_for_item( $item->get_id() ) ) );
  if ( $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) ) { $net = 0; }
  return round( $net * ( 1 - (float) $item->get_meta( '_airsoft_commission_rate' ) / 100 ), wc_get_price_decimals() );
 }
 private static function paid( $id ) {
  if ( array_key_exists( (int) $id, self::$paid_cache ) ) { return self::$paid_cache[(int) $id]; }
  global $wpdb;
  return self::$paid_cache[(int) $id] = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE item_id = %d', $id ) );
 }
 private static function credited( $id ) {
  if ( array_key_exists( (int) $id, self::$credited_cache ) ) { return self::$credited_cache[(int) $id]; }
  global $wpdb;
  return self::$credited_cache[(int) $id] = (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::wallet_table() . ' WHERE item_id = %d', $id ) );
 }
 private static function has_open_return( $item_id ) {
  if ( array_key_exists( (int) $item_id, self::$return_cache ) ) { return self::$return_cache[(int) $item_id]; }
  global $wpdb;
  return self::$return_cache[(int) $item_id] = (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::returns_table() . ' WHERE item_id = %d AND state IN (%s,%s) LIMIT 1', $item_id, 'requested', 'approved' ) );
 }
 public static function payout_blocked_by_return( $item_id ) { return self::has_open_return( $item_id ); }
 private static function invalidate_item_state( $item_id ) { unset( self::$paid_cache[(int) $item_id], self::$credited_cache[(int) $item_id], self::$return_cache[(int) $item_id] ); }
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
 private static function rows( $owner = 0, $page = null, $search = '', $batch_size = 20 ) {
  global $wpdb;
  $page = max( 1, absint( null === $page ? ( $_GET['orders_page'] ?? 1 ) : $page ) );
  $batch_size = max( 1, absint( $batch_size ) );
  $where = "m.meta_key = '_airsoft_reseller_owner'";
  if ( $owner ) { $where .= $wpdb->prepare( ' AND m.meta_value = %s', (string) $owner ); }
  if ( '' !== (string) $search ) {
   $term = '%' . $wpdb->esc_like( (string) $search ) . '%';
   $where .= is_numeric( $search ) ? $wpdb->prepare( ' AND (i.order_id = %d OR i.order_item_name LIKE %s)', (int) $search, $term ) : $wpdb->prepare( ' AND i.order_item_name LIKE %s', $term );
  }
  // Order item tables are shared by legacy storage and HPOS. Order data uses CRUD.
  return $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT i.order_id, i.order_item_id FROM {$wpdb->prefix}woocommerce_order_items i INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m ON i.order_item_id = m.order_item_id WHERE $where ORDER BY i.order_item_id DESC LIMIT %d OFFSET %d", $batch_size + 1, ( $page - 1 ) * $batch_size ) );
 }

 public static function matches_filters( $entry, $filters ) {
  $order = $entry['order']; $item = $entry['item'];
  $created = $order->get_date_created();
  $date = $created ? $created->date( 'Y-m-d' ) : '';
  if ( $filters['search'] && false === stripos( $order->get_order_number() . ' ' . $item->get_name(), $filters['search'] ) ) { return false; }
  if ( $filters['after'] && ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $filters['after'] ) || $date < $filters['after'] ) ) { return false; }
  if ( $filters['before'] && ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $filters['before'] ) || $date > $filters['before'] ) ) { return false; }
  $status = self::entry_status( $entry );
  return ! $filters['status'] || $filters['status'] === $status;
 }

 public static function csv_value( $value ) {
  $value = (string) $value;
  return preg_match( '/^[=+@\-\t\r]/', $value ) ? "'" . $value : $value;
 }

 public static function export_row( $entry ) {
  $order = $entry['order']; $item = $entry['item']; $date = $order->get_date_created();
  return array( $order->get_order_number(), $date ? $date->date( 'Y-m-d H:i:s' ) : '', wc_get_order_status_name( $order->get_status() ), $item->get_name(), self::item_variation_label( $item ), $item->get_quantity(), $item->get_total(), $order->get_currency(), self::entry_status_label( $entry ), $item->get_meta( '_airsoft_tracking_carrier' ), $entry['tracking'], $item->get_meta( '_airsoft_tracking_url' ), $order->get_formatted_billing_full_name(), wp_strip_all_tags( $order->get_formatted_shipping_address() ) );
 }

 /** Data for the signed-in seller only; purchase orders are handled separately. */
 private static function portal_entry( $order, $item, $paid = null ) {
  if ( ! $order || 'shop_order' !== $order->get_type() || ! $item || (int) $item->get_order_id() !== $order->get_id() || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { return false; }
  if ( null === $paid ) { $paid = self::paid( $item->get_id() ); }
  $earned = self::earning( $order, $item );
  $tracking = $item->get_meta( '_airsoft_tracking_number' );
  if ( ! $tracking ) { $tracking = $item->get_meta( '_airsoft_tracking' ); }
  $hold = self::needs_reconciliation( $order );
  $quantity = (float) $item->get_quantity() - abs( (float) $order->get_qty_refunded_for_item( $item->get_id() ) );
  $refunded = (float) $item->get_total() > 0 && abs( (float) $order->get_total_refunded_for_item( $item->get_id() ) ) >= (float) $item->get_total();
  $product = $item->get_product();
  $physical = ! $product || $product->needs_shipping();
  $return_hold = self::has_open_return( $item->get_id() );
  return array( 'order' => $order, 'item' => $item, 'earned' => $earned, 'paid' => $paid, 'hold' => $hold, 'return_hold' => $return_hold, 'tracking' => $tracking, 'seller_status' => $item->get_meta( '_airsoft_seller_status' ), 'payout_review' => $item->get_meta( '_airsoft_payout_review' ), 'wallet_credited' => self::credited( $item->get_id() ), 'eligible' => ! $hold && ! $return_hold && $order->is_paid() && $order->has_status( 'completed' ) && in_array( $item->get_meta( '_airsoft_seller_status' ), array( 'complete', 'completed' ), true ) && 'rejected' !== $item->get_meta( '_airsoft_payout_review' ), 'can_ship' => $physical && $quantity > 0 && ! $refunded && $order->is_paid() && $order->has_status( array( 'processing', 'completed' ) ), 'needs_shipment' => $physical && $quantity > 0 && ! $refunded && ! $tracking && $order->is_paid() && $order->has_status( 'processing' ) );
 }

 public static function portal_page( $page = 1, $filters = array() ) {
  $result = array( 'items' => array(), 'more' => false );
  if ( ! Airsoft_Product_Manager_Resellers::approved() || ! get_current_user_id() ) { return $result; }
  $filters = self::normalize_filters( $filters );
  $matches = array(); $scanned = 0; $limited = false;
  $offset = ( max( 1, absint( $page ) ) - 1 ) * 20;
  $needed = $offset + 21;
  for ( $scan_page = 1; ; $scan_page++ ) {
   $rows = (array) self::rows( get_current_user_id(), $scan_page, $filters['search'], 100 );
   self::prime_item_state( array_map( static function( $row ) { return $row->order_item_id; }, $rows ) );
   foreach ( array_slice( $rows, 0, 100 ) as $row ) {
    if ( $scanned++ >= self::PORTAL_SCAN_LIMIT ) { $limited = true; break 2; }
    $order = wc_get_order( $row->order_id );
    $entry = self::portal_entry( $order, $order ? $order->get_item( $row->order_item_id ) : false );
    if ( ! $entry ) { continue; }
    if ( ! self::matches_filters( $entry, $filters ) ) { continue; }
    $matches[] = $entry;
    if ( count( $matches ) >= $needed ) { break 2; }
   }
   if ( count( $rows ) <= 100 ) { break; }
  }
  $result['items'] = array_slice( $matches, $offset, 20 );
  $result['more'] = count( $matches ) > $offset + 20;
  $result['limited'] = $limited;
  return $result;
 }

 public static function export_orders() {
  if ( ! is_user_logged_in() || ! Airsoft_Product_Manager_Resellers::approved() ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_reseller_orders_csv' );
  $filters = self::normalize_filters( $_GET );
  nocache_headers();
  header( 'Content-Type: text/csv; charset=utf-8' );
  header( 'Content-Disposition: attachment; filename="reseller-orders-' . gmdate( 'Y-m-d' ) . '.csv"' );
  $out = fopen( 'php://output', 'w' );
  fputcsv( $out, array( 'Order number', 'Order date', 'Order status', 'Product', 'Variation', 'Quantity', 'Item total', 'Currency', 'Fulfilment status', 'Carrier', 'Tracking number', 'Tracking URL', 'Customer', 'Shipping address' ) );
  $scanned = 0; $limited = false;
  for ( $scan_page = 1; ; $scan_page++ ) {
   $rows = (array) self::rows( get_current_user_id(), $scan_page, $filters['search'], 100 );
   self::prime_item_state( array_map( static function( $row ) { return $row->order_item_id; }, $rows ) );
   foreach ( array_slice( $rows, 0, 100 ) as $row ) {
    if ( $scanned++ >= self::PORTAL_SCAN_LIMIT ) { $limited = true; break 2; }
    $order = wc_get_order( $row->order_id );
    $entry = self::portal_entry( $order, $order ? $order->get_item( $row->order_item_id ) : false );
    if ( ! $entry || ! self::matches_filters( $entry, $filters ) ) { continue; }
    $values = self::export_row( $entry );
    foreach ( $values as &$value ) { $value = self::csv_value( $value ); }
    unset( $value ); fputcsv( $out, $values );
   }
   if ( count( $rows ) <= 100 ) { break; }
  }
  if ( $limited ) { fputcsv( $out, array( 'Export capped at ' . self::PORTAL_SCAN_LIMIT . ' order items. Refine the search or date range to export a smaller set.' ) ); }
  fclose( $out );
  exit;
 }

 public static function return_requests( $reseller_id = 0, $limit = 50, $page = 1, $states = array() ) {
  global $wpdb;
  $where = array(); $args = array();
  if ( $reseller_id ) { $where[] = 'reseller_id = %d'; $args[] = absint( $reseller_id ); }
  $states = array_values( array_intersect( array_map( 'sanitize_key', (array) $states ), array( 'requested', 'approved', 'rejected', 'resolved' ) ) );
  if ( $states ) { $where[] = 'state IN (' . implode( ',', array_fill( 0, count( $states ), '%s' ) ) . ')'; $args = array_merge( $args, $states ); }
  $sql = 'SELECT * FROM ' . self::returns_table() . ( $where ? ' WHERE ' . implode( ' AND ', $where ) : '' ) . ' ORDER BY id DESC LIMIT %d OFFSET %d';
  $args[] = max( 1, absint( $limit ) ); $args[] = ( max( 1, absint( $page ) ) - 1 ) * max( 1, absint( $limit ) );
  return (array) $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) );
 }

 public static function return_request_count( $states, $reseller_id = 0 ) {
  global $wpdb;
  $states = array_values( array_intersect( array_map( 'sanitize_key', (array) $states ), array( 'requested', 'approved', 'rejected', 'resolved' ) ) );
  if ( ! $states ) { return 0; }
  $sql = 'SELECT COUNT(*) FROM ' . self::returns_table() . ' WHERE state IN (' . implode( ',', array_fill( 0, count( $states ), '%s' ) ) . ')';
  $args = $states;
  if ( $reseller_id ) { $sql .= ' AND reseller_id = %d'; $args[] = absint( $reseller_id ); }
  return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) );
 }

 public static function return_requests_for_item( $reseller_id, $item_id ) {
  global $wpdb;
  return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::returns_table() . ' WHERE reseller_id = %d AND item_id = %d ORDER BY (state IN (%s,%s)) DESC, id DESC LIMIT 20', absint( $reseller_id ), absint( $item_id ), 'requested', 'approved' ) );
 }

 public static function request_return() {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { throw new Exception( 'Reseller approval is required.' ); }
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( absint( $_POST['item_id'] ?? 0 ) ) : false;
  $reason = sanitize_textarea_field( wp_unslash( $_POST['return_reason'] ?? '' ) );
  if ( ! $order || ! $item || (int) $item->get_order_id() !== $order->get_id() || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || ! $order->is_paid() || $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) || ! $reason || strlen( $reason ) > 2000 ) { throw new Exception( 'This return request is not valid.' ); }
  if ( self::paid( $item->get_id() ) || self::credited( $item->get_id() ) ) { throw new Exception( 'This item has already been paid; contact the site administrator.' ); }
  global $wpdb;
  $lock = 'airsoft_return_' . absint( $item->get_id() );
  if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock ) ) ) { throw new Exception( 'Return request is busy. Please retry.' ); }
  try {
   self::invalidate_item_state( $item->get_id() );
   if ( self::has_open_return( $item->get_id() ) ) { throw new Exception( 'A return request is already open for this item.' ); }
   $saved = $wpdb->insert( self::returns_table(), array( 'order_id' => $order->get_id(), 'item_id' => $item->get_id(), 'reseller_id' => get_current_user_id(), 'reason' => $reason, 'state' => 'requested', 'created_at' => current_time( 'mysql', true ), 'admin_id' => 0, 'decision_note' => '', 'resolution_note' => '', 'resolved_by' => 0 ), array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d' ) );
  } finally { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); }
  if ( ! $saved ) { throw new Exception( 'The return request could not be saved.' ); }
  self::invalidate_item_state( $item->get_id() );
  $item_label = self::item_label( $item );
  $order->add_order_note( 'Reseller requested a return review for ' . $item_label . '. No refund was issued.' );
  Airsoft_Product_Manager_Reseller_Emails::send( get_option( 'admin_email' ), 'Reseller return request for order #' . $order->get_order_number(), 'A reseller requested a return review for ' . $item_label . ' on order #' . $order->get_order_number() . '. Review it under Product Manager > Reseller payouts: ' . admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) );
  wc_add_notice( 'Return request sent to the site administrator for review.', 'success' );
 }

 public static function review_return() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['return_id'] ?? 0 ); check_admin_referer( 'airsoft_reseller_return_' . $id );
  $decision = sanitize_key( wp_unslash( $_POST['decision'] ?? '' ) );
  $note = sanitize_textarea_field( wp_unslash( $_POST['decision_note'] ?? '' ) );
  if ( ! in_array( $decision, array( 'approved', 'rejected', 'completed' ), true ) || strlen( $note ) > 2000 ) { wp_die( 'Invalid return review.' ); }
  global $wpdb;
  $next_state = self::transition_return( $id, $decision, $note, get_current_user_id() );
  if ( ! $next_state ) { wp_die( 'This return request was already reviewed.' ); }
  $request = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::returns_table() . ' WHERE id = %d', $id ) );
  $order = $request ? wc_get_order( $request->order_id ) : false;
  if ( $order ) {
   $review_item = $order->get_item( $request->item_id );
   $order->add_order_note( 'Return request for item ' . ( $review_item ? self::item_label( $review_item ) : '#' . absint( $request->item_id ) ) . ' was ' . $next_state . '. Any refund must be processed through WooCommerce separately.' );
   self::invalidate_item_state( $request->item_id );
   $reseller = get_userdata( $request->reseller_id );
   if ( $reseller ) { Airsoft_Product_Manager_Reseller_Emails::send( $reseller->user_email, 'Return request update for order #' . $order->get_order_number(), 'Your return request for order #' . $order->get_order_number() . ' was updated to ' . $next_state . '.' . ( $note ? "\n\nSite note: " . $note : '' ) . ' View the order in your Reseller Portal: ' . add_query_arg( array( 'section' => 'sales', 'order_id' => $order->get_id() ), Airsoft_Product_Manager_Resellers::url() ) ); }
  }
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) ); exit;
 }

 public static function transition_return( $id, $decision, $note, $admin_id ) {
  if ( ! in_array( $decision, array( 'approved', 'rejected', 'completed' ), true ) || strlen( (string) $note ) > 2000 ) { return false; }
  $from_state = 'completed' === $decision ? 'approved' : 'requested';
  $next_state = 'completed' === $decision ? 'resolved' : $decision;
  $data = 'completed' === $decision ? array( 'state' => $next_state, 'resolved_by' => $admin_id, 'resolution_note' => $note ) : array( 'state' => $next_state, 'admin_id' => $admin_id, 'decision_note' => $note );
  global $wpdb;
  $updated = $wpdb->update( self::returns_table(), $data, array( 'id' => absint( $id ), 'state' => $from_state ), array( '%s', '%d', '%s' ), array( '%d', '%s' ) );
  if ( 1 !== $updated ) { return false; }
  $request = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::returns_table() . ' WHERE id = %d', $id ) );
  if ( $request ) { self::invalidate_item_state( $request->item_id ); }
  return $next_state;
 }

 public static function portal_order( $id ) {
  $entries = array();
  if ( ! Airsoft_Product_Manager_Resellers::approved() || ! get_current_user_id() ) { return $entries; }
  $order = wc_get_order( absint( $id ) );
  if ( ! $order || 'shop_order' !== $order->get_type() ) { return $entries; }
  self::prime_item_state( array_map( static function( $item ) { return $item->get_id(); }, $order->get_items() ) );
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
   $rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT i.order_id, i.order_item_id, p.item_id AS payout_item_id, p.amount AS payout_amount, p.currency AS payout_currency, p.reference AS payout_reference, p.destination AS payout_destination, p.bank_details AS payout_bank_details, p.paid_at AS payout_date FROM {$wpdb->prefix}woocommerce_order_items i INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m ON i.order_item_id = m.order_item_id LEFT JOIN $table p ON p.item_id = i.order_item_id WHERE m.meta_key = '_airsoft_reseller_owner' AND m.meta_value = %s AND i.order_item_id < %d ORDER BY i.order_item_id DESC LIMIT 200", (string) $owner, $cursor ) );
   self::prime_item_state( array_map( static function( $row ) { return $row->order_item_id; }, $rows ) );
   foreach ( $rows as $row ) {
    $cursor = min( $cursor, (int) $row->order_item_id );
    if ( ! $last_order || $last_order->get_id() !== (int) $row->order_id ) { $last_order = wc_get_order( $row->order_id ); }
    $paid = ! empty( $row->payout_item_id ) ? (object) array( 'amount' => $row->payout_amount, 'currency' => $row->payout_currency, 'reference' => $row->payout_reference, 'destination' => $row->payout_destination, 'bank_details' => $row->payout_bank_details, 'paid_at' => $row->payout_date ) : false;
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
  self::prime_item_state( array_map( static function( $row ) { return $row->order_item_id; }, (array) $rows ) );
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
   if ( $admin ) { echo '<article class="airsoft-payout-order">'; }
   else { echo '<hr>'; }
   echo '<h4>Order #' . esc_html( $order->get_order_number() ) . ' — ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</h4><p>' . esc_html( self::item_label( $item ) ) . ' × ' . esc_html( $item->get_quantity() ) . '</p>';
   if ( $admin ) { echo '<p>Reseller account ID: ' . absint( $item->get_meta( '_airsoft_reseller_owner' ) ) . '</p>'; }
   echo '<p>Earnings: ' . wp_kses_post( wc_price( $earned, array( 'currency' => $order->get_currency() ) ) ) . ' · Commission: ' . esc_html( $item->get_meta( '_airsoft_commission_rate' ) ) . '%</p>';
   if ( $admin ) {
    $seller_id = absint( $item->get_meta( '_airsoft_reseller_owner' ) );
    $seller = get_userdata( $seller_id );
    echo '<details><summary>Reseller bank details — ' . esc_html( $seller ? $seller->display_name : 'Reseller #' . $seller_id ) . '</summary>';
    self::render_bank_snapshot( self::bank_details( $seller_id ) );
    echo '</details>';
   }
   if ( $paid ) {
    echo '<p>' . ( 'wallet' === ( $paid->destination ?? 'bank' ) ? 'Wallet credit recorded' : 'Bank payment recorded' ) . ': ' . wp_kses_post( wc_price( $paid->amount, array( 'currency' => $paid->currency ) ) ) . ' · ' . esc_html( $paid->reference ) . ' · ' . esc_html( $paid->paid_at ) . ' UTC</p>';
    if ( $admin && ! empty( $paid->bank_details ) ) { self::render_bank_snapshot( $paid->bank_details ); }
    if ( abs( $earned - (float) $paid->amount ) > 0.00001 ) { echo '<p>Adjustment requiring admin reconciliation: ' . wp_kses_post( wc_price( $earned - (float) $paid->amount, array( 'currency' => $order->get_currency() ) ) ) . '</p>'; }
   } elseif ( self::credited( $item->get_id() ) ) { echo '<p>Released to reseller wallet.</p>'; }
   else { echo '<p>Payout: ' . ( self::has_open_return( $item->get_id() ) ? 'On hold — return review' : ( ! $hold && $order->has_status( 'completed' ) && $order->is_paid() ? 'Eligible' : 'Pending completion or refund review' ) ) . '</p>'; }
   if ( $hold ) { echo '<p>This order has a refund without full item allocation. Admin reconciliation is required before recording a payout.</p>'; }
   $tracking = $item->get_meta( '_airsoft_tracking' );
   if ( $tracking ) { echo '<p>Tracking: ' . esc_html( $tracking ) . '</p>'; }
   echo '<p>Reseller status: ' . esc_html( $item->get_meta( '_airsoft_seller_status' ) ? $item->get_meta( '_airsoft_seller_status' ) : 'Not set' ) . '</p>';
   if ( $admin && in_array( $item->get_meta( '_airsoft_seller_status' ), array( 'complete', 'completed' ), true ) && ! self::credited( $item->get_id() ) && ! $paid && 'rejected' !== $item->get_meta( '_airsoft_payout_review' ) && ! self::has_open_return( $item->get_id() ) ) {
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_release"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '"><input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '">';
    wp_nonce_field( 'airsoft_release_' . $item->get_id() );
    echo '<label>Destination <select name="destination">';
    if ( self::wallet_enabled() ) { echo '<option value="wallet" selected>Wallet</option><option value="bank">Bank (record external payment)</option>'; }
    else { echo '<option value="bank" selected>Bank (record external payment)</option>'; }
    echo '</select></label> ';
    Airsoft_Product_Manager_Resellers::field( 'Bank payment reference (required for bank)', 'reference', '', 'text', 'maxlength="200"' );
    echo '<button name="decision" value="release">Release payout</button> <button name="decision" value="reject">Reject payout</button></form>';
   }
   if ( $admin && self::has_open_return( $item->get_id() ) ) { echo '<p>Payout held while a return request is reviewed.</p>'; }
   if ( $admin && 'rejected' === $item->get_meta( '_airsoft_payout_review' ) ) { echo '<p>Payout rejected. The reseller can update the status and submit again.</p>'; }
   if ( ! $admin && $order->is_paid() && $order->has_status( array( 'processing', 'completed' ) ) ) {
    echo '<p>Deliver to:<br>' . wp_kses_post( $order->get_formatted_shipping_address() ) . '</p><form method="post">';
    wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
    echo '<input type="hidden" name="item_id" value="' . absint( $item->get_id() ) . '"><input type="hidden" name="order_id" value="' . absint( $order->get_id() ) . '">';
    Airsoft_Product_Manager_Resellers::field( 'Carrier and tracking number', 'tracking', $tracking, 'text', 'required maxlength="200"' );
    echo '<button name="airsoft_action" value="ship">Save shipment</button></form>';
   }
   if ( $admin ) { echo '</article>'; }

  }
  $page = max( 1, absint( $_GET['orders_page'] ?? 1 ) );
  $url = $admin ? admin_url( 'admin.php?page=airsoft-reseller-payouts' ) : add_query_arg( 'section', 'sales', Airsoft_Product_Manager_Resellers::url() );
  if ( $page > 1 ) { echo '<a href="' . esc_url( add_query_arg( 'orders_page', $page - 1, $url ) ) . '">Previous</a> '; }
  if ( $more ) { echo '<a href="' . esc_url( add_query_arg( 'orders_page', $page + 1, $url ) ) . '">Next</a>'; }
 }
 private static function render_bank_snapshot( $details ) {
  if ( is_string( $details ) ) { $details = json_decode( $details, true ); }
  $details = is_array( $details ) ? $details : array();
  echo '<p><strong>Account holder:</strong> ' . esc_html( $details['account_holder'] ?? 'Not provided' ) . '<br><strong>Bank:</strong> ' . esc_html( $details['bank_name'] ?? 'Not provided' ) . '<br><strong>Account number:</strong> ' . esc_html( $details['account_number'] ?? 'Not provided' ) . '<br><strong>Bank address:</strong><br>' . nl2br( esc_html( $details['bank_address'] ?? '' ) ) . '</p>';
 }

 public static function ship() {
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( absint( $_POST['item_id'] ?? 0 ) ) : false;
  if ( ! $order || ! $item || (int) $item->get_order_id() !== $order->get_id() || ! $order->is_paid() || ! $order->has_status( array( 'processing', 'completed' ) ) || (int) $item->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { throw new Exception( 'You cannot update this order.' ); }
  $carrier = sanitize_text_field( wp_unslash( $_POST['tracking_carrier'] ?? '' ) );
  $tracking = sanitize_text_field( wp_unslash( $_POST['tracking_number'] ?? ( $_POST['tracking'] ?? '' ) ) );
  $raw_url = isset( $_POST['tracking_url'] ) && is_scalar( $_POST['tracking_url'] ) ? (string) wp_unslash( $_POST['tracking_url'] ) : '';
  $url = esc_url_raw( $raw_url );
  if ( ! $tracking || strlen( $tracking ) > 200 || strlen( $carrier ) > 100 || strlen( $raw_url ) > 2000 || preg_match( '/[\x00-\x1F\x7F]/', $raw_url ) || ( $url && ! in_array( wp_parse_url( $url, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) ) { throw new Exception( 'Enter a valid tracking number, carrier and optional HTTP or HTTPS tracking link (maximum 2,000 characters).' ); }
  $item->update_meta_data( '_airsoft_tracking', $tracking ); // Backward compatibility for existing integrations.
  $item->update_meta_data( '_airsoft_tracking_carrier', $carrier );
  $item->update_meta_data( '_airsoft_tracking_number', $tracking );
  $item->update_meta_data( '_airsoft_tracking_url', $url );
  $item->save();
  $description = trim( $carrier . ' ' . $tracking );
  $order->add_order_note( 'Shipment for ' . self::item_label( $item ) . ': ' . $description . ( $url ? ' — ' . $url : '' ), true );
  if ( class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { do_action( 'airsoft_order_shipped', $order->get_id(), 'reseller-item-' . $item->get_id() ); }
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
   $order->add_order_note( 'Admin rejected reseller item ' . self::item_label( $item ) . ' (#' . $id . ') payout.' );
   wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
  }
  if ( ! $order->is_paid() || ! $order->has_status( 'completed' ) || self::needs_reconciliation( $order ) || self::has_open_return( $id ) ) { wp_die( 'Payout cannot be released until the paid order is complete and all refund and return reviews are resolved.' ); }
  $amount = self::earning( $order, $item );
  if ( $amount <= 0 ) { wp_die( 'No earnings to release.' ); }
  $destination = sanitize_key( wp_unslash( $_POST['destination'] ?? self::default_destination() ) );
  if ( ! in_array( $destination, array( 'wallet', 'bank' ), true ) ) { wp_die( 'Choose a valid payout destination.' ); }
  if ( 'wallet' === $destination && ! self::wallet_enabled() ) { wp_die( 'Wallet payouts are paused. Set the default payout destination to Wallet to resume wallet use.' ); }
  global $wpdb;
  $seller_id = absint( $item->get_meta( '_airsoft_reseller_owner' ) );
  $lock = 'airsoft_item_payout_' . $id;
  if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock ) ) ) { wp_die( 'This payout is being processed. Please retry.' ); }
  self::invalidate_item_state( $id );
  if ( self::credited( $id ) || self::paid( $id ) ) {
   $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
   wp_die( 'This item already has a payout.' );
  }
  if ( 'bank' === $destination ) {
   $reference = sanitize_text_field( wp_unslash( $_POST['reference'] ?? '' ) );
   $bank = self::bank_details( $seller_id );
   if ( ! $reference || strlen( $reference ) > 200 ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); wp_die( 'Enter the bank transfer reference before recording this payout.' ); }
   if ( count( array_filter( $bank, 'strlen' ) ) !== count( $bank ) ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); wp_die( 'The reseller must complete bank details before a bank payout can be recorded.' ); }
   $snapshot = wp_json_encode( $bank );
   $saved = $wpdb->insert( self::table(), array( 'item_id' => $id, 'reseller_id' => $seller_id, 'amount' => $amount, 'currency' => $order->get_currency(), 'reference' => $reference, 'destination' => 'bank', 'bank_details' => $snapshot, 'paid_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%d' ) );
   if ( ! $saved ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); wp_die( 'Payout was already recorded or could not be saved.' ); }
   self::$paid_cache[$id] = (object) array( 'destination' => 'bank', 'reference' => $reference );
   $order->add_order_note( 'Admin recorded reseller item ' . self::item_label( $item ) . ' (#' . $id . ') as bank paid: ' . $amount . ' ' . $order->get_currency() . '. Reference: ' . $reference );
  } else {
   if ( ! $wpdb->insert( self::wallet_table(), array( 'reseller_id' => $seller_id, 'item_id' => $id, 'amount' => $amount, 'currency' => $order->get_currency(), 'state' => 'credit', 'created_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%d' ) ) ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); wp_die( 'Payout was already released or could not be saved.' ); }
   self::$credited_cache[$id] = true;
   $order->add_order_note( 'Admin released reseller item ' . self::item_label( $item ) . ' (#' . $id . ') earnings to wallet: ' . $amount . ' ' . $order->get_currency() . '.' );
  }
  $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
  $seller = get_userdata( $seller_id );
  if ( $seller ) { Airsoft_Product_Manager_Reseller_Emails::send( $seller->user_email, 'Reseller payout update for order #' . $order->get_order_number(), 'Your earnings for ' . self::item_label( $item ) . ' on order #' . $order->get_order_number() . ' were ' . ( 'bank' === $destination ? 'recorded as paid to your bank account. Reference: ' . $reference : 'credited to your reseller wallet.' ) . ' View your Earnings in the Reseller Portal: ' . add_query_arg( 'section', 'earnings', Airsoft_Product_Manager_Resellers::url() ) ); }
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
  if ( ! self::wallet_enabled() ) { throw new Exception( 'Wallet withdrawals are paused while Bank is selected as the default payout destination.' ); }
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
   $number = $item->get_meta( '_airsoft_tracking_number' );
   if ( ! $number ) { $number = $item->get_meta( '_airsoft_tracking' ); }
   if ( $number ) {
    $carrier = $item->get_meta( '_airsoft_tracking_carrier' );
    $url = esc_url( $item->get_meta( '_airsoft_tracking_url' ) );
    echo '<p>' . esc_html( self::item_label( $item ) ) . ' — ' . ( $carrier ? esc_html( $carrier ) . ': ' : 'Tracking: ' );
    echo $url ? '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . esc_html( $number ) . '</a>' : esc_html( $number );
    echo '</p>';
   }
  }
 }
 public static function menu() {
  add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'Reseller payouts', 'Reseller payouts', 'manage_woocommerce', 'airsoft-reseller-payouts', array( __CLASS__, 'admin' ) );
 }
 public static function admin_assets( $hook ) {
  if ( false === strpos( (string) $hook, 'airsoft-reseller-payouts' ) ) { return; }
  $path = AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/css/reseller-admin.css';
  wp_enqueue_style( 'airsoft-reseller-admin', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/css/reseller-admin.css', array(), file_exists( $path ) ? filemtime( $path ) : '1.0.0' );
 }
 public static function admin() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
  echo '<div class="wrap airsoft-reseller-payout-admin"><header class="airsoft-payout-header"><div><p class="airsoft-payout-eyebrow">PRODUCT MANAGER</p><h1>Reseller payouts</h1><p class="airsoft-payout-intro">Review reseller earnings, manage payout settings, and resolve return or withdrawal requests.</p></div><a class="button" href="#return-requests">Review return requests</a></header><div class="airsoft-payout-guidance"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><p><strong>Manual payment reminder</strong><br>Wallet releases credit the reseller wallet. For Bank, transfer funds outside the site first, then record the payment reference. Each order item can be released once. Refunds after payment show an adjustment for manual reconciliation; checkout tax and shipping remain with the site.</p></div><section class="airsoft-payout-settings"><div class="airsoft-payout-section-heading"><div><h2>Payout settings</h2><p>Set the commission for new orders and choose the default payout destination.</p></div></div><div class="airsoft-payout-settings-grid"><article class="airsoft-payout-card"><h3>Commission rate</h3><p>Applies to new reseller orders. Existing orders keep their saved rate.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_commission">';
  wp_nonce_field( 'airsoft_commission' );
  Airsoft_Product_Manager_Resellers::field( 'Commission % for new orders', 'commission', self::rate(), 'number', 'required min="0" max="100" step="0.01"' );
  echo '<button class="button button-primary">Save commission</button></form></article><article class="airsoft-payout-card"><h3>Default destination</h3><p>Bank pauses wallet use while preserving existing balances. In Wallet mode, you can still choose Bank for an individual payout.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_payout_settings">';
  wp_nonce_field( 'airsoft_reseller_payout_settings' );
  echo '<label>Default payout destination<select name="destination"><option value="wallet"' . selected( self::default_destination(), 'wallet', false ) . '>Wallet</option><option value="bank"' . selected( self::default_destination(), 'bank', false ) . '>Bank (external payment recorded by admin)</option></select></label><button class="button button-primary">Save payout setting</button></form></article></div></section><section class="airsoft-payout-orders">';
  self::render( true );
  echo '</section>';
  global $wpdb;
  echo '<section class="airsoft-payout-section airsoft-payout-returns"><div class="airsoft-payout-section-heading"><div><h2 id="return-requests">Open return requests</h2><p>Review the return reason and order before resolving a request. Approval does not issue a refund; process refunds through WooCommerce.</p></div><span class="airsoft-payout-count">' . absint( self::return_request_count( array( 'requested', 'approved' ) ) ) . ' open</span></div>';
  $return_page = max( 1, absint( $_GET['return_page'] ?? 1 ) );
  $return_requests = self::return_requests( 0, 25, $return_page, array( 'requested', 'approved' ) );
  if ( ! $return_requests ) { echo '<p>No open return requests.</p>'; }
  foreach ( $return_requests as $return_request ) {
   $order = wc_get_order( $return_request->order_id );
   $item = $order ? $order->get_item( $return_request->item_id ) : false;
   echo '<div class="airsoft-return-admin-request"><h3>Order #' . esc_html( $order ? $order->get_order_number() : $return_request->order_id ) . ' · Item ' . esc_html( $item ? self::item_label( $item ) : $return_request->item_id ) . '</h3><p>Reseller account #' . absint( $return_request->reseller_id ) . ' · ' . esc_html( $return_request->created_at ) . ' UTC · <strong>' . esc_html( ucfirst( $return_request->state ) ) . '</strong></p><p>' . nl2br( esc_html( $return_request->reason ) ) . '</p>';
   if ( in_array( $return_request->state, array( 'requested', 'approved' ), true ) ) {
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_return_review"><input type="hidden" name="return_id" value="' . absint( $return_request->id ) . '">';
    wp_nonce_field( 'airsoft_reseller_return_' . $return_request->id );
    Airsoft_Product_Manager_Resellers::field( 'Decision note (optional)', 'decision_note', '', 'text', 'maxlength="2000"' );
    if ( 'requested' === $return_request->state ) { echo '<button class="button button-primary" name="decision" value="approved">Approve request</button> <button class="button" name="decision" value="rejected">Reject request</button>'; }
    else { echo '<p>After processing any refund through WooCommerce, close the review to remove the payout hold.</p><button class="button button-primary" name="decision" value="completed">Mark resolved</button>'; }
    echo '</form>';
   } elseif ( $return_request->decision_note ) { echo '<p>Review note: ' . esc_html( $return_request->decision_note ) . '</p>'; }
   echo '</div>';
  }
  $open_count = self::return_request_count( array( 'requested', 'approved' ) );
  if ( $return_page > 1 ) { echo '<a href="' . esc_url( add_query_arg( 'return_page', $return_page - 1, admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) ) ) . '">Previous open returns</a> '; }
  if ( $open_count > $return_page * 25 ) { echo '<a href="' . esc_url( add_query_arg( 'return_page', $return_page + 1, admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) ) ) . '">Next open returns</a>'; }
  echo '<div class="airsoft-payout-subsection"><h3>Closed return history <span class="airsoft-payout-count">' . absint( self::return_request_count( array( 'rejected', 'resolved' ) ) ) . '</span></h3>';
  $history_page = max( 1, absint( $_GET['return_history_page'] ?? 1 ) );
  $history = self::return_requests( 0, 25, $history_page, array( 'rejected', 'resolved' ) );
  foreach ( $history as $return_request ) {
   $order = wc_get_order( $return_request->order_id ); $item = $order ? $order->get_item( $return_request->item_id ) : false;
   echo '<p>Order #' . esc_html( $order ? $order->get_order_number() : $return_request->order_id ) . ' · ' . esc_html( $item ? self::item_label( $item ) : $return_request->item_id ) . ' · ' . esc_html( ucfirst( $return_request->state ) ) . ' · ' . esc_html( $return_request->created_at ) . ' UTC</p>';
   if ( $return_request->decision_note ) { echo '<p>Review note: ' . esc_html( $return_request->decision_note ) . '</p>'; }
   if ( $return_request->resolution_note ) { echo '<p>Resolution note: ' . esc_html( $return_request->resolution_note ) . '</p>'; }
  }
  $closed_count = self::return_request_count( array( 'rejected', 'resolved' ) );
  if ( $history_page > 1 ) { echo '<a href="' . esc_url( add_query_arg( 'return_history_page', $history_page - 1, admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) ) ) . '">Previous return history</a> '; }
  if ( $closed_count > $history_page * 25 ) { echo '<a href="' . esc_url( add_query_arg( 'return_history_page', $history_page + 1, admin_url( 'admin.php?page=airsoft-reseller-payouts#return-requests' ) ) ) . '">Next return history</a>'; }
  echo '</div></section><section class="airsoft-payout-section airsoft-payout-withdrawals"><div class="airsoft-payout-section-heading"><div><h2>Withdrawal requests</h2><p>Review reseller wallet withdrawals awaiting an external payment decision.</p></div></div>';
  $requests = $wpdb->get_results( "SELECT * FROM " . self::wallet_table() . " WHERE state = 'requested' AND item_id IS NULL ORDER BY id DESC LIMIT 100" );
  if ( ! $requests ) { echo '<p>No pending withdrawals.</p>'; }
  foreach ( (array) $requests as $request ) {
   echo '<article class="airsoft-withdrawal-request"><div><strong>Reseller #' . absint( $request->reseller_id ) . '</strong><span>' . wp_kses_post( wc_price( $request->amount, array( 'currency' => $request->currency ) ) ) . '</span><small>Requested ' . esc_html( $request->created_at ) . ' UTC</small></div><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_withdrawal_review"><input type="hidden" name="wallet_id" value="' . absint( $request->id ) . '">';
   wp_nonce_field( 'airsoft_withdrawal_' . $request->id );
   echo '<button class="button button-primary" name="decision" value="paid">Mark externally paid</button> <button class="button" name="decision" value="rejected">Reject and return to wallet</button></form></article>';
  }
  echo '</section></div>';
 }
 public static function commission() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_commission' );
  $rate = wc_format_decimal( wp_unslash( $_POST['commission'] ?? '' ) );
  if ( ! is_numeric( $rate ) || $rate < 0 || $rate > 100 ) { wp_die( 'Commission must be between 0 and 100.' ); }
  update_option( 'airsoft_reseller_commission', $rate );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
 public static function save_payout_settings() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_reseller_payout_settings' );
  $destination = sanitize_key( wp_unslash( $_POST['destination'] ?? '' ) );
  if ( ! in_array( $destination, array( 'wallet', 'bank' ), true ) ) { wp_die( 'Choose a valid payout destination.' ); }
  update_option( 'airsoft_reseller_default_payout_destination', $destination, false );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
 public static function payout() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['item_id'] ?? 0 );
  check_admin_referer( 'airsoft_payout_' . $id );
  $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
  $item = $order ? $order->get_item( $id ) : false;
  $reference = sanitize_text_field( wp_unslash( $_POST['reference'] ?? '' ) );
  if ( ! $order || ! $item || (int) $item->get_order_id() !== $order->get_id() || ! $item->get_meta( '_airsoft_reseller_owner' ) || $item->get_meta( '_airsoft_seller_status' ) || self::credited( $id ) || ! $order->is_paid() || ! $order->has_status( 'completed' ) || self::needs_reconciliation( $order ) || self::has_open_return( $id ) || ! $reference || strlen( $reference ) > 200 ) { wp_die( 'Invalid payout or refund reconciliation required.' ); }
  $amount = self::earning( $order, $item );
  if ( $amount <= 0 || abs( $amount - (float) ( $_POST['amount'] ?? -1 ) ) > 0.00001 ) { wp_die( 'Earnings changed. Refresh and review this order before recording payment.' ); }
  global $wpdb;
  $saved = $wpdb->insert( self::table(), array( 'item_id' => $id, 'reseller_id' => absint( $item->get_meta( '_airsoft_reseller_owner' ) ), 'amount' => $amount, 'currency' => $order->get_currency(), 'reference' => $reference, 'paid_at' => current_time( 'mysql', true ), 'admin_id' => get_current_user_id() ), array( '%d', '%d', '%f', '%s', '%s', '%s', '%d' ) );
  if ( ! $saved ) { wp_die( 'Payment was already recorded, or the payout record could not be saved. Check the payout list.' ); }
  $order->add_order_note( 'Reseller payout recorded for item ' . self::item_label( $item ) . ' (#' . $id . '): ' . $amount . ' ' . $order->get_currency() . '. Reference: ' . $reference );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-payouts' ) ); exit;
 }
}
