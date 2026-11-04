# Site Feature Guide

This guide summarizes the custom features implemented in this WordPress/WooCommerce site repository. It covers the BeTheme child theme and the Product Manager plugin; it does not describe the bundled BeTheme, WooCommerce, or other third-party plugin features unless they are extended here.

## Storefront and shopping

### Product browsing and discovery

- **Sale archive (`/sale/`)** — lists WooCommerce products that are currently on sale. It uses the normal shop archive, so sorting, filters, result counts, pagination, and theme layouts continue to apply. Paginated URLs use `/sale/page/2/`.
- **New Arrivals archive (`/new-arrivals/`)** — lists products published within the most recent 30 days, newest first by default. An explicit sort selection is respected. The period can be changed by the `mfn_child_new_arrivals_period_days` filter. Pagination uses `/new-arrivals/page/2/`.
- **Group Buy Deals archive (`/gb-deals/`)** — lists eligible active group-buy products in the normal shop layout. Expired deals are excluded; products without an end date remain eligible. Product visibility and stock rules still apply. Pagination and shop filters are supported.
- **Expandable category filters** — category filters with children can expand and collapse. The controls work with BeTheme's AJAX filter refreshes and keep parent and child state synchronized with Show More/Show Less and Reset All.
- **Shop-filter category visibility** — product categories have a **Show in Shop Filter** setting in the category editor. Disabling a parent category removes its branch from BeTheme's advanced Shop filter; it does not remove products or change category archives. New categories default to visible.
- **Mobile filter controls** — the child theme adds responsive filter presentation and interaction for smaller screens.
- **Product-card information controls** — custom BeBuilder `shop_products` options support showing or hiding each product's category, SKU, and tags. These options currently include parent-theme builder patches; see [BeTheme update notes](themes/betheme-child/UPDATE-README.md).
- **Brand displays** — brand grids and brand lists are available through shortcodes. Brand lists can hide empty brands or show product counts. The grid uses the site's configured brand taxonomy/plugin data.
- **Search shortcodes** — a WooCommerce product search form and a brand search form can be embedded in content or a header.

### Group-buy promotions

- Products enabled for group buy can display tiered deal information and pricing through the site's group-buy integration.
- Active deals with a future end time show a countdown on product pages and product cards, including AJAX-loaded or paginated cards. Deal timers are not added to disabled, expired, undated, or password-protected products.
- A fallback countdown is added to single-product layouts that omit the native deal summary, without duplicating a timer already present.
- Store administrators can embed group-buy information, a deal list, or a deal banner in page content or slider layers with the shortcodes documented in [Product Manager Shortcodes](plugins/product-manager/SHORTCODES.md).
- The GB Deals archive and countdown display continue to use the existing group-buy product metadata and WooCommerce catalog behavior.

### Cart and checkout

- **Unavailable cart items** — sold-out cart products are grouped under **Invalid items**, with individual and remove-all actions. They are temporarily removed when checkout begins and restored if the shopper returns without placing an order. The behavior also supports WooCommerce Cart blocks.
- **Cart quantity layout** — the child theme adjusts the cart quantity control into a compact quantity cell.
- **Cart trust information** — the cart can show the store's authenticity, support, returns, and payment reassurance strip on classic and block carts.
- **Cart item count shortcode** — a shortcode is available for displaying the current WooCommerce cart item count.
- **Side-cart refinements** — BeTheme's native side cart remains enabled; the child theme keeps its native handlers and count badge, exposes drawer actions on the cart page, and adjusts empty-cart button visibility.
- **Checkout and order confirmation styling** — the repository includes child-theme refinements for the checkout and order success experiences. The underlying order and payment behavior remains WooCommerce-controlled.

### Wishlist and customer account

