<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

class MfnLocalCssCompability {

	private $id = false;
	private $mfn_items = false;
	private $mfn_items_obj = array();
	private $s = 0;
	private $w = 0;
	private $i = 0;
	private $nw = 0;
	private $detect_old_builder = false;
	public $devices = array('laptop', 'tablet', 'mobile');
	public $builder_storage = false;


	public function render($id) {
		$this->mfn_items_obj = array();

		if( is_numeric($id) ) {
			$this->id = $id;
			$this->mfn_items = get_post_meta($this->id, 'mfn-page-items', true);
			if( empty(get_post_meta($this->id, 'mfn-page-items-backup', true)) ) update_post_meta($this->id, 'mfn-page-items-backup', $this->mfn_items);
		}else{
			$this->mfn_items = $id; // prebuilts
		}
		
		$this->detect_old_builder = false;
		$this->builder_storage = mfn_opts_get('builder-storage');

		if( !is_array( $this->mfn_items ) ) $this->mfn_items = unserialize( call_user_func('base'.'64_decode', $this->mfn_items), ['allowed_classes' => false] );
		
		if( !empty( $this->mfn_items ) && is_array( $this->mfn_items ) ) $this->sections();

		if( !$this->id ) return $this->mfn_items;
	}

	public function sections() {
		$mfn_fields = new Mfn_Builder_Fields();
		$sections_fields = $mfn_fields->get_section();

		$this->detect_old_builder = false;

		foreach( $this->mfn_items as $s=>$section ) {

			if( !empty($section['mfn_global_section_id']) ) continue;

			$this->s = $s;

			if( !empty($section['attr']) && is_iterable($section['attr']) ) {

				foreach ($sections_fields as $sf) {

					$item_obj = array();

					if( empty($sf['id']) ) continue;

					if( strpos($sf['id'], 'css_') === false ) {

						if( $sf['id'] == 'width_switcher' && !empty($this->mfn_items[$s]['attr'][$sf['id']]) && $this->mfn_items[$s]['attr'][$sf['id']] == 'default' ) unset($this->mfn_items[$s]['attr'][$sf['id']]);
						if( $sf['id'] == 'height_switcher' && !empty($this->mfn_items[$s]['attr'][$sf['id']]) && $this->mfn_items[$s]['attr'][$sf['id']] == 'default' ) unset($this->mfn_items[$s]['attr'][$sf['id']]);

						if( !empty( $sf['responsive'] ) && !isset($this->mfn_items[$s]['attr'][$sf['id']]['desktop']) ){
							if( isset( $this->mfn_items[$s]['attr'][$sf['id']] ) && !isset( $this->mfn_items[$s]['attr'][$sf['id']]['desktop'] )  ) $this->mfn_items[$s]['attr'][$sf['id']] = array( 'desktop' => $this->mfn_items[$s]['attr'][$sf['id']]);

							foreach( $this->devices as $device ) {
								if( isset( $this->mfn_items[$s]['attr'][$sf['id'].'_'.$device] ) && isset($this->mfn_items[$s]['attr'][$sf['id']]) && is_array($this->mfn_items[$s]['attr'][$sf['id']]) ) $this->mfn_items[$s]['attr'][$sf['id']][$device] = $this->mfn_items[$s]['attr'][$sf['id'].'_'.$device];
							}
						}
						
					}else{

						$item_obj['selector'] = $sf['selector'];
						$item_obj['style'] = $sf['style'];
						$item_obj['val'] = array();

						if( !empty($sf['old_id']) && isset( $this->mfn_items[$s]['attr'][$sf['old_id']] ) ) {
							// if style: for dekstop
							if( !empty($sf['responsive']) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$s]['attr'][$sf['old_id']];
							}else{
								$item_obj['val'] = $this->mfn_items[$s]['attr'][$sf['old_id']];
							}
							unset($this->mfn_items[$s]['attr'][$sf['old_id']]);
							$this->detect_old_builder = true;
						}

						if( !empty( $this->mfn_items[$s]['attr'][$sf['id']]['val'] ) ) {
							// if css_ for desktop with val
							if( !empty($sf['responsive']) && !isset( $this->mfn_items[$s]['attr'][$sf['id']]['val']['desktop'] ) && !isset( $this->mfn_items[$s]['attr'][$sf['id']]['val']['laptop'] ) && !isset( $this->mfn_items[$s]['attr'][$sf['id']]['val']['tablet'] ) && !isset( $this->mfn_items[$s]['attr'][$sf['id']]['val']['mobile'] ) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$s]['attr'][$sf['id']]['val'];
							}else{
								$item_obj['val'] = $this->mfn_items[$s]['attr'][$sf['id']]['val'];
							}
							
							unset( $this->mfn_items[$s]['attr'][$sf['id']]['val'] );
							$this->detect_old_builder = true;
						}
						
						if( !empty($sf['responsive']) ) {
							foreach( $this->devices as $device ) {

								if( isset($sf['old_id']) ) {
									$sa_device = $sf['old_id'].'_'.$device;
									
									if( isset( $this->mfn_items[$s]['attr'][$sa_device] ) ) {
										// if style: below desktop
										$sf_device = $sf['id'].'_'.$device;
										if( !empty( $this->mfn_items[$s]['attr'][$sa_device] ) ) $item_obj['val'][$device] = $this->mfn_items[$s]['attr'][$sa_device];
										unset($this->mfn_items[$s]['attr'][$sa_device]);
										$this->detect_old_builder = true;
									}
								}

								$sa_device_n = $sf['id'].'_'.$device;

								if( isset( $this->mfn_items[$s]['attr'][$sa_device_n]['val'] ) && !isset( $this->mfn_items[$s]['attr'][$sa_device_n]['val'][$device] ) ) {
									// if css_ below desktop
									if( !empty( $this->mfn_items[$s]['attr'][$sa_device_n]['val'] ) ) $item_obj['val'][$device] = $this->mfn_items[$s]['attr'][$sa_device_n]['val'];
									unset($this->mfn_items[$s]['attr'][$sa_device_n]);
									$this->detect_old_builder = true;
								}
								
							}
						}

						if( !empty( $item_obj['val'] ) ) $this->mfn_items[$s]['attr'][$sf['id']] = $item_obj;

					}

				}

			}

