<?php
/**
 * Child theme helper for WooCommerce Featured Product block HTML.
 *
 * Edit the `$custom_html` variable in `mfn_child_customize_featured_product_block_html()`
 * if you want to globally change the rendered HTML for the block:
 * `woocommerce/featured-product`
 */

if (!defined('ABSPATH')) {
	exit;
}

add_filter('render_block_woocommerce/featured-product', 'mfn_child_customize_featured_product_block_html', 10, 3);

function mfn_child_customize_featured_product_block_html($block_content, $parsed_block, $block_instance)
{
	$product_id = 0;

	if (!empty($parsed_block['attrs']['productId'])) {
		$product_id = absint($parsed_block['attrs']['productId']);
	} elseif (!empty($block_instance->context['postId'])) {
		$product_id = absint($block_instance->context['postId']);
	}

	if (!$product_id) {
		return $block_content;
	}

	$product = wc_get_product($product_id);
	if (!$product) {
		return $block_content;
	}

	$permalink = get_permalink($product_id);
	$title = $product->get_name();
	$price_html = $product->get_price_html();
	$image_html = $product->get_image('full', array('class' => 'mfn-child-featured-product__image'));
	$description = $product->get_short_description() ? $product->get_short_description() : wc_trim_string(wp_strip_all_tags($product->get_description()), 180);
	$button_text = !empty($parsed_block['attrs']['linkText']) ? $parsed_block['attrs']['linkText'] : __('Shop now', 'woocommerce');
	$category_html = wc_get_product_category_list($product_id, ', ');
	$tag_html = wc_get_product_tag_list($product_id, ', ');

	/*
	|--------------------------------------------------------------------------
	| Global Featured Product Block HTML
	|--------------------------------------------------------------------------
	|
	| Change the HTML in `$custom_html` below if you want to globally customize
	| the WooCommerce Featured Product block from the child theme.
	|
	| Available variables:
	| - $block_content
	| - $product
	| - $product_id
	| - $permalink
	| - $title
	| - $price_html
	| - $image_html
	| - $description
	| - $button_text
	| - $category_html
	| - $tag_html
	|
	| Safe default:
	| keep the original WooCommerce block output, but add a custom class hook.
	*/

	$custom_html = $block_content;

	if (class_exists('WP_HTML_Tag_Processor')) {
		$processor = new WP_HTML_Tag_Processor($custom_html);
		if ($processor->next_tag()) {
			$processor->add_class('mfn-child-featured-product');
			$processor->set_attribute('data-product-id', (string) $product_id);
			$custom_html = $processor->get_updated_html();
		}
	}

	/*
	| Example starter template:
	|
	| $custom_html = '
	| <div class="mfn-child-featured-product" data-product-id="' . esc_attr($product_id) . '">
	|     <a class="mfn-child-featured-product__media" href="' . esc_url($permalink) . '">' . $image_html . '</a>
	|     <div class="mfn-child-featured-product__content">
	|         <div class="mfn-child-featured-product__category">' . $category_html . '</div>
	|         <h2 class="mfn-child-featured-product__title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h2>
	|         <div class="mfn-child-featured-product__price">' . $price_html . '</div>
	|         <div class="mfn-child-featured-product__description">' . wp_kses_post(wc_format_content($description)) . '</div>
	|         <div class="mfn-child-featured-product__tags">' . $tag_html . '</div>
	|         <a class="button mfn-child-featured-product__button" href="' . esc_url($permalink) . '">' . esc_html($button_text) . '</a>
	|     </div>
	| </div>';
	*/

	return $custom_html;
}
