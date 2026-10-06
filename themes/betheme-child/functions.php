<?php
// Load child theme includes - Product display features
get_template_part('includes/class-mfn-child-woo-helper');
get_template_part('includes/class-mfn-child-featured-product-block');
require_once __DIR__ . '/includes/class-mfn-child-product-category-filter.php';

add_action('wp_enqueue_scripts', function () {
    // Load parent theme style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Load child theme style
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));

    // Load child theme custom responsive overrides
    wp_enqueue_style('child-custom-responsive-style', get_stylesheet_directory_uri() . '/css/custom-responsive.css', array('child-style'));

    // Load child theme wishlist style
    wp_enqueue_style('child-wishlist-style', get_stylesheet_directory_uri() . '/css/wishlist.css', array('child-style'));

});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'betheme-child-mfn-opt-expandable',
        get_stylesheet_directory_uri() . '/js/mfn-opt-expandable.js',
        array('jquery'),
        filemtime(get_stylesheet_directory() . '/js/mfn-opt-expandable.js'),
        true
    );
}, 100);

add_action('wp_enqueue_scripts', function () {
    if (! wp_script_is('mfn-woojs', 'enqueued')) {
        return;
    }

    wp_enqueue_script(
        'betheme-child-woocommerce',
        get_stylesheet_directory_uri() . '/js/woocommerce.js',
        array('jquery', 'mfn-woojs'),
        filemtime(get_stylesheet_directory() . '/js/woocommerce.js'),
        true
    );
}, 20);

add_action('admin_enqueue_scripts', function () {
    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'mfn-live-builder' ) {
        return;
    }

    wp_enqueue_script(
        'betheme-child-bebuilder-fixes',
        get_stylesheet_directory_uri() . '/js/bebuilder-fixes.js',
        array( 'jquery', 'mfn-builder' ),
        filemtime( get_stylesheet_directory() . '/js/bebuilder-fixes.js' ),
        true
    );
}, 100);

add_action('init', function () {
    add_shortcode('woocommerce_brands_list', 'mfn_child_woocommerce_brands_list_shortcode');
    add_shortcode('wc_brands_list', 'mfn_child_woocommerce_brands_list_shortcode');
    add_shortcode('betheme_social_icons', 'mfn_child_betheme_social_icons_shortcode');
    add_shortcode('mfn_social_icons', 'mfn_child_betheme_social_icons_shortcode');
});

/**
 * Display the BeTheme social icon list from Theme Options.
 *
 * Usage:
 * [betheme_social_icons]
 * [betheme_social_icons class="my-social-icons"]
 */
function mfn_child_betheme_social_icons_shortcode($atts) {
    if (! function_exists('mfn_opts_get')) {
        return '';
    }

    $atts = shortcode_atts(
        array(
            'class'   => '',
            'wrapper' => 'div',
        ),
        $atts,
        'betheme_social_icons'
    );

    $wrapper = strtolower(sanitize_key($atts['wrapper']));
    $allowed_wrappers = array('div', 'nav', 'span');

    if (! in_array($wrapper, $allowed_wrappers, true)) {
        $wrapper = 'div';
    }

    $classes = array('betheme-social-icons-shortcode');

    if ($atts['class'] !== '') {
        $classes[] = sanitize_html_class($atts['class']);
    }

    ob_start();
    get_template_part('includes/include', 'social');
    $social_icons = trim(ob_get_clean());

    if ($social_icons === '') {
        return '';
    }

    return sprintf(
        '<%1$s class="%2$s">%3$s</%1$s>',
        esc_attr($wrapper),
        esc_attr(implode(' ', $classes)),
        $social_icons
    );
}

/**
 * Display WooCommerce product brands.
 *
 * Usage:
 * [woocommerce_brands_list]
 * [woocommerce_brands_list hide_empty="true" show_count="true"]
 */
