<?php
/**
 * Functions for labels.
 *
 * @package Kijam
 */

add_action(
	'restrict_manage_posts',
	function( $post_type ) {
		if ( 'shop_order' === $post_type && is_admin() ) {
			?>
			<script>
				var kshippingargentina_metabox_loading = false;
				var kshippingargentina_metabox_nonce = '<?php echo esc_html( wp_create_nonce( 'kshippingargentina_massive_label_nonce' ) ); ?>';
				function generateLabelsShippingArgentina() {
					var $ = jQuery;
					var list_posts = [];
					$('.check-column input[type=checkbox]:checked').each(function() {
						list_posts.push($(this).attr('value'));
					});
					if (list_posts.length < 1) { alert('<?php echo esc_html_e( 'You must select at least one order', 'carriers-of-argentina-for-woocommerce' ); ?>'); return; }
					if (kshippingargentina_metabox_loading) return;
					kshippingargentina_metabox_loading = true;
					var url = 'edit.php?post_type=shop_order&generate_massive_tracking_code=1&';
					url += 'kca_posts='+list_posts.join(',')+'&';
					url += 'kshippingargentina_massive_label_nonce='+kshippingargentina_metabox_nonce;
					$('#kshippingargentinao_generate_label').html('<?php echo esc_html_e( 'Cargando...', 'carriers-of-argentina-for-woocommerce' ); ?>');
					$.get(url, function(list) {
						$('#kshippingargentinao_generate_label').html('<?php echo esc_html_e( 'Generate Label', 'carriers-of-argentina-for-woocommerce' ); ?>');
						kshippingargentina_metabox_loading = false;
						var list = jQuery.parseJSON(list);
						kshippingargentina_metabox_nonce = list.new_nonce;
						if (list.error)
							alert(list.error); // show response from the php script.
						else if (list.errors) {
							let msg = '';
							for(let i in list.errors) {
								msg += '#'+list.errors[i].order_id + ': ' + list.errors[i].msg + '\n';
							}
							if (msg)
								alert(msg); // show response from the php script.
						}
						if (typeof list.url_zip != 'undefined' && list.url_zip)
							document.location.href = list.url_zip;
					}).fail(function() {
						$('#kshippingargentinao_generate_label').html('<?php echo esc_html_e( 'Generate Label', 'carriers-of-argentina-for-woocommerce' ); ?>');
						kshippingargentina_metabox_loading = false;
						alert('<?php echo esc_html_e( 'Internal server error', 'carriers-of-argentina-for-woocommerce' ); ?>');
					});
				}
				var kca_wait_jQuery = setInterval(function(){
					if (typeof jQuery == 'undefined') return;
					var $ = jQuery;
					var form = $('#kca_form').clone();
					var parent = $('#kca_form').parent();
					$('#kca_form').remove();
					$(parent).append(form);
					clearInterval(kca_wait_jQuery);
				}, 100);
			</script>
			<div id="kca_form" style="float: right;border-left: 1px solid black;border-right: 1px solid black;margin: 0 1px;padding: 0 3px;">
				<button class="button action" id="kshippingargentinao_generate_label" type="button" onclick="generateLabelsShippingArgentina()"><?php echo esc_html_e( 'Generate Label', 'carriers-of-argentina-for-woocommerce' ); ?></button>
			</div> 
			<?php
		}
	},
	10,
	1
);

