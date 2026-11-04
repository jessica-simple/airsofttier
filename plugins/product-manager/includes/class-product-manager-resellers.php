<?php
/** Reseller approval and legacy account endpoint compatibility. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Resellers {
 const ROLE = 'airsoft_reseller';

 public static function init() {
  add_action( 'init', array( __CLASS__, 'register' ) );
  add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
  add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu' ) );
  add_action( 'woocommerce_account_reseller_endpoint', array( __CLASS__, 'dashboard' ) );
  add_action( 'template_redirect', array( __CLASS__, 'handle' ) );
  add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
  add_action( 'admin_post_airsoft_reseller_review', array( __CLASS__, 'review' ) );
 }

 public static function register() {
  add_role( self::ROLE, 'Reseller', array( 'read' => true ) );
  add_rewrite_endpoint( 'reseller', EP_ROOT | EP_PAGES );
  if ( '2' !== get_option( 'airsoft_reseller_routes' ) ) {
   flush_rewrite_rules( false );
   update_option( 'airsoft_reseller_routes', '2', false );
  }
 }

 public static function query_vars( $vars ) {
  $vars['reseller'] = 'reseller';
  return $vars;
 }

 public static function approved( $user_id = 0 ) {
  $user = get_userdata( $user_id ? $user_id : get_current_user_id() );
  return $user && in_array( self::ROLE, (array) $user->roles, true );
 }

 public static function url() { return Airsoft_Product_Manager_Reseller_Portal::url(); }
 public static function menu( $items ) {
  unset( $items['reseller'] );
  return $items;
 }

 public static function field( $label, $name, $value = '', $type = 'text', $attributes = '' ) {
  echo '<p><label>' . esc_html( $label ) . '<br><input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" ' . $attributes . '></label></p>';
 }

 public static function dashboard() {
  echo '<p>Reseller accounts now have a separate portal. <a href="' . esc_url( self::url() ) . '">Open Reseller Portal</a>.</p>';
 }

 public static function handle() {
  if ( empty( $_POST['airsoft_action'] ) || ! is_user_logged_in() || ! Airsoft_Product_Manager_Reseller_Portal::is_page( 'portal' ) ) { return; }
  if ( ! isset( $_POST['airsoft_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['airsoft_nonce'] ) ), 'airsoft_reseller' ) ) { wp_die( 'Invalid request.' ); }
  $action = sanitize_key( wp_unslash( $_POST['airsoft_action'] ) );
  $failed = false;
  try {
   if ( self::approved() ) {
    if ( 'save_product' === $action ) { Airsoft_Product_Manager_Reseller_Products::save(); }
    elseif ( 'ship' === $action ) { Airsoft_Product_Manager_Reseller_Orders::ship(); }
    elseif ( 'order_status' === $action ) { Airsoft_Product_Manager_Reseller_Orders::request_status(); }
    elseif ( 'return_request' === $action ) { Airsoft_Product_Manager_Reseller_Orders::request_return(); }
    elseif ( 'withdraw' === $action ) { Airsoft_Product_Manager_Reseller_Orders::withdraw(); }
    elseif ( 'save_bank_details' === $action ) { Airsoft_Product_Manager_Reseller_Orders::save_bank_details(); }
   } else { throw new Exception( 'Reseller approval is required.' ); }
  } catch ( Exception $e ) {
   $failed = true;
   wc_add_notice( $e->getMessage(), 'error' );
   if ( 'save_product' === $action ) { Airsoft_Product_Manager_Reseller_Products::remember_form_input(); }
  }
  $destination = in_array( $action, array( 'ship', 'order_status', 'return_request' ), true ) ? array( 'section' => 'sales', 'order_id' => absint( $_POST['order_id'] ?? 0 ) ) : ( 'withdraw' === $action ? array( 'section' => 'earnings' ) : ( 'save_bank_details' === $action ? array( 'section' => 'bank-details' ) : ( 'save_product' === $action && $failed ? array( 'section' => 'product-edit', 'edit_product' => absint( $_POST['product_id'] ?? 0 ) ) : array( 'section' => 'products' ) ) ) );
  wp_safe_redirect( add_query_arg( $destination, self::url() ) );
  exit;
 }

 public static function admin_menu() {
  $hook = add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'Resellers', 'Resellers', 'manage_woocommerce', 'airsoft-resellers', array( __CLASS__, 'admin_page' ) );
  add_action( 'load-' . $hook, array( __CLASS__, 'table_actions' ) );
 }

 public static function list_filters( $input ) {
  $status = sanitize_key( wp_unslash( $input['application_status'] ?? '' ) );
  $orderby = sanitize_key( wp_unslash( $input['orderby'] ?? 'registered' ) );
  return array(
   'application_status' => in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ? $status : '',
   's' => sanitize_text_field( wp_unslash( $input['s'] ?? '' ) ),
   'paged' => max( 1, absint( $input['paged'] ?? 1 ) ),
   'orderby' => in_array( $orderby, array( 'login', 'display_name', 'email', 'registered' ), true ) ? $orderby : 'registered',
   'order' => 'asc' === strtolower( (string) ( $input['order'] ?? '' ) ) ? 'asc' : 'desc',
  );
 }

 public static function application_query( $filters ) {
  $meta = array( array( 'key' => '_airsoft_reseller_application', 'compare' => 'EXISTS' ) );
  if ( $filters['application_status'] ) { $meta[] = array( 'key' => '_airsoft_reseller_application', 'value' => $filters['application_status'], 'compare' => '=' ); }
  $args = array( 'meta_query' => $meta, 'number' => 25, 'paged' => $filters['paged'], 'orderby' => $filters['orderby'], 'order' => strtoupper( $filters['order'] ), 'count_total' => true );
  if ( $filters['s'] ) {
   $args['search'] = '*' . $filters['s'] . '*';
   $args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
  }
  return $args;
 }

 public static function admin_page() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'You are not allowed to review reseller applications.' ); }
  require_once __DIR__ . '/class-product-manager-reseller-applications-table.php';
  $filters = self::list_filters( $_GET );
  $table = new Airsoft_Product_Manager_Reseller_Applications_Table();
  $table->prepare_items();
  echo '<div class="wrap"><h1 class="wp-heading-inline">Reseller applications</h1> <a class="page-title-action" href="' . esc_url( Airsoft_Product_Manager_Reseller_Portal::url( 'register' ) ) . '">Open reseller registration</a> <a class="page-title-action" href="' . esc_url( self::url() ) . '">Open Reseller Portal</a><hr class="wp-header-end">';
  if ( isset( $_GET['reviewed'] ) ) {
   echo '<div class="notice notice-success is-dismissible"><p>' . absint( $_GET['reviewed'] ) . ' application(s) updated. ' . absint( $_GET['skipped'] ?? 0 ) . ' skipped.</p></div>';
   if ( ! empty( $_GET['mail_failed'] ) ) { echo '<div class="notice notice-warning"><p>' . absint( $_GET['mail_failed'] ) . ' update email(s) could not be handed to the mail function. Account changes were saved.</p></div>'; }
  }
  echo '<p>Review separate reseller accounts. Search by username, name or email. Legacy member applications require a new reseller registration.</p>';
  $table->views();
  echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="airsoft-resellers"><input type="hidden" name="application_status" value="' . esc_attr( $filters['application_status'] ) . '"><input type="hidden" name="orderby" value="' . esc_attr( $filters['orderby'] ) . '"><input type="hidden" name="order" value="' . esc_attr( $filters['order'] ) . '">';
  $table->search_box( 'Search applications', 'reseller-applications' );
  echo '</form><form method="post" action="' . esc_url( add_query_arg( 'page', 'airsoft-resellers', admin_url( 'admin.php' ) ) ) . '">';
  foreach ( $filters as $key => $value ) { echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">'; }
  $table->display();
  echo '</form>';
  echo '</div>';
 }

 public static function table_actions() {
  if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { return; }
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $decision = sanitize_key( wp_unslash( $_POST['action'] ?? '' ) );
  $ids = (array) ( $_POST['application_ids'] ?? array() );
  if ( ! empty( $_POST['review_application'] ) ) {
   $row = explode( ':', sanitize_text_field( wp_unslash( $_POST['review_application'] ) ), 2 );
   $decision = $row[0]; $ids = array( $row[1] ?? 0 );
  } elseif ( ! in_array( $decision, array( 'approved', 'rejected' ), true ) ) { $decision = sanitize_key( wp_unslash( $_POST['action2'] ?? '' ) ); }
  if ( ! in_array( $decision, array( 'approved', 'rejected' ), true ) ) {
   // Native list-table pagination submits the current-page field on Enter.
   wp_safe_redirect( add_query_arg( array_merge( array( 'page' => 'airsoft-resellers' ), self::list_filters( $_POST ) ), admin_url( 'admin.php' ) ) ); exit;
  }
  check_admin_referer( 'bulk-airsoft_reseller_applications' );
  $result = self::review_users( $ids, $decision );
  $args = array_merge( array( 'page' => 'airsoft-resellers' ), self::list_filters( $_POST ), $result );
  wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) ); exit;
 }

 public static function review_users( $ids, $decision ) {
  if ( ! current_user_can( 'manage_woocommerce' ) || ! in_array( $decision, array( 'approved', 'rejected' ), true ) ) { wp_die( 'Access denied.' ); }
  $result = array( 'reviewed' => 0, 'skipped' => 0, 'mail_failed' => 0 );
  $ids = array_slice( array_unique( array_map( 'absint', (array) $ids ) ), 0, 100 );
  foreach ( $ids as $id ) {
   $user = get_userdata( $id );
   $status = get_user_meta( $id, '_airsoft_reseller_application', true );
   if ( ! $user || ! $status || ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $id ) || ( $status === $decision && self::approved( $id ) === ( 'approved' === $decision ) ) ) { $result['skipped']++; continue; }
   Airsoft_Product_Manager_Reseller_Portal::set_status( $user, $decision );
   update_user_meta( $id, '_airsoft_reseller_application', $decision );
   $result['reviewed']++;
   if ( ! Airsoft_Product_Manager_Reseller_Emails::send( $user->user_email, 'Reseller application update', 'Your reseller application status: ' . $decision ) ) { $result['mail_failed']++; }
  }
  return $result;
 }

 public static function review() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['user_id'] ?? 0 );
  check_admin_referer( 'airsoft_review_' . $id );
  $user = get_userdata( $id );
  $decision = sanitize_key( $_POST['decision'] ?? '' );
  if ( ! $user || ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $id ) || ! in_array( $decision, array( 'approved', 'rejected' ), true ) || ! get_user_meta( $id, '_airsoft_reseller_application', true ) ) { wp_die( 'A separate reseller account is required.' ); }
  self::review_users( array( $id ), $decision );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-resellers' ) ); exit;
 }
}
