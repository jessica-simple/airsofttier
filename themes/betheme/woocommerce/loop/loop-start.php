<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
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
 * @version		3.3.0
 */

$classes = array();
$columns = array(
	'grid' => 3,
	'grid col-2' => 2,
	'grid col-4' => 4,
	'masonry' => 3,
	'list' => 1,
	'list_2' => 1,
);

if( is_woocommerce() ){

	// shop layout aplies ONLY for archives page (Shop)

	if( ! is_product() ){

		// layout

		if( ! empty( $_GET['layout'] ) ){
			$shop_layout = str_replace('grid4', 'grid col-4', esc_attr($_GET['layout']));
		} else {
			$shop_layout = mfn_opts_get( 'shop-layout', 'grid' );
		}
		
		$classes[] = 'columns-'. $columns[$shop_layout];
		$classes[] = $shop_layout;

		// isotope

		if( 'masonry' == $shop_layout ){
			$classes[] = 'isotope';
		};

	}

}

if( !empty(mfn_opts_get('shop_equal_heights') ) ) $classes[] = 'mfn-equal-heights';
if( !empty(mfn_opts_get('shop_equal_heights_last_el_class') ) ) $classes[] = 'mfn-equal-height-el-'.mfn_opts_get('shop_equal_heights_last_el_class');

$classes[] = !empty(Mfn_Builder_Front::$item_obj['attr']['product_badge_onsale_position']) ? 'mfn-product-badges-right' : 'mfn-product-badges-left';
if( !empty(Mfn_Builder_Front::$item_obj['attr']['product_star_rating_stats']) ) $classes[] = ' mfn-stars-'.Mfn_Builder_Front::$item_obj['attr']['product_star_rating_stats'];

$classes = implode( ' ', $classes );

?>

<div class="products_wrapper mfn-woo-products isotope_wrapper default-woo-loop lm_wrapper">
	<ul class="products <?php echo esc_attr( $classes ); ?>">
