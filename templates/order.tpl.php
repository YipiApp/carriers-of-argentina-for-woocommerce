<?php
/**
 * Template for Label generator in Order.
 *
 * @package Kijam
 */

$offices = array();
if ( $shipping->office ) {
	$offices = KShippingArgentina_API::get_office( $shipping->service_type, $postcode, null, true );
}
$offices_src = KShippingArgentina_API::get_office( $shipping->service_type, $setting['postcode'], true, null );

?>
<div id="kshippingargentina-container">
	<div class="order_data_column_container">
		<div class="order_data_column">
			<strong><?php esc_html_e( 'Customer Address', 'wc-kshippingargentina' ); ?></strong>
			<p class="form-field form-field-wide">
				<label for="kshipping_fullname"><?php esc_html_e( 'Full name', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_fullname" name="kshipping[full_name]" id="kshipping_fullname" value="<?php echo esc_html( trim( $first_name . ' ' . $last_name ) ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_vat_type"><?php esc_html_e( 'Identification number type', 'wc-kshippingargentina' ); ?>:</label>
				<select class="kshipping_vat_type" name="kshipping[vat_type]" id="kshipping_vat_type">
				<?php
				foreach ( WC_KShippingArgentina::$vat_types as $v_key => $v_name ) {
					if ( $v_key === $vat_type ) {
						echo '<option selected value="' . esc_html( $v_key ) . '">' . esc_html( $v_name ) . '</option>';
					} else {
						echo '<option value="' . esc_html( $v_key ) . '">' . esc_html( $v_name ) . '</option>';
					}
				}
				?>
				</select>
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_vat"><?php esc_html_e( 'Identification number', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_vat" name="kshipping[vat]" id="kshipping_" value="<?php echo esc_html( $vat ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_state"><?php esc_html_e( 'State', 'wc-kshippingargentina' ); ?>:</label>
				<select class="kshipping_state" name="kshipping[state]" id="kshipping_state">
					<?php
					foreach ( $states as $s_key => $s_name ) {
						if ( $s_key === $state ) {
							echo '<option selected value="' . esc_html( $s_key ) . '">' . esc_html( $s_name ) . '</option>';
						} else {
							echo '<option value="' . esc_html( $s_key ) . '">' . esc_html( $s_name ) . '</option>';
						}
					}
					?>
				</select>
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_city"><?php esc_html_e( 'City', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_city" name="kshipping[city]" id="kshipping_city" value="<?php echo esc_html( $city ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_city"><?php esc_html_e( 'Postcode', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_postcode" name="kshipping[postcode]" id="kshipping_postcode" value="<?php echo esc_html( $postcode ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_address_1"><?php esc_html_e( 'Street name', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_address_1" name="kshipping[address_1]" id="kshipping_address_1" value="<?php echo esc_html( $address_1 ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_address_2"><?php esc_html_e( 'Detail (Between-streets, etc)', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_address_2" name="kshipping[address_2]" id="kshipping_address_2" value="<?php echo esc_html( $address_2 ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_number"><?php esc_html_e( 'Height (Enter numbers only)', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_number" name="kshipping[number]" id="kshipping_number" value="<?php echo esc_html( $number ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_floor"><?php esc_html_e( 'Floor', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" maxlength="3" class="kshipping_floor" name="kshipping[floor]" id="kshipping_floor" value="<?php echo esc_html( $floor ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_apartment"><?php esc_html_e( 'Apartment', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" maxlength="3" class="kshipping_apartment" name="kshipping[apartment]" id="kshipping_apartment" value="<?php echo esc_html( $apartment ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_prefix_phone"><?php esc_html_e( 'Mobile Phone Area Code', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_prefix_phone" name="kshipping[prefix_phone]" id="kshipping_prefix_phone" value="<?php echo esc_html( $prefix_phone ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_phone"><?php esc_html_e( 'Mobile Phone (No Area Code)', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_phone" name="kshipping[phone]" id="kshipping_phone" value="<?php echo esc_html( $phone ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="other_phone"><?php esc_html_e( 'Other Phone', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_other_phone" name="kshipping[other_phone]" id="kshipping_other_phone" value="<?php echo esc_html( $other_phone ); ?>" />
			</p>
		</div>
		<div class="order_data_column">
			<strong><?php esc_html_e( 'Carrier Information', 'wc-kshippingargentina' ); ?> - <?php echo esc_html( $order->get_shipping_method() ); ?></strong>

			<p class="form-field form-field-wide">
				<label for="kshipping_postcode_src"><?php esc_html_e( 'Origin Postcode', 'wc-kshippingargentina' ); ?>:</label>
				<input type="text" class="kshipping_postcode_src" name="kshipping[postcode_src]" id="kshipping_postcode_src" value="<?php echo esc_html( $setting['postcode'] ); ?>" />
			</p>
			<p class="form-field form-field-wide">
				<label for="kshipping_office_src"><?php esc_html_e( 'Origin Office', 'wc-kshippingargentina' ); ?>:</label>
				<select data-default="<?php echo esc_html( $iso_office_src ); ?>" class="kshipping_office_src" name="kshipping[office_src]" id="kshipping_office">
					<?php
					foreach ( $offices_src as $o_key => $office ) {
						if ( $o_key === $iso_office_src ) {
							echo '<option selected value="' . esc_html( $office['iso'] . '#' . $office['id'] ) . '">' . esc_html( $office['description'] . ' - ' . $office['address'] ) . '</option>';
						} else {
							echo '<option value="' . esc_html( $office['iso'] . '#' . $office['id'] ) . '">' . esc_html( $office['description'] . ' - ' . $office['address'] ) . '</option>';
						}
					}
					?>
				</select>
			</p>
			<?php
			if ( $shipping->office ) {
				?>
				<p class="form-field form-field-wide">
					<label for="kshipping_office"><?php esc_html_e( 'Destination Office', 'wc-kshippingargentina' ); ?>:</label>
					<select class="kshipping_office" name="kshipping[office]" id="kshipping_office">
						<?php
						foreach ( $offices as $o_key => $office ) {
							if ( $o_key === $order_offices[ $shipping->instance_id ]['office'] ) {
								echo '<option selected value="' . esc_html( $office['iso'] . '#' . $office['id'] ) . '">' . esc_html( $office['description'] . ' - ' . $office['address'] ) . '</option>';
							} else {
								echo '<option value="' . esc_html( $office['iso'] . '#' . $office['id'] ) . '">' . esc_html( $office['description'] . ' - ' . $office['address'] ) . '</option>';
							}
						}
						?>
					</select>
				</p>
				<?php
			}
			?>
			<br />
			<strong><?php esc_html_e( 'Boxes', 'wc-kshippingargentina' ); ?></strong>
			<br /><br />
			<div class="clear"></div>
			<div class="kshippingargentina-boxes">
			<?php
			foreach ( $box['weight'] as $b_id => $weight ) :
				?>
				<div class="kshippingargentina-box">
					<strong><?php esc_html_e( 'Box', 'wc-kshippingargentina' ); ?>:</strong>
					<p class="form-field form-field-wide kshippingargentina-dimensions">
						<label for=""><?php esc_html_e( 'Dimensions (In CM)', 'wc-kshippingargentina' ); ?>:</label>
						<br />
						<input type="text" class="kshipping_box_width" name="kshipping[box][width][]" value="<?php echo esc_html( $box['width'][ $b_id ] ); ?>"  /> x
						<input type="text" class="kshipping_box_height" name="kshipping[box][height][]" value="<?php echo esc_html( $box['height'][ $b_id ] ); ?>" /> x
						<input type="text" class="kshipping_box_depth" name="kshipping[box][depth][]" value="<?php echo esc_html( $box['depth'][ $b_id ] ); ?>" />
					</p>
					<p class="form-field form-field-wide">
						<label for=""><?php esc_html_e( 'Weight (In KG)', 'wc-kshippingargentina' ); ?>:</label>
						<input type="text" class="kshipping_box_weight" name="kshipping[box][weight][]" value="<?php echo esc_html( $box['weight'][ $b_id ] ); ?>" />
					</p>
					<p class="form-field form-field-wide">
						<label for=""><?php esc_html_e( 'Contents', 'wc-kshippingargentina' ); ?>:</label>
						<input type="text" class="kshipping_box_content" name="kshipping[box][content][]" value="<?php echo esc_html( $box['content'][ $b_id ] ); ?>" />
					</p>
					<p class="form-field form-field-wide">
						<label for=""><?php esc_html_e( 'Total Cost (In ARS)', 'wc-kshippingargentina' ); ?>:</label>
						<input type="text" class="kshipping_box_total" name="kshipping[box][total][]" value="<?php echo esc_html( $box['total'][ $b_id ] ); ?>" />
					</p>
					<?php
					if ( 0 !== (int) $b_id ) {
						?>
						<a href="javascript:;" onclick="kshipping_remove_box(this)"><?php esc_html_e( 'Add new box', 'wc-kshippingargentina' ); ?></a>
						<?php
					}
					?>
				</div>
				<?php
			endforeach;
			?>
			</div>
			<div class="clear"></div>
			<br /><br />
			<button class="button" type="button" onclick="kshipping_new_box()"><?php esc_html_e( 'Add new box', 'wc-kshippingargentina' ); ?></button>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<input type="hidden" name="kshippingargentina_generate_label_nonce" value="<?php echo esc_html( wp_create_nonce( 'kshippingargentina_generate_label_nonce' ) ); ?>" />
	<input type="hidden" name="kshippingargentina_order_id" value="<?php echo esc_html( $order_id ); ?>" />
	<input type="hidden" name="kshippingargentina_instance_id" value="<?php echo esc_html( $shipping->instance_id ); ?>" />
	<button class="button" type="button" onclick="kshipping_generate_label(this)"><?php esc_html_e( 'Generate Label', 'wc-kshippingargentina' ); ?></button>
</div>
<input type="hidden" name="kshippingargentina_is_order_save" />
<style>
	#kshippingargentina-container .order_data_column:last-child {
		padding-right: 0;
	}

	#kshippingargentina-container select {
		width: 100%;
	}

	#kshippingargentina-container .order_data_column {
		width: 48%;
		padding: 0 2% 0 0;
		float: left;
	}
	.kshippingargentina-dimensions input:first-child {
		display: unset;
	}
	.kshippingargentina-dimensions input {
		display: inline;
		width: 50px !important;
	}
	.kshippingargentina-box {
		float: left;
		width: 48%;
	}
	.kshippingargentina-box p {
		margin: 0;
	}
	@media only screen and (max-width: 1280px) {
		#kshippingargentina-container .order_data_column {
			width: 98%;
		}
	}
</style>