function mfn_child_woocommerce_brands_list_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'taxonomy'   => 'auto',
            'hide_empty' => 'false',
            'orderby'    => 'name',
            'order'      => 'ASC',
            'show_count' => 'false',
            'parent'     => '',
            'class'      => '',
        ),
        $atts,
        'woocommerce_brands_list'
    );

    $taxonomy = sanitize_key($atts['taxonomy']);

    if ($taxonomy === 'auto') {
        $taxonomy = '';

        foreach (array('product_brand', 'pa_brand', 'pwb-brand', 'yith_product_brand') as $brand_taxonomy) {
            if (! taxonomy_exists($brand_taxonomy)) {
                continue;
            }

            $matching_terms = get_terms(
                array(
                    'taxonomy'   => $brand_taxonomy,
                    'hide_empty' => false,
                    'number'     => 1,
                )
            );

            if (! is_wp_error($matching_terms) && ! empty($matching_terms)) {
                $taxonomy = $brand_taxonomy;
                break;
            }
        }
    }

    if (! $taxonomy || ! taxonomy_exists($taxonomy)) {
        return '';
    }

    $term_args = array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
        'number'     => 8,
        'orderby'    => sanitize_key($atts['orderby']),
        'order'      => strtoupper($atts['order']) === 'DESC' ? 'DESC' : 'ASC',
    );

    if ($atts['parent'] !== '') {
        $term_args['parent'] = (int) $atts['parent'];
    }

    $brands = get_terms($term_args);

    if (is_wp_error($brands) || empty($brands)) {
        return '';
    }

    $classes = array('woocommerce-brands-list');

    if ($atts['class'] !== '') {
        $classes[] = sanitize_html_class($atts['class']);
    }

    ob_start();
    ?>
    <ul class="<?php echo esc_attr(implode(' ', $classes)); ?>">
        <?php foreach ($brands as $brand) : ?>
            <?php $brand_link = get_term_link($brand); ?>
            <?php if (is_wp_error($brand_link)) : ?>
                <?php continue; ?>
            <?php endif; ?>
            <li class="woocommerce-brands-list__item">
                <a class="woocommerce-brands-list__link" href="<?php echo esc_url($brand_link); ?>">
                    <?php echo esc_html($brand->name); ?>
                    <?php if (filter_var($atts['show_count'], FILTER_VALIDATE_BOOLEAN)) : ?>
                        <span class="woocommerce-brands-list__count">(<?php echo esc_html($brand->count); ?>)</span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php

    return ob_get_clean();
}

add_action( 'init', 'betheme_child_register_wishlist_endpoint' );
function betheme_child_register_wishlist_endpoint() {
    add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'coupons', EP_ROOT | EP_PAGES );
}

add_action( 'after_switch_theme', 'betheme_child_flush_rewrite_rules' );
function betheme_child_flush_rewrite_rules() {
    betheme_child_register_wishlist_endpoint();
    flush_rewrite_rules();
}

add_action( 'init', 'betheme_child_maybe_flush_rewrite_rules' );
function betheme_child_maybe_flush_rewrite_rules() {
    if ( get_option( 'betheme_child_wishlist_rules_flushed' ) !== '1' || get_option( 'betheme_child_coupons_rules_flushed' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'betheme_child_wishlist_rules_flushed', '1' );
        update_option( 'betheme_child_coupons_rules_flushed', '1' );
    }
}

add_filter( 'query_vars', 'betheme_child_wishlist_query_vars', 0 );
function betheme_child_wishlist_query_vars( $vars ) {
    $vars[] = 'wishlist';
    $vars[] = 'coupons';
    return $vars;
}

add_filter( 'woocommerce_account_menu_items', 'betheme_child_add_wishlist_menu_item' );
function betheme_child_add_wishlist_menu_item( $items ) {
    if ( ! isset( $items['wishlist'] ) ) {
        if ( isset( $items['customer-logout'] ) ) {
            $logout = $items['customer-logout'];
            unset( $items['customer-logout'] );
            $items['wishlist'] = __( 'Wishlist', 'betheme-child' );
            $items['customer-logout'] = $logout;
        } else {
            $items['wishlist'] = __( 'Wishlist', 'betheme-child' );
        }
    }
    return $items;
}

add_action( 'woocommerce_account_wishlist_endpoint', 'betheme_child_wishlist_endpoint_content' );
function betheme_child_wishlist_endpoint_content() {
    wc_get_template( 'myaccount/my-account-wishlist.php' );
}

