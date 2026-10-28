<?php
/** Separate reseller identity, registration, sign-in and customer tools. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Reseller_Portal {
 const APPLICANT_ROLE = 'airsoft_reseller_applicant';
 private static $error = '';

 public static function init() {
  add_action( 'init', array( __CLASS__, 'roles' ) );
  add_action( 'admin_init', array( __CLASS__, 'pages' ) );
  add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 100 );
  add_action( 'template_redirect', array( __CLASS__, 'route' ), 2 );
  add_action( 'wp_loaded', array( __CLASS__, 'address_context' ), 5 );
  add_action( 'wp_login', array( __CLASS__, 'normalize_legacy_account' ), 10, 2 );
  add_action( 'woocommerce_before_customer_login_form', array( __CLASS__, 'entry_links' ) );
  add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'can_purchase' ), 40, 2 );
  add_filter( 'woocommerce_variation_is_purchasable', array( __CLASS__, 'can_purchase' ), 40, 2 );
  add_filter( 'woocommerce_process_login_errors', array( __CLASS__, 'member_login' ), 30, 2 );
  add_filter( 'woocommerce_login_redirect', array( __CLASS__, 'login_redirect' ), 99, 2 );
  add_filter( 'login_redirect', array( __CLASS__, 'wp_login_redirect' ), 99, 3 );
  add_filter( 'woocommerce_get_endpoint_url', array( __CLASS__, 'endpoint_url' ), 30, 4 );
  add_shortcode( 'airsoft_reseller_register', array( __CLASS__, 'registration' ) );
  add_shortcode( 'airsoft_reseller_login', array( __CLASS__, 'login' ) );
  add_shortcode( 'airsoft_reseller_portal', array( __CLASS__, 'portal' ) );
 }

 public static function roles() { add_role( self::APPLICANT_ROLE, 'Reseller applicant', array( 'read' => true ) ); }

 public static function assets() {
  if ( ! self::is_page( 'login' ) && ! self::is_page( 'register' ) && ! self::is_page( 'portal' ) ) { return; }
  wp_enqueue_style( 'airsoft-reseller', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/css/reseller.css', array(), filemtime( AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/css/reseller.css' ) );
 }

 public static function can_purchase( $allowed, $product ) {
  return self::is_reseller_account() && ! Airsoft_Product_Manager_Resellers::approved() ? false : $allowed;
 }
 public static function entry_links() {
  echo '<p>Reseller? <a href="' . esc_url( self::url( 'login' ) ) . '">Reseller login</a> · <a href="' . esc_url( self::url( 'register' ) ) . '">Create a separate reseller account</a></p>';
 }

 public static function pages() {
  if ( ! current_user_can( 'manage_options' ) || '1' === get_option( 'airsoft_reseller_portal_pages' ) ) { return; }
  $complete = true;
  foreach ( array( 'register' => 'Reseller Registration', 'login' => 'Reseller Login', 'portal' => 'Reseller Portal' ) as $key => $title ) {
   $id = absint( get_option( 'airsoft_reseller_page_' . $key ) );
   if ( $id && 'publish' === get_post_status( $id ) ) { continue; }
   $shortcode = 'airsoft_reseller_' . ( 'register' === $key ? 'register' : $key );
   // Never overwrite a site's existing page with the same title or slug.
   $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => 'reseller-' . ( 'register' === $key ? 'registration' : $key ), 'post_content' => '[' . $shortcode . ']' ), true );
   if ( is_wp_error( $id ) || ! $id ) { $complete = false; continue; }
   update_option( 'airsoft_reseller_page_' . $key, $id, false );
  }
  if ( $complete ) { update_option( 'airsoft_reseller_portal_pages', '1', false ); }
 }

 public static function url( $key = 'portal' ) {
  $id = absint( get_option( 'airsoft_reseller_page_' . $key ) );
  return $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : home_url( '/reseller-' . ( 'register' === $key ? 'registration' : $key ) . '/' );
 }

 public static function is_page( $key ) {
  $id = absint( get_option( 'airsoft_reseller_page_' . $key ) );
  $post = get_post();
  $tag = 'airsoft_reseller_' . $key;
  return ( $id && is_page( $id ) ) || ( $post && has_shortcode( $post->post_content, $tag ) );
 }

 public static function is_reseller_account( $id = 0 ) {
  $id = $id ? $id : get_current_user_id();
  $user = get_userdata( $id );
  return $user && ( 'reseller' === get_user_meta( $id, '_airsoft_account_type', true ) || in_array( Airsoft_Product_Manager_Resellers::ROLE, (array) $user->roles, true ) || in_array( self::APPLICANT_ROLE, (array) $user->roles, true ) );
 }

 /** Preserve legacy IDs, products and orders; remove only member-facing roles. */
 public static function normalize_legacy_account( $login, $user ) {
  if ( ! self::is_reseller_account( $user->ID ) ) { return; }
  update_user_meta( $user->ID, '_airsoft_account_type', 'reseller' );
  $user->remove_role( 'customer' );
  $user->remove_role( 'subscriber' );
 }

 public static function set_status( $user, $status ) {
  $user->remove_role( 'customer' );
  $user->remove_role( 'subscriber' );
  $user->remove_role( Airsoft_Product_Manager_Resellers::ROLE );
  $user->remove_role( self::APPLICANT_ROLE );
  $user->add_role( 'approved' === $status ? Airsoft_Product_Manager_Resellers::ROLE : self::APPLICANT_ROLE );
  update_user_meta( $user->ID, '_airsoft_account_type', 'reseller' );
 }

 public static function member_login( $errors, $login ) {
  $user = get_user_by( is_email( $login ) ? 'email' : 'login', $login );
  if ( $user && self::is_reseller_account( $user->ID ) ) { $errors->add( 'reseller_login', 'Please use the separate Reseller Login page for this account.' ); }
  return $errors;
 }
 public static function login_redirect( $redirect, $user ) { return $user && self::is_reseller_account( $user->ID ) ? self::url() : $redirect; }
 public static function wp_login_redirect( $redirect, $requested, $user ) { return is_wp_error( $user ) ? $redirect : self::login_redirect( $redirect, $user ); }

 public static function route() {
  $portal_page = self::is_page( 'portal' );
  $login_page = self::is_page( 'login' );
  $register_page = self::is_page( 'register' );
  if ( $portal_page || $login_page || $register_page ) {
   if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
   nocache_headers();
  }
  if ( is_user_logged_in() && self::is_reseller_account() && is_account_page() && ! is_wc_endpoint_url( 'lost-password' ) && ! is_wc_endpoint_url( 'customer-logout' ) ) {
   $target = self::url();
   if ( is_wc_endpoint_url( 'notifications' ) ) { $target = add_query_arg( 'section', 'notifications', $target ); }
   elseif ( is_wc_endpoint_url( 'view-order' ) ) {
    global $wp;
    $target = add_query_arg( array( 'section' => 'purchases', 'view_order' => absint( $wp->query_vars['view-order'] ?? 0 ) ), $target );
   }
   wp_safe_redirect( $target ); exit;
  }
  if ( is_account_page() && is_wc_endpoint_url( 'reseller' ) ) {
   wp_safe_redirect( self::url( is_user_logged_in() && ! self::is_reseller_account() ? 'register' : 'login' ) ); exit;
  }
  if ( ! $portal_page && ! $login_page && ! $register_page ) { return; }
  if ( $portal_page && ! is_user_logged_in() ) { wp_safe_redirect( self::url( 'login' ) ); exit; }
  if ( is_user_logged_in() ) {
   if ( self::is_reseller_account() && ( $login_page || $register_page ) ) { wp_safe_redirect( self::url() ); exit; }
   // Members may read the explanation but cannot submit using their identity.
   return;
  }
  $action = sanitize_key( wp_unslash( $_POST['airsoft_portal_action'] ?? '' ) );
  if ( ! $action ) { return; }
  if ( ! isset( $_POST['airsoft_portal_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['airsoft_portal_nonce'] ) ), 'airsoft_portal' ) ) { self::$error = 'Your session expired. Refresh the page and try again.'; return; }
  if ( 'register' === $action && $register_page ) {
   $result = self::create_account( $_POST );
   if ( is_wp_error( $result ) ) { self::$error = $result->get_error_message(); return; }
   wp_safe_redirect( add_query_arg( 'submitted', '1', self::url( 'login' ) ) ); exit;
  }
  if ( 'login' === $action && $login_page ) {
   $login = sanitize_text_field( wp_unslash( $_POST['reseller_login'] ?? '' ) );
   $user = get_user_by( is_email( $login ) ? 'email' : 'login', $login );
   if ( ! $user || ! self::is_reseller_account( $user->ID ) ) { self::$error = 'Use your reseller account credentials. Member accounts sign in through My Account.'; return; }
   // WordPress authentication filters (including installed login protection) run.
   $result = wp_signon( array( 'user_login' => $user->user_login, 'user_password' => (string) wp_unslash( $_POST['reseller_password'] ?? '' ), 'remember' => ! empty( $_POST['remember'] ) ), is_ssl() );
   if ( is_wp_error( $result ) ) { self::$error = 'Unable to sign in. Check your credentials and try again.'; return; }
   wp_safe_redirect( self::url() ); exit;
  }
 }

 public static function create_account( $input ) {
  if ( is_user_logged_in() ) { return new WP_Error( 'separate_account', 'Log out before creating a separate reseller account.' ); }
  $email = sanitize_email( wp_unslash( $input['email'] ?? '' ) );
  $business = sanitize_text_field( wp_unslash( $input['business'] ?? '' ) );
  $details = sanitize_textarea_field( wp_unslash( $input['details'] ?? '' ) );
  $password = (string) wp_unslash( $input['password'] ?? '' );
  $confirm = (string) wp_unslash( $input['password_confirm'] ?? '' );
  if ( ! is_email( $email ) || ! $business || ! $details || strlen( $business ) > 150 || strlen( $details ) > 2000 ) { return new WP_Error( 'fields', 'Enter a valid email, business name and product details.' ); }
  if ( email_exists( $email ) ) { return new WP_Error( 'email_exists', 'This email already has an account. Use a different email for a separate reseller account, or sign in to your existing reseller account.' ); }
  if ( strlen( $password ) < 12 || $password !== $confirm ) { return new WP_Error( 'password', 'Use a password of at least 12 characters and enter the same password twice.' ); }
  // Respect registration validation provided by security and anti-spam plugins.
  $login = 'reseller_' . wp_generate_password( 16, false );
  $errors = apply_filters( 'registration_errors', new WP_Error(), $login, $email );
  if ( $errors->has_errors() ) { return $errors; }
  $id = wp_insert_user( array( 'user_login' => $login, 'user_email' => $email, 'user_pass' => $password, 'display_name' => $business, 'role' => self::APPLICANT_ROLE, 'meta_input' => array( '_airsoft_account_type' => 'reseller', '_airsoft_reseller_application' => 'pending', '_airsoft_reseller_business' => $business, '_airsoft_reseller_details' => $details ) ) );
  if ( is_wp_error( $id ) ) { return $id; }
  Airsoft_Product_Manager_Reseller_Emails::send( get_option( 'admin_email' ), 'New reseller application', 'Review the separate reseller account under Product Manager > Resellers.' );
  Airsoft_Product_Manager_Reseller_Emails::send( $email, 'Reseller application received', 'Your application is pending approval. Sign in using your email at ' . self::url( 'login' ) );
  return $id;
 }

 private static function member_notice() {
  if ( ! is_user_logged_in() || self::is_reseller_account() ) { return false; }
  echo '<p>You are signed in to a member account. Resellers use a separate account and email address.</p><p><a href="' . esc_url( wp_logout_url( self::url( 'register' ) ) ) . '">Log out to register or sign in as a reseller</a></p>';
  return true;
 }
 private static function error() { if ( self::$error ) { echo '<p role="alert">' . esc_html( self::$error ) . '</p>'; } }

 public static function registration() {
  ob_start(); echo '<div class="woocommerce airsoft-reseller-registration"><h2>Reseller registration</h2>';
  if ( self::member_notice() ) { echo '</div>'; return ob_get_clean(); }
  if ( is_user_logged_in() ) { echo '<p><a href="' . esc_url( self::url() ) . '">Open Reseller Portal</a></p></div>'; return ob_get_clean(); }
  self::error();
  echo '<p>Create a separate reseller account using an email that is not registered to a member account. Admin approval is required before buying at reseller prices or selling.</p><form method="post">';
  wp_nonce_field( 'airsoft_portal', 'airsoft_portal_nonce' );
  Airsoft_Product_Manager_Resellers::field( 'Email', 'email', '', 'email', 'required autocomplete="email"' );
  Airsoft_Product_Manager_Resellers::field( 'Business / trading name', 'business', '', 'text', 'required maxlength="150"' );
  echo '<p><label>Tell us about your products<br><textarea name="details" required maxlength="2000"></textarea></label></p>';
  Airsoft_Product_Manager_Resellers::field( 'Password (at least 12 characters)', 'password', '', 'password', 'required minlength="12" autocomplete="new-password"' );
  Airsoft_Product_Manager_Resellers::field( 'Confirm password', 'password_confirm', '', 'password', 'required minlength="12" autocomplete="new-password"' );
  echo '<button name="airsoft_portal_action" value="register">Submit application</button></form><p><a href="' . esc_url( self::url( 'login' ) ) . '">Already registered? Reseller login</a></p></div>';
  return ob_get_clean();
 }
 public static function login() {
  ob_start(); echo '<div class="woocommerce airsoft-reseller-login"><h2>Reseller login</h2>';
  if ( self::member_notice() ) { echo '</div>'; return ob_get_clean(); }
  if ( is_user_logged_in() ) { echo '<p><a href="' . esc_url( self::url() ) . '">Open Reseller Portal</a></p></div>'; return ob_get_clean(); }
  self::error();
  if ( isset( $_GET['submitted'] ) ) { echo '<p>Your application was submitted. Sign in to check its status.</p>'; }
  echo '<form method="post">'; wp_nonce_field( 'airsoft_portal', 'airsoft_portal_nonce' );
  Airsoft_Product_Manager_Resellers::field( 'Reseller email', 'reseller_login', '', 'email', 'required autocomplete="username"' );
  Airsoft_Product_Manager_Resellers::field( 'Password', 'reseller_password', '', 'password', 'required autocomplete="current-password"' );
  echo '<p><label><input type="checkbox" name="remember" value="1"> Remember me</label></p><button name="airsoft_portal_action" value="login">Log in</button></form><p><a href="' . esc_url( wp_lostpassword_url( self::url( 'login' ) ) ) . '">Forgot password?</a> · <a href="' . esc_url( self::url( 'register' ) ) . '">Register a reseller account</a></p></div>';
  return ob_get_clean();
 }

 public static function portal() {
  ob_start(); echo '<div class="woocommerce airsoft-reseller-portal"><h2>Reseller Portal</h2>';
  if ( ! is_user_logged_in() ) { echo '<p><a href="' . esc_url( self::url( 'login' ) ) . '">Sign in as a reseller</a></p></div>'; return ob_get_clean(); }
  if ( self::member_notice() ) { echo '</div>'; return ob_get_clean(); }
  wc_print_notices();
  echo '<p><a href="' . esc_url( wp_logout_url( self::url( 'login' ) ) ) . '">Log out</a></p>';
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) {
   $status = get_user_meta( get_current_user_id(), '_airsoft_reseller_application', true );
   echo '<p>Application status: ' . esc_html( $status ? $status : 'Awaiting review' ) . '. Reseller purchasing and selling require admin approval.</p></div>'; return ob_get_clean();
  }
  $section = sanitize_key( wp_unslash( $_GET['section'] ?? 'products' ) );
  $tabs = array( 'products' => 'My products', 'sales' => 'Sales and earnings', 'purchases' => 'My purchases', 'billing' => 'Billing address', 'shipping' => 'Shipping address', 'profile' => 'Account details' );
  if ( class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { $tabs['notifications'] = 'Notifications'; }
  echo '<nav aria-label="Reseller pages">';
  foreach ( $tabs as $key => $label ) { echo '<a style="display:inline-block;margin:0 1em 1em 0" href="' . esc_url( add_query_arg( 'section', $key, self::url() ) ) . '"' . ( $key === $section ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>'; }
  echo '</nav>';
  if ( 'sales' === $section ) { Airsoft_Product_Manager_Reseller_Orders::render(); }
  elseif ( 'purchases' === $section ) { self::purchases(); }
  elseif ( in_array( $section, array( 'billing', 'shipping' ), true ) ) { WC_Shortcode_My_Account::edit_address( $section ); }
  elseif ( 'profile' === $section ) { WC_Shortcode_My_Account::edit_account(); }
  elseif ( 'notifications' === $section && class_exists( 'Airsoft_Product_Manager_Notifications' ) ) { Airsoft_Product_Manager_Notifications::render(); }
  else { Airsoft_Product_Manager_Reseller_Products::render(); }
  echo '</div>'; return ob_get_clean();
 }

 private static function purchases() {
  $id = absint( $_GET['view_order'] ?? 0 );
  if ( $id ) {
   $order = wc_get_order( $id );
   if ( ! $order || (int) $order->get_customer_id() !== get_current_user_id() ) { echo '<p>This purchase is unavailable.</p>'; return; }
   WC_Shortcode_My_Account::view_order( $id ); return;
  }
  $page = max( 1, absint( $_GET['purchase_page'] ?? 1 ) );
  $result = wc_get_orders( array( 'customer_id' => get_current_user_id(), 'type' => 'shop_order', 'limit' => 20, 'page' => $page, 'paginate' => true ) );
  echo '<h3>My purchases</h3><ul>';
  foreach ( $result->orders as $order ) {
   echo '<li><a href="' . esc_url( add_query_arg( array( 'section' => 'purchases', 'view_order' => $order->get_id() ), self::url() ) ) . '">Order #' . esc_html( $order->get_order_number() ) . '</a> — ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . ' — ' . wp_kses_post( $order->get_formatted_order_total() ) . '</li>';
  }
  echo '</ul>';
  if ( ! $result->orders ) { echo '<p>No purchases yet.</p>'; }
  if ( $page > 1 ) { echo '<a href="' . esc_url( add_query_arg( array( 'section' => 'purchases', 'purchase_page' => $page - 1 ), self::url() ) ) . '">Previous</a> '; }
  if ( $page < $result->max_num_pages ) { echo '<a href="' . esc_url( add_query_arg( array( 'section' => 'purchases', 'purchase_page' => $page + 1 ), self::url() ) ) . '">Next</a>'; }
 }

 /** Keep WooCommerce's validated profile/address forms inside the reseller portal. */
 public static function endpoint_url( $url, $endpoint, $value, $permalink ) {
  if ( ! is_user_logged_in() || ! self::is_reseller_account() ) { return $url; }
  $sections = array( 'dashboard' => 'products', 'orders' => 'purchases', 'view-order' => 'purchases', 'edit-address' => 'billing', 'edit-account' => 'profile', 'reseller' => 'products' );
  if ( ! isset( $sections[ $endpoint ] ) ) { return $url; }
  $args = array( 'section' => $sections[ $endpoint ] );
  if ( 'view-order' === $endpoint ) { $args['view_order'] = absint( $value ); }
  if ( 'edit-address' === $endpoint && in_array( $value, array( 'billing', 'shipping' ), true ) ) { $args['section'] = $value; }
  return add_query_arg( $args, self::url() );
 }
 public static function address_context() {
  if ( ! is_user_logged_in() || ! self::is_reseller_account() || 'edit_address' !== ( $_POST['action'] ?? '' ) ) { return; }
  $section = sanitize_key( wp_unslash( $_GET['section'] ?? '' ) );
  if ( ! in_array( $section, array( 'billing', 'shipping' ), true ) ) { return; }
  $nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce-edit-address-nonce'] ?? '' ) );
  if ( ! wp_verify_nonce( $nonce, 'woocommerce-edit_address' ) ) { return; }
  global $wp;
  $wp->query_vars['edit-address'] = $section;
 }
}
