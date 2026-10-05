<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

$custom_replaced_logo = apply_filters('betheme_logo', '') ? 'style="background-image:url('. apply_filters('betheme_logo_nohtml', ''). ')"' : '';
$version = apply_filters('betheme_disable_theme_version', MFN_THEME_VERSION);

$view_page_link = get_the_permalink( $this->post_id );
$generate_preview_link = get_permalink($this->post_id).'&'.apply_filters('betheme_slug', 'mfn').'-preview=true';

if( $this->template_type && in_array($this->template_type, array('footer', 'header')) ) {
    $view_page_link = get_site_url();
    $generate_preview_link = get_site_url().'?mfn-'.$this->template_type.'-template='.$this->post_id.'&'.apply_filters('betheme_slug', 'mfn').'-preview=true';
}elseif( $this->template_type && in_array($this->template_type, array('single-product', 'shop-archive')) ){
    $view_page_link = get_the_permalink( $this->post_id ).'?mfn-template-id='.$this->post_id;
    $generate_preview_link = get_the_permalink( $this->post_id ).'?mfn-template-id='.$this->post_id.'&'.apply_filters('betheme_slug', 'mfn').'-preview=true';
}

if( wp_get_referer() && strpos( wp_get_referer(), 'login' ) === false && strpos( wp_get_referer(), 'action=mfn-live-builder' ) === false ){
    $referrer = wp_get_referer();
}else{
    $referrer = admin_url();
}


$post_status = get_post_status($this->post_id);

?>


