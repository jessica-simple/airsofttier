<!-- modal: template display conditions -->

<div class="mfn-modal has-footer modal-display-conditions">

	<div class="mfn-modalbox mfn-form mfn-form-verical mfn-shadow-1">

		<div class="modalbox-header">

			<div class="options-group">
				<div class="modalbox-title-group">
					<span class="modalbox-icon mfn-icon-shop"></span>
					<div class="modalbox-desc">
						<h4 class="modalbox-title"><?php esc_html_e('Display Conditions', 'mfn-opts'); ?></h4>
					</div>
				</div>
			</div>

			<div class="options-group">
				<a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" title="Close" href="#">
					<span class="mfn-icon mfn-icon-close"></span>
				</a>
			</div>

		</div>

		<div class="modalbox-content">
			<span class="mfn-icon display-conditions"></span>
			<h3><?php esc_html_e('Where Do You Want to Display Your Template?', 'mfn-opts'); ?></h3>
			<p><?php _e('Set the conditions that determine where your Template is used throughout your site.<br>For example, choose \'Entire Site\' to display the template across your site.', 'mfn-opts'); ?></p>

			<?php $conditions = (array) json_decode( get_post_meta($this->post_id, 'mfn_template_conditions', true) ); ?>

			<form id="tmpl-conditions-form">
			<div class="mfn-dynamic-form mfn-form">

				<?php

				$limit = (int) apply_filters( 'mfn_hints_limit', 500 );

				if( $this->template_type && (strpos($this->template_type, 'archive-') !== false || strpos($this->template_type, 'single-') !== false) ):

				$post_type = 'post';
				$everywhere_label = 'All posts';

				if( $this->template_type == 'archive-post' ) {
					$post_type = 'post';
					$everywhere_label = 'All archives';
				}
				if( strpos($this->template_type, 'archive-') !== false ) {
					$post_type = str_replace('archive-', '', $this->template_type);
					$everywhere_label = 'All archives';
				}
				if( strpos($this->template_type, 'single-') !== false ) { 
					$post_type = str_replace('single-', '', $this->template_type);
					$everywhere_label = 'All posts';
				}
				$taxonomies = get_object_taxonomies($post_type, 'objects');
				$excludes = array('post_format', 'product_type', 'product_visibility', 'product_shipping_class', 'elementor_library_category', 'elementor_library_type', 'link_category', 'nav_menu', 'wp_pattern_category', 'wp_template_part_area', 'wp_theme');

				foreach($excludes as $ex) {
					if( isset($taxonomies[$ex]) ) unset($taxonomies[$ex]);
				}

				if( isset($conditions) && count($conditions) > 0){ $x = 0; foreach($conditions as $c=>$cond) { 
					if( $cond->var == 'productcategory' ) {
						$cond->var = 'product_cat';
					}
					if( $cond->var == 'producttag' ) {
						$cond->var = 'product_tag';
					}
				?>
				<div class="mfn-df-row">
					<div class="df-row-inputs">
						<select name="mfn_template_conditions[<?php echo $x; ?>][rule]" class="mfn-form-control mfn-form-select df-input df-input-rule <?php if($cond->rule == 'exclude'){ echo 'minus'; } ?>">
							<option <?php if($cond->rule == 'include'){ echo 'selected'; } ?> value="include"><?php esc_html_e('Include', 'mfn-opts'); ?></option>
							<option <?php if($cond->rule == 'exclude'){ echo 'selected'; } ?> value="exclude"><?php esc_html_e('Exclude', 'mfn-opts'); ?></option>
						</select>
						<select name="mfn_template_conditions[<?php echo $x; ?>][var]" class="mfn-form-control mfn-form-select df-input df-input-var">
							<option <?php if($cond->var == 'all'){ echo 'selected'; } ?> value="all"><?php esc_html_e($everywhere_label, 'mfn-opts'); ?></option>
							<?php if( !empty($taxonomies) ) {
								foreach($taxonomies as $t=>$tax) {
									if( !empty($tax->public) ) echo '<option '.( $cond->var == $tax->name ? 'selected' : '' ).' value="'.$tax->name.'">'.$tax->label.'</option>';
								}
							} ?>
							<?php if( $this->template_type == 'archive-product' ) {
								echo '<option '.( $cond->var == 'wishlist' ? 'selected' : '' ).' value="wishlist">Wishlist</option>';
								echo '<option '.( $cond->var == 'products-search' ? 'selected' : '' ).' value="products-search">Search page</option>';
							} ?>
						</select>
						<?php if( !empty($taxonomies) ) {
							foreach($taxonomies as $t=>$tax) {
								if( !empty($tax->public) ) {

									$terms = get_terms([ 'taxonomy' => $tax->name, 'hide_empty' => false, 'number' => $limit ]);
									echo '<select name="mfn_template_conditions['.$x.']['.$tax->name.']" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-'.$tax->name.' '.( $cond->var == $tax->name ? 'show' : '' ).'">
									<option value="all">All</option>';
									if( !empty($terms) ){
										foreach($terms as $term){
											if( $tax->name == 'product_cat' ){
												echo '<option '. ( (isset($cond->{$tax->name}) && $cond->{$tax->name} == $term->term_id) || (isset($cond->productcategory) && $cond->productcategory == $term->term_id) ? 'selected' : '') .' value="'.$term->term_id.'">'.$term->name.'</option>';
											}else if( $tax->name == 'product_tag' ){
												echo '<option '. ( (isset($cond->{$tax->name}) && $cond->{$tax->name} == $term->term_id) || (isset($cond->producttag) && $cond->producttag == $term->term_id) ? 'selected' : '') .' value="'.$term->term_id.'">'.$term->name.'</option>';
											}else{
												echo '<option '. (isset($cond->{$tax->name}) && $cond->{$tax->name} == $term->term_id ? 'selected' : '') .' value="'.$term->term_id.'">'.$term->name.'</option>';
											}
										}
									}
									echo '</select>';
								}
							}
						} ?>
					</div>
					<a class="mfn-option-btn mfn-option-blank btn-large df-remove" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
				</div>
				<?php $x++; }} ?>

				<div class="mfn-df-row clone">
					<div class="df-row-inputs">
						<select data-name="mfn_template_conditions[0][rule]" class="mfn-form-control mfn-form-select df-input df-input-rule">
							<option value="include"><?php esc_html_e('Include', 'mfn-opts'); ?></option>
							<option value="exclude"><?php esc_html_e('Exclude', 'mfn-opts'); ?></option>
						</select>
						<select data-name="mfn_template_conditions[0][var]" class="mfn-form-control mfn-form-select df-input df-input-var">
							<option value="all"><?php esc_html_e($everywhere_label, 'mfn-opts'); ?></option>
							<?php if( !empty($taxonomies) ) {
								foreach($taxonomies as $t=>$tax) {
									if( !empty($tax->public) ) echo '<option value="'.$tax->name.'">'.$tax->label.'</option>';
								}
							} ?>
							<?php if( $this->template_type == 'archive-product' ) {
								echo '<option value="wishlist">Wishlist</option>';
								echo '<option value="products-search">Search page</option>';
							} ?>
						</select>
						<?php if( !empty($taxonomies) ) {

							foreach($taxonomies as $t=>$tax) {
								if( !empty($tax->public) ) {
									$terms = get_terms([ 'taxonomy' => $tax->name, 'hide_empty' => false, 'number' => $limit ]);
									echo '<select data-name="mfn_template_conditions[0]['.$tax->name.']" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-'.$tax->name.'">
									<option value="all">All</option>';
									if( !empty($terms) ){
										foreach($terms as $term){
											echo '<option value="'.$term->term_id.'">'.$term->name.'</option>';
										}
									}
									echo '</select>';
								}
							}
						} ?>
					</div>
					<a class="mfn-option-btn mfn-option-blank btn-large df-remove" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
				</div>

				<?php else: 

				$mfn_cond_terms = mfn_get_posttypes('tax');
				/*echo '<pre>';
				print_r($mfn_cond_terms);
				echo '</pre>';*/

				?>

				<?php if( isset($conditions) && count($conditions) > 0){ $x = 0; foreach($conditions as $c=>$cond){ ?>
					<div class="mfn-df-row">
					<div class="df-row-inputs">
						<select name="mfn_template_conditions[<?php echo $x; ?>][rule]" class="mfn-form-control mfn-form-select df-input df-input-rule <?php if($cond->rule == 'exclude'){ echo 'minus'; } ?>">
							<option <?php if($cond->rule == 'include'){ echo 'selected'; } ?> value="include"><?php esc_html_e('Include', 'mfn-opts'); ?></option>
							<option <?php if($cond->rule == 'exclude'){ echo 'selected'; } ?> value="exclude"><?php esc_html_e('Exclude', 'mfn-opts'); ?></option>
						</select>
						<select name="mfn_template_conditions[<?php echo $x; ?>][var]" class="mfn-form-control mfn-form-select df-input df-input-var">
							<option <?php if($cond->var == 'everywhere'){ echo 'selected'; } ?> value="everywhere"><?php esc_html_e('Entire Site', 'mfn-opts'); ?></option>
							<option <?php if($cond->var == 'archives'){ echo 'selected'; } ?> value="archives"><?php esc_html_e('Archives', 'mfn-opts'); ?></option>
							<option <?php if($cond->var == 'singular'){ echo 'selected'; } ?> value="singular"><?php esc_html_e('Singular', 'mfn-opts'); ?></option>
							<option <?php if($cond->var == 'other'){ echo 'selected'; } ?> value="other"><?php esc_html_e('Other', 'mfn-opts'); ?></option>
						</select>
						<select name="mfn_template_conditions[<?php echo $x; ?>][archives]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-archives <?php if($cond->var == 'archives') {echo 'show';} ?>">
							<?php if(count($mfn_cond_terms) > 0): foreach($mfn_cond_terms as $s=>$item){
								if( is_array($item) && $item['items'] ){
									echo '<optgroup label="'.$item['label'].'">';
									echo '<option '.( !empty($cond->archives) && $cond->archives == $s ? "selected" : null ).' value="'.$s.'">'.esc_html__('All', 'mfn-opts').'</option>';
									if( is_iterable($item['items']) ){
										foreach($item['items'] as $opt){
											echo '<option '.( !empty($cond->archives) && $cond->archives == $s.':'.$opt->id ? "selected" : null ).' value="'.$s.':'.$opt->id.'">'.$opt->name.'</option>';
										}
									}
									echo '</optgroup>';
								}elseif( !is_array($item) ){
									echo '<option '.( !empty($cond->archives) && $cond->archives == $s ? "selected" : null ).' value="'.$s.'">'.$item.'</option>';
								}
							} endif; ?>
						</select>
						<select name="mfn_template_conditions[<?php echo $x; ?>][singular]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-singular <?php if($cond->var == 'singular') {echo 'show';} ?>">
							<?php 
							if(count($mfn_cond_terms) > 0): foreach($mfn_cond_terms as $s=>$item){
								if( is_array($item) ){
									echo '<optgroup label="'.$item['label'].'">';
									echo '<option '.( !empty($cond->singular) && $cond->singular == $s ? "selected" : null ).' value="'.$s.'">'.esc_html__('All', 'mfn-opts').'</option>';
									/*if( $s == 'page' ){
										echo '<option '.( !empty($cond->singular) && $cond->singular == "front-page" ? "selected" : null ).' value="front-page">Front page</option>';
									}*/
									if( is_array($item) && $item['items'] ){
										if( is_iterable($item['items']) ){
											foreach( $item['items'] as $opt){
												echo '<option '.( !empty($cond->singular) && $cond->singular == $s.':'.$opt->id ? "selected" : null ).' value="'.$s.':'.$opt->id.'">'.$opt->name.'</option>';
											}
										}
										
									}
									echo '</optgroup>';
								}else{
									echo '<option '.( !empty($cond->singular) && $cond->singular == $s ? "selected" : null ).' value="'.$s.'">'.$item.'</option>';
								}
							} endif; ?>
						</select>
						<select name="mfn_template_conditions[<?php echo $x; ?>][other]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-other <?php if($cond->var == 'other') {echo 'show';} ?>">
							<?php 
							echo '<option '.( !empty($cond->other) && $cond->other == 'search-page' ? "selected" : null ).' value="search-page">'.esc_html__('Search page', 'mfn-opts').'</option>';
							?>
						</select>
					</div>
					<a class="mfn-option-btn mfn-option-blank btn-large df-remove" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
				</div>
				<?php $x++; }} ?>

				<div class="mfn-df-row clone df-type-tmpl-part">
					<div class="df-row-inputs">
						<select data-name="mfn_template_conditions[0][rule]" class="mfn-form-control mfn-form-select df-input df-input-rule">
							<option value="include"><?php esc_html_e('Include', 'mfn-opts'); ?></option>
							<option value="exclude"><?php esc_html_e('Exclude', 'mfn-opts'); ?></option>
						</select>
						<select data-name="mfn_template_conditions[0][var]" class="mfn-form-control mfn-form-select df-input df-input-var">
							<option value="everywhere"><?php esc_html_e('Entire Site', 'mfn-opts'); ?></option>
							<option value="archives"><?php esc_html_e('Archives', 'mfn-opts'); ?></option>
							<option value="singular"><?php esc_html_e('Singular', 'mfn-opts'); ?></option>
							<option value="other"><?php esc_html_e('Other', 'mfn-opts'); ?></option>
						</select>
						<select data-name="mfn_template_conditions[0][archives]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-archives">
							<?php if(count($mfn_cond_terms) > 0): foreach($mfn_cond_terms as $s=>$item){
								if( is_array($item) && $item['items'] ) {
									echo '<optgroup label="'.$item['label'].'">';
									echo '<option value="'.$s.'">'.esc_html__('All', 'mfn-opts').'</option>';
									if( is_iterable($item['items']) ) {
										foreach($item['items'] as $opt) {
											echo '<option value="'.$s.':'.$opt->id.'">'.$opt->name.'</option>';
										}
									}
									echo '</optgroup>';
								}elseif( !is_array($item) ){
									echo '<option value="'.$s.'">'.$item.'</option>';
								}
							} endif; ?>
						</select>
						<select data-name="mfn_template_conditions[0][singular]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-singular">
							<?php 
							if(count($mfn_cond_terms) > 0): foreach($mfn_cond_terms as $s=>$item) {
								if( is_array($item) ) {
									echo '<optgroup label="'.$item['label'].'">';
									echo '<option value="'.$s.'">'.esc_html__('All', 'mfn-opts').'</option>';
									/*if( $s == 'page' ){
										echo '<option value="front-page">Front page</option>';
									}*/
									if( is_array($item) && $item['items'] ) {
										if( is_iterable($item['items']) ) {
											foreach( $item['items'] as $opt) {
												echo '<option value="'.$s.':'.$opt->id.'">'.$opt->name.'</option>';
											}
										}
									}
									echo '</optgroup>';
								}else{
									echo '<option value="'.$s.'">'.$item.'</option>';
								}
							} endif; ?>
						</select>
						<select data-name="mfn_template_conditions[0][other]" class="mfn-form-control mfn-form-select df-input df-input-opt df-input-other">
							<?php 
							echo '<option selected value="search-page">'.esc_html__('Search page', 'mfn-opts').'</option>';
							?>
						</select>
					</div>
					<a class="mfn-option-btn mfn-option-blank btn-large df-remove" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
				</div>

				<?php endif; ?>
			</div>

			<a class="mfn-btn btn-icon-left df-add-row mfn-btn-blank" href="#"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-add"></span><?php esc_html_e('Add condition', 'mfn-opts'); ?></span></a>

			<?php if( $this->post_type == 'template' && $this->template_type == 'popup' ) {
				$publication_opts = !empty(get_post_meta($this->post_id, 'mfn_publication_options', true)) ? json_decode(get_post_meta($this->post_id, 'mfn_publication_options', true)) : false; ?>

				<div class="mfn-conditions-dates">
					<h3>Publication options</h3>
					<div class="mfn-publication-opt-wrapper <?php echo !empty($publication_opts->date_start) || !empty($publication_opts->date_end) ? 'mfn-opt-active' : ''; ?>">
						<span class="mfn-checkbox <?php echo !empty($publication_opts->date_start) || !empty($publication_opts->date_end) ? 'active' : ''; ?>"></span>
						<div class="mfn-publication-opt-input">
							<label><span class="mfn-icon icon-calendar-line"></span> Start date</label>
							<input type="datetime-local" class="mfn-form-control" name="mfn_publication_options[date_start]" <?php if( !empty($publication_opts->date_start) ) { echo 'value="'.$publication_opts->date_start.'"';} ?> min="<?php echo current_time( 'Y-m-d H:i' ); ?>" class="df-input">
						</div>
						<div class="mfn-publication-opt-input">
							<label><span class="mfn-icon icon-calendar-line"></span> End date</label>
							<input type="datetime-local" class="mfn-form-control" name="mfn_publication_options[date_end]" <?php if( !empty($publication_opts->date_end) ) { echo 'value="'.$publication_opts->date_end.'"';} ?> min="<?php echo current_time( 'Y-m-d H:i' ); ?>" class="df-input">
						</div>
					</div>
				</div>
			<?php } ?>

			</form>


		</div>


		<div class="modalbox-footer">
			<div class="options-group right">
				<a class="mfn-btn mfn-btn-blue btn-modal-save btn-save-changes" href="#"><span class="btn-wrapper"><?php esc_html_e('Save', 'mfn-opts'); ?></span></a>
				<a class="mfn-btn btn-modal-close" href="#"><span class="btn-wrapper"><?php esc_html_e('Cancel', 'mfn-opts'); ?></span></a>
			</div>
		</div>

	</div>

</div>
