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
   $special = $product->get_meta( '_airsoft_reseller_price' );
   $normal_display = self::normal_price_display( $product );
   $special_display = $product->is_type( 'variable' ) ? 'Per variation' : ( '' !== $special ? wp_kses_post( wc_price( $special ) ) : 'Not set' );
   $stock_display = $product->is_type( 'variable' ) ? 'Per variation' : ( $product->get_manage_stock() ? $product->get_stock_quantity() : 'Not tracked' );
   echo '<tr><td><div class="airsoft-product-name">' . wp_kses_post( $product->get_image( array( 40, 40 ) ) ) . '<span>' . esc_html( $product->get_name() ) . '</span></div></td><td>' . $normal_display . '</td><td>' . $special_display . '</td><td>' . esc_html( $stock_display ) . '</td><td><span class="airsoft-badge ' . ( 'publish' === $product->get_status() ? 'airsoft-badge-good' : 'airsoft-badge-wait' ) . '">' . esc_html( $labels[$product->get_status()] ?? $product->get_status() ) . '</span></td><td>';
   if ( 'publish' === $product->get_status() ) { echo '<a class="airsoft-button" href="' . esc_url( $product->get_permalink() ) . '">View product</a> '; }
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

 public static function normal_price_display( $product ) {
  if ( $product->is_type( 'variable' ) ) {
   $minimum = $product->get_variation_regular_price( 'min', true );
   return '' !== $minimum ? wp_kses_post( 'From ' . wc_price( $minimum ) ) : 'Per variation';
  }
  $normal = $product->get_regular_price( 'edit' );
  return '' !== $normal ? wp_kses_post( wc_price( $normal ) ) : '—';
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
  $previous = get_transient( 'airsoft_reseller_product_form_' . get_current_user_id() );
  delete_transient( 'airsoft_reseller_product_form_' . get_current_user_id() );
  $previous = is_array( $previous ) ? $previous : array();
  $value = static function( $key, $fallback = '' ) use ( $previous ) { return array_key_exists( $key, $previous ) ? $previous[$key] : $fallback; };
  $id = absint( $_GET['edit_product'] ?? 0 );
  $product = $id ? wc_get_product( $id ) : false;
  if ( $id && ( ! $product || (int) $product->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || ! $product->is_type( 'simple' ) ) ) { echo '<p>This product cannot be edited here.</p>'; return; }
  echo '<section class="airsoft-panel"><h3>Product details</h3><p class="airsoft-muted airsoft-panel-note">Editing a live product takes it offline until approved. Shipping uses the site’s checkout rates.</p><form class="airsoft-product-form" method="post" enctype="multipart/form-data">';
  wp_nonce_field( 'airsoft_reseller', 'airsoft_nonce' );
  echo '<input type="hidden" name="product_id" value="' . absint( $id ) . '">';
  echo '<div class="airsoft-form-grid">';
  if ( ! $id ) {
   echo '<p><label>Product type<br><select name="product_type"><option value="simple"' . selected( $value( 'product_type', 'simple' ), 'simple', false ) . '>Single product</option><option value="variable"' . selected( $value( 'product_type' ), 'variable', false ) . '>Product with variations</option></select></label></p>';
   Airsoft_Product_Manager_Resellers::field( 'Variation option name (for example, Size)', 'variation_attribute', $value( 'variation_attribute' ), 'text', 'maxlength="50"' );
   echo '<p><label>Variation options (one per line)<br><textarea name="variation_rows" placeholder="Small | 20.00 | 18.00 | 5&#10;Large | 25.00 | 22.00 | 3">' . esc_textarea( $value( 'variation_rows' ) ) . '</textarea><small>For a variation product, enter one option per line as: option | normal price | reseller price (optional) | stock.</small></label></p>';
  }
  Airsoft_Product_Manager_Resellers::field( 'Product name', 'product_name', $value( 'product_name', $product ? $product->get_name() : '' ), 'text', 'required maxlength="200"' );
  echo '<p><label>Description<br><textarea name="description" required>' . esc_textarea( $value( 'description', $product ? $product->get_description() : '' ) ) . '</textarea></label></p>';
  Airsoft_Product_Manager_Resellers::field( 'Normal price (single product only)', 'normal_price', $value( 'normal_price', $product ? $product->get_regular_price( 'edit' ) : '' ), 'number', 'min="0" step="any"' );
  Airsoft_Product_Manager_Resellers::field( 'Reseller price (single product only)', 'reseller_price', $value( 'reseller_price', $product ? $product->get_meta( '_airsoft_reseller_price' ) : '' ), 'number', 'min="0" step="any"' );
  Airsoft_Product_Manager_Resellers::field( 'Stock quantity (single product only)', 'stock', $value( 'stock', $product ? $product->get_stock_quantity() : 0 ), 'number', 'min="0" step="1"' );
  echo '<p><label>Product photo (JPEG, PNG or WebP, maximum 5 MB)<br><input type="file" name="product_photo" accept="image/jpeg,image/png,image/webp"></label></p></div><div class="airsoft-form-actions"><a class="airsoft-button" href="' . esc_url( add_query_arg( 'section', 'products', Airsoft_Product_Manager_Resellers::url() ) ) . '">Cancel</a><button class="airsoft-button airsoft-button-primary" name="airsoft_action" value="save_product">Submit for approval</button></div></form></section>';
 }

 public static function remember_form_input() {
  if ( ! function_exists( 'set_transient' ) || ! get_current_user_id() ) { return; }
  $input = array();
  foreach ( array( 'product_type', 'variation_attribute', 'product_name', 'normal_price', 'reseller_price', 'stock' ) as $key ) {
   if ( isset( $_POST[$key] ) && is_scalar( $_POST[$key] ) ) { $input[$key] = sanitize_text_field( wp_unslash( (string) $_POST[$key] ) ); }
  }
  foreach ( array( 'variation_rows', 'description' ) as $key ) {
   if ( isset( $_POST[$key] ) && is_scalar( $_POST[$key] ) ) { $raw = wp_unslash( (string) $_POST[$key] ); $input[$key] = 'description' === $key ? wp_kses_post( $raw ) : sanitize_textarea_field( $raw ); }
  }
  set_transient( 'airsoft_reseller_product_form_' . get_current_user_id(), $input, 10 * MINUTE_IN_SECONDS );
 }
 public static function save() {
  $id = absint( $_POST['product_id'] ?? 0 );
  $product_type = ! $id ? sanitize_key( wp_unslash( $_POST['product_type'] ?? 'simple' ) ) : 'simple';
  if ( ! in_array( $product_type, array( 'simple', 'variable' ), true ) ) { throw new Exception( 'Select a supported product type.' ); }
  $product = $id ? wc_get_product( $id ) : ( 'variable' === $product_type ? new WC_Product_Variable() : new WC_Product_Simple() );
  if ( ! $product || ( $id && ( (int) $product->get_meta( '_airsoft_reseller_owner' ) !== get_current_user_id() || ! $product->is_type( 'simple' ) || in_array( $product->get_status(), array( 'trash', 'private' ), true ) ) ) ) { throw new Exception( 'You cannot edit this product.' ); }
  $name = sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) );
  $description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
  $normal = wc_format_decimal( wp_unslash( $_POST['normal_price'] ?? '' ) );
  $special = wc_format_decimal( wp_unslash( $_POST['reseller_price'] ?? '' ) );
  $stock = sanitize_text_field( wp_unslash( $_POST['stock'] ?? '' ) );
  $variants = array();
  if ( 'variable' === $product_type ) {
   $attribute_name = sanitize_text_field( wp_unslash( $_POST['variation_attribute'] ?? '' ) );
   $lines = preg_split( '/\r\n|\r|\n/', wp_unslash( $_POST['variation_rows'] ?? '' ) );
   foreach ( $lines as $line ) {
    if ( '' === trim( $line ) ) { continue; }
    $parts = array_map( 'trim', explode( '|', $line ) );
    if ( count( $parts ) !== 4 ) { throw new Exception( 'Each variation line must contain option, normal price, reseller price and stock, separated by | characters.' ); }
    list( $option, $regular, $reseller_price, $quantity ) = $parts;
    $regular = wc_format_decimal( $regular ); $reseller_price = '' === $reseller_price ? '' : wc_format_decimal( $reseller_price );
    if ( '' === $option || strlen( $option ) > 50 || ! is_numeric( $regular ) || (float) $regular < 0 || ( '' !== $reseller_price && ( ! is_numeric( $reseller_price ) || (float) $reseller_price < 0 || (float) $reseller_price > (float) $regular ) ) || ! preg_match( '/^\d+$/', $quantity ) ) { throw new Exception( 'Check each variation option, price and stock quantity. Reseller price cannot exceed normal price.' ); }
    $variants[] = array( 'option' => $option, 'regular' => $regular, 'reseller' => $reseller_price, 'stock' => (int) $quantity );
   }
   if ( ! $attribute_name || strlen( $attribute_name ) > 50 || count( $variants ) < 2 || count( $variants ) > 50 ) { throw new Exception( 'Variable products need an option name and between 2 and 50 variation rows.' ); }
   if ( count( array_unique( array_map( static function( $variant ) { return strtolower( $variant['option'] ); }, $variants ) ) ) !== count( $variants ) ) { throw new Exception( 'Variation options must be unique.' ); }
  }
  if ( ! $name || ! $description || ( 'simple' === $product_type && ( ! is_numeric( $normal ) || (float) $normal < 0 || ! preg_match( '/^\d+$/', $stock ) || ( '' !== $special && ( ! is_numeric( $special ) || (float) $special < 0 || (float) $special > (float) $normal ) ) ) ) ) { throw new Exception( 'Check the name, description, prices and stock. Reseller price cannot exceed the normal price.' ); }
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
  if ( 'simple' === $product_type ) {
   $product->set_regular_price( $normal );
   $product->set_sale_price( '' );
   $product->set_price( $normal );
   $product->set_manage_stock( true );
   $product->set_stock_quantity( (int) $stock );
   $product->set_backorders( 'no' );
  } else {
   $attribute = new WC_Product_Attribute();
   $attribute->set_id( 0 );
   $attribute->set_name( $attribute_name );
   $attribute->set_options( array_map( static function( $variant ) { return $variant['option']; }, $variants ) );
   $attribute->set_position( 0 ); $attribute->set_visible( true ); $attribute->set_variation( true );
   $product->set_attributes( array( $attribute ) );
   $product->set_manage_stock( false );
  }
  $product->set_status( 'pending' );
  $product->update_meta_data( '_airsoft_reseller_owner', get_current_user_id() );
  $product->update_meta_data( '_airsoft_reseller_price', 'simple' === $product_type ? $special : '' );
  if ( $image_id ) { $product->set_image_id( $image_id ); }
  $id = $product->save();
  if ( 'variable' === $product_type ) {
   $attribute_key = sanitize_title( $attribute_name );
   foreach ( $variants as $variant ) {
    $variation = new WC_Product_Variation();
    $variation->set_parent_id( $id );
    $variation->set_attributes( array( $attribute_key => $variant['option'] ) );
    $variation->set_regular_price( $variant['regular'] );
    $variation->set_price( $variant['regular'] );
    $variation->set_manage_stock( true );
    $variation->set_stock_quantity( $variant['stock'] );
    $variation->set_backorders( 'no' );
    $variation->update_meta_data( '_airsoft_reseller_owner', get_current_user_id() );
    $variation->update_meta_data( '_airsoft_reseller_price', $variant['reseller'] );
    $variation->save();
   }
   WC_Product_Variable::sync( $id );
  }
  if ( $image_id ) { wp_update_post( array( 'ID' => $image_id, 'post_parent' => $id ) ); }
  Airsoft_Product_Manager_Reseller_Emails::send( get_option( 'admin_email' ), 'Reseller product awaiting approval', admin_url( 'post.php?post=' . $id . '&action=edit' ) );
  wc_add_notice( 'Product submitted. An admin will review it before publication.', 'success' );
 }
}
