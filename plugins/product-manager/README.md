# Product Manager

Product Manager adds an admin area for WooCommerce product categories and keeps
the site's group-buy pricing, shortcodes, cart display, GB Deals archive, and
countdowns together in one plugin.

## Sold-out cart items

Sold-out products are grouped under **Invalid items** in the cart and can be
removed individually or all at once. Proceeding to checkout temporarily removes
them from the order cart; returning to the cart without placing an order restores
them.

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

## Reseller marketplace

Member and reseller accounts are separate WordPress identities with different
emails and sign-in pages. My Account remains the member dashboard and has no
reseller menu. An approved reseller uses one reseller role for discounted buying
and selling inside the **Reseller Portal**. Admins review applications under
**Product Manager → Resellers**. Pending/rejected reseller accounts retain their
separate account type and cannot buy or sell until approved. Revoked sellers'
listings cannot be purchased. Resellers cannot buy their own items.

### Setup and operation

1. Deploy the complete Product Manager plugin folder and visit WordPress admin
   as an administrator. The plugin creates three published pages once, without
   overwriting existing pages: **Reseller Registration**, **Reseller Login** and
   **Reseller Portal**. Default slugs are `/reseller-registration/`,
   `/reseller-login/` and `/reseller-portal/`; WordPress may append a suffix if
   a slug already exists. Use the actual links under **Product Manager → Resellers**.
   Add the registration/login pages to the site's public menu as desired.
   Exclude all three pages from page caching.
2. Set the commission percentage under **Product Manager → Reseller payouts**.
   It defaults to **0%**. Each order item snapshots its seller and commission at
   checkout, so later settings and product ownership changes do not alter orders.
3. Applicants register from the separate registration page while logged out,
   using an email not already attached to any account. They can sign in through
   Reseller Login to see their application status. Approve applications under
   **Product Manager → Resellers**. Member sign-in rejects reseller accounts,
   and reseller sign-in rejects member accounts. A member wishing to apply must
   log out and register a second identity with a different email.
4. Approved resellers submit products through **Reseller Portal → My products**. Review pending
   products in WooCommerce **Products**, then publish approved listings.
5. Resellers receive email notifications when orders enter a paid processing or
   completed state. **Sales and earnings** shows only their order items, their earnings,
   and the delivery address needed to fulfil the order. They add carrier/tracking
   details per order item; customers see these in their order and customer notes.
6. Admins review fulfilment and mark the WooCommerce order completed. Resellers
   cannot complete a whole order that may contain other sellers' items.
7. Pay sellers externally, then record the payment reference per item under
   **Reseller payouts**. Recording a payment does not transfer funds. A unique
   ledger key prevents recording a second payment for the same item.

### WooCommerce email sender

Open **Product Manager → WooCommerce Emails** or **WooCommerce → Settings → Emails**
to change the shared sender name and email address. Both screens use WooCommerce's
native sender options. Order, payment and customer emails, reseller application
and seller notifications, and cart follow-up messages use these settings.
The old reseller-only override is no longer read; existing WooCommerce settings
are preserved. Unrelated WordPress emails retain their current sender. Use an
address authorised by your mail provider. An SMTP plugin configured to force its
sender may override these settings.

### Reseller account tools and existing accounts

The portal includes **My purchases**, billing/shipping addresses, account details
and notifications (when the notifications module is present). Purchases are
queried by the signed-in reseller's customer ID; sales are queried by the seller
snapshot on each order item. WooCommerce's existing validated account/address
forms handle profile, password and address edits. Member account URLs redirect
resellers to their portal, with old order links routed to reseller purchase
details. Password reset and logout continue to use WordPress/WooCommerce's
standard flows.

Existing approved resellers keep their user IDs, products, purchases and earnings.
On their next sign-in the plugin removes their customer/subscriber roles and
marks them as reseller accounts. Their historical purchases remain attached to
that reseller identity; the plugin does not split or transfer history. To also
use a member account, they must register a new member identity with another email.
Legacy pending member applications remain visible for reference but cannot be
approved into mixed accounts; those applicants must register separately.

