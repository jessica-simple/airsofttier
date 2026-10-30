<?php
/** Dashboard data, isolation and rendering checks. No WordPress server required. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$current = 7; $orders = array(); $products = array(); $payouts = array(); $queries = array();
function get_current_user_id() { global $current; return $current; }
function is_user_logged_in() { return get_current_user_id() > 0; }
function get_userdata( $id ) { return $id ? (object) array( 'roles' => array( 8 === $id ? 'customer' : 'airsoft_reseller' ) ) : false; }
function get_user_meta( $id, $key, $single = true ) { return '_airsoft_reseller_business' === $key ? 'My reseller store' : ''; }
function get_option( $key, $default = false ) { return $default; }
function get_bloginfo( $key ) { return 'Store'; }
function home_url( $path ) { return 'https://example.test' . $path; }
function absint( $v ) { return abs( (int) $v ); }
function sanitize_key( $v ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $v ) ); }
function sanitize_text_field( $v ) { return trim( strip_tags( $v ) ); }
function wp_unslash( $v ) { return stripslashes( $v ); }
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES ); }
function esc_attr( $v ) { return esc_html( $v ); }
function esc_textarea( $v ) { return esc_html( $v ); }
function esc_url( $v ) { return esc_html( $v ); }
function wp_kses_post( $v ) { return $v; }
function wc_get_price_decimals() { return 2; }
function get_woocommerce_currency() { return 'USD'; }
function wc_price( $amount, $args = array() ) { return ( $args['currency'] ?? 'USD' ) . ' ' . number_format( (float) $amount, 2, '.', '' ); }
function wc_get_order_status_name( $status ) { return ucfirst( $status ); }
function wc_get_order( $id ) { global $orders; return $orders[$id] ?? false; }
function wc_get_product( $id ) { global $products; return $products[$id] ?? false; }
function wc_print_notices() {}
function wc_add_notice( $message, $type = '' ) {}
function wp_nonce_field( $action, $name ) { echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="test">'; }
function wp_logout_url( $url ) { return 'https://example.test/logout?redirect=' . urlencode( $url ); }
function add_query_arg( $key, $value, $url = null ) {
 if ( is_array( $key ) ) { $args = $key; $url = $value; } else { $args = array( $key => $value ); }
 $query = array(); parse_str( parse_url( $url, PHP_URL_QUERY ) ?? '', $query );
 return explode( '?', $url )[0] . '?' . http_build_query( array_merge( $query, $args ) );
}
function wc_get_products( $args ) {
 global $products, $queries; $queries[] = $args;
 $matches = array_filter( $products, function ( $product ) use ( $args ) { return (int) $product->get_meta( '_airsoft_reseller_owner' ) === (int) $args['meta_value'] && in_array( $product->get_status(), (array) $args['status'], true ); } );
 return (object) array( 'products' => array_slice( array_values( $matches ), ( ( $args['page'] ?? 1 ) - 1 ) * $args['limit'], $args['limit'] ), 'total' => count( $matches ), 'max_num_pages' => (int) ceil( count( $matches ) / $args['limit'] ) );
}
function wc_get_orders( $args ) {
 global $orders, $queries; $queries[] = $args;
 return (object) array( 'orders' => array_values( array_filter( $orders, function ( $order ) use ( $args ) { return $order->customer === $args['customer_id']; } ) ), 'max_num_pages' => 1 );
}
class WC_Shortcode_My_Account {
 public static function view_order( $id ) { echo '<h3>Purchase details #' . $id . '</h3>'; }
 public static function edit_account() { echo '<form class="woocommerce-EditAccountForm"><label>Display name<input name="account_display_name" value="My store"></label><button>Save changes</button></form>'; }
 public static function edit_address( $type ) { echo '<form><h3>' . esc_html( ucfirst( $type ) ) . ' address</h3><label>Street address<input name="' . esc_attr( $type ) . '_address_1"></label><button>Save address</button></form>'; }
}
class DashboardProduct {
 public $id, $owner, $name, $status, $physical = true;
 public function __construct( $id, $owner, $name, $status = 'publish' ) { $this->id = $id; $this->owner = $owner; $this->name = $name; $this->status = $status; }
 public function get_meta( $key ) { return '_airsoft_reseller_owner' === $key ? $this->owner : ( '_airsoft_reseller_price' === $key ? '80' : '' ); }
 public function needs_shipping() { return $this->physical; }
 public function get_id() { return $this->id; }
 public function get_name() { return $this->name; }
 public function get_status() { return $this->status; }
 public function get_regular_price( $context = '' ) { return '100'; }
 public function get_description() { return 'A product description.'; }
 public function get_image( $size ) { return '<img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==">'; }
 public function get_manage_stock() { return true; }
 public function get_stock_quantity() { return 12; }
 public function is_type( $type ) { return 'simple' === $type; }
}
class DashboardItem {
 public $id, $owner, $name, $total, $order_id, $tracking = '', $physical = true;
 public function __construct( $id, $owner, $total, $name = 'Own product' ) { $this->id = $id; $this->owner = $owner; $this->total = $total; $this->name = $name; }
 public function get_id() { return $this->id; }
 public function get_order_id() { return $this->order_id; }
 public function update_meta_data( $key, $value ) { if ( '_airsoft_tracking' === $key ) { $this->tracking = $value; } }
 public function save() {}
 public function get_meta( $key ) { return array( '_airsoft_reseller_owner' => $this->owner, '_airsoft_commission_rate' => 10, '_airsoft_tracking' => $this->tracking )[$key] ?? ''; }
 public function get_total() { return $this->total; }
 public function get_name() { return $this->name; }
 public function get_quantity() { return 1; }
 public function get_product() { $p = new DashboardProduct( 1, $this->owner, $this->name ); $p->physical = $this->physical; return $p; }
}
class DashboardOrder {
 public $id, $status, $currency, $items, $refunded = array(), $refunded_qty = array(), $hold = false, $customer = 0, $notes = array();
 public function __construct( $id, $status, $currency, $items ) { $this->id = $id; $this->status = $status; $this->currency = $currency; $this->items = $items; foreach ( $items as $item ) { $item->order_id = $id; } }
 public function get_id() { return $this->id; }
 public function get_type() { return 'shop_order'; }
 public function get_status() { return $this->status; }
 public function has_status( $statuses ) { return in_array( $this->status, (array) $statuses, true ); }
 public function is_paid() { return in_array( $this->status, array( 'processing', 'completed' ), true ); }
 public function get_currency() { return $this->currency; }
 public function get_item( $id ) { global $orders; foreach ( $orders as $order ) { if ( isset( $order->items[$id] ) ) { return $order->items[$id]; } } return false; }
 public function add_order_note( $note, $customer ) { $this->notes[] = $note; }
 public function get_items() { return $this->items; }
 public function get_order_number() { return (string) $this->id; }
 public function get_total_refunded_for_item( $id ) { return $this->refunded[$id] ?? 0; }
 public function get_qty_refunded_for_item( $id ) { return $this->refunded_qty[$id] ?? 0; }
 public function get_refunds() { return $this->hold ? array( new class { public function get_amount() { return 10; } public function get_items( $types ) { return array(); } } ) : array(); }
 public function get_formatted_shipping_address() { return 'PRIVATE ADDRESS ' . $this->id; }
 public function get_customer_id() { return $this->customer; }
 public function get_formatted_order_total() { return $this->currency . ' 100.00'; }
}
class DashboardDB {
 public $prefix = 'wp_', $calls = array();
 public function prepare( $sql, ...$args ) {
  foreach ( $args as $arg ) { $sql = preg_replace_callback( '/%[ds]/', function ( $m ) use ( $arg ) { return '%d' === $m[0] ? (string) (int) $arg : "'" . str_replace( "'", "''", $arg ) . "'"; }, $sql, 1 ); }
  return $sql;
 }
 public function get_row( $sql ) { global $payouts; preg_match( '/item_id = (\d+)/', $sql, $match ); return $payouts[(int) $match[1]] ?? false; }
 public function get_results( $sql ) {
  global $orders, $payouts;
  $this->calls[] = $sql; $rows = array();
  preg_match( "/m.meta_value = '(\d+)'/", $sql, $owner );
  preg_match( '/i.order_item_id < (\d+)/', $sql, $cursor );
  foreach ( $orders as $order ) { foreach ( $order->items as $item ) {
   if ( isset( $owner[1] ) && $item->owner !== (int) $owner[1] ) { continue; }
   if ( isset( $cursor[1] ) && $item->id >= (int) $cursor[1] ) { continue; }
   $paid = $payouts[$item->id] ?? null;
   $rows[] = (object) array( 'order_id' => $order->id, 'order_item_id' => $item->id, 'payout_item_id' => $paid ? $item->id : null, 'payout_amount' => $paid->amount ?? null, 'payout_currency' => $paid->currency ?? null, 'payout_reference' => $paid->reference ?? null, 'payout_date' => $paid->paid_at ?? null );
  } }
  usort( $rows, function ( $a, $b ) { return $b->order_item_id <=> $a->order_item_id; } );
  if ( isset( $cursor[1] ) ) { return array_slice( $rows, 0, 200 ); }
  preg_match( '/OFFSET (\d+)/', $sql, $offset );
  return array_slice( $rows, (int) ( $offset[1] ?? 0 ), 21 );
 }
}
$wpdb = new DashboardDB();
require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-products.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-orders.php';
$products[1] = new DashboardProduct( 1, 7, 'Range carry bag' );
$products[2] = new DashboardProduct( 2, 7, 'Training gloves', 'pending' );
$products[3] = new DashboardProduct( 3, 7, 'Draft product', 'draft' );
$products[9] = new DashboardProduct( 9, 9, 'OTHER SELLER PRIVATE PRODUCT' );
$a = new DashboardItem( 101, 7, 100, 'Range carry bag' );
$b = new DashboardItem( 102, 7, 50, 'Training gloves' ); $b->tracking = 'TRACK123';
$other = new DashboardItem( 103, 9, 999, 'OTHER SELLER PRIVATE ITEM' );
$orders[10] = new DashboardOrder( 10, 'processing', 'USD', array( 101 => $a, 102 => $b, 103 => $other ) );
$orders[20] = new DashboardOrder( 20, 'completed', 'USD', array( 201 => new DashboardItem( 201, 7, 200 ) ) ); $orders[20]->refunded[201] = -50;
$orders[30] = new DashboardOrder( 30, 'completed', 'HKD', array( 301 => new DashboardItem( 301, 7, 300 ) ) ); $orders[30]->refunded[301] = -30;
$payouts[301] = (object) array( 'amount' => 270, 'currency' => 'HKD', 'reference' => 'PAID123', 'paid_at' => '2026-10-29 00:00:00' );
$orders[40] = new DashboardOrder( 40, 'completed', 'USD', array( 401 => new DashboardItem( 401, 7, 80 ) ) ); $orders[40]->hold = true;
$orders[50] = new DashboardOrder( 50, 'cancelled', 'USD', array( 501 => new DashboardItem( 501, 7, 60 ) ) );
$virtual = new DashboardItem( 601, 7, 20 ); $virtual->physical = false;
$orders[60] = new DashboardOrder( 60, 'processing', 'USD', array( 601 => $virtual ) );
$orders[70] = new DashboardOrder( 70, 'processing', 'USD', array( 701 => new DashboardItem( 701, 7, 90 ) ) ); $orders[70]->refunded[701] = -90; $orders[70]->refunded_qty[701] = -1;
$orders[90] = new DashboardOrder( 90, 'processing', 'USD', array( 901 => new DashboardItem( 901, 9, 100 ) ) );
$checks = 0;
function verify( $pass, $label ) { global $checks; if ( ! $pass ) { throw new Exception( 'FAIL: ' . $label ); } $checks++; }
function portal_html( $get ) { $_GET = $get; $GLOBALS['_GET'] = $get; return Airsoft_Product_Manager_Reseller_Portal::portal(); }
$summary = Airsoft_Product_Manager_Reseller_Orders::portal_summary();
verify( 1 === $summary['to_ship'], 'Shipment count excludes tracked, virtual, cancelled, refunded and other seller items' );
verify( 1 === count( $summary['attention'] ) && 101 === $summary['attention'][0]['item']->id, 'Attention list contains only own shippable outstanding items' );
$b->tracking = '';
verify( 1 === Airsoft_Product_Manager_Reseller_Orders::portal_summary()['to_ship'], 'Two outstanding items in one order count as one order to ship' );
$b->tracking = 'TRACK123';
verify( 135.0 === $summary['eligible']['USD'], 'Eligible earnings include proportional item refund and commission' );
verify( 225.0 === $summary['pending']['USD'], 'Refund holds and in-progress earnings remain pending' );
verify( 270.0 === $summary['paid']['HKD'] && ! isset( $summary['paid']['USD'] ), 'Recorded payouts stay separated by currency' );
verify( -27.0 === $summary['adjustments']['HKD'], 'Refund after payout remains visible as adjustment' );
$orders[30]->currency = 'USD';
$currency_summary = Airsoft_Product_Manager_Reseller_Orders::portal_summary();
verify( 1 === $currency_summary['currency_review'] && empty( $currency_summary['adjustments'] ), 'Changed order currency triggers review instead of subtracting different currencies' );
$orders[30]->currency = 'HKD';
$own = Airsoft_Product_Manager_Reseller_Orders::portal_order( 10 );
verify( 2 === count( $own ) && 101 === $own[0]['item']->id, 'Mixed order exposes only the signed-in seller items' );
verify( array() === Airsoft_Product_Manager_Reseller_Orders::portal_order( 90 ), 'Other seller order cannot be opened by ID' );
$counts = Airsoft_Product_Manager_Reseller_Products::counts();
verify( array( 'publish' => 1, 'pending' => 1, 'draft' => 1 ) === $counts, 'Product counts include only owned listings' );
$html = portal_html( array() );
verify( false !== strpos( $html, '<h2>Overview</h2>' ) && false !== strpos( $html, 'Selling' ) && false !== strpos( $html, 'Buying' ), 'Default portal renders overview and separate selling/buying groups' );
verify( false !== strpos( $html, 'USD 135.00' ) && false !== strpos( $html, 'Ship order' ), 'Overview uses actual eligible earnings and visible shipping action' );
verify( false === strpos( $html, 'OTHER SELLER' ) && false === strpos( $html, 'PRIVATE ADDRESS' ), 'Overview leaks no other seller data or customer address' );
$previews = array( 'overview' => $html );
$html = portal_html( array( 'section' => 'products', 'seller_id' => 9 ) );
verify( false !== strpos( $html, 'Range carry bag' ) && false === strpos( $html, 'OTHER SELLER' ), 'Product listing ignores forged seller ID' );
verify( false !== strpos( $html, 'section=product-edit' ) && false === strpos( $html, 'enctype=' ), 'Listing links to dedicated product editor' );
$previews['products'] = $html;
$html = portal_html( array( 'section' => 'product-edit', 'edit_product' => 9 ) );
verify( false !== strpos( $html, 'cannot be edited' ) && false === strpos( $html, 'enctype=' ), 'Cross-seller product editor blocked' );
$html = portal_html( array( 'section' => 'product-edit' ) );
verify( false !== strpos( $html, 'enctype="multipart/form-data"' ) && false !== strpos( $html, 'value="save_product"' ) && false !== strpos( $html, 'airsoft_nonce' ), 'Product submission retains upload, action and nonce' );
$previews['product-edit'] = $html;
$html = portal_html( array( 'edit_product' => 1 ) );
verify( false !== strpos( $html, '<h2>Edit product</h2>' ), 'Legacy product edit link still opens editor' );
$html = portal_html( array( 'section' => 'sales', 'order_id' => 90 ) );
verify( false !== strpos( $html, 'order is unavailable' ) && false === strpos( $html, 'PRIVATE ADDRESS' ), 'Forged customer order ID reveals no address' );
$html = portal_html( array( 'section' => 'sales', 'order_id' => 10 ) );
verify( false !== strpos( $html, 'PRIVATE ADDRESS 10' ) && false !== strpos( $html, 'value="ship"' ) && false === strpos( $html, 'OTHER SELLER' ), 'Owned shipment form contains only own items and authorized delivery address' );
$previews['sales'] = $html;
$_POST = array( 'order_id' => 90, 'item_id' => 101, 'tracking' => 'TRACK NEW' );
try { Airsoft_Product_Manager_Reseller_Orders::ship(); verify( false, 'Mismatched item/order accepted' ); }
catch ( Exception $e ) { verify( 'You cannot update this order.' === $e->getMessage() && empty( $orders[90]->notes ), 'Forged order ID cannot attach own-item shipment to another customer order' ); }
$_POST['order_id'] = 10;
Airsoft_Product_Manager_Reseller_Orders::ship();
verify( 'TRACK NEW' === $a->tracking && 1 === count( $orders[10]->notes ), 'Shipment saves tracking and customer note on the correct order' );
$a->tracking = '';
$html = portal_html( array( 'section' => 'earnings' ) );
verify( false !== strpos( $html, 'HKD -27.00' ) && false !== strpos( $html, 'PAID123' ) && false !== strpos( $html, 'Refund review' ), 'Earnings retains paid references, refund holds and adjustments' );
$previews['earnings'] = $html;
$html = portal_html( array( 'section' => 'shipping' ) );
verify( false !== strpos( $html, 'shipping_address_1' ) && false !== strpos( $html, 'aria-current="page"' ), 'Address editor and active navigation retained' );
$previews['shipping'] = $html;
$orders[91] = new DashboardOrder( 91, 'completed', 'USD', array() ); $orders[91]->customer = 7;
$orders[92] = new DashboardOrder( 92, 'completed', 'USD', array() ); $orders[92]->customer = 9;
$html = portal_html( array( 'section' => 'purchases' ) );
verify( false !== strpos( $html, '#91' ) && false === strpos( $html, '#92' ), 'Purchases are restricted to current customer ID' );
$previews['purchases'] = $html;
$html = portal_html( array( 'section' => 'purchases', 'view_order' => 92 ) );
verify( false !== strpos( $html, 'purchase is unavailable' ), 'Other customer purchase detail blocked' );
$current = 8;
verify( array() === Airsoft_Product_Manager_Reseller_Orders::portal_page()['items'] && 0 === Airsoft_Product_Manager_Reseller_Orders::portal_summary()['to_ship'], 'Member cannot access dashboard sales data' );
$html = portal_html( array() );
verify( false === strpos( $html, 'airsoft-stats' ) && false !== strpos( $html, 'member account' ), 'Member never gets approved reseller dashboard' );
$current = 7;
for ( $i = 1000; $i < 1201; $i++ ) { $orders[$i] = new DashboardOrder( $i, 'completed', 'USD', array( $i => new DashboardItem( $i, 7, 1 ) ) ); }
$summary = Airsoft_Product_Manager_Reseller_Orders::portal_summary();
verify( abs( $summary['eligible']['USD'] - 315.9 ) < .00001, 'Summary includes all keyset batches, beyond first 200 records' );
$page = Airsoft_Product_Manager_Reseller_Orders::portal_page();
verify( 20 === count( $page['items'] ) && $page['more'], 'Customer-order table remains paginated' );
if ( ! empty( $argv[1] ) ) {
 $css = 'file:///' . str_replace( array( '\\', ' ' ), array( '/', '%20' ), dirname( __DIR__ ) . '/assets/css/reseller.css' );
 foreach ( $previews as $section => $preview ) {
  $path = 'overview' === $section ? $argv[1] : preg_replace( '/\.html$/', '-' . $section . '.html', $argv[1] );
  file_put_contents( $path, '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="' . esc_attr( $css ) . '"><style>body{background:#f5f6f9;font-family:Arial,sans-serif;margin:24px}a{color:inherit}.dashicons{display:inline-block}</style></head><body>' . $preview . '</body></html>' );
 }
}
echo "PASS: $checks reseller dashboard checks\n";
