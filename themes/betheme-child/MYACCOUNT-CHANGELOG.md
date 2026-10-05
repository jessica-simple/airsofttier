# My Account Dashboard Change Log

Date: 2026-06-04

## Summary

Updated the WooCommerce My Account area to match the provided `csw-dashboard.html` dashboard design.

## Files Changed

- `woocommerce/myaccount/dashboard.php`
  - Added a custom My Account dashboard template.
  - Replaced the default WooCommerce dashboard text with a card-based account dashboard.
  - Added dynamic customer data:
    - Display name
    - Email
    - Billing phone
    - Joined date
    - Active order count
    - Recent orders
    - Shipping and billing address summaries
  - Added quick links for orders, wishlist, coupons, and addresses.
  - Added account details card.
  - Added default addresses card.
  - Added refer-a-friend banner.

- `woocommerce/myaccount/navigation.php`
  - Added a custom My Account sidebar template.
  - Added SVG icons to navigation links.
  - Added sidebar links for:
    - Dashboard
    - Orders
    - Wishlist
    - Coupons
    - Addresses
    - Account Details
    - Change Password
    - Log Out
  - Preserved WooCommerce active menu state for core endpoints.

- `style.css`
  - Added scoped CSS for the new My Account dashboard.
  - Styled dashboard cards, sidebar, stats, quick links, recent orders, account details, addresses, and refer banner.
  - Added responsive rules for tablet and mobile layouts.
  - Scoped layout changes to `.woocommerce-account.logged-in` to avoid affecting the login/register page.

- `functions.php`
  - Removed the old `woocommerce_account_dashboard` hook-based dashboard output.
  - Removed duplicate custom dashboard functions:
    - `csw_custom_dashboard`
    - `csw_dashboard_top`
  - Dashboard output now comes from the WooCommerce template override instead of action hooks.

## Notes

- Wishlist links use the YITH Wishlist URL if the plugin is active. Otherwise, they fall back to the shop page.
- Coupon links currently point to the shop page because WooCommerce does not provide a default customer coupons endpoint.
- PHP CLI was not available in this environment, so PHP syntax linting could not be run here.

## Follow-up Fix

Date: 2026-06-04

- `style.css`
  - Scoped the My Account dashboard layout selectors to `#Content`.
  - Changed selectors like `.woocommerce-account.logged-in .woocommerce` to `.woocommerce-account.logged-in #Content .woocommerce`.
  - This prevents the dashboard grid/layout styles from affecting the BeTheme header account dropdown, including `mfn-header-login`, `.woocommerce`, and `mfn-header-modal-nav` elements.

## Follow-up Removal

Date: 2026-06-05

- `woocommerce/myaccount/dashboard.php`
  - Removed the T-Money points user meta lookup.
  - Removed the T-Money points stat card.
  - Removed the T-Money Rewards card.
  - Updated the refer banner copy so it no longer mentions T-Money points.

- `style.css`
  - Removed unused T-Money Rewards styles.
  - Changed the welcome card layout to a single full-width column.
  - Changed the stats row from four columns to three columns.
