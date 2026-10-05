const deprecated_fields = {
    item: ['style:.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-header-menu > li.current-menu-item.mfn-menu-li > a.mfn-menu-link:color','style:.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-header-menu > li.current-menu-item.mfn-menu-li > a.mfn-menu-link:background-color'],
    wrap: [],
    section: []
};

const sizes = [
    {index: 1, key: '1/6', desktop: 'one-sixth', laptop: 'laptop-one-sixth', tablet: 'tablet-one-sixth', mobile: 'mobile-one-sixth', percent: '16.666%'},
    {index: 2, key: '1/5', desktop: 'one-fifth', laptop: 'laptop-one-fifth', tablet: 'tablet-one-fifth', mobile: 'mobile-one-fifth', percent: '20%'},
    {index: 3, key: '1/4', desktop: 'one-fourth', laptop: 'laptop-one-fourth', tablet: 'tablet-one-fourth', mobile: 'mobile-one-fourth', percent: '25%'},
    {index: 4, key: '1/3', desktop: 'one-third', laptop: 'laptop-one-third', tablet: 'tablet-one-third', mobile: 'mobile-one-third', percent: '33.333%'},
    {index: 5, key: '2/5', desktop: 'two-fifth', laptop: 'laptop-two-fifth', tablet: 'tablet-two-fifth', mobile: 'mobile-two-fifth', percent: '40%'},
    {index: 6, key: '1/2', desktop: 'one-second', laptop: 'laptop-one-second', tablet: 'tablet-one-second', mobile: 'mobile-one-second', percent: '50%'},
    {index: 7, key: '3/5', desktop: 'three-fifth', laptop: 'laptop-three-fifth', tablet: 'tablet-three-fifth', mobile: 'mobile-three-fifth', percent: '60%'},
    {index: 8, key: '2/3', desktop: 'two-third', laptop: 'laptop-two-third', tablet: 'tablet-two-third', mobile: 'mobile-two-third', percent: '66%'},
    {index: 9, key: '3/4', desktop: 'three-fourth', laptop: 'laptop-three-fourth', tablet: 'tablet-three-fourth', mobile: 'mobile-three-fourth', percent: '75%'},
    {index: 10, key: '4/5', desktop: 'four-fifth', laptop: 'laptop-four-fifth', tablet: 'tablet-four-fifth', mobile: 'mobile-four-fifth', percent: '80%'},
    {index: 11, key: '5/6', desktop: 'five-sixth', laptop: 'laptop-five-sixth', tablet: 'tablet-five-sixth', mobile: 'mobile-five-sixth', percent: '83.333%'},
    {index: 12, key: '1/1', desktop: 'one', laptop: 'laptop-one', tablet: 'tablet-one', mobile: 'mobile-one', percent: '100%'}
];


