<?php
/**
 * The Template for displaying Team Roster.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-roster.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check required params
if ( empty( $data->team_id ) || empty( $data->season_id ) ) {
	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'team_id'   => '',
		'season_id' => '',
		'class'     => '',
		'header'    => true,
	]
);

// Prepare roster
$roster = sports_leagues()->team->tmpl_prepare_team_roster( $data->team_id, $data->season_id );

// Default photo
$default_photo = sports_leagues()->helper->get_default_player_photo();
?>
<div class="anwp-b-wrap roster roster--shortcode <?php echo esc_attr( $data->class ); ?>">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'shortcode__roster__roster', __( 'Roster', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( empty( $roster ) || empty( wp_list_filter( $roster, [ 'type' => 'player' ] ) ) ) :
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'shortcode__roster__no_players_in_roster', __( 'No players in roster', 'sports-leagues' ) ),
			],
			'general/no-data'
		);
	else :
		?>
		<div class="table-responsive">
			<table class="w-100 team__roster team-roster team__roster--table table">
				<tbody>
				<?php
				/*
				|--------------------------------------------------------------------
				| Prepare output
				|--------------------------------------------------------------------
				*/
				$roster_output    = [];
				$last_group_index = 0;

				foreach ( $roster as $roster_item ) :

					if ( 'group' === $roster_item['type'] ) :
						ob_start();
						?>
						<tr class="anwp-bg-light text-dark team-roster__th-wrapper">
							<td class="anwp-text-center px-0 align-middle team-roster__th-number">##</td>
							<td class="px-2 text-uppercase team-roster__th" colspan="4">
								<?php echo esc_html( sports_leagues()->config->get_name_by_id( 'roster_groups', $roster_item['title'] ) ); ?>
							</td>
						</tr>
						<?php
						$last_group_index ++;
						$roster_output[ $last_group_index ]['title'] = ob_get_clean();
						continue;
					endif;

					// Check for "hidden" status with minus prefix
					if ( '-' === mb_substr( $roster_item['status'], 0, 1 ) ) {
						continue;
					}

					ob_start();
					?>
					<tr>
						<td class="anwp-bg-secondary text-white anwp-text-center py-0 px-2 align-middle team__player-number team-roster__number">
							<?php echo esc_html( $roster_item['number'] ); ?>
						</td>
						<td class="position-relative team__player-photo-wrapper anwp-text-center team-roster__photo">
							<img class="team__player-photo" src="<?php echo esc_url( $roster_item['photo'] ?: $default_photo ); ?>" alt="player photo">
							<?php if ( 'in team' !== $roster_item['status'] ) : ?>
								<span class="badge badge-info font-weight-normal small text-uppercase team__player-status-badge"><?php echo esc_html( $roster_item['status'] ); ?></span>
							<?php endif; ?>
						</td>
						<td class="align-middle team__player-name-cell team-roster__player">
							<a class="team__player-name anwp-link-without-effects" href="<?php echo esc_url( $roster_item['link'] ); ?>">
								<?php echo esc_html( $roster_item['name'] ); ?>
							</a>
							<span class="team__player-role d-block team-roster__role"><?php echo esc_html( $roster_item['role'] ); ?></span>
						</td>
						<td class="px-2 team-roster__age">
							<span class="team__player-param-name d-block team-roster__age-label"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__roster__age', __( 'Age', 'sports-leagues' ) ) ); ?></span>
							<span class="mt-2 d-block team-roster__age-text"><?php echo esc_html( $roster_item['age'] ?: '-' ); ?></span>
						</td>
						<td class="text-right team-roster__nationality">
							<span class="team__player-param-name d-block"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__roster__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></span>
							<?php if ( ! empty( $roster_item['nationality'] ) && is_array( $roster_item['nationality'] ) ) : ?>
								<?php foreach ( $roster_item ['nationality'] as $country_code ) : ?>
									<span class="options__flag f32" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>"><span class="flag <?php echo esc_attr( $country_code ); ?>"></span></span>
								<?php endforeach; ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php

					if ( ! isset( $roster_output[ $last_group_index ]['items'] ) ) {
						$roster_output[ $last_group_index ]['items'] = [];
					}

					$roster_output[ $last_group_index ]['items'][] = ob_get_clean();
				endforeach;

				/*
				|--------------------------------------------------------------------
				| Generate output
				|--------------------------------------------------------------------
				*/
				foreach ( $roster_output as $output_group ) {
					if ( empty( $output_group['items'] ) ) {
						continue;
					}

					if ( ! empty( $output_group['title'] ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $output_group['title'];
					}

					foreach ( $output_group['items'] as $output_group_item ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $output_group_item;
					}
				}
				?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
