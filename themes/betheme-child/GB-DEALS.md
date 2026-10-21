# GB Deals shop archive

Visit `/gb-deals/` to browse all active group-buy products in the existing
WooCommerce / BeTheme shop layout. No WordPress Page or shortcode is required.
Add this URL as a custom menu link with the label **GB Deals** if needed.

Products must have **Enable Group Buy** checked in Product Data > General.
Expired deals are excluded; products with no end date remain eligible. End
dates use the WordPress site's timezone, matching the group-buy plugin.
WooCommerce's catalog visibility and stock settings still apply.

On single-product pages, active GB items with a future end date show the
plugin's countdown. If a custom BeBuilder layout omits the native deal summary,
the child theme places a countdown beneath the product price (or at the start
of the product content when no price is rendered). Existing timers are reused
without duplication. Disabled, expired, undated, and password-protected products
receive no fallback timer. The plugin handles ticking and fresh expiry data.

The same countdown appears on active GB items in native WooCommerce and
BeTheme product lists, including Shop, GB Deals, category/search results, and
related-product loops. Each card uses its own product ID. The plugin's timer
also picks up newly rendered cards after AJAX filtering or pagination.
List countdowns sit immediately after `.mfn-li-product-row-price`, including
cards inserted by filters or load more. Native WooCommerce cards use `.price`.

The native shop controls provide sorting, filtering, result counts, and
pagination (`/gb-deals/page/2/`). Existing metadata filters are grouped with
AND so OR filters cannot include non-deal products. The standard shop and other
product archives are unaffected.

Rewrite rules refresh once automatically after installing this child-theme
change. If the URL returns 404 after deployment, save **Settings > Permalinks**
once and clear any page cache.

Before release, check the archive with future, expired, blank, and missing end
dates; disabled GB products; catalog-hidden products; sorting; filters; an
empty result; and a second page of results. This checkout has no configured
WordPress database or running storefront, so live layout and integration
verification must happen on the installed site.
