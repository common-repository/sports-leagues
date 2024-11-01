<?php
/**
 * The Template for displaying Game.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game.php.
 *
 * @var object $data - Object with args.
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
		'attendance'         => '',
		'away_link'          => '',
		'away_logo'          => '',
		'away_scores'        => '',
		'away_team'          => '',
		'away_title'         => '',
		'context'            => 'shortcode',
		'finished'           => '',
		'game_date'          => '',
		'game_day'           => '',
		'game_id'            => '',
		'game_time'          => '',
		'home_link'          => '',
		'home_logo'          => '',
		'home_scores'        => '',
		'home_team'          => '',
		'home_title'         => '',
		'kickoff'            => '',
		'permalink'          => '',
		'show_game_datetime' => true,
		'special_status'     => '',
		'stage_id'           => '',
		'tournament_id'      => '',
		'venue_id'           => '',
	]
);

$finished = Sports_Leagues::string_to_bool( $data->finished );

// Get header events
$header_events = sports_leagues()->event->get_game_events_to_render( $data->game_id, 'header' );
$temp_players  = sports_leagues()->game->get_temp_players( $data->game_id );
?>

<div class="game-header game-status__<?php echo esc_attr( $data->finished ); ?> anwp-section anwp-bg-light anwp-border anwp-border-light"
	data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>">

	<?php
	/*
	|--------------------------------------------------------------------
	| Header Top - Breadcrumbs (Tournament, Stage, GameDay)
	|--------------------------------------------------------------------
	*/
	?>
	<div class="game-header__top px-3 py-1 anwp-text-center">
		<a class="anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $data->tournament_id ) ); ?>"><?php echo esc_html( get_the_title( (int) $data->tournament_id ) ); ?></a>
		<span class="game-header__top-separator">></span>
		<a class="text-nowrap anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $data->stage_id ) ); ?>"><?php echo esc_html( get_the_title( (int) $data->stage_id ) ); ?></a>

		<?php if ( $data->game_day ) : ?>
			<span class="game-header__top-separator">></span>
			<span class="text-nowrap"><?php echo esc_html( Sports_Leagues_Text::get_value( 'game__header__game_day', __( 'Game Day', 'sports-leagues' ) ) ) . ': ' . esc_html( $data->game_day ); ?></span>
		<?php endif; ?>
	</div>

	<?php
	/*
	|--------------------------------------------------------------------
	| Date and Time
	|--------------------------------------------------------------------
	*/
	?>
	<div class="py-2 game-header__kickoff anwp-text-center d-flex align-items-center justify-content-center">
		<?php if ( $data->show_game_datetime ) : ?>
			<?php if ( $data->game_date ) : ?>
				<div class="mx-2 d-flex align-items-center">
					<svg class="anwp-icon mr-1">
						<use xlink:href="#icon-calendar"></use>
					</svg>
					<span class="game__date-formatted"><?php echo esc_html( $data->game_date ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $data->game_time && 'TBD' !== $data->special_status ) : ?>
				<div class="mx-2 d-flex align-items-center">
					<svg class="game-header__clock-icon anwp-icon anwp-icon--feather mr-1">
						<use xlink:href="#icon-clock-alt"></use>
					</svg>
					<span class="anwp-leading-1 game__time-formatted"><?php echo esc_html( $data->game_time ); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php
	/*
	|--------------------------------------------------------------------
	| Scores Block
	|--------------------------------------------------------------------
	*/
	?>
	<div class="game-header__main d-sm-flex">
		<div class="game-header__team-wrapper game-header__team-home anwp-flex-1 d-flex flex-sm-column align-items-center position-relative mb-3 mb-sm-0">
			<?php if ( $data->home_logo ) : ?>
				<a class="anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->home_link ); ?>">
					<img loading="lazy" width="80" height="80" class="anwp-object-contain game-header__team-logo anwp-flex-none mb-0 mx-3 anwp-w-80 anwp-h-80"
						src="<?php echo esc_url( $data->home_logo ); ?>" alt="<?php echo esc_attr( $data->home_title ); ?>">
				</a>
			<?php endif; ?>

			<div class="game-header__team-title anwp-leading-1 text-truncate anwp-text-sm-center pt-1 pb-2 anwp-break-word">
				<?php echo esc_html( $data->home_title ); ?>
			</div>

			<?php if ( 'hide' !== Sports_Leagues_Customizer::get_value( 'game', 'team_series_game_header' ) ) : ?>
				<div class="d-none d-sm-flex justify-content-center">
					<?php
					sports_leagues()->helper->get_team_form(
						[
							'team_id'        => $data->home_team,
							'kickoff_before' => $data->kickoff,
							'echo'           => true,
						]
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $finished ) : ?>
				<div class="game-header__scores-wrapper anwp-font-semibold d-inline-block d-sm-none ml-auto px-3">
					<span class="game-header__scores-number"><?php echo esc_html( $data->home_scores ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $finished ) : ?>
			<div class="game-header__scores-wrapper d-sm-flex align-items-center mx-2 mx-sm-4 anwp-font-semibold d-none">
				<span class="game-header__scores-number mx-1"><?php echo esc_html( $data->home_scores ); ?></span>
				<span class="game-header__scores-number mx-1"><?php echo esc_html( $data->away_scores ); ?></span>
			</div>
		<?php else : ?>
			<div class="d-sm-flex align-items-center mx-2 mx-sm-4 d-none">
				<span class="mx-1 game-header__scores-vs">vs</span>
			</div>
		<?php endif; ?>

		<div class="game-header__team-wrapper game-header__team-away anwp-flex-1 d-flex flex-sm-column align-items-center position-relative">
			<?php if ( $data->away_logo ) : ?>
				<a class="anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->away_link ); ?>">
					<img loading="lazy" width="80" height="80" class="anwp-object-contain game-header__team-logo anwp-flex-none mb-0 mx-3 anwp-w-80 anwp-h-80"
						src="<?php echo esc_url( $data->away_logo ); ?>" alt="<?php echo esc_attr( $data->away_title ); ?>">
				</a>
			<?php endif; ?>

			<div class="game-header__team-title anwp-leading-1 text-truncate anwp-text-sm-center pt-1 pb-2 anwp-break-word">
				<?php echo esc_html( $data->away_title ); ?>
			</div>

			<?php if ( 'hide' !== Sports_Leagues_Customizer::get_value( 'game', 'team_series_game_header' ) ) : ?>
				<div class="d-none d-sm-flex justify-content-center">
					<?php
					sports_leagues()->helper->get_team_form(
						[
							'team_id'        => $data->away_team,
							'kickoff_before' => $data->kickoff,
							'echo'           => true,
						]
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $finished ) : ?>
				<div class="game-header__scores-wrapper anwp-font-semibold d-inline-block d-sm-none ml-auto px-3">
					<span class="game-header__scores-number"><?php echo esc_html( $data->away_scores ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
	/*
	|--------------------------------------------------------------------
	| Header Events
	|--------------------------------------------------------------------
	*/
	?>
	<div class="d-flex px-sm-3 px-2 mb-4">
		<div class="anwp-flex-1 game-header__events">
			<?php
			if ( ! empty( $header_events[ $data->home_team ] ) && is_array( $header_events[ $data->home_team ] ) ) :
				foreach ( $header_events[ $data->home_team ] as $header_event ) :
					sports_leagues()->event->render_event( 'header', $header_event, 'home', $temp_players );
				endforeach;
			endif;
			?>
		</div>
		<div class="anwp-flex-1 game-header__events">
			<?php
			if ( ! empty( $header_events[ $data->away_team ] ) && is_array( $header_events[ $data->away_team ] ) ) :
				foreach ( $header_events[ $data->away_team ] as $header_event ) :
					sports_leagues()->event->render_event( 'header', $header_event, 'away', $temp_players );
				endforeach;
			endif;
			?>
		</div>
	</div>

	<?php
	/*
	|--------------------------------------------------------------------
	| Outcome Label
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->finished ) ) :

		// Render Period Scores
		echo sports_leagues()->game->render_period_scores( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Render Finished label
		?>
		<div class="game-header__finished-label anwp-text-center py-3">
			<span class="px-2 py-1">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__header__full_time', __( 'Full Time', 'sports-leagues' ) ) ); ?>
			</span>
		</div>
		<?php
	else :
		if ( 'PST' === $data->special_status ) :
			?>
			<div class="game-header__finished-label anwp-text-center py-3">
				<span class="px-2 py-1"><?php echo esc_html( Sports_Leagues_Text::get_value( 'game__game__game_postponed', __( 'Game Postponed', 'sports-leagues' ) ) ); ?></span>
			</div>
			<?php
		elseif ( '0000-00-00 00:00:00' !== $data->kickoff && $data->kickoff ) :
			sports_leagues()->load_partial( $data, 'game/game-countdown', 'modern' );
		endif;
	endif;

	/*
	|--------------------------------------------------------------------
	| Footer
	|--------------------------------------------------------------------
	*/
	?>
	<div class="game-header__footer py-1 px-2 mt-2 anwp-text-center">
		<?php
		// Game venue
		$venue = intval( $data->venue_id ) ? get_post( $data->venue_id ) : null;

		if ( $venue && 'publish' === $venue->post_status ) {
			echo '<svg class="anwp-icon mr-1 anwp-icon--s14"><use xlink:href="#icon-location"></use></svg>';
			echo '<a class="anwp-link anwp-link-without-effects" href="' . esc_url( get_permalink( $venue ) ) . '">' . esc_html( $venue->post_title ) . '</a>';
		}

		if ( (int) $data->attendance ) {
			echo '<span class="anwp-words-separator mx-2">|</span>';
			echo esc_html( Sports_Leagues_Text::get_value( 'game__header__attendance', __( 'Attendance', 'sports-leagues' ) ) ) . ': ';
			echo esc_html( number_format_i18n( (int) $data->attendance ) );
		}

		if ( $data->aggtext ) {
			echo '<span class="anwp-words-separator mx-2">|</span>';
			echo esc_html( $data->aggtext );
		}

		// Officials
		echo '<div>';
		sports_leagues()->official->render_game_officials( $data->game_id );
		echo '</div>';
		?>
	</div>
</div>
