<?php 
if(!empty($_GET)){
	$mfn_active_filters = '';
	foreach($_GET as $f=>$filter){
		if(is_array($filter) && is_iterable($filter)){
			foreach($filter as $filtr){
				$term = get_term_by('slug', $filtr, str_replace('mfn_', '', $f));

				if(!isset($term->term_id)){
					$term = get_term_by('slug', $filtr, 'pa_'.str_replace('mfn_', '', $f));
				}

				if(isset($term->term_id)){
					$mfn_active_filters .= '<li><span data-id="mfn_attr_'.$term->term_id.'"><span class="label">'.$term->name.'</span><span class="del">&#10005;</span></span></li>';
				}
			}
		}else{
			$term = get_term_by('slug', $filter, str_replace('mfn_', '', $f));

			if(!isset($term->term_id)){
				$term = get_term_by('slug', $filter, 'pa_'.str_replace('mfn_', '', $f));
			}

			if(isset($term->term_id)){
				$mfn_active_filters .= '<li><span class="label" data-id="mfn_attr_'.$term->term_id.'">'.$term->name.'</span></li>';
			}
		}
	}

	if( !empty($mfn_active_filters) ) {
		echo '<div class="mfn-woo-list-active-filters">';
			echo '<ul class="mfn-active-woo-filters">'.$mfn_active_filters.'</ul>';
		echo '</div>';
	}
}