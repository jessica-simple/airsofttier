# Product Manager

Product Manager adds an admin area for WooCommerce product categories and keeps
the site's group-buy pricing, shortcodes, cart display, GB Deals archive, and
countdowns together in one plugin.

## Cart follow-ups

Open **Product Manager → Cart Follow-ups** to review inactive WooCommerce carts,
see their saved items and cart value, and mark follow-ups as contacted or
dismissed. The page sends an email summary to the configured notification
address after a cart has been inactive for the selected time (60 minutes by
default). Notifications are checked every 15 minutes, and old records are
removed after 90 days.

Logged-in shoppers are associated with their WordPress account. Guest carts are
listed as guests; an email action appears only when an email address is known.
Cart snapshots are marked converted when an order is placed.

## Remove products from a category

In WordPress admin, open **Product Manager → Categories**, select a category,
and optionally search for products. Select products and choose **Remove selected
products from this category**, or use a product's **Remove** button. This only
removes the selected category assignment. It preserves the product, its other
category assignments, and all product details.

## Move from the legacy GB plugin

Upload Product Manager and the updated `wc-group-buy-tiers.php` compatibility
loader from this project before activating Product Manager. If you cannot update
the legacy plugin file first, deactivate **WooCommerce Group Buy & Tiered Pricing**
before activating Product Manager. This prevents duplicate group-buy handlers.
Group-buy settings and the `[gb_deal_info]`, `[gb_deal_list]`, and
`[gb_deal_banner]` shortcodes continue to use the same product metadata.

WooCommerce must be installed and active.

## Customer notifications

Product Manager also provides **My Account → Notifications**, its unread indicator,
and order/payment/refund/customer-note/shipping/cart-stock event handlers. The
`[airsoft_notifications limit="5" label="Notifications"]` shortcode renders a header
bell with an unread badge and dropdown. See [SHORTCODES.md](SHORTCODES.md) for placement and
[NOTIFICATIONS.md](NOTIFICATIONS.md) for supported events and shipping integration.
Existing notifications and read states are preserved when moving from the child theme.
