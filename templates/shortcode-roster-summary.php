<?php
/**
 * The Template for displaying Team Roster.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-roster-summary.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.0
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
		<div class="table-responsive">
			<table class="w-100 team__roster team-roster team__roster--table table">
				<tbody>
				<?php
				/*
				|--------------------------------------------------------------------
				| Prepare output
				|--------------------------------------------------------------------
				*/
				foreach ( $roster_output as $group_key => $roster_items ) :
					if ( '_' !== $group_key ) :
						?>
						<tr class="anwp-bg-light text-dark team-roster__th-wrapper">
							<td class="anwp-text-center px-0 align-middle team-roster__th-number">##</td>
							<td class="px-2 text-uppercase team-roster__th" colspan="4">
								<?php echo esc_html( sports_leagues()->config->get_name_by_id( 'roster_groups', $group_key ) ); ?>
							</td>
						</tr>
						<?php
					endif;

					foreach ( $roster_items as $roster_item ) :
						?>
						<tr data-filter="<?php echo esc_html( $roster_item['subteam_id'] ); ?>">
							<td class="anwp-bg-secondary text-white anwp-text-center py-0 px-2 align-middle team__player-number team-roster__number">
								<?php echo esc_html( $roster_item['number'] ); ?>
							</td>
							<td class="position-relative team__player-photo-wrapper anwp-text-center team-roster__photo">
								<img class="team__player-photo" src="<?php echo esc_url( $roster_item['photo'] ?: $default_photo ); ?>" alt="player photo">
							</td>
							<td class="align-middle team__player-name-cell team-roster__player">
								<a class="team__player-name anwp-link-without-effects" href="<?php echo esc_url( $roster_item['link'] ); ?>">
									<?php echo esc_html( $roster_item['name'] ); ?>
								</a>
								<span class="anwp-bg-gray-200 px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-xs anwp-leading-1-25">
									<?php echo esc_html( $roster_item['subteam_title'] ); ?>
								</span>
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
					endforeach;
				endforeach;
				?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
