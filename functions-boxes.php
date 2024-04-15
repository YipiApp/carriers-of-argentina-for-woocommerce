<?php
/**
 * Functions for boxes.
 *
 * @package Kijam
 */

add_action(
	'init',
	function () {
		register_post_type(
			'kshipping-box',
			array(
				'labels'              => array(
					'name'           => __( 'Shipping Boxes', 'carriers-of-argentina-for-woocommerce' ),
					'singular_name'  => __( 'Shipping Box', 'carriers-of-argentina-for-woocommerce' ),
					'menu_name'      => _x( 'Shipping Boxes', 'admin menu', 'carriers-of-argentina-for-woocommerce' ),
					'name_admin_bar' => _x( 'Shipping Boxes', 'admin bar', 'carriers-of-argentina-for-woocommerce' ),
					'add_new'        => _x( 'Add Box', 'add new', 'carriers-of-argentina-for-woocommerce' ),
					'add_new_item'   => __( 'Add Box', 'carriers-of-argentina-for-woocommerce' ),
					'new_item'       => __( 'New Box', 'carriers-of-argentina-for-woocommerce' ),
					'edit_item'      => __( 'Edit Box', 'carriers-of-argentina-for-woocommerce' ),
					'view_item'      => __( 'View Box', 'carriers-of-argentina-for-woocommerce' ),
					'all_items'      => __( 'All Boxes', 'carriers-of-argentina-for-woocommerce' ),
					'search_items'   => __( 'Search Box', 'carriers-of-argentina-for-woocommerce' ),
					'not_found'      => __( 'No box found.', 'carriers-of-argentina-for-woocommerce' ),
				),
				'supports'            => array(
					'title',
				),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				'show_in_rest'        => true,

			)
		);
		$boxes                = kshipping_argentina_boxes();
		$is_already_installed = get_option( 'kshipping_boxes_installed', false );
		if ( ! count( $boxes ) ) {
			if ( $is_already_installed ) {
				return;
			}
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'kshipping-box',
					'post_title'  => '35x35x7cm - 1 Kg',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta(
					$post_id,
					'kshipping_sizes',
					array(
						'width'     => 35,
						'height'    => 35,
						'depth'     => 7,
						'maxWeight' => 1,
					)
				);
			}
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'kshipping-box',
					'post_title'  => '40x40x7cm - 5 Kg',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta(
					$post_id,
					'kshipping_sizes',
					array(
						'width'     => 40,
						'height'    => 40,
						'depth'     => 7,
						'maxWeight' => 5,
					)
				);
			}
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'kshipping-box',
					'post_title'  => '40x40x14cm - 10 Kg',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta(
					$post_id,
					'kshipping_sizes',
					array(
						'width'     => 40,
						'height'    => 40,
						'depth'     => 14,
						'maxWeight' => 10,
					)
				);
			}
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'kshipping-box',
					'post_title'  => '40x40x28cm - 15 Kg',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta(
					$post_id,
					'kshipping_sizes',
					array(
						'width'     => 40,
						'height'    => 40,
						'depth'     => 28,
						'maxWeight' => 15,
					)
				);
			}
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'kshipping-box',
					'post_title'  => '40x60x28cm - 30 Kg',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta(
					$post_id,
					'kshipping_sizes',
					array(
						'width'     => 40,
						'height'    => 60,
						'depth'     => 28,
						'maxWeight' => 30,
					)
				);
			}
			update_option( 'kshipping_boxes_installed', true );
		} elseif ( ! $is_already_installed ) {
			update_option( 'kshipping_boxes_installed', true );
		}
	},
	1
);
add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['kshipping_box'] ) || ! isset( $_POST['kshipping_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshipping_box_nonce'] ) ), 'kshipping_box_nonce' ) ) {
			return;
		}
		$values = $_POST;
		update_post_meta( $post_id, 'kshipping_sizes', $values['kshipping_box'] );
		return;
	}
);
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'kshipping-box',
			__( 'Dimensions', 'carriers-of-argentina-for-woocommerce' ),
			function () {
				global $post;
				$sizes = get_post_meta( $post->ID, 'kshipping_sizes', true );
				if ( ! $sizes ) {
					$sizes = array(
						'width'     => 60,
						'height'    => 60,
						'depth'     => 60,
						'maxWeight' => 30,
					);
				}
				?>
				<input type="hidden" name="kshipping_box_nonce" value="<?php echo esc_html( wp_create_nonce( 'kshipping_box_nonce' ) ); ?>">
				<table>
					<tr>
						<td><b><?php esc_html_e( 'Width (In CM)', 'carriers-of-argentina-for-woocommerce' ); ?>:</b></td>
						<td><input name="kshipping_box[width]" type="text" value="<?php echo esc_html( $sizes['width'] ); ?>"/></td></td>
					</tr>
					<tr>
						<td><b><?php esc_html_e( 'Height (In CM)', 'carriers-of-argentina-for-woocommerce' ); ?>:</b></td>
						<td><input name="kshipping_box[height]" type="text" value="<?php echo esc_html( $sizes['height'] ); ?>"/></td></td>
					</tr>
					<tr>
						<td><b><?php esc_html_e( 'Depth (In CM)', 'carriers-of-argentina-for-woocommerce' ); ?>:</b></td>
						<td><input name="kshipping_box[depth]" type="text" value="<?php echo esc_html( $sizes['depth'] ); ?>"/></td></td>
					</tr>
					<tr>
						<td><b><?php esc_html_e( 'Max weight (In KG)', 'carriers-of-argentina-for-woocommerce' ); ?>:</b></td>
						<td><input name="kshipping_box[maxWeight]" type="text" value="<?php echo esc_html( $sizes['maxWeight'] ); ?>"/></td></td>
					</tr>
				</table>
				<?php
			},
			'kshipping-box'
		);
	}
);