const aliases = {
    'heading_title': [
        { key: 'title', val: '{title}' }
    ],
    'heading_price': [
        { key: 'title', val: '{price}' },
        { key: 'header_tag', val: 'h6' }
    ],
    'plain_text_excerpt': [
        { key: 'content', val: '{excerpt}' }
    ],
    'image_featured_image': [
        { key: 'src', val: '{featured_image}' }
    ],
    'button_read_more': [
        { key: 'link', val: '{permalink}' },
        { key: 'title', val: 'Read more' }
    ],
    'shop_products_related': [
        { key: 'products', val: '4' },
        { key: 'display', val: 'related' },
        { key: 'title_tag', val: 'h3' },
        { key: 'css_columns_grid', val: { val: {'desktop': 'repeat(4, 1fr)'}, selector: ".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-grid-layout", style: "grid-template-columns"} },
    ],
    'shop_products_upsells': [
        { key: 'products', val: '4' },
        { key: 'title_tag', val: 'h3' },
        { key: 'display', val: 'related' },
        { key: 'css_columns_grid', val: { val: {'desktop': 'repeat(4, 1fr)'}, selector: ".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-grid-layout", style: "grid-template-columns"} },
    ],
    'shop_cat_desc': [
        { key: 'content', val: '{content}' }
    ],
    'shop_cat_top_desc': [
        { key: 'content', val: '{termmeta:mfn_product_cat_top_content}' }
    ],
    'shop_cat_bottom_desc': [
        { key: 'content', val: '{termmeta:mfn_product_cat_bottom_content}' }
    ],
    'post_excerpt': [
        { key: 'content', val: '{excerpt}' }
    ],
    'archive_read_more': [
        { key: 'link', val: '{permalink}' },
        { key: 'title', val: 'Read more' }
    ],
    'archive_blog_categories': [
        { key: 'category', val: 'category' }
    ],
    'archive_portfolio_categories': [
        { key: 'category', val: 'portfolio-types' }
    ],
    'archive_heading': [
        { key: 'title', val: '{title}' }
    ],
    'archive_content': [
        { key: 'content', val: '{content}' }
    ],
    'archive_image': [
        { key: 'src', val: '{featured_image}' }
    ],
    'post_heading': [
        { key: 'title', val: '{title}' }
    ],
    'post_image': [
        { key: 'src', val: '{featured_image}' }
    ],
    'post_love': [
        { key: 'title', val: '{postmeta:mfn-post-love}' },
        { key: 'icon_position', val: {desktop: 'left'} },
        { key: 'link', val: '#' },
        { key: 'icon', val: 'icon-heart-empty-fa' },
        { key: 'content', val: '' },
        { key: 'css_icon_box_icon_wrapper_width', val: { val: {'desktop': '30px'}, selector: ".mcb-section .mcb-wrap .mcb-item-mfnuidelement .icon-wrapper", style: "width"} },
        { key: 'css_icon_box_icon_wrapper_i_font_size', val: { val: {'desktop': '25px'}, selector: ".mcb-section .mcb-wrap .mcb-item-mfnuidelement .icon-wrapper i", style: "font-size"} },
        { key: 'title_tag', val: 'p' }
    ],
    'post_author': [
        { key: 'title', val: '{author}' },
        { key: 'icon_position', val: {desktop: 'left'} },
        { key: 'link', val: '{permalink:author}' },
        { key: 'content', val: '' },
        { key: 'image', val: '{featured_image:author}' },
        { key: 'css_icon_box_icon_wrapper_width',  val: { val: {'desktop': '48px'}, selector: ".mcb-section .mcb-wrap .mcb-item-mfnuidelement .icon-wrapper", style: "width"} },
        { key: 'title_tag', val: 'p' }
    ],
    'post_date': [
        { key: 'title', val: '{date}' },
        { key: 'header_tag', val: 'p' }
    ],
    'post_blog_related': [
        { key: 'related', val: '1' }
    ],
    'post_portfolio_related': [
        { key: 'related', val: '1' }
    ],
    'post_blog_categories': [
        { key: 'reference', val: 'post' }
    ],
    'post_portfolio_categories': [
        { key: 'reference', val: 'post' },
        { key: 'category', val: 'portfolio-types' }
    ],
    'post_blog_tags': [
        { key: 'reference', val: 'post' },
        { key: 'category', val: 'post_tag' },
    ],
    'readmore': [
        { key: 'button_function', val: 'mfn-read-more' }
    ]
}

