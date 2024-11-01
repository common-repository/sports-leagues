<?php
/**
 * The Template for displaying Team Roster.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-roster-summary--grid.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.0
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

try {
	$roster_data  = [];
	$subteam_list = get_post_meta( $data->team_id, '_sl_subteam_list', true );

	if ( ! empty( $subteam_list ) && is_array( $subteam_list ) ) {
		foreach ( $subteam_list as $subteam_item ) {
			$roster_data[] = [
				'team_id' => $subteam_item['subteam'],
				'title'   => $subteam_item['title'],
				'roster'  => sports_leagues()->team->tmpl_prepare_team_roster( $subteam_item['subteam'], $data->season_id ),
			];
		}
	}
} catch ( Exception $e ) {
	return;
}

// Default photo
$default_photo = sports_leagues()->helper->get_default_player_photo();

/*
|--------------------------------------------------------------------
| Roster Output
|--------------------------------------------------------------------
*/
$roster_output = [];

foreach ( $roster_data as $team_squad_data ) {
	$active_group_slug = '_';

	foreach ( $team_squad_data['roster'] as $roster_item ) {
		if ( 'group' === $roster_item['type'] ) {
			$active_group_slug = $roster_item['title'];
			continue;
		}

		if ( '-' === mb_substr( $roster_item['status'], 0, 1 ) ) {
			continue;
		}

		$roster_item['subteam_id']    = $team_squad_data['team_id'];
		$roster_item['subteam_title'] = $team_squad_data['title'];

		$roster_output[ $active_group_slug ][] = $roster_item;
	}
}
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

	if ( empty( $roster_data ) ) :
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'shortcode__roster__no_players_in_roster', __( 'No players in roster', 'sports-leagues' ) ),
			],
			'general/no-data'
		);

	else :
		$root_team_title = get_post_meta( $data->team_id, '_sl_root_team_title', true );
		?>
		<div class="team-subteams pb-3 pt-1 d-flex flex-wrap">
			<div class="m-1 team-subteams__item team-subteams__roster anwp-sl-btn d-flex align-items-center position-relative py-0 team-subteams__item--active anwp-cursor-default"
					data-filter-value="">
				<?php echo esc_html( $root_team_title ); ?>
			</div>

			<?php foreach ( $subteam_list as $subteam_item ) : ?>
				<div class="m-1 team-subteams__item team-subteams__roster anwp-sl-btn d-flex align-items-center position-relative py-0" data-filter-value="<?php echo esc_attr( $subteam_item['subteam'] ); ?>">
					<?php echo esc_html( $subteam_item['title'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="anwp-grid-table team-roster-grid">
			<?php
			/*
			|--------------------------------------------------------------------
			| Prepare output
			|--------------------------------------------------------------------
			*/
			foreach ( $roster_output as $group_key => $roster_items ) :
				if ( '_' !== $group_key ) :
					?>
					<div class="team-roster-grid__header anwp-bg-light">
						<?php echo esc_html( sports_leagues()->config->get_name_by_id( 'roster_groups', $group_key ) ); ?>
					</div>
					<?php
				endif;

				foreach ( $roster_items as $roster_item ) :
					?>
					<div class="team-roster-grid__block position-relative d-flex flex-column anwp-border-light anwp-sl-hover" data-filter="<?php echo esc_html( $roster_item['subteam_id'] ); ?>">
						<div class="team-roster-grid__photo-wrapper anwp-text-center d-flex justify-content-between align-items-center anwp-bg-light anwp-border-light">
							<img loading="lazy" class="team-roster-grid__photo anwp-object-contain anwp-w-70 anwp-h-70" alt="<?php echo esc_attr( $roster_item['name'] ); ?>" src="<?php echo esc_url( $roster_item['photo'] ?: $default_photo ); ?>">
							<div class="team-roster-grid__player-number"><?php echo esc_html( $roster_item['number'] ); ?></div>
						</div>
						<div class="d-flex flex-column position-relative team-roster-grid__player-content">

							<div class="anwp-bg-gray-600 px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-sm anwp-leading-1-25 mx-auto mt-n2 text-white">
								<?php echo esc_html( $roster_item['subteam_title'] ); ?>
							</div>

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
				endforeach;
			endforeach;
			?>
		</div>
	<?php endif; ?>
</div>
