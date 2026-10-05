<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

?>
<div class="mfn-modal modal-another-pages" id="modal-another-pages">
	<div class="mfn-modalbox mfn-form mfn-shadow-1">

		<div class="modalbox-header">
			<div class="options-group">
				<div class="modalbox-title-group">
					<span class="modalbox-icon mfn-icon-edit-pages"></span>
					<div class="modalbox-desc">
						<h4 class="modalbox-title"><?php esc_html_e('Another pages', 'mfn-opts'); ?></h4>
					</div>
				</div>
			</div>
			
			<div class="options-group right">
				<div class="modalbox-search">
					<div class="form-control">
						<input class="mfn-form-control mfn-form-input mfn-search" type="text" placeholder="Search">
					</div>
				</div>
	      	</div>

			<div class="options-group">
				<a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
			</div>
			
		</div>

		<div class="modalbox-content"></div>

	</div>
</div>