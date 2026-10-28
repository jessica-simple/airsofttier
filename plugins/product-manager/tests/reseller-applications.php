<?php
/** Regression checks for the application page and WooCommerce endpoint routing. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$hooks = array();
$applications = array();
$metadata = array();
$options = array( 'airsoft_reseller_routes' => '1' );
$account_page = 10;
$flushes = 0;
function add_action( $hook, $callback ) { add_filter( $hook, $callback ); }
function add_filter( $hook, $callback ) { global $hooks; $hooks[ $hook ][] = $callback; }
function apply_filters( $hook, $value ) { global $hooks; foreach ( $hooks[ $hook ] ?? array() as $callback ) { $value = call_user_func( $callback, $value ); } return $value; }
function current_user_can( $cap ) { return 'manage_woocommerce' === $cap; }
function is_user_logged_in() { return true; }
function get_current_user_id() { return 8; }
function get_userdata( $id ) { return (object) array( 'roles' => array( 'customer' ) ); }
function get_user_meta( $id, $key, $single ) { global $metadata; return $metadata[ $id ][ $key ] ?? ''; }
function get_option( $key ) { global $options; return $options[ $key ] ?? false; }
function update_option( $key, $value, $autoload ) { global $options; $options[ $key ] = $value; }
function add_role( $role, $name, $caps ) {}
function add_rewrite_endpoint( $slug, $mask ) {}
function flush_rewrite_rules( $hard ) { global $flushes; $flushes++; }
define( 'EP_ROOT', 64 ); define( 'EP_PAGES', 4096 );
function absint( $v ) { return abs( (int) $v ); }
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES ); }
function esc_attr( $v ) { return esc_html( $v ); }
function esc_textarea( $v ) { return esc_html( $v ); }
function esc_url( $v ) { return esc_html( $v ); }
function wp_kses_post( $v ) { return $v; }
function admin_url( $path ) { return 'https://example.test/wp-admin/' . $path; }
function wc_get_page_id( $name ) { global $account_page; return $account_page; }
function wc_get_account_endpoint_url( $name ) { return 'https://example.test/my-account/' . $name . '/'; }
function home_url( $path ) { return 'https://example.test' . $path; }
function wp_nonce_field( $action, $name = '_wpnonce' ) { echo '<input name="' . esc_attr( $name ) . '" value="test-nonce">'; }
function paginate_links( $args ) { return ''; }
function do_action( $hook ) {}
function __( $text, $domain ) { return $text; }
function esc_html_e( $text, $domain ) { echo esc_html( $text ); }
function wc_get_page_permalink( $name ) { return 'https://example.test/shop/'; }
function wc_get_account_menu_items() {
 global $enable_reseller_menu;
 $items = array( 'dashboard' => 'Dashboard', 'orders' => 'Orders', 'customer-logout' => 'Log Out' );
 return $enable_reseller_menu ? apply_filters( 'woocommerce_account_menu_items', $items ) : $items;
}
function wc_is_current_account_menu_item( $endpoint ) { global $wc_query; return $wc_query->get_current_endpoint() === $endpoint; }
function is_wc_endpoint_url( $endpoint ) { return wc_is_current_account_menu_item( $endpoint ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
class WP_User_Query {
 public function __construct( $args ) {}
 public function get_results() { global $applications; return $applications; }
 public function get_total() { global $applications; return count( $applications ); }
}
require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
require_once __DIR__ . '/../../woocommerce/includes/class-wc-query.php';
Airsoft_Product_Manager_Resellers::init();
$checks = 0;
function verify( $condition, $label ) { global $checks; if ( ! $condition ) { throw new Exception( 'FAIL: ' . $label ); } $checks++; }
function output_of( $callback ) { ob_start(); call_user_func( $callback ); return ob_get_clean(); }

// Exercise the actual bundled WooCommerce routing implementation, without its
// unrelated container dependencies or a WordPress database.
$wc_query = ( new ReflectionClass( 'WC_Query' ) )->newInstanceWithoutConstructor();
verify( 'reseller' === $wc_query->get_query_vars()['reseller'], 'Endpoint registered with WooCommerce' );
verify( in_array( 'reseller', $wc_query->add_query_vars( array() ), true ), 'Endpoint exposed to WordPress query parsing' );
$wp = (object) array( 'query_vars' => array() );
$_GET = array( 'reseller' => '' );
$wc_query->parse_request();
verify( 'reseller' === $wc_query->get_current_endpoint(), 'Plain permalink application URL routes to reseller endpoint' );
$_GET = array(); $wp->query_vars = array( 'reseller' => '' );
verify( 'reseller' === $wc_query->get_current_endpoint(), 'Pretty permalink endpoint recognised' );
Airsoft_Product_Manager_Resellers::register();
Airsoft_Product_Manager_Resellers::register();
verify( 1 === $flushes && '2' === $options['airsoft_reseller_routes'], 'Existing installs refresh rewrite rules once' );

$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false !== strpos( $html, 'No reseller applications yet.' ), 'Empty admin screen explains missing applications' );
verify( false !== strpos( $html, 'https://example.test/reseller-registration/' ), 'Admin has a link to separate registration' );
$account_page = -1;
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false !== strpos( $html, 'https://example.test/reseller-portal/' ), 'Reseller portal does not depend on member account page' );
$_GET['paged'] = 3;
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false !== strpos( $html, 'Return to the first page' ), 'Empty later page has a way back' );
$_GET = array(); $account_page = 10;
$applications = array( (object) array( 'ID' => 8, 'display_name' => 'Test Applicant', 'user_email' => 'applicant@example.test' ) );
$metadata[8] = array( '_airsoft_account_type' => 'reseller', '_airsoft_reseller_application' => 'pending', '_airsoft_reseller_business' => '<script>business</script>' );
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false !== strpos( $html, 'Test Applicant' ) && false !== strpos( $html, 'value="approved"' ), 'Submitted applicant and approval actions render' );
verify( false === strpos( $html, '<script>' ), 'Application content is escaped' );
verify( false === strpos( $html, 'No reseller applications yet.' ), 'Empty message hidden for existing applicants' );
$metadata = array();
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'dashboard' ) );
verify( false !== strpos( $html, 'Open Reseller Portal' ) && false === strpos( $html, '<form' ), 'Legacy member endpoint directs to separate portal' );
$metadata[8]['_airsoft_reseller_application'] = 'pending';
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'dashboard' ) );
verify( false === strpos( $html, '<form' ), 'Legacy member application form is unavailable' );

// The actual child-theme override must not mix reseller tools into My Account.
$enable_reseller_menu = true;
$wp->query_vars = array( 'reseller' => '' );
$template = dirname( __DIR__, 3 ) . '/themes/betheme-child/woocommerce/myaccount/navigation.php';
$render_navigation = function () use ( $template ) { include $template; };
$html = output_of( $render_navigation );
verify( false !== strpos( $html, 'csw-account-sidebar' ) && 0 === preg_match( '/\bReseller\b/', strip_tags( $html ) ), 'Member sidebar excludes reseller entry' );
verify( false === strpos( $html, 'href="https://example.test/my-account/reseller/"' ), 'Member sidebar has no reseller endpoint link' );
$enable_reseller_menu = false;
$html = output_of( $render_navigation );
verify( false === strpos( $html, 'https://example.test/my-account/reseller/' ), 'Custom sidebar hides reseller link when feature is unavailable' );
echo "PASS: $checks application and endpoint checks\n";
