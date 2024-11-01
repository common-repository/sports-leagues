<?php
/**
 * The Template for displaying Game (Card A).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game--card-a.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.13
 *
 * @version       0.12.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'away_abbr'          => '',
		'away_logo'          => '',
		'away_scores'        => '',
		'game_id'            => '',
		'finished'           => '',
		'home_abbr'          => '',
		'home_logo'          => '',
		'home_scores'        => '',
		'permalink'          => '',
		'show_team_name'     => '',
		'show_game_datetime' => '',
		'stage_id'           => '',
		'tournament_id'      => '',
		'kickoff'            => '',
		'game_date'          => '',
		'special_status'     => '',
		'datetime_tz'        => true,
	]
);
?>

<div class="game-card anwp-w-200 anwp-w-min-200 game-card--a py-1 px-2 d-flex flex-column position-relative game-list game-list--status-<?php echo absint( $data->finished ); ?>"
	data-sl-game="<?php echo intval( $data->game_id ); ?>"
	<?php if ( Sports_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"  data-sl-date-format="v2"
	<?php endif; ?>
>

	<div class="game-card__header anwp-text-center">
		<div class="game-card__header-item text-truncate anwp-text-center"><?php echo esc_html( sports_leagues()->tournament->get_title( $data->tournament_id ) ); ?></div>
		<div class="game-card__header-item text-truncate anwp-text-center"><?php echo esc_html( sports_leagues()->tournament->get_title( $data->stage_id ) ); ?></div>
	</div>

	<div class="d-flex no-gutters my-2">

		<div class="anwp-flex-1 anwp-text-center anwp-min-width-0">
			<?php if ( $data->home_logo ) : ?>
				<div class="team-logo__cover team-logo__cover--xlarge mx-auto" style="background-image: url('<?php echo esc_url( $data->home_logo ); ?>')"></div>
			<?php endif; ?>

			<?php if ( $data->show_team_name ) : ?>
				<div class="game-card__team-title text-truncate">
					<?php echo esc_html( $data->home_abbr ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="anwp-flex-none anwp-text-center text-nowrap text-monospace game-card__scores">
			<span class="d-inline-block ml-1 game-list--home-score"><?php echo (int) $data->finished ? esc_html( $data->home_scores ) : '-'; ?></span><span>:</span><span class="d-inline-block mr-1 game-list--away-score"><?php echo (int) $data->finished ? esc_html( $data->away_scores ) : '-'; ?></span>
		</div>

		<div class="anwp-flex-1 anwp-text-center anwp-min-width-0">
			<?php if ( $data->away_logo ) : ?>
				<div class="team-logo__cover team-logo__cover--xlarge mx-auto" style="background-image: url('<?php echo esc_url( $data->away_logo ); ?>')"></div>
			<?php endif; ?>

			<?php if ( $data->show_team_name ) : ?>
				<div class="game-card__team-title text-truncate">
					<?php echo esc_html( $data->away_abbr ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $data->show_game_datetime && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
		<div class="game-card__footer anwp-bg-light anwp-text-center mt-auto d-flex justify-content-center mt-2 game-list--kickoff">
			<?php if ( 'PST' === $data->special_status ) : ?>
				<span class="game-card__date">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__game__game_postponed', __( 'Game Postponed', 'sports-leagues' ) ) ); ?>
				</span>
			<?php else : ?>
				<span class="game-card__date game__date-formatted"><?php echo esc_html( $data->game_date ); ?></span>
				<?php if ( 'TBD' !== $data->special_status ) : ?>
					<span class="mx-1">-</span>
					<span class="game-card__time game__time-formatted"><?php echo esc_html( $data->game_time ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<a class="stretched-link anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