- **Wishlist** — customers can save products from product cards and single-product pages, review them under **My Account → Wishlist**, remove saved items, and add saved products to the cart. Wishlist actions are available to signed-in customers; login can return a shopper to the requested wishlist action.
- **Custom account dashboard** — the My Account landing page shows customer details, order activity, recent orders, address summaries, and shortcuts to account sections.
- **Account navigation** — custom sidebar navigation includes Dashboard, Orders, Wishlist, Coupons, Addresses, Account Details, Change Password, and Log Out, with responsive styling.
- **Coupons view** — a custom account endpoint lists coupons available to the customer.
- **Registration spam honeypot** — the WooCommerce registration form includes a hidden anti-bot field and rejects submissions that fill it.

## Customer notifications and cart follow-ups

- **Notification inbox** — signed-in customers can view recent order, payment, refund, customer-note, shipment, and cart-stock notifications under **My Account → Notifications**. The unread count is shown in account navigation and through an unread site-wide link. Customers can mark notifications read individually or all at once. The inbox retains up to 100 notifications per customer and begins collecting events after deployment; it does not backfill historical orders.
- **Header notification bell** — `[airsoft_notifications]` adds an accessible bell/dropdown with unread count, latest notices, mark-all-read, and an inbox link. Without the shortcode, the plugin shows a floating bell when there are unread notices. Opening the dropdown refreshes the list without marking it read.
- **Cart follow-ups** — administrators can review inactive carts under **Product Manager → Cart Follow-ups**, inspect cart contents and value, and mark records contacted or dismissed. Scheduled checks send a summary to the configured notification address after the inactivity threshold (60 minutes by default); checks run every 15 minutes and old records are removed after 90 days. Guest carts are identified as guests, and an email action is offered only when an address is known. Cart records are marked converted when an order is placed.

## Reseller marketplace

The marketplace provides separate member and reseller identities, registration/login pages, an approval process, product listings, seller order fulfillment, and administrator-controlled earnings and payouts. Its user-facing entry points are `/reseller-registration/`, `/reseller-login/`, and `/reseller-portal/` (WordPress may add a suffix if a slug is already taken). The generated pages use a standalone layout and should be excluded from page caching.

### Reseller access and products

- Applicants register with a separate email/account and can check their application status. Administrators approve, reject, or revoke reseller access under **Product Manager → Resellers**.
- Approved resellers use the **Reseller Portal** to manage listings, sales, purchases, earnings, bank details, addresses, account details, and notifications (when the notification module is active).
- Product submissions support physical simple products and variable submissions with up to 50 values for one option. Submitted products require administrator review before publication. Resellers can edit their own simple products; an edit returns the listing to pending review. Administrators manage variations after submission.
- Reseller products can have reseller prices on simple products and individual variations. The lower of the normal/group-buy price and reseller price applies; discounts do not stack. Blank uses normal pricing and zero is a valid reseller price. Normal WooCommerce coupons still apply.
- Resellers cannot buy their own listings. Revoked sellers' listings cannot be purchased. Existing historical orders are not assigned to resellers retroactively.

### Orders, shipping, and returns

- At checkout, each order item records a seller and commission snapshot. This keeps the order's seller and commission stable if the product owner or commission setting later changes.
- Resellers see only their own order items, with the delivery information needed to fulfill them. They can search/filter orders, export matching rows to CSV, set item fulfillment status, and enter a carrier, tracking number, and optional tracking link.
- Customers see shipment tracking on their order and receive shipment notifications. Mixed-seller orders are handled item by item; resellers cannot complete an entire WooCommerce order on behalf of other sellers.
- An approved reseller can request a return review for an eligible paid item. Review approval is not a refund. WooCommerce remains the system for processing refunds, and unresolved returns/refund reconciliation hold payout release.
- Reseller order, shipment, return, payout, customer tracking, and CSV details identify selected product variations.

### Earnings and payouts

