# WooCommerce Group Buy Shortcodes

Quick reference for the custom group-buy shortcodes in this plugin.

## Deal Info

Shows the full group-buy info box for one product.

```text
[gb_deal_info id="123"]
```

If `id` is omitted, it uses the current product/post ID.

## Deal List

Shows a grid/list of active group-buy products.

```text
[gb_deal_list limit="10" columns="3" theme="theme-1"]
```

Options:

- `limit`: number of deals to query.
- `columns`: grid columns, for example `2`, `3`, or `4`.
- `cat`: optional product category slug.
- `theme`: `theme-1` or `theme-2`.

Examples:

```text
[gb_deal_list limit="6" columns="3"]
[gb_deal_list cat="airsoft" limit="4" columns="2" theme="theme-2"]
```

## Revolution Banner Deal

Shows the first active/non-expired group-buy product, preferring one with a live countdown and falling back to an active product without a countdown when no countdown deal is available. Useful for Revolution Slider layers because you can request only the exact part you need.

```text
[gb_deal_banner show="thumbnail,title,countdown,price,discount_price"]
```

Options:

- `show`: comma-separated fields to display.
- `fields`: alias of `show`; use either one.
- `id`: optional product ID. If provided, this exact product is shown even when it has no live countdown; countdown and discount fields are skipped, and `price` shows the original price.
- `cat`: optional product category slug used when finding the first active deal.
- `image_size`: WordPress image size for `thumbnail`; default is `full`.
- `link`: use `yes` to link thumbnail/title to the product page.
- `class`: optional extra CSS class on the wrapper.

Available fields:

- `thumbnail` or `image`
- `title`
- `countdown` or `timer`
- `price`
- `regular_price`
- `discount_price` or `sale_price`
- `discount` or `discount_badge`

Examples:

```text
[gb_deal_banner show="title"]
[gb_deal_banner show="thumbnail" image_size="full"]
[gb_deal_banner show="countdown"]
[gb_deal_banner show="regular_price,discount_price,discount"]
[gb_deal_banner cat="airsoft" show="title,countdown,discount_price"]
[gb_deal_banner id="123" show="thumbnail,title,price" link="yes"]
```
