<?php
/**
 * The Template for displaying Teams Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-teams.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          Sports-Leagues/Templates
 *
 * @since            0.5.5
 *
 * @version          0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'stage_id'       => '',
		'layout'         => '',
		'context'        => 'shortcode',
		'logo_height'    => '50px',
		'logo_width'     => '50px',
		'exclude_ids'    => '',
		'include_ids'    => '',
		'show_team_name' => '1',
	]
);

if ( ! empty( $data->include_ids ) ) {
	$teams_array = wp_parse_id_list( $data->include_ids );
} else {

	if ( empty( $data->stage_id ) ) {
		return;
	}

	$teams_array = sports_leagues()->tournament->get_stage_teams( $data->stage_id, 'all' );

	// Check exclude ids
	if ( ! empty( $data->exclude_ids ) ) {
		$exclude     = $data->exclude_ids ? wp_parse_id_list( $data->exclude_ids ) : [];
		$teams_array = array_diff( $teams_array, $exclude );
	}
}

if ( '' === $data->layout ) : ?>
	<div class="anwp-b-wrap teams teams--shortcode teams__inner context--<?php echo esc_attr( $data->context ); ?>">
		<div class="d-flex flex-wrap">
			<?php
			foreach ( $teams_array as $team ) :
				$team_data = sports_leagues()->team->get_team_by_id( $team );

				if ( empty( $team_data->id ) || ! intval( $team_data->id ) ) {
					continue;
				}

				// Prepare style code
				$style_code = 'width: ' . $data->logo_width . '; height: ' . $data->logo_height . '; ';

				if ( $team_data->logo ) {
					$style_code .= "background-image: url('" . $team_data->logo . "');";
				}
				?>
				<div class="team-logo position-relative" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $team_data->title ); ?>">
					<div class="anwp-image-background-contain team-logo__image" style="<?php echo esc_attr( $style_code ); ?>"></div>
					<a class="anwp-link-without-effects anwp-link-cover" href="<?php echo esc_url( $team_data->link ); ?>"></a>
					<?php if ( Sports_Leagues::string_to_bool( $data->show_team_name ) ) : ?>
						<div class="team-logo__text anwp-text-center text-truncate text-nowrap" style="<?php echo esc_attr( 'width: ' . $data->logo_width ); ?>">
							<?php echo esc_html( $team_data->abbr ? : $team_data->title ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php elseif ( in_array( $data->layout, [ '2col', '3col', '4col', '6col' ], true ) ) : ?>
	<div class="anwp-b-wrap teams teams--shortcode teams__inner context--<?php echo esc_attr( $data->context ); ?>">
		<div class="anwp-row anwp-no-gutters layout--grid">
			<?php
			$col_class = [
				'2col' => 'anwp-col-6',
				'3col' => 'anwp-col-4',
				'4col' => 'anwp-col-3',
				'6col' => 'anwp-col-2',
			];

			foreach ( $teams_array as $team ) :
				$team_data = sports_leagues()->team->get_team_by_id( $team );

				if ( empty( $team_data->id ) || ! intval( $team_data->id ) ) {
					continue;
				}
				?>
				<div class="<?php echo esc_attr( $col_class[ $data->layout ] ); ?> p-1 d-flex align-self-stretch" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $team_data->title ); ?>">
					<div class="team-logo team-logo--grid w-100 d-flex flex-column">
						<a class="anwp-link-without-effects d-flex align-items-center justify-content-center w-100 h-100 anwp-team-logo--widget"
							style="background-image: url('<?php echo esc_attr( $team_data->logo ?: '' ); ?>')" href="<?php echo esc_url( $team_data->link ); ?>">
							<img class="invisible" src="<?php echo esc_attr( $team_data->logo ?: '' ); ?>" alt="">
						</a>
						<?php if ( Sports_Leagues::string_to_bool( $data->show_team_name ) ) : ?>
							<div class="team-logo__text anwp-text-center text-truncate text-nowrap"><?php echo esc_html( $team_data->abbr ? : $team_data->title ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
endif;
