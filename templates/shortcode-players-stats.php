<?php
/**
 * The Template for displaying Players Aggregate Statistics.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-players-stats.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports_Leagues/Templates
 * @since         0.7.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'stats_id'          => '',
		'game_id'           => '',
		'team_id'           => '',
		'tournament_id'     => '',
		'stage_id'          => '',
		'league_id'         => '',
		'season_id'         => '',
		'group_id'          => '',
		'round_id'          => '',
		'venue_id'          => '',
		'game_day'          => '',
		'order'             => '',
		'position'          => '',
		'limit'             => 0,
		'soft_limit'        => 0,
		'soft_limit_qty'    => '',
		'show_position'     => 1,
		'show_team'         => 1,
		'show_nationality'  => 1,
		'show_photo'        => 1,
		'show_games_played' => 0,
		'link_to_profile'   => 0,
		'context'           => 'shortcode',
		'show_full'         => 0,
	]
);

if ( '' === $data->stats_id ) {
	return;
}

// Try to get from cache
$cache_key = 'SL-SHORTCODE_players-stats__' . md5( maybe_serialize( $data ) );

if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
	$players = sports_leagues()->cache->get( $cache_key, 'sl_game' );
} else {
	// Load data in default way
	$players = sports_leagues()->player_stats->get_players_aggregate_stats( $data );

	// Save transient
	if ( ! empty( $players ) ) {
		sports_leagues()->cache->set( $cache_key, $players, 'sl_game' );
	}
}

if ( empty( $players ) ) {
	return;
}

$stats_column     = sports_leagues()->player_stats->get_stats_player_game_column_by_id( $data->stats_id );
$default_photo    = sports_leagues()->helper->get_default_player_photo();
$player_photo_map = sports_leagues()->player->get_player_photo_map();
?>
<div class="anwp-b-wrap players-stats players-stats--context-<?php echo esc_attr( $data->context ); ?>">
	<table class="table table-sm table-bordered mb-0">
		<tbody>
		<tr class="anwp-bg-light">
			<th class="anwp-text-center"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__players_stats__rank', __( '#', 'sports-leagues' ) ) ); ?></th>
			<th class="pl-2 w-100"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__players_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>

			<?php if ( Sports_Leagues::string_to_bool( $data->show_games_played ) ) : ?>
				<th class="anwp-text-center"
					data-toggle="anwp-sl-tooltip"
					data-tippy-content="<?php echo esc_attr( Sports_Leagues_Text::get_value( 'shortcode__players_stats__games_played', __( 'Games Played', 'sports-leagues' ) ) ); ?>">
					<?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__players_stats__gp', __( 'GP', 'sports-leagues' ) ) ); ?>
				</th>
			<?php endif; ?>

			<th class="anwp-text-center" data-toggle="<?php echo $stats_column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $stats_column->name ); ?>">
				<?php echo esc_html( $stats_column->abbr ); ?>
			</th>
		</tr>
		</tbody>

		<tbody>
		<?php foreach ( $players as $index => $player ) : ?>
			<tr class="anwp-text-center">
				<td class="player-list__rank text-nowrap px-2">
					<?php echo intval( $index + 1 ); ?>
				</td>
				<td class="text-left text-truncate anwp-max-width-1">
					<div class="d-flex">
						<?php
						/*
						|--------------------------------------------------------------------
						| Player Photo
						|--------------------------------------------------------------------
						*/
						if ( Sports_Leagues::string_to_bool( $data->show_photo ) ) :
							$player_photo = isset( $player_photo_map[ $player->ID ] ) ? $player_photo_map[ $player->ID ] : $default_photo;

							?>
							<div class="player-list__photo position-relative player-photo__wrapper anwp-text-center mr-1">
								<img class="player-photo__img" src="<?php echo esc_url( $player_photo ); ?>" alt="<?php echo esc_attr( $player->player_name ); ?>">
							</div>
						<?php endif; ?>

						<div class="text-truncate d-flex flex-column">
							<?php
							/*
							|--------------------------------------------------------------------
							| Player Name (with link to profile or without)
							|--------------------------------------------------------------------
							*/
							?>
							<div class="d-flex align-items-center">
								<?php if ( Sports_Leagues::string_to_bool( $data->link_to_profile ) && $player->link ) : ?>
									<a class="anwp-link d-block text-truncate mt-1 player-list__link-name" title="<?php echo esc_attr( $player->player_name ); ?>"
										href="<?php echo esc_url( $player->link ); ?>"><?php echo esc_html( $player->player_name ); ?>
									</a>
								<?php else : ?>
									<span class="player-list__name"><?php echo esc_html( $player->player_name ); ?></span>
								<?php endif; ?>

								<?php
								/*
								|--------------------------------------------------------------------
								| Nationality
								|--------------------------------------------------------------------
								*/
								if ( Sports_Leagues::string_to_bool( $data->show_nationality ) && ! empty( $player->player_nationality ) ) :
									$countries = maybe_unserialize( $player->player_nationality );

									$extra_class = ( Sports_Leagues::string_to_bool( $data->link_to_profile ) && $player->link ) ? 'mb-n1' : '';

									if ( ! empty( $countries ) && is_array( $countries ) ) :
										foreach ( $countries as $country_code ) :
											?>
											<span class="options__flag f16 mx-2 mt-1 <?php echo esc_attr( $extra_class ); ?>" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>"><span class="flag <?php echo esc_attr( $country_code ); ?>"></span></span>
											<?php
										endforeach;
									endif;
								endif;
								?>
							</div>
							<div class="d-flex flex-wrap small align-items-center">
								<?php
								/*
								|--------------------------------------------------------------------
								| Player Team(s)
								|--------------------------------------------------------------------
								*/
								if ( Sports_Leagues::string_to_bool( $data->show_team ) ) :

									$teams = explode( ',', $player->teams );

									foreach ( $teams as $ii => $team ) :
										$team_data = sports_leagues()->team->get_team_by_id( $team );
										if ( $team_data ) :
											?>
											<div class="player-list__team d-flex align-items-center mt-1 <?php echo $ii ? 'ml-2' : ''; ?>">
												<?php if ( $team_data->logo ) : ?>
													<span class="team-logo__cover team-logo__cover--mini mr-1 align-middle" style="background-image: url('<?php echo esc_url( $team_data->logo ); ?>')"></span>
												<?php endif; ?>
												<?php echo esc_html( $team_data->title ); ?>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php
								/*
								|--------------------------------------------------------------------
								| Player Position
								|--------------------------------------------------------------------
								*/
								if ( Sports_Leagues::string_to_bool( $data->show_position ) && $player->player_position ) :
									if ( Sports_Leagues::string_to_bool( $data->show_team ) ) {
										echo '<span class="text-muted mx-2 mt-1 anwp-separator">|</span>';
									}
									?>
									<span class="player-list__position text-muted mt-1"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player->player_position ) ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</td>

				<?php if ( Sports_Leagues::string_to_bool( $data->show_games_played ) ) : ?>
					<td class="player-list__stat player-list__stat-gp text-nowrap px-2">
						<?php echo esc_html( $player->gp ); ?>
					</td>
				<?php endif; ?>

				<td class="player-list__stat text-nowrap px-2">
					<?php if ( 'simple' === $stats_column->type ) : ?>
						<?php echo esc_html( $player->qty ); ?>
					<?php elseif ( 'time' === $stats_column->type ) : ?>
						<div class="d-flex">
							<span class="player-list__stat-minutes"><?php echo esc_html( absint( $player->qty / 3600 ) ); ?></span>
							<span class="player-list__stat-seconds"><?php echo esc_html( date( ':s', mktime( 0, 0, $player->qty / 60 ) ) ); ?></span>
						</div>
					<?php elseif ( 'calculated' === $stats_column->type ) : ?>

						<?php if ( ! empty( $stats_column->prefix ) ) : ?>
							<span class="player-list__stat-prefix"><?php echo esc_html( $stats_column->prefix ); ?></span>
						<?php endif; ?>

						<?php echo esc_html( number_format( $player->qty, absint( $stats_column->digits ), '.', '' ) ); ?>

						<?php if ( ! empty( $stats_column->postfix ) ) : ?>
							<span class="player-list__stat-prefix"><?php echo esc_html( $stats_column->postfix ); ?></span>
						<?php endif; ?>

					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $players ) && Sports_Leagues::string_to_bool( $data->show_full ) ) : ?>
		<div class="anwp-bg-light anwp-border anwp-border-light anwp-border-top-0">
			<a class="d-flex align-items-center p-2 justify-content-center text-decoration-none anwp-link-without-effects anwp-sl-modal-stat-players-list"
				href="#" data-anwp-args="<?php echo esc_attr( sports_leagues()->player->get_serialized_stat_players_data( $data ) ); ?>">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'players__stats__show_full_list', __( 'Show full list', 'sports-leagues' ) ) ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
