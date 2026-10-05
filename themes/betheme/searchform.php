<?php
/**
 * The main template file.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

$icon = isset( $args['icon'] ) ? '<span class="icon_search"><i class="'.$args['icon'].'"></i></span>' : '<svg class="icon_search" width="26" viewBox="0 0 26 26" aria-label="'. __('search icon', 'betheme') .'"><defs><style>.path{fill:none;stroke:#000;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><circle class="path" cx="11.35" cy="11.35" r="6"></circle><line class="path" x1="15.59" y1="15.59" x2="20.65" y2="20.65"></line></svg>';
$id = 's-'. Mfn_Builder_Helper::unique_ID();

// translate

$translate['search-placeholder'] = mfn_opts_get('translate') ? mfn_opts_get('translate-search-placeholder','Enter your search') : __('Enter your search','betheme');
if( isset($args['placeholder']) ){
	$translate['search-placeholder'] = $args['placeholder'];
}

?>

<form method="get" class="form-searchform" action="<?php echo esc_url(home_url('/')); ?>">

	<?php if( mfn_opts_get('header-search') == 'shop' ): ?>
		<input type="hidden" name="post_type" value="product" />
	<?php endif; ?>

  <?php echo $icon; ?>

	<?php if( mfn_opts_get('keyboard-support') ): ?>
		<label for="<?php echo $id; ?>" class="screen-reader-text"><?php echo esc_html($translate['search-placeholder']); ?></label>
	<?php endif;?>

	<input type="text" class="field" name="s" <?php if( mfn_opts_get('keyboard-support') ) echo 'id="'. $id .'"'; ?> autocomplete="off" placeholder="<?php echo esc_html($translate['search-placeholder']); ?>" aria-label="<?php echo esc_html($translate['search-placeholder']); ?>" />
	<input type="submit" class="display-none" value="" aria-label="<?php esc_html_e('Search', 'betheme'); ?>"/>

	<span class="mfn-close-icon icon_close" tabindex="0" role="link" aria-label="<?php esc_html_e('Close', 'betheme'); ?>"><span class="icon">✕</span></span>

</form>