This uses separate identities in one WordPress installation, with one active
sign-in per browser session. Use different browser profiles for simultaneous
member and reseller sessions. Rejected accounts keep their application status;
admins can later approve the same reseller identity after resolving the issue.

### Prices and earnings

- Admins can set a reseller price on simple products and individual variations.
  Resellers set it on their own submissions. Blank means use normal pricing;
  zero is a valid price. Front-end submissions reject a reseller price above the
  normal price.
- The lower of the existing WooCommerce/group-buy price and reseller price wins.
  These two prices do not stack. Ordinary WooCommerce coupon rules still apply.
  Price filters, variation ranges, classic carts and Store API carts use the
  customer's eligible price. Cart recalculation reloads the saved product price
  so discounts do not compound, and role changes are reflected on recalculation.
- Earnings use item totals after coupons, excluding tax and shipping, less item
  refunds and the commission captured at checkout. The site retains checkout
  tax and shipping amounts. Shipping reimbursements must be settled separately.
- Only paid, completed orders are eligible for payout. Refunds without full item
  allocation block payout recording until an admin reconciles the refund in
  WooCommerce. Refunds after a recorded payout show the difference for manual
  reconciliation; payment history is retained.
- New marketplace orders are supported. Existing historical orders are not
  assigned to resellers retroactively. Order access uses WooCommerce CRUD and
  the shared order-item tables, supporting both legacy storage and HPOS.

### First-version boundaries

The reseller submission form supports physical **simple products** with one photo
(JPEG, PNG or WebP, at most 5 MB). Admins can manage variations through WooCommerce.
Resellers edit only their own simple products. Every edit takes the listing
offline and resubmits it as pending approval. Product categories, shipping classes,
weights and dimensions can be assigned by the admin during review. Checkout uses
the site's existing shipping and tax setup; there are no separate seller shipping
rates, automatic split payments, scheduled payouts or automated refund recovery.

Keep account, cart and checkout pages excluded from page caching. Any shop or
product page cache must vary by reseller eligibility so customer-specific prices
are not shared between visitors.

### Validation

Run the standalone checks from the repo root:

```text
php -n plugins/product-manager/tests/reseller-regression.php
php -n plugins/product-manager/tests/reseller-applications.php
php -n plugins/product-manager/tests/reseller-accounts.php
php -n plugins/product-manager/tests/reseller-emails.php
```

The standalone tests cover account separation, duplicate emails, signup roles,
login restrictions, portal access, legacy identities, routing, ownership,
eligibility, price precedence, zero/blank prices,
repeated cart totals, role changes, seller ownership, immutable order snapshots,
commission/refund calculations, refund holds and payout permissions.

Before production, test on a WordPress/WooCommerce staging site:

1. Apply, approve, reject and revoke accounts; check dashboard and checkout access.
   Confirm separate emails are required, cross-account login is rejected, member
   menus omit reseller tools, all three generated pages work, and address/profile
   saves return to the reseller portal. Verify password reset and purchase details.
2. Submit a photo and product, approve it, then edit and reapprove it. Attempt to
   edit another reseller's product by changing the submitted ID.
3. Compare guest/member/reseller prices, including variations, sales, group-buy
   expiry and coupons, in both classic and block checkout. Switch eligibility
   while a cart exists and confirm totals refresh.
4. Place a mixed-seller order and verify each dashboard's item and address scope,
   email notification and customer tracking. Test on both legacy storage and HPOS.
5. Record payment on a completed order, resubmit the payment request, refund an
   item before and after payout, and test a refund without item allocation.

## Customer notifications

Product Manager also provides **My Account → Notifications**, its unread indicator,
and order/payment/refund/customer-note/shipping/cart-stock event handlers. The
`[airsoft_notifications limit="5" label="Notifications"]` shortcode renders a header
bell with an unread badge and dropdown. See [SHORTCODES.md](SHORTCODES.md) for placement and
[NOTIFICATIONS.md](NOTIFICATIONS.md) for supported events and shipping integration.
Existing notifications and read states are preserved when moving from the child theme.
