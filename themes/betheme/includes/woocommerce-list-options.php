<?php
	global $mfn_global;
	
    echo '<div class="mfn-woo-list-options">';
    	echo '<form class="mfn-before-products-list-form mfn_attr_filters" role="navigation" aria-label="'. __('attribute filters', 'betheme') .'">';

        if( !empty($_GET['mfn-template-id']) ){
          echo '<input type="hidden" name="mfn-template-id" value="'. esc_attr($_GET['mfn-template-id']) .'">';
        }

    		// echo '<input type="hidden" name="mfn_attr_filter" value="1">';


    		$raw_qm = !empty($mfn_global['archive_template']) ? get_post_meta( $mfn_global['archive_template'], 'mfn-query-modifiers', true ) : false;

    		$to_pp = !empty($mfn_global['archive_template']) && !empty(get_post_meta( $mfn_global['archive_template'], 'mfn_template_perpage', true )) ? get_post_meta( $mfn_global['archive_template'], 'mfn_template_perpage', true ) : mfn_opts_get('shop-products');

    		if( !empty($to_pp) || !empty($_GET['visual']) || !empty( $raw_qm ) ) {

          $current_per_page = filter_input(INPUT_GET, 'per_page', FILTER_SANITIZE_NUMBER_INT);
          $perpage = $to_pp ?? 20;

					if ( ! empty( $raw_qm ) ) {
						$qm = json_decode( $raw_qm, true );
						if( !empty($qm['posts_per_page']) ) $perpage = $qm['posts_per_page'];
					}

    			$x = 0;

      		echo '<div class="mfn-woo-list mfn-woo-list-perpage">';

      			$pp1 = round( $perpage/2 );
      			$pp2 = $perpage;
      			$pp3 = $perpage*2;
      			$pp4 = $perpage*3;

    			  echo '<span class="show">'.__('Show', 'woocommerce').': </span>';

    			  echo '<ul>';
    			    echo '<li '.($current_per_page && $current_per_page == $pp1 ? 'class="active"' : null ).' role="link" tabindex="0"><span class="num"><input '.($current_per_page && $current_per_page == $pp1 ? 'checked' : null ).' type="radio" name="per_page" value="'.$pp1.'">'.$pp1.'</span></li>';
    			    echo '<li '.($current_per_page && $current_per_page == $pp2 || !$current_per_page ? 'class="active"' : null ).' role="link" tabindex="0"><span class="num"><input '.($current_per_page && $current_per_page == $pp2 || !$current_per_page ? 'checked' : null ).' type="radio" name="per_page" value="'.$pp2.'">'.$pp2.'</span></li>';
    			    echo '<li '.($current_per_page && $current_per_page == $pp3 ? 'class="active"' : null ).' role="link" tabindex="0"><span class="num"><input '.($current_per_page && $current_per_page == $pp3 ? 'checked' : null ).' type="radio" name="per_page" value="'.$pp3.'">'.$pp3.'</span></li>';
    			    echo '<li '.($current_per_page && $current_per_page == $pp4 ? 'class="active"' : null ).' role="link" tabindex="0"><span class="num"><input '.($current_per_page && $current_per_page == $pp4 ? 'checked' : null ).' type="radio" name="per_page" value="'.$pp4.'">'.$pp4.'</span></li>';
    				echo '</ul>';

    			echo '</div>';
    	  }

			if( !empty(mfn_opts_get('shop-list-layout')) || !empty($_GET['visual']) || !empty( get_post_meta($mfn_global['archive_template'], 'mfn-shop-list-layout', true) ) ){

			  $layout = mfn_get_layout();

		    echo '<div class="mfn-woo-list mfn-woo-list-style">';
		    	echo '<ul>';

			    	if( !empty(Mfn_Builder_Front::$item_obj['attr']['layout_version']) ){

			    		$layout = !empty(Mfn_Builder_Front::$item_obj['attr']['layout_new']) ? Mfn_Builder_Front::$item_obj['attr']['layout_new'] : 'grid';

			    		if( !empty( $_GET['layout'] ) && in_array($_GET['layout'], array('grid', 'list', 'masonry')) ) $layout = $_GET['layout'];

			    		echo '<li '. ( $layout && $layout == 'grid' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'grid' ? 'checked' : null ).' type="radio" name="layout" value="grid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('grid three columns', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><line class="path" x1="6" y1="14" x2="6" y2="2"/><line class="path" x1="10" y1="14" x2="10" y2="2"/><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="2" y1="6" x2="14" y2="6"/><line class="path" x1="2" y1="10" x2="14" y2="10"/></g></svg></li> ';
	  		    	echo '<li '. ( $layout && $layout == 'masonry' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'masonry' ? 'checked' : null ).' type="radio" name="layout" value="masonry"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('masonry', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><line class="path" x1="2" y1="7" x2="6" y2="7"/><line class="path" x1="6" y1="9" x2="10" y2="9"/><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="10" y1="8" x2="14" y2="8"/><line class="path" x1="6" y1="14" x2="6" y2="2"/><line class="path" x1="10" y1="14" x2="10" y2="2"/></g></svg></li> ';
	  		    	echo '<li '. ( $layout && ($layout == 'list' || $layout == 'list_2') ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && ($layout == 'list' || $layout == 'list_2') ? 'checked' : null ).' type="radio" name="layout" value="'.( !empty(Mfn_Builder_Front::$item_obj['attr']['layout_new']) && Mfn_Builder_Front::$item_obj['attr']['layout_new'] == 'list_2' ? 'list_2' : 'list' ).'"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('list', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="2" y1="6" x2="14" y2="6"/><line class="path" x1="2" y1="10" x2="14" y2="10"/></g></svg></li> ';

			    	}else{
	  		    	echo '<li '. ( $layout && $layout == 'grid' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'grid' ? 'checked' : null ).' type="radio" name="layout" value="grid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('grid three columns', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><line class="path" x1="6" y1="14" x2="6" y2="2"/><line class="path" x1="10" y1="14" x2="10" y2="2"/><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="2" y1="6" x2="14" y2="6"/><line class="path" x1="2" y1="10" x2="14" y2="10"/></g></svg></li> ';
	  		    	echo '<li '. ( $layout && $layout == 'grid col-4' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'grid col-4' ? 'checked' : null ).' type="radio" name="layout" value="grid4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('grid four columns', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><line class="path" x1="11" y1="14" x2="11" y2="2"/><line class="path" x1="5" y1="14" x2="5" y2="2"/><line class="path" x1="8" y1="14" x2="8" y2="2"/><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="2" y1="8" x2="14" y2="8"/><line class="path" x1="2" y1="5" x2="14" y2="5"/><line class="path" x1="2" y1="11" x2="14" y2="11"/></g></svg></li> ';
	  		    	echo '<li '. ( $layout && $layout == 'masonry' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'masonry' ? 'checked' : null ).' type="radio" name="layout" value="masonry"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('masonry', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><line class="path" x1="2" y1="7" x2="6" y2="7"/><line class="path" x1="6" y1="9" x2="10" y2="9"/><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="10" y1="8" x2="14" y2="8"/><line class="path" x1="6" y1="14" x2="6" y2="2"/><line class="path" x1="10" y1="14" x2="10" y2="2"/></g></svg></li> ';
	  		    	echo '<li '. ( $layout && $layout == 'list' ? 'class="active"' : null ).' role="link" tabindex="0"><input '. ( $layout && $layout == 'list' ? 'checked' : null ).' type="radio" name="layout" value="list"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-label="'. __('list', 'betheme') .'"><defs><style>.cls-1{opacity:0.2;}.path{fill:none;stroke-miterlimit:10;}</style></defs><g id="Layer_4" data-name="Layer 4"><rect class="path" x="2" y="2" width="12" height="12"/><line class="path" x1="2" y1="6" x2="14" y2="6"/><line class="path" x1="2" y1="10" x2="14" y2="10"/></g></svg></li> ';
	  		    }
		    	echo '</ul>';
		    echo '</div>';

			}

		echo '</form>';
	echo '</div>';
 ?>