add_action( 'woocommerce_account_coupons_endpoint', 'betheme_child_coupons_endpoint_content' );
function betheme_child_coupons_endpoint_content() {
    wc_get_template( 'myaccount/my-account-coupons.php' );
}

add_action( 'template_redirect', 'betheme_child_handle_wishlist_add_request' );
function betheme_child_handle_wishlist_add_request() {
    if ( empty( $_GET['betheme_wishlist_add_product'] ) ) {
        return;
    }

    $product_id = absint( wp_unslash( $_GET['betheme_wishlist_add_product'] ) );
    if ( $product_id <= 0 ) {
        return;
    }

    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( wp_login_url( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
        exit;
    }

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'betheme_child_wishlist_add_product' ) ) {
        return;
    }

    if ( betheme_child_add_product_to_wishlist( $product_id ) ) {
        wc_add_notice( esc_html__( 'Product added to your wishlist.', 'betheme-child' ), 'success' );
    } else {
        wc_add_notice( esc_html__( 'Product is already in your wishlist.', 'betheme-child' ), 'notice' );
    }

    $redirect = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
    wp_safe_redirect( esc_url_raw( $redirect ) );
    exit;
}

// Hook removed - wishlist button output inline in content-product.php template only
add_filter( 'woocommerce_loop_add_to_cart_link', 'betheme_child_append_wishlist_to_cart_button', 10, 2 );
function betheme_child_append_wishlist_to_cart_button( $html, $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) {
        return $html;
    }

    // Build wishlist button HTML
    $wishlist_html = '<div class="betheme-child-wishlist-loop-button">';
    
    if ( ! is_user_logged_in() ) {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $redirect_to = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );
        $login_url = wp_login_url( $redirect_to );
        $wishlist_html .= '<a class="button" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Login to add wishlist', 'betheme-child' ) . '</a>';
    } elseif ( betheme_child_is_product_in_wishlist( $product->get_id() ) ) {
        $wishlist_html .= '<span class="button secondary betheme-child-wishlist-added">' . esc_html__( 'In wishlist', 'betheme-child' ) . '</span>';
    } else {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $add_url     = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );
        $wishlist_html .= '<a class="button alt betheme-child-wishlist-button" href="' . esc_url( $add_url ) . '">' . esc_html__( 'Add to wishlist', 'betheme-child' ) . '</a>';
    }
    
    $wishlist_html .= '</div>';
    
    return $html . $wishlist_html;
}

function betheme_child_display_wishlist_loop_button() {
    global $product;

    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    echo '<div class="betheme-child-wishlist-loop-button">';

    if ( ! is_user_logged_in() ) {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $redirect_to = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );
        $login_url = wp_login_url( $redirect_to );
        echo '<a class="button" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Login to add wishlist', 'betheme-child' ) . '</a>';
    } elseif ( betheme_child_is_product_in_wishlist( $product->get_id() ) ) {
        echo '<span class="button secondary betheme-child-wishlist-added">' . esc_html__( 'In wishlist', 'betheme-child' ) . '</span>';
    } else {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $add_url     = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );

        echo '<a class="button alt betheme-child-wishlist-button" href="' . esc_url( $add_url ) . '">' . esc_html__( 'Add to wishlist', 'betheme-child' ) . '</a>';
    }

    echo '</div>';
}

