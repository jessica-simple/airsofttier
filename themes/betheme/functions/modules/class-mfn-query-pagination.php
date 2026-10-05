<?php  
defined( 'ABSPATH' ) || exit;

class MfnQueryPagination {

	public $type = false;
	public $query = false;
	public $paged = false;
	public $args = false;
	public $obj = false;
	public $translate = [];

	public function __construct( $obj, $query = false ){

		global $wp_query;

		$this->obj = $obj;
		$this->type = $obj['attr']['query_post_pagination'];

		if( !method_exists( $this, $this->type ) ) return;

		$this->query = $query ? $query : $wp_query;

		$this->paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);

		$this->translate['prev'] = mfn_opts_get('translate') ? mfn_opts_get('translate-prev', '&lsaquo; Prev page') : __('Prev page', 'betheme');
		$this->translate['next'] = mfn_opts_get('translate') ? mfn_opts_get('translate-next', 'Next page &rsaquo;') : __('Next page', 'betheme');
		$this->translate['load-more'] = mfn_opts_get('translate') ? mfn_opts_get('translate-load-more', 'Load more') : __('Load more', 'betheme');

		$this->args = array(
            'current' => $this->paged,
            'total' => $this->query->max_num_pages ?? 0,
            'type' => 'list',
            'next_text' => esc_html($this->translate['next']) .'<i class="icon-right-open" aria-hidden="true"></i>',
            'prev_text' => '<i class="icon-left-open" aria-hidden="true"></i>' . esc_html($this->translate['prev']),
            'show_all' => true
        );

        if( $this->type == 'loadmore' || $this->type == 'infiniteload' ) $this->args['next_text'] = $this->translate['load-more'];
	}

	public function render() {

		$fun_name = $this->type;
		$classes = array('mfn-query-pagination', 'mfn-query-pagination-'.$this->type);

		if( $this->type == 'infiniteload' ) {
			$classes[] = 'mfn-query-pagination-loadmore';
		}

		echo '<div class="'.implode(' ', $classes).'">';
			$this->$fun_name();
		echo '</div>';
	}

	public function numbers() {
		echo paginate_links($this->args);
	}

	public function numbers_shortened() {
		$this->args['show_all'] = false;
		echo paginate_links($this->args);
	}

	public function dots() {
		echo paginate_links($this->args);
	}

	public function prevnext(){
		echo paginate_links($this->args);
	}

	public function loadmore(){
		echo paginate_links($this->args);
	}

	public function infiniteload(){
		wp_enqueue_script('mfn-waypoints', get_theme_file_uri('/js/plugins/waypoints.min.js'), ['jquery'], MFN_THEME_VERSION, true);
		echo paginate_links($this->args);
	}

	public function bebuilderHtml() {

		echo '<div class="mfn-query-pagination mfn-query-pagination-'.$this->type.'">';
			echo '<ul class="page-numbers">
				<li><a class="prev page-numbers" href="#">'.$this->args['prev_text'].'</a></li>
				<li><a class="page-numbers" href="#">1</a></li>
				<li><span aria-current="page" class="page-numbers current">2</span></li>
				<li><a class="page-numbers" href="#">3</a></li>
				<li><a class="next page-numbers" href="#">'.$this->args['next_text'].'</a></li>
			</ul>';
		echo '</div>';
	}

}

?>