# Betheme Update Notes - Custom Shop Product Fields

This file is a handoff note for the next developer or AI working on this site.

The project has custom `shop_products` builder options for:

- `show_category`
- `show_product_sku`
- `show_product_tags`

These options are not fully child-theme safe because part of the admin UI lives in the parent theme's generated BeBuilder form file.

Important:

- `show_category` is custom code for this project
- `show_product_sku` is custom code for this project
- `show_product_tags` is custom code for this project
- Do not assume any of these three fields are safe after a Betheme parent theme update

## Re-Add After Theme Update

When Betheme is updated, check and re-add these customizations in the parent theme if they are gone:

1. `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`
2. `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js`
3. `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
4. `wp-content/themes/betheme/visual-builder/visual-builder.php`

Usually no re-add is needed for these child theme files because they should survive the update:

1. `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php`
2. `wp-content/themes/betheme-child/functions.php`

Quick summary:

| File | Re-add after theme update? | Notes |
| --- | --- | --- |
| `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php` | Yes | Must contain `show_category`, `show_product_sku`, `show_product_tags` |
| `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js` | Yes | Admin builder will not show the field if this generated file is missing it |
| `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php` | Yes | Must pass `shop_products` attrs into global `$mfn_shop_products_attr` before calling `sc_shop_products()` |
| `wp-content/themes/betheme/visual-builder/visual-builder.php` | Yes | Builder preview must also pass `shop_products` attrs into global `$mfn_shop_products_attr` |
| `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php` | No | Child theme file, should remain after update |
| `wp-content/themes/betheme-child/functions.php` | No | Child theme file, should remain after update |

## Problem Summary

If `Product Tags` or the other custom toggles do not appear in the admin builder:

1. The PHP field may already exist in `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`
2. But the admin UI will still not show it if the generated BeBuilder JS file does not contain the same field

This already happened once with `show_product_tags`.

## Root Cause

Betheme does not read builder field definitions directly from PHP every time the admin loads.

It also uses a generated file here:

- `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-28.4.js`

The version number in the filename follows the theme version, so after future theme updates it may become something like:

- `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js`

If the PHP field exists but the generated JS file is missing the field, the option will not show in admin.

## Files Involved

### 1. Parent theme builder fields

Path:

- `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`

This is where the custom fields were added near the `shop_products` options.

Current custom block:

```php
array(
    'id' => 'show_category',
    'attr_id' => 'show_category',
    're_render' => true,
    'type' => 'switch',
    'title' => __('Product Category', 'mfn-opts'),
    'options' => array(
        '' => __('Default', 'mfn-opts'),
        '0' => __('Hide', 'mfn-opts'),
        '1' => __('Show', 'mfn-opts'),
    ),
    'std' => '',
),

array(
    'id' => 'show_product_sku',
    'attr_id' => 'show_product_sku',
    're_render' => true,
    'type' => 'switch',
    'title' => __('Product SKU', 'mfn-opts'),
    'options' => array(
        '' => __('Default', 'mfn-opts'),
        '0' => __('Hide', 'mfn-opts'),
        '1' => __('Show', 'mfn-opts'),
    ),
    'std' => '',
),