add_action( 'woocommerce_single_product_summary', 'betheme_child_display_single_wishlist_button', 35 );
function betheme_child_display_single_wishlist_button() {
    global $product;

    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    echo '<div class="betheme-child-wishlist-loop-button">';

    if ( ! is_user_logged_in() ) {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $redirect_to = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );
        $login_url = wp_login_url( $redirect_to );
        echo '<a class="button" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Login to add wishlist', 'betheme-child' ) . '</a>';
    } elseif ( betheme_child_is_product_in_wishlist( $product->get_id() ) ) {
        echo '<span class="button secondary betheme-child-wishlist-added">' . esc_html__( 'In wishlist', 'betheme-child' ) . '</span>';
    } else {
        $current_url = remove_query_arg( array( 'betheme_wishlist_add_product', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $add_url     = add_query_arg(
            array(
                'betheme_wishlist_add_product' => $product->get_id(),
                '_wpnonce'                     => wp_create_nonce( 'betheme_child_wishlist_add_product' ),
            ),
            $current_url
        );

        echo '<a class="button alt betheme-child-wishlist-button" href="' . esc_url( $add_url ) . '">' . esc_html__( 'Add to wishlist', 'betheme-child' ) . '</a>';
    }

    echo '</div>';
}

function betheme_child_is_product_in_wishlist( $product_id, $wishlist_items = null ) {
    if ( $wishlist_items === null ) {
        $wishlist_items = betheme_child_get_wishlist_items();
    }

    foreach ( $wishlist_items as $item ) {
        if ( isset( $item['product_id'] ) && absint( $item['product_id'] ) === $product_id ) {
            return true;
        }
    }

    return false;
}

function betheme_child_add_product_to_wishlist( $product_id ) {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        return false;
    }

    $wishlist_items = betheme_child_get_wishlist_items();
    if ( betheme_child_is_product_in_wishlist( $product_id, $wishlist_items ) ) {
        return false;
    }

    $wishlist_items[] = array(
        'product_id' => $product_id,
        'name'       => $product->get_name(),
        'url'        => $product->is_type( 'external' ) ? $product->get_product_url() : get_permalink( $product_id ),
    );

    betheme_child_save_wishlist_items( $wishlist_items );
    return true;
}

function betheme_child_get_wishlist_items() {
    if ( ! is_user_logged_in() ) {
        return array();
    }

    $items = get_user_meta( get_current_user_id(), '_betheme_child_wishlist_items', true );
    return is_array( $items ) ? $items : array();
}

function betheme_child_save_wishlist_items( $items ) {
    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), '_betheme_child_wishlist_items', array_values( $items ) );
    }
}

function betheme_child_get_active_coupons() {
    if ( ! is_user_logged_in() ) {
        return array();
    }

    $customer_id = get_current_user_id();
    $customer    = new WC_Customer( $customer_id );
    $customer_email = $customer->get_email();

    $coupon_posts = get_posts( array(
        'posts_per_page' => -1,
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
    ) );

    $active_coupons = array();
    foreach ( $coupon_posts as $post ) {
        $coupon = new WC_Coupon( $post->ID );
        
        // Expiry check
        if ( $coupon->get_date_expires() && $coupon->get_date_expires()->getTimestamp() < time() ) {
            continue;
        }
        
        // Email restrictions check
        $allowed_emails = $coupon->get_email_restrictions();
        if ( ! empty( $allowed_emails ) ) {
            $email_match = false;
            foreach ( $allowed_emails as $email ) {
                if ( fnmatch( $email, $customer_email ) ) {
                    $email_match = true;
                    break;
                }
            }
            if ( ! $email_match ) {
                continue;
            }
        }
        
        // Usage limit check
        if ( $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
            continue;
        }
        
        // Usage limit per user check
        if ( $coupon->get_usage_limit_per_user() > 0 ) {
            $user_usage_count = $coupon->get_usage_by_user_id( $customer_id );
            if ( $user_usage_count >= $coupon->get_usage_limit_per_user() ) {
                continue;
            }
        }

        $active_coupons[] = $coupon;
    }

    return $active_coupons;
}

add_action('woocommerce_before_cart', function() {
    echo '<div class="cart-page-title">';
    echo '<h1>Shopping Cart</h1>';
    echo '</div>';
});

// Shortcode to display total cart items count
add_shortcode('woocommerce_cart_items_count', 'betheme_child_cart_items_count_shortcode');
function betheme_child_cart_items_count_shortcode() {
    if (function_exists('WC')) {
        $cart_count = WC()->cart->get_cart_contents_count();
        return '<span class="woocommerce-cart-items-count">' . intval($cart_count) . '</span>';
    }
    return '0';
}

