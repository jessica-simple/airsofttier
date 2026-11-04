<?php
/** Native WordPress list table for reseller applications. */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
 require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Airsoft_Product_Manager_Reseller_Applications_Table extends WP_List_Table {
 private $filters;

 public function __construct() {
  parent::__construct( array( 'singular' => 'reseller_application', 'plural' => 'airsoft_reseller_applications', 'ajax' => false ) );
  $this->filters = Airsoft_Product_Manager_Resellers::list_filters( $_GET );
 }

 public function get_columns() {
  return array( 'cb' => '<input type="checkbox">', 'username' => 'Username', 'name' => 'Name', 'email' => 'Email', 'business' => 'Business / application', 'status' => 'Status', 'registered' => 'Registered', 'actions' => 'Actions' );
 }

 protected function get_sortable_columns() {
  return array( 'username' => array( 'login', false ), 'name' => array( 'display_name', false ), 'email' => array( 'email', false ), 'registered' => array( 'registered', true ) );
 }

 protected function get_bulk_actions() { return array( 'approved' => 'Approve', 'rejected' => 'Reject / revoke' ); }

 public function prepare_items() {
  $query = new WP_User_Query( Airsoft_Product_Manager_Resellers::application_query( $this->filters ) );
  $this->items = $query->get_results();
  $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'username' );
  $this->set_pagination_args( array( 'total_items' => (int) $query->get_total(), 'per_page' => 25 ) );
 }

 protected function get_views() {
  $views = array();
  foreach ( array( '' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected' ) as $status => $label ) {
   $filters = array( 'application_status' => $status );
   $args = Airsoft_Product_Manager_Resellers::application_query( Airsoft_Product_Manager_Resellers::list_filters( $filters ) );
   $args['number'] = 1;
   $args['fields'] = 'ID';
   $count = ( new WP_User_Query( $args ) )->get_total();
   $url = add_query_arg( array( 'page' => 'airsoft-resellers', 'application_status' => $status ), admin_url( 'admin.php' ) );
   $current = $this->filters['application_status'] === $status;
   $views[ $status ? $status : 'all' ] = '<a href="' . esc_url( $url ) . '"' . ( $current ? ' class="current" aria-current="page"' : '' ) . '>' . esc_html( $label ) . ' <span class="count">(' . number_format_i18n( $count ) . ')</span></a>';
  }
  return $views;
 }

 public function no_items() {
  echo $this->filters['s'] || $this->filters['application_status'] ? 'No applications match your search or selected status.' : 'No reseller applications yet.';
 }

 protected function column_cb( $user ) {
  if ( ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $user->ID ) ) { return ''; }
  return '<label class="screen-reader-text" for="application-' . absint( $user->ID ) . '">Select ' . esc_html( $user->display_name ) . '</label><input id="application-' . absint( $user->ID ) . '" type="checkbox" name="application_ids[]" value="' . absint( $user->ID ) . '">';
 }

 protected function column_username( $user ) {
  $login = esc_html( $user->user_login );
  if ( current_user_can( 'edit_user', $user->ID ) ) { $login = '<a href="' . esc_url( get_edit_user_link( $user->ID ) . '#airsoft-reseller-bank-details' ) . '">' . $login . '</a>'; }
  $output = get_avatar( $user->ID, 32 ) . '<strong>' . $login . '</strong>';
  $actions = array();
  if ( current_user_can( 'edit_user', $user->ID ) ) { $actions['edit'] = '<a href="' . esc_url( get_edit_user_link( $user->ID ) . '#airsoft-reseller-bank-details' ) . '">View reseller details</a>'; }
  return $output . $this->row_actions( $actions );
 }

 protected function column_actions( $user ) {
  if ( ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $user->ID ) ) { return '<span class="description">Separate reseller account required.</span>'; }
  $status = get_user_meta( $user->ID, '_airsoft_reseller_application', true );
  $buttons = array();
  if ( 'approved' !== $status || ! Airsoft_Product_Manager_Resellers::approved( $user->ID ) ) {
   $buttons[] = '<button type="submit" class="button button-primary button-small" name="review_application" value="approved:' . absint( $user->ID ) . '">Approve</button>';
  }
  if ( 'rejected' !== $status || Airsoft_Product_Manager_Resellers::approved( $user->ID ) ) {
   $buttons[] = '<button type="submit" class="button button-small" name="review_application" value="rejected:' . absint( $user->ID ) . '">' . ( 'approved' === $status ? 'Revoke' : 'Reject' ) . '</button>';
  }
  return implode( ' ', $buttons );
 }

 protected function column_default( $user, $column ) {
  if ( 'name' === $column ) { return esc_html( $user->display_name ); }
  if ( 'email' === $column ) { return '<a href="' . esc_url( 'mailto:' . $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a>'; }
  if ( 'business' === $column ) {
   $business = esc_html( get_user_meta( $user->ID, '_airsoft_reseller_business', true ) );
   $details = get_user_meta( $user->ID, '_airsoft_reseller_details', true );
   if ( $details ) { $business .= '<details><summary>View application</summary><p>' . nl2br( esc_html( $details ) ) . '</p></details>'; }
   if ( ! Airsoft_Product_Manager_Reseller_Portal::is_reseller_account( $user->ID ) ) { $business .= '<p class="description">Legacy member application. A separate reseller registration is required.</p>'; }
   return $business;
  }
  if ( 'status' === $column ) {
   $status = get_user_meta( $user->ID, '_airsoft_reseller_application', true );
   $labels = array( 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected' );
   return esc_html( $labels[$status] ?? 'Unknown' );
  }
  if ( 'registered' === $column ) {
   return esc_html( mysql2date( get_option( 'date_format' ), $user->user_registered ) );
  }
  return '';
 }
}