var dynamic_data = {
    'labels' : {
        'category': 'Post category',
        'post_tag': 'Post tag',
        'portfolio_types': 'Portfolio category',
        'offer_types': 'Offer category',
        'testimonial_types': 'Testimonial category',
        'product_cat': 'Product category',
    },
    'dynamic' : {
        'posts': {
            'title': [{ key: 'title', label: 'Title' }],
            'permalink': [{key: 'permalink', label: 'Link'}],
            'featured_image': [{key: 'featured_image', label: 'Image'}],
            'content': [{key: 'title', label: 'Title'}, /*{key: 'author', label: 'Author'},*/ {key: 'date', label: 'Date'}, {key: 'date:modified', label: 'Modified Date'}, {key: 'permalink', label: 'Link'}, {key: 'featured_image', label: 'Image'}, {key: 'featured_image:tag', label: 'Image tag'}],
            'heading': {
                'title': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, /*{key: 'author', label: 'Author'},*/ {key: 'date', label: 'Date'}, {key: 'date:modified', label: 'Modified Date'}]
            },
            'fancy_heading': {
                'title': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, /*{key: 'author', label: 'Author'},*/ {key: 'date', label: 'Date'}, {key: 'date:modified', label: 'Modified Date'}]
            },
            'icon_box_2': {
                'title': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, /*{key: 'author', label: 'Author'},*/ {key: 'date', label: 'Date'}, {key: 'date:modified', label: 'Modified Date'}]
            },
            'product': {
                'title': [{key: 'category', label: 'Main category'}, {key: 'categories', label: 'Categories'}, { key: 'price', label: 'Price' }, { key: 'title:add_to_cart', label: 'Add to cart' }, { key: 'title:attributes', label: 'Title with attributes' }],
                'permalink': [{ key: 'permalink:add_to_cart', label: 'Add to cart' }],
                'featured_image': [{ key: 'featured_image:badge', label: 'Image with Badge' }, { key: 'featured_image:product_gallery', label: 'Gallery slider' }, { key: 'featured_image:product_gallery_badge', label: 'Gallery slider with Badge' }],
                'content': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}, { key: 'price', label: 'Price' }]
            },
            'page': {
                'content': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}],
                'featured_image': [{ key: 'postmeta:mfn-post-subheader-image', label: 'Meta subheader image' }],
            },
            'post': {
                'content': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'title': [{key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}]
            },
            'testimonial': {
                'title': [{ key: 'postmeta:mfn-post-link', label: 'Meta Link' }, { key: 'postmeta:mfn-post-author', label: 'Meta Author' }, { key: 'postmeta:mfn-post-company', label: 'Meta Company' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'permalink': [{ key: 'postmeta:mfn-post-link', label: 'Meta Link' }],
                'content': [{key: 'content', label: 'Content'}, { key: 'postmeta:mfn-post-link', label: 'Meta Link' }, { key: 'postmeta:mfn-post-author', label: 'Meta Author' }, { key: 'postmeta:mfn-post-company', label: 'Meta Company' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}]
            },
            'slide': {
                'content': [{ key: 'postmeta:mfn-post-desc', label: 'Meta Description' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'permalink': [{ key: 'postmeta:mfn-post-link', label: 'Meta Link' }],
                'title': [{key: 'categories', label: 'Categories'}, { key: 'postmeta:mfn-post-desc', label: 'Meta Desc' }, {key: 'category', label: 'Main category'}],
                'featured_image': [{ key: 'postmeta:mfn-post-mp4', label: 'Meta Video' }],
            },
            'client': {
                'permalink': [{ key: 'postmeta:mfn-post-link', label: 'Meta link' }],
            },
            'offer': {
                'title': [{ key: 'postmeta:mfn-post-link_title', label: 'Meta button text' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'permalink': [{ key: 'postmeta:mfn-post-link', label: 'Meta button link' }],
                'featured_image': [{ key: 'postmeta:mfn-post-thumbnail', label: 'Meta thumbnail' }],
                'content': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}]
            },
            'portfolio': {
                'title': [{ key: 'postmeta:mfn-post-client', label: 'Meta client' }, { key: 'postmeta:mfn-post-task', label: 'Meta task' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'content': [{key: 'content', label: 'Content'}, {key: 'excerpt', label: 'Excerpt'}, { key: 'postmeta:mfn-post-client', label: 'Meta client' }, { key: 'postmeta:mfn-post-task', label: 'Meta task' }, {key: 'categories', label: 'Categories'}, {key: 'category', label: 'Main category'}],
                'permalink': [{ key: 'postmeta:mfn-post-link', label: 'Meta website' }]
            }
        },
        'terms': {
            'title': [{ key: 'title', label: 'Title' }],
            'permalink': [{key: 'permalink', label: 'Link'}],
            'featured_image': [{key: 'featured_image', label: 'Image'}],
            'content': [{key: 'content', label: 'Content'}],
            'category': {
                'content': [{ key: 'termmeta:mfn_product_cat_top_content', label: 'Top content' }, { key: 'termmeta:mfn_product_cat_bottom_content', label: 'Bottom content' }],
            }
        }
    },
    'global' : {
        'title': [{ key: 'title:site', label: 'Site title' }],
        'permalink': [{ key: 'permalink:site', label: 'Site link' }],
        'featured_image': [{ key: 'featured_image:site', label: 'Site Logo' }],
        'content': [{ key: 'title:site', label: 'Site title' }],
    },
    'user' : {
        'title': [{ key: 'user', label: 'Name' }, { key: 'user:first_name', label: 'User first name' }, { key: 'user:last_name', label: 'Last name' }],
        'permalink': [{ key: 'permalink:user', label: 'User archive' }],
        'featured_image': [{ key: 'featured_image:user', label: 'User Avatar' }],
        'content': [{ key: 'user', label: 'Name' }, { key: 'user:first_name', label: 'User first name' }, { key: 'user:last_name', label: 'Last name' }],
    },
    'author' : {
        'title': [{ key: 'author', label: 'Display name' }, { key: 'author:first_name', label: 'Author first name' }, { key: 'author:last_name', label: 'Last name' }],
        'permalink': [{ key: 'permalink:author', label: 'Author archive' }],
        'featured_image': [{ key: 'featured_image:author', label: 'Author Avatar' }],
        'content': [{ key: 'author', label: 'Name' }, { key: 'author:first_name', label: 'Author first name' }, { key: 'author:last_name', label: 'Last name' }, { key: 'author:description', label: 'Bio' }],
    }
};

var presets_keys = {
    'icon_box_2': ['title_tag', 'icon_position', 'icon_position_tablet', 'icon_position_laptop', 'icon_position_mobile', 'icon_align', 'icon_align_tablet', 'icon_align_laptop', 'icon_align_mobile', 'hover'],
    'section': ['width_switcher', 'height_switcher'],
    'wrap': ['width_switcher', 'height_switcher'],
};

var units = ['px', '%', 'em', 'rem', 'vw', 'vh'];

var items_size = {
    'wrap': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],

    'accordion': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'article_box': ['1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'before_after': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'blockquote': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'blog': ['1/1'],
    'blog_news': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'blog_slider': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'blog_teaser': ['1/1'],
    'button': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'call_to_action': ['1/1'],
    'chart': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'clients': ['1/1'],
    'clients_slider': ['1/1'],
    'code': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'column': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'contact_box': ['1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'content': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'countdown': ['1/1'],
    'counter': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'divider': ['1/1'],
    'fancy_divider': ['1/1'],
    'fancy_heading': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'feature_box': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'feature_list': ['1/1'],
    'faq': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'flat_box': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'helper': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'hover_box': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'hover_color': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'how_it_works': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'icon_box': ['1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'image': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'image_gallery': ['1/1'],
    'info_box': ['1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'list': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'map_basic': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'map': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'offer': ['1/1'],
    'offer_thumb': ['1/1'],
    'opening_hours': ['1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'our_team': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'our_team_list': ['1/1'],
    'photo_box': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'placeholder': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'portfolio': ['1/1'],
    'portfolio_grid': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'portfolio_photo': ['1/1'],
    'portfolio_slider': ['1/1'],
    'pricing_item': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'progress_bars': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'promo_box': ['1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'quick_fact': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'shop': ['1/1'],
    'shop_slider': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'sidebar_widget': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'slider': ['1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'slider_plugin': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'sliding_box': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'story_box': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'tabs': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'testimonials': ['1/1'],
    'testimonials_list': ['1/1'],
    'trailer_box': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'timeline': ['1/1'],
    'video': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'visual': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'zoom_box': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'shop_categories': ['1/1'],
    'shop_products': ['1/1'],
    'shop_title': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_title': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_images': ['1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_price': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_cart_button': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_reviews': ['1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_rating': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_stock': ['1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_meta': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_short_description': ['1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_content': ['1/1'],
    'product_additional_information': ['1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'],
    'product_related': ['1/1'],
    'product_upsells': ['1/1'],
};

const additional_css = {
    'css_menu_li-submenulia_justify_content': { // header_burger justify content & text align
        new_id: 'css_menu_li-submenulia_text_align',
        selector: '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-header-tmpl-menu-sidebar .mfn-header-menu li .mfn-submenu li a',
        style: 'text-align',
        rewrites: {
            'flex-start': 'left',
            'flex-end': 'right',
            'center': 'center',
        }
    },

    'css_product_text_align': {
        new_id: 'css_product_align_items',
        selector: '.mcb-section .mcb-wrap .mcb-item-mfnuidelement ul.products:not(.mfn-list-layout,.mfn-list_2-layout) li.product',
        style: 'align-items',
        rewrites: {
            'left': 'flex-start',
            'right': 'flex-end',
            'center': 'center',
        }
    },
    
    'css_banner-box_text_align': { // banner box text align & align items
        new_id: 'css_banner_box_align_items',
        selector: '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-banner-box .banner-wrapper',
        style: 'align-items',
        rewrites: {
            'left': 'flex-start',
            'right': 'flex-end',
            'center': 'center',
        }
    },

    'css_grid_item_vertical_align' : {
        new_id: 'css_grid_item_vertical_align_text_align',
        selector: '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-grid-item .mfn-grid-item-desc',
        style: 'text-align',
        rewrites: {
            'flex-start': 'left',
            'flex-end': 'right',
            'center': 'center',
        }
    },

    'style:.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-banner-box .banner-wrapper:text-align': { // banner box text align & align items
        new_id: 'css_banner_box_align_items',
        selector: '.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-banner-box .banner-wrapper',
        style: 'align-items',
        rewrites: {
            'left': 'flex-start',
            'right': 'flex-end',
            'center': 'center',
        }
    },

}

const color_palette = [
    _.has(mfn.themeoptions, 'color-palette-1') && mfn.themeoptions['color-palette-1'].length ? mfn.themeoptions['color-palette-1'] : '#f44336',
    _.has(mfn.themeoptions, 'color-palette-2') && mfn.themeoptions['color-palette-2'].length ? mfn.themeoptions['color-palette-2'] : '#e91e63',
    _.has(mfn.themeoptions, 'color-palette-3') && mfn.themeoptions['color-palette-3'].length ? mfn.themeoptions['color-palette-3'] : '#9c27b0',
    _.has(mfn.themeoptions, 'color-palette-4') && mfn.themeoptions['color-palette-4'].length ? mfn.themeoptions['color-palette-4'] : '#673ab7',
    _.has(mfn.themeoptions, 'color-palette-5') && mfn.themeoptions['color-palette-5'].length ? mfn.themeoptions['color-palette-5'] : '#3f51b5',
    _.has(mfn.themeoptions, 'color-palette-6') && mfn.themeoptions['color-palette-6'].length ? mfn.themeoptions['color-palette-6'] : '#2196f3',
    _.has(mfn.themeoptions, 'color-palette-7') && mfn.themeoptions['color-palette-7'].length ? mfn.themeoptions['color-palette-7'] : '#03a9f4',
    _.has(mfn.themeoptions, 'color-palette-8') && mfn.themeoptions['color-palette-8'].length ? mfn.themeoptions['color-palette-8'] : '#00bcd4',
    _.has(mfn.themeoptions, 'color-palette-9') && mfn.themeoptions['color-palette-9'].length ? mfn.themeoptions['color-palette-9'] : '#009688',
    _.has(mfn.themeoptions, 'color-palette-10') && mfn.themeoptions['color-palette-10'].length ? mfn.themeoptions['color-palette-10'] : '#4caf50',
    _.has(mfn.themeoptions, 'color-palette-11') && mfn.themeoptions['color-palette-11'].length ? mfn.themeoptions['color-palette-11'] : '#8bc34a',
    _.has(mfn.themeoptions, 'color-palette-12') && mfn.themeoptions['color-palette-12'].length ? mfn.themeoptions['color-palette-12'] : '#cddc39',
    _.has(mfn.themeoptions, 'color-palette-13') && mfn.themeoptions['color-palette-13'].length ? mfn.themeoptions['color-palette-13'] : '#ffeb3b',
    _.has(mfn.themeoptions, 'color-palette-14') && mfn.themeoptions['color-palette-14'].length ? mfn.themeoptions['color-palette-14'] : '#ffc107'
];


const disabled_items_in_ql = [
    'shop',
    'blog',
    'blog_news',
    'blog_slider',
    'blog_teaser',
    'clients',
    'clients_slider',
    'offer',
    'offer_thumb',
    'portfolio',
    'portfolio_grid',
    'portfolio_photo',
    'portfolio_slider',
    'shop_categories',
    'shop_slider',
    'slider',
    'table_of_contents',
    'header_currency_switcher'
]

