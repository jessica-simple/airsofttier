<?php
/** Sender isolation, validation and settings permissions. No email is sent. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$settings = array(); $messages = array(); $allowed = false; $nonce_checked = false; $permit_nonce = false; $saved_options = array();
class WP_Error {
 private $message;
 public function __construct( $code, $message ) { $this->message = $message; }
 public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function is_email( $value ) { return filter_var( $value, FILTER_VALIDATE_EMAIL ); }
function get_option( $key, $default = false ) {
 global $settings;
 if ( 'woocommerce_email_from_name' === $key ) { return $settings['name'] ?? ''; }
 if ( 'woocommerce_email_from_address' === $key ) { return $settings['email'] ?? ''; }
 return $default;
}
function get_bloginfo( $key ) { return 'Store'; }
function wp_mail( $to, $subject, $body, $headers = array() ) { global $messages; $messages[] = compact( 'to', 'subject', 'body', 'headers' ); return true; }
function current_user_can( $capability ) { global $allowed; return $allowed; }
function wp_die( $message ) { throw new Exception( $message ); }
function check_admin_referer( $action ) { global $nonce_checked, $permit_nonce; $nonce_checked = true; if ( ! $permit_nonce ) { throw new Exception( 'nonce checked' ); } }
function wp_unslash( $value ) { return stripslashes( $value ); }
function update_option( $key, $value, $autoload ) { global $saved_options; $saved_options[$key] = $value; }
function admin_url( $path ) { return '/wp-admin/' . $path; }
function wp_safe_redirect( $url ) { throw new Exception( 'saved redirect' ); }
require_once __DIR__ . '/../includes/class-product-manager-reseller-emails.php';
$checks = 0;
function verify( $condition, $message ) { global $checks; if ( ! $condition ) { throw new Exception( 'FAIL: ' . $message ); } $checks++; }
$class = 'Airsoft_Product_Manager_Reseller_Emails';
verify( $class::send( 'reseller@example.test', 'Approved', 'Approved message' ), 'Mailer return value preserved' );
verify( array() === $messages[0]['headers'], 'Unset sender retains site defaults' );
$settings = array( 'name' => 'Reseller Team', 'email' => 'team@example.test' );
$class::send( 'reseller@example.test', 'Approved', 'Approved message' );
verify( array( 'From: Reseller Team <team@example.test>' ) === $messages[1]['headers'], 'Custom sender applied to reseller message' );
verify( 'reseller@example.test' === $messages[1]['to'] && 'Approved' === $messages[1]['subject'], 'Recipient and subject unchanged' );
wp_mail( 'admin@example.test', 'WordPress update', 'WordPress message' );
verify( array() === $messages[2]['headers'], 'Unrelated email does not inherit reseller sender' );
verify( $class::settings()['name'] === get_option( 'woocommerce_email_from_name' ) && $class::settings()['email'] === get_option( 'woocommerce_email_from_address' ), 'Custom store emails share WooCommerce native sender options' );
$settings['name'] = '';
$class::send( 'reseller@example.test', 'Update', 'Message' );
verify( array( 'From: team@example.test' ) === $messages[3]['headers'], 'Email-only custom sender supported' );
verify( is_wp_error( $class::validate( 'Team', '' ) ), 'Name without email rejected' );
verify( is_wp_error( $class::validate( 'Team', 'not-an-email' ) ), 'Invalid address rejected' );
verify( is_wp_error( $class::validate( "Team\r\nBcc: attacker@example.test", 'team@example.test' ) ), 'Name header injection rejected' );
verify( is_wp_error( $class::validate( 'Team', "team@example.test\nBcc: attacker@example.test" ) ), 'Email header injection rejected' );
verify( is_wp_error( $class::validate( array(), 'team@example.test' ) ), 'Malformed submitted name rejected' );
verify( array( 'name' => '', 'email' => '' ) === $class::validate( '', '' ), 'Clearing settings restores defaults' );
$settings = array( 'name' => "Team\nInjected", 'email' => 'team@example.test' );
$class::send( 'reseller@example.test', 'Update', 'Message' );
verify( array() === $messages[4]['headers'], 'Malformed stored settings safely fall back to defaults' );
$settings = array( 'name' => 'Team "Display"', 'email' => 'team@example.test' );
$class::send( 'reseller@example.test', 'Update', 'Message' );
verify( array( 'From: Team Display <team@example.test>' ) === $messages[5]['headers'], 'Display-name delimiters cannot alter mailbox header' );
try { $class::save(); verify( false, 'Unauthorized save accepted' ); }
catch ( Exception $e ) { verify( 'Access denied.' === $e->getMessage() && ! $nonce_checked, 'Settings save requires management permission' ); }
$allowed = true;
try { $class::save(); verify( false, 'Save bypassed nonce' ); }
catch ( Exception $e ) { verify( $nonce_checked && 'nonce checked' === $e->getMessage(), 'Authorized save still checks nonce' ); }
$permit_nonce = true;
$_POST = array( 'sender_name' => 'Entire Store', 'sender_email' => 'store@example.test' );
try { $class::save(); }
catch ( Exception $e ) { verify( 'saved redirect' === $e->getMessage(), 'Successful settings save redirects' ); }
verify( array( 'woocommerce_email_from_name' => 'Entire Store', 'woocommerce_email_from_address' => 'store@example.test' ) === $saved_options, 'Saving updates only WooCommerce shared sender options' );
echo "PASS: $checks reseller email checks\n";
