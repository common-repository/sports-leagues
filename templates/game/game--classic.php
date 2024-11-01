<?php
/**
 * The Template for displaying Game (classic version).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game--classic.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author         Andrei Strekozov <anwp.pro>
 * @package        Sports-Leagues/Templates
 * @since          0.5.9
 *
 * @version        0.12.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'aggtext'            => '',
		'away_abbr'          => '',
		'away_link'          => '',
		'away_logo'          => '',
		'away_scores'        => '',
		'away_team'          => '',
		'away_title'         => '',
		'finished'           => '',
		'game_date'          => '',
		'game_id'            => '',
		'game_time'          => '',
		'home_link'          => '',
		'home_abbr'          => '',
		'home_logo'          => '',
		'home_scores'        => '',
		'home_team'          => '',
		'home_title'         => '',
		'kickoff'            => '',
		'permalink'          => '',
		'show_game_datetime' => true,
		'show_team_name'     => true,
		'stage_id'           => '',
		'tournament_id'      => '',
		'tournament_logo'    => false,
		'venue_id'           => '',
		'special_status'     => '',
		'datetime_tz'        => true,
	]
);

$venue = intval( $data->venue_id ) ? sports_leagues()->venue->get_venue_title_by_id( $data->venue_id ) : '';
?>

<div class="list-group-item tournament__game game-list--classic p-0 position-relative game-list game-list--status-<?php echo absint( $data->finished ); ?>" data-sl-game="<?php echo intval( $data->game_id ); ?>"
	<?php if ( Sports_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
>

	<?php if ( $data->show_game_datetime ) : ?>
		<div class="game-list__kickoff anwp-bg-light text-sm-center p-2 py-sm-0">
			<span class="game-list__date game__date-formatted"><?php echo esc_html( $data->game_date ); ?></span>

			<?php if ( 'TBD' !== $data->special_status ) : ?>
				- <span class="game-list__time game__time-formatted"><?php echo esc_html( $data->game_time ); ?></span>
			<?php endif; ?>

			<?php if ( $venue ) : ?>
				<span class="game-list__venue ml-3"><svg class="anwp-icon anwp-icon--s12"><use xlink:href="#icon-location"></use></svg><?php echo esc_html( $venue ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="anwp-row anwp-no-gutters p-1">

		<div class="anwp-col-6 d-flex align-items-center">
			<?php if ( $data->home_logo ) : ?>
				<?php if ( $data->show_team_name ) : ?>
					<div class="team-logo__cover team-logo__cover--small" style="background-image: url('<?php echo esc_url( $data->home_logo ); ?>')"></div>
				<?php else : ?>
					<div class="d-flex align-items-center align-self-stretch my-1 justify-content-center flex-grow-1">
						<div class="team-logo__cover team-logo__cover--large" style="background-image: url('<?php echo esc_url( $data->home_logo ); ?>')"></div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $data->show_team_name ) : ?>
				<div class="flex-grow-1 game-list__team anwp-text-center mx-1 text-truncate mt-1"><?php echo esc_html( $data->home_abbr ); ?></div>
			<?php endif; ?>

			<div class="ml-auto anwp-text-center game-list__scores-number mr-1 game-list--home-score"><?php echo (int) $data->finished ? esc_html( $data->home_scores ) : '-'; ?></div>
		</div>

		<div class="anwp-col-6 d-flex align-items-center">

			<div class="ml-1 anwp-text-center game-list__scores-number game-list--away-score"><?php echo (int) $data->finished ? esc_html( $data->away_scores ) : '-'; ?></div>

			<?php if ( $data->show_team_name ) : ?>
				<div class="flex-grow-1 game-list__team anwp-text-center mx-1 text-truncate mt-1"><?php echo esc_html( $data->away_abbr ); ?></div>
			<?php endif; ?>

			<?php if ( $data->away_logo ) : ?>
				<?php if ( $data->show_team_name ) : ?>
					<div class="team-logo__cover team-logo__cover--small ml-auto" style="background-image: url('<?php echo esc_url( $data->away_logo ); ?>')"></div>
				<?php else : ?>
					<div class="d-flex align-items-center align-self-stretch my-1 justify-content-center flex-grow-1">
						<div class="team-logo__cover team-logo__cover--large" style="background-image: url('<?php echo esc_url( $data->away_logo ); ?>')"></div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( $data->aggtext || 'PST' === $data->special_status ) : ?>
			<div class="anwp-col-12 game-list__time-result-wrapper text-sm-center pl-2 pl-sm-0">
				<?php
				if ( 'PST' === $data->special_status ) {
					echo '<span class="game-list__time-result d-inline-block text-nowrap mx-1">' . esc_html( Sports_Leagues_Text::get_value( 'game__game__game_postponed', __( 'Game Postponed', 'sports-leagues' ) ) ) . '</span>';
				}

				if ( $data->aggtext ) {
					echo '<span class="game-list__time-result d-inline-block text-nowrap">' . esc_html( $data->aggtext ) . '</span>';
				}
				?>
			</div>
		<?php endif; ?>
	</div>

	<a class="stretched-link anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
