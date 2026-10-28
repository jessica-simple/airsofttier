<?php
/** Shared WooCommerce sender settings, also used by custom store messages. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Emails {


 public static function init() {
  add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
  add_action( 'admin_post_airsoft_reseller_email_settings', array( __CLASS__, 'save' ) );
 }

 public static function menu() {
  add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'WooCommerce Emails', 'WooCommerce Emails', 'manage_woocommerce', 'airsoft-reseller-emails', array( __CLASS__, 'page' ) );
 }

 public static function settings() {
  return array(
   'name' => get_option( 'woocommerce_email_from_name', get_bloginfo( 'name' ) ),
   'email' => get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) ),
  );
 }

 public static function page() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $settings = self::settings();
  echo '<div class="wrap"><h1>WooCommerce Emails</h1>';
  if ( isset( $_GET['saved'] ) ) { echo '<div class="notice notice-success"><p>WooCommerce email sender settings saved.</p></div>'; }
  echo '<p>Set the sender name and email for WooCommerce order, payment, customer and reseller messages. These are the same sender settings used under WooCommerce → Settings → Emails. Unrelated WordPress emails keep their existing sender.</p>';
  echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_email_settings">';
  wp_nonce_field( 'airsoft_reseller_email_settings' );
  echo '<table class="form-table"><tr><th><label for="airsoft-sender-name">Sender name</label></th><td><input class="regular-text" id="airsoft-sender-name" name="sender_name" type="text" maxlength="150" value="' . esc_attr( $settings['name'] ?? '' ) . '"></td></tr>';
  echo '<tr><th><label for="airsoft-sender-email">Sender email</label></th><td><input class="regular-text" id="airsoft-sender-email" name="sender_email" type="email" value="' . esc_attr( $settings['email'] ?? '' ) . '"><p class="description">Use an address authorised by your email provider. A sender email is required when setting a custom sender name. SMTP plugins with a forced sender can override these settings.</p></td></tr></table>';
  submit_button( 'Save WooCommerce email settings' );
  echo '</form></div>';
 }

 public static function validate( $name, $email ) {
  if ( ! is_string( $name ) || ! is_string( $email ) || preg_match( '/[\r\n]/', $name . $email ) ) { return new WP_Error( 'sender', 'Enter a valid sender name and email without line breaks.' ); }
  $name = sanitize_text_field( $name );
  $email = trim( $email );
  if ( strlen( $name ) > 150 || ( '' !== $email && ! is_email( $email ) ) || ( '' !== $name && '' === $email ) ) { return new WP_Error( 'sender', 'Enter a valid sender email with your custom sender name, or leave both fields blank.' ); }
  return array( 'name' => $name, 'email' => $email );
 }

 public static function save() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_reseller_email_settings' );
  $settings = self::validate( wp_unslash( $_POST['sender_name'] ?? '' ), wp_unslash( $_POST['sender_email'] ?? '' ) );
  if ( is_wp_error( $settings ) ) { wp_die( esc_html( $settings->get_error_message() ) ); }
  update_option( 'woocommerce_email_from_name', $settings['name'], false );
  update_option( 'woocommerce_email_from_address', $settings['email'], false );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-emails&saved=1' ) ); exit;
 }

 public static function send( $to, $subject, $message ) {
  $settings = self::settings();
  $valid = self::validate( $settings['name'] ?? '', $settings['email'] ?? '' );
  $headers = array();
  if ( ! is_wp_error( $valid ) && '' !== $valid['email'] ) {
   // Strip address delimiters from the display name so it cannot become a
   // second mailbox/header. Pass the sender only on this individual message.
   $name = trim( str_replace( array( '<', '>', '"', '\\' ), '', $valid['name'] ) );
   $headers[] = 'From: ' . ( '' !== $name ? $name . ' <' . $valid['email'] . '>' : $valid['email'] );
  }
  return wp_mail( $to, $subject, $message, $headers );
 }
}
