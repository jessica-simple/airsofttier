# AI Development Rules

## WordPress Theme Modification

This project uses the BeTheme parent theme with a custom child theme.

### Critical Rule

All custom WordPress/BeTheme modifications MUST be made inside:

`betheme-child/`

NEVER directly modify files inside:

`betheme/`

The `betheme/` directory is the parent theme and must remain unchanged.

### Why

Changes made directly to `betheme/` can be lost when the parent theme is updated.

Use the child theme for:

- PHP customizations
- Template overrides
- CSS
- JavaScript
- WordPress hooks and filters
- WooCommerce customizations
- Navigation/menu customizations
- Theme-specific functionality

### Before Editing

AI/developers must first inspect the existing parent-theme implementation and determine the correct child-theme override, hook, filter, or extension point.

Do not copy large amounts of parent-theme code unnecessarily.

Prefer the smallest maintainable child-theme customization.

### Current Customization

The WooCommerce category toggle is implemented in `js/woocommerce.js` and styled in `style.css`.

BeTheme's parent-theme `js/woocommerce.js` adds `.cat-expander` toggles to WooCommerce block category-list items when they contain a nested list. Its WooCommerce Product Categories widget uses the `product_cat` taxonomy and renders hierarchy through `wp_list_categories()` and the WooCommerce category walker, with `.product-categories`, `.cat-item`, and nested `.children` markup.

The child-theme handler detects both block-list items and classic Product Categories widget items by checking for their rendered direct child `<ul>`. It does not compare category names or IDs; WordPress/WooCommerce remains responsible for the term hierarchy. It reuses the `.cat-expander` class, BeTheme icon glyph, `.li-expanded` state, and 300 ms slide behavior. Classic-widget styling is added in the child theme because BeTheme's existing toggle CSS only targets the block widget.

The previous child-theme handler only selected `.wc-block-product-categories-list-item`, so classic widget items (`li.cat-item`) were not recognized and did not receive a toggle even when they had child markup. The change is in `js/woocommerce.js` and `style.css`.

The local checkout does not include WP-CLI, WordPress configuration, a connected database, or a running `/shop/` page. Therefore the live `Gas CO2 Others` term ID, parent ID, child terms, and the exact shop-page widget output could not be independently verified here. Do not document guessed IDs or term data.

The category data must remain dynamic and come from WordPress/WooCommerce. Never hard-code the category name, term ID, or child list.

Do not hard-code category names or child categories unless specifically required.

### Advanced Filter Category Toggles

WooCommerce category filters are generated dynamically by BeTheme from taxonomy term data. A category filter parent is identified by its rendered checkbox label (`.mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label`) and a direct child `<ul>`, not by its name or term ID.

The existing accessible expand/collapse button and behavior are implemented in the child theme's `js/mfn-opt-expandable.js`, with its styling in `style.css`. The script marks only filter items with child lists as expandable, inserts a button separate from the checkbox label, and observes DOM updates so AJAX-refreshed filters receive the same behavior.

The filter markup previously included its child `<ul>` but did not carry the `.mfn-opt-expandable` class required by the existing child-theme handler. As a result, the existing handler never initialized those filter parents. Detection must remain generic: never hard-code category names or IDs for toggle behavior.

Never directly modify files inside `betheme/`.

### Shop Filter Category Visibility

The Product Categories admin screen provides a **Show in Shop Filter** checkbox through `includes/class-mfn-child-product-category-filter.php`. Its value is stored in WordPress term metadata under `_mfn_child_show_in_shop_filter`; a missing value is treated as enabled to preserve the existing visibility of categories.

BeTheme builds the advanced-filter category hierarchy in `sc_filters()` with `get_terms()` calls for roots and each child level. The child theme filters `get_terms` results only when the call comes from `sc_filters()` and the taxonomy is `product_cat`. Disabled parents are omitted before their child terms are requested, so their entire branch is excluded. For enabled parents, each returned child is evaluated using its own setting.

This setting only affects categories rendered in the BeTheme advanced-filter form. It does not alter the taxonomy, product assignments, or product-category archive pages. The existing category expand/collapse behavior continues to detect the rendered child `<ul>` and is independent of this setting.

New categories default to enabled. When an administrator saves a category, the checkbox state is persisted as `yes` or `no`.

All implementation files belong inside `betheme-child/`. Never edit `betheme/`.