- Earnings are calculated from item totals after coupons, excluding tax and shipping, minus item refunds and the commission captured at checkout. Totals are grouped by currency.
- An item becomes eligible for release only after its WooCommerce order is paid and completed and refund/return checks are clear. Administrators retain control of WooCommerce order status and payout release.
- The administrator configures the commission and default payout destination. **Wallet** mode supports wallet credits and withdrawal requests. A withdrawal reserves funds until an administrator records an external payment or rejects the request (which returns the reserved funds).
- **Bank** mode pauses wallet credits/withdrawals and hides wallet panels while preserving balances and ledger history. In Wallet mode, administrators may choose Wallet or Bank per completed item. Bank transfers happen outside the site; admins record a transfer reference and the payout stores a snapshot of the bank details used.
- A unique payout ledger key prevents the same order item from being released twice. Existing manual payout records remain visible but are not credited to the wallet. Refunds after payout remain recorded for manual reconciliation; the site does not automatically claw back money.
- The portal overview summarizes live products, orders needing shipment, eligible earnings, and recent items to ship. Earnings show eligible amounts, amounts awaiting order completion/review, payouts, and refund adjustments.

### Reseller administration and email

- The Resellers screen provides status tabs/counts, search, sorting, pagination, expandable application details, and individual or bulk approval/rejection/revocation actions.
- Administrators can review open return requests and closed history, configure commission and payout destination, inspect reseller bank details, and release item payouts from **Product Manager → Reseller payouts**.
- The shared WooCommerce sender name/address can be changed under **Product Manager → WooCommerce Emails** or **WooCommerce → Settings → Emails**. Registration sender diagnostics can show the effective From header and mail handoff details without storing recipients or message bodies.
- Resellers receive order, shipment, and payout update emails. Email acceptance by the site's mail function does not by itself confirm delivery by the mail provider.

See [Product Manager README](plugins/product-manager/README.md) for setup steps, operating rules, first-version boundaries, and validation notes.

## Administrator product tools

- **Product Manager → Categories** supports selecting or searching products and removing selected products from a category. The operation removes only that category assignment; the product and its other details and categories remain.
- Product Manager also centralizes group-buy compatibility, deals, cart follow-ups, reseller workflows, notification behavior, and WooCommerce email settings described above.

## Shortcode reference

| Shortcode | Purpose |
| --- | --- |
| `[woocommerce_search]` | WooCommerce product search form |
| `[search_brand]` | Brand search form |
| `[brands_grid]` | Brand grid |
| `[woocommerce_brands_list]` or `[wc_brands_list]` | Brand list; supports options such as `hide_empty` and `show_count` |
| `[betheme_social_icons]` or `[mfn_social_icons]` | Social icon links |
| `[woocommerce_cart_items_count]` | Current cart item count |
| `[airsoft_notifications limit="5" label="Notifications"]` | Customer notification bell/dropdown |
| `[airsoft_reseller_register]` | Reseller registration page content |
| `[airsoft_reseller_login]` | Reseller login page content |
| `[airsoft_reseller_portal]` | Reseller workspace |
| `[gb_deal_info id="123"]` | Group-buy details for a product |
| `[gb_deal_list limit="10" columns="3"]` | Active group-buy product list |
| `[gb_deal_banner show="thumbnail,title,countdown,price"]` | Group-buy banner fields for a slider or page |

See [Product Manager Shortcodes](plugins/product-manager/SHORTCODES.md) for all group-buy options and detailed usage.

## Implementation and operational notes

- Custom theme behavior belongs in `themes/betheme-child/`. The `themes/betheme/` directory is the BeTheme parent theme and may be overwritten by upstream updates. The product card builder fields currently require restoring the documented parent-theme patches after a parent theme update.
- The site requires WordPress and WooCommerce. Group-buy and reseller behavior also requires Product Manager and the related group-buy product metadata/integration to be active.
- Keep reseller registration/login/portal, My Account, cart, and checkout out of full-page caching. Product or shop caches must vary by reseller eligibility because reseller prices can be customer-specific.
- This guide describes code present in the repository. Runtime availability also depends on deploying and activating the relevant plugin/theme files and on the site's WordPress, WooCommerce, and plugin configuration.

