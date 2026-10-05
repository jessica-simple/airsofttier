<?php

// 1. from product
// 2. by conditions (cat, tag)
// 3. from theme options by default

global $mfn_global;

if( ( empty($_GET['visual']) || !empty($_GET['mfn-template-id']) ) && !empty( $mfn_global['singular_template'] ) && is_numeric( $mfn_global['singular_template'] ) && get_post_type( $mfn_global['singular_template'] ) == 'template' && ! post_password_required() ) {

	global $product;

	$classes = ['mfn-single-product-tmpl-wrapper'];

	if( !empty( get_post_meta(get_the_ID(), '_sold_individually', true) ) && get_post_meta(get_the_ID(), '_sold_individually', true) == 'yes' ){
		$classes[] = 'sold-individually';
	}

	if( !is_object($product) && is_product() ) {
		$product = wc_get_product( get_the_ID() );
	}

	if( is_object($product) && method_exists($product, 'get_stock_quantity') ) {
		$product_quantity = $product->get_stock_quantity();
		if( 1 === $product_quantity ){
			$classes[] = 'quantity-one';
		}
	}

	while ( have_posts() ) :
	the_post();

	echo '<div class="'. implode(' ', wc_get_product_class( $classes, get_the_ID() )) .'">';

		if( empty( get_post_meta($mfn_global['singular_template'], 'mfn_template_alert', true) ) ) {
			echo '<div class="section_wrapper no-bebuilder-section clearfix">';
				do_action( 'woocommerce_before_single_product' );
			echo '</div>';
			// else moved to shortcode
		}

		$mfn_builder = new Mfn_Builder_Front( $mfn_global['singular_template'] );
		$mfn_builder->show();

		echo '<div class="section_wrapper no-bebuilder-section clearfix">';
			do_action('woocommerce_after_single_product');
		echo '</div>';

	echo '</div>';

	endwhile;

}else{

	echo '<div class="section section_product_before_tabs">';
		echo '<div class="section_wrapper no-bebuilder-section clearfix">';
			woocommerce_content();
		echo '</div>';
	echo '</div>';

}

?>
