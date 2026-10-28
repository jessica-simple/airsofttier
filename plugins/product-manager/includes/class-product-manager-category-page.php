<?php
/**
 * Product category management screen.
 */

defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Category_Page {
	const PAGE_SLUG = 'product-manager-categories';
	const NONCE_ACTION = 'product_manager_remove_from_category';
	const PAGE_SIZE = 25;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_product_manager_remove_from_category', array( __CLASS__, 'remove_from_category' ) );
	}

	public static function register_menu() {
		add_menu_page(
			__( 'Product Manager', 'product-manager' ),
			__( 'Product Manager', 'product-manager' ),
			'edit_products',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-products',
			56
		);

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'Remove Products from Category', 'product-manager' ),
			__( 'Categories', 'product-manager' ),
			'edit_products',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	private static function can_manage_categories() {
		$taxonomy = get_taxonomy( 'product_cat' );
		return $taxonomy && current_user_can( $taxonomy->cap->assign_terms );
	}

	public static function render_page() {
		if ( ! current_user_can( 'edit_products' ) || ! self::can_manage_categories() ) {
			wp_die( esc_html__( 'You are not allowed to manage product categories.', 'product-manager' ) );
		}
		if ( ! function_exists( 'wc_get_product' ) ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Product Manager', 'product-manager' ) . '</h1><div class="notice notice-error"><p>' . esc_html__( 'Activate WooCommerce to manage products and categories.', 'product-manager' ) . '</p></div></div>';
			return;
		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		$category_id = isset( $_GET['category_id'] ) ? absint( wp_unslash( $_GET['category_id'] ) ) : 0;
		$search      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged       = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$category    = ! is_wp_error( $categories ) && $category_id ? get_term( $category_id, 'product_cat' ) : null;

		$products = null;
		if ( $category && ! is_wp_error( $category ) ) {
			$products = new WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
					'perm'           => 'editable',
					'posts_per_page' => self::PAGE_SIZE,
					'paged'          => $paged,
					's'              => $search,
					'tax_query'      => array(
						array(
							'taxonomy'         => 'product_cat',
							'field'            => 'term_id',
							'terms'            => $category_id,
							'include_children' => false,
						),
					),
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Product Manager', 'product-manager' ); ?></h1>
			<p><?php esc_html_e( 'Remove products from a category while keeping the products and their other category assignments.', 'product-manager' ); ?></p>

			<?php self::render_notice(); ?>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
				<label for="product-manager-category"><strong><?php esc_html_e( 'Category', 'product-manager' ); ?></strong></label>
				<select id="product-manager-category" name="category_id" required>
					<option value=""><?php esc_html_e( 'Select a category', 'product-manager' ); ?></option>
					<?php if ( ! is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $category_option ) : ?>
							<option value="<?php echo esc_attr( $category_option->term_id ); ?>" <?php selected( $category_id, $category_option->term_id ); ?>><?php echo esc_html( $category_option->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<label for="product-manager-search" class="screen-reader-text"><?php esc_html_e( 'Search products', 'product-manager' ); ?></label>
				<input id="product-manager-search" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search products', 'product-manager' ); ?>">
				<?php submit_button( __( 'Show Products', 'product-manager' ), 'secondary', '', false ); ?>
			</form>

			<?php if ( $category && ! is_wp_error( $category ) && $products instanceof WP_Query ) : ?>
				<h2><?php echo esc_html( sprintf( __( 'Products in “%s”', 'product-manager' ), $category->name ) ); ?></h2>
				<?php if ( $products->have_posts() ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="product_manager_remove_from_category">
						<input type="hidden" name="category_id" value="<?php echo esc_attr( $category_id ); ?>">
						<input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>">
						<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>">
						<?php wp_nonce_field( self::NONCE_ACTION, '_product_manager_nonce' ); ?>
						<table class="widefat striped">
							<thead><tr>
								<td class="check-column"><input type="checkbox" class="product-manager-select-all" aria-label="<?php esc_attr_e( 'Select all products on this page', 'product-manager' ); ?>"></td>
								<th scope="col"><?php esc_html_e( 'Product', 'product-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'SKU', 'product-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Other categories', 'product-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Action', 'product-manager' ); ?></th>
							</tr></thead>
							<tbody>
								<?php foreach ( $products->posts as $product_post ) : ?>
									<?php
									$product       = wc_get_product( $product_post->ID );
									$other_terms   = wp_get_object_terms( $product_post->ID, 'product_cat', array( 'exclude' => array( $category_id ) ) );
									$other_names   = ! is_wp_error( $other_terms ) ? wp_list_pluck( $other_terms, 'name' ) : array();
									?>
									<tr>
										<th scope="row" class="check-column"><input type="checkbox" name="product_ids[]" value="<?php echo esc_attr( $product_post->ID ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Select %s', 'product-manager' ), $product_post->post_title ) ); ?>"></th>
										<td><strong><a href="<?php echo esc_url( get_edit_post_link( $product_post->ID ) ); ?>"><?php echo esc_html( $product_post->post_title ); ?></a></strong><div class="row-actions"><?php echo esc_html( ucfirst( $product_post->post_status ) ); ?></div></td>
										<td><?php echo esc_html( $product ? $product->get_sku() : '' ); ?></td>
										<td><?php echo esc_html( $other_names ? implode( ', ', $other_names ) : __( 'None', 'product-manager' ) ); ?></td>
										<td><button type="submit" class="button" name="remove_product_id" value="<?php echo esc_attr( $product_post->ID ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Remove this product from the selected category?', 'product-manager' ) ); ?>');"><?php esc_html_e( 'Remove', 'product-manager' ); ?></button></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php submit_button( __( 'Remove selected products from this category', 'product-manager' ), 'delete', 'submit', true ); ?>
					</form>

					<?php if ( $products->max_num_pages > 1 ) : ?>
						<div class="tablenav"><div class="tablenav-pages"><?php
							echo wp_kses_post(
								paginate_links(
									array(
										'base'      => add_query_arg( array( 'page' => self::PAGE_SLUG, 'category_id' => $category_id, 's' => $search, 'paged' => '%#%' ), admin_url( 'admin.php' ) ),
										'format'    => '',
										'current'   => $paged,
										'total'     => $products->max_num_pages,
										'prev_text' => __( 'Previous', 'product-manager' ),
										'next_text' => __( 'Next', 'product-manager' ),
									)
								)
							);
						?></div></div>
					<?php endif; ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No products in this category match your search.', 'product-manager' ); ?></p>
				<?php endif; ?>
			<?php elseif ( $category_id ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Select a valid product category.', 'product-manager' ); ?></p></div>
			<?php endif; ?>
		</div>
		<script>
			(function () {
				var selectAll = document.querySelector('.product-manager-select-all');
				if (!selectAll) return;
				selectAll.addEventListener('change', function () {
					document.querySelectorAll('input[name="product_ids[]"]').forEach(function (checkbox) {
						checkbox.checked = selectAll.checked;
					});
				});
			}());
		</script>
		<?php
	}

	public static function remove_from_category() {
		if ( ! current_user_can( 'edit_products' ) || ! self::can_manage_categories() ) {
			wp_die( esc_html__( 'You are not allowed to manage product categories.', 'product-manager' ), 403 );
		}

		check_admin_referer( self::NONCE_ACTION, '_product_manager_nonce' );
		$category_id = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
		$category    = $category_id ? get_term( $category_id, 'product_cat' ) : null;
		if ( ! $category || is_wp_error( $category ) ) {
			wp_die( esc_html__( 'Select a valid product category.', 'product-manager' ), 400 );
		}

		$product_ids = array();
		if ( isset( $_POST['remove_product_id'] ) ) {
			$product_ids[] = absint( wp_unslash( $_POST['remove_product_id'] ) );
		} elseif ( isset( $_POST['product_ids'] ) && is_array( $_POST['product_ids'] ) ) {
			$product_ids = array_values( array_unique( array_filter( array_map( 'absint', wp_unslash( $_POST['product_ids'] ) ) ) ) );
		}

		$removed = 0;
		$skipped = 0;
		foreach ( $product_ids as $product_id ) {
			if ( 'product' !== get_post_type( $product_id ) || ! current_user_can( 'edit_post', $product_id ) ) {
				++$skipped;
				continue;
			}

			$result = wp_remove_object_terms( $product_id, $category_id, 'product_cat' );
			if ( is_wp_error( $result ) || ! $result ) {
				++$skipped;
				continue;
			}

			if ( function_exists( 'wc_delete_product_transients' ) ) {
				wc_delete_product_transients( $product_id );
			}
			clean_post_cache( $product_id );
			++$removed;
		}

		$redirect_args = array(
			'page'         => self::PAGE_SLUG,
			'category_id'  => $category_id,
			'removed'      => $removed,
			'skipped'      => $skipped,
		);
		if ( isset( $_POST['s'] ) && is_string( $_POST['s'] ) ) {
			$redirect_args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) );
		}
		if ( isset( $_POST['paged'] ) ) {
			$redirect_args['paged'] = max( 1, absint( wp_unslash( $_POST['paged'] ) ) );
		}

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function render_notice() {
		if ( ! isset( $_GET['removed'] ) ) {
			return;
		}

		$removed = absint( wp_unslash( $_GET['removed'] ) );
		$skipped = isset( $_GET['skipped'] ) ? absint( wp_unslash( $_GET['skipped'] ) ) : 0;
		$message = sprintf(
			_n( 'Removed %d product from the category.', 'Removed %d products from the category.', $removed, 'product-manager' ),
			$removed
		);
		if ( $skipped ) {
			$message .= ' ' . sprintf( __( '%d products were skipped because they could not be updated.', 'product-manager' ), $skipped );
		}

		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}
}
