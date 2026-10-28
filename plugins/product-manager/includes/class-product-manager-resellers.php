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
  try {
   if ( self::approved() ) {
    if ( 'save_product' === $action ) { Airsoft_Product_Manager_Reseller_Products::save(); }
    elseif ( 'ship' === $action ) { Airsoft_Product_Manager_Reseller_Orders::ship(); }
   } else { throw new Exception( 'Reseller approval is required.' ); }
  } catch ( Exception $e ) { wc_add_notice( $e->getMessage(), 'error' ); }
  wp_safe_redirect( 'ship' === $action ? add_query_arg( 'section', 'sales', self::url() ) : self::url() );
  exit;
 }

 public static function admin_menu() {
  add_submenu_page( Airsoft_Product_Manager_Category_Page::PAGE_SLUG, 'Resellers', 'Resellers', 'manage_woocommerce', 'airsoft-resellers', array( __CLASS__, 'admin_page' ) );
 }

 public static function admin_page() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'You are not allowed to review reseller applications.' ); }
  echo '<div class="wrap"><h1>Reseller applications</h1>';
  echo '<p>Resellers register separately using a different email from their member account.</p><p><a class="button" href="' . esc_url( Airsoft_Product_Manager_Reseller_Portal::url( 'register' ) ) . '">Open reseller registration</a> <a class="button" href="' . esc_url( self::url() ) . '">Open Reseller Portal</a></p>';
  $page = max( 1, absint( $_GET['paged'] ?? 1 ) );
  $query = new WP_User_Query( array( 'meta_key' => '_airsoft_reseller_application', 'meta_compare' => 'EXISTS', 'number' => 25, 'paged' => $page, 'orderby' => 'ID', 'order' => 'DESC' ) );
  $applications = $query->get_results();
  if ( ! $applications ) {
   if ( $page > 1 ) {
    echo '<p>No applications on this page. <a href="' . esc_url( admin_url( 'admin.php?page=airsoft-resellers' ) ) . '">Return to the first page</a>.</p>';
   } else {
    echo '<div class="notice notice-info inline"><p>No reseller applications yet. Applicants must create a separate account on the reseller registration page.</p></div>';
   }
  }
  foreach ( $applications as $user ) {
   echo '<hr><h2>' . esc_html( $user->display_name ) . '</h2><p>' . esc_html( $user->user_email ) . '</p><p>' . esc_html( get_user_meta( $user->ID, '_airsoft_reseller_business', true ) ) . '</p><p>' . esc_html( get_user_meta( $user->ID, '_airsoft_reseller_details', true ) ) . '</p><p>Status: ' . esc_html( get_user_meta( $user->ID, '_airsoft_reseller_application', true ) ) . '</p>';
   echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="airsoft_reseller_review"><input type="hidden" name="user_id" value="' . absint( $user->ID ) . '">';
   wp_nonce_field( 'airsoft_review_' . $user->ID );
   if ( Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $user->ID ) ) {
    echo '<button name="decision" value="approved">Approve</button> <button name="decision" value="rejected">Reject / revoke</button>';
   } else { echo '<p>This is a legacy member application. Ask this applicant to register a separate reseller account; the member account will be preserved.</p>'; }
   echo '</form>';
  }
  echo wp_kses_post( paginate_links( array( 'total' => ceil( $query->get_total() / 25 ), 'current' => $page ) ) );
  echo '</div>';
 }

 public static function review() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Access denied.' ); }
  $id = absint( $_POST['user_id'] ?? 0 );
  check_admin_referer( 'airsoft_review_' . $id );
  $user = get_userdata( $id );
  $decision = sanitize_key( $_POST['decision'] ?? '' );
  if ( ! $user || ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $id ) || ! in_array( $decision, array( 'approved', 'rejected' ), true ) || ! get_user_meta( $id, '_airsoft_reseller_application', true ) ) { wp_die( 'A separate reseller account is required.' ); }
  Airsoft_Product_Manager_Reseller_Portal::set_status( $user, $decision );
  update_user_meta( $id, '_airsoft_reseller_application', $decision );
  Airsoft_Product_Manager_Reseller_Emails::send( $user->user_email, 'Reseller application update', 'Your reseller application status: ' . $decision );
  wp_safe_redirect( admin_url( 'admin.php?page=airsoft-resellers' ) ); exit;
 }
}
