<?php  
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

echo '<div class="panel panel-global-sections" style="display: none;"><ul class="prebuilt-sections-list global-sections">';
    echo '</ul>';
    echo '<a target="_blank" href="'.admin_url('edit.php?post_type=template&tab=section').'" class="mfn-btn mfn-btn-fw mfn-btn-blue"><span class="btn-wrapper">'.esc_html__('Create a Global Section', 'mfn-opts').'</span></a>'; 
echo '</div>';