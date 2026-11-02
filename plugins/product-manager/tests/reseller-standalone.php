<?php
/** Standalone template routing and rendering checks; no WordPress server required. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$sandbox = sys_get_temp_dir() . '/airsoft-standalone-' . bin2hex( random_bytes( 6 ) ) . '/';
mkdir( $sandbox . 'templates', 0777, true );
mkdir( $sandbox . 'assets/css', 0777, true );
copy( __DIR__ . '/../templates/reseller-standalone.php', $sandbox . 'templates/reseller-standalone.php' );
file_put_contents( $sandbox . 'assets/css/reseller.css', '' );
define( 'AIRSOFT_PRODUCT_MANAGER_DIR', $sandbox );
define( 'AIRSOFT_PRODUCT_MANAGER_URL', 'https://example.test/plugin/' );
register_shutdown_function( function () use ( $sandbox ) {
 foreach ( array( 'templates/reseller-standalone.php', 'assets/css/reseller.css' ) as $file ) { if ( is_file( $sandbox . $file ) ) { unlink( $sandbox . $file ); } }
 foreach ( array( 'templates', 'assets/css', 'assets', '' ) as $dir ) { rmdir( $sandbox . $dir ); }
} );
set_error_handler( function ( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
$options = array(); $post = null; $queried_id = 0; $current = 0; $flags = array();
$styles = array(); $scripts = array(); $hooks = array(); $shortcodes = array();
$name = 'Combat Shooting Zones'; $shop = 'https://example.test/shop/';
$responsive = false; $meta_hook = false; $loop = false; $content = ''; $content_calls = 0;
$preview = false; $checks = 0;
function verify( $pass, $label ) { global $checks; if ( ! $pass ) { throw new Exception( 'FAIL: ' . $label ); } $checks++; }
function add_action( $hook, $callback, $priority = 10, $args = 1 ) { global $hooks; $hooks[$hook][] = array( $callback, $priority, $args ); }
function add_filter( $hook, $callback, $priority = 10, $args = 1 ) { add_action( $hook, $callback, $priority, $args ); }
function add_shortcode( $tag, $callback ) { global $shortcodes; $shortcodes[$tag] = $callback; }
function has_action( $hook, $callback ) { global $meta_hook; return $meta_hook && 'mfn_meta' === $callback ? 1 : false; }
function mfn_opts_get( $key ) { global $responsive; return $responsive; }
function get_option( $key, $default = false ) { global $options; return $options[$key] ?? $default; }
function get_post() { global $post; return $post; }
function is_page( $id ) { global $queried_id; return $id === $queried_id; }
function has_shortcode( $text, $tag ) { return false !== strpos( $text, '[' . $tag . ']' ); }
function is_admin() { global $flags; return ! empty( $flags['admin'] ); }
function is_singular( $type ) { global $flags; return 'page' === $type && empty( $flags['non_page'] ); }
function is_embed() { global $flags; return ! empty( $flags['embed'] ); }
function is_customize_preview() { global $flags; return ! empty( $flags['customizer'] ); }
function is_shop() { global $flags; return ! empty( $flags['shop'] ); }
function is_cart() { global $flags; return ! empty( $flags['cart'] ); }
function is_checkout() { global $flags; return ! empty( $flags['checkout'] ); }
function is_account_page() { global $flags; return ! empty( $flags['account'] ); }
function is_wc_endpoint_url() { global $flags; return ! empty( $flags['endpoint'] ); }
function get_current_user_id() { global $current; return $current; }
function is_user_logged_in() { return 0 !== get_current_user_id(); }
function get_userdata( $id ) {
 if ( ! $id ) { return false; }
 return (object) array( 'roles' => array( 1 === $id ? 'customer' : ( 2 === $id ? 'airsoft_reseller' : 'airsoft_reseller_applicant' ) ) );
}
function get_user_meta( $id, $key, $single = true ) { return '_airsoft_reseller_application' === $key ? ( 4 === $id ? 'rejected' : 'pending' ) : ''; }
function absint( $value ) { return abs( (int) $value ); }
function get_bloginfo( $key ) { global $name; return 'charset' === $key ? 'UTF-8' : $name; }
function home_url( $path ) { return 'https://example.test' . $path; }
function wc_get_page_permalink( $page ) { global $shop; return $shop; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url( $value ) { return esc_html( $value ); }
function get_post_status( $id ) { return 'publish'; }
function get_permalink( $id ) { return home_url( '/page-' . $id . '/' ); }
function wp_logout_url( $url ) { return home_url( '/logout/?redirect=' . urlencode( $url ) ); }
function wp_lostpassword_url( $url ) { return home_url( '/my-account/lost-password/' ); }
function wp_nonce_field( $action, $name ) { echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="test">'; }
function wc_print_notices() {}
function wp_enqueue_style( $handle, $url = '', $deps = array(), $version = null ) { global $styles; $styles[] = $handle; }
function wp_enqueue_script( $handle ) { global $scripts; $scripts[] = $handle; }
function language_attributes() { echo 'lang="en"'; }
function body_class( $class ) { echo 'class="' . esc_attr( $class ) . '"'; }
function wp_head() {
 global $meta_hook, $responsive, $preview;
 echo '<!-- wp_head -->';
 if ( $meta_hook && $responsive ) { echo '<meta name="viewport" content="width=device-width, initial-scale=1">'; }
 if ( $preview ) {
  $root = dirname( __DIR__, 3 );
  foreach ( array( $root . '/themes/betheme/css/be.css', dirname( __DIR__ ) . '/assets/css/reseller.css' ) as $css ) {
   echo '<link rel="stylesheet" href="file:///' . esc_attr( str_replace( '\\', '/', $css ) ) . '">';
  }
  echo '<style>body{font-family:Arial,sans-serif}.dashicons{display:inline-block}</style>';
 }
}
function wp_body_open() { echo '<!-- wp_body_open -->'; }
function wp_footer() { echo '<!-- wp_footer -->'; }
function have_posts() { global $loop; return ! $loop; }
function the_post() { global $loop; $loop = true; }
function the_content() { global $content, $content_calls; $content_calls++; echo $content; }
require_once __DIR__ . '/../includes/class-product-manager-resellers.php';
require_once __DIR__ . '/../includes/class-product-manager-reseller-portal.php';
function page( $key, $user = 0 ) {
 global $post, $current, $options, $queried_id, $flags;
 $post = (object) array( 'post_content' => $key ? '[airsoft_reseller_' . $key . ']' : 'Ordinary content' );
 $current = $user; $options = array(); $queried_id = 0; $flags = array();
}
function document( $html ) {
 global $loop, $content, $content_calls;
 $loop = false; $content = $html; $content_calls = 0;
 ob_start(); include AIRSOFT_PRODUCT_MANAGER_DIR . 'templates/reseller-standalone.php'; return ob_get_clean();
}
function balanced_divs( $html ) { return substr_count( $html, '<div' ) === substr_count( $html, '</div>' ); }
Airsoft_Product_Manager_Reseller_Portal::init();
verify( 100 === $hooks['template_include'][0][1], 'Template filter registered at priority 100' );
verify( 3 === count( $shortcodes ), 'All existing reseller shortcodes retained' );
$original = '/theme/page.php';
foreach ( array( 'portal', 'login', 'register' ) as $key ) {
 page( $key );
 verify( $key === Airsoft_Product_Manager_Reseller_Portal::page_key(), 'Shortcode detection: ' . $key );
 verify( AIRSOFT_PRODUCT_MANAGER_DIR . 'templates/reseller-standalone.php' === Airsoft_Product_Manager_Reseller_Portal::standalone_template( $original ), 'Standalone template: ' . $key );
 $styles = $scripts = array(); Airsoft_Product_Manager_Reseller_Portal::assets();
 verify( array( 'airsoft-reseller', 'dashicons' ) === $styles && array( 'wc-account-i18n' ) === $scripts, 'Page assets: ' . $key );
 $post->post_content = 'Saved page without shortcode'; $queried_id = 10; $options['airsoft_reseller_page_' . $key] = 10;
 verify( $key === Airsoft_Product_Manager_Reseller_Portal::page_key(), 'Saved ID detection: ' . $key );
}
page( '' );
verify( '' === Airsoft_Product_Manager_Reseller_Portal::page_key() && $original === Airsoft_Product_Manager_Reseller_Portal::standalone_template( $original ), 'Ordinary page retains theme' );
$styles = $scripts = array(); Airsoft_Product_Manager_Reseller_Portal::assets();
verify( ! $styles && ! $scripts, 'No reseller assets on ordinary page' );
page( 'login' );
foreach ( array( 'admin', 'non_page', 'embed', 'customizer', 'shop', 'cart', 'checkout', 'account', 'endpoint' ) as $flag ) {
 $flags = array( $flag => true );
 verify( $original === Airsoft_Product_Manager_Reseller_Portal::standalone_template( $original ), 'Original template for ' . $flag );
}
$flags = array();
unlink( $sandbox . 'templates/reseller-standalone.php' );
verify( $original === Airsoft_Product_Manager_Reseller_Portal::standalone_template( $original ), 'Missing template falls back safely' );
copy( __DIR__ . '/../templates/reseller-standalone.php', $sandbox . 'templates/reseller-standalone.php' );
foreach ( array( 0, 1, 2, 3, 4 ) as $user ) {
 page( 'portal', $user );
 verify( ( 2 === $user ) === (bool) Airsoft_Product_Manager_Reseller_Portal::dashboard_view(), 'Dashboard landmark decision for user ' . $user );
}
page( 'login', 2 );
verify( ! Airsoft_Product_Manager_Reseller_Portal::dashboard_view(), 'Approved login view does not claim dashboard landmark' );
page( '', 2 ); $options['airsoft_reseller_page_portal'] = 20; $queried_id = 20;
verify( ! Airsoft_Product_Manager_Reseller_Portal::dashboard_view(), 'Stale portal ID without shortcode keeps template landmark' );
foreach ( array( 'portal', 'auth' ) as $context ) {
 ob_start(); Airsoft_Product_Manager_Reseller_Portal::site_header( $context ); $html = ob_get_clean();
 verify( false !== strpos( $html, 'Combat Shooting Zones' ) && false !== strpos( $html, 'portal' === $context ? 'Visit shop' : 'Return to store' ), 'Header identity and action: ' . $context );
 verify( false !== strpos( $html, 'href="' . ( 'portal' === $context ? $shop : home_url( '/' ) ) . '"' ), 'Header destination: ' . $context );
}
$name = '<script>unsafe</script>'; $shop = 'https://example.test/shop/?one=1&two=2';
ob_start(); Airsoft_Product_Manager_Reseller_Portal::site_header(); $html = ob_get_clean();
verify( false === strpos( $html, '<script>' ) && false !== strpos( $html, '&lt;script&gt;' ) && false !== strpos( $html, '&amp;two=' ), 'Header escapes site name and URL attributes' );
$name = ''; $shop = '';
ob_start(); Airsoft_Product_Manager_Reseller_Portal::site_header(); $html = ob_get_clean();
verify( false !== strpos( $html, '>Store</strong>' ) && 2 === substr_count( $html, 'href="https://example.test/"' ), 'Empty identity and shop fall back safely' );
$name = 'Combat Shooting Zones'; $shop = 'https://example.test/shop/';
$previews = array();
foreach ( array( 'login', 'register' ) as $key ) {
 foreach ( array( 0, 1, 2 ) as $user ) {
  page( $key, $user );
  $html = 'login' === $key ? Airsoft_Product_Manager_Reseller_Portal::login() : Airsoft_Product_Manager_Reseller_Portal::registration();
  verify( balanced_divs( $html ) && 1 === substr_count( $html, 'airsoft-portal-topbar' ), 'Auth wrappers and one header: ' . $key . '/' . $user );
  verify( false !== strpos( $html, 0 === $user ? 'airsoft_portal_nonce' : ( 1 === $user ? 'member account' : 'Open Reseller Portal' ) ), 'Auth content retained: ' . $key . '/' . $user );
  $doc = document( $html );
  verify( 1 === $content_calls && 1 === substr_count( $doc, '<main ' ) && 1 === substr_count( $doc, '</main>' ), 'One render and landmark: ' . $key . '/' . $user );
  if ( 0 === $user ) { $previews[$key] = $html; }
 }
}
foreach ( array( 0, 1, 3, 4 ) as $user ) {
 page( 'portal', $user ); $html = Airsoft_Product_Manager_Reseller_Portal::portal(); $doc = document( $html );
 verify( balanced_divs( $html ) && 1 === substr_count( $html, 'airsoft-portal-topbar' ) && false !== strpos( $html, 'Return to store' ), 'Restricted portal header: ' . $user );
 verify( 1 === substr_count( $doc, '<main ' ) && false === strpos( $doc, 'airsoft-portal-sidebar' ), 'Restricted portal has one main and no seller sidebar: ' . $user );
 if ( $user >= 3 ) { verify( false !== strpos( $html, 4 === $user ? 'rejected' : 'pending' ), 'Applicant status retained: ' . $user ); }
 $previews['restricted-' . $user] = $html;
}
page( 'portal', 2 ); $doc = document( '<main class="airsoft-portal-main">Dashboard</main>' );
verify( 1 === $content_calls && 1 === substr_count( $doc, '<main ' ) && false !== strpos( $doc, 'airsoft-standalone-content' ), 'Template does not wrap dashboard main in another main' );
verify( false !== strpos( $doc, '<html lang="en">' ) && false !== strpos( $doc, 'charset="UTF-8"' ) && false !== strpos( $doc, 'class="airsoft-reseller-standalone airsoft-reseller-dashboard"' ), 'Document language, charset and approved workspace body classes present' );
foreach ( array( 'wp_head', 'wp_body_open', 'wp_footer' ) as $marker ) { verify( 1 === substr_count( $doc, '<!-- ' . $marker . ' -->' ), 'Required hook runs once: ' . $marker ); }
page( 'login' );
verify( false === strpos( document( $previews['login'] ), 'airsoft-reseller-dashboard' ), 'Login retains auth-card spacing instead of approved workspace layout' );
foreach ( array( false, true ) as $responsive ) {
 foreach ( array( false, true ) as $meta_hook ) {
  $doc = document( $previews['login'] );
  verify( 1 === substr_count( $doc, 'name="viewport"' ), 'Exactly one complete-document viewport: responsive=' . (int) $responsive . ', hook=' . (int) $meta_hook );
 }
}
$error = new ReflectionProperty( 'Airsoft_Product_Manager_Reseller_Portal', 'error' ); $error->setAccessible( true );
$error->setValue( null, 'Unable to sign in. Check your credentials and try again.' );
$html = Airsoft_Product_Manager_Reseller_Portal::login();
verify( false !== strpos( $html, 'role="alert"' ) && false !== strpos( $html, 'Unable to sign in' ), 'Login error retains alert semantics' );
$previews['login-error'] = $html; $error->setValue( null, '' );
if ( ! empty( $argv[1] ) ) {
 if ( ! is_dir( $argv[1] ) ) { throw new Exception( 'Preview destination must be an existing directory.' ); }
 $preview = true;
 foreach ( $previews as $key => $html ) { page( 0 === strpos( $key, 'restricted-' ) ? 'portal' : ( 'register' === $key ? 'register' : 'login' ) ); file_put_contents( $argv[1] . '/' . $key . '.html', document( $html ) ); }
 $name = str_repeat( 'VeryLongStoreName', 8 ); page( 'login' );
 file_put_contents( $argv[1] . '/long-name.html', document( Airsoft_Product_Manager_Reseller_Portal::login() ) );
}
echo "PASS: $checks reseller standalone checks\n";
