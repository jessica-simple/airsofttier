<?php
/** Customer notifications for WooCommerce order and cart events. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Notifications {
    const PREFIX = '_airsoft_notification_';
    const LIMIT = 100;
    private static $header_rendered = false;

    public static function init() {
        add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
        add_filter( 'woocommerce_get_endpoint_url', array( __CLASS__, 'endpoint_url' ), 10, 4 );
        add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu' ), 30 );
        add_action( 'woocommerce_account_notifications_endpoint', array( __CLASS__, 'render' ) );
        add_action( 'template_redirect', array( __CLASS__, 'check_cart' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'mark_read' ), 5 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
        add_action( 'wp_footer', array( __CLASS__, 'banner' ) );
        add_shortcode( 'airsoft_notifications', array( __CLASS__, 'link' ) );
        add_action( 'wp_ajax_airsoft_notifications', array( __CLASS__, 'ajax' ) );
        add_action( 'woocommerce_checkout_order_created', array( __CLASS__, 'placed' ) );
        add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'placed' ) );
        add_action( 'woocommerce_payment_complete', array( __CLASS__, 'paid' ) );
        add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'status' ), 10, 4 );
        add_action( 'woocommerce_order_refunded', array( __CLASS__, 'refunded' ), 10, 2 );
        add_action( 'woocommerce_order_note_added', array( __CLASS__, 'note' ), 10, 2 );
        add_action( 'woocommerce_after_order_object_save', array( __CLASS__, 'tracking' ) );
        add_action( 'airsoft_order_shipped', array( __CLASS__, 'shipped' ), 10, 2 );
    }

    // Use the existing My Account route with a query endpoint; no rewrite flush required.
    public static function query_vars( $vars ) {
        $vars['notifications'] = 'notifications';
        return $vars;
    }

    public static function url() {
        if ( class_exists( 'Airsoft_Product_Manager_Reseller_Portal' ) && Airsoft_Product_Manager_Reseller_Portal::is_reseller_account() ) {
            return add_query_arg( 'section', 'notifications', Airsoft_Product_Manager_Reseller_Portal::url() );
        }
        return add_query_arg( 'notifications', '1', wc_get_page_permalink( 'myaccount' ) );
    }

    public static function endpoint_url( $url, $endpoint, $value, $permalink ) {
        return 'notifications' === $endpoint ? add_query_arg( 'notifications', '1', $permalink ) : $url;
    }

    public static function items( $user_id ) {
        $items = array();
        foreach ( get_user_meta( $user_id ) as $key => $values ) {
            if ( 0 !== strpos( $key, self::PREFIX ) ) {
                continue;
            }
            $item = maybe_unserialize( $values[0] );
            if ( is_array( $item ) && isset( $item['event'], $item['message'], $item['time'] ) ) {
                $items[$key] = $item;
            }
        }
        uasort( $items, function ( $a, $b ) { return $b['time'] <=> $a['time']; } );
        return $items;
    }

    public static function unread_count() {
        if ( ! is_user_logged_in() ) {
            return 0;
        }
        return count( array_filter( self::items( get_current_user_id() ), function ( $item ) { return empty( $item['read'] ); } ) );
    }

    public static function add( $user_id, $event, $message, $url, $event_id ) {
        if ( ! $user_id ) {
            return; // Guest order updates continue through WooCommerce's existing emails.
        }
        $key = self::PREFIX . hash( 'sha256', $event . ':' . $event_id );
        $item = array(
            'event' => sanitize_key( $event ),
            'message' => wp_strip_all_tags( $message ),
            'url' => esc_url_raw( $url ),
            'time' => microtime( true ),
            'read' => false,
        );
        if ( ! add_user_meta( $user_id, $key, $item, true ) ) {
            return;
        }
        // Separate records avoid overwriting other events arriving at the same time.
        foreach ( array_slice( self::items( $user_id ), self::LIMIT, null, true ) as $old_key => $old_item ) {
            delete_user_meta( $user_id, $old_key );
        }
    }

    public static function order_event( $order, $event, $message, $event_id, $pay = false ) {
        if ( ! $order instanceof WC_Order || $order instanceof WC_Order_Refund || ! $order->get_customer_id() ) {
            return;
        }
        $once = in_array( $event, array( 'order_placed', 'payment_received', 'order_shipped' ), true ) || 'initial-payment' === $event_id;
        $marker = '_airsoft_notified_' . sanitize_key( $event );
        if ( $once && $order->get_meta( $marker, true ) ) {
            return;
        }
        self::add(
            $order->get_customer_id(), $event,
            sprintf( $message, $order->get_order_number() ),
            $pay && $order->needs_payment() ? $order->get_checkout_payment_url() : $order->get_view_order_url(),
            $order->get_id() . ':' . $event_id
        );
        if ( $once ) {
            $order->update_meta_data( $marker, time() );
            $order->save_meta_data();
        }
    }

    public static function placed( $order ) {
        if ( ! $order instanceof WC_Order || $order->has_status( array( 'checkout-draft', 'auto-draft' ) ) ) {
            return;
        }
        self::order_event( $order, 'order_placed', __( 'Order #%s has been received.', 'product-manager' ), 'placed' );
        if ( $order->needs_payment() ) {
            self::order_event( $order, 'payment_required', __( 'Complete payment for order #%s.', 'product-manager' ), 'initial-payment', true );
        }
    }

    public static function paid( $order_id ) {
        self::order_event( wc_get_order( $order_id ), 'payment_received', __( 'Payment for order #%s was received.', 'product-manager' ), 'paid' );
    }

    public static function status( $order_id, $from, $to, $order ) {
        if ( in_array( $from, array( 'checkout-draft', 'auto-draft' ), true ) ) {
            self::placed( $order );
        }
        $messages = array(
            'processing' => array( 'order_processing', __( 'We are preparing order #%s.', 'product-manager' ) ),
            'completed' => array( 'order_completed', __( 'Order #%s is complete.', 'product-manager' ) ),
            'cancelled' => array( 'order_cancelled', __( 'Order #%s has been cancelled.', 'product-manager' ) ),
            'failed' => array( 'payment_failed', __( 'Payment for order #%s failed. Please retry payment.', 'product-manager' ) ),
            'on-hold' => array( 'order_on_hold', __( 'Order #%s is on hold while we confirm payment or availability.', 'product-manager' ) ),
        );
        if ( 'shipped' === $to ) {
            self::shipped( $order_id );
            return;
        }
        if ( 'pending' === $to && ! in_array( $from, array( '', 'checkout-draft', 'auto-draft' ), true ) && $order->needs_payment() ) {
            $messages['pending'] = array( 'payment_required', __( 'Complete payment for order #%s.', 'product-manager' ) );
        }
        if ( isset( $messages[$to] ) ) {
            $modified = $order->get_date_modified();
            $id = $from . ':' . $to . ':' . ( $modified ? $modified->getTimestamp() : time() );
            self::order_event( $order, $messages[$to][0], $messages[$to][1], $id, in_array( $to, array( 'failed', 'pending' ), true ) );
        }
    }

    public static function refunded( $order_id, $refund_id ) {
        $order = wc_get_order( $order_id );
        $refund = wc_get_order( $refund_id );
        if ( ! $order || ! $refund instanceof WC_Order_Refund ) {
            return;
        }
        $amount = html_entity_decode( wp_strip_all_tags( wc_price( $refund->get_amount(), array( 'currency' => $order->get_currency() ) ) ), ENT_QUOTES, 'UTF-8' );
        self::add( $order->get_customer_id(), 'order_refunded', sprintf( __( 'A refund of %1$s has been issued for order #%2$s.', 'product-manager' ), $amount, $order->get_order_number() ), $order->get_view_order_url(), $refund_id );
    }

    public static function note( $note_id, $order ) {
        if ( get_comment_meta( $note_id, 'is_customer_note', true ) ) {
            self::order_event( $order, 'order_note_added', __( 'You have a new update about order #%s.', 'product-manager' ), 'note:' . $note_id );
        }
    }

    public static function shipped( $order_id, $shipment_id = 'shipped' ) {
        self::order_event( wc_get_order( $order_id ), 'order_shipped', __( 'Order #%s has shipped. View your order for delivery details.', 'product-manager' ), 'shipment:' . sanitize_text_field( $shipment_id ) );
    }

    public static function tracking( $order ) {
        if ( ! $order instanceof WC_Order || $order instanceof WC_Order_Refund ) {
            return;
        }
        // Standard WooCommerce Shipment Tracking data, compatible with HPOS.
        $items = $order->get_meta( '_wc_shipment_tracking_items', true );
        foreach ( is_array( $items ) ? $items : array() as $item ) {
            if ( is_array( $item ) && ! empty( $item['tracking_number'] ) ) {
                // Share the status event's key so a status plus tracking save creates one alert.
                self::shipped( $order->get_id() );
                break;
            }
        }
    }

    public static function check_cart() {
        if ( is_admin() || wp_doing_ajax() || ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
            return;
        }
        $previous = WC()->session->get( 'airsoft_cart_notification_states', array() );
        $states = array();
        $quantities = WC()->cart->get_cart_item_quantities();
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            $product = isset( $item['data'] ) ? $item['data'] : null;
            if ( ! $product instanceof WC_Product || ! $product->exists() || empty( $item['quantity'] ) ) {
                continue;
            }
            $quantity = $quantities[$product->get_stock_managed_by_id()] ?? $item['quantity'];
            if ( ! $product->is_in_stock() ) {
                $event = 'cart_item_became_unavailable';
                $message = sprintf( __( '%s in your cart is now out of stock. Please remove it from your cart to continue.', 'product-manager' ), $product->get_name() );
            } elseif ( ! $product->backorders_allowed() && ! $product->has_enough_stock( $quantity ) ) {
                $event = 'cart_quantity_unavailable';
                $message = sprintf( __( 'Only %1$s units of %2$s remain. Please update the quantity in your cart.', 'product-manager' ), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ), $product->get_name() );
            } else {
                continue;
            }
            $state = hash( 'sha256', wp_json_encode( array( $event, $quantity, $product->get_stock_quantity() ) ) );
            $states[$key] = $state;
            if ( isset( $previous[$key] ) && $previous[$key] === $state ) {
                continue;
            }
            wc_add_notice( esc_html( $message ), 'notice' );
            self::add( get_current_user_id(), $event, $message, wc_get_cart_url(), $key . ':' . $state . ':' . wp_generate_uuid4() );
        }
        WC()->session->set( 'airsoft_cart_notification_states', $states );
        WC()->session->__unset( 'airsoft_invalid_cart_notice_signature' );
    }

    public static function menu( $items ) {
        $count = self::unread_count();
        $label = __( 'Notifications', 'product-manager' ) . ( $count ? ' (' . $count . ')' : '' );
        $logout = $items['customer-logout'] ?? null;
        unset( $items['customer-logout'] );
        $items['notifications'] = $label;
        if ( $logout ) {
            $items['customer-logout'] = $logout;
        }
        return $items;
    }

    public static function mark_read() {
        if ( ! is_user_logged_in() || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['airsoft_mark_notifications_read'] ) ) {
            return;
        }
        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'airsoft_mark_notifications_read' ) ) {
            return;
        }
        foreach ( self::items( get_current_user_id() ) as $key => $item ) {
            $item['read'] = true;
            update_user_meta( get_current_user_id(), $key, $item );
        }
        wp_safe_redirect( self::url() );
        exit;
    }

    public static function render() {
        if ( ! is_user_logged_in() ) {
            return;
        }
        $items = self::items( get_current_user_id() );
        echo '<section class="airsoft-notifications"><h2>' . esc_html__( 'Notifications', 'product-manager' ) . '</h2>';
        if ( ! $items ) {
            echo '<p>' . esc_html__( 'You have no notifications yet.', 'product-manager' ) . '</p></section>';
            return;
        }
        echo '<form method="post" action="' . esc_url( self::url() ) . '">';
        wp_nonce_field( 'airsoft_mark_notifications_read' );
        echo '<button class="button" name="airsoft_mark_notifications_read" value="1">' . esc_html__( 'Mark all as read', 'product-manager' ) . '</button></form><ul class="airsoft-notifications__list">';
        foreach ( $items as $item ) {
            echo '<li class="' . esc_attr( empty( $item['read'] ) ? 'is-unread' : '' ) . '">';
            if ( empty( $item['read'] ) ) {
                echo '<span class="airsoft-notifications__new">' . esc_html__( 'Unread', 'product-manager' ) . '</span> ';
            }
            echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['message'] ) . '</a>';
            $timestamp = (int) $item['time'];
            echo '<time datetime="' . esc_attr( gmdate( 'c', $timestamp ) ) . '">' . esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) ) . '</time></li>';
        }
        echo '</ul></section>';
    }

    public static function preview_items( $limit = 10 ) {
        $result = array();
        foreach ( array_slice( self::items( get_current_user_id() ), 0, $limit, true ) as $key => $item ) {
            $timestamp = (int) $item['time'];
            $result[] = array(
                'id' => $key,
                'message' => $item['message'],
                'url' => wp_validate_redirect( $item['url'], self::url() ),
                'datetime' => gmdate( 'c', $timestamp ),
                'time' => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ),
                'unread' => empty( $item['read'] ),
            );
        }
        return $result;
    }

    public static function ajax() {
        if ( ! is_user_logged_in() || ! function_exists( 'WC' ) ) {
            wp_send_json_error( array( 'message' => __( 'Please sign in again.', 'product-manager' ) ), 401 );
        }
        check_ajax_referer( 'airsoft_notifications', 'nonce' );
        if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'product-manager' ) ), 405 );
        }
        $mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'list';
        if ( ! in_array( $mode, array( 'list', 'read', 'all' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'product-manager' ) ), 400 );
        }
        $items = self::items( get_current_user_id() );
        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        if ( 'read' === $mode && ! isset( $items[$id] ) ) {
            wp_send_json_error( array( 'message' => __( 'Notification no longer available.', 'product-manager' ) ), 404 );
        }
        if ( 'list' !== $mode ) {
            foreach ( $items as $key => $item ) {
                if ( empty( $item['read'] ) && ( 'all' === $mode || $key === $id ) ) {
                    $item['read'] = true;
                    update_user_meta( get_current_user_id(), $key, $item );
                }
            }
        }
        wp_send_json_success( array( 'count' => self::unread_count(), 'items' => self::preview_items() ) );
    }

    public static function link( $atts = array() ) {
        if ( ! function_exists( 'WC' ) || ! is_user_logged_in() ) {
            return '';
        }
        self::$header_rendered = true;
        $atts = shortcode_atts( array( 'limit' => 5, 'label' => __( 'Notifications', 'product-manager' ) ), $atts, 'airsoft_notifications' );
        $limit = max( 1, min( 10, absint( $atts['limit'] ) ) );
        $label = sanitize_text_field( $atts['label'] );
        $id = wp_unique_id( 'airsoft-notifications-' );
        $count = self::unread_count();
        ob_start();
        ?>
        <div class="airsoft-notification-widget" data-limit="<?php echo esc_attr( $limit ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
            <button type="button" class="airsoft-notification-toggle" aria-label="<?php echo esc_attr( $label . ( $count ? ' (' . $count . ')' : '' ) ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>">
                <span class="airsoft-notification-symbol" aria-hidden="true">
                    <span class="airsoft-notification-icon"></span>
                    <span class="airsoft-notification-badge"<?php echo $count ? '' : ' hidden'; ?>><?php echo esc_html( $count > 99 ? '99+' : $count ); ?></span>
                </span>
                <span class="airsoft-notification-caption"><?php echo esc_html( $label ); ?></span>
            </button>
            <section id="<?php echo esc_attr( $id ); ?>" class="airsoft-notification-panel" aria-label="<?php echo esc_attr( $label ); ?>" hidden>
                <div class="airsoft-notification-heading"><strong><?php echo esc_html( $label ); ?></strong><button type="button" class="airsoft-notification-read-all"<?php echo $count ? '' : ' disabled'; ?>><?php esc_html_e( 'Mark all as read', 'product-manager' ); ?></button></div>
                <p class="airsoft-notification-feedback" role="status" aria-live="polite"></p>
                <ul class="airsoft-notification-preview">
                    <?php foreach ( self::preview_items( $limit ) as $item ) : ?>
                        <li class="<?php echo $item['unread'] ? 'is-unread' : ''; ?>"><a href="<?php echo esc_url( $item['url'] ); ?>" data-notification-id="<?php echo esc_attr( $item['id'] ); ?>">
                            <?php if ( $item['unread'] ) : ?><span class="airsoft-notification-unread"><?php esc_html_e( 'Unread', 'product-manager' ); ?></span><?php endif; ?>
                            <span><?php echo esc_html( $item['message'] ); ?></span><time datetime="<?php echo esc_attr( $item['datetime'] ); ?>"><?php echo esc_html( $item['time'] ); ?></time>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
                <p class="airsoft-notification-empty"<?php echo self::preview_items( 1 ) ? ' hidden' : ''; ?>><?php esc_html_e( 'You’re all caught up.', 'product-manager' ); ?></p>
                <a class="airsoft-notification-view-all" href="<?php echo esc_url( self::url() ); ?>"><?php esc_html_e( 'View all notifications', 'product-manager' ); ?></a>
            </section>
            <noscript><a href="<?php echo esc_url( self::url() ); ?>"><?php echo esc_html( $label ); ?></a></noscript>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function banner() {
        if ( self::$header_rendered || ! function_exists( 'WC' ) || ! is_user_logged_in() || is_wc_endpoint_url( 'notifications' ) || ! self::unread_count() ) {
            return;
        }
        echo '<aside class="airsoft-notifications-banner" aria-label="' . esc_attr__( 'Unread notifications', 'product-manager' ) . '">' . self::link() . '</aside>';
    }

    public static function assets() {
        if ( function_exists( 'WC' ) && is_user_logged_in() ) {
            wp_enqueue_style( 'airsoft-notifications', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/css/notifications.css', array(), filemtime( AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/css/notifications.css' ) );
            wp_enqueue_script( 'airsoft-notifications', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/js/notifications.js', array(), filemtime( AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/js/notifications.js' ), true );
            wp_localize_script( 'airsoft-notifications', 'airsoftNotifications', array(
                'url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'airsoft_notifications' ),
                'unread' => __( 'Unread', 'product-manager' ),
                'error' => __( 'Could not update notifications. Please try again.', 'product-manager' ),
            ) );
        }
    }
}

