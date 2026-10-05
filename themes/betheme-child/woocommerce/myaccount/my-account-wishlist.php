<?php
/**
 * My Account wishlist endpoint.
 *
 * @package WooCommerce	emplates
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wc_print_notice(esc_html__('You must be logged in to manage your wishlist.', 'betheme-child'), 'error');
    return;
}

$wishlist_items = betheme_child_get_wishlist_items();
$endpoint_url = wc_get_account_endpoint_url('wishlist');

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['wishlist_action']) && wp_verify_nonce(wp_unslash($_POST['_wpnonce'] ?? ''), 'betheme_child_wishlist_action')) {
    $action = sanitize_key(wp_unslash($_POST['wishlist_action']));

    if ('add' === $action) {
        $name = trim(sanitize_text_field(wp_unslash($_POST['item_name'] ?? '')));
        $url = trim(esc_url_raw(wp_unslash($_POST['item_url'] ?? '')));

        if ($name !== '') {
            $wishlist_items[] = array(
                'name' => $name,
                'url' => $url,
            );
            betheme_child_save_wishlist_items($wishlist_items);
            wc_add_notice(esc_html__('Item added to your wishlist.', 'betheme-child'), 'success');
        } else {
            wc_add_notice(esc_html__('Please enter an item name before saving.', 'betheme-child'), 'error');
        }
    } elseif ('remove' === $action) {
        $index = isset($_POST['item_index']) ? absint(wp_unslash($_POST['item_index'])) : -1;
        if (isset($wishlist_items[$index])) {
            array_splice($wishlist_items, $index, 1);
            betheme_child_save_wishlist_items($wishlist_items);
            wc_add_notice(esc_html__('Item removed from your wishlist.', 'betheme-child'), 'success');
        }
    } elseif ('clear' === $action) {
        $wishlist_items = array();
        betheme_child_save_wishlist_items($wishlist_items);
        wc_add_notice(esc_html__('Wishlist cleared.', 'betheme-child'), 'success');
    }

    wp_safe_redirect($endpoint_url);
    exit;
}

wc_print_notices();
?>
<div class="wishlist-page">

    <div class="wishlist-header">
        <div>
            <h1><?php esc_html_e('Wishlist', 'betheme-child'); ?></h1>
            <p><?php esc_html_e('Save items you love so you can find them again later.', 'betheme-child'); ?></p>
        </div>

        <div class="wishlist-count">
            <?php
            $item_count = count($wishlist_items);
            echo sprintf(_n('%d Item', '%d Items', $item_count, 'betheme-child'), $item_count);
            ?>
        </div>
    </div>

    <?php if (empty($wishlist_items)): ?>
        <div class="wishlist-empty-container">
            <h2><?php esc_html_e('Your wishlist is empty', 'betheme-child'); ?></h2>
            <p><?php esc_html_e('Save items you love so you can find them again later.', 'betheme-child'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
                <?php esc_html_e('Go To Shop', 'betheme-child'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="wishlist-items-container">
            <?php foreach ($wishlist_items as $index => $item): ?>
                <?php
                $product_id = isset($item['product_id']) ? absint($item['product_id']) : 0;
                $product = $product_id ? wc_get_product($product_id) : null;

                // 1. Get Product Image URL
                $product_image_url = '';
                if ($product) {
                    $product_image_url = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');
                }
                if (!$product_image_url) {
                    $product_image_url = wc_placeholder_img_src();
                }

                // 2. Get Product Tag / Category
                $category_name = '';
                if ($product) {
                    $terms = wp_get_post_terms($product->get_id(), 'product_cat', array('number' => 1));
                    if (!is_wp_error($terms) && !empty($terms)) {
                        $category_name = $terms[0]->name;
                    }
                }
                if (empty($category_name)) {
                    $category_name = esc_html__('Product', 'betheme-child');
                }

                // 3. Product Title & URL
                $product_name = $product ? $product->get_name() : $item['name'];
                $product_url = $product ? get_permalink($product->get_id()) : (!empty($item['url']) ? $item['url'] : '#');

                // 4. Stock Status
                $is_in_stock = $product ? $product->is_in_stock() : true;

                // 5. Product Price HTML
                $price_html = $product ? $product->get_price_html() : '';

                // 6. Add to Cart / Action URL
                $add_to_cart_url = '';
                $is_purchasable = false;
                if ($product) {
                    $is_purchasable = $product->is_purchasable() && $product->is_in_stock();
                    $add_to_cart_url = $product->add_to_cart_url();
                }
                ?>
                <div class="wishlist-card">

                    <div class="wishlist-image">
                        <img src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product_name); ?>">
                    </div>

                    <div class="wishlist-content">

                        <span class="product-tag"><?php echo esc_html($category_name); ?></span>

                        <h2><?php echo esc_html($product_name); ?></h2>

                        <?php if ($is_in_stock): ?>
                            <div class="stock-status in-stock">
                                <span class="dot"></span>
                                <?php esc_html_e('In Stock', 'betheme-child'); ?>
                            </div>
                        <?php else: ?>
                            <div class="stock-status out-of-stock">
                                <span class="dot"></span>
                                <?php esc_html_e('Out of Stock', 'betheme-child'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($price_html)): ?>
                            <div class="product-price">
                                <?php echo wp_kses_post($price_html); ?>
                            </div>
                        <?php endif; ?>

                        <div class="wishlist-actions">
                            <?php if (!empty($product_url) && '#' !== $product_url): ?>
                                <a href="<?php echo esc_url($product_url); ?>" class="btn btn-outline">
                                    <?php esc_html_e('View Product', 'betheme-child'); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($product): ?>
                                <?php
                                if ($product->is_type('variable')) {
                                    $btn_label = esc_html__('Select Options', 'betheme-child');
                                    $btn_class = 'btn btn-primary';
                                } elseif (!$product->is_in_stock()) {
                                    $btn_label = esc_html__('Read More', 'betheme-child');
                                    $btn_class = 'btn btn-primary';
                                } else {
                                    $btn_label = esc_html__('Add To Cart', 'betheme-child');
                                    $btn_class = 'btn btn-primary add_to_cart_button ajax_add_to_cart';
                                }
                                ?>
                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                                    class="<?php echo esc_attr($btn_class); ?>"
                                    data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                    data-product_sku="<?php echo esc_attr($product->get_sku()); ?>">
                                    <?php echo esc_html($btn_label); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>

                    <form method="post" action="<?php echo esc_url($endpoint_url); ?>" class="wishlist-remove-form">
                        <input type="hidden" name="wishlist_action" value="remove">
                        <input type="hidden" name="item_index" value="<?php echo esc_attr($index); ?>">
                        <?php wp_nonce_field('betheme_child_wishlist_action'); ?>
                        <button type="submit" class="wishlist-remove"
                            title="<?php esc_attr_e('Remove from Wishlist', 'betheme-child'); ?>">
                            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 448 512" height="200px"
                                width="200px" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z">
                                </path>
                            </svg>
                        </button>
                    </form>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($wishlist_items)): ?>
        <div class="wishlist-footer">
            <form method="post" action="<?php echo esc_url($endpoint_url); ?>" class="csw-wishlist-clear-form"
                style="margin: 0; display: inline-block;">
                <input type="hidden" name="wishlist_action" value="clear">
                <?php wp_nonce_field('betheme_child_wishlist_action'); ?>
                <button type="submit" class="btn btn-danger">
                    <?php esc_html_e('Clear Wishlist', 'betheme-child'); ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>