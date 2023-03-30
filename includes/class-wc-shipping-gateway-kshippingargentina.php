<?php
/**
 * Override WC_Shipping_Method
 *
 * @package Kijam
 */

if ( ! class_exists( 'WC_Shipping_Gateway_KShippingArgentina' ) ) :
	/**
	 * Generate Text HTML.
	 *
	 * @since  1.0.0
	 */
	class WC_Shipping_Gateway_KShippingArgentina extends WC_Shipping_Method {
		/**
		 * Generate Text HTML.
		 *
		 * @param  mixed $key Key of label.
		 * @param  mixed $data data of label.
		 * @since  1.0.0
		 * @return string
		 */
		public function generate_multiselectmp_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'             => '',
				'disabled'          => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => array(),
				'select_buttons'    => false,
				'options'           => array(),
			);

			$data  = wp_parse_args( $data, $defaults );
			$value = (array) $this->get_option( $key, array() );

			ob_start();
			?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo wp_kses_post( $this->get_tooltip_html( $data ) ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<!-- <select multiple="multiple" class="multiselect <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo wp_kses_post( $this->get_custom_attribute_html( $data ) ); ?>> -->
					<div style="overflow: auto;height: 150px;min-width:250px;max-width:500px">
						<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
							<input type="checkbox" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( in_array( $option_key, $value, true ), true ); ?> /><?php echo esc_attr( $option_value ); ?><br />
						<?php endforeach; ?>
					</div>
					<!-- </select> -->
					<?php echo wp_kses_post( $this->get_description_html( $data ) ); ?>
					<?php if ( $data['select_buttons'] ) : ?>
						<br/><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'wc-kshippingargentina' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'wc-kshippingargentina' ); ?></a>
					<?php endif; ?>
				</fieldset>
			</td>
		</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Validate for label.
		 *
		 * @param mixed $key Key of label.
		 * @param mixed $value Value of label.
		 *
		 * @return string
		 */
		public function validate_multiselectmp_field( $key, $value ) {
			return is_array( $value ) ? array_map( 'wc_clean', array_map( 'stripslashes', $value ) ) : '';
		}

		/**
		 * HTML for HTML label.
		 *
		 * @param mixed $key Key of label.
		 * @param mixed $data Data of label.
		 *
		 * @return string
		 */
		public function generate_html_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'       => '',
				'type'        => 'html',
				'description' => '',
			);
			$data      = wp_parse_args( $data, $defaults );
			ob_start();
			?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<?php echo wp_kses_post( $data['description'] ); ?>
			</td>
		</tr>
			<?php
			return ob_get_clean();
		}
	}
endif;
