<?php

if ( ! class_exists( 'AnWP_CMB2_Field_Repeated' ) ) :

	class AnWP_CMB2_Field_Repeated {

		const VERSION = '0.1.0';

		public function __construct() {
			add_filter( 'cmb2_render_anwp_repeated', [ $this, 'render_anwp_repeated' ], 10, 5 );
			add_filter( 'cmb2_sanitize_anwp_repeated', [ $this, 'sanitize_anwp_repeated' ], 10, 4 );
			add_filter( 'cmb2_types_esc_anwp_repeated', [ $this, 'escape' ], 10, 4 );
		}

		/**
		 * Render 'anwp_repeated' custom field type
		 *
		 * @since 0.1.0
		 *
		 * @param array      $field               The passed in `CMB2_Field` object
		 * @param mixed      $field_escaped_value The value of this field escaped.
		 *                                        It defaults to `sanitize_text_field`.
		 *                                        If you need the unescaped value, you can access it
		 *                                        via `$field->value()`
		 * @param int        $field_object_id     The ID of the current object
		 * @param string     $field_object_type   The type of object you are working with.
		 *                                        Most commonly, `post` (this applies to all post-types),
		 *                                        but could also be `comment`, `user` or `options-page`.
		 * @param CMB2_Types $field_type_object   The `CMB2_Types` object
		 */
		public function render_anwp_repeated( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

			// make sure we assign each part of the value we need.
			$value = wp_parse_args(
				$field_escaped_value,
				[
					'name' => '',
					'abbr' => '',
				]
			);

			$name_args = [
				'name'  => $field_type_object->_name( '[name]' ),
				'id'    => $field_type_object->_id( '_name' ),
				'value' => isset( $value['name'] ) ? $value['name'] : '',
			];

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $field_type_object->input( $name_args );

			$abbr_args = [
				'class' => 'cmb_text_small',
				'name'  => $field_type_object->_name( '[abbr]' ),
				'id'    => $field_type_object->_id( '_abbr' ),
				'value' => isset( $value['abbr'] ) ? $value['abbr'] : '',
			];

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $field_type_object->input( $abbr_args );
		}

		/**
		 * Handle sanitization for repeatable fields
		 */
		public function sanitize_anwp_repeated( $check, $meta_value, $object_id, $field_args ) {

			if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
				return $check;
			}

			foreach ( $meta_value as $key => $val ) {
				$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
			}

			return array_filter( $meta_value );
		}

		public static function escape( $check, $meta_value, $field_args, $field_object ) {

			if ( empty( $meta_value ) || ! is_array( $meta_value ) ) {
				return [];
			}

			foreach ( $meta_value as $key => $val ) {
				if ( is_array( $val ) ) {
					$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
				} else {
					$meta_value[ $key ] = esc_html( $val );
				}
			}

			return array_filter( $meta_value );
		}
	}

endif;

new AnWP_CMB2_Field_Repeated();
