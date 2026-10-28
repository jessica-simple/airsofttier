<?php
/** Standalone domain regression checks. Run: php -n tests/reseller-regression.php */
define( 'ABSPATH', __DIR__ . '/' );
$test_user = 7;
$test_roles = array( 7 => array( 'airsoft_reseller' ), 8 => array( 'customer' ), 9 => array( 'airsoft_reseller' ) );
$test_products = array();
function get_current_user_id() { global $test_user; return $test_user; }
function get_userdata( $id ) { global $test_roles; return isset( $test_roles[ $id ] ) ? (object) array( 'roles' => $test_roles[ $id ] ) : false; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function absint( $v ) { return abs( (int) $v ); }
function wc_get_price_decimals() { return 2; }
function get_option( $key, $default = false ) { return 'airsoft_reseller_commission' === $key ? 10 : $default; }
function wc_get_product( $id ) { global $test_products; return isset( $test_products[ $id ] ) ? $test_products[ $id ] : false; }
function current_user_can( $cap ) { return false; }
function wp_die( $message ) { throw new Exception( $message ); }

require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-pricing.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-products.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-orders.php';

class TestProduct {
 public $meta;
 public $id;
 public $base;
 public $cart_price;
 public function __construct( $id, $meta, $base = '100' ) { $this->id = $id; $this->meta = $meta; $this->base = $base; }
 public function get_meta( $key, $single = true ) { return $this->meta[ $key ] ?? ''; }
 public function get_id() { return $this->id; }
 public function get_parent_id() { return 0; }
 public function is_type( $type ) { return 'simple' === $type; }
 public function get_price() { return Airsoft_Product_Manager_Reseller_Pricing::price( $this->base, $this ); }
 public function set_price( $v ) { $this->cart_price = $v; }
}
class TestCart {
 public $product;
 public function get_cart() { return array( array( 'data' => $this->product ) ); }
}
class TestItem {
 public $meta = array();
 public $total = 200;
 public $product;
 public function get_meta( $key ) { return $this->meta[ $key ] ?? ''; }
 public function add_meta_data( $key, $value, $unique ) { $this->meta[ $key ] = $value; }
 public function get_product() { return $this->product; }
 public function get_total() { return $this->total; }
 public function get_id() { return 21; }
}
class TestOrder {
 public $refund = 0;
 public $status = 'completed';
 public $refunds = array();
 public function get_total_refunded_for_item( $id ) { return $this->refund; }
 public function has_status( $statuses ) { return in_array( $this->status, (array) $statuses, true ); }
 public function get_refunds() { return $this->refunds; }
}
class TestRefund {
 public $amount;
 public $items;
 public function get_amount() { return $this->amount; }
 public function get_items( $types ) { return $this->items; }
}
class TestRefundLine {
 public function get_total() { return -20; }
 public function get_total_tax() { return -2; }
}
$checks = 0;
function check( $condition, $message ) { global $checks; if ( ! $condition ) { throw new Exception( 'FAIL: ' . $message ); } $checks++; }
function call_private( $method, $args ) { $r = new ReflectionMethod( 'Airsoft_Product_Manager_Reseller_Orders', $method ); $r->setAccessible( true ); return $r->invokeArgs( null, $args ); }

$product = new TestProduct( 1, array( '_airsoft_reseller_price' => '80', '_airsoft_reseller_owner' => 9 ) );
check( '80' === Airsoft_Product_Manager_Reseller_Pricing::price( '100', $product ), 'Reseller discount' );
check( '60' === Airsoft_Product_Manager_Reseller_Pricing::price( '60', $product ), 'Lower group-buy price wins' );
$test_user = 8;
check( '100' === Airsoft_Product_Manager_Reseller_Pricing::price( '100', $product ), 'Member does not receive reseller price' );
$test_user = 7;
$product->meta['_airsoft_reseller_price'] = '';
check( '100' === Airsoft_Product_Manager_Reseller_Pricing::price( '100', $product ), 'Missing reseller price fallback' );
$product->meta['_airsoft_reseller_price'] = '0';
check( '0' === Airsoft_Product_Manager_Reseller_Pricing::price( '100', $product ), 'Zero price is valid' );
$product->meta['_airsoft_reseller_price'] = '80';
check( true === Airsoft_Product_Manager_Reseller_Products::purchasable( true, $product ), 'Approved other reseller listing' );
$test_user = 9;
check( false === Airsoft_Product_Manager_Reseller_Products::purchasable( true, $product ), 'Cannot buy own listing' );
$test_user = 7;
$test_roles[9] = array( 'customer' );
check( false === Airsoft_Product_Manager_Reseller_Products::purchasable( true, $product ), 'Revoked reseller cannot sell' );
$test_products[1] = $product;
$_POST = array( 'product_id' => 1 );
try { Airsoft_Product_Manager_Reseller_Products::save(); check( false, 'Cross-owner edit accepted' ); }
catch ( Exception $e ) { check( 'You cannot edit this product.' === $e->getMessage(), 'Cross-owner edit rejected before mutation' ); }
$cart = new TestCart(); $cart->product = clone $product;
Airsoft_Product_Manager_Reseller_Pricing::cart( $cart );
Airsoft_Product_Manager_Reseller_Pricing::cart( $cart );
check( '80' === $cart->product->cart_price, 'Repeated cart calculation does not stack discounts' );
$test_user = 8;
Airsoft_Product_Manager_Reseller_Pricing::cart( $cart );
check( '100' === $cart->product->cart_price, 'Role change resets cart price' );
$item = new TestItem(); $item->product = $product;
Airsoft_Product_Manager_Reseller_Orders::snapshot( $item );
check( 9 === $item->meta['_airsoft_reseller_owner'] && 10.0 === $item->meta['_airsoft_commission_rate'], 'Owner and commission snapshot' );
$product->meta['_airsoft_reseller_owner'] = 7;
Airsoft_Product_Manager_Reseller_Orders::snapshot( $item );
check( 9 === $item->meta['_airsoft_reseller_owner'], 'Order ownership snapshot is immutable' );
$order = new TestOrder();
check( 180.0 === call_private( 'earning', array( $order, $item ) ), 'Commission on item total' );
$order->refund = -50;
check( 135.0 === call_private( 'earning', array( $order, $item ) ), 'Partial refund reverses proportional earnings and commission' );
$order->status = 'cancelled';
check( 0.0 === call_private( 'earning', array( $order, $item ) ), 'Cancelled order has no earnings' );
$order->status = 'completed'; $order->refund = -300;
check( 0.0 === call_private( 'earning', array( $order, $item ) ), 'Refunds cannot create negative gross earnings' );
$refund = new TestRefund(); $refund->amount = 22; $refund->items = array( new TestRefundLine() ); $order->refunds = array( $refund );
check( false === call_private( 'needs_reconciliation', array( $order ) ), 'Allocated refund is allowed' );
$refund->amount = 30;
check( true === call_private( 'needs_reconciliation', array( $order ) ), 'Unallocated refund blocks payout' );
try { Airsoft_Product_Manager_Reseller_Orders::payout(); check( false, 'Member payout accepted' ); }
catch ( Exception $e ) { check( 'Access denied.' === $e->getMessage(), 'Member cannot record payout' ); }
echo "PASS: $checks reseller regression checks\n";
