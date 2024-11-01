<?php
/*
CMB2 Field Type: Maps by anwp.pro
Version: 0.1.0
License: GPLv2+
*/

// Field Hooks
add_filter( 'cmb2_sanitize_anwp_map', 'cmb2_sanitize_anwp_map_callback', 10, 2 );
add_action( 'cmb2_render_anwp_map', 'cmb2_render_anwp_map_callback', 10, 5 );

if ( ! function_exists( 'cmb2_sanitize_anwp_map_callback' ) ) {

	/**
	 * Filter the value before it is saved.
	 *
	 * @param bool|mixed $override_value Sanitization/Validation override value to return. Default false to skip it.
	 * @param mixed      $value          The value to be saved to this field.
	 *
	 * @return mixed
	 */
	function cmb2_sanitize_anwp_map_callback( $override_value, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'cmb2_render_anwp_map_callback' ) ) {

	/**
	 * @param array  $field              The passed in `CMB2_Field` object
	 * @param mixed  $escaped_value      The value of this field escaped.
	 *                                   It defaults to `sanitize_text_field`.
	 *                                   If you need the unescaped value, you can access it
	 *                                   via `$field->value()`
	 * @param int    $field_object_id    The ID of the current object
	 * @param string $field_object_type  The type of object you are working with.
	 *                                   Most commonly, `post` (this applies to all post-types),
	 *                                   but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object  This `CMB2_Types` object
	 */
	function cmb2_render_anwp_map_callback( $field, $escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

		ob_start(); ?>

		<h4><?php echo esc_html__( 'Set location', 'sports-leagues' ); ?></h4>

		<div class="d-flex flex-wrap">
			<div class="d-flex flex-column mr-2 mb-2">
				<label class="mb-1"><?php echo esc_html__( 'Latitude', 'sports-leagues' ); ?></label>
				<?php
				$input_args = [
					'type'     => 'text',
					'name'     => $field->args( '_name' ) . '[lat]',
					'value'    => empty( $escaped_value['lat'] ) ? '' : esc_attr( $escaped_value['lat'] ),
					'readonly' => 'true',
					'id'       => 'anwp_cmb2_map_input_latitude',
				];

				echo $field_type_object->input( $input_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>

			<div class="d-flex flex-column mb-2">
				<label class="mb-1"><?php echo esc_html__( 'Longitude', 'sports-leagues' ); ?></label>
				<?php
				$input_args = [
					'type'     => 'text',
					'name'     => $field->args( '_name' ) . '[longitude]',
					'value'    => empty( $escaped_value['longitude'] ) ? '' : esc_attr( $escaped_value['longitude'] ),
					'readonly' => 'true',
					'id'       => 'anwp_cmb2_map_input_longitude',
				];

				echo $field_type_object->input( $input_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</div>

		<div>
			<button id="anwp_cmb2_map_reset_btn" type="button" class="button mt-2"><?php echo esc_html__( 'Reset location', 'sports-leagues' ); ?></button>
		</div>
		<div class="mt-3">
			<label for="anwp_cmb2_map_input_address"><?php echo esc_html__( 'Address Search', 'sports-leagues' ); ?></label>
			<input type="text" name="" id="anwp_cmb2_map_input_address" value="">
		</div>

		<?php if ( sports_leagues()->get_option_value( 'google_maps_api' ) ) : ?>
			<div id="anwp_sl_map_wrapper"></div>
		<?php else : ?>
			<div class="alert alert-warning my-2"><?php echo esc_html__( 'Please insert Google Maps API Key in plugin settings.', 'sports-leagues' ); ?></div>
		<?php endif; ?>

		<?php

		// grab the data from the output buffer.
		echo ob_get_clean(); // WPCS: XSS ok.
	}
}
