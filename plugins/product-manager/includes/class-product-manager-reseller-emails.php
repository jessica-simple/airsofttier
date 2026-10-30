<?php
/** Shared WooCommerce sender settings, also used by custom store messages. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Emails {
 private static $attempts = array();
 public static function init() {
  add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
  add_action( 'admin_post_airsoft_reseller_email_settings', array( __CLASS__, 'save' ) );
  add_action( 'admin_post_airsoft_registration_email_test', array( __CLASS__, 'test_registration' ) );
  add_filter( 'woocommerce_email_from_name', array( __CLASS__, 'from_name' ), 999 );
  add_filter( 'woocommerce_email_from_address', array( __CLASS__, 'from_email' ), 999 );
 }

 public static function menu() {
  add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'WooCommerce Emails', 'WooCommerce Emails', 'manage_woocommerce', 'airsoft-reseller-emails', array( __CLASS__, 'page' ) );
 }

 public static function settings() {
  $name = get_option( 'woocommerce_email_from_name', '' );
  $email = get_option( 'woocommerce_email_from_address', '' );
  return array(
   'name' => is_string( $name ) && '' !== trim( $name ) ? $name : get_bloginfo( 'name' ),
   'email' => is_string( $email ) && '' !== trim( $email ) ? $email : get_option( 'admin_email', '' ),
  );
 }

 public static function from_name( $current ) {
  $settings = self::settings();
  $valid = self::validate( $settings['name'], $settings['email'] );
  return ! is_wp_error( $valid ) && '' !== $valid['name'] ? trim( str_replace( array( '<', '>', '"', '\\' ), '', $valid['name'] ) ) : $current;
 }

 public static function from_email( $current ) {
  $settings = self::settings();
  $valid = self::validate( $settings['name'], $settings['email'] );
  return ! is_wp_error( $valid ) && '' !== $valid['email'] ? $valid['email'] : $current;
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
  echo '</form><h2>Registration email check</h2><p>This section keeps the registration confirmation separate from cart follow-ups. No recipient address or email content is stored. A successful handoff does not confirm delivery.</p>';
  $user = wp_get_current_user();
  echo '<p><strong>Test destination:</strong> ' . esc_html( $user->user_email ) . ' (your signed-in WordPress profile). <a href="' . esc_url( admin_url( 'profile.php' ) ) . '">View my profile</a></p>';
  if ( isset( $_GET['test_accepted'] ) ) { echo '<div class="notice notice-info inline"><p>The mail function accepted the test for this profile address. Check that inbox and its spam folder. Delivery has not been confirmed.</p></div>'; }
  echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_registration_email_test">';
  wp_nonce_field( 'airsoft_registration_email_test' );
  submit_button( 'Send registration sender check to my email', 'secondary' );
  echo '</form>';
  self::render_attempt( get_option( 'airsoft_registration_mail_last_attempt', array() ), 'No registration confirmation has been captured by this version yet. Use the button above or submit a new registration.' );
  echo '<h2>Latest custom store email</h2>';
  $last = get_option( 'airsoft_store_mail_last_attempt', array() );
  self::render_attempt( $last, 'No custom store email has been captured by this version yet.' );
  echo '</div>';
 }

 private static function render_attempt( $last, $empty ) {
  if ( is_array( $last ) && ! empty( $last['time'] ) ) {
   echo '<p>Last attempt (UTC): ' . esc_html( $last['time'] ) . '<br>Subject: ' . esc_html( $last['subject'] ?? '' ) . '<br>Sender name: ' . esc_html( $last['name'] ?? 'Not captured' ) . '<br>Sender email: ' . esc_html( $last['email'] ?? 'Not captured' ) . '<br>Message content: ' . ( ! empty( $last['body_bytes'] ) ? 'Present (' . absint( $last['body_bytes'] ) . ' bytes)' : 'Empty or mailer not reached' ) . '<br>Format: ' . esc_html( $last['content_type'] ?? 'Not captured' ) . '<br>Mail handoff: ' . ( ! empty( $last['accepted'] ) ? 'Accepted by mail function' : 'Failed or interrupted' ) . '</p>';
   echo '<p>Expected sender: ' . esc_html( $last['expected_name'] ?? 'Not captured' ) . ' &lt;' . esc_html( $last['expected_email'] ?? 'Not captured' ) . '&gt;<br>Generated From header: ' . esc_html( $last['generated_from'] ?? 'Not captured' ) . '<br>Message-ID: ' . esc_html( $last['message_id'] ?? 'Not captured' ) . '</p><p>Compare the Message-ID and From header with Gmail → More → Show original for this same message.</p>';
  } else { echo '<p>' . esc_html( $empty ) . '</p>'; }
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

 public static function test_registration() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  check_admin_referer( 'airsoft_registration_email_test' );
  $user = wp_get_current_user();
  if ( ! is_email( $user->user_email ) ) { wp_die( 'Set a valid email address on your administrator profile first.' ); }
  $sent = self::send( $user->user_email, 'Reseller registration sender check', "This is a delivery check for reseller registration emails.\n\nIf this text is visible, the message content is present. Compare this message's From header and Message-ID with the Registration email check in Product Manager > WooCommerce Emails.", 'registration' );
  if ( ! $sent ) { wp_die( 'The mail function did not accept the check email. Review Registration email check for details.' ); }
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-reseller-emails&test_accepted=1' ) ); exit;
 }

 public static function send( $to, $subject, $message, $context = 'store' ) {
  $settings = self::settings();
  $valid = self::validate( $settings['name'] ?? '', $settings['email'] ?? '' );
  $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
  if ( ! is_wp_error( $valid ) && '' !== $valid['email'] ) {
   // Strip address delimiters from the display name so it cannot become a
   // second mailbox/header. Pass the sender only on this individual message.
   $name = trim( str_replace( array( '<', '>', '"', '\\' ), '', $valid['name'] ) );
   $headers[] = 'From: ' . ( '' !== $name ? $name . ' <' . $valid['email'] . '>' : $valid['email'] );
  }
  // WordPress applies sender filters after parsing the From header. Set both
  // values during custom store sends too, so an earlier generic sender filter
  // cannot reset them to WordPress. Always clean up, even when delivery throws.
  add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ), 999 );
  add_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ), 999 );
  add_filter( 'wp_mail_content_type', array( __CLASS__, 'plain_text' ), 999 );
  add_action( 'phpmailer_init', array( __CLASS__, 'capture' ), PHP_INT_MAX );
  self::$attempts[] = array( 'time' => gmdate( 'Y-m-d H:i:s' ), 'subject' => sanitize_text_field( $subject ), 'accepted' => false, 'context' => $context, 'expected_name' => self::from_name( '' ), 'expected_email' => self::from_email( '' ) );
  try {
   $sent = wp_mail( $to, $subject, $message, $headers );
   $index = count( self::$attempts ) - 1;
   self::$attempts[$index]['accepted'] = (bool) $sent;
   // Read the MIME generated by PHPMailer after send(), rather than assuming
   // that the earlier phpmailer_init snapshot is the final transmitted header.
   global $phpmailer;
   if ( $sent && isset( self::$attempts[$index]['body_bytes'] ) && is_object( $phpmailer ) && $phpmailer->Subject === $subject && method_exists( $phpmailer, 'getSentMIMEMessage' ) ) {
    $mime = $phpmailer->getSentMIMEMessage();
    $parts = preg_split( '/\r?\n\r?\n/', $mime, 2 );
    $head = preg_replace( '/\r?\n[\t ]+/', ' ', $parts[0] );
    if ( preg_match( '/^From:\s*(.+)$/mi', $head, $match ) ) { self::$attempts[$index]['generated_from'] = trim( $match[1] ); }
    if ( preg_match( '/^Message-ID:\s*(.+)$/mi', $head, $match ) ) { self::$attempts[$index]['message_id'] = trim( $match[1] ); }
   }
   return $sent;
  } finally {
   $attempt = array_pop( self::$attempts );
   update_option( 'airsoft_store_mail_last_attempt', $attempt, false );
   if ( 'registration' === $context ) { update_option( 'airsoft_registration_mail_last_attempt', $attempt, false ); }
   if ( ! self::$attempts ) {
    remove_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ), 999 );
    remove_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ), 999 );
    remove_filter( 'wp_mail_content_type', array( __CLASS__, 'plain_text' ), 999 );
    remove_action( 'phpmailer_init', array( __CLASS__, 'capture' ), PHP_INT_MAX );
   }
  }
 }

 public static function plain_text( $type ) { return 'text/plain'; }

 public static function capture( $mailer ) {
  $index = count( self::$attempts ) - 1;
  if ( $index < 0 ) { return; }
  self::$attempts[$index]['name'] = $mailer->FromName;
  self::$attempts[$index]['email'] = $mailer->From;
  self::$attempts[$index]['body_bytes'] = strlen( (string) $mailer->Body );
  self::$attempts[$index]['content_type'] = $mailer->ContentType;
 }
}
