<?php
/**
 * Custom WooCommerce Helper - Product SKU Feature
 * 
 * This file adds the Product SKU show/hide functionality to the product list.
 * Place in: wp-content/themes/betheme-child/includes/
 * 
 * Note: The "Product SKU" field is added in the parent theme's class-mfn-builder-fields.php
 * The SKU display is handled in the parent theme's class-mfn-builder-woo-helper.php
 * This file provides backup functionality and hide via CSS.
 */

if (!defined('ABSPATH')) {
	exit;
}

// Store shop_products attributes in global when shortcode runs
global $mfn_shop_products_attr;
$mfn_shop_products_attr = array();

add_action('woocommerce_before_shop_loop', function() {
	global $mfn_shop_products_attr, $product;
	
	if (empty($mfn_shop_products_attr) && is_a($product, 'WC_Product')) {
		$mfn_shop_products_attr = array();
	}
}, 5);

// Wrap the shop_products shortcode to capture attributes
remove_shortcode('shop_products');
add_shortcode('shop_products', function($attr, $content = null) {
	global $mfn_shop_products_attr;
	
	// Store the attributes globally for use while this shortcode renders.
	$previous_attr = isset($mfn_shop_products_attr) ? $mfn_shop_products_attr : array();
	$mfn_shop_products_attr = $attr;
	
	// Call the original shortcode function
	if (function_exists('sc_shop_products')) {
		$output = sc_shop_products($attr, $content);
		$mfn_shop_products_attr = $previous_attr;
		return $output;
	}
	
	$mfn_shop_products_attr = $previous_attr;
	return '';
});

// Display product category in shop loop
add_action('woocommerce_after_shop_loop_item', function() {
	global $product, $mfn_shop_products_attr;
	
	if (!$product) return;
	
	$attr = isset($mfn_shop_products_attr) ? $mfn_shop_products_attr : array();
	
	// Show product category if set to show
	// if (isset($attr['show_category']) && $attr['show_category'] === '1') {
	// 	$categories = $product->get_category_ids();
		
	// 	if (!empty($categories)) {
	// 		echo '<div class="mfn-li-product-row-category" style="margin-top:10px;">';
	// 		echo '<span class="cat-label" style="font-weight:bold;font-size:12px;">' . __('Category:', 'betheme') . '</span> ';
			
	// 		$cat_names = array();
	// 		foreach ($categories as $cat_id) {
	// 			$term = get_term($cat_id, 'product_cat');
	// 			if ($term && !is_wp_error($term)) {
	// 				$cat_names[] = '<span class="product-cat" style="color:#666;font-size:11px;margin-right:5px;">' . esc_html($term->name) . '</span>';
	// 			}
	// 		}
			
	// 		echo implode(', ', $cat_names);
	// 		echo '</div>';
	// 	}
	// }
}, 45);

// Display product SKU in shop loop
// add_action('woocommerce_after_shop_loop_item', function() {
// 	global $product, $mfn_shop_products_attr;
	
// 	if (!$product) return;
	
// 	$attr = isset($mfn_shop_products_attr) ? $mfn_shop_products_attr : array();
	
// 	// Show product SKU if set to show
// 	if (isset($attr['show_product_sku']) && $attr['show_product_sku'] === '1') {
// 		$sku = $product->get_sku();
		
// 		if ($sku) {
// 			echo '<div class="mfn-li-product-row-sku" style="margin-top:5px;">';
// 			echo '<span class="sku-label" style="font-weight:bold;font-size:12px;">' . __('SKU:', 'betheme') . '</span> ';
// 			echo '<span class="product-sku" style="color:#666;font-size:11px;">' . esc_html($sku) . '</span>';
// 			echo '</div>';
// 		}
// 	}
// }, 48);

// Display product tags in shop loop
add_action('woocommerce_after_shop_loop_item', function() {
	global $product, $mfn_shop_products_attr;
	
	if (!$product) return;
	
	$attr = isset($mfn_shop_products_attr) ? $mfn_shop_products_attr : array();
	
	// Show product tags if set to show
	if (isset($attr['show_product_tags']) && $attr['show_product_tags'] === '1') {
		$tags = $product->get_tag_ids();
		
		if (!empty($tags)) {
			$tag_names = array();
			$tag_classes = array('mfn-li-product-row-tags');
			foreach ($tags as $tag_id) {
				$term = get_term($tag_id, 'product_tag');
				if ($term && !is_wp_error($term)) {
					$tag_slug = !empty($term->slug) ? $term->slug : sanitize_title($term->name);
					$tag_class = 'product-tag-' . sanitize_html_class($tag_slug);
					$tag_classes[] = $tag_class;
					$tag_names[] = '<span class="product-tag ' . esc_attr($tag_class) . '" style="background:#f0f0f0;padding:2px 8px;border-radius:3px;font-size:11px;margin-right:5px;display:inline-block;">' . esc_html($term->name) . '</span>';
				}
			}
			echo '<div class="' . esc_attr(implode(' ', array_unique($tag_classes))) . '" style="margin-top:10px;">';
			// echo '<span class="tag-label" style="font-weight:bold;font-size:12px;">' . __('Tags:', 'betheme') . '</span> ';
			
			
			
			echo implode('', $tag_names);
			echo '</div>';
		}
	}
}, 50);
