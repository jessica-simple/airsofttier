<?php
/**
 * Description tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

?>

<?php
  $mfn_builder = new Mfn_Builder_Front( $post->ID );

  if( apply_filters('bebuilder_preview', true) || wp_doing_ajax() ) {

    if( !empty($_GET['mfn-template-id']) && $_GET['mfn-template-id'] !== $post->ID ){
      $mfn_builder->show(false, true);
    }else{
      $mfn_builder->show();
    }
    //echo $_GET['mfn-template-id'].' / '.$post->ID;
    
  }else{
    $mfn_builder->show();
  }
  // $mfn_builder->show();
?>
