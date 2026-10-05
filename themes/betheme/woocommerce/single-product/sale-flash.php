<?php
/**
 * Single Product Sale Flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/sale-flash.php.
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
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;
$mfn_extra_label = get_post_meta($product->get_id(), 'mfn_product_labels', true);
$mfn_extra_label_color = get_post_meta($product->get_id(), 'mfn_product_labels_color', true);
$mfn_extra_label_bg = get_post_meta($product->get_id(), 'mfn_product_labels_bg_color', true);
$mfn_extra_label_border = get_post_meta($product->get_id(), 'mfn_product_labels_border_color', true);
$new_product_label = false;

if( mfn_opts_get('product-badge-new') == 1 ){
    $newness_days = mfn_opts_get('product-badge-new-days') ? mfn_opts_get('product-badge-new-days') : 14;
    $created = strtotime( $product->get_date_created() );

    $label = mfn_opts_get('product-badge-new-text', __( 'NEW', 'woocommerce' ));
    if ( ( time() - ( 60 * 60 * 24 * $newness_days ) ) < $created ) {
      $new_product_label = '<span class="mfn-new-badge onsale-label onsale">' . esc_html( $label ) . '</span>';
    }
}

// Consider adding wrapper div do labels

if ( $product->is_on_sale() || !empty($mfn_extra_label) || !empty($new_product_label) ) :

    echo '<div class="mfn-product-badges">';
        
        if( $product->is_on_sale() ){
            $salehtml = '<span class="onsale onsale-label">'. ( !empty(mfn_opts_get('sale-badge-label')) ? mfn_opts_get('sale-badge-label') : __('On Sale', 'woocommerce') ) .'</span>';
            if( mfn_opts_get( 'sale-badge-style' ) == 'percent' ){
                $percent = Mfn_Builder_Woo_Helper::getDiscount($product);
                $salehtml = '<span class="onsale onsale-label">';
                    $salehtml .= !empty( mfn_opts_get('sale-badge-before') ) ? mfn_opts_get('sale-badge-before') : '-';
                    $salehtml .= $percent;
                    $salehtml .= !empty( mfn_opts_get('sale-badge-after') ) ? mfn_opts_get('sale-badge-after') : '';
                $salehtml .= '</span>';
            }

            echo apply_filters( 'woocommerce_sale_flash', $salehtml, $post, $product ); 
        }


        if( !empty($new_product_label) ) echo $new_product_label;

        if( !empty($mfn_extra_label) ) {
            $ex_clrs = '';
            if( !empty($mfn_extra_label_color) || !empty($mfn_extra_label_bg) || !empty($mfn_extra_label_border) ){
                $ex_clrs = 'style="';
                if( !empty($mfn_extra_label_color) ) $ex_clrs .= 'color: '.$mfn_extra_label_color.'; ';
                if( !empty($mfn_extra_label_bg) ) $ex_clrs .= 'background-color: '.$mfn_extra_label_bg.'; ';
                if( !empty($mfn_extra_label_border) ) $ex_clrs .= 'border-color: '.$mfn_extra_label_border.'; ';
                $ex_clrs .= '"';
            }

            echo apply_filters( 'woocommerce_sale_flash', '<span '.$ex_clrs.' class="onsale onsale-extra-label">'.$mfn_extra_label.'</span>', $post, $product );
        }

    echo '</div>';

endif;



/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

?>
