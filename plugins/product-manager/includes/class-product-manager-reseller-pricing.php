<?php
/** Role-aware pricing; runs after the existing group-buy price filter. */
defined( 'ABSPATH' ) || exit;
class Airsoft_Product_Manager_Reseller_Pricing {
 public static function init() {
  add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'field' ) );
  add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
  add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'variation_field' ), 10, 3 );
  add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation' ), 10, 2 );
  add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'price' ), 30, 2 );
  add_filter( 'woocommerce_product_variation_get_price', array( __CLASS__, 'price' ), 30, 2 );
  add_filter( 'woocommerce_variation_prices_price', array( __CLASS__, 'variation_price' ), 30, 2 );
  add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'hash' ), 30 );
  add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'html' ), 30, 2 );
  add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'cart' ), 30 );
 }
 public static function field() {
  woocommerce_wp_text_input( array( 'id' => '_airsoft_reseller_price', 'label' => 'Reseller price', 'data_type' => 'price', 'description' => 'Optional. Approved resellers receive the lower eligible price.' ) );
 }
 public static function save( $product ) {
  if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['_airsoft_reseller_price'] ) ) { return; }
  $price = wc_format_decimal( wp_unslash( $_POST['_airsoft_reseller_price'] ) );
  if ( '' !== $price && ( ! is_numeric( $price ) || (float) $price < 0 ) ) { return; }
  $product->update_meta_data( '_airsoft_reseller_price', $price );
 }
 public static function variation_field( $loop, $data, $variation ) {
  woocommerce_wp_text_input( array( 'id' => 'airsoft_reseller_price_' . $loop, 'name' => 'airsoft_reseller_price[' . $loop . ']', 'value' => get_post_meta( $variation->ID, '_airsoft_reseller_price', true ), 'label' => 'Reseller price', 'data_type' => 'price', 'wrapper_class' => 'form-row form-row-full' ) );
 }
 public static function save_variation( $id, $loop ) {
  if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['airsoft_reseller_price'][ $loop ] ) ) { return; }
  $price = wc_format_decimal( wp_unslash( $_POST['airsoft_reseller_price'][ $loop ] ) );
  if ( '' !== $price && ( ! is_numeric( $price ) || (float) $price < 0 ) ) { return; }
  $product = wc_get_product( $id );
  if ( $product ) { $product->update_meta_data( '_airsoft_reseller_price', $price ); $product->save(); }
 }
 public static function price( $price, $product ) {
  if ( ( is_admin() && ! wp_doing_ajax() ) || ! Airsoft_Product_Manager_Resellers::approved() || '' === $price ) { return $price; }
  $special = $product->get_meta( '_airsoft_reseller_price', true );
  if ( '' === $special || ! is_numeric( $special ) || (float) $special < 0 ) { return $price; }
  return (string) min( (float) $price, (float) $special );
 }
 public static function variation_price( $price, $variation ) { return $variation->get_price(); }
 public static function hash( $hash ) {
  $hash['airsoft_reseller'] = Airsoft_Product_Manager_Resellers::approved() ? 'yes' : 'no';
  // Existing group-buy deadlines can change prices without a product edit.
  $hash['airsoft_price_minute'] = (int) floor( time() / 60 );
  return $hash;
 }
 public static function html( $html, $product ) {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { return $html; }
  if ( $product->is_type( 'variable' ) ) {
   $min = $product->get_variation_price( 'min', true );
   $max = $product->get_variation_price( 'max', true );
   if ( '' === $min ) { return $html; }
   return ( $min !== $max ? wc_format_price_range( $min, $max ) : wc_price( $min ) ) . $product->get_price_suffix();
  }
  if ( '' === $product->get_price() ) { return $html; }
  return wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
 }
 public static function cart( $cart ) {
  if ( is_admin() && ! wp_doing_ajax() ) { return; }
  foreach ( $cart->get_cart() as $item ) {
   // Reload the saved base price: never use a previously discounted cart object.
   $product = wc_get_product( $item['data']->get_id() );
   if ( $product ) { $item['data']->set_price( $product->get_price() ); }
  }
 }
}
