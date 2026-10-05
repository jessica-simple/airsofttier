<?php

$this_modal_title = __('Cart', 'woocommerce' );

if( $this->template_type == 'checkout' ) { 
	$this_modal_title = __('Checkout', 'woocommerce' );
}else if( $this->template_type == 'thanks' ) { 
	$this_modal_title = __('Thank you', 'woocommerce' );
}else if( $this->template_type == 'search' ) { 
	$this_modal_title = __('Search', 'woocommerce' );
}

?>

<div class="mfn-modal modal-cart-confirmation modal-confirm mfn-modal-700"> 
	<div class="mfn-modalbox mfn-form mfn-shadow-1"> 
		<div class="modalbox-header"> 
			<div class="options-group"> 
				<div class="modalbox-title-group"> 
					<span class="modalbox-icon mfn-icon-settings"></span> 
					<div class="modalbox-desc"> 
						<h4 class="modalbox-title"><?php echo $this_modal_title.' '; _e('template confirmation', 'mfn-opts' ); ?></h4> 
					</div>
				</div>
			</div>

			<div class="options-group"> 
				<a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a> 
			</div>
		</div>
		<div class="modalbox-content"> 
			<img class="icon" alt="" src="<?php echo get_template_directory_uri('/').'/muffin-options/svg/warning.svg'; ?>"> 
			<h3><?php echo $this_modal_title.' '; _e('template confirmation', 'mfn-opts'); ?></h3>
			<?php 

			$valid_tmpl = get_option('mfn_'.$this->template_type.'_template');

			if( empty($valid_tmpl) || ( !empty($valid_tmpl) && !is_numeric($valid_tmpl) ) ){
				$valid_tmpl = get_option('mfn_'.$this->template_type.'_template_used');
			}

			if( !empty($valid_tmpl) && get_post_status($valid_tmpl) == 'publish' && get_post_type($valid_tmpl) == 'template' ) {
					
				if($this->post_id == $valid_tmpl) {
					echo '<p class="modal-cart-confirmation-desc">'.__('This is the currently valid template.', 'mfn-opts' ).'</p>';
				}else{
					echo '<p class="modal-cart-confirmation-desc">'.sprintf( 'The currently valid template is %s. Do you want to overwrite it?', '<strong>'.get_the_title($valid_tmpl).'</strong>' ).'</p>';
				}
				
			} else {
				echo '<p class="modal-cart-confirmation-desc">'.__('Do you want to apply this template as default view?', 'mfn-opts' ).'</p>';
			}

			echo '<a class="mfn-btn mfn-btn-red btn-wide btn-modal-close" href="#"><span class="btn-wrapper">'.__('Cancel', 'mfn-opts').'</span></a> ';

			if( (!empty($valid_tmpl) && $this->post_id != $valid_tmpl) || empty($valid_tmpl) ) {
				echo '<a class="mfn-btn btn-wide btn-modal-confirm-with-overwrite" data-set="'.__('Save & Set as default View', 'mfn-opts').'" data-back="'.__('Back to default View', 'mfn-opts').'" href="#"><span class="btn-wrapper">'.__('Save & Set as default View', 'mfn-opts').'</span></a> ';
			}elseif( !empty($valid_tmpl) && $this->post_id == $valid_tmpl ) {
				echo '<a class="mfn-btn btn-wide btn-modal-confirm-with-overwrite confirmed" data-set="'.__('Save & Set as default View', 'mfn-opts').'" data-back="'.__('Back to default View', 'mfn-opts').'" href="#"><span class="btn-wrapper">'.__('Back to default View', 'mfn-opts').'</span></a> ';
			}

			echo '<a class="mfn-btn mfn-btn-green btn-wide btn-save-changes" href="#"><span class="btn-wrapper">'.__('Update', 'mfn-opts').'</span></a> ';

				
			?>
			
		</div>
	</div>
</div>
