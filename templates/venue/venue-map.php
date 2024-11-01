<?php
/**
 * The Template for displaying Venue >> Map Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/venue/venue-map.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'venue_id' => '',
		'header'   => true,
	]
);

$map_key = Sports_Leagues_Options::get_value( 'google_maps_api' );

if ( empty( $map_key ) ) {
	return;
}

$map_data = maybe_unserialize( get_post_meta( $data->venue_id, '_sl_map', true ) );

if ( ! is_array( $map_data ) || empty( $map_data['lat'] ) || empty( $map_data['longitude'] ) ) {
	return;
}

$is_consent_required = 'yes' === sports_leagues()->customizer->get_value( 'venue', 'map_consent_required' );

if ( $is_consent_required ) {
	$cookies = wp_unslash( $_COOKIE );

	if ( isset( $cookies['__sl_map_consent_allow'] ) && 'yes' === $cookies['__sl_map_consent_allow'] ) {
		$is_consent_required = false;
	}
}

if ( ! $is_consent_required ) {
	$google_maps_api_key = '?key=' . $map_key;
	wp_enqueue_script( 'google-maps-api-3', '//maps.googleapis.com/maps/api/js' . $google_maps_api_key . '&callback=Function.prototype', [], 3, false );
}
?>
<div class="anwp-section">
	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'venue__location', __( 'Location', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( $is_consent_required ) :
		?>
		<div class="venue-map__consent anwp-h-min-400 p-4 anwp-bg-light">
			<p class="anwp-text-center mt-3">
				<?php echo esc_html( sports_leagues()->customizer->get_value( 'venue', 'map_consent_text', 'Consent Text' ) ); ?>
			</p>
			<p class="anwp-text-center mt-1">
				<button id="anwp-sl-map-consent-allow" class="button" type="button">
					<?php echo esc_html( sports_leagues()->customizer->get_value( 'venue', 'map_consent_btn_text', 'Load Map' ) ); ?>
				</button>
			</p>
			<p class="anwp-text-center mt-3">
				<img src="<?php echo esc_url( Sports_Leagues::url( 'public/img/google-maps-placeholder.png' ) ); ?>" alt="map placeholder">
			</p>
		</div>
	<?php else : ?>
		<div id="map--venue" class="map map--venue" data-lat="<?php echo esc_attr( $map_data['lat'] ); ?>" data-longitude="<?php echo esc_attr( $map_data['longitude'] ); ?>"></div>
	<?php endif; ?>
</div>


