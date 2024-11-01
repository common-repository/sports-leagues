<?php
/**
 * The Template for displaying Team Roster.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-roster--grid.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.10
 *
 * @version       0.13.0
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
<div class="anwp-b-wrap roster roster--shortcode roster-grid <?php echo esc_attr( $data->class ); ?>">

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

		<div class="anwp-grid-table team-roster-grid">
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
					<div class="team-roster-grid__header anwp-bg-light">
						<?php echo esc_html( sports_leagues()->config->get_name_by_id( 'roster_groups', $roster_item['title'] ) ); ?>
					</div>
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
				<div class="team-roster-grid__block position-relative d-flex flex-column anwp-border-light anwp-sl-hover">
					<div class="team-roster-grid__photo-wrapper anwp-text-center d-flex justify-content-between align-items-center anwp-bg-light anwp-border-light">
						<img loading="lazy" class="team-roster-grid__photo anwp-object-contain anwp-w-70 anwp-h-70" alt="<?php echo esc_attr( $roster_item['name'] ); ?>" src="<?php echo esc_url( $roster_item['photo'] ?: $default_photo ); ?>">
						<div class="team-roster-grid__player-number"><?php echo esc_html( $roster_item['number'] ); ?></div>
					</div>
					<div class="d-flex flex-column position-relative team-roster-grid__player-content">

						<?php if ( 'in team' !== $roster_item['status'] && $roster_item['status'] ) : ?>
							<span class="team-roster-grid__status-badge anwp-bg-info anwp-text-white anwp-leading-1 anwp-text-sm anwp-text-center position-absolute"><?php echo esc_html( $roster_item['status'] ); ?></span>
						<?php endif; ?>

						<div class="team-roster-grid__name mb-auto"><?php echo esc_html( $roster_item['name'] ); ?></div>

						<?php if ( $roster_item['role'] ) : ?>
							<div class="team-roster-grid__player-param d-flex anwp-border-light team-roster-grid__role">
								<span class="team-roster-grid__player-param-title anwp-opacity-70 anwp-text-nowrap mr-2"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__roster__position', __( 'Position', 'sports-leagues' ) ) ); ?></span>
								<span class="team-roster-grid__player-param-value ml-auto anwp-leading-1-25 anwp-text-right"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $roster_item['role'] ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $roster_item['age'] ) : ?>
							<div class="team-roster-grid__player-param d-flex anwp-border-light team-roster-grid__age">
								<span class="team-roster-grid__player-param-title anwp-opacity-70"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__roster__age', __( 'Age', 'sports-leagues' ) ) ); ?></span>
								<span class="team-roster-grid__player-param-value ml-auto"><?php echo esc_html( $roster_item['age'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $roster_item['nationality'] ) && is_array( $roster_item['nationality'] ) ) : ?>
							<div class="team-roster-grid__player-param d-flex team-roster-grid__nationality">
								<span class="team-roster-grid__player-param-title anwp-opacity-70"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__roster__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></span>
								<div class="team-roster-grid__player-param-value ml-auto">
									<?php foreach ( $roster_item ['nationality'] as $country_code ) : ?>
										<span class="options__flag f32" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>"><span class="flag <?php echo esc_attr( $country_code ); ?>"></span></span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( $roster_item['link'] ); ?>" class="anwp-link-without-effects anwp-link-cover"></a>
				</div>
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
		</div>
	<?php endif; ?>
</div>