add_filter(
	'manage_edit-kshipping-box_columns',
	function ( $columns ) {
		$columns['dimensions'] = __( 'Dimensions (In CM)', 'carriers-of-argentina-for-woocommerce' );
		$columns['maxWeight']  = __( 'Max weight (In KG)', 'carriers-of-argentina-for-woocommerce' );
		return $columns;
	}
);

add_action(
	'manage_kshipping-box_posts_custom_column',
	function ( $column, $post_id ) {
		$sizes = get_post_meta( $post_id, 'kshipping_sizes', true );
		switch ( $column ) {
			case 'dimensions':
				echo esc_html( $sizes['width'] . ' x ' . $sizes['height'] . ' x ' . $sizes['depth'] );
				break;

			case 'maxWeight':
				echo esc_html( $sizes['maxWeight'] );
				break;

		}
	},
	10,
	2
);

/**
 * Retrieves and filters shipping boxes for Argentina.
 */
function kshipping_argentina_boxes() {
	$boxes  = get_posts(
		array(
			'post_type'   => 'kshipping-box',
			'post_status' => 'publish',
			'numberposts' => -1,
		)
	);
	$result = array();
	foreach ( $boxes as $post ) {
		$box = get_post_meta( $post->ID, 'kshipping_sizes', true );
		if ( $box && isset( $box['width'] ) && isset( $box['height'] ) && isset( $box['depth'] ) && isset( $box['maxWeight'] ) ) {
			if ( ! is_numeric( $box['width'] ) ) {
				continue;
			}
			if ( ! is_numeric( $box['height'] ) ) {
				continue;
			}
			if ( ! is_numeric( $box['depth'] ) ) {
				continue;
			}
			if ( ! is_numeric( $box['maxWeight'] ) ) {
				continue;
			}
			$result[] = $box;
		}
	}
	return $result;
}

// Add custom fields to the Shipping tab in the product
add_action(
	'woocommerce_product_options_shipping',
	function () {
		global $post;
		echo '<div class="options_group show_if_simple" id="package_data">';
		echo '<input type="hidden" name="kshipping_package_nonce" value="' . esc_html( wp_create_nonce( 'kshipping_package_nonce' ) ) . '">';
		woocommerce_wp_checkbox(
			array(
				'id'            => '_force_label',
				'label'         => __( 'Send with own label', 'carriers-of-argentina-for-woocommerce' ),
				'description'   => __( 'This product and its extra packages must have its own shipping label.', 'carriers-of-argentina-for-woocommerce' ),
				'desc_tip'      => true,
				'value'         => get_post_meta( $post->ID, '_force_label', true ),
				'wrapper_class' => 'show_if_simple',
				'style'         => 'margin-top:10px;',
			)
		);
		echo '<p class="form-field">
			<label for="_add_package">' . esc_html__( 'Extra Packages', 'carriers-of-argentina-for-woocommerce' ) . '</label>
			<button type="button" id="_add_package" class="button tagadd show_if_simple">' . esc_html__( 'Add Extra Package', 'carriers-of-argentina-for-woocommerce' ) . '</button>
		</p>';

		$package_data = get_post_meta( $post->ID, '_package_data', true );
		if ( $package_data ) {
			foreach ( array_keys( $package_data['width'] ) as $idx ) {
				echo '<div class="old_package">
					<p class="form-field">
						<p class="form-field _weight_field_package ">
							<label for="_weight">' . esc_html__( 'Weight', 'carriers-of-argentina-for-woocommerce' ) . '</label>
							<span class="woocommerce-help-tip" tabindex="0" aria-label="Peso en forma decimal"></span><input type="text" class="short wc_input_decimal" style="" name="_package_data[weight][]" value="' . esc_attr( $package_data['weight'][ $idx ] ) . '" placeholder="0">
						</p>
						<p class="form-field dimensions_field_package">
							<label for="product_length_package">' . esc_html__( 'Dimensions', 'carriers-of-argentina-for-woocommerce' ) . '</label>
							<span class="wrap">
								<input style="width: 25%;margin-right: 3.8%;" placeholder="Longitud" class="input-text wc_input_decimal"     size="6" type="text" name="_package_data[width][]"  value="' . esc_attr( $package_data['width'][ $idx ] ) . '" />
								<input style="width: 25%;margin-right: 3.8%;" placeholder="Ancho"    class="input-text wc_input_decimal"     size="6" type="text" name="_package_data[height][]" value="' . esc_attr( $package_data['height'][ $idx ] ) . '" />
								<input style="width: 25%;margin-right: 3.8%;" placeholder="Alto"    class="input-text wc_input_decimal last" size="6" type="text" name="_package_data[depth][]"  value="' . esc_attr( $package_data['depth'][ $idx ] ) . '" />
							</span>
						</p>
						<p class="form-field dimensions_field_package">
							<button class="remove_old_package button tagadd">' . esc_html( __( 'Remove Extra Package', 'carriers-of-argentina-for-woocommerce' ) ) . '</button>
						</p>
					</p>
				</div>';
			}
		}
		echo '</div>';
	}
);

// Save package data as product metadata.
add_action(
	'woocommerce_process_product_meta',
	function ( $post_id ) {
		if ( ! isset( $_POST['kshipping_package_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshipping_package_nonce'] ) ), 'kshipping_package_nonce' ) ) {
			return;
		}
		update_post_meta( $post_id, '_force_label', ( isset( $_POST['_force_label'] ) && 'yes' === $_POST['_force_label'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_package_data', $_POST['_package_data'] ?? array() );
	},
	10,
	1
);
