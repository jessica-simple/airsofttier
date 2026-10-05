<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

// extra post classes

$classes = [];

$classes[] = 'isotope-item';

// alignment

$classes[] = 'align-'. mfn_opts_get('shop-align', 'center');

// background

if( ! empty( mfn_opts_get('background-archives-product') ) ){
	$classes[] = 'has-background-color';
}

// title tag

$title_tag = mfn_opts_get('shop-title-tag','h4');
$title_class = '';

if( ! empty($title_tag) ){
	if( 'p.lead' == $title_tag ){
		$title_tag = 'p';
		$title_class = 'lead';
	}
}

$title_tag_before = '<'. mfn_allowed_title_tag($title_tag) .' class="mfn-woo-product-title '. esc_attr($title_class) .'">';
$title_tag_after = '</'. mfn_allowed_title_tag($title_tag) .'>';

?>
<li <?php wc_product_class( $classes, $product ); ?> >

	<?php
		/**
		 * woocommerce_before_shop_loop_item hook.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */

		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
		do_action( 'woocommerce_before_shop_loop_item' );

		/**
		 * woocommerce_before_shop_loop_item hook.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		echo Mfn_Builder_Woo_Helper::get_woo_product_image($product);
	?>

	<div class="desc">

		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>

		<?php echo $title_tag_before; ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><?php echo $title_tag_after; ?>

		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook.
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );

			/**
			 * woocommerce_after_shop_loop_item hook.
			 *
			 * @hooked woocommerce_template_loop_product_link_close - 5
			 * @hooked woocommerce_template_loop_add_to_cart - 10
			 */

			if( $button = mfn_opts_get('shop-button') ){
				$button_class = 'show-button button-'. $button;
			} else {
				$button_class = 'hide-button';
			}

			echo '<div class="mfn-li-product-row mfn-li-product-row-button '. esc_attr($button_class) .'">';

				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

				if( ! mfn_opts_get('shop-button') ){
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}

				do_action( 'woocommerce_after_shop_loop_item' );

			echo '</div>';
		?>

	</div>

</li>
