<?php


echo '<div class="mfn-preview-toolbar mfn-header mfn-header-green header-large">';

    echo '<div class="options-group group-title">';
    echo '<div class="header-label">'.esc_html__('Responsive mode', 'mfn-opts').'</div>';
    echo '</div>';

    echo '<div class="options-group group-options">';
    echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt btn-active" data-preview="desktop" title="Desktop" href="#" data-tooltip="'.esc_html__('Desktop', 'mfn-opts').'" href="#" data-position="bottom"><span class="mfn-icon mfn-icon-desktop-light"></span></a>';
    echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt" data-preview="laptop" title="Laptop" href="#" data-tooltip="'.esc_html__('Laptop', 'mfn-opts').'" href="#" data-position="bottom"><span class="mfn-icon mfn-icon-laptop-light"></span></a>';
    echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt" data-preview="tablet" title="Tablet" href="#" data-tooltip="'.esc_html__('Tablet', 'mfn-opts').'" href="#" data-position="bottom"><span class="mfn-icon mfn-icon-tablet-light"></span></a>';
    echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt" data-preview="mobile" title="Mobile" href="#" data-tooltip="'.esc_html__('Mobile', 'mfn-opts').'" href="#" data-position="bottom"><span class="mfn-icon mfn-icon-mobile-light"></span></a>';

    echo '</div>';

    if( $this->template_type && $this->template_type == 'header' ){
        echo '<div class="options-group group-options mfn-header-type-preview mfn-builder-preview-type">';

        echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header btn-active" data-preview="header-default" title="Default" href="#" data-tooltip="Default" href="#" data-position="bottom">'.esc_html__('Default', 'mfn-opts').'</a>';
        echo '<a class="disabled mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header" data-preview="header-sticky" title="Sticky" href="#" data-tooltip="Enable it in Header Options" href="#" data-position="bottom">'.esc_html__('Sticky', 'mfn-opts').'</a>';
        echo '<a class="disabled mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-header" data-preview="header-mobile" title="Mobile" href="#" data-tooltip="Enable it in Header Options" href="#" data-position="bottom">'.esc_html__('Mobile', 'mfn-opts').'</a>';

        echo '</div>';
    }else if( $this->template_type && $this->template_type == 'cart' ) {
        echo '<div class="topbar-cart-options mfn-cart-type-preview mfn-builder-preview-type">
            <a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-cart btn-active" data-preview="default" title="Not empty" href="#" data-tooltip="'.esc_html__('Not empty', 'mfn-opts').'" href="#" data-position="bottom">'.esc_html__('Not empty', 'mfn-opts').'</a>
            <a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-opt-cart" data-preview="cart-empty" title="Empty" href="#" data-tooltip="'.esc_html__('Empty', 'mfn-opts').'" href="#" data-position="bottom">'.esc_html__('Empty', 'mfn-opts').'</a>
        </div>';
    }

    echo '<div class="options-group group-close">';
    echo '<a class="mfn-option-btn mfn-option-blank-dark btn-large mfn-preview-mode-close" title="Close" href="#"><span class="mfn-icon mfn-icon-close-light"></span></a>';
    echo '</div>';

echo '</div>';

?>