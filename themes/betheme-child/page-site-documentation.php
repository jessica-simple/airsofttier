<?php
/**
 * Template Name: Site Documentation
 * Description: Public guide to the custom Airsoft store features.
 */

get_header();
?>
<div id="Content" role="main" class="site-documentation-page">
	<div class="content_wrapper clearfix">
		<main class="sections_group">
			<article class="entry-content" itemprop="mainContentOfPage">
				<section class="site-doc-hero">
					<div class="site-doc-container">
						<p class="site-doc-eyebrow">AIRSOFT STORE GUIDE</p>
						<h1>Site Documentation</h1>
						<p class="site-doc-intro">A guide to shopping tools, customer account features, group-buy deals, notifications, and the reseller marketplace.</p>
						<a class="site-doc-cta" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Browse the shop <span aria-hidden="true">→</span></a>
					</div>
				</section>

				<div class="site-doc-container site-doc-layout">
					<nav class="site-doc-nav" aria-label="Documentation sections">
						<strong>On this page</strong>
						<a href="#shopping">Shopping</a>
						<a href="#group-buy">Group-buy deals</a>
						<a href="#account">Customer account</a>
						<a href="#notifications">Notifications</a>
						<a href="#resellers">Reseller marketplace</a>
						<a href="#shortcodes">Shortcodes</a>
					</nav>

					<div class="site-doc-content">
						<section id="shopping" class="site-doc-section">
							<p class="site-doc-kicker">FIND AND BUY</p>
							<h2>Shopping features</h2>
							<div class="site-doc-cards">
								<article class="site-doc-card"><h3>Product discovery</h3><p>Browse dedicated <a href="<?php echo esc_url(home_url('/sale/')); ?>">On Sale</a>, <a href="<?php echo esc_url(home_url('/new-arrivals/')); ?>">New Arrivals</a>, and <a href="<?php echo esc_url(home_url('/gb-deals/')); ?>">GB Deals</a> collections. Shop sorting, filters, and pagination are available on these archives.</p></article>
								<article class="site-doc-card"><h3>Filters and brands</h3><p>Expand category filters to browse subcategories. Store staff can choose which categories appear in the Shop filter. Brand grids and lists help customers browse by brand, and product and brand search can be placed in the header or page content.</p></article>
								<article class="site-doc-card"><h3>Product details</h3><p>Group-buy products can show tiered deal information and countdown timers on product pages and product cards. Product cards can also be configured to show or hide category, SKU, and product tags.</p></article>
								<article class="site-doc-card"><h3>Cart and checkout</h3><p>Unavailable products are grouped under Invalid items in the cart and can be removed. Cart refinements include a compact quantity control, store reassurance details, a cart item count, and BeTheme's side cart.</p></article>
							</div>
						</section>

						<section id="group-buy" class="site-doc-section">
							<p class="site-doc-kicker">LIMITED-TIME OFFERS</p>
							<h2>Group-buy deals</h2>
							<p>Products marked as group-buy deals can display deal pricing, tier information, and a countdown when an end date is set. Expired deals do not appear in the GB Deals collection. Products without an end date can still appear when the deal is active. Existing WooCommerce stock and catalog visibility rules apply.</p>
							<p>Deal details can be added to pages and promotional banners using the shortcodes below. Countdown timers also appear in product lists, including cards loaded by filters or pagination.</p>
							<a class="site-doc-text-link" href="<?php echo esc_url(home_url('/gb-deals/')); ?>">Explore GB Deals <span aria-hidden="true">→</span></a>
						</section>

						<section id="account" class="site-doc-section">
							<p class="site-doc-kicker">YOUR STORE ACCOUNT</p>
							<h2>Customer account</h2>
							<ul class="site-doc-list">
								<li><strong>Dashboard:</strong> see account details, recent orders, address summaries, and shortcuts to account tools.</li>
								<li><strong>Wishlist:</strong> save products from product cards or product pages, then review, remove, or add saved items to your cart.</li>
								<li><strong>Orders and addresses:</strong> use WooCommerce account pages to review purchases and manage billing, shipping, and account details.</li>
								<li><strong>Coupons:</strong> review coupons available to your account.</li>
								<li><strong>Notifications:</strong> open the account inbox for order, payment, shipment, refund, and cart availability updates.</li>
							</ul>
							<a class="site-doc-text-link" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Go to My Account <span aria-hidden="true">→</span></a>
						</section>

						<section id="notifications" class="site-doc-section">
							<p class="site-doc-kicker">STAY UP TO DATE</p>
							<h2>Notifications and cart follow-ups</h2>
							<p>Signed-in customers can see unread notifications in My Account. Depending on the event, an alert links to the relevant order or cart. Opening the notification dropdown refreshes it without marking items read; customers can mark one item or all items as read.</p>
							<p>The optional header bell can be added with <code>[airsoft_notifications]</code>. If the header bell is not used, the site can show a floating bell when there are unread alerts. Store staff can review inactive carts under <strong>Product Manager → Cart Follow-ups</strong>.</p>
							<p class="site-doc-note">Notifications are collected from deployment onward. They do not import earlier order activity. WooCommerce order emails continue to work separately.</p>
						</section>

						<section id="resellers" class="site-doc-section">
							<p class="site-doc-kicker">SELL ON THE MARKETPLACE</p>
							<h2>Reseller marketplace</h2>
							<p>Resellers use a separate account from regular store members. Applicants register and wait for administrator review. Approved resellers can submit products, manage their listings, fulfill their own order items, add tracking, view purchases, and review earnings in the Reseller Portal.</p>
							<div class="site-doc-steps">
								<div><span>01</span><p><strong>Apply</strong><br>Register with a separate email and sign in to view application status.</p></div>
								<div><span>02</span><p><strong>List products</strong><br>After approval, submit listings for administrator review.</p></div>
								<div><span>03</span><p><strong>Fulfill orders</strong><br>Update your order items and share shipment tracking with customers.</p></div>
								<div><span>04</span><p><strong>Review earnings</strong><br>See eligible earnings and request wallet withdrawals when wallet payouts are enabled.</p></div>
							</div>
							<p>Payouts are controlled by administrators. Wallet withdrawals are paid externally by staff; bank payouts are also arranged outside the site and recorded in the marketplace. Returns and refunds are processed through WooCommerce.</p>
							<div class="site-doc-actions">
								<a class="site-doc-cta" href="<?php echo esc_url(home_url('/reseller-registration/')); ?>">Apply to become a reseller</a>
								<a class="site-doc-secondary" href="<?php echo esc_url(home_url('/reseller-login/')); ?>">Reseller login</a>
							</div>
						</section>

						<section id="shortcodes" class="site-doc-section site-doc-section-last">
							<p class="site-doc-kicker">FOR PAGE EDITORS</p>
							<h2>Useful shortcodes</h2>
							<p>Administrators can add these shortcodes in a WordPress Shortcode block or a BeTheme element that processes shortcodes.</p>
							<div class="site-doc-shortcodes">
								<div><code>[airsoft_notifications]</code><span>Customer notification bell</span></div>
								<div><code>[gb_deal_info id="123"]</code><span>Group-buy details for one product</span></div>
								<div><code>[gb_deal_list limit="6" columns="3"]</code><span>Active group-buy product grid</span></div>
								<div><code>[gb_deal_banner show="title,countdown,discount_price"]</code><span>Group-buy promotional banner</span></div>
								<div><code>[woocommerce_search]</code><span>Product search form</span></div>
								<div><code>[brands_grid]</code><span>Brand grid</span></div>
							</div>
							<p class="site-doc-note">For technical setup, configuration, and full shortcode options, see the Product Manager documentation maintained with the site code.</p>
						</section>
					</div>
				</div>
			</article>
		</main>
	</div>
</div>
<?php get_footer(); ?>
