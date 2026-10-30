<?php
/** Front-end product submission with ownership enforced on every write. */
defined( 'ABSPATH' ) || exit;
class Airsoft_Product_Manager_Reseller_Products {
 public static function init() {
  add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'purchasable' ), 30, 2 );
  add_filter( 'woocommerce_variation_is_purchasable', array( __CLASS__, 'purchasable' ), 30, 2 );
 }
 public static function purchasable( $allowed, $product ) {
  $owner = absint( $product->get_meta( '_airsoft_reseller_owner' ) );
  if ( ! $owner && $product->get_parent_id() ) { $owner = absint( get_post_meta( $product->get_parent_id(), '_airsoft_reseller_owner', true ) ); }
  // Revocation also suspends checkout for that reseller's existing listings.
  return $owner && ( ! Airsoft_Product_Manager_Resellers::approved( $owner ) || $owner === get_current_user_id() ) ? false : $allowed;
 }
 public static function render() {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { return; }
  $page = max( 1, absint( $_GET['product_page'] ?? 1 ) );
  $products = wc_get_products( array( 'limit' => 20, 'page' => $page, 'paginate' => true, 'status' => array( 'publish', 'pending', 'draft' ), 'meta_key' => '_airsoft_reseller_owner', 'meta_value' => get_current_user_id(), 'orderby' => 'date', 'order' => 'DESC' ) );
  echo '<section class="airsoft-panel"><div class="airsoft-panel-heading"><h3>Your listings</h3><span class="airsoft-muted">' . absint( $products->total ) . ' products</span></div><div class="airsoft-table-wrap"><table class="airsoft-table"><thead><tr><th>Product</th><th>Normal price</th><th>Reseller price</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead><tbody>';
  foreach ( $products->products as $product ) {
   if ( (int) $product->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() ) { continue; }
   $labels = array( 'publish' => 'Live', 'pending' => 'Pending review', 'draft' => 'Draft' );
   $normal = $product->get_regular_price( 'edit' );
   $special = $product->get_meta( '_airsoft_reseller_price' );
   echo '<tr><td><div class="airsoft-product-name">' . wp_kses_post( $product->get_image( array( 40, 40 ) ) ) . '<span>' . esc_html( $product->get_name() ) . '</span></div></td><td>' . ( '' !== $normal ? wp_kses_post( wc_price( $normal ) ) : '—' ) . '</td><td>' . ( '' !== $special ? wp_kses_post( wc_price( $special ) ) : 'Not set' ) . '</td><td>' . esc_html( $product->get_manage_stock() ? $product->get_stock_quantity() : 'Not tracked' ) . '</td><td><span class="airsoft-badge ' . ( 'publish' === $product->get_status() ? 'airsoft-badge-good' : 'airsoft-badge-wait' ) . '">' . esc_html( $labels[$product->get_status()] ?? $product->get_status() ) . '</span></td><td>';
   if ( $product->is_type( 'simple' ) ) { echo '<a class="airsoft-button" href="' . esc_url( add_query_arg( array( 'section' => 'product-edit', 'edit_product' => $product->get_id() ), Airsoft_Product_Manager_Resellers::url() ) ) . '">Edit</a>'; }
   else { echo '<span class="airsoft-muted">Contact admin to edit variations</span>'; }
   echo '</td></tr>';
  }
  if ( ! $products->products ) { echo '<tr><td colspan="6">No products yet. Add your first product to submit it for approval.</td></tr>'; }
  echo '</tbody></table></div><p class="airsoft-muted airsoft-panel-note">New listings and edits require admin approval.</p><nav class="airsoft-pagination" aria-label="Product pages">';
  if ( $page > 1 ) { echo '<a class="airsoft-button" href="' . esc_url( add_query_arg( array( 'section' => 'products', 'product_page' => $page - 1 ), Airsoft_Product_Manager_Resellers::url() ) ) . '">Previous</a>'; }
  if ( $page < $products->max_num_pages ) { echo '<a class="airsoft-button" href="' . esc_url( add_query_arg( array( 'section' => 'products', 'product_page' => $page + 1 ), Airsoft_Product_Manager_Resellers::url() ) ) . '">Next</a>'; }
  echo '</nav></section>';
 }

 public static function counts() {
  $counts = array( 'publish' => 0, 'pending' => 0, 'draft' => 0 );
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { return $counts; }
  foreach ( array_keys( $counts ) as $status ) {
   $result = wc_get_products( array( 'limit' => 1, 'paginate' => true, 'return' => 'ids', 'status' => $status, 'meta_key' => '_airsoft_reseller_owner', 'meta_value' => get_current_user_id() ) );
   $counts[$status] = (int) $result->total;
  }
  return $counts;
 }

 public static function form() {
  if ( ! Airsoft_Product_Manager_Resellers::approved() ) { return; }
  $id = absint( $_GET['edit_product'] ?? 0 );
  $product = $id ? wc_get_product( $id ) : false;
  if ( $id && ( ! $product || (int) $product->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || ! $product->is_type( 'simple' ) ) ) { echo '<p>This product cannot be edited here.</p>'; return; }
  echo '<section class="airsoft-panel"><h3>Product details</h3><p class="airsoft-muted airsoft-panel-note">Editing a live product takes it offline until approved. Shipping uses the site’s checkout rates.</p><form class="airsoft-product-form" method="post" enctype="multipart/form-data">';
  wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
  echo '<input type="hidden" name="product_id" value="' . absint( $id ) . '">';
  echo '<div class="airsoft-form-grid">';
  Airsoft_Product_Manager_Resellers::field( 'Product name', 'product_name', $product ? $product->get_name() : '', 'text', 'required maxlength="200"' );
  echo '<p><label>Description<br><textarea name="description" required>' . esc_textarea( $product ? $product->get_description() : '' ) . '</textarea></label></p>';
  Airsoft_Product_Manager_Resellers::field( 'Normal price', 'normal_price', $product ? $product->get_regular_price( 'edit' ) : '', 'number', 'required min="0" step="any"' );
  Airsoft_Product_Manager_Resellers::field( 'Reseller price (optional)', 'reseller_price', $product ? $product->get_meta( '_airsoft_reseller_price' ) : '', 'number', 'min="0" step="any"' );
  Airsoft_Product_Manager_Resellers::field( 'Stock quantity', 'stock', $product ? $product->get_stock_quantity() : 0, 'number', 'required min="0" step="1"' );
  echo '<p><label>Product photo (JPEG, PNG or WebP, maximum 5 MB)<br><input type="file" name="product_photo" accept="image/jpeg,image/png,image/webp"></label></p></div><div class="airsoft-form-actions"><a class="airsoft-button" href="' . esc_url( add_query_arg( 'section', 'products', Airsoft_Product_Manager_Resellers::url() ) ) . '">Cancel</a><button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="save_product">Submit for approval</button></div></form></section>';
 }
 public static function save() {
  $id = absint( $_POST['product_id'] ?? 0 );
  $product = $id ? wc_get_product( $id ) : new WC_Product_Simple();
  if ( ! $product || ( $id && ( (int) $product->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || ! $product->is_type( 'simple' ) || in_array( $product->get_status(), array( 'trash', 'private' ), true ) ) ) ) { throw new Exception( 'You cannot edit this product.' ); }
  $name = sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) );
  $description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
  $normal = wc_format_decimal( wp_unslash( $_POST['normal_price'] ?? '' ) );
  $special = wc_format_decimal( wp_unslash( $_POST['reseller_price'] ?? '' ) );
  $stock = sanitize_text_field( wp_unslash( $_POST['stock'] ?? '' ) );
  if ( ! $name || ! $description || ! is_numeric( $normal ) || (float) $normal < 0 || ! preg_match( '/^\d+$/', $stock ) || ( '' !== $special && ( ! is_numeric( $special ) || (float) $special < 0 || (float) $special > (float) $normal ) ) ) { throw new Exception( 'Check the name, description, prices and stock. Reseller price cannot exceed the normal price.' ); }
  $image_id = 0;
  if ( isset( $_FILES['product_photo'] ) && UPLOAD_ERR_NO_FILE !== (int) $_FILES['product_photo']['error'] ) {
   $file = $_FILES['product_photo'];
   if ( UPLOAD_ERR_OK !== (int) $file['error'] || $file['size'] > 5 * MB_IN_BYTES || ! is_uploaded_file( $file['tmp_name'] ) ) { throw new Exception( 'Photo upload failed or exceeds 5 MB.' ); }
   $mime = wp_get_image_mime( $file['tmp_name'] );
   if ( ! in_array( $mime, array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) { throw new Exception( 'Upload a JPEG, PNG or WebP image.' ); }
   require_once ABSPATH . 'wp-admin/includes/file.php';
   require_once ABSPATH . 'wp-admin/includes/media.php';
   require_once ABSPATH . 'wp-admin/includes/image.php';
   $image_id = media_handle_upload( 'product_photo', 0, array(), array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
   if ( is_wp_error( $image_id ) ) { throw new Exception( $image_id->get_error_message() ); }
  }
  $product->set_name( $name );
  $product->set_description( $description );
  $product->set_regular_price( $normal );
  $product->set_sale_price( '' );
  $product->set_price( $normal );
  $product->set_manage_stock( true );
  $product->set_stock_quantity( (int) $stock );
  $product->set_backorders( 'no' );
  $product->set_status( 'pending' );
  $product->update_meta_data( '_airsoft_reseller_owner', get_current_user_id() );
  $product->update_meta_data( '_airsoft_reseller_price', $special );
  if ( $image_id ) { $product->set_image_id( $image_id ); }
  $id = $product->save();
  if ( $image_id ) { wp_update_post( array( 'ID' => $image_id, 'post_parent' => $id ) ); }
  Airsoft_Product_Manager_Reseller_Emails::send( get_option( 'admin_email' ), 'Reseller product awaiting approval', admin_url( 'post.php?post=' . $id . '&action=edit' ) );
  wc_add_notice( 'Product submitted. An admin will review it before publication.', 'success' );
 }
}
