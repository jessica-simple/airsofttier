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
		list( $sort, $order ) = self::get_sort( $_GET );

		$products = null;
		$brands = taxonomy_exists( 'product_brand' ) ? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) ) : array();
		$brand_filter = isset( $_GET['brand_filter'] ) && is_string( $_GET['brand_filter'] ) ? sanitize_key( wp_unslash( $_GET['brand_filter'] ) ) : '';
		$show_brand_filter = taxonomy_exists( 'product_brand' ) && ! is_wp_error( $brands );
		$has_filters = '' !== $search || ( $show_brand_filter && '' !== $brand_filter );
		if ( ( $category && ! is_wp_error( $category ) ) || ( ! $category_id && $has_filters ) ) {
			$tax_query = $category_id ? array(
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => $category_id,
					'include_children' => false,
				),
			) : array();
			if ( $show_brand_filter && 'none' === $brand_filter ) {
				$tax_query[] = array( 'taxonomy' => 'product_brand', 'operator' => 'NOT EXISTS' );
			} elseif ( $show_brand_filter && ctype_digit( $brand_filter ) && absint( $brand_filter ) > 0 ) {
				$tax_query[] = array(
					'taxonomy'         => 'product_brand',
					'field'            => 'term_id',
					'terms'            => absint( $brand_filter ),
					'include_children' => false,
				);
			}
			$products = new WP_Query();
			$sort_by_lookup = in_array( $sort, array( 'sku', 'price' ), true );
			$sort_clauses = static function ( $clauses, $query ) use ( $products, $sort, $order ) {
				if ( $query !== $products ) {
					return $clauses;
				}

				global $wpdb;
				$column = 'sku' === $sort ? 'sku' : ( 'ASC' === $order ? 'min_price' : 'max_price' );
				$clauses['join']   .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} AS pm_sort_lookup ON {$wpdb->posts}.ID = pm_sort_lookup.product_id";
				$clauses['orderby'] = "pm_sort_lookup.{$column} {$order}, {$wpdb->posts}.ID {$order}";
				return $clauses;
			};
			if ( $sort_by_lookup ) {
				add_filter( 'posts_clauses', $sort_clauses, 10, 2 );
			}
			$products->query(
				array(
					'post_type'      => 'product',
					'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
					'perm'           => 'editable',
					'posts_per_page' => self::PAGE_SIZE,
					'paged'          => $paged,
					's'              => $search,
					'tax_query'      => $tax_query,
					'orderby'        => array( 'date' === $sort ? 'date' : 'title' => $order, 'ID' => $order ),
				)
			);
			if ( $sort_by_lookup ) {
				remove_filter( 'posts_clauses', $sort_clauses, 10 );
			}
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Product Manager', 'product-manager' ); ?></h1>
			<p><?php esc_html_e( 'Remove products from a category while keeping the products and their other category assignments.', 'product-manager' ); ?></p>

			<?php self::render_notice(); ?>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $sort ); ?>">
				<input type="hidden" name="order" value="<?php echo esc_attr( $order ); ?>">
				<label for="product-manager-category"><strong><?php esc_html_e( 'Category', 'product-manager' ); ?></strong></label>
				<select id="product-manager-category" name="category_id">
					<option value=""><?php esc_html_e( 'All categories', 'product-manager' ); ?></option>
					<?php if ( ! is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $category_option ) : ?>
							<option value="<?php echo esc_attr( $category_option->term_id ); ?>" <?php selected( $category_id, $category_option->term_id ); ?>><?php echo esc_html( $category_option->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<?php if ( $show_brand_filter ) : ?>
					<label for="product-manager-brand"><strong><?php esc_html_e( 'Brand', 'product-manager' ); ?></strong></label>
					<select id="product-manager-brand" name="brand_filter">
						<option value=""><?php esc_html_e( 'All brands', 'product-manager' ); ?></option>
						<option value="none" <?php selected( $brand_filter, 'none' ); ?>><?php esc_html_e( 'No brand', 'product-manager' ); ?></option>
						<?php foreach ( $brands as $brand ) : ?>
							<option value="<?php echo esc_attr( $brand->term_id ); ?>" <?php selected( $brand_filter, $brand->term_id ); ?>><?php echo esc_html( $brand->name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<label for="product-manager-search" class="screen-reader-text"><?php esc_html_e( 'Search products', 'product-manager' ); ?></label>
				<input id="product-manager-search" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search products', 'product-manager' ); ?>">
				<?php submit_button( __( 'Show Products', 'product-manager' ), 'secondary', '', false ); ?>
			</form>

			<?php if ( $products instanceof WP_Query ) : ?>
				<h2><?php echo esc_html( $category_id ? sprintf( __( 'Products in “%s”', 'product-manager' ), $category->name ) : __( 'Products', 'product-manager' ) ); ?></h2>
				<?php if ( $products->have_posts() ) : ?>
					<?php self::render_pagination( $products, 'top' ); ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="product_manager_remove_from_category">
						<input type="hidden" name="category_id" value="<?php echo esc_attr( $category_id ); ?>">
						<input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>">
						<input type="hidden" name="brand_filter" value="<?php echo esc_attr( $brand_filter ); ?>">
						<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>">
						<input type="hidden" name="orderby" value="<?php echo esc_attr( $sort ); ?>">
						<input type="hidden" name="order" value="<?php echo esc_attr( $order ); ?>">
						<?php wp_nonce_field( self::NONCE_ACTION, '_product_manager_nonce' ); ?>
						<table class="wp-list-table widefat fixed striped posts">
							<thead><tr>
								<?php if ( $category_id ) : ?>
									<td class="check-column"><input type="checkbox" class="product-manager-select-all" aria-label="<?php esc_attr_e( 'Select all products on this page', 'product-manager' ); ?>"></td>
								<?php endif; ?>
								<th scope="col" class="column-thumb" style="width: 60px;"><?php esc_html_e( 'Image', 'product-manager' ); ?></th>
								<?php self::render_sort_header( 'title', __( 'Name', 'product-manager' ), $sort, $order, 'column-primary' ); ?>
								<?php self::render_sort_header( 'sku', __( 'SKU', 'product-manager' ), $sort, $order ); ?>
								<th scope="col"><?php esc_html_e( 'Stock', 'product-manager' ); ?></th>
								<?php self::render_sort_header( 'price', __( 'Price', 'product-manager' ), $sort, $order ); ?>
								<th scope="col"><?php echo esc_html( $category_id ? __( 'Other categories', 'product-manager' ) : __( 'Categories', 'product-manager' ) ); ?></th>
								<?php if ( $show_brand_filter ) : ?>
									<th scope="col"><?php esc_html_e( 'Brands', 'product-manager' ); ?></th>
								<?php endif; ?>
								<th scope="col"><?php esc_html_e( 'Tags', 'product-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Featured', 'product-manager' ); ?></th>
								<?php self::render_sort_header( 'date', __( 'Date', 'product-manager' ), $sort, $order ); ?>
								<?php if ( $category_id ) : ?>
									<th scope="col"><?php esc_html_e( 'Action', 'product-manager' ); ?></th>
								<?php endif; ?>
							</tr></thead>
							<tbody>
								<?php foreach ( $products->posts as $product_post ) : ?>
									<?php
									$product       = wc_get_product( $product_post->ID );
									$other_terms   = wp_get_object_terms( $product_post->ID, 'product_cat', array( 'exclude' => array( $category_id ) ) );
									$other_names   = ! is_wp_error( $other_terms ) ? wp_list_pluck( $other_terms, 'name' ) : array();
									?>
									<tr>
										<?php if ( $category_id ) : ?>
											<th scope="row" class="check-column"><input type="checkbox" name="product_ids[]" value="<?php echo esc_attr( $product_post->ID ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Select %s', 'product-manager' ), $product_post->post_title ) ); ?>"></th>
										<?php endif; ?>
										<td class="column-thumb"><?php echo $product ? wp_kses_post( $product->get_image( array( 48, 48 ) ) ) : ''; ?></td>
										<td class="column-primary">
											<strong>
												<?php if ( current_user_can( 'edit_post', $product_post->ID ) ) : ?>
													<a class="row-title" href="<?php echo esc_url( get_edit_post_link( $product_post->ID ) ); ?>"><?php echo esc_html( $product_post->post_title ); ?></a>
												<?php else : ?>
													<?php echo esc_html( $product_post->post_title ); ?>
												<?php endif; ?>
											</strong>
											<div class="post-state"><?php echo esc_html( get_post_status_object( $product_post->post_status )->label ); ?></div>
											<?php self::render_product_actions( $product_post ); ?>
										</td>
										<td><?php echo esc_html( $product ? $product->get_sku() : '' ); ?></td>
										<td>
											<?php if ( $product ) : ?>
												<?php
												$stock_status = wc_get_product_stock_status_options();
												echo esc_html( $stock_status[ $product->get_stock_status() ] ?? '' );
												if ( $product->managing_stock() ) {
													echo ' (' . esc_html( wc_stock_amount( $product->get_stock_quantity() ) ) . ')';
												}
												?>
											<?php endif; ?>
										</td>
										<td><?php echo $product ? wp_kses_post( $product->get_price_html() ) : ''; ?></td>
										<td><?php echo esc_html( $other_names ? implode( ', ', $other_names ) : __( 'None', 'product-manager' ) ); ?></td>
										<?php if ( $show_brand_filter ) : ?>
											<?php $brand_links = get_the_term_list( $product_post->ID, 'product_brand', '', ', ' ); ?>
											<td><?php echo wp_kses_post( $brand_links && ! is_wp_error( $brand_links ) ? $brand_links : '&mdash;' ); ?></td>
										<?php endif; ?>
										<td><?php echo wp_kses_post( wc_get_product_tag_list( $product_post->ID ) ?: '&mdash;' ); ?></td>
										<td><?php echo esc_html( $product && $product->is_featured() ? __( 'Yes', 'product-manager' ) : __( 'No', 'product-manager' ) ); ?></td>
										<td><?php echo esc_html( get_the_date( '', $product_post ) ); ?></td>
										<?php if ( $category_id ) : ?>
											<td><button type="submit" class="button" name="remove_product_id" value="<?php echo esc_attr( $product_post->ID ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Remove this product from the selected category?', 'product-manager' ) ); ?>');"><?php esc_html_e( 'Remove', 'product-manager' ); ?></button></td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php if ( $category_id ) : ?>
							<?php submit_button( __( 'Remove selected products from this category', 'product-manager' ), 'delete', 'submit', true ); ?>
						<?php endif; ?>
					</form>

					<?php self::render_pagination( $products, 'bottom' ); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No products match your filters.', 'product-manager' ); ?></p>
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

	private static function get_sort( $request ) {
		$sort = $request['orderby'] ?? '';
		if ( ! in_array( $sort, array( 'title', 'sku', 'price', 'date' ), true ) ) {
			$sort = 'title';
		}
		$order = $request['order'] ?? '';
		return array( $sort, is_string( $order ) && 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC' );
	}

	private static function render_sort_header( $column, $label, $sort, $order, $class = '' ) {
		$active = $column === $sort;
		$next   = $active ? ( 'ASC' === $order ? 'desc' : 'asc' ) : ( 'date' === $column ? 'desc' : 'asc' );
		$url    = add_query_arg(
			array( 'orderby' => $column, 'order' => $next ),
			remove_query_arg( array( 'paged', 'removed', 'skipped' ) )
		);
		$class .= $active ? ' sorted ' . strtolower( $order ) : ' sortable ' . $next;
		?>
		<th scope="col" class="<?php echo esc_attr( trim( $class ) ); ?>"<?php if ( $active ) : ?> aria-sort="<?php echo 'ASC' === $order ? 'ascending' : 'descending'; ?>"<?php endif; ?>>
			<a href="<?php echo esc_url( $url ); ?>">
				<span><?php echo esc_html( $label ); ?></span>
				<span class="sorting-indicators" aria-hidden="true"><span class="sorting-indicator asc"></span><span class="sorting-indicator desc"></span></span>
			</a>
		</th>
		<?php
	}

	private static function render_pagination( $products, $position ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

		$table = new class( array( 'screen' => get_current_screen() ) ) extends WP_List_Table {
			public function render_pagination( $position ) {
				$this->pagination( $position );
			}
		};
		$table->set_pagination_args(
			array(
				'total_items' => $products->found_posts,
				'total_pages' => $products->max_num_pages,
				'per_page'    => self::PAGE_SIZE,
			)
		);
		?>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
			<?php foreach ( array( 'category_id', 's', 'brand_filter', 'orderby', 'order' ) as $filter ) : ?>
				<?php if ( isset( $_GET[ $filter ] ) && is_string( $_GET[ $filter ] ) ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $filter ); ?>" value="<?php echo esc_attr( wp_unslash( $_GET[ $filter ] ) ); ?>">
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="tablenav <?php echo esc_attr( $position ); ?>">
				<?php $table->render_pagination( $position ); ?>
				<br class="clear">
			</div>
		</form>
		<?php
	}

	private static function render_product_actions( $product_post ) {
		$product_id = $product_post->ID;
		$actions    = array();
		$can_edit   = current_user_can( 'edit_post', $product_id );
		if ( $can_edit ) {
			$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . esc_html__( 'Edit', 'product-manager' ) . '</a>';
		}

		if ( EMPTY_TRASH_DAYS && current_user_can( 'delete_post', $product_id ) ) {
			$actions['trash'] = '<a class="submitdelete" href="' . esc_url( get_delete_post_link( $product_id ) ) . '">' . esc_html__( 'Trash', 'product-manager' ) . '</a>';
		}

		if ( is_post_status_viewable( $product_post->post_status ) ) {
			$actions['view'] = '<a href="' . esc_url( get_permalink( $product_post ) ) . '">' . esc_html__( 'View', 'product-manager' ) . '</a>';
		} elseif ( $can_edit && in_array( $product_post->post_status, array( 'draft', 'pending', 'future' ), true ) ) {
			$actions['view'] = '<a href="' . esc_url( get_preview_post_link( $product_post ) ) . '">' . esc_html__( 'Preview', 'product-manager' ) . '</a>';
		}

		// WooCommerce adds its Duplicate action through this hook.
		$actions = apply_filters( 'post_row_actions', $actions, $product_post );
		$links   = array();
		foreach ( $actions as $action => $link ) {
			$links[] = '<span class="' . esc_attr( $action ) . '">' . wp_kses_post( $link ) . '</span>';
		}
		if ( $links ) {
			echo '<div class="row-actions">' . implode( ' | ', $links ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each action is escaped above.
		}
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
		if ( isset( $_POST['brand_filter'] ) && is_string( $_POST['brand_filter'] ) ) {
			$redirect_args['brand_filter'] = sanitize_key( wp_unslash( $_POST['brand_filter'] ) );
		}
		list( $redirect_args['orderby'], $redirect_args['order'] ) = self::get_sort( $_POST );

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
