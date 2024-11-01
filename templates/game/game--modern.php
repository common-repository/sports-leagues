<?php
/**
 * The Template for displaying Game (modern version).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game--modern.php.
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

<div class="list-group-item tournament__game game-list--modern p-0 position-relative pb-1 game-list game-list--status-<?php echo absint( $data->finished ); ?>" data-sl-game="<?php echo intval( $data->game_id ); ?>"
	<?php if ( Sports_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
>

	<?php if ( $data->show_game_datetime ) : ?>
		<div class="game-list__kickoff anwp-bg-light px-2 py-1">
			<span class="game-list__date game__date-formatted"><?php echo esc_html( $data->game_date ); ?></span>

			<?php if ( 'TBD' !== $data->special_status ) : ?>
				- <span class="game-list__time game__time-formatted"><?php echo esc_html( $data->game_time ); ?></span>
			<?php endif; ?>

			<?php if ( $venue ) : ?>
				<span class="game-list__venue ml-3"><svg class="anwp-icon anwp-icon--s12"><use xlink:href="#icon-location"></use></svg><?php echo esc_html( $venue ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="d-flex align-items-center my-1 mx-2 mt-2">

		<?php if ( $data->home_logo ) : ?>
			<div class="team-logo__cover team-logo__cover--small" style="background-image: url('<?php echo esc_url( $data->home_logo ); ?>')"></div>
		<?php endif; ?>

		<?php if ( $data->show_team_name ) : ?>
			<span class="text-truncate d-inline-block mx-2"><?php echo esc_html( $data->home_abbr ); ?></span>
		<?php endif; ?>

		<span class="game-list__scores-number d-inline-block anwp-text-center ml-auto mr-0 game-list--home-score"><?php echo (int) $data->finished ? esc_html( $data->home_scores ) : '-'; ?></span>
	</div>

	<div class="d-flex align-items-center my-1 mx-2">

		<?php if ( $data->away_logo ) : ?>
			<div class="team-logo__cover team-logo__cover--small" style="background-image: url('<?php echo esc_url( $data->away_logo ); ?>')"></div>
		<?php endif; ?>

		<?php if ( $data->show_team_name ) : ?>
			<span class="text-truncate d-inline-block mx-2"><?php echo esc_html( $data->away_abbr ); ?></span>
		<?php endif; ?>

		<span class="game-list__scores-number d-inline-block anwp-text-center ml-auto mr-0 game-list--away-score"><?php echo (int) $data->finished ? esc_html( $data->away_scores ) : '-'; ?></span>
	</div>

	<?php if ( $data->aggtext || 'PST' === $data->special_status ) : ?>
		<div class="border-top pt-1 m-2">
			<?php
			if ( 'PST' === $data->special_status ) {
				echo '<span class="game-list__time-result text-muted mt-0 mx-1">' . esc_html( Sports_Leagues_Text::get_value( 'game__game__game_postponed', __( 'Game Postponed', 'sports-leagues' ) ) ) . '</span>';
			}

			if ( $data->aggtext ) {
				echo '<span class="game-list__time-result text-muted mt-0">' . esc_html( $data->aggtext ) . '</span>';
			}
			?>
		</div>
	<?php endif; ?>

	<a class="stretched-link anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