			if( !empty($section['wraps']) ) {
				foreach( $section['wraps'] as $w=>$wrap ) {
					$this->w = $w;
					$this->wraps($wrap);
				}
			}

			$this->mfn_items_obj[] = $this->mfn_items[$s];

		}

		if( $this->id && $this->detect_old_builder ) $this->update();

	}


	public function wraps( $wrap ) {
		$mfn_fields = new Mfn_Builder_Fields();
		$wraps_fields = $mfn_fields->get_wrap();

		if( !empty($wrap['attr']) && is_iterable($wrap['attr']) ) {

			foreach ($wraps_fields as $wf) {

				$item_obj = array();

				if( empty($wf['id']) ) continue;

				if( strpos($wf['id'], 'css_') === false ) {

					if( $wf['id'] == 'width_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]);
					if( $wf['id'] == 'height_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]);

					if( !empty( $wf['responsive'] ) ) {
						if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['desktop'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] = array( 'desktop' => $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] );

						foreach( $this->devices as $device ) {
							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id'].'_'.$device] ) && isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]) && is_array($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]) ) $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id'].'_'.$device];
						}
					}

				}else{

					$item_obj['selector'] = $wf['selector'];
					$item_obj['style'] = $wf['style'];
					$item_obj['val'] = array();

					if( !empty($wf['old_id']) ) {

						$old_id_1 = $wf['old_id'];
						$old_id_2 = false;

						if( strpos($old_id_1, 'style:.mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner') !== false ) {
							$old_id_2 = str_replace('style:.mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner', 'style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner', $old_id_1);
						}

						if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_1] ) ) {

							// if style: for dekstop
							if( !empty($wf['responsive']) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_1];
							}else{
								$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_1];
							}
							
							unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_1]);
							$this->detect_old_builder = true;
						
						}else if( !empty($old_id_2) && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_2] ) ) {

							// if style: for dekstop
							if( !empty($wf['responsive']) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_2];
							}else{
								$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_2];
							}
							
							unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$old_id_2]);
							$this->detect_old_builder = true;
							
						}

					}

					if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] ) ) {
						// if css_ for desktop with val
						if( !empty($wf['responsive']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']['desktop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']['laptop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']['tablet']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']['mobile']) ) {
							
							if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']) ){
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val'];
							}else{
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']];
							}
							
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val'];
						}
						
						if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val']) ) {
							unset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']]['val'] );
						}else{
							unset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] );
						}
						$this->detect_old_builder = true;
					}

					if( !empty($wf['responsive']) ) {
						foreach( $this->devices as $device ) {

							if( isset($wf['old_id']) ) {
								$sa_device = $wf['old_id'] . '_' . $device;

								if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device] ) ) {
									if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device] ) ) {
										$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device];
									}
									unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device]);
									$this->detect_old_builder = true;
								} elseif( !empty($old_id_2) ) {
									$sa_device_2 = $old_id_2 . '_' . $device;

									if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_2] ) ) {
										if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_2] ) ) {
											$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_2];
										}
										unset($this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_2]);
										$this->detect_old_builder = true;
									}
								}
							}

							$sa_device_n = $wf['id'] . '_' . $device;

							if(
								isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_n]['val'] )
								&& !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_n]['val'][$device] )
							) {
								if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_n]['val'] ) ) {
									$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_n]['val'];
								}
								unset( $this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$sa_device_n] );
								$this->detect_old_builder = true;
							}

						}
					}

					if( !empty( $item_obj['val'] ) ) {
						$this->mfn_items[$this->s]['wraps'][$this->w]['attr'][$wf['id']] = $item_obj;
					}

				}


			}

		}

		if( !empty($wrap['items']) ) {

			foreach( $wrap['items'] as $i=>$item ) {
				if( !empty($item['item_is_wrap']) ) {
					$this->nw = $i;
					$this->nested_wrap($item);
				}else{
					$this->i = $i;
					$this->item($item);
				}

			}
		}

		$this->mfn_items_obj[] = $this->mfn_items[$this->s]['wraps'][$this->w];


	}

	public function nested_wrap($wrap) {
		$mfn_fields = new Mfn_Builder_Fields();
		$wraps_fields = $mfn_fields->get_wrap();

		if( !empty( $wrap['attr']['global_wraps_select'] ) ) return;

		if( !empty($wrap['attr']) && is_iterable($wrap['attr']) ) {

			foreach ($wraps_fields as $wf) {

				$item_obj = array();

				if( empty($wf['id']) ) continue;

				if( strpos($wf['id'], 'css_') === false ){

					if( $wf['id'] == 'width_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]);

					if( !empty( $wf['responsive'] ) ) {
						if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['desktop'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']] = array( 'desktop' => $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']] );

						foreach( $this->devices as $device ) {
							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id'].'_'.$device] ) && isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]) && is_array($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id'].'_'.$device];
						}
					}

				}else{

					$item_obj['selector'] = $wf['selector'];
					$item_obj['style'] = $wf['style'];
					$item_obj['val'] = array();

					if( !empty($wf['old_id']) ) {



						$old_id_1 = $wf['old_id'];
						$old_id_2 = false;

						if( strpos($old_id_1, 'style:.mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner') !== false ) {
							$old_id_2 = str_replace('style:.mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner', 'style:.mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner', $old_id_1);
						}

						if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_1] ) ) {

							// if style: for dekstop
							if( !empty($wf['responsive']) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_1];
							}else{
								$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_1];
							}
							
							unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_1]);
							$this->detect_old_builder = true;
						
						}else if( !empty($old_id_2) && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_2] ) ) {

							// if style: for dekstop
							if( !empty($wf['responsive']) ) {
								$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_2];
							}else{
								$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_2];
							}
							
							unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$old_id_2]);
							$this->detect_old_builder = true;
							
						}

					}

					if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val'] ) ) {
						// if css_ for desktop with val
						if( !empty($wf['responsive']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val']['desktop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val']['laptop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val']['tablet']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val']['mobile']) ) {
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val'];
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val'];
						}
						
						unset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']]['val'] );
						$this->detect_old_builder = true;
					}

					if( !empty($wf['responsive']) ) {
						foreach( $this->devices as $device ) {

							if( isset($wf['old_id']) ) {
								$sa_device = $wf['old_id'] . '_' . $device;

								if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device] ) ) {
									if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device] ) ) {
										$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device];
									}
									unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device]);
									$this->detect_old_builder = true;
								} elseif( !empty($old_id_2) ) {
									$sa_device_2 = $old_id_2 . '_' . $device;

									if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_2] ) ) {
										if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_2] ) ) {
											$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_2];
										}
										unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_2]);
										$this->detect_old_builder = true;
									}
								}
							}

							$sa_device_n = $wf['id'] . '_' . $device;

							if(
								isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_n]['val'] )
								&& !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_n]['val'][$device] )
							) {
								if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_n]['val'] ) ) {
									$item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_n]['val'];
								}
								unset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$sa_device_n] );
								$this->detect_old_builder = true;
							}

						}
					}

					if( !empty( $item_obj['val'] ) ) {
						$this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['attr'][$wf['id']] = $item_obj;
						
					}

				}


			}

		}

		if( !empty($wrap['items']) ){

			foreach( $wrap['items'] as $i=>$item ) {
				$this->i = $i;
				$this->nested_item($item);
			}
		}

		$this->mfn_items_obj[] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw];

		
	}

	public function nested_item($item) {
		$mfn_fields = new Mfn_Builder_Fields();
		$items_fields = $mfn_fields->get_items();
		$items_advanced = $mfn_fields->get_advanced(true);

		if( !empty($item['type']) && !empty($items_fields[$item['type']]['attr']) && is_iterable($items_fields[$item['type']]['attr']) ) {

			if( isset( $items_fields[$item['type']]['attr'] ) && is_array($items_fields[$item['type']]['attr']) ){
				$items_fields[$item['type']]['attr'] = array_merge($items_fields[$item['type']]['attr'], $items_advanced);
			}else{
				$items_fields[$item['type']]['attr'] = $items_advanced;
			}

			if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['fields']) ) {
				$this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['fields'];
			}

			foreach ($items_fields[$item['type']]['attr'] as $it) {

				if( empty($it['id']) ) continue;

				if( $it['id'] == 'hotspots' && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] ) ) {

					$hot_array = array();

					foreach( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] as $h=>$hot ) {
						$hot_array[$h] = array();
						if( is_array($hot) ) {

							foreach( $hot as $k=>$ht ) {

								if( is_array($ht) ) {
									$hot_array[$h][$k] = array();
									foreach( $ht as $c=>$ss ) {

										$rwd = 'desktop';

										if( strpos($c, 'laptop') === false && strpos($c, 'tablet') === false && strpos($c, 'mobile') === false ) {
											$hot_array[$h][$k][$c] = $ss;
										}else{
											if( strpos($c, 'laptop') === false ){
												$rwd = 'laptop';
											}else if( strpos($c, 'tablet') === false ){
												$rwd = 'tablet';
											}else if( strpos($c, 'mobile') === false ){
												$rwd = 'mobile';
											}

											$c = str_replace('_laptop', '', $c);
											$c = str_replace('_tablet', '', $c);
											$c = str_replace('_mobile', '', $c);

											if( isset($hot_array[$h][$k][$c]['val']) && is_array($hot_array[$h][$k][$c]['val']) ){
												$hot_array[$h][$k][$c]['val'][$rwd] = $ss['val'];
											}
										}

										

										if( isset($hot_array[$h][$k][$c]['val']) && is_string($hot_array[$h][$k][$c]['val']) ) {
											$ss_val = $hot_array[$h][$k][$c]['val'];
											$hot_array[$h][$k][$c]['val'] = array();
											$hot_array[$h][$k][$c]['val']['desktop'] = $ss_val;
										}

										if( isset($hot_array[$h][$k][$c]['css_style']) ) {
											$hot_array[$h][$k][$c]['style'] = $hot_array[$h][$k][$c]['css_style'];
											unset($hot_array[$h][$k][$c]['css_style']);
										}
										if( isset($hot_array[$h][$k][$c]['css_path']) ) {
											$hot_array[$h][$k][$c]['selector'] = $hot_array[$h][$k][$c]['css_path'];
											unset($hot_array[$h][$k][$c]['css_path']);
										}
									}
								}else{
									$hot_array[$h][$k] = $ht;
								}
							}
						}else{
							$hot_array[$h] = $hot;
						}
					}

					$this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] = $hot_array;

				}elseif( strpos($it['id'], 'css_') === false ){

					if( $it['id'] == 'width_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]);

					if( !empty( $it['responsive'] ) ){
						if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['desktop'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] = array( 'desktop' => $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] );

						foreach( $this->devices as $device ) {
							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id'].'_'.$device] ) && isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]) && is_array($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id'].'_'.$device];
						}
					}

				}else{

					$item_obj = array();

					$item_obj['selector'] = $it['selector'];
					$item_obj['style'] = $it['style'];
					$item_obj['val'] = array();

					if( !empty($it['old_id']) && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['old_id']] ) ) {
						// if style: for dekstop
						
						if( !empty($it['responsive']) ){
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['old_id']];
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['old_id']];
						}

						if( $it['style'] == 'typography' ) {

							$obj_to_iterate = !empty($item_obj['val']['desktop']) ? $item_obj['val']['desktop'] : $item_obj['val'];

							if( isset($obj_to_iterate) && is_iterable($obj_to_iterate) ) {
								foreach ($obj_to_iterate as $k => $v) {
									if( strpos($k, '_laptop') !== false ) {
										$new_k = str_replace('_laptop', '', $k);
										$item_obj['val']['laptop'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_tablet') !== false ) {
										$new_k = str_replace('_tablet', '', $k);
										$item_obj['val']['tablet'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_mobile') !== false ) {
										$new_k = str_replace('_mobile', '', $k);
										$item_obj['val']['mobile'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}
								}
								if( empty($item_obj['val']) ) unset($item_obj['val']);
							}
						}

						$this->detect_old_builder = true;
					}

					if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val'] ) ) {
						// if css_ for desktop with val
						if( !empty($it['responsive']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val']['desktop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val']['laptop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val']['tablet']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val']['mobile']) ) {
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val'];
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val'];
						}
						
						unset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']]['val'] );
						$this->detect_old_builder = true;
					}
					
					if( !empty($it['responsive']) ) {
						
						foreach( $this->devices as $device ) {

							if( isset($it['old_id']) ) {
								$sa_device = $it['old_id'].'_'.$device;
								if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device] ) ) {
									// if style: below desktop
									$wf_device = $it['id'].'_'.$device;
									if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device] ) ) $item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device];
									unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device]);
									$this->detect_old_builder = true;
								}
							}

							$sa_device_n = $it['id'].'_'.$device;

							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device_n]['val'] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device_n]['val'][$device] ) ) {
								// if css_ below desktop
								if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device_n]['val'] ) ) $item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device_n]['val'];
								unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$sa_device_n]);
								$this->detect_old_builder = true;
							}
						}
					}
					if( !empty( $item_obj['val'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i]['attr'][$it['id']] = $item_obj;
				}
			}
		}

		$this->mfn_items_obj[] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->nw]['items'][$this->i];

	}



	public function item($item) {
		$mfn_fields = new Mfn_Builder_Fields();
		$items_fields = $mfn_fields->get_items();
		$items_advanced = $mfn_fields->get_advanced(true);

		if( !empty($item['type']) && !empty($items_fields[$item['type']]['attr']) && is_iterable($items_fields[$item['type']]['attr']) ) {

			if( isset( $items_fields[$item['type']]['attr'] ) && is_array($items_fields[$item['type']]['attr']) ) {
				$items_fields[$item['type']]['attr'] = array_merge($items_fields[$item['type']]['attr'], $items_advanced);
			}else{
				$items_fields[$item['type']]['attr'] = $items_advanced;
			}

			if( isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['fields']) ) {
				$this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['fields'];
				unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['fields']);
			}

			foreach ($items_fields[$item['type']]['attr'] as $i=>$it) {

				if( empty($it['id']) ) continue;

				if( $it['id'] == 'hotspots' && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] ) ) {

					$hot_array = array();

					foreach( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] as $h=>$hot ) {
						$hot_array[$h] = array();
						if( is_array($hot) ) {

							foreach( $hot as $k=>$ht ) {

								if( is_array($ht) ) {
									$hot_array[$h][$k] = array();
									foreach( $ht as $c=>$ss ) {

										$rwd = 'desktop';

										if( strpos($c, 'laptop') === false && strpos($c, 'tablet') === false && strpos($c, 'mobile') === false ) {
											$hot_array[$h][$k][$c] = $ss;
										}else{
											if( strpos($c, 'laptop') === false ){
												$rwd = 'laptop';
											}else if( strpos($c, 'tablet') === false ){
												$rwd = 'tablet';
											}else if( strpos($c, 'mobile') === false ){
												$rwd = 'mobile';
											}

											$c = str_replace('_laptop', '', $c);
											$c = str_replace('_tablet', '', $c);
											$c = str_replace('_mobile', '', $c);

											if( isset($hot_array[$h][$k][$c]['val']) && is_array($hot_array[$h][$k][$c]['val']) ){
												$hot_array[$h][$k][$c]['val'][$rwd] = $ss['val'];
											}
										}

										

										if( isset($hot_array[$h][$k][$c]['val']) && is_string($hot_array[$h][$k][$c]['val']) ) {
											$ss_val = $hot_array[$h][$k][$c]['val'];
											$hot_array[$h][$k][$c]['val'] = array();
											$hot_array[$h][$k][$c]['val']['desktop'] = $ss_val;
										}

										if( isset($hot_array[$h][$k][$c]['css_style']) ) {
											$hot_array[$h][$k][$c]['style'] = $hot_array[$h][$k][$c]['css_style'];
											unset($hot_array[$h][$k][$c]['css_style']);
										}
										if( isset($hot_array[$h][$k][$c]['css_path']) ) {
											$hot_array[$h][$k][$c]['selector'] = $hot_array[$h][$k][$c]['css_path'];
											unset($hot_array[$h][$k][$c]['css_path']);
										}
									}
								}else{
									$hot_array[$h][$k] = $ht;
								}
							}
						}else{
							$hot_array[$h] = $hot;
						}
					}

					$this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] = $hot_array;

				}elseif( strpos($it['id'], 'css_') === false ){

					if( $it['id'] == 'width_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]);
					if( $it['id'] == 'height_switcher' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] == 'default' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]);

					if( !empty( $it['responsive'] ) ){
						if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['desktop'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] = array( 'desktop' => $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] );

						foreach( $this->devices as $device ) {
							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id'].'_'.$device] ) && isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]) && is_array($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id'].'_'.$device];
						}
					}

				}else{
					
					if( $it['id'] == 'css_bg_img_pos' && !empty($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]) && $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] == 'center center' ) unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]);

					$item_obj = array();

					$item_obj['selector'] = $it['selector'];
					$item_obj['style'] = $it['style'];
					$item_obj['val'] = array();

					if( !empty($it['old_id']) && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id']] ) ) {
						// if style: for dekstop
						
						if( !empty($it['responsive']) ) {
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id']];
						}else {
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id']];
						}

						if( $it['style'] == 'typography' ) {

							$obj_to_iterate = !empty($item_obj['val']['desktop']) ? $item_obj['val']['desktop'] : $item_obj['val'];

							if( isset($obj_to_iterate) && is_iterable($obj_to_iterate) ) {
								
								foreach ($obj_to_iterate as $k => $v) {
									if( strpos($k, '_laptop') !== false ) {
										$new_k = str_replace('_laptop', '', $k);
										$item_obj['val']['laptop'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_tablet') !== false ) {
										$new_k = str_replace('_tablet', '', $k);
										$item_obj['val']['tablet'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_mobile') !== false ) {
										$new_k = str_replace('_mobile', '', $k);
										$item_obj['val']['mobile'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}
								}

								if( empty($item_obj['val']) ) unset($item_obj['val']);

							}

						}

						unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id']]);
						$this->detect_old_builder = true;

					}

					if( !empty($it['old_id2']) && isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id2']] ) ) {
						// for some deprecated ids
						
						if( !empty($it['responsive']) ){
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id2']];
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id2']];
						}
						unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['old_id2']]);
						$this->detect_old_builder = true;
					}

					if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val'] ) ) {
						// if css_ for desktop with val
						if( !empty($it['responsive']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val']['desktop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val']['laptop']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val']['tablet']) && !isset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val']['mobile']) ) {
							$item_obj['val']['desktop'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val'];
						}else{
							$item_obj['val'] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val'];
						}

						if( $it['style'] == 'typography' ) {

							$obj_to_iterate = !empty($item_obj['val']['desktop']) ? $item_obj['val']['desktop'] : $item_obj['val'];

							if( isset($obj_to_iterate) && is_iterable($obj_to_iterate) ) {
								
								foreach ($obj_to_iterate as $k => $v) {
									if( strpos($k, '_laptop') !== false ) {
										$new_k = str_replace('_laptop', '', $k);
										$item_obj['val']['laptop'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_tablet') !== false ) {
										$new_k = str_replace('_tablet', '', $k);
										$item_obj['val']['tablet'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}elseif( strpos($k, '_mobile') !== false ) {
										$new_k = str_replace('_mobile', '', $k);
										$item_obj['val']['mobile'][$new_k] = $v;
										if( isset($item_obj['val']['desktop'][$k]) ) unset($item_obj['val']['desktop'][$k]);
										unset( $item_obj['val'][$k] );
									}
								}

								if( empty($item_obj['val']) ) unset($item_obj['val']);

							}

						}
						
						unset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']]['val'] );
						$this->detect_old_builder = true;
					}
					
					if( !empty($it['responsive']) ) {
						
						foreach( $this->devices as $device ) {

							if( isset($it['old_id']) ) {
								$sa_device = $it['old_id'].'_'.$device;
								
								if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device] ) ) {
									// if style: below desktop
									$wf_device = $it['id'].'_'.$device;
									if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device] ) ) $item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device];
									unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device]);
									$this->detect_old_builder = true;
								}
							}

							$sa_device_n = $it['id'].'_'.$device;

							if( isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device_n]['val'] ) && !isset( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device_n]['val'][$device] ) ) {
								// if css_ below desktop
								if( !empty( $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device_n]['val'] ) ) $item_obj['val'][$device] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device_n]['val'];
								unset($this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$sa_device_n]);
								$this->detect_old_builder = true;
							}
							
						}

					}
					if( !empty( $item_obj['val'] ) ) $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i]['attr'][$it['id']] = $item_obj;		
				}
			}

		}

		$this->mfn_items_obj[] = $this->mfn_items[$this->s]['wraps'][$this->w]['items'][$this->i];

		

	}


	public function update() {

		if ( 'encode' == $this->builder_storage ) {
			$new = call_user_func('base'.'64_encode', serialize($this->mfn_items));
		}else{
			$new = $this->mfn_items;
		}

		update_post_meta($this->id, 'mfn-page-items', $new);
		update_option('mfn-css-db-update', '1');

		Mfn_Helper::preparePostUpdate( $this->mfn_items_obj, $this->id, 'mfn-page-local-style' );

	}


}