<div class="mfn-topbar">

    <div class="topbar-nav">
        <?php if( ! WHITE_LABEL ){ ?>
            <a href="<?php echo admin_url('admin.php?page='.apply_filters('betheme_slug', 'betheme')); ?>" class="mfnb-logo" <?php echo $custom_replaced_logo; ?>>Be Builder - Powered by Muffin Group</a>
        <?php }else{ ?>
            <a href="<?php echo admin_url('admin.php?page='.apply_filters('betheme_slug', 'betheme')); ?>" class="mfnb-logo" style="background-image:unset"></a>
        <?php } ?>
        <nav id="main-menu">
            <ul>
                <li class="menu-items active"><a data-tooltip="<?php esc_html_e('Elements', 'mfn-opts'); ?>" data-position="bottom" href="#"><?php esc_html_e('Elements', 'mfn-opts'); ?> <?php echo $this->view; ?></a></li>
                <li class="menu-sections"><a data-tooltip="<?php esc_html_e('Pre-built sections', 'mfn-opts'); ?>" data-position="bottom" href="#"><?php esc_html_e('Pre-built sections', 'mfn-opts'); ?></a></li>
                <li class="menu-export"><a class="mfn-export-get" data-tooltip="<?php esc_html_e('Export / Import', 'mfn-opts'); ?> <?php echo ($this->view == 'demo' ? '(Unavailable in Demo)' : ''); ?>" data-position="bottom" href="#"><?php esc_html_e('Export / Import', 'mfn-opts'); ?></a></li>
                <?php if ( defined( 'WPSEO_FILE' ) && $this->post_type !== 'template' ) { ?>
                	<li class="menu-yoast"><a data-tooltip="<?php esc_html_e('Yoast SEO', 'mfn-opts'); ?>" class="mfn-yoast-tab" title="Yoast SEO" href="#" data-position="bottom"><?php esc_html_e('Yoast SEO', 'mfn-opts'); ?></a></li>
                <?php }elseif( ! apply_filters('betheme_disable_single', false) ){ ?>
                	<li class="menu-page"><a data-tooltip="<?php esc_html_e('Single page import', 'mfn-opts'); ?> <?php echo ($this->view == 'demo' ? '(Unavailable in Demo)' : ''); ?>" data-position="bottom" href="#"><?php esc_html_e('Single page import', 'mfn-opts'); ?></a></li>
                <?php } ?>
                <li class="menu-revisions"><a data-tooltip="<?php esc_html_e('History', 'mfn-opts'); ?> <?php echo ($this->view == 'demo' ? '(Unavailable in Demo)' : ''); ?>" data-position="bottom" href="#"><?php esc_html_e('History', 'mfn-opts'); ?></a></li>
                <li class="menu-settings"><a data-tooltip="<?php esc_html_e('Settings', 'mfn-opts'); ?> <?php echo ($this->view == 'demo' ? '(Unavailable in Demo)' : ''); ?>" class="mfn-view-options-tab" data-position="bottom" href="#"><?php esc_html_e('Settings', 'mfn-opts'); ?></a></li>
            </ul>
        </nav>
    </div>

    <div class="topbar-addons">

        <div class="mfn-option-dropdown page-options">
            <a href="#" class="mfn-option-btn btn-large mfn-option-text btn-icon-right mfn-option-blank"><span class="text"><?php echo get_the_title($this->post_id); ?></span><span class="mfn-icon mfn-icon-arrow-down"></span></a>
            <div class="dropdown-wrapper">
                <a class="mfn-dropdown-item" href="<?php echo admin_url('post.php?post='.$this->post_id.'&action=edit'); ?>"><span class="mfn-icon mfn-icon-edit"></span> <?php esc_html_e('Edit page', 'mfn-opts'); ?></a>
                <a class="mfn-dropdown-item mfn-show-another-pages" href="#"><span class="mfn-icon mfn-icon-edit-pages"></span> <?php esc_html_e('Edit another page', 'mfn-opts'); ?></a>
                <div class="mfn-dropdown-divider"></div>
                <a class="mfn-dropdown-item" href="<?php echo admin_url(); ?>"><span class="mfn-icon mfn-icon-wordpress"></span> <?php esc_html_e('Back to WordPress', 'mfn-opts'); ?></a>
            </div>
        </div>

        <ul class="options-group responsive-mode mfn-preview-toolbar">
            <li><a class="mfn-option-btn btn-medium mfn-option-blank mfn-preview-opt btn-active" data-preview="desktop" title="Desktop" href="#" data-tooltip="Desktop 1441px <" data-position="bottom"><span class="mfn-icon mfn-icon-desktop"></span></a></li>
            <li><a class="mfn-option-btn btn-medium mfn-option-blank mfn-preview-opt" data-preview="laptop" title="Laptop" href="#" data-tooltip="Laptop 960 - 1440px" data-position="bottom"><span class="mfn-icon mfn-icon-laptop"></span></a></li>
            <li><a class="mfn-option-btn btn-medium mfn-option-blank mfn-preview-opt" data-preview="tablet" title="Tablet" href="#" data-tooltip="Tablet 768 - 959px" data-position="bottom"><span class="mfn-icon mfn-icon-tablet"></span></a></li>
            <li><a class="mfn-option-btn btn-medium mfn-option-blank mfn-preview-opt" data-preview="mobile" title="Mobile" href="#" data-tooltip="Mobile < 768px" data-position="bottom"><span class="mfn-icon mfn-icon-mobile"></span></a></li>
        </ul>

    </div>

    <div class="topbar-tools">

        <?php if( $this->template_type && in_array($this->template_type, array('header')) ) { ?>
            <div class="topbar-header-options mfn-header-type-preview mfn-builder-preview-type">
                <a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header btn-active" data-preview="header-default" title="Default" href="#" data-tooltip="<?php esc_html_e('Default', 'mfn-opts'); ?>" href="#" data-position="bottom"><?php esc_html_e('Default', 'mfn-opts'); ?></a>
                <a class="disabled mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header" data-preview="header-sticky" title="Sticky" href="#" data-tooltip="<?php esc_html_e('Enable it in Header Options', 'mfn-opts'); ?>" href="#" data-position="bottom"><?php esc_html_e('Sticky', 'mfn-opts'); ?></a>
                <a class="disabled mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header" data-preview="header-mobile" title="Mobile" href="#" data-tooltip="<?php esc_html_e('Enable it in Header Options', 'mfn-opts'); ?>" href="#" data-position="bottom"><?php esc_html_e('Mobile', 'mfn-opts'); ?></a>
            </div>
        <?php }else if( $this->template_type && in_array($this->template_type, array('cart')) ) { ?>
            <div class="topbar-cart-options mfn-cart-type-preview mfn-builder-preview-type">
                <a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-cart btn-active" data-preview="default" title="Not empty" href="#" data-tooltip="<?php esc_html_e('Not empty', 'mfn-opts'); ?>" href="#" data-position="bottom"><?php esc_html_e('Not empty', 'mfn-opts'); ?></a>
                <a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-cart" data-preview="cart-empty" title="Empty" href="#" data-tooltip="<?php esc_html_e('Empty', 'mfn-opts'); ?>" href="#" data-position="bottom"><?php esc_html_e('Empty', 'mfn-opts'); ?></a>
            </div>
        <?php } ?>

        <a class="mfn-option-btn btn-large mfn-option-blank mfn-back-to-wp" title="Back to WordPress" href="<?php echo $referrer; ?>" data-tooltip="<?php esc_html_e('Back to WordPress', 'mfn-opts'); ?>" data-position="bottom"><span class="mfn-icon mfn-icon-wordpress"></span></a>

        <div class="mfn-option-dropdown">
            <a class="mfn-option-btn btn-large mfn-option-blank btn-icon-right mfn-option-text btn-icon-right btn-medium" style="padding: 0 4px;" href="#"><span class="mfn-icon mfn-icon-support"></span><span style="width: 9px;" class="mfn-icon mfn-icon-unfold"></span></a>
            <div class="dropdown-wrapper">
                <h6><?php esc_html_e('Manual & Support', 'mfn-opts'); ?>:</h6>
                <div class="mfn-dropdown-divider"></div>
                <a target="_blank" class="mfn-dropdown-item" href="https://support.muffingroup.com/"><span class="mfn-icon mfn-icon-support-center"></span> <?php esc_html_e('Support Center', 'mfn-opts'); ?></a>
                <a target="_blank" class="mfn-dropdown-item" href="https://forum.muffingroup.com/betheme/"><span class="mfn-icon mfn-icon-ticket-system"></span> <?php esc_html_e('Ticket System', 'mfn-opts'); ?></a>
                <div class="mfn-dropdown-divider"></div>
                <a target="_blank" class="mfn-dropdown-item" href="https://support.muffingroup.com/documentation/"><span class="mfn-icon mfn-icon-documentation"></span> <?php esc_html_e('Documentation', 'mfn-opts'); ?></a>
                <a target="_blank" class="mfn-dropdown-item" href="https://www.youtube.com/@MuffinGroup"><span class="mfn-icon mfn-icon-video-tutorials"></span> <?php esc_html_e('Video Tutorials', 'mfn-opts'); ?></a>
                <a target="_blank" class="mfn-dropdown-item" href="https://support.muffingroup.com/faq/"><span class="mfn-icon mfn-icon-support-faq"></span> <?php esc_html_e('FAQ', 'mfn-opts'); ?></a>
                <a target="_blank" class="mfn-dropdown-item" href="https://support.muffingroup.com/changelog/"><span class="mfn-icon mfn-icon-changelog"></span> <?php esc_html_e('Changelog', 'mfn-opts'); ?></a>
                <div class="mfn-dropdown-divider"></div>
                <a target="_blank" class="mfn-dropdown-item" href="https://www.facebook.com/groups/betheme"><span class="mfn-icon mfn-icon-community"></span> <?php esc_html_e('FB Community', 'mfn-opts'); ?></a>
                <a target="_blank" class="mfn-dropdown-item" href="https://muffingroup.com/betheme/"><span class="mfn-icon mfn-icon-be"></span> <?php esc_html_e('Betheme Website', 'mfn-opts'); ?></a>
            </div>
        </div>

        <a class="mfn-option-btn btn-large mfn-option-blank btn-live-css-switcher" title="Live CSS" href="#" data-tooltip="<?php esc_html_e('Live CSS', 'mfn-opts'); ?>" data-position="bottom"><span class="mfn-icon mfn-icon-css-live-editor"></span></a>

        <a class="mfn-option-btn btn-large mfn-option-blank btn-navigator-switcher" title="Navigator" href="#" data-tooltip="<?php esc_html_e('Navigator', 'mfn-opts'); ?>" data-position="bottom"><span class="mfn-icon mfn-icon-navigator"></span></a>
        <a class="mfn-option-btn btn-large mfn-option-blank btn-undo mfn-history-btn" title="Undo" href="#" data-tooltip="<?php esc_html_e('Undo', 'mfn-opts'); ?>" data-position="bottom"><span class="mfn-icon mfn-icon-undo"></span></a>
        <a class="mfn-option-btn btn-large mfn-option-blank btn-redo mfn-history-btn inactive" title="Redo" href="#" data-tooltip="<?php esc_html_e('Redo', 'mfn-opts'); ?>" data-position="bottom"><span class="mfn-icon mfn-icon-redo"></span></a>

        <a data-tooltip="<?php esc_html_e('View page', 'mfn-opts'); ?>" data-position="bottom" class="mfn-option-btn btn-large mfn-option-blank menu-viewpage" title="View page" href="<?php echo $view_page_link; ?>" target="_blank"><span class="mfn-icon mfn-icon-view-page"></span></a>
        <a class="mfn-option-btn btn-large mfn-option-blank mfn-preview-generate" title="Generate preview" data-tooltip="<?php esc_html_e('Generate preview', 'mfn-opts'); ?>" data-position="bottom" target="_blank" href="#" data-href="<?php echo $generate_preview_link; ?>"><span class="mfn-icon mfn-icon-preview"></span></a>

        <a href="#" data-action="update" class="mfn-btn btn-save-form-primary mfn-btn-green btn-copy-text btn-save-changes"> <span data-publishlabel="<?php echo esc_html__( 'Update', 'mfn-opts' ); ?>" data-draftlabel="<?php echo esc_html__( 'Save draft', 'mfn-opts' ); ?>" class="btn-wrapper"><?php echo $post_status === 'draft' ? esc_html__( 'Save draft', 'mfn-opts' ) : esc_html__( 'Update', 'mfn-opts' ); ?></span> </a>

        <div class="mfn-option-dropdown btn-save-action">
            <a href="#" class="mfn-btn btn-save-option mfn-btn-green"> <span class="mfn-icon mfn-icon-unfold-light"></span> </a>
            <div class="dropdown-wrapper">
                <a data-action="<?php echo $post_status === 'draft' ? 'publish' : 'draft'; ?>" class="mfn-dropdown-item btn-save-form-secondary btn-save-changes" data-publishlabel="<?php echo esc_html__( 'Save as draft', 'mfn-opts' ); ?>" data-draftlabel="<?php echo esc_html__( 'Publish', 'mfn-opts' ); ?>" href="#"> <?php echo $post_status === 'draft' ? esc_html__( 'Publish', 'mfn-opts' ) : esc_html__( 'Save as draft', 'mfn-opts' ); ?> </a>
            </div>
        </div>

    </div>

</div>
