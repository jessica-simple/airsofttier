<?php
/**
 * Single Template
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */


$tmp_id = get_the_ID();
$tmp_type = get_post_meta($tmp_id, 'mfn_template_type', true);
$preview_string = apply_filters('betheme_slug', 'mfn').'-preview';

/**
 * Redirect if shop tmpl preview
 * */

if( empty($_GET['visual']) && $tmp_type && $tmp_type == 'single-product' ) {
	$sample = Mfn_Builder_Woo_Helper::sample_item('product');
	$product = wc_get_product($sample);

	if( is_object($product) && $product->get_id() ) {
		if( !empty($_GET[$preview_string]) ) {
			wp_safe_redirect( get_permalink($product->get_id()).'?mfn-template-id='.$tmp_id.'&'.$preview_string.'=true' );
		}else{
			wp_safe_redirect( get_permalink($product->get_id()).'?mfn-template-id='.$tmp_id );
		}
	}
}elseif( empty($_GET['visual']) && $tmp_type && $tmp_type == 'archive-product' ) {
	if(wc_get_page_id( 'shop' )) {
		if( !empty($_GET[$preview_string]) ) {
			wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ).'?mfn-template-id='.$tmp_id.'&'.$preview_string.'=true' );
		}else{
			wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ).'?mfn-template-id='.$tmp_id );
		}
	}
}elseif( empty($_GET['visual']) && $tmp_type && $tmp_type == 'single-post' ) {
	$sample = get_posts( array('post_type' => 'post', 'numberposts' => 1 ));
	if( !empty($sample[0]->ID) ) {
		wp_safe_redirect( get_permalink($sample[0]->ID).'?mfn-template-id='.$tmp_id );
	}
}elseif( empty($_GET['visual']) && $tmp_type && $tmp_type == 'single-portfolio' ) {
	$sample = get_posts( array('post_type' => 'portfolio', 'numberposts' => 1 ));
	if( !empty($sample[0]->ID) ) {
		wp_safe_redirect( get_permalink($sample[0]->ID).'?mfn-template-id='.$tmp_id );
	}
}elseif( empty($_GET['visual']) && $tmp_type && $tmp_type == 'archive-portfolio' ) {
	$portfolio_page_id = mfn_opts_get('portfolio-page');

	if( ! $portfolio_page_id ){
		return false;
	}

	wp_safe_redirect( get_permalink($portfolio_page_id).'?mfn-template-id='.$tmp_id );
	
}elseif( empty($_GET['visual']) && $tmp_type && ( $tmp_type == 'header' || $tmp_type == 'footer' ) ) {
	if( !empty($_GET[$preview_string]) ) {
		wp_safe_redirect(  get_home_url().'?mfn-header-template='.$tmp_id.'&'.$preview_string.'=true' );
	}else{
		wp_safe_redirect(  get_home_url().'?mfn-header-template='.$tmp_id );
	}
}elseif( empty($_GET['visual']) && $tmp_type && in_array($tmp_type, array('popup', 'sidemenu')) ) {
	wp_safe_redirect(  get_home_url() );
}elseif( empty($_GET['visual']) && function_exists('is_woocommerce') && $tmp_type && in_array($tmp_type, array('cart')) ) {
	wp_safe_redirect(  wc_get_cart_url() . '?mfn-template-id='.get_the_ID() );
}elseif( empty($_GET['visual']) && function_exists('is_woocommerce') && $tmp_type && in_array($tmp_type, array('checkout')) && !empty(get_option( 'woocommerce_checkout_page_id' )) ) {
	wp_safe_redirect(  wc_get_checkout_url() . '?mfn-template-id='.get_the_ID() );
}

if( empty($_GET['visual']) ) exit;

if( $tmp_type && in_array( $tmp_type, array('single-product', 'archive-product')) ) {
	get_header( 'shop' );
}else{
	get_header();
}

// header tmpl
$mfn_header_tmpl_class = array();

$mfn_hasStickyHeader = get_post_meta($tmp_id, 'header_sticky', true);
$mfn_hasMobileHeader = get_post_meta($tmp_id, 'header_mobile', true);
$mfn_header_tmpl_pos = get_post_meta($tmp_id, 'header_position', true);
$mfn_header_offset_top = get_post_meta($tmp_id, 'body_offset_header', true);