add_action('woocommerce_after_cart_table', 'betheme_child_display_cart_trust_bar', 20);
add_action('woocommerce_after_cart', 'betheme_child_display_cart_trust_bar', 5);
function betheme_child_display_cart_trust_bar() {
    static $displayed = false;

    if ($displayed) {
        return;
    }

    $displayed = true;
    echo '<div class="cart-trust-bar">';
    
    echo '<div class="trust-item">';
        echo '<span class="icon">&#128737;&#65039;</span>';
        echo '<div>';
            echo '<strong>100% Authentic</strong>';
            echo '<small>Genuine products only</small>';
        echo '</div>';
    echo '</div>';

    echo '<div class="trust-item">';
        echo '<span class="icon">&#127911;</span>';
        echo '<div>';
            echo '<strong>Expert Support</strong>';
            echo '<small>Knowledgeable support</small>';
        echo '</div>';
    echo '</div>';

    echo '<div class="trust-item">';
        echo '<span class="icon">&#128260;</span>';
        echo '<div>';
            echo '<strong>Easy Returns</strong>';
            echo '<small>Hassle-free returns</small>';
        echo '</div>';
    echo '</div>';

    echo '<div class="trust-item">';
        echo '<span class="icon">&#128274;</span>';
        echo '<div>';
            echo '<strong>Secure Payment</strong>';
            echo '<small>Your data is safe</small>';
        echo '</div>';
    echo '</div>';

    echo '</div>';
}

add_action('wp_footer', 'betheme_child_display_block_cart_trust_bar');
function betheme_child_display_block_cart_trust_bar() {
    if (! function_exists('is_cart') || ! is_cart()) {
        return;
    }
    ?>
    <script>
        (function () {
            var trustIcon = '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" height="200px" width="200px" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
            var supportIcon = '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="200px" width="200px" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.486 2 2 6.486 2 12v4.143C2 17.167 2.897 18 4 18h1a1 1 0 0 0 1-1v-5.143a1 1 0 0 0-1-1h-.908C4.648 6.987 7.978 4 12 4s7.352 2.987 7.908 6.857H19a1 1 0 0 0-1 1V18c0 1.103-.897 2-2 2h-2v-1h-4v3h6c2.206 0 4-1.794 4-4 1.103 0 2-.833 2-1.857V12c0-5.514-4.486-10-10-10z"></path></svg>';
            var returnIcon = '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="200px" width="200px" xmlns="http://www.w3.org/2000/svg"><path d="M256 48C141.31 48 48 141.32 48 256c0 114.86 93.14 208 208 208 114.69 0 208-93.31 208-208 0-114.87-93.13-208-208-208zm0 313a94 94 0 0 1 0-188h4.21l-14.11-14.1a14 14 0 0 1 19.8-19.8l40 40a14 14 0 0 1 0 19.8l-40 40a14 14 0 0 1-19.8-19.8l18-18c-2.38-.1-5.1-.1-8.1-.1a66 66 0 1 0 66 66 14 14 0 0 1 28 0 94.11 94.11 0 0 1-94 94z"></path></svg>';
            var secureIcon = '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="200px" width="200px" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"></path></svg>';
            var trustBarHtml = '<div class="cart-trust-bar cart-trust-bar--block">' +
                '<div class="trust-item"><span class="icon">' + trustIcon + '</span><div><strong>100% Authentic</strong><br/><small>Genuine products only</small></div></div>' +
                '<div class="trust-item"><span class="icon">' + supportIcon + '</span><div><strong>Expert Support</strong><br/><small>Knowledgeable support</small></div></div>' +
                '<div class="trust-item"><span class="icon">' + returnIcon + '</span><div><strong>Easy Returns</strong><br/><small>Hassle-free returns</small></div></div>' +
                '<div class="trust-item"><span class="icon">' + secureIcon + '</span><div><strong>Secure Payment</strong><br/><small>Your data is safe</small></div></div>' +
            '</div>';

            function insertTrustBar() {
                var blockCart = document.querySelector('.wp-block-woocommerce-cart');
                var classicCart = document.querySelector('form.woocommerce-cart-form');

                if (! blockCart || classicCart || document.querySelector('.cart-trust-bar')) {
                    return;
                }

                blockCart.insertAdjacentHTML('afterend', trustBarHtml);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', insertTrustBar);
            } else {
                insertTrustBar();
            }

            window.setTimeout(insertTrustBar, 500);
        }());
    </script>
    <?php
}
