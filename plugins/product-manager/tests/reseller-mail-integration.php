<?php
/** Run with a WordPress core directory. Generates MIME locally; never sends. */
if ( PHP_SAPI !== 'cli' ) { exit; }
$core = $argv[1] ?? '';
if ( ! is_file( $core . '/wp-includes/pluggable.php' ) ) { fwrite( STDERR, "Pass the WordPress core directory.\n" ); exit( 1 ); }
define( 'ABSPATH', rtrim( $core, '/\\' ) . '/' );
define( 'WPINC', 'wp-includes' );
require_once ABSPATH . WPINC . '/plugin.php';
require_once ABSPATH . WPINC . '/class-wp-error.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
function get_option( $key, $default = false ) {
 $options = array( 'woocommerce_email_from_name' => 'Store Registration Team', 'woocommerce_email_from_address' => 'store@example.test', 'admin_email' => 'admin@example.test' );
 return $options[$key] ?? $default;
}
function update_option( $key, $value, $autoload ) { global $mail_diagnostic, $captured_options; $captured_options[$key] = $value; if ( 'airsoft_store_mail_last_attempt' === $key ) { $mail_diagnostic = $value; } }
function get_bloginfo( $key ) { return 'charset' === $key ? 'UTF-8' : 'Store'; }
function sanitize_text_field( $text ) { return trim( strip_tags( $text ) ); }
function sanitize_textarea_field( $text ) { return trim( strip_tags( $text ) ); }
function sanitize_email( $text ) { return filter_var( $text, FILTER_SANITIZE_EMAIL ); }
function is_email( $text ) { return filter_var( $text, FILTER_VALIDATE_EMAIL ); }
function wp_unslash( $text ) { return stripslashes( $text ); }
function email_exists( $email ) { return false; }
function wp_generate_password( $length, $special ) { return 'registrationtest'; }
function wp_insert_user( $data ) { return 123; }
function get_current_user_id() { return 0; }
function get_userdata( $id ) { return false; }
function is_user_logged_in() { return false; }
function absint( $value ) { return abs( (int) $value ); }
function home_url( $path ) { return 'https://example.test' . $path; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function network_home_url() { return 'https://example.test/'; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
require_once ABSPATH . WPINC . '/pluggable.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-emails.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
class CaptureRegistrationMailer extends PHPMailer\PHPMailer\PHPMailer {
 public $captured = array();
 public function postSend() {
  $this->captured[] = array( 'name' => $this->FromName, 'from' => $this->From, 'body' => $this->Body, 'mime' => $this->getSentMIMEMessage() );
  return true;
 }
}
$phpmailer = new CaptureRegistrationMailer( true );
$result = Airsoft_Product_Manager_Reseller_Portal::create_account( array( 'email' => 'applicant@example.test', 'business' => 'Test Shop', 'details' => 'Products', 'password' => 'password123456', 'password_confirm' => 'password123456' ) );
if ( is_wp_error( $result ) ) { throw new Exception( $result->get_error_message() ); }
if ( count( $phpmailer->captured ) !== 2 ) { throw new Exception( 'Registration did not generate two messages.' ); }
foreach ( $phpmailer->captured as $mail ) {
 if ( 'store@example.test' !== $mail['from'] || 'Store Registration Team' !== $mail['name'] || ! $mail['body'] || false === strpos( $mail['mime'], $mail['body'] ) ) { throw new Exception( 'Sender or body failed MIME check.' ); }
}
$confirmation = $phpmailer->captured[1];
if ( false === strpos( $confirmation['body'], 'pending approval' ) || false === strpos( $confirmation['body'], '/reseller-login/' ) ) { throw new Exception( 'Confirmation content missing.' ); }
if ( empty( $mail_diagnostic['body_bytes'] ) || 'store@example.test' !== $mail_diagnostic['email'] || 'Store Registration Team' !== $mail_diagnostic['name'] || ! $mail_diagnostic['accepted'] ) { throw new Exception( 'Real mailer diagnostics not captured.' ); }
if ( isset( $mail_diagnostic['body'] ) || isset( $mail_diagnostic['to'] ) ) { throw new Exception( 'Diagnostics stored recipient or body.' ); }
if ( false === strpos( $mail_diagnostic['generated_from'] ?? '', 'store@example.test' ) || empty( $mail_diagnostic['message_id'] ) ) { throw new Exception( 'Final generated From and Message-ID were not recorded.' ); }
$registration = $captured_options['airsoft_registration_mail_last_attempt'] ?? array();
if ( 'Reseller application received' !== ( $registration['subject'] ?? '' ) ) { throw new Exception( 'Registration confirmation does not have a separate diagnostic record.' ); }
Airsoft_Product_Manager_Reseller_Emails::send( 'admin@example.test', '2 cart(s) need follow-up', 'A cart follow-up message.' );
if ( $registration !== $captured_options['airsoft_registration_mail_last_attempt'] || '2 cart(s) need follow-up' !== $mail_diagnostic['subject'] ) { throw new Exception( 'Cart follow-up overwrote registration diagnostics.' ); }
if ( false === strpos( $confirmation['mime'], $registration['message_id'] ) || false === strpos( $confirmation['mime'], $registration['generated_from'] ) ) { throw new Exception( 'Registration diagnostic does not match generated confirmation MIME.' ); }
echo "PASS: Real WordPress/PHPMailer registration sender and content, final MIME headers, Message-ID correlation and separate cart/registration records; no messages sent.\n";
