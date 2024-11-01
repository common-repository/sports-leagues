<?php
/**
 * The Template for displaying Game (slim version).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game--slim.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.5
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
		'game_time'          => '',
		'home_abbr'          => '',
		'home_link'          => '',
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
		'tournament_logo'    => true,
		'venue_id'           => '',
		'special_status'     => '',
		'outcome_id'         => '',
		'datetime_tz'        => true,
	]
);

$venue = intval( $data->venue_id ) ? sports_leagues()->venue->get_venue_title_by_id( $data->venue_id ) : '';

/*
|--------------------------------------------------------------------
| Tournament Logo
|--------------------------------------------------------------------
*/
$tournament = (object) [
	'title' => '',
	'logo'  => '',
];

if ( Sports_Leagues::string_to_bool( $data->tournament_logo ) ) {
	$tournament->title = sports_leagues()->tournament->get_title( $data->tournament_id ) . ' ' . sports_leagues()->tournament->get_title( $data->stage_id );
	$tournament->logo  = get_the_post_thumbnail_url( $data->tournament_id );
}
?>
<div class="list-group-item tournament__game game-list d-flex flex-wrap flex-sm-nowrap no-gutters p-1 position-relative game-list--slim"
	<?php if ( Sports_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
>

	<?php if ( $tournament->title && $tournament->logo ) : ?>
		<div class="game-list__tournament anwp-flex-none p-1 d-sm-flex align-items-center d-none mr-2 anwp-w-30 anwp-h-30 anwp-box-content">
			<img data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $tournament->title ); ?>" alt="competition logo"
				class="anwp-object-contain anwp-w-30 anwp-h-30"
				src="<?php echo esc_url( $tournament->logo ); ?>"/>
		</div>
	<?php endif; ?>

	<?php if ( $data->show_game_datetime ) : ?>
		<div class="game-list__kickoff anwp-col-sm-auto anwp-col-12 d-flex flex-sm-column justify-content-sm-center">

			<?php if ( $tournament->title && $tournament->logo ) : ?>
				<div class="game-list__tournament anwp-flex-none p-1 d-sm-none mr-2 anwp-w-30 anwp-h-30 anwp-box-content anwp-bg-white border anwp-border-gray-700">
					<img data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $tournament->title ); ?>" alt="competition logo"
						class="anwp-object-contain anwp-w-30 anwp-h-30"
						src="<?php echo esc_url( $tournament->logo ); ?>"/>
				</div>
			<?php endif; ?>

			<div class="flex-grow-1 anwp-min-width-0 d-flex d-sm-block flex-wrap">
				<?php if ( $tournament->title ) : ?>
					<div class="game-list__tournament-title d-sm-none anwp-col-12 no-gutters text-truncate"><?php echo esc_attr( $tournament->title ); ?></div>
				<?php endif; ?>

				<?php if ( $venue ) : ?>
					<span class="game-list__venue d-block"><svg class="anwp-icon anwp-icon--s12"><use xlink:href="#icon-location"></use></svg><?php echo esc_html( $venue ); ?></span>
					<span class="mx-2 d-sm-none anwp-small-separator">|</span>
				<?php endif; ?>

				<?php if ( '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
					<span class="game-list__date d-block mr-2 game__date-formatted"><?php echo esc_html( $data->game_date ); ?></span>
					<?php if ( 'TBD' !== $data->special_status && '00:00' !== $data->game_time && $data->game_time ) : ?>
						<span class="game-list__time d-block game__time-formatted"><?php echo esc_html( $data->game_time ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php
			/**
			 * Render outcome.
			 *
			 * @since 0.10.23
			 */
			if ( $data->outcome_id ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo sports_leagues()->game->get_game_outcome_label( $data, 'd-sm-none d-flex anwp-flex-none align-items-center justify-content-center' );
			}
			?>

		</div>
	<?php endif; ?>

	<div class="anwp-row align-items-center no-gutters flex-grow-1">
		<div class="anwp-col-sm d-flex align-items-center flex-sm-row-reverse mb-1 mb-sm-0 align-self-stretch anwp-min-width-0">

			<?php if ( $data->home_logo ) : ?>
				<div class="team-logo__cover team-logo__cover--small ml-2 ml-sm-0" style="background-image: url('<?php echo esc_url( $data->home_logo ); ?>')"></div>
			<?php endif; ?>

			<div class="game-list__team d-inline-block text-sm-right anwp-text-truncate">
				<?php echo esc_html( 'title' === Sports_Leagues_Customizer::get_value( 'game_list', 'team_name_slim' ) ? $data->home_title : $data->home_abbr ); ?>
			</div>

			<div class="d-sm-none ml-auto anwp-text-center align-items-center mr-1">
				<span class="game-list__scores-number d-inline-block mr-0"><?php echo (int) $data->finished ? esc_html( $data->home_scores ) : '-'; ?></span>
			</div>

		</div>
		<div class="anwp-col-sm-auto game-list__scores d-none d-sm-inline-block anwp-text-center">

			<div class="d-flex align-items-center">
				<span class="game-list__scores-number d-inline-block mr-1"><?php echo (int) $data->finished ? esc_html( $data->home_scores ) : '-'; ?></span>
				<span class="game-list__scores-number d-inline-block"><?php echo (int) $data->finished ? esc_html( $data->away_scores ) : '-'; ?></span>
			</div>

		</div>
		<div class="anwp-col-sm d-flex align-self-stretch align-items-center anwp-min-width-0">

			<?php if ( $data->away_logo ) : ?>
				<div class="team-logo__cover team-logo__cover--small ml-2 ml-sm-0" style="background-image: url('<?php echo esc_url( $data->away_logo ); ?>')"></div>
			<?php endif; ?>

			<div class="game-list__team d-inline-block anwp-text-truncate">
				<?php echo esc_html( 'title' === Sports_Leagues_Customizer::get_value( 'game_list', 'team_name_slim' ) ? $data->away_title : $data->away_abbr ); ?>
			</div>

			<div class="d-sm-none ml-auto anwp-text-center d-flex align-items-start align-items-center mr-1">
				<span class="game-list__scores-number d-inline-block mr-0"><?php echo (int) $data->finished ? esc_html( $data->away_scores ) : '-'; ?></span>
			</div>
		</div>

		<?php
		/**
		 * Render outcome.
		 *
		 * @since 0.10.23
		 */
		if ( $data->outcome_id ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->game->get_game_outcome_label( $data, 'd-none d-sm-flex align-items-center justify-content-center' );
		}

		/**
		 * Inject extra actions info game slim.
		 * Hook: sports-leagues/tmpl-game-slim/extra_action
		 *
		 * @since 0.1.0
		 *
		 * @param object $data
		 */
		do_action( 'sports-leagues/tmpl-game-slim/extra_action', $data );

		?>
		<?php if ( $data->aggtext || ( 'PST' === $data->special_status && ! Sports_Leagues::string_to_bool( $data->finished ) ) ) : ?>
			<div class="anwp-col-12 game-list__time-result-wrapper text-sm-center my-1">
				<?php
				if ( 'PST' === $data->special_status && ! Sports_Leagues::string_to_bool( $data->finished ) ) {
					echo '<span class="game-list__time-result d-inline-block text-nowrap">' . esc_html( Sports_Leagues_Text::get_value( 'game__game__game_postponed', __( 'Game Postponed', 'sports-leagues' ) ) ) . '</span>';
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