array(
    'id' => 'show_product_tags',
    'attr_id' => 'show_product_tags',
    're_render' => true,
    'type' => 'switch',
    'title' => __('Product Tags', 'mfn-opts'),
    'options' => array(
        '' => __('Default', 'mfn-opts'),
        '0' => __('Hide', 'mfn-opts'),
        '1' => __('Show', 'mfn-opts'),
    ),
    'std' => '',
),
```

Quick search:

- Search for `title_tag`
- The custom fields sit around that area in the `shop_products` form definition

### 2. Generated BeBuilder admin form file

Path for the current version:

- `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-28.4.js`

This file must also contain:

- `show_category`
- `show_product_sku`
- `show_product_tags`

Important:

- If any of these custom fields are missing here, they will not appear in admin even if the PHP field exists
- After a Betheme update, the filename may change with the theme version

Exact JS field snippets to restore if missing:

```js
{"id":"show_category","attr_id":"show_category","re_render":true,"type":"switch","title":"Product Category","options":{"":"Default","0":"Hide","1":"Show"},"std":""}
```

```js
{"id":"show_product_sku","attr_id":"show_product_sku","re_render":true,"type":"switch","title":"Product SKU","options":{"":"Default","0":"Hide","1":"Show"},"std":""}
```

```js
{"id":"show_product_tags","attr_id":"show_product_tags","re_render":true,"type":"switch","title":"Product Tags","options":{"":"Default","0":"Hide","1":"Show"},"std":""}
```

Expected placement in the generated file:

- `show_category`
- then `show_product_sku`
- then `show_product_tags`
- then `title_tag`

Useful search pattern in the generated JS:

```js
{"id":"show_category","attr_id":"show_category","re_render":true,"type":"switch","title":"Product Category","options":{"":"Default","0":"Hide","1":"Show"},"std":""},{"id":"show_product_sku","attr_id":"show_product_sku","re_render":true,"type":"switch","title":"Product SKU","options":{"":"Default","0":"Hide","1":"Show"},"std":""},{"id":"show_product_tags","attr_id":"show_product_tags","re_render":true,"type":"switch","title":"Product Tags","options":{"":"Default","0":"Hide","1":"Show"},"std":""},{"id":"title_tag"
```

### 3. Child theme frontend output

Path:

- `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php`

This file handles frontend behavior for the custom fields and should survive parent theme updates.

It already contains logic for:

- hide/show category
- hide/show SKU
- hide/show product tags

### 4. Parent theme shop_products render bridge

Paths:

- `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
- `wp-content/themes/betheme/visual-builder/visual-builder.php`

These files were also patched for this project.

Reason:

- the child helper uses global `$mfn_shop_products_attr`
- the shortcode wrapper sets that global
- but Betheme builder rendering and builder preview can call `sc_shop_products()` directly
- without this bridge, `show_product_tags="1"` may be selected in admin but product tags still do not show on the frontend or in builder preview

What the patch does:

- store current `shop_products` attrs in global `$mfn_shop_products_attr`
- call `sc_shop_products()`
- restore the previous global value afterward

If these parent theme changes are lost during update, the `Product Tags` toggle may appear in admin but still not work when set to `Show`

## What To Check First

When the admin option is missing, search these two files first:

1. `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`
2. `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js`

Search string:

- `show_product_tags`

If it exists only in the PHP file but not in the generated JS file, that is the reason the admin field is missing.

If the `Product Tags` field appears in admin but still does not render on the frontend when set to `Show`, also check:

1. `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
2. `wp-content/themes/betheme/visual-builder/visual-builder.php`
3. `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php`

Search strings:

- `mfn_shop_products_attr`
- `item_shop_products`
- `sc_shop_products($attr, 'sample')`

## How Betheme Generates The Admin JS

Useful references:

- `wp-content/themes/betheme/functions/admin/class-mfn-helper.php`
- `wp-content/themes/betheme/visual-builder/classes/visual-builder-class.php`
- `wp-content/themes/betheme/muffin-options/options.php`

Relevant behavior:

- Betheme can regenerate the BeBuilder fields file through `Mfn_Helper::generate_bebuilder_items()`
- Admin loads the generated JS file, not just the PHP definition
- Saving theme options may regenerate the file

## Recommended Recovery Steps After A Parent Theme Update

1. Re-check `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`
2. Confirm the three custom fields still exist
3. Check the current generated BeBuilder file in `wp-content/themes/betheme/visual-builder/assets/js/forms/`
4. Confirm that `show_category`, `show_product_sku`, and `show_product_tags` are present there too
5. Re-check `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php` and confirm `item_shop_products()` sets global `$mfn_shop_products_attr` before calling `sc_shop_products()`
6. Re-check `wp-content/themes/betheme/visual-builder/visual-builder.php` and confirm builder preview also sets global `$mfn_shop_products_attr` before calling `sc_shop_products($attr, 'sample')`
7. If the field is missing from the generated JS, regenerate the BeBuilder form file or patch it
8. Hard refresh the admin builder after the update

## Known Incident

On this project, `show_product_tags` was already added to:

- `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`

But it was missing from:

- `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-28.4.js`

Result:

- `Product Tags` did not appear in admin

Fix:

- Add the same field definition into the generated BeBuilder JS file or regenerate the file so it includes the field

Note:

- `show_category` and `show_product_sku` are also project custom code
- Treat all three fields as restore-required customizations after theme updates

## Second Known Incident

On this project, `Product Tags` also failed in another way:

- the toggle existed in admin
- but tags still did not render when set to `Show`

Root cause:

- `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php` depends on global `$mfn_shop_products_attr`
- Betheme builder rendering in `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
- and builder preview in `wp-content/themes/betheme/visual-builder/visual-builder.php`
- were calling `sc_shop_products()` directly without setting that global first

Fix:

- patch both parent theme files so they set and restore `$mfn_shop_products_attr` around the `sc_shop_products()` call

Useful restore targets:

- `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
- `wp-content/themes/betheme/visual-builder/visual-builder.php`
- `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php`

## Short Version For Future AI

If `Product Tags` is not showing in Betheme admin, do not stop after checking `class-mfn-builder-fields.php`.

Also check the generated BeBuilder file:

- `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js`

That file is often the real reason the field is missing in admin.

## Prompt For Future AI

Yes, AI should be able to re-add this after a normal Betheme update.

Use a prompt like this:

`Read wp-content/themes/betheme-child/UPDATE-README.md and restore the custom Betheme shop_products fields after the theme update. Re-add show_category, show_product_sku, and show_product_tags to the parent builder fields and make sure the current bebuilder-<theme-version>.js generated form file also contains them. Then verify the child theme helper still supports the frontend output.`

Expected restore targets:

1. `wp-content/themes/betheme/functions/builder/class-mfn-builder-fields.php`
2. `wp-content/themes/betheme/visual-builder/assets/js/forms/bebuilder-<theme-version>.js`
3. `wp-content/themes/betheme/functions/builder/class-mfn-builder-items.php`
4. `wp-content/themes/betheme/visual-builder/visual-builder.php`
5. `wp-content/themes/betheme-child/includes/class-mfn-child-woo-helper.php`

Important note for future AI:

- Do not assume the JS filename is still `bebuilder-28.4.js`
- Find the current `bebuilder-<theme-version>.js` file in `wp-content/themes/betheme/visual-builder/assets/js/forms/`
- Confirm all three fields exist in both the PHP definition and the generated JS file
- `show_category`, `show_product_sku`, and `show_product_tags` are all custom for this project
- If any one of the three is missing in JS, re-add the exact snippets from this README in the correct order before `title_tag`
- If `show_product_tags` exists in admin but does not render when set to `Show`, verify the `shop_products` render bridge in `class-mfn-builder-items.php` and `visual-builder.php` is still present