if( !empty($mfn_hasStickyHeader) && $mfn_hasStickyHeader == 'enabled' ) $mfn_header_tmpl_class[] = 'mfn-hasSticky';
if( !empty($mfn_hasMobileHeader) && $mfn_hasMobileHeader == 'enabled' ) $mfn_header_tmpl_class[] = 'mfn-hasMobile';

if( $mfn_header_tmpl_pos && in_array($mfn_header_tmpl_pos, array('fixed', 'absolute')) && !$mfn_header_offset_top ) $mfn_header_tmpl_class[] = 'mfn-header-tmpl-absolute';

if( !$tmp_type || ( $tmp_type && !in_array($tmp_type, array('header', 'footer')) )) { ?>
<div id="Content">
	<div class="content_wrapper clearfix">

		<main class="sections_group">

			<div class="entry-content" itemprop="mainContentOfPage">

			<?php } ?>

				<?php
					if( $tmp_type && $tmp_type == 'single-product' ) echo '<div class="product">'; // single product wrapper
					if( $tmp_type && $tmp_type == 'header' ) echo '<div class="mfn-header-tmpl '.implode(' ', $mfn_header_tmpl_class).'">'; // header wrapper
					if( $tmp_type && $tmp_type == 'footer' ) echo '<div class="mfn-footer-tmpl mfn-footer">'; // footer wrapper
					if( $tmp_type && $tmp_type == 'megamenu' ) echo '<div id="mfn-megamenu-'.$tmp_id.'" class="mfn-megamenu-wrapper mfn-megamenu-'.( !empty(get_post_meta($tmp_id, 'megamenu_width', true)) ? get_post_meta($tmp_id, 'megamenu_width', true) : 'full-width' ).'">'; // megamenu wrapper 

					if( $tmp_type && $tmp_type == 'popup' ) {
						$popup = new MfnPopup($tmp_id);
						//$popup->css();
						$popup_classes = $popup->classes_get();
						echo '<div id="mfn-popup-template-'.$tmp_id.'" class="mfn-popup-tmpl '.implode(' ', $popup_classes).'">';
					}

					if( $tmp_type && $tmp_type == 'sidemenu' ) {
						$sidemenu = new MfnSideMenu($tmp_id);
						echo '<div id="mfn-sidemenu-tmpl-'.$tmp_id.'" class="mfn-sidemenu-tmpl '.implode(' ', $sidemenu->classes).'">';
					}

					$mfn_builder = new Mfn_Builder_Front($tmp_id);
					$mfn_builder->show();

					if( $tmp_type && $tmp_type == 'single-product' ) echo '</div>'; // end single product wrapper
					if( $tmp_type && $tmp_type == 'header' ) echo '</div>'; // end header wrapper
					if( $tmp_type && $tmp_type == 'footer' ) echo '</div>'; // end footer wrapper
					if( $tmp_type && $tmp_type == 'megamenu' ) echo '</div>'; // end megamenu wrapper
					if( $tmp_type && $tmp_type == 'popup' ) echo '</div>'; // end popup wrapper
					if( $tmp_type && $tmp_type == 'sidemenu' ) echo '</div>'; // end sidemenu wrapper

				?>

				<?php
					// sample content for header builder
					if( $tmp_type == 'header'){
						echo '<div class="mfn-only-sample-content">';
			        	$sample_page_id = get_option( 'page_on_front' );
			        	$mfn_item_sample = get_post_meta($sample_page_id, 'mfn-page-items', true);
			        	echo mfn_slider($sample_page_id);
			        	$front = new Mfn_Builder_Front($sample_page_id);
						$front->show($mfn_item_sample, true);
						echo '</div>';
			        }

			        if( $tmp_type == 'cart' && !empty($_GET['visual']) ){
			        	echo '<div class="mfn-cart-sample">';
			        	echo do_shortcode('[woocommerce_cart]');
			        	echo '</div>';
			        }

			        if( $tmp_type == 'checkout' && !empty($_GET['visual']) ){
			        	echo '<div class="mfn-checkout-sample">';
			        	echo do_shortcode('[woocommerce_checkout]');
			        	echo '</div>';
			        }
			        
				?>
			<?php if( !$tmp_type || ( $tmp_type && !in_array($tmp_type, array('header', 'footer')) )){ ?>
			</div>

		</main>

		<?php get_sidebar(); ?>

	</div>
</div>

<?php

}

if( $tmp_type && in_array( $tmp_type, array('single-product', 'archive-product')) ){
	get_footer( 'shop' );
}else{
	get_footer();
}
