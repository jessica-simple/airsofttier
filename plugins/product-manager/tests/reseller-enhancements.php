<?php
/** Focused regression checks for the 1.6 reseller workflow changes. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/fixtures/' );
define( 'MINUTE_IN_SECONDS', 60 );
$current = 77; $options = array( 'airsoft_reseller_ledger_version' => '2' ); $products = array(); $orders = array(); $user_meta = array(); $next_product = 1; $test_currency = 'USD';
function get_current_user_id() { global $current; return $current; }
function is_user_logged_in() { return get_current_user_id() > 0; }
function current_user_can( $cap ) { return 'manage_woocommerce' === $cap; }
function get_userdata( $id ) { return (int) $id === 77 ? (object) array( 'roles' => array( 'airsoft_reseller' ), 'user_email' => 'seller@example.test' ) : false; }
function get_option( $key, $default = false ) { global $options; return $options[$key] ?? $default; }
function update_option( $key, $value, $autoload = null ) { global $options; $options[$key] = $value; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) ); }
function sanitize_title( $value ) { return sanitize_key( str_replace( ' ', '-', (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function wp_unslash( $value ) { return is_string( $value ) ? stripslashes( $value ) : $value; }
function wp_strip_all_tags( $value ) { return strip_tags( (string) $value ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url_raw( $value ) { return filter_var( (string) $value, FILTER_SANITIZE_URL ); }
function wp_kses_post( $value ) { return $value; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function wc_get_price_decimals() { return 2; }
function wc_price( $amount, $args = array() ) { global $test_currency; $currency = $args['currency'] ?? $test_currency; $symbol = array( 'USD' => '&#36;', 'HKD' => 'HK&#36;', 'GBP' => '&pound;' )[$currency] ?? $currency . ' '; return $symbol . number_format( (float) $amount, 2, '.', '' ); }
function wc_format_decimal( $value ) { return is_numeric( $value ) ? (string) $value : ''; }
function wc_get_order_status_name( $status ) { return array( 'processing' => 'Processing', 'on-hold' => 'On hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded', 'to-ship' => 'To ship', 'shipped' => 'Shipped' )[$status] ?? ucfirst( $status ); }
function wc_get_order_statuses() { return array( 'wc-processing' => 'Processing', 'wc-on-hold' => 'On hold', 'wc-completed' => 'Completed' ); }
function get_post_stati( $args = array(), $output = 'names' ) { return array(); }
function wc_format_datetime( $date ) { return $date ? '2026-11-03' : ''; }
function wc_get_formatted_variation( $product, $flat = false, $include_names = true, $skip = false ) { return $product->variation_label; }
function wc_get_product( $id ) { global $products; return $products[(int) $id] ?? false; }
function wc_get_order( $id ) { global $orders; return $orders[(int) $id] ?? false; }
function wc_add_notice( $message, $type = '' ) {}
function get_userdata_dummy() { return false; }
function current_time( $type, $gmt = false ) { return '2026-11-03 00:00:00'; }
function get_option_dummy() {}
function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . $path; }
function home_url( $path = '' ) { return 'https://example.test' . $path; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function check_admin_referer( $action ) {}
function wp_die( $message ) { throw new Exception( $message ); }
function do_action( $action, ...$args ) {}
function get_user_meta( $user_id, $key = '', $single = false ) { global $user_meta; return '' === $key ? ( $user_meta[$user_id] ?? array() ) : ( $user_meta[$user_id][$key][0] ?? '' ); }
function maybe_unserialize( $value ) { return $value; }
function add_user_meta( $user_id, $key, $value, $unique = false ) { global $user_meta; if ( $unique && isset( $user_meta[$user_id][$key] ) ) { return false; } $user_meta[$user_id][$key] = array( $value ); return true; }
function delete_user_meta( $user_id, $key ) { global $user_meta; unset( $user_meta[$user_id][$key] ); }
function esc_url( $url ) { return esc_html( $url ); }
function __( $text ) { return $text; }
function set_transient( $key, $value, $expiration ) {}
function get_transient( $key ) { return false; }
function delete_transient( $key ) {}
function wp_nonce_field( $action, $name = '_wpnonce' ) { echo '<input name="' . $name . '">'; }
class Airsoft_Product_Manager_Reseller_Emails { public static function send( $to, $subject, $body ) { return true; } }
class WC_Product_Attribute {
 public $name, $options, $variation;
 public function set_id( $value ) {}
 public function set_name( $value ) { $this->name = $value; }
 public function set_options( $value ) { $this->options = $value; }
 public function set_position( $value ) {}
 public function set_visible( $value ) {}
 public function set_variation( $value ) { $this->variation = $value; }
}
class WC_Product_Variable {
 public $id = 0, $name, $description, $attributes = array(), $meta = array(), $status;
 public function set_name( $value ) { $this->name = $value; }
 public function set_description( $value ) { $this->description = $value; }
 public function set_attributes( $value ) { $this->attributes = $value; }
 public function set_manage_stock( $value ) {}
 public function set_status( $value ) { $this->status = $value; }
 public function update_meta_data( $key, $value ) { $this->meta[$key] = $value; }
 public function set_image_id( $value ) {}
 public function is_type( $type ) { return 'variable' === $type; }
 public function get_variation_regular_price( $range, $display ) { return 20; }
 public function get_regular_price( $context = '' ) { return ''; }
 public function get_meta( $key ) { return $this->meta[$key] ?? ''; }
 public function get_status() { return $this->status; }
 public function get_manage_stock() { return false; }
 public function get_stock_quantity() { return null; }
 public function get_image( $size ) { return ''; }
 public function get_name() { return $this->name; }
 public function get_permalink() { return ''; }
 public function save() { global $next_product, $products; $this->id = $next_product++; $products[$this->id] = $this; return $this->id; }
 public static function sync( $id ) {}
}
class WC_Product_Variation {
 public static $saved = array(); public $parent_id, $attributes, $regular_price, $price, $stock, $meta = array();
 public function set_parent_id( $id ) { $this->parent_id = $id; }
 public function set_attributes( $value ) { $this->attributes = $value; }
 public function set_regular_price( $value ) { $this->regular_price = $value; }
 public function set_price( $value ) { $this->price = $value; }
 public function set_manage_stock( $value ) {}
 public function set_stock_quantity( $value ) { $this->stock = $value; }
 public function set_backorders( $value ) {}
 public function update_meta_data( $key, $value ) { $this->meta[$key] = $value; }
 public function save() { self::$saved[] = $this; return count( self::$saved ); }
}
class TestVariationProduct {
 public $id, $parent, $variation_label;
 public function __construct( $id, $parent, $label ) { $this->id = $id; $this->parent = $parent; $this->variation_label = $label; }
 public function is_type( $type ) { return 'variation' === $type; }
 public function get_parent_id() { return $this->parent; }
 public function get_image( $size ) { return ''; }
}
class TestLineItem {
 public $id, $owner, $product, $name = 'Replica', $meta = array();
 public function __construct( $id, $owner, $product = null ) { $this->id = $id; $this->owner = $owner; $this->product = $product; }
 public function get_id() { return $this->id; }
 public function get_name() { return $this->name; }
 public function get_product() { return $this->product; }
 public function get_meta( $key ) { return '_airsoft_reseller_owner' === $key ? $this->owner : ( $this->meta[$key] ?? '' ); }
 public function update_meta_data( $key, $value ) { $this->meta[$key] = $value; }
 public function save() {}
 public function get_quantity() { return 1; }
 public function get_total() { return 25; }
 public function get_order_id() { return 900; }
}
class WC_Order {}
class WC_Order_Refund {}
class TestShopOrder extends WC_Order {
 public $id = 900, $item, $notes = array(), $status = 'processing';
 public function __construct( $item ) { $this->item = $item; }
 public function get_type() { return 'shop_order'; }
 public function get_id() { return $this->id; }
 public function get_item( $id ) { return $this->item->id === (int) $id ? $this->item : false; }
 public function get_items() { return array( $this->item->id => $this->item ); }
 public function get_customer_id() { return 88; }
 public function get_order_number() { return '900'; }
 public function get_date_created() { return new class { public function date( $format ) { return '2026-11-03 12:00:00'; } }; }
 public function get_currency() { return 'USD'; }
 public function get_formatted_billing_full_name() { return 'Buyer'; }
 public function get_formatted_shipping_address() { return '1 Main Street'; }
 public function is_paid() { return true; }
 public function get_status() { return $this->status; }
 public function has_status( $statuses ) { return in_array( $this->status, (array) $statuses, true ); }
 public function add_order_note( $note, $customer_note = false ) { $this->notes[] = $note; }
 public function get_view_order_url() { return 'https://example.test/order/900'; }
 public function needs_payment() { return false; }
 public function get_checkout_payment_url() { return ''; }
 public function get_id_for_test() { return $this->id; }
 public function get_meta( $key, $single = true ) { return ''; }
 public function update_meta_data( $key, $value ) {}
 public function save_meta_data() {}
}
class EnhancementDB {
 public $prefix = 'wp_', $payouts = array(), $credits = array(), $returns = array(), $schema_changes = array();
 public function get_charset_collate() { return 'DEFAULT CHARACTER SET utf8mb4'; }
 public function esc_like( $value ) { return addcslashes( $value, '_%\\' ); }
 public function prepare( $sql, ...$args ) { $index = 0; return preg_replace_callback( '/%[ds]/', static function( $match ) use ( &$index, $args ) { $arg = $args[$index++] ?? ''; return '%d' === $match[0] ? (string) (int) $arg : "'" . str_replace( "'", "''", (string) $arg ) . "'"; }, $sql ); }
 public function get_var( $sql ) {
  if ( false !== strpos( $sql, 'GET_LOCK' ) || false !== strpos( $sql, 'RELEASE_LOCK' ) ) { return 1; }
  if ( false !== strpos( $sql, 'SHOW TABLES LIKE' ) ) { return 'wp_airsoft_reseller_returns'; }
  if ( false !== strpos( $sql, 'COUNT(*)' ) ) { preg_match( '/state IN \(([^)]+)\)/', $sql, $m ); $states = array_map( static function( $s ) { return trim( $s, " '\"" ); }, explode( ',', $m[1] ?? '' ) ); $count = 0; foreach ( $this->returns as $row ) { if ( in_array( $row['state'], $states, true ) ) { $count++; } } return $count; }
  if ( false !== strpos( $sql, 'airsoft_reseller_wallet' ) && preg_match( '/item_id = (\d+)/', $sql, $m ) ) { return ! empty( $this->credits[(int) $m[1]] ) ? 1 : 0; }
  if ( false !== strpos( $sql, 'airsoft_reseller_returns' ) && preg_match( '/item_id = (\d+)/', $sql, $m ) ) { foreach ( $this->returns as $row ) { if ( $row['item_id'] === (int) $m[1] && in_array( $row['state'], array( 'requested', 'approved' ), true ) ) { return $row['id']; } } }
  return 0;
 }
 public function get_row( $sql ) {
  if ( false !== strpos( $sql, 'airsoft_reseller_payouts' ) && preg_match( '/item_id = (\d+)/', $sql, $m ) ) { return $this->payouts[(int) $m[1]] ?? false; }
  if ( false !== strpos( $sql, 'airsoft_reseller_returns' ) && preg_match( '/id = (\d+)/', $sql, $m ) ) { foreach ( $this->returns as $row ) { if ( $row['id'] === (int) $m[1] ) { return (object) $row; } } }
  return false;
 }
 public function get_results( $sql ) {
  if ( false !== strpos( $sql, 'item_id IN (' ) ) {
   preg_match( '/IN \(([^)]+)\)/', $sql, $m ); $ids = array_map( 'intval', explode( ',', $m[1] ?? '' ) ); $rows = array();
   if ( false !== strpos( $sql, 'airsoft_reseller_payouts' ) ) { foreach ( $ids as $id ) { if ( isset( $this->payouts[$id] ) ) { $rows[] = (object) array_merge( array( 'item_id' => $id ), (array) $this->payouts[$id] ); } } }
   if ( false !== strpos( $sql, 'airsoft_reseller_wallet' ) ) { foreach ( $ids as $id ) { if ( ! empty( $this->credits[$id] ) ) { $rows[] = (object) array( 'item_id' => $id ); } } }
   return $rows;
  }
  if ( false !== strpos( $sql, 'airsoft_reseller_returns' ) ) {
   preg_match( '/state IN \(([^)]+)\)/', $sql, $m ); $states = array_map( static function( $s ) { return trim( $s, " '\"" ); }, explode( ',', $m[1] ?? '' ) );
   preg_match( '/reseller_id = (\d+)/', $sql, $reseller ); preg_match( '/item_id = (\d+)/', $sql, $item ); preg_match( '/LIMIT (\d+) OFFSET (\d+)/', $sql, $page );
   $rows = array_filter( $this->returns, static function( $row ) use ( $states, $reseller, $item ) { return ( ! $states[0] || in_array( $row['state'], $states, true ) ) && ( ! isset( $reseller[1] ) || $row['reseller_id'] === (int) $reseller[1] ) && ( ! isset( $item[1] ) || $row['item_id'] === (int) $item[1] ); } );
   usort( $rows, static function( $a, $b ) { $ao = in_array( $a['state'], array( 'requested', 'approved' ), true ); $bo = in_array( $b['state'], array( 'requested', 'approved' ), true ); return $ao === $bo ? $b['id'] <=> $a['id'] : ( $bo <=> $ao ); } );
   if ( isset( $page[1] ) ) { $rows = array_slice( $rows, (int) $page[2], (int) $page[1] ); }
   return array_map( static function( $row ) { return (object) $row; }, array_values( $rows ) );
  }
  return array();
 }
 public function insert( $table, $data, $formats = null ) { if ( false !== strpos( $table, 'reseller_returns' ) ) { $data['id'] = count( $this->returns ) + 1; $this->returns[] = $data; return 1; } return 1; }
 public function update( $table, $data, $where, $formats = null, $where_formats = null ) { foreach ( $this->returns as &$row ) { if ( $row['id'] === $where['id'] && $row['state'] === $where['state'] ) { $row = array_merge( $row, $data ); return 1; } } return 0; }
}
$wpdb = new EnhancementDB();
require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-pricing.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-products.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-orders.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-dashboard.php';
require_once __DIR__ . '/../includes/class-product-manager-notifications.php';
function verify_enhancement( $condition, $message ) { global $checks; if ( ! $condition ) { throw new Exception( 'FAIL: ' . $message ); } $checks++; }
$checks = 0;

// A 1.5.x schema upgrades to 1.6 and a fresh install gets the same return ledger.
Airsoft_Product_Manager_Reseller_Orders::install();
verify_enhancement( '4' === $options['airsoft_reseller_ledger_version'] && 3 === count( $wpdb->schema_changes ), 'Option 2 migration creates the return table and advances ledger schema' );
unset( $options['airsoft_reseller_ledger_version'] ); $wpdb->schema_changes = array();
Airsoft_Product_Manager_Reseller_Orders::install();
verify_enhancement( '4' === $options['airsoft_reseller_ledger_version'] && 3 === count( $wpdb->schema_changes ), 'Fresh install creates reseller ledgers including returns' );

// Create products with the minimum and maximum supported variation counts.
$base = array( 'product_type' => 'variable', 'variation_attribute' => 'Size', 'product_name' => 'Replica', 'description' => 'Description' );
foreach ( array( 2, 50 ) as $count ) {
 $rows = array(); for ( $i = 1; $i <= $count; $i++ ) { $rows[] = 'Option ' . $i . ' | ' . ( 20 + $i ) . ' | ' . ( 18 + $i ) . ' | ' . $i; }
 $_POST = array_merge( $base, array( 'variation_rows' => implode( "\n", $rows ) ) ); $_FILES = array();
 Airsoft_Product_Manager_Reseller_Products::save();
 $parent = end( $products ); $created = array_slice( WC_Product_Variation::$saved, -$count );
 verify_enhancement( count( $created ) === $count && count( $parent->attributes ) === 1 && $parent->attributes[0]->variation, $count . ' variations create one variation attribute and the expected variation count' );
 verify_enhancement( 77 === $created[0]->meta['_airsoft_reseller_owner'] && '19' === $created[0]->meta['_airsoft_reseller_price'] && 1 === $created[0]->stock, $count . ' variations retain owner, per-variation price and stock' );
}
foreach ( array( 'USD' => '&#36;20.00', 'HKD' => 'HK&#36;20.00', 'GBP' => '&pound;20.00' ) as $currency => $expected ) {
 $test_currency = $currency;
 $listed = Airsoft_Product_Manager_Reseller_Products::normal_price_display( new WC_Product_Variable() );
 verify_enhancement( false !== strpos( $listed, 'From ' . $expected ) && false === strpos( $listed, '&amp;#' ), $currency . ' variable price keeps a displayable currency entity' );
}

// Variation labels remain distinct and status/filter/export all use one rule.
$parent = new class { public function get_name() { return 'Replica'; } };
$variation = new TestVariationProduct( 600, 55, 'Size: Large' ); $products[55] = $parent; $products[600] = $variation;
$item = new TestLineItem( 777, 77, $variation );
verify_enhancement( 'Replica — Size: Large' === Airsoft_Product_Manager_Reseller_Orders::item_label( $item ), 'Order line keeps parent product name and variation label' );
$export_entry = array( 'order' => new TestShopOrder( $item ), 'item' => $item, 'tracking' => '', 'seller_status' => '', 'needs_shipment' => false );
verify_enhancement( 'Size: Large' === Airsoft_Product_Manager_Reseller_Orders::export_row( $export_entry )[4], 'CSV export row contains a separate variation column' );
$table_item = new class( $item ) {
 public $source;
 public function __construct( $source ) { $this->source = $source; }
 public function get_product() { return $this->source->get_product(); }
 public function get_id() { return $this->source->get_id(); }
 public function get_name() { return $this->source->get_name(); }
 public function get_quantity() { return 1; }
 public function get_total() { return 25; }
 public function get_meta( $key ) { return $this->source->get_meta( $key ); }
};
$table_order = new class {
 public function get_id() { return 900; }
 public function get_currency() { return 'USD'; }
 public function get_date_created() { return false; }
 public function get_status() { return 'processing'; }
 public function get_order_number() { return '900'; }
 public function has_status( $status ) { return in_array( 'processing', (array) $status, true ); }
};
$render_table = new ReflectionMethod( 'Airsoft_Product_Manager_Reseller_Dashboard', 'order_table' ); $render_table->setAccessible( true );
ob_start(); $render_table->invoke( null, array( array( 'order' => $table_order, 'item' => $table_item, 'paid' => false, 'wallet_credited' => false, 'payout_review' => '', 'seller_status' => '', 'needs_shipment' => true, 'tracking' => '' ) ), 'No orders' ); $table_html = ob_get_clean();
verify_enhancement( false !== strpos( $table_html, 'Replica — Size: Large' ), 'Seller order table identifies the selected variation' );
$orders[900] = new TestShopOrder( $item );
$_POST = array( 'order_id' => 900, 'item_id' => 777, 'tracking_carrier' => 'TestShip', 'tracking_number' => 'ABC123', 'tracking_url' => 'https://track.example.test/ABC123' );
Airsoft_Product_Manager_Reseller_Orders::ship();
verify_enhancement( false !== strpos( end( $orders[900]->notes ), 'Replica — Size: Large' ), 'Shipment note identifies the chosen variation' );
ob_start(); Airsoft_Product_Manager_Reseller_Orders::customer_tracking( $orders[900] ); $tracking_html = ob_get_clean();
verify_enhancement( false !== strpos( $tracking_html, 'Replica — Size: Large' ) && false !== strpos( $tracking_html, 'https://track.example.test/ABC123' ), 'Customer tracking identifies the chosen variation and uses a tracking link' );
$order = new class { public function get_status() { return 'processing'; } public function get_date_created() { return new class { public function date( $format ) { return '2026-11-03'; } }; } };
$entry = array( 'order' => $order, 'seller_status' => 'on-hold', 'needs_shipment' => false, 'tracking' => 'TRACK' );
verify_enhancement( 'on-hold' === Airsoft_Product_Manager_Reseller_Orders::entry_status( $entry ) && 'On hold' === Airsoft_Product_Manager_Reseller_Orders::entry_status_label( $entry ), 'Seller status takes precedence for badge and CSV label' );
verify_enhancement( Airsoft_Product_Manager_Reseller_Orders::matches_filters( $entry, array( 'search' => '', 'status' => 'on-hold', 'after' => '', 'before' => '' ) ) && ! Airsoft_Product_Manager_Reseller_Orders::matches_filters( $entry, array( 'search' => '', 'status' => 'shipped', 'after' => '', 'before' => '' ) ), 'Status filters match the displayed status' );
$export_entry['order'] = new TestShopOrder( $item ); $export_entry['seller_status'] = 'on-hold'; $export_entry['tracking'] = 'TRACK';
verify_enhancement( 'On hold' === Airsoft_Product_Manager_Reseller_Orders::export_row( $export_entry )[8], 'CSV status label agrees with the badge and filter rule' );
$entry['seller_status'] = 'completed';
verify_enhancement( 'completed' === Airsoft_Product_Manager_Reseller_Orders::entry_status( $entry ) && 'Completed' === Airsoft_Product_Manager_Reseller_Orders::entry_status_label( $entry ), 'Completed seller state takes precedence over tracking' );
$entry['seller_status'] = ''; $entry['needs_shipment'] = true;
verify_enhancement( 'to-ship' === Airsoft_Product_Manager_Reseller_Orders::entry_status( $entry ), 'Shipment task status is consistent' );
$entry['needs_shipment'] = false;
verify_enhancement( 'shipped' === Airsoft_Product_Manager_Reseller_Orders::entry_status( $entry ), 'Tracking status is consistent when seller state is absent' );
verify_enhancement( "'=2+2" === Airsoft_Product_Manager_Reseller_Orders::csv_value( '=2+2' ) && 'safe' === Airsoft_Product_Manager_Reseller_Orders::csv_value( 'safe' ), 'CSV formula values are escaped' );
$normalized = Airsoft_Product_Manager_Reseller_Orders::normalize_filters( array( 'search' => array( 'bad' ), 'status' => array( 'bad' ), 'after' => "2026-11-03\\'", 'before' => '' ) );
verify_enhancement( '' === $normalized['search'] && '' === $normalized['status'] && "2026-11-03'" === $normalized['after'], 'CSV and listing filters normalize slashes and reject array values' );
$apostrophe_item = new TestLineItem( 778, 77 ); $apostrophe_item->name = "Bob's replica";
$apostrophe_filter = Airsoft_Product_Manager_Reseller_Orders::normalize_filters( array( 'search' => "Bob\\'s" ) );
$apostrophe_entry = array( 'order' => new TestShopOrder( $apostrophe_item ), 'item' => $apostrophe_item, 'seller_status' => '', 'needs_shipment' => false, 'tracking' => '' );
verify_enhancement( "Bob's" === $apostrophe_filter['search'] && Airsoft_Product_Manager_Reseller_Orders::matches_filters( $apostrophe_entry, $apostrophe_filter ), 'Apostrophe search normalizes identically for dashboard and CSV' );

// Open return transitions hold payout, retain the approval audit, and can be resolved.
$return_item = new TestLineItem( 901, 77 ); $return_order = new TestShopOrder( $return_item ); $orders[900] = $return_order;
$_POST = array( 'order_id' => 900, 'item_id' => 901, 'return_reason' => 'Item is damaged' );
Airsoft_Product_Manager_Reseller_Orders::request_return();
verify_enhancement( Airsoft_Product_Manager_Reseller_Orders::payout_blocked_by_return( 901 ), 'Requested return blocks payout' );
try { Airsoft_Product_Manager_Reseller_Orders::request_return(); verify_enhancement( false, 'Duplicate open return accepted' ); } catch ( Exception $e ) { verify_enhancement( false !== strpos( $e->getMessage(), 'already open' ), 'Atomic return guard blocks a second open request' ); }
verify_enhancement( 'approved' === Airsoft_Product_Manager_Reseller_Orders::transition_return( 1, 'approved', 'Review started', 9 ) && Airsoft_Product_Manager_Reseller_Orders::payout_blocked_by_return( 901 ), 'Approved return remains on payout hold' );
verify_enhancement( 'resolved' === Airsoft_Product_Manager_Reseller_Orders::transition_return( 1, 'completed', 'Refund reviewed', 10 ) && ! Airsoft_Product_Manager_Reseller_Orders::payout_blocked_by_return( 901 ), 'Resolved return removes hold' );
verify_enhancement( 'Review started' === $wpdb->returns[0]['decision_note'] && 'Refund reviewed' === $wpdb->returns[0]['resolution_note'] && 9 === $wpdb->returns[0]['admin_id'] && 10 === $wpdb->returns[0]['resolved_by'], 'Resolution preserves approval decision and reviewer audit' );
$rejected_item = new TestLineItem( 905, 77 ); $orders[900] = new TestShopOrder( $rejected_item );
$_POST = array( 'order_id' => 900, 'item_id' => 905, 'return_reason' => 'Wrong item' );
Airsoft_Product_Manager_Reseller_Orders::request_return();
verify_enhancement( 'rejected' === Airsoft_Product_Manager_Reseller_Orders::transition_return( 2, 'rejected', 'Not eligible', 9 ) && ! Airsoft_Product_Manager_Reseller_Orders::payout_blocked_by_return( 905 ), 'Rejected return removes payout hold' );
$_POST = array( 'order_id' => 900, 'item_id' => 777, 'return_reason' => 'Variation damaged' );
$orders[900] = new TestShopOrder( $item );
Airsoft_Product_Manager_Reseller_Orders::request_return();
verify_enhancement( false === strpos( end( $orders[900]->notes ), 'item 777' ) && false !== strpos( end( $orders[900]->notes ), 'Replica — Size: Large' ), 'Return note identifies the selected variation' );

// Paid or credited items cannot start return requests because there is no clawback.
$paid_item = new TestLineItem( 902, 77 ); $orders[900] = new TestShopOrder( $paid_item );
$wpdb->payouts[902] = (object) array( 'item_id' => 902 ); $_POST = array( 'order_id' => 900, 'item_id' => 902, 'return_reason' => 'Return' );
try { Airsoft_Product_Manager_Reseller_Orders::request_return(); verify_enhancement( false, 'Paid item return request accepted' ); } catch ( Exception $e ) { verify_enhancement( false !== strpos( $e->getMessage(), 'already been paid' ), 'Paid item return request rejected clearly' ); }
$credited_item = new TestLineItem( 903, 77 ); $orders[900] = new TestShopOrder( $credited_item ); $wpdb->credits[903] = true; $_POST['item_id'] = 903;
try { Airsoft_Product_Manager_Reseller_Orders::request_return(); verify_enhancement( false, 'Wallet credited item return request accepted' ); } catch ( Exception $e ) { verify_enhancement( false !== strpos( $e->getMessage(), 'already been paid' ), 'Wallet credited item return request rejected clearly' ); }

// Open queue state filtering finds an old open item beyond 150 newer closed rows.
$wpdb->returns = array();
for ( $i = 1; $i <= 150; $i++ ) { $wpdb->returns[] = array( 'id' => $i, 'order_id' => 900, 'item_id' => 1000 + $i, 'reseller_id' => 77, 'reason' => '', 'state' => 'resolved', 'created_at' => '', 'admin_id' => 9, 'decision_note' => '', 'resolution_note' => '', 'resolved_by' => 9 ); }
$wpdb->returns[] = array( 'id' => 151, 'order_id' => 900, 'item_id' => 2000, 'reseller_id' => 77, 'reason' => '', 'state' => 'requested', 'created_at' => '', 'admin_id' => 0, 'decision_note' => '', 'resolution_note' => '', 'resolved_by' => 0 );
$open = Airsoft_Product_Manager_Reseller_Orders::return_requests( 0, 25, 1, array( 'requested', 'approved' ) );
verify_enhancement( 1 === count( $open ) && 151 === $open[0]->id && 1 === Airsoft_Product_Manager_Reseller_Orders::return_request_count( array( 'requested', 'approved' ) ), 'Open return queue remains paginated separately from closed history' );
verify_enhancement( 151 === Airsoft_Product_Manager_Reseller_Orders::return_requests_for_item( 77, 2000 )[0]->id, 'Reseller item view finds its old open return' );

// Seller item shipments create separate, correctly worded customer notifications.
$orders[900] = $return_order;
Airsoft_Product_Manager_Notifications::shipped( 900, 'reseller-item-901' );
$return_order->item = new TestLineItem( 904, 77 );
Airsoft_Product_Manager_Notifications::shipped( 900, 'reseller-item-904' );
Airsoft_Product_Manager_Notifications::shipped( 900, 'reseller-item-901' );
$notices = Airsoft_Product_Manager_Notifications::items( 88 );
verify_enhancement( 2 === count( $notices ), 'Shipment alerts deduplicate per item and retain both seller shipments' );
foreach ( $notices as $notice ) { verify_enhancement( false !== strpos( $notice['message'], 'Part of order #900 has shipped' ), 'Mixed-seller shipment notification does not claim the entire order shipped' ); }

echo "PASS: $checks reseller enhancement checks\n";
