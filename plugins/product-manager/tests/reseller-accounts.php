<?php
/** Standalone account separation and portal authorization checks. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$users = array(); $metadata = array(); $options = array(); $current = 0;
$page_key = ''; $member_page = false; $endpoint = ''; $signons = 0; $pages = array(); $created_pages = 0;
class WP_Error {
 public $errors = array();
 public function __construct( $code = '', $message = '' ) { if ( $code ) { $this->add( $code, $message ); } }
 public function add( $code, $message ) { $this->errors[ $code ] = $message; }
 public function has_errors() { return ! empty( $this->errors ); }
 public function get_error_message() { return reset( $this->errors ); }
}
class TestUser {
 public $ID; public $roles; public $user_email; public $user_login;
 public function __construct( $id, $roles, $email ) { $this->ID = $id; $this->roles = $roles; $this->user_email = $email; $this->user_login = 'user_' . $id; }
 public function add_role( $role ) { $this->roles[] = $role; $this->roles = array_values( array_unique( $this->roles ) ); }
 public function remove_role( $role ) { $this->roles = array_values( array_diff( $this->roles, array( $role ) ) ); }
}
class Redirected extends Exception {}
function is_wp_error( $v ) { return $v instanceof WP_Error; }
function is_user_logged_in() { global $current; return $current > 0; }
function get_current_user_id() { global $current; return $current; }
function get_userdata( $id ) { global $users; return $users[ $id ] ?? false; }
function get_user_by( $field, $value ) { global $users; foreach ( $users as $user ) { if ( ( 'email' === $field ? $user->user_email : $user->user_login ) === $value ) { return $user; } } return false; }
function get_user_meta( $id, $key, $single ) { global $metadata; return $metadata[ $id ][ $key ] ?? ''; }
function update_user_meta( $id, $key, $value ) { global $metadata; $metadata[ $id ][ $key ] = $value; }
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_textarea_field( $v ) { return sanitize_text_field( $v ); }
function sanitize_key( $v ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $v ) ); }
function sanitize_email( $v ) { return filter_var( $v, FILTER_SANITIZE_EMAIL ); }
function is_email( $v ) { return filter_var( $v, FILTER_VALIDATE_EMAIL ); }
function email_exists( $email ) { return (bool) get_user_by( 'email', $email ); }
function wp_unslash( $v ) { return stripslashes( $v ); }
function wp_generate_password( $len, $special ) { return 'generatedname1234'; }
function apply_filters( $hook, $value ) { return $value; }
function wp_insert_user( $data ) {
 global $users, $metadata, $last_created;
 $last_created = $data; $id = count( $users ) + 100;
 $users[$id] = new TestUser( $id, array( $data['role'] ), $data['user_email'] );
 $metadata[$id] = $data['meta_input']; return $id;
}
function get_option( $key ) { global $options; return $options[$key] ?? false; }
function get_bloginfo( $key ) { return 'Test Store'; }
function update_option( $key, $value, $autoload ) { global $options; $options[$key] = $value; }
function wp_mail( $to, $subject, $body ) { return true; }
function absint( $v ) { return abs( (int) $v ); }
function home_url( $path ) { return 'https://example.test' . $path; }
function get_post_status( $id ) { global $pages; return isset( $pages[$id] ) ? 'publish' : false; }
function get_permalink( $id ) { return home_url( '/page-' . $id . '/' ); }
function current_user_can( $cap ) { return 'manage_options' === $cap; }
function wp_insert_post( $data, $error ) { global $pages, $created_pages; $id = 1000 + ++$created_pages; $pages[$id] = $data; return $id; }
function get_post() { global $page_key; return $page_key ? (object) array( 'post_content' => '[airsoft_reseller_' . $page_key . ']' ) : null; }
function is_page( $id ) { return false; }
function has_shortcode( $content, $tag ) { return false !== strpos( $content, '[' . $tag . ']' ); }
function is_account_page() { global $member_page; return $member_page; }
function is_wc_endpoint_url( $name ) { global $endpoint; return $name === $endpoint; }
function nocache_headers() {}
function wp_verify_nonce( $value, $action ) { return 'valid' === $value; }
function is_ssl() { return true; }
function wp_signon( $credentials, $secure ) { global $signons; $signons++; return new WP_Error( 'bad_password', 'Invalid password' ); }
function wp_safe_redirect( $url ) { throw new Redirected( $url ); }
function add_query_arg( $key, $value, $url = null ) { if ( is_array( $key ) ) { $args = $key; $url = $value; } else { $args = array( $key => $value ); } return $url . '?' . http_build_query( $args ); }
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES ); }
function esc_attr( $v ) { return esc_html( $v ); }
function esc_url( $v ) { return esc_html( $v ); }
function wp_logout_url( $url ) { return 'https://example.test/logout?redirect=' . urlencode( $url ); }
function wp_lostpassword_url( $url ) { return 'https://example.test/password-reset'; }
function wp_nonce_field( $action, $name ) { echo '<input name="' . esc_attr( $name ) . '">'; }
function wc_print_notices() {}
function wc_get_order( $id ) { return new class { public function get_customer_id() { return 1; } }; }
require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-emails.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
$checks = 0;
function verify( $pass, $label ) { global $checks; if ( ! $pass ) { throw new Exception( 'FAIL: ' . $label ); } $checks++; }
$users[1] = new TestUser( 1, array( 'customer' ), 'member@example.test' );
$users[2] = new TestUser( 2, array( 'airsoft_reseller' ), 'reseller@example.test' );
$users[3] = new TestUser( 3, array( 'airsoft_reseller_applicant' ), 'pending@example.test' );
$users[4] = new TestUser( 4, array( 'customer', 'airsoft_reseller' ), 'legacy@example.test' );
verify( ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( 1 ), 'Member is not reseller' );
verify( Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( 2 ), 'Approved account classification' );
verify( Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( 3 ), 'Applicant account stays separate' );
$input = array( 'email' => 'new@example.test', 'business' => 'New Business', 'details' => 'Products', 'password' => 'longpassword123', 'password_confirm' => 'longpassword123', 'role' => 'administrator' );
$id = Airsoft_Product_Manager_Reseller_Portal::create_account( $input );
verify( is_int( $id ) && array( 'airsoft_reseller_applicant' ) === $users[$id]->roles, 'Registration grants applicant role only, ignoring requested privilege' );
verify( 'reseller' === $metadata[$id]['_airsoft_account_type'] && 'pending' === $metadata[$id]['_airsoft_reseller_application'], 'New identity stores type and pending approval' );
verify( ! Airsoft_Product_Manager_Resellers::approved( $id ), 'Application cannot grant pricing or seller access' );
verify( is_wp_error( Airsoft_Product_Manager_Reseller_Portal::create_account( $input ) ), 'Duplicate reseller email rejected' );
$input['email'] = 'member@example.test';
verify( is_wp_error( Airsoft_Product_Manager_Reseller_Portal::create_account( $input ) ), 'Existing member email rejected' );
$input['email'] = 'another@example.test'; $input['password_confirm'] = 'different';
verify( is_wp_error( Airsoft_Product_Manager_Reseller_Portal::create_account( $input ) ), 'Password confirmation validated' );
$current = 1;
verify( is_wp_error( Airsoft_Product_Manager_Reseller_Portal::create_account( $input ) ), 'Signed-in member cannot convert account through registration' );
verify( array( 'customer' ) === $users[1]->roles && empty( $metadata[1] ), 'Member identity unchanged' );
$errors = Airsoft_Product_Manager_Reseller_Portal::member_login( new WP_Error(), 'reseller@example.test' );
verify( $errors->has_errors(), 'Member login rejects reseller identity' );
verify( ! Airsoft_Product_Manager_Reseller_Portal::member_login( new WP_Error(), 'member@example.test' )->has_errors(), 'Member login accepts member identity' );
Airsoft_Product_Manager_Reseller_Portal::normalize_legacy_account( '', $users[4] );
verify( array( 'airsoft_reseller' ) === $users[4]->roles && 4 === $users[4]->ID, 'Legacy reseller retains ID and loses customer role' );
Airsoft_Product_Manager_Reseller_Portal::set_status( $users[3], 'approved' );
verify( array( 'airsoft_reseller' ) === $users[3]->roles, 'Approval replaces applicant role' );
Airsoft_Product_Manager_Reseller_Portal::set_status( $users[3], 'rejected' );
verify( array( 'airsoft_reseller_applicant' ) === $users[3]->roles && Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( 3 ), 'Revocation never converts reseller to member' );
$current = 3;
verify( false === Airsoft_Product_Manager_Reseller_Portal::can_purchase( true, null ), 'Unapproved reseller cannot purchase' );
$current = 2;
verify( true === Airsoft_Product_Manager_Reseller_Portal::can_purchase( true, null ), 'Approved reseller can purchase' );
verify( false === Airsoft_Product_Manager_Reseller_Portal::can_purchase( false, null ), 'Purchase filter preserves existing restrictions' );
$current = 1;
$html = Airsoft_Product_Manager_Reseller_Portal::portal();
verify( false !== strpos( $html, 'member account' ) && false === strpos( $html, 'Sales and earnings' ), 'Member cannot see reseller tools' );
$current = 3;
$html = Airsoft_Product_Manager_Reseller_Portal::portal();
verify( false !== strpos( $html, 'Application status:' ) && false === strpos( $html, 'Sales and earnings' ), 'Unapproved reseller sees status without tools' );
$current = 0;
verify( false !== strpos( Airsoft_Product_Manager_Reseller_Portal::portal(), 'Sign in as a reseller' ), 'Guest cannot see portal tools' );
$page_key = 'login';
$_POST = array( 'airsoft_portal_action' => 'login', 'airsoft_portal_nonce' => 'valid', 'reseller_login' => 'member@example.test', 'reseller_password' => 'password' );
Airsoft_Product_Manager_Reseller_Portal::route();
verify( 0 === $signons, 'Reseller login never authenticates member identity' );
$_POST['reseller_login'] = 'reseller@example.test';
Airsoft_Product_Manager_Reseller_Portal::route();
verify( 1 === $signons, 'Reseller login runs WordPress authentication' );
$_POST['airsoft_portal_nonce'] = 'invalid';
Airsoft_Product_Manager_Reseller_Portal::route();
verify( 1 === $signons, 'Bad nonce stops authentication' );
$_POST = array(); $page_key = 'portal';
try { Airsoft_Product_Manager_Reseller_Portal::route(); verify( false, 'Guest redirect missing' ); }
catch ( Redirected $e ) { verify( false !== strpos( $e->getMessage(), '/reseller-login/' ), 'Guest portal redirects to reseller login' ); }
$current = 2; $page_key = ''; $member_page = true;
try { Airsoft_Product_Manager_Reseller_Portal::route(); verify( false, 'Reseller member-page redirect missing' ); }
catch ( Redirected $e ) { verify( false !== strpos( $e->getMessage(), '/reseller-portal/' ), 'Reseller cannot use member dashboard' ); }
$member_page = false;
$url = Airsoft_Product_Manager_Reseller_Portal::endpoint_url( '/my-account/view-order/5/', 'view-order', 5, '' );
verify( false !== strpos( $url, 'section=purchases' ) && false !== strpos( $url, 'view_order=5' ), 'Purchase links stay in reseller portal' );
$current = 1;
verify( '/member-orders/' === Airsoft_Product_Manager_Reseller_Portal::endpoint_url( '/member-orders/', 'orders', '', '' ), 'Member URLs remain unchanged' );
$current = 2; $_GET = array( 'section' => 'shipping' ); $_POST = array( 'action' => 'edit_address', 'woocommerce-edit-address-nonce' => 'valid' ); $wp = (object) array( 'query_vars' => array() );
Airsoft_Product_Manager_Reseller_Portal::address_context();
verify( 'shipping' === $wp->query_vars['edit-address'], 'WooCommerce saves portal shipping form as shipping address' );
$current = 1; $wp->query_vars = array();
Airsoft_Product_Manager_Reseller_Portal::address_context();
verify( empty( $wp->query_vars ), 'Member form context cannot be altered by portal' );
Airsoft_Product_Manager_Reseller_Portal::pages();
Airsoft_Product_Manager_Reseller_Portal::pages();
verify( 3 === $created_pages, 'Registration, login and portal pages created once' );
verify( '[airsoft_reseller_register]' === $pages[1001]['post_content'], 'Registration page uses correct shortcode' );
$_GET = array( 'view_order' => 77 ); $current = 2;
$method = new ReflectionMethod( 'Airsoft_Product_Manager_Reseller_Portal', 'purchases' ); $method->setAccessible( true );
ob_start(); $method->invoke( null ); $html = ob_get_clean();
verify( false !== strpos( $html, 'unavailable' ), 'Reseller cannot view another account purchase by changing ID' );
echo "PASS: $checks account separation checks\n";
