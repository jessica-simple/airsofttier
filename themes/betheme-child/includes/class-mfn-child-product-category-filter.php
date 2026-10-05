<?php
/**
 * Controls which product categories appear in BeTheme's advanced filters.
 */
class MFN_Child_Product_Category_Filter {
    const META_KEY = '_mfn_child_show_in_shop_filter';
    const NONCE_NAME = '_mfn_child_shop_filter_nonce';
    const NONCE_ACTION = 'mfn_child_save_shop_filter';

    public static function init() {
        add_action('product_cat_add_form_fields', array(__CLASS__, 'render_add_field'));
        add_action('product_cat_edit_form_fields', array(__CLASS__, 'render_edit_field'));
        add_action('created_product_cat', array(__CLASS__, 'save_field'), 10, 3);
        add_action('edited_product_cat', array(__CLASS__, 'save_field'), 10, 3);
        add_filter('get_terms', array(__CLASS__, 'filter_advanced_filter_terms'), 10, 4);
    }

    public static function render_add_field() {
        self::render_field(true);
    }

    public static function render_edit_field($term) {
        self::render_field(false, $term);
    }

    private static function render_field($is_add_form, $term = null) {
        $enabled = $is_add_form || get_term_meta($term->term_id, self::META_KEY, true) !== 'no';

        if ($is_add_form) {
            ?>
            <div class="form-field">
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>
                <label for="<?php echo esc_attr(self::META_KEY); ?>">
                    <?php esc_html_e('Show in Shop Filter', 'betheme-child'); ?>
                </label>
                <input
                    type="hidden"
                    name="<?php echo esc_attr(self::META_KEY); ?>"
                    value="no"
                >
                <input
                    type="checkbox"
                    id="<?php echo esc_attr(self::META_KEY); ?>"
                    name="<?php echo esc_attr(self::META_KEY); ?>"
                    value="yes"
                    <?php checked($enabled); ?>
                >
                <p class="description">
                    <?php esc_html_e('Show this category in the Shop page category filter.', 'betheme-child'); ?>
                </p>
            </div>
            <?php

            return;
        }

        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="<?php echo esc_attr(self::META_KEY); ?>">
                    <?php esc_html_e('Show in Shop Filter', 'betheme-child'); ?>
                </label>
            </th>
            <td>
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>
                <input
                    type="hidden"
                    name="<?php echo esc_attr(self::META_KEY); ?>"
                    value="no"
                >
                <input
                    type="checkbox"
                    id="<?php echo esc_attr(self::META_KEY); ?>"
                    name="<?php echo esc_attr(self::META_KEY); ?>"
                    value="yes"
                    <?php checked($enabled); ?>
                >
                <label for="<?php echo esc_attr(self::META_KEY); ?>">
                    <?php esc_html_e('Show this category in the Shop page category filter.', 'betheme-child'); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    public static function save_field($term_id, $term_taxonomy_id, $args) {
        if (! current_user_can('edit_term', $term_id)) {
            return;
        }

        if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]));

        if (! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        $value = isset($_POST[self::META_KEY]) && is_string($_POST[self::META_KEY])
            ? sanitize_key(wp_unslash($_POST[self::META_KEY]))
            : 'no';

        update_term_meta($term_id, self::META_KEY, $value === 'yes' ? 'yes' : 'no');
    }

    public static function filter_advanced_filter_terms($terms, $taxonomies, $args, $term_query) {
        if (
            is_wp_error($terms) ||
            ! is_array($terms) ||
            ! self::is_advanced_filters_product_category_query($taxonomies, $args)
        ) {
            return $terms;
        }

        return array_values(array_filter($terms, function ($term) {
            if (! isset($term->term_id)) {
                return true;
            }

            return get_term_meta($term->term_id, self::META_KEY, true) !== 'no';
        }));
    }

    private static function is_advanced_filters_product_category_query($taxonomies, $args) {
        $query_taxonomies = isset($args['taxonomy'])
            ? (array) $args['taxonomy']
            : (array) $taxonomies;

        if (! in_array('product_cat', $query_taxonomies, true)) {
            return false;
        }

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20) as $frame) {
            if (isset($frame['function']) && $frame['function'] === 'sc_filters') {
                return true;
            }
        }

        return false;
    }
}

MFN_Child_Product_Category_Filter::init();
