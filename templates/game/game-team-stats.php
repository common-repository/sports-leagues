<?php
/**
 * The Template for displaying Game >> Team Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-team-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.3
 *
 * @version       0.10.0
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_team'       => '',
		'away_team'       => '',
		'home_logo'       => '',
		'away_logo'       => '',
		'home_title'      => '',
		'away_title'      => '',
		'team_stats_home' => '',
		'team_stats_away' => '',
		'header'          => true,
		'prev_layout'     => '',
	]
);

if ( empty( (array) $data->team_stats_home ) && empty( (array) $data->team_stats_away ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Try to load custom layout
|--------------------------------------------------------------------
*/
$custom_layout = Sports_Leagues_Customizer::get_value( 'layout', 'game_teams_stats' );

if ( ! empty( $custom_layout ) && $data->prev_layout !== $custom_layout ) {
	$data->prev_layout = $custom_layout;
	return sports_leagues()->load_partial( $data, 'game/game-team-stats', $custom_layout );
}


$color_home = sports_leagues()->team->get_team_color( $data->home_team, true );
$color_away = sports_leagues()->team->get_team_color( $data->away_team, false );

$stats_config = Sports_Leagues_Config::get_value( 'game_team_stats', [] );

ob_start();
foreach ( $stats_config as $stat_group_data ) {

	$stat_group = $stat_group_data['id'];

	if ( ( ! isset( $data->team_stats_home->{$stat_group} ) || '' === $data->team_stats_home->{$stat_group} ) && ( ! isset( $data->team_stats_away->{$stat_group} ) || '' === $data->team_stats_away->{$stat_group} ) ) {
		continue;
	}

	// Values
	$value_home  = isset( $data->team_stats_home->{$stat_group} ) ? $data->team_stats_home->{$stat_group} : 0;
	$value_away  = isset( $data->team_stats_away->{$stat_group} ) ? $data->team_stats_away->{$stat_group} : 0;
	$value_total = intval( $value_home ) + intval( $value_away );

	// Check for empty values
	$value_home = '' === $value_home ? 0 : $value_home;
	$value_away = '' === $value_away ? 0 : $value_away;

	// Width
	$width_home = $value_total ? intval( intval( $value_home ) / $value_total * 100 ) : 0;
	$width_away = $value_total ? ( 100 - $width_home ) : 0;
	?>
	<div class="team-stats">
		<div class="anwp-text-center team-stats__title mt-3"><?php echo esc_html( $stat_group_data['name'] ); ?></div>
		<div class="anwp-row anwp-no-gutters mt-0">
			<div class="anwp-col-auto h5 m-0 team-stats__value"><span class="text-nowrap"><?php echo esc_html( $value_home ); ?></span></div>
			<div class="anwp-col mx-1 d-flex align-items-center">
				<?php if ( $width_home ) : ?>
					<div class="team-stats__bar" style="width: <?php echo intval( $width_home ); ?>%; background-color: <?php echo esc_attr( $color_home ); ?>"></div>
				<?php endif; ?>
				<?php if ( $width_away ) : ?>
					<div class="team-stats__bar" style="width: <?php echo intval( $width_away ); ?>%; background-color: <?php echo esc_attr( $color_away ); ?>"></div>
				<?php endif; ?>
				<?php if ( 0 === $value_total ) : ?>
					<div class="team-stats__bar w-100 team-stats__bar-empty"></div>
				<?php endif; ?>
			</div>
			<div class="anwp-col-auto h5 m-0 team-stats__value text-right"><span class="text-nowrap"><?php echo esc_html( $value_away ); ?></span></div>
		</div>
	</div>
	<?php
}

$stats_html = ob_get_clean();

if ( empty( $stats_html ) ) {
	return;
}

/**
 * Hook: sports-leagues/tmpl-game/team_stats_before
 *
 * @param object $data Game data
 *
 * @since 0.5.3
 */
do_action( 'sports-leagues/tmpl-game/team_stats_before', $data );
?>
<div class="anwp-section game__team-stats">

	<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header mb-0">
			<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__team_stats__team_statistics', __( 'Team Statistics', 'sports-leagues' ) ) ); ?>
		</div>
	<?php endif; ?>

	<div class="anwp-row">
		<div class="anwp-col-sm">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->helper->render_team_header( $data->home_logo, $data->home_title, $data->home_team, true );
			?>
		</div>
		<div class="anwp-col-sm">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->helper->render_team_header( $data->away_logo, $data->away_title, $data->away_team, false );
			?>
		</div>
	</div>

	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $stats_html;
	?>
</div>
