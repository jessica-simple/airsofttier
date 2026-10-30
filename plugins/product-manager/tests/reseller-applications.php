<?php
/** Regression checks for the application page and WooCommerce endpoint routing. */
if ( PHP_SAPI !== 'cli' ) { exit; }
$core = $argv[1] ?? '';
if ( ! is_file( $core . '/wp-admin/includes/class-wp-list-table.php' ) ) { fwrite( STDERR, "Pass the WordPress core directory to exercise the native list table.\n" ); exit( 1 ); }
define( 'ABSPATH', rtrim( $core, '/\\' ) . '/' );
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=airsoft-resellers';
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
function get_userdata( $id ) { global $role_fixtures; return (object) array( 'roles' => $role_fixtures[$id] ?? array( 'customer' ) ); }
function get_user_meta( $id, $key, $single ) { global $metadata; return $metadata[ $id ][ $key ] ?? ''; }
function get_option( $key, $default = false ) { global $options; return $options[ $key ] ?? $default; }
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
function __( $text, $domain = '' ) { return $text; }
function _e( $text, $domain = '' ) { echo $text; }
function _x( $text, $context, $domain = '' ) { return $text; }
function _n( $single, $plural, $count ) { return 1 === (int) $count ? $single : $plural; }
function esc_attr__( $text ) { return esc_attr( $text ); }
function esc_attr_e( $text ) { echo esc_attr( $text ); }
function esc_html__( $text ) { return esc_html( $text ); }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
class TestAdminScreen {
 public $id = 'product-manager_page_airsoft-resellers';
 public $base = 'product-manager';
 public function render_screen_reader_content( $key ) {}
 public function get_option( $key, $type = null ) { return 'list'; }
}
function convert_to_screen( $screen ) { return new TestAdminScreen(); }
function get_current_screen() { return new TestAdminScreen(); }
function get_column_headers( $screen ) { return apply_filters( 'manage_' . $screen->id . '_columns', array() ); }
function number_format_i18n( $number ) { return (string) $number; }
function wp_doing_ajax() { return false; }
function get_user_setting( $name, $default = false ) { return $default; }
function _admin_search_query() { echo esc_attr( $_REQUEST['s'] ?? '' ); }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function set_url_scheme( $url ) { return $url; }
function wp_removable_query_args() { return array(); }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function add_query_arg( $key, $value, $url = null ) {
 if ( is_array( $key ) ) { $args = $key; $url = $value; } else { $args = array( $key => $value ); }
 $url = $url ?: 'https://example.test/wp-admin/admin.php';
 $query = array(); parse_str( parse_url( $url, PHP_URL_QUERY ) ?? '', $query );
 $query = array_merge( $query, $args );
 foreach ( $query as $name => $entry ) { if ( false === $entry ) { unset( $query[$name] ); } }
 return explode( '?', $url )[0] . ( $query ? '?' . http_build_query( $query ) : '' );
}
function remove_query_arg( $keys, $url = '' ) { return add_query_arg( array_fill_keys( (array) $keys, false ), $url ?: null ); }
function submit_button( $label, $type = '', $name = 'submit', $wrap = true, $attributes = array() ) { echo '<button name="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</button>'; }
function get_avatar( $id, $size ) { return '<img class="avatar" width="32" height="32" alt="">'; }
function mysql2date( $format, $date ) { return $date; }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ); }
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
verify( false !== strpos( $html, 'wp-list-table' ), 'Empty later page retains native table controls' );
$_GET = array(); $account_page = 10;
$applications = array( (object) array( 'ID' => 8, 'user_login' => 'applicant', 'display_name' => 'Test Applicant', 'user_email' => 'applicant@example.test', 'user_registered' => '2026-10-29 00:00:00' ) );
$metadata[8] = array( '_airsoft_account_type' => 'reseller', '_airsoft_reseller_application' => 'pending', '_airsoft_reseller_business' => '<script>business</script>' );
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false !== strpos( $html, 'Test Applicant' ) && false !== strpos( $html, 'value="approved:8"' ), 'Submitted applicant and approval actions render' );
verify( 1 === preg_match( '/<td[^>]*column-actions[^>]*>.*value="approved:8".*value="rejected:8"/s', $html ), 'Pending row has visible Approve and Reject buttons in Actions column' );
verify( false !== strpos( $html, 'button button-primary button-small' ), 'Approve uses a visible native button rather than a hover-only link' );
verify( false === strpos( $html, '<script>' ), 'Application content is escaped' );
verify( false === strpos( $html, 'No reseller applications yet.' ), 'Empty message hidden for existing applicants' );
verify( false !== strpos( $html, 'widefat fixed striped' ) && false !== strpos( $html, 'class=\'subsubsub\'' ), 'Native Users-style table and status tabs render' );
verify( false !== strpos( $html, 'bulk-action-selector-top' ) && false !== strpos( $html, 'application_ids[]' ), 'Native bulk controls and applicant checkboxes render' );
verify( false !== strpos( $html, 'Search applications' ) && false !== strpos( $html, 'orderby=email' ), 'Search and sortable email column render' );
$filters = Airsoft_Product_Manager_Resellers::list_filters( array( 'application_status' => 'pending', 's' => 'applicant@example.test', 'orderby' => 'email', 'order' => 'asc', 'paged' => 2 ) );
$args = Airsoft_Product_Manager_Resellers::application_query( $filters );
verify( 2 === $args['paged'] && 'email' === $args['orderby'] && 'ASC' === $args['order'], 'Pagination and approved sort keys passed to user query' );
verify( 'pending' === $args['meta_query'][1]['value'] && '*applicant@example.test*' === $args['search'], 'Status filter and search restrict applicant query' );
$filters = Airsoft_Product_Manager_Resellers::list_filters( array( 'application_status' => 'administrator', 'orderby' => 'arbitrary_sql', 'order' => 'invalid', 'paged' => 0 ) );
verify( '' === $filters['application_status'] && 'registered' === $filters['orderby'] && 'desc' === $filters['order'] && 1 === $filters['paged'], 'Invalid filters and sorting use safe defaults' );
$metadata[8]['_airsoft_reseller_application'] = 'approved';
$role_fixtures[8] = array( 'airsoft_reseller' );
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false === strpos( $html, 'value="approved:8"' ) && false !== strpos( $html, '>Revoke</button>' ), 'Approved row has Revoke instead of a redundant Approve button' );
$role_fixtures = array();
$metadata[8] = array( '_airsoft_reseller_application' => 'pending' );
$html = output_of( array( 'Airsoft_Product_Manager_Resellers', 'admin_page' ) );
verify( false === strpos( $html, 'value="approved:8"' ) && false === strpos( $html, 'name="application_ids[]"' ), 'Legacy member applicants have no bulk checkbox or approval action' );
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
