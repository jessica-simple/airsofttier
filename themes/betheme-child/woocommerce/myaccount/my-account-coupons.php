<?php
/**
 * My Account coupons endpoint.
 *
 * @package WooCommerce\Templates
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wc_print_notice(esc_html__('You must be logged in to view your coupons.', 'betheme-child'), 'error');
    return;
}

$active_coupons = betheme_child_get_active_coupons();
$endpoint_url = wc_get_account_endpoint_url('coupons');

wc_print_notices();
?>
<div class="coupons-page">

    <div class="coupons-header">
        <div>
            <h1><?php esc_html_e('My Coupons', 'betheme-child'); ?></h1>
            <p><?php esc_html_e('View and copy coupon codes to apply during checkout.', 'betheme-child'); ?></p>
        </div>

        <div class="coupons-count">
            <?php
            $item_count = count($active_coupons);
            echo sprintf(_n('%d Coupon', '%d Coupons', $item_count, 'betheme-child'), $item_count);
            ?>
        </div>
    </div>

    <?php if (empty($active_coupons)): ?>
        <div class="coupons-empty-container">
            <div class="empty-icon"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24"
                    height="200px" width="200px" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M21 5H3a1 1 0 0 0-1 1v4h.893c.996 0 1.92.681 2.08 1.664A2.001 2.001 0 0 1 3 14H2v4a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-4h-1a2.001 2.001 0 0 1-1.973-2.336c.16-.983 1.084-1.664 2.08-1.664H22V6a1 1 0 0 0-1-1zM11 17H9v-2h2v2zm0-4H9v-2h2v2zm0-4H9V7h2v2z">
                    </path>
                </svg></div>
            <h2><?php esc_html_e('No coupons available', 'betheme-child'); ?></h2>
            <p><?php esc_html_e('There are no active coupons available for your account at the moment.', 'betheme-child'); ?>
            </p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
                <?php esc_html_e('Continue Shopping', 'betheme-child'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="coupons-grid">
            <?php foreach ($active_coupons as $coupon): ?>
                <?php
                $coupon_id = $coupon->get_id();
                $code = $coupon->get_code();
                $type = $coupon->get_discount_type();
                $amount = $coupon->get_amount();
                $description = $coupon->get_description();
                $expiry_date = $coupon->get_date_expires();

                // Format discount value
                $discount_display = '';
                if ('percent' === $type) {
                    $discount_display = $amount . '%';
                } else {
                    $discount_display = wc_price($amount);
                }

                // Friendly discount type description
                $discount_label = '';
                if ('percent' === $type) {
                    $discount_label = esc_html__('OFF', 'betheme-child');
                } elseif ('fixed_cart' === $type) {
                    $discount_label = esc_html__('Cart Discount', 'betheme-child');
                } elseif ('fixed_product' === $type) {
                    $discount_label = esc_html__('Product Discount', 'betheme-child');
                }
                ?>
                <div class="coupon-ticket">
                    <!-- Ticket Left (Discount Amount) -->
                    <div class="ticket-left">
                        <span class="ticket-amount"><?php echo wp_kses_post($discount_display); ?></span>
                        <span class="ticket-label"><?php echo esc_html($discount_label); ?></span>
                    </div>

                    <!-- Ticket Divider -->
                    <div class="ticket-divider">
                        <span class="divider-notch top"></span>
                        <span class="divider-line"></span>
                        <span class="divider-notch bottom"></span>
                    </div>

                    <!-- Ticket Right (Details) -->
                    <div class="ticket-right">
                        <div class="ticket-info">
                            <span class="coupon-tag"><?php esc_html_e('Coupon Code', 'betheme-child'); ?></span>
                            <h3 class="coupon-code-title"><?php echo esc_html(strtoupper($code)); ?></h3>
                            <?php if (!empty($description)): ?>
                                <p class="coupon-description"><?php echo esc_html($description); ?></p>
                            <?php else: ?>
                                <p class="coupon-description">
                                    <?php esc_html_e('Apply this coupon at checkout to save.', 'betheme-child'); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="ticket-meta-row">
                            <div class="coupon-meta">
                                <?php if ($expiry_date): ?>
                                    <span class="coupon-expiry">
                                        <?php esc_html_e('Expires:', 'betheme-child'); ?><br>
                                        <?php echo esc_html(wc_format_datetime($expiry_date)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="coupon-expiry no-expiry">
                                        <?php esc_html_e('No Expiry Date', 'betheme-child'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <button class="coupon-copy-btn btn" data-code="<?php echo esc_attr($code); ?>"
                                onclick="cswCopyCoupon(this)">
                                <?php esc_html_e('Copy Code', 'betheme-child'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Custom Copy Script -->
        <script>
            function cswCopyCoupon(button) {
                var code = button.getAttribute('data-code');
                if (!code) return;

                navigator.clipboard.writeText(code).then(function () {
                    var originalText = button.innerHTML;
                    button.innerHTML = "<?php esc_html_e('Copied!', 'betheme-child'); ?>";
                    button.classList.add('copied');
                    button.disabled = true;

                    setTimeout(function () {
                        button.innerHTML = originalText;
                        button.classList.remove('copied');
                        button.disabled = false;
                    }, 2000);
                }).catch(function (err) {
                    console.error('Failed to copy text: ', err);
                });
            }
        </script>
    <?php endif; ?>

</div>