add_action(
	'init',
	function () {
		global $wp_filesystem;
		if ( ! isset( $_POST['kshippingargentina_is_order_save'] ) &&
			isset( $_POST['delete_label'] ) &&
			isset( $_POST['service_type'] ) &&
			isset( $_POST['kshippingargentina_delete_label_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshippingargentina_delete_label_nonce'] ) ), ( (int) $_POST['delete_label'] ) . '_kshippingargentina_delete_label_nonce' )
		) {
			$order = wc_get_order( (int) $_POST['delete_label'] );
			if ( ! $order || is_wp_error( $order ) ) {
				die(
					wp_json_encode(
						array(
							'ok'    => false,
							'error' => __( 'The order could not be found', 'carriers-of-argentina-for-woocommerce' ),
						)
					)
				);
			}
			$oca_tracking_reference = $order->get_meta( 'kshippingargentina_oca_tracking_reference' );
			if ( $oca_tracking_reference ) {
				$pdf_error = false;
				KShippingArgentina_API::cancel_oca_label( $oca_tracking_reference, $pdf_error );
				$order->delete_meta_data( 'kshippingargentina_oca_tracking_reference' );
				$order->delete_meta_data( 'kshippingargentina_oca_operation_code' );
			}
			$order->delete_meta_data( 'kshippingargentina_label_file' );
			$order->save();
			die(
				wp_json_encode(
					array(
						'ok' => true,
					)
				)
			);
		}
		if ( ! isset( $_POST['kshippingargentina_is_order_save'] ) &&
			isset( $_POST['save_tracking_code'] ) &&
			isset( $_POST['tracking_code'] ) &&
			isset( $_POST['instance_id'] ) &&
			isset( $_POST['kshippingargentina_tracking_code_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshippingargentina_tracking_code_nonce'] ) ), ( (int) $_POST['save_tracking_code'] ) . '_kshippingargentina_tracking_code_nonce' )
		) {
			$shipping = WC_KShippingArgentina_Shipping::get_instance( (int) $_POST['instance_id'] );

			$tracking_codes = array_filter(
				array_map(
					function( $tc ) {
						return trim( $tc );
					},
					explode( ',', sanitize_text_field( wp_unslash( $_POST['tracking_code'] ) ) )
				)
			);

			if ( ! count( $tracking_codes ) ) {
				die(
					wp_json_encode(
						array(
							'ok'    => false,
							'error' => __( 'Invalid tracking codes', 'carriers-of-argentina-for-woocommerce' ),
						)
					)
				);
			}

			$order_id = (int) $_POST['save_tracking_code'];
			$order    = wc_get_order( $order_id );
			$labels   = $order->get_meta( 'kshippingargentina_label_file', true );
			if ( $labels && is_array( $labels ) && count( $labels ) > 0 ) {
				if ( ! isset( $labels['no_tracking_code'] ) ) {
					die(
						wp_json_encode(
							array(
								'ok'    => false,
								'error' => __( 'This order already has tracking codes assigned', 'carriers-of-argentina-for-woocommerce' ),
							)
						)
					);
				}
				$label      = $labels['no_tracking_code'];
				$new_labels = array();
				foreach ( $tracking_codes as $tc ) {
					$new_labels[ $tc ] = $label;
				}
				$order->update_meta_data( 'kshippingargentina_label_file', $new_labels );
				$order->save();
				kshipping_notify_new_tracking( $order, $shipping );
				die(
					wp_json_encode(
						array(
							'ok' => true,
						)
					)
				);
			}
			foreach ( $tracking_codes as $tc ) {
				$api_error = false;
				if ( 'correo_argentino' === $shipping->service_type ) {
					$new_labels[ $tc ] = false;
				} elseif ( 'andreani' === $shipping->service_type ) {
					$pdf               = KShippingArgentina_API::get_andreani_pdf_label( $tc, $api_error );
					$new_labels[ $tc ] = $pdf ? kshipping_save_pdf(
						$order_id,
						"andreani_{$order_id}_{$tc}.pdf",
						$pdf
					) : false;
				} elseif ( 'oca' === $shipping->service_type ) {
					$pdf               = KShippingArgentina_API::get_oca_pdf_label( $tc, $api_error );
					$new_labels[ $tc ] = $pdf ? kshipping_save_pdf(
						$order_id,
						"oca_{$order_id}_{$tc}.pdf",
						$pdf
					) : false;
				}
			}
			if ( count( $new_labels ) ) {
				$order = wc_get_order( $order_id );
				$order->update_meta_data( 'kshippingargentina_label_file', $new_labels );
				$order->save();
				kshipping_notify_new_tracking( $order, $shipping );
				die(
					wp_json_encode(
						array(
							'ok' => true,
						)
					)
				);
			}
			die(
				wp_json_encode(
					array(
						'ok'    => false,
						'error' => __( 'Invalid service type.', 'carriers-of-argentina-for-woocommerce' ),
					)
				)
			);
		}
		if ( ! isset( $_POST['kshippingargentina_is_order_save'] ) &&
			isset( $_POST['kshippingargentina_order_id'] ) &&
			isset( $_POST['kshippingargentina_instance_id'] ) &&
			isset( $_POST['kshippingargentina_generate_label_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshippingargentina_generate_label_nonce'] ) ), ( (int) $_POST['kshippingargentina_order_id'] ) . '_kshippingargentina_generate_label_nonce' )
		) {
			$data = $_POST;
			KShippingArgentina_API::debug( 'New request for Label generator', $data );
			$label    = $data['kshipping'];
			$order    = wc_get_order( (int) $_POST['kshippingargentina_order_id'] );
			$shipping = WC_KShippingArgentina_Shipping::get_instance( (int) $_POST['kshippingargentina_instance_id'] );
			$order->update_meta_data( 'kshippingargentina_label_data', $label );
			$order->save();
			$file = false;
			KShippingArgentina_API::debug(
				'Massive Label',
				array(
					'service_type' => $shipping->service_type,
					'label'        => $label,
					'shipping'     => $shipping,
				)
			);
			if ( 'correo_argentino' === $shipping->service_type ) {
				$file = kshipping_generate_label_correo_argentino( $order, $label, $shipping );
			} elseif ( 'andreani' === $shipping->service_type ) {
				$file = kshipping_generate_label_andreani( $order, $label, $shipping );
			} elseif ( 'oca' === $shipping->service_type ) {
				$file = kshipping_generate_label_oca( $order, $label, $shipping );
			}
			if ( $file && isset( $file['error'] ) && ! $file['error'] ) {
				$order->update_meta_data( 'kshippingargentina_label_file', $file['tracking_code'] );
				$order->save();
				kshipping_notify_new_tracking( $order, $shipping );
				die(
					wp_json_encode(
						array(
							'ok'    => $file,
							'error' => false,
							'data'  => $label,
						)
					)
				);
			}
			die(
				wp_json_encode(
					array(
						'ok'    => false,
						'error' => $file && isset( $file['error'] ) && $file['error'] ? $file['error'] : __( 'The label for this order could not be generated, you can verify what happens from the plugin log if you have it active in the configuration.', 'carriers-of-argentina-for-woocommerce' ),
						'data'  => $label,
					)
				)
			);
		}

		if ( is_admin() &&
			isset( $_GET['generate_massive_tracking_code'] ) &&
			isset( $_GET['kca_posts'] ) &&
			isset( $_GET['kshippingargentina_massive_label_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['kshippingargentina_massive_label_nonce'] ) ), 'kshippingargentina_massive_label_nonce' )
		) {
			$posts = array_map(
				function( $v ) {
					return (int) $v;
				},
				array_filter( explode( ',', sanitize_text_field( wp_unslash( $_GET['kca_posts'] ) ) ) )
			);
			if ( count( $posts ) ) {
				$posts_correo   = array();
				$posts_andreani = array();
				$posts_oca      = array();
				$errors         = array();
				foreach ( $posts as $order_id ) {
					$order = new WC_Order( $order_id );

					$status = $order->get_status();
					if ( in_array( $status, array( 'pending', 'on-hold', 'cancelled', 'refunded', 'failed' ), true ) ) {
						$errors[] = array(
							'msg'      => __( 'Invalid Status:', 'carriers-of-argentina-for-woocommerce' ) . ' ' . $status,
							'order_id' => $order_id,
						);
						continue;
					}

					$shipping    = null;
					$instance_id = $order->get_meta( 'kshippingargentina_instance_id' );
					if ( $instance_id ) {
						$shipping = WC_KShippingArgentina_Shipping::get_instance( $instance_id );
					}
					if ( ! $shipping || ! isset( $shipping->service_type ) || empty( $shipping->service_type ) ) {
						$errors[] = array(
							'msg'      => __( 'Shipping instance not exists.', 'carriers-of-argentina-for-woocommerce' ),
							'order_id' => $order_id,
						);
						continue;
					}
					if ( 'correo_argentino' === $shipping->service_type ) {
						$posts_correo[] = array(
							'shipping' => $shipping,
							'order'    => $order,
						);
					} elseif ( 'oca' === $shipping->service_type ) {
						$posts_oca[] = array(
							'shipping' => $shipping,
							'order'    => $order,
						);
					} elseif ( 'andreani' === $shipping->service_type ) {
						$posts_andreani[] = array(
							'shipping' => $shipping,
							'order'    => $order,
						);
					} else {
						$errors[] = array(
							'msg'      => __( 'Shipping type not found:', 'carriers-of-argentina-for-woocommerce' ) . ' ' . $shipping->service_type,
							'order_id' => $order_id,
						);
						continue;
					}
				}
				if ( count( $errors ) > 0 ) {
					die(
						wp_json_encode(
							array(
								'error'     => false,
								'errors'    => $errors,
								'new_nonce' => wp_create_nonce( 'kshippingargentina_massive_label_nonce' ),
							)
						)
					);
				} else {
					$files  = array();
					$errors = array();
					foreach ( $posts_correo as &$to_label ) {
						$tracking_codes = get_post_meta( $to_label['order']->get_id(), 'kshippingargentina_label_file', true );
						if ( $tracking_codes && is_array( $tracking_codes ) && count( $tracking_codes ) ) {
							foreach ( $tracking_codes as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $tracking_codes;
							continue;
						}
						$label = kshippingargentina_order_to_label_data( $to_label['order'], $to_label['shipping'] );
						KShippingArgentina_API::debug(
							'New Label',
							array(
								'service_type' => 'correo_argentino',
								'label'        => $label,
								'shipping'     => $to_label['shipping'],
							)
						);
						$file = kshipping_generate_label_correo_argentino( $to_label['order'], $label, $to_label['shipping'] );
						if ( ! $file['error'] ) {
							$to_label['order']->update_meta_data( 'kshippingargentina_label_file', $file['tracking_code'] );
							$to_label['order']->save();
							kshipping_notify_new_tracking( $to_label['order'], $to_label['shipping'] );
							foreach ( $file['tracking_code'] as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $file['tracking_code'];
						} else {
							$errors[] = array(
								'msg'      => $file['error'],
								'order_id' => $to_label['order']->get_id(),
							);
						}
					}
					foreach ( $posts_oca as &$to_label ) {
						$tracking_codes = get_post_meta( $to_label['order']->get_id(), 'kshippingargentina_label_file', true );
						if ( $tracking_codes && is_array( $tracking_codes ) && count( $tracking_codes ) ) {
							foreach ( $tracking_codes as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $tracking_codes;
							continue;
						}
						$label = kshippingargentina_order_to_label_data( $to_label['order'], $to_label['shipping'] );
						KShippingArgentina_API::debug(
							'New Label',
							array(
								'service_type' => 'oca',
								'label'        => $label,
								'shipping'     => $to_label['shipping'],
							)
						);
						$file = kshipping_generate_label_oca( $to_label['order'], $label, $to_label['shipping'] );
						if ( ! $file['error'] ) {
							$to_label['order']->update_meta_data( 'kshippingargentina_label_file', $file['tracking_code'] );
							$to_label['order']->save();
							kshipping_notify_new_tracking( $to_label['order'], $to_label['shipping'] );
							foreach ( $file['tracking_code'] as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $file['tracking_code'];
						} else {
							$errors[] = array(
								'msg'      => $file['error'],
								'order_id' => $to_label['order']->get_id(),
							);
						}
					}
					foreach ( $posts_andreani as &$to_label ) {
						$tracking_codes = get_post_meta( $to_label['order']->get_id(), 'kshippingargentina_label_file', true );
						if ( $tracking_codes && is_array( $tracking_codes ) && count( $tracking_codes ) ) {
							foreach ( $tracking_codes as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $tracking_codes;
							continue;
						}
						$label = kshippingargentina_order_to_label_data( $to_label['order'], $to_label['shipping'] );
						KShippingArgentina_API::debug(
							'New Label',
							array(
								'service_type' => 'andreani',
								'label'        => $label,
								'shipping'     => $to_label['shipping'],
							)
						);
						$file = kshipping_generate_label_andreani( $to_label['order'], $label, $to_label['shipping'] );
						if ( ! $file['error'] ) {
							$to_label['order']->update_meta_data( 'kshippingargentina_label_file', $file['tracking_code'] );
							$to_label['order']->save();
							kshipping_notify_new_tracking( $to_label['order'], $to_label['shipping'] );
							foreach ( $file['tracking_code'] as $tc => $data ) {
								if ( ! $data ) {
									$errors[] = array(
										// translators: %s => Tracking code.
										'msg'      => sprintf( __( 'Label %s generated, but the pdf was impossible to get because of an error in the service provider.' ), $tc ),
										'order_id' => $to_label['order']->get_id(),
									);
									continue 2;
								}
							}
							$files[] = $file['tracking_code'];
						} else {
							$errors[] = array(
								'msg'      => $file['error'],
								'order_id' => $to_label['order']->get_id(),
							);
						}
					}
					$url_zip = false;
					if ( count( $files ) ) {
						// Initialize the WP filesystem.
						if ( ! $wp_filesystem ) {
							require_once ABSPATH . '/wp-admin/includes/file.php';
							WP_Filesystem();
						}
						$upload_dir = wp_upload_dir();
						$base_dir   = $upload_dir['basedir'];
						$final_path = '/kshipping_argentina';
						if ( ! is_dir( $base_dir . $final_path ) ) {
							mkdir( $base_dir . $final_path );
						}
						$final_path .= '/massive';
						if ( ! is_dir( $base_dir . $final_path ) ) {
							mkdir( $base_dir . $final_path );
						}
						if ( ! file_exists( $base_dir . $final_path . '/index.php' ) ) {
							$wp_filesystem->put_contents( $base_dir . $final_path . '/index.php', '' );
						}
						if ( class_exists( 'ZipArchive' ) ) {
							$zip       = new ZipArchive();
							$file_name = 'labels-' . time() . '.zip';
							if ( $zip->open( $base_dir . $final_path . '/' . $file_name, ZipArchive::CREATE ) === true ) {
								foreach ( $files as $file ) {
									foreach ( $file as $data ) {
										$zip->addFile( $data['file_path'], $data['file_name'] );
									}
								}
								$zip->close();
								$url_zip = $upload_dir['baseurl'] . $final_path . '/' . $file_name;
							}
						}
						if ( ! $url_zip && class_exists( 'PharData' ) ) {
							$file_name = 'labels-' . time() . '.tar';
							$tar       = new PharData( $base_dir . $final_path . '/' . $file_name );
							foreach ( $files as $file ) {
								foreach ( $file as $data ) {
									$tar->addFile( $data['file_path'], $data['file_name'] );
								}
							}
							$url_zip = $upload_dir['baseurl'] . $final_path . '/' . $file_name;
						}
					}
					die(
						wp_json_encode(
							array(
								'errors'    => $errors,
								'files'     => $files,
								'url_zip'   => $url_zip,
								'new_nonce' => wp_create_nonce( 'kshippingargentina_massive_label_nonce' ),
							)
						)
					);
				}
			}
			die(
				wp_json_encode(
					array(
						'error'     => 'No order found with Correo Argentina/Andreani/OCA in the given range.',
						'new_nonce' => wp_create_nonce( 'kshippingargentina_massive_label_nonce' ),
						'args'      => $args,
						'count'     => count( $posts ),
					)
				)
			);
		}
	},
	100000
);


/**
 * Show metabox in order.
 *
 * @param int|WC_Order $order Order Object or Order ID.
 * @param bool         $is_dokan Detect dokan case.
 */
function kshippingargentina_metabox_cb( $order = false, $is_dokan = false ) {
	global $theorder;
	if ( ! $is_dokan || ! $order ) {
		$order = $theorder;
	}
	$order_id = 0;
	if ( is_object( $order ) ) {
		if ( method_exists( $order, 'get_id' ) ) {
			$order_id = (int) $order->get_id();
		} else {
			$order_id = (int) $order->ID;
			$order    = wc_get_order( $order_id );
		}
	} elseif ( is_numeric( $order ) ) {
		$order_id = (int) $order;
		$order    = wc_get_order( $order_id );
	}
	if ( ! (int) $order_id ) {
		esc_html_e( 'The client asked that it not be sent by an Argentine carrier.', 'carriers-of-argentina-for-woocommerce' );
		return;
	}

	$shipping    = null;
	$instance_id = $order->get_meta( 'kshippingargentina_instance_id' );
	if ( $instance_id ) {
		$shipping = WC_KShippingArgentina_Shipping::get_instance( $instance_id );
	}
	if ( ! $shipping || ! isset( $shipping->service_type ) || empty( $shipping->service_type ) ) {
		esc_html_e( 'This carrier not is supported.', 'carriers-of-argentina-for-woocommerce' );
		return;
	}

	$status = $order->get_status();
	if ( in_array( $status, array( 'pending', 'on-hold', 'cancelled', 'refunded', 'failed' ), true ) ) {
		esc_html_e( 'Invalid status, payment is not completed.', 'carriers-of-argentina-for-woocommerce' );
		return;
	}

	$tracking_code = get_post_meta( $order_id, 'kshippingargentina_tracking_code', true );
	if ( $tracking_code && ! empty( $tracking_code ) ) {
		esc_html_e( 'Current tracking code:', 'carriers-of-argentina-for-woocommerce' ) . ': ' . $tracking_code;
		return;
	}

	$setting = get_option( 'woocommerce_kshippingargentina-manager_settings' );

	$vars             = kshippingargentina_order_to_label_data( $order, $shipping );
	$vars['order']    = $order;
	$vars['order_id'] = $order_id;
	$vars['shipping'] = $shipping;
	$vars['setting']  = $setting;
	wc_get_template(
		'order.tpl.php',
		$vars,
		'',
		plugin_dir_path( __FILE__ ) . 'templates/'
	);
}

add_action(
	'add_meta_boxes',
	function () {
		$screen = class_exists( 'CustomOrdersTableController' ) && function_exists( 'wc_get_container' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		add_meta_box( 'kshippingargentina-metabox', __( 'Data of the Argentine carrier', 'carriers-of-argentina-for-woocommerce' ), 'kshippingargentina_metabox_cb', $screen, 'normal', 'high' );
	}
);

add_action(
	'dokan_order_detail_after_order_items',
	function ( $order ) {
		if ( $order ) {
			?>
		<div class="" style="width:100%">
				<div class="dokan-panel dokan-panel-default">
						<div class="dokan-panel-heading"><strong><?php esc_html_e( 'Datos Correo Argentino', 'carriers-of-argentina-for-woocommerce' ); ?></div>
					<div class="dokan-panel-body" id="kshippingargentina-metabox">
							<?php
							kshippingargentina_metabox_cb( $order, true );
							?>
					</div>
				</div>
		</div>
			<?php
		}
	}
);

/**
 * Create Label data.
 *
 * @param int|WC_Order                   $order Order Object.
 * @param WC_KShippingArgentina_Shipping $shipping Shipping Object.
 */
function kshippingargentina_order_to_label_data( $order, $shipping ) {
	$setting          = get_option( 'woocommerce_kshippingargentina-manager_settings' );
	$billing_address  = $order->get_address( 'billing' );
	$shipping_address = $order->get_address( 'shipping' );
	$other_phone      = $billing_address['phone'];
	$email            = $billing_address['email'];
	$prefix_phone     = '';
	$phone            = '';
	$vat_type         = 'DNI';
	$vat              = '';

	if ( isset( $setting['meta_phone'] ) && ! empty( $setting['meta_phone'] ) ) {
		$phone = $order->get_meta( $setting['meta_phone'] );
		if ( ! $phone ) {
			$phone = $order->get_meta( '_' . $setting['meta_phone'] );
		}
	}

	if ( empty( $phone ) ) {
		$prefix_phone = $order->get_meta( '_billing_kphone_prefix' );
		$phone        = $order->get_meta( '_billing_kphone' );
	}

	if ( empty( $phone ) ) {
		$phone = $other_phone;
	}

	if ( isset( $setting['meta_dni'] ) && ! empty( $setting['meta_dni'] ) ) {
		$vat_type = 'DNI';
		$vat      = $order->get_meta( $setting['meta_dni'] );
		if ( ! $vat ) {
			$vat = $order->get_meta( '_' . $setting['meta_dni'] );
		}
	}
	if ( empty( $vat ) ) {
		$vat_type = $order->get_meta( '_billing_vat_type' );
		$vat      = $order->get_meta( '_billing_vat' );
	}

	$postcode  = $shipping_address['postcode'];
	$number    = $order->get_meta( '_shipping_number' );
	$floor     = $order->get_meta( '_shipping_floor' );
	$apartment = $order->get_meta( '_shipping_apartment' );
	if ( empty( $number ) && isset( $setting['meta_number_shipping'] ) && ! empty( $setting['meta_number_shipping'] ) ) {
		$number = $order->get_meta( $setting['meta_number_shipping'] );
		if ( ! $number ) {
			$number = $order->get_meta( '_' . $setting['meta_number_shipping'] );
		}
	}
	if ( empty( $apartment ) && isset( $setting['meta_apartment_shipping'] ) && ! empty( $setting['meta_apartment_shipping'] ) ) {
		$apartment = $order->get_meta( $setting['meta_apartment_shipping'] );
		if ( ! $apartment ) {
			$apartment = $order->get_meta( '_' . $setting['meta_apartment_shipping'] );
		}
	}
	if ( empty( $floor ) && isset( $setting['meta_floor_shipping'] ) && ! empty( $setting['meta_floor_shipping'] ) ) {
		$floor = $order->get_meta( $setting['meta_floor_shipping'] );
		if ( ! $floor ) {
			$floor = $order->get_meta( '_' . $setting['meta_floor_shipping'] );
		}
	}
	if ( empty( $postcode ) || empty( $number ) ) {
		$shipping_address = $billing_address;
		$postcode         = $billing_address['postcode'];
		$number           = $order->get_meta( '_billing_number' );
		$floor            = $order->get_meta( '_billing_floor' );
		$apartment        = $order->get_meta( '_billing_apartment' );
		if ( empty( $number ) && isset( $setting['meta_number'] ) && ! empty( $setting['meta_number'] ) ) {
			$number = $order->get_meta( $setting['meta_number'] );
			if ( ! $number ) {
				$number = $order->get_meta( '_' . $setting['meta_number'] );
			}
		}
		if ( empty( $apartment ) && isset( $setting['meta_apartment'] ) && ! empty( $setting['meta_apartment'] ) ) {
			$apartment = $order->get_meta( $setting['meta_apartment'] );
			if ( ! $apartment ) {
				$apartment = $order->get_meta( '_' . $setting['meta_apartment'] );
			}
		}
		if ( empty( $floor ) && isset( $setting['meta_floor'] ) && ! empty( $setting['meta_floor'] ) ) {
			$floor = $order->get_meta( $setting['meta_floor'] );
			if ( ! $floor ) {
				$floor = $order->get_meta( '_' . $setting['meta_floor'] );
			}
		}
	}

	$state          = $shipping_address['state'];
	$city           = $shipping_address['city'];
	$address_1      = $shipping_address['address_1'];
	$address_2      = $shipping_address['address_2'];
	$first_name     = $shipping_address['first_name'];
	$last_name      = $shipping_address['last_name'];
	$iso_office_src = explode( '#', $shipping->office_src )[0];

	$packages           = array();
	$exclude_products   = $shipping->exclude_products;
	$exclude_categories = $shipping->exclude_categories;
	foreach ( $order->get_items() as $item_id => $item ) {
		$product_id   = $item->get_product_id();
		$variation_id = $item->get_variation_id();
		$product      = $item->get_product();
		$product_name = $item->get_name();
		$quantity     = $item->get_quantity();
		$subtotal     = $item->get_subtotal();
		$total        = $item->get_total();
		$p_tax        = $item->get_subtotal_tax();
		if ( ! $product->needs_shipping() ) {
			continue;
		}
		$r               = array();
		$r['product_id'] = (int) $product_id;
		if ( in_array( (int) $product_id, $exclude_products, true ) ) {
			return;// Este producto esta excluido para ser usado.
		}
		$cats = get_the_terms( $product_id, 'product_cat' );
		if ( $cats && is_array( $cats ) && count( $cats ) > 0 ) {
			foreach ( $cats as $c_term ) {
				if ( in_array( (int) $c_term->term_id, $exclude_categories, true ) ) {
					return;// Esta categoria esta excluida para ser usado.
				}
			}
		}
		$author_id              = get_post_field( 'post_author', $product_id );
		$content_desc[]         = $product_name;
		$r['variation_id']      = (int) $variation_id;
		$r['name']              = $product_name;
		$r['quantity']          = $quantity;
		$r['line_total']        = $total;
		$r['line_tax']          = $p_tax;
		$r['line_subtotal']     = $subtotal;
		$r['line_subtotal_tax'] = $p_tax;
		if ( isset( $r['variation_id'] ) && $r['variation_id'] ) {
			$r['weight'] = (float) get_metadata( 'post', $r['variation_id'], '_weight', true );
			$r['length'] = (float) get_metadata( 'post', $r['variation_id'], '_length', true );
			$r['width']  = (float) get_metadata( 'post', $r['variation_id'], '_width', true );
			$r['height'] = (float) get_metadata( 'post', $r['variation_id'], '_height', true );
			if ( $r['weight'] < 0.001 ) {
				$r['weight'] = (float) get_metadata( 'post', $r['product_id'], '_weight', true );
			}
			if ( $r['length'] < 0.001 ) {
				$r['length'] = (float) get_metadata( 'post', $r['product_id'], '_length', true );
			}
			if ( $r['width'] < 0.001 ) {
				$r['width'] = (float) get_metadata( 'post', $r['product_id'], '_width', true );
			}
			if ( $r['height'] < 0.001 ) {
				$r['height'] = (float) get_metadata( 'post', $r['product_id'], '_height', true );
			}
		} else {
			$r['weight'] = (float) get_metadata( 'post', $r['product_id'], '_weight', true );
			$r['length'] = (float) get_metadata( 'post', $r['product_id'], '_length', true );
			$r['width']  = (float) get_metadata( 'post', $r['product_id'], '_width', true );
			$r['height'] = (float) get_metadata( 'post', $r['product_id'], '_height', true );
		}
		$packages[ $item_id ] = $r;
	}
	$dim    = WC_KShippingArgentina_Shipping::box_shipping( $packages );
	$to_ars = WC_KShippingArgentina_Manager::get_instance()->get_conversion_rate( get_woocommerce_currency(), 'ARS' );
	$data   = array(
		'box'          => array(
			'width'   => array(),
			'height'  => array(),
			'depth'   => array(),
			'weight'  => array(),
			'content' => array(),
			'total'   => array(),
		),
		'full_name'    => trim( $first_name . ' ' . $last_name ),
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'vat_type'     => $vat_type,
		'vat'          => $vat,
		'state'        => $state,
		'city'         => $city,
		'postcode'     => preg_replace( '/[^0-9]/', '', $postcode ),
		'address_1'    => $address_1,
		'address_2'    => $address_2,
		'number'       => $number,
		'floor'        => $floor,
		'email'        => $email,
		'apartment'    => $apartment,
		'prefix_phone' => $prefix_phone,
		'phone'        => $phone,
		'other_phone'  => $other_phone,
		'office'       => false,
		'postcode_src' => preg_replace( '/[^0-9]/', '', $setting['postcode'] ),
		'office_src'   => $iso_office_src,
		'office_sfull' => $shipping->office_src,
	);
	foreach ( $dim['weight'] as $b_id => $weight ) {
		$data['box']['width'][]   = $dim['width'][ $b_id ];
		$data['box']['height'][]  = $dim['height'][ $b_id ];
		$data['box']['depth'][]   = $dim['depth'][ $b_id ];
		$data['box']['weight'][]  = $dim['weight'][ $b_id ];
		$data['box']['content'][] = $dim['content'][ $b_id ];
		$data['box']['total'][]   = round( $dim['total'][ $b_id ] * $to_ars, 2 );
	}
	$order_offices = $order->get_meta( '_office_kshippingargentina' );
	if ( $shipping->office ) {
		$data['office'] = $order_offices[ $shipping->instance_id ]['office'];
	}
	$saved_data = get_post_meta( $order->get_id(), 'kshippingargentina_label_data', true );
	if ( $saved_data ) {
		foreach ( $data as $key => $value ) {
			if ( isset( $saved_data[ $key ] ) ) {
				$data[ $key ] = $saved_data[ $key ];
			}
		}
	}
	return apply_filters( 'kshippingargentina_label_data', $data, $order, $shipping );
}

$kshipping_woocommerce_after_order_object_save = array();
add_action(
	'woocommerce_after_order_object_save',
	function ( $order ) {
		global $kshipping_woocommerce_after_order_object_save;
		if ( isset( $_POST['kshippingargentina_is_order_save'] ) &&
			isset( $_POST['kshipping'] ) &&
			isset( $_POST['kshippingargentina_order_id'] ) &&
			isset( $_POST['kshippingargentina_generate_label_nonce'] ) &&
			! in_array( (int) $order->get_id(), $kshipping_woocommerce_after_order_object_save, true ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshippingargentina_generate_label_nonce'] ) ), ( (int) $_POST['kshippingargentina_order_id'] ) . '_kshippingargentina_generate_label_nonce' )
		) {
			$kshipping_woocommerce_after_order_object_save[] = (int) $order->get_id();
			$data  = $_POST;
			$label = $data['kshipping'];
			KShippingArgentina_API::debug( 'New request for Label save', $label );
			$order->update_meta_data( 'kshippingargentina_label_data', $label );
			$order->save();
		}
	},
	10,
	1
);

/**
 * Create Label in OCA ePack.
 *
 * @param int|WC_Order                   $order Order Object or Order ID.
 * @param array                          $label Labels.
 * @param WC_KShippingArgentina_Shipping $shipping Shipping Object.
 */
function kshipping_generate_label_oca( $order, $label, $shipping ) {
	$order_id = $order->get_id();
	$xml      = apply_filters(
		'kshipping_xml_oca',
		KShippingArgentina_API::oca_to_xml( $order_id . '-' . $label['vat_type'] . $label['vat'], $label, $shipping ),
		$order,
		$label,
		$shipping
	);
	KShippingArgentina_API::debug( 'OCA XML: ' . $xml );
	$setting = get_option( 'woocommerce_kshippingargentina-manager_settings' );
	$data    = KShippingArgentina_API::call_oca(
		'IngresoORMultiplesRetiros',
		array(
			'usr'             => $setting['oca_username'],
			'psw'             => $setting['oca_password'],
			'ConfirmarRetiro' => true,
			'xml_Datos'       => $xml,
		)
	);
	$error   = array();

	if ( isset( $data['Errores'] ) ) {
		$error[] = __( 'Error generating OCA order', 'carriers-of-argentina-for-woocommerce' ) . ': ' . ( (string) $data['Errores']['Error']['Descripcion'] );
	}

	$tracking_code = array();

	if ( isset( $data['Error'] ) ) {
		$error[] = __( 'Error generating OCA order', 'carriers-of-argentina-for-woocommerce' ) . ': ' . ( (string) $data['Error']['Descripcion'] );
	} elseif ( ! isset( $data['Resumen'] ) ) {
		$error[] = __( 'Error generating OCA order', 'carriers-of-argentina-for-woocommerce' ) . ': ' . wp_json_encode( $data );
	} elseif ( isset( $data['DetalleIngresos'] ) && isset( $data['DetalleIngresos']['NumeroEnvio'] ) ) {
		$order->update_meta_data( 'kshippingargentina_oca_tracking_reference', $data['DetalleIngresos']['OrdenRetiro'] );
		$order->update_meta_data( 'kshippingargentina_oca_operation_code', (int) $data['Resumen']['CodigoOperacion'] );
		$order->save();
		$tc        = (string) $data['DetalleIngresos']['NumeroEnvio'];
		$pdf_error = false;
		$pdf       = KShippingArgentina_API::get_oca_pdf_label( $tc, $pdf_error );
		if ( $pdf ) {
			$tracking_code = array(
				$tc => kshipping_save_pdf( $order_id, "oca_{$order_id}_$tc.pdf", $pdf ),
			);
		} else {
			$tracking_code = array(
				$tc => false,
			);
		}
	}
	$result = array(
		'error'         => false,
		'tracking_code' => $tracking_code,
	);
	if ( ! count( $tracking_code ) ) {
		$result['error'] = count( $error ) ? implode( ', ', $error ) : __( 'An error occurred connecting to OCA, try again later, if the error persists you should check the plugin logs with technical support.', 'carriers-of-argentina-for-woocommerce' );
	}
	return apply_filters(
		'kshipping_generate_label_andreani',
		$result,
		$order,
		$label,
		$shipping
	);

}

/**
 * Create Label in Andreani.
 *
 * @param int|WC_Order                   $order Order Object or Order ID.
 * @param array                          $label Labels.
 * @param WC_KShippingArgentina_Shipping $shipping Shipping Object.
 */
function kshipping_generate_label_andreani( $order, $label, $shipping ) {
	$ofi_src          = explode( '#', $label['office_src'] );
	$ofi_dst          = explode( '#', $label['office'] );
	$setting          = get_option( 'woocommerce_kshippingargentina-manager_settings' );
	$order_id         = $order->get_id();
	$andreani_request = array(
		'contrato'     => $shipping->product_type,
		'origen'       => $shipping->find_in_store ? array(
			'postal' => array(
				'pais'         => 'Argentina',
				'region'       => 'AR-' . $setting['state'],
				'codigoPostal' => $setting['postcode'],
				'calle'        => $setting['street'],
				'numero'       => $setting['number'],
				'localidad'    => $setting['city'],
			),
		) : array(
			'sucursal' => array( 'id' => $ofi_src[1] ),
		),
		'remitente'    => array(
			'nombreCompleto'  => $setting['fullname'],
			'eMail'           => $setting['email'],
			'documentoTipo'   => $setting['dni_type'],
			'documentoNumero' => $setting['dni'],
			'telefonos'       => array(
				array(
					'tipo'   => 1,
					'numero' => $setting['phone'],
				),
			),
		),
		'destinatario' => array(
			array(
				'nombreCompleto'  => $label['full_name'],
				'eMail'           => $label['email'],
				'documentoTipo'   => $label['vat_type'],
				'documentoNumero' => $label['vat'],
				'telefonos'       => array(
					array(
						'tipo'   => (int) 2,
						'numero' => $label['prefix_phone'] . $label['phone'],
					),
				),
			),
		),
		'destino'      => ! $shipping->office ? array(
			'postal' => array(
				'pais'         => 'Argentina',
				'region'       => 'AR-' . $label['state'],
				'codigoPostal' => $label['postcode'],
				'calle'        => $label['address_1'],
				'numero'       => $label['number'],
				'localidad'    => $label['city'],
			),
		) : array(
			'sucursal' => array( 'id' => $ofi_dst[1] ),
		),
	);
	if ( $shipping->find_in_store && ! empty( $setting['other'] ) ) {
		$andreani_request['origen']['postal']['componentesDeDireccion'] = array(
			array(
				'meta'      => 'Detalle',
				'contenido' => $setting['other'],
			),
		);
	}
	if ( ! $shipping->office && ! empty( $label['address_2'] ) ) {
		$andreani_request['destino']['postal']['componentesDeDireccion'] = array(
			array(
				'meta'      => 'Detalle',
				'contenido' => $label['address_2'],
			),
		);
	}
	$bultos = array();
	foreach ( array_keys( $label['box']['width'] ) as $i ) {
		$bultos[] = array(
			'anchoCm'                    => (int) $label['box']['width'][ $i ],
			'altoCm'                     => (int) $label['box']['height'][ $i ],
			'largoCm'                    => (int) $label['box']['depth'][ $i ],
			'volumenCm'                  => (int) ( $label['box']['width'][ $i ] * $label['box']['height'][ $i ] * $label['box']['depth'][ $i ] ),
			'kilos'                      => (float) round( $label['box']['weight'][ $i ], 2 ),
			'descripcion'                => $label['box']['content'][ $i ],
			'valorDeclaradoSinImpuestos' => (int) $label['box']['total'][ $i ],
			'valorDeclaradoConImpuestos' => (int) $label['box']['total'][ $i ],
			'referencias'                => array(
				array(
					'meta'      => 'detalle',
					'contenido' => $label['box']['content'][ $i ],
				),
				array(
					'meta'      => 'idCliente',
					'contenido' => $order_id . '-' . $i,
				),
				array(
					'meta'      => 'observaciones',
					'contenido' => ( isset( $label['address_2'] ) && ! empty( $label['address_2'] ) ? $label['address_2'] : '' ) . ' - ' . $label['box']['content'][ $i ],
				),
			),
		);
	}
	$andreani_request['bultos'] = $bultos;

	$api_error = false;
	$result    = KShippingArgentina_API::create_label_andreani(
		apply_filters(
			'kshippingargentina_andreani_label_request',
			$andreani_request,
			$order,
			$label,
			$shipping
		),
		$api_error
	);
	KShippingArgentina_API::debug( 'kshippingargentina_andreani_label_request result: ', array( $result, $api_error ) );
	if ( ! $result || ! isset( $result['bultos'] ) || ! count( $result['bultos'] ) ) {
		if ( ! $api_error && isset( $result['title'] ) && isset( $result['detail'] ) ) {
			$api_error = "{$result['title']}: {$result['detail']}";
		}
		return apply_filters(
			'kshipping_generate_label_andreani',
			array(
				'error'         => $api_error ? $api_error : __( 'Andreani return with Timeout', 'carriers-of-argentina-for-woocommerce' ),
				'tracking_code' => false,
			),
			$order,
			$label,
			$shipping
		);
	}
	$tracking_code = array();
	foreach ( $result['bultos'] as $bulto ) {
		$api_error                                = false;
		$pdf                                      = KShippingArgentina_API::get_andreani_pdf_label( $bulto['numeroDeEnvio'], $api_error );
		$tracking_code[ $bulto['numeroDeEnvio'] ] = $pdf ? kshipping_save_pdf(
			$order_id,
			"andreani_{$order_id}_{$bulto['numeroDeEnvio']}.pdf",
			$pdf
		) : false;
	}
	return apply_filters(
		'kshipping_generate_label_andreani',
		array(
			'error'         => false,
			'tracking_code' => $tracking_code,
		),
		$order,
		$label,
		$shipping
	);
}

/**
 * Create CSV for Correo Argentino.
 *
 * @param int|WC_Order                   $order Order Object or Order ID.
 * @param array                          $label Labels.
 * @param WC_KShippingArgentina_Shipping $shipping Shipping Object.
 */
function kshipping_generate_label_correo_argentino( $order, $label, $shipping ) {
	$dim   = $label['box'];
	$lines = array();
	foreach ( $dim['weight'] as $i => $weight ) {
		$csvl = "{$shipping->product_type};{$dim['height'][$i]};{$dim['width'][$i]};{$dim['depth'][$i]};{$dim['weight'][$i]};{$dim['total'][$i]};{$label['state']};";
		if ( (bool) $shipping->office ) {
			$csvl .= explode( '#', $label['office'] )[0] . ';;;;;;;';
		} else {
			$address = $label['address_1'] . ( ! empty( $label['address_2'] ) ? ', ' . $label['address_2'] : '' );
			$csvl   .= ";{$label['city']};{$address};{$label['number']};{$label['floor']};{$label['apartment']};{$label['postcode']};";
		}
		$csvl   .= "{$label['full_name']};EMAILAQUI;;;{$label['prefix_phone']};{$label['phone']}";
		$csv     = str_replace(
			'EMAILAQUI',
			$label['email'],
			preg_replace(
				'/[^0-9a-zA-Z ,._;\(\)-]/',
				'',
				str_replace(
					array(
						'Á',
						'É',
						'Í',
						'Ó',
						'Ú',
						'Ñ',
						'á',
						'é',
						'í',
						'ó',
						'ú',
						'ñ',
					),
					array(
						'A',
						'E',
						'I',
						'O',
						'U',
						'N',
						'a',
						'e',
						'i',
						'o',
						'u',
						'n',
					),
					$csvl
				)
			)
		);
		$lines[] = $csv;
	}
	if ( count( $lines ) > 0 ) {
		$csv       = apply_filters(
			'kshipping_csv_correo_argentino',
			'tipo_producto(obligatorio);largo(obligatorio en CM);ancho(obligatorio en CM);altura(obligatorio en CM);peso(obligatorio en KG);valor_del_contenido(obligatorio en pesos argentinos);provincia_destino(obligatorio);sucursal_destino(obligatorio solo en caso de no ingresar localidad de destino);localidad_destino(obligatorio solo en caso de no ingresar sucursal de destino);calle_destino(obligatorio solo en caso de no ingresar sucursal de destino);altura_destino(obligatorio solo en caso de no ingresar sucursal de destino);piso(opcional solo en caso de no ingresar sucursal de destino);dpto(opcional solo en caso de no ingresar sucursal de destino);codpostal_destino(obligatorio solo en caso de no ingresar sucursal de destino);destino_nombre(obligatorio);destino_email(obligatorio, debe ser un email valido);cod_area_tel(opcional);tel(opcional);cod_area_cel(obligatorio);cel(obligatorio)' . "\n" . implode( "\n", $lines ),
			$order,
			$label,
			$shipping
		);
		$file_name = apply_filters(
			'kshipping_filename_correo_argentino',
			'correo_argentino_' . $order->get_id() . '.csv',
			$order,
			$label,
			$shipping
		);
		$file      = kshipping_save_pdf( $order->get_id(), $file_name, $csv );
		return apply_filters(
			'kshipping_generate_label_correo_argentino',
			array(
				'error'         => false,
				'tracking_code' => array(
					'no_tracking_code' => $file,
				),
			),
			$order,
			$label,
			$shipping
		);
	}
	return false;
}

/**
 * Save file label to file system.
 *
 * @param int    $order_id Order ID.
 * @param string $file_name File name.
 * @param mixed  $binary Binary RAW.
 * @param bool   $override Override.
 */
function kshipping_save_pdf( $order_id, $file_name, $binary, $override = true ) {
	global $wp_filesystem;
	// Initialize the WP filesystem.
	if ( ! $wp_filesystem ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}
	$upload_dir = wp_upload_dir();
	$base_dir   = $upload_dir['basedir'];
	$final_path = '/kshipping_argentina';
	if ( ! is_dir( $base_dir . $final_path ) ) {
		mkdir( $base_dir . $final_path );
	}

	if ( ! file_exists( $base_dir . $final_path . '/index.php' ) ) {
		$wp_filesystem->put_contents( $base_dir . $final_path . '/index.php', '' );
	}

	$sub_dirs = array();
	$to_sub   = (int) $order_id;
	while ( $to_sub > 0 ) {
		$sub_dirs[] = $to_sub % 10;
		$to_sub     = (int) ( $to_sub / 10 );
	}
	for ( $i = count( $sub_dirs ) - 1; $i >= 0; --$i ) {
		$final_path .= '/' . $sub_dirs[ $i ];
		if ( ! is_dir( $base_dir . $final_path ) ) {
			mkdir( $base_dir . $final_path );
		}
	}
	if ( $override && file_exists( $base_dir . $final_path . '/' . $file_name ) ) {
		unlink( $base_dir . $final_path . '/' . $file_name );
	}

	if ( ! file_exists( $base_dir . $final_path . '/index.php' ) ) {
		$wp_filesystem->put_contents( $base_dir . $final_path . '/index.php', '' );
	}

	if ( ! file_exists( $base_dir . $final_path . '/' . $file_name ) ) {
		if ( ! $wp_filesystem->put_contents( $base_dir . $final_path . '/' . $file_name, $binary, 0644 ) ) {
			return false;
		}
	}

	return apply_filters(
		'kshipping_save_pdf',
		array(
			'file_path' => $base_dir . $final_path . '/' . $file_name,
			'url_path'  => $upload_dir['baseurl'] . $final_path . '/' . $file_name,
			'path'      => $final_path . '/' . $file_name,
			'dir_path'  => $final_path,
			'file_name' => $file_name,
		),
		$order_id,
		$file_name,
		$binary
	);
}

/**
 * Create CSV for Correo Argentino.
 *
 * @param int|WC_Order                   $order Order ID.
 * @param WC_KShippingArgentina_Shipping $shipping Shipping Object.
 */
function kshipping_notify_new_tracking( $order, $shipping ) {
	$mailer = WC()->mailer();
	$mailer->get_emails();
	$setting = get_option( 'woocommerce_kshippingargentina-manager_settings' );
	KShippingArgentina_API::debug(
		'kshipping_notify_new_tracking',
		array(
			$order->get_id(),
			$setting['no_change_in_transit'],
			$shipping->service_type,
			$shipping->instance_id,
		)
	);
	if ( 'yes' !== $setting['no_change_in_transit'] ) {
		$order->update_status( 'intransit' );
	}
	WC_KNewTracking_Customer_Email::get_instance();
	WC_KNewTracking_Admin_Email::get_instance();
	do_action( 'woocommerce_order_new_ktracking_code', $order->get_id(), $shipping );
}
