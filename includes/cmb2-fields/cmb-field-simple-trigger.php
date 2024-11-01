<?php
/*
CMB2 Field Type: Simple Trigger by anwp.pro
Version: 0.1.0
License: GPLv2+

Using example:
'type'    => 'anwp_sl_simple_trigger',
'options' => [
	''    => [
		'color' => 'neutral',
		'text'  => esc_html__( 'No', 'sports-leagues' ),
	],
	'yes' => [
		'color' => 'success',
		'text'  => esc_html__( 'Yes', 'sports-leagues' ),
	],
],
*/

add_action( 'cmb2_render_anwp_sl_simple_trigger', 'cmb2_render_anwp_sl_simple_trigger', 10, 2 );
add_filter( 'cmb2_sanitize_anwp_sl_simple_trigger', 'cmb2_sanitize_anwp_sl_simple_trigger', 10, 4 );

if ( ! function_exists( 'cmb2_render_anwp_sl_simple_trigger' ) ) {

	/**
	 * Render Simple Trigger field
	 *
	 * @param $field
	 * @param $value
	 */
	function cmb2_render_anwp_sl_simple_trigger( $field, $value ) {
		?>
		<div class="clearfix">
			<div class="anwp-sl-button-group">

				<?php foreach ( $field->args['options'] as $key => $option ) : ?>

					<?php
					$checked = ( $key === $value ) ? 'checked' : '';
					printf(
						'<input class="d-none" id="%1$s_%2$s" name="%1$s" type="radio" value="%2$s" %3$s>',
						esc_attr( $field->args['id'] ),
						esc_attr( $key ),
						esc_attr( $checked )
					);
					?>
					<label for="<?php printf( '%1$s_%2$s', esc_attr( $field->args['id'] ), esc_attr( $key ) ); ?>" class="button anwp-sl-button-<?php echo esc_attr( $option['color'] ); ?>">
						<?php echo esc_html( $option['text'] ); ?>
					</label>
				<?php endforeach; ?>

			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'cmb2_sanitize_anwp_sl_simple_trigger' ) ) {
	/**
	 * Sanitize Simple Trigger field
	 *
	 * @param $null
	 * @param $value
	 * @param $object_id
	 * @param $args
	 *
	 * @return mixed
	 */
	function cmb2_sanitize_anwp_sl_simple_trigger( $null, $value, $object_id, $args ) {

		if ( ! isset( $args['options'][ $value ] ) ) {
			return $args['default'];
		}

		return $value;
	}
}
