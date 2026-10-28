# Customer notifications

Product Manager adds **My Account > Notifications** with an unread count, a site-wide link while notifications are unread, and a **Mark all as read** action. The latest 100 notifications are retained per customer. These are in-site notifications; WooCommerce continues to send its normal emails.

## Events

- Order placed: classic checkout and Checkout blocks, deduplicated across hooks.
- Payment required: initial unpaid checkout and subsequent transitions to pending payment.
- Payment received: successful WooCommerce payment completion.
- Payment failed, processing, on hold, completed, and cancelled: corresponding order status transitions.
- Refund issued: each refund, including partial refunds, with its amount and currency.
- Customer note added: customer-visible notes only; private staff notes are excluded.
- Shipped: a `shipped` order status, or standard `_wc_shipment_tracking_items` saved through the WooCommerce order object. A completed order alone does not imply shipment. Other shipping integrations can call `do_action( 'airsoft_order_shipped', $order_id );` after confirming shipment. The alert links to the order where the integration displays tracking details.
- Cart item became unavailable: a product already in the cart is now out of stock.
- Cart quantity unavailable: the total quantity sharing a stock pool exceeds available stock and backorders are disabled.

The stock check runs on the next normal frontend page request. Each changed stock condition adds a cart notice and an inbox entry for a signed-in customer. Reloading without a stock/cart change does not repeat the alert. The unread link appears throughout the site, including pages that do not render WooCommerce notices. A customer can mark notifications as read; stock messages represent the state when the alert was created, while the cart always validates the current state.

Guests receive the existing WooCommerce cart notices and order emails. The persistent inbox belongs to signed-in customers, identified by the order's customer ID, and does not match users by billing email. The inbox starts collecting events once this code is deployed; it does not import past orders.

## Integration

The implementation is in `includes/class-product-manager-notifications.php`, loaded and initialized by `product-manager.php`. The plugin registers the cart stock checker and order events. Both the standard WooCommerce account menu and the child theme's custom navigation include the Notifications link while Product Manager is active. The query URL uses the existing account page and requires no permalink flush. No notification logic or assets are loaded by the theme.

The optional `[airsoft_notifications limit="5" label="Notifications"]` shortcode renders an accessible header bell and dropdown. Add it to a WordPress Shortcode block or BeTheme header shortcode element. `limit` accepts 1–10, defaulting to 5. `assets/css/notifications.css` and `assets/js/notifications.js` provide its appearance and behavior independently of the theme. Separate desktop/mobile instances synchronize their unread badges. The dropdown stays inside the viewport and is placed outside header containers to avoid clipping.

The count refreshes on page load and each time the dropdown opens. Opening alone does not mark anything read. Clicking an item marks it read before navigating; **Mark all as read** updates the dropdown immediately. **View all notifications** opens the existing inbox. Escape, outside clicks, and the bell toggle close the dropdown. A failed refresh displays a retry message; a failed read update still lets the customer follow the notification's destination. Requests require a signed-in customer and a nonce, and read updates can access only that customer's stored records.

The floating unread indicator is suppressed when the header shortcode is rendered. Without a header shortcode, the fallback indicator uses the same dropdown. Guests see no bell. Without JavaScript, the shortcode provides a link to the full inbox.

Notification records are separate customer metadata entries so concurrent events do not replace the whole inbox. Read actions require the signed-in customer's nonce and operate only on that customer's records. Orders use WooCommerce data APIs, including shipping metadata, for HPOS compatibility.

The move from the child theme preserves the existing notification metadata, read states, event markers, stock signatures, shortcode, and shipping action. No data migration is needed. Deploy the updated plugin and theme together and keep Product Manager active. Deactivating Product Manager hides its notification interface and stops collecting events; existing records remain stored.
