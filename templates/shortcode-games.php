<?php
/**
 * The Template for displaying Games Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-games.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports_Leagues
 * @since         0.5.5
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'tournament_id'      => '',
		'stage_id'           => '',
		'season_id'          => '',
		'league_id'          => '',
		'group_id'           => '',
		'round_id'           => '',
		'venue_id'           => '',
		'date_from'          => '',
		'date_to'            => '',
		'finished'           => '',
		'filter_by_team'     => '',
		'filter_by_game_day' => '',
		'limit'              => '',
		'days_offset'        => '',
		'days_offset_to'     => '',
		'priority'           => '',
		'sort_by_date'       => '',
		'sort_by_game_day'   => '',
		'group_by'           => '',
		'show_team_logo'     => 1,
		'show_team_name'     => 1,
		'show_game_datetime' => 1,
		'tournament_logo'    => 1,
		'class'              => '',
		'game_layout'        => 'slim',
		'context'            => 'shortcode',
		'kickoff_before'     => '',
		'exclude_ids'        => '',
		'include_ids'        => '',
		'outcome_id'         => '',
		'header_style'       => 'header',
		'header_class'       => '',
		'load_more_per_load' => 20,
		'show_load_more'     => false,
	]
);

/*
|--------------------------------------------------------------------
| Prepare Load More
|--------------------------------------------------------------------
*/
$show_load_more = Sports_Leagues::string_to_bool( $args->show_load_more );

if ( $show_load_more && ( ! absint( $args->limit ) || $args->include_ids ) ) {
	$show_load_more = false;
}

if ( $show_load_more ) {
	$args->limit ++;
}

// Get games
$games = sports_leagues()->game->get_games_extended( $args );

// Post getting grid posts
if ( $show_load_more ) {
	$args->limit --;
	$show_load_more = count( $games ) > $args->limit;

	if ( $show_load_more ) {
		array_pop( $games );
	}
}

// Update "load more"
$data->show_load_more = $show_load_more;

if ( empty( $games ) ) {
	return;
}

if ( ! in_array( $args->header_style, [ 'header', 'subheader' ], true ) ) {
	$args->header_style = 'header';
}
?>
<div class="anwp-b-wrap game-list game-list--<?php echo esc_attr( $args->context ); ?> <?php echo esc_attr( $args->class ); ?>">
	<div class="list-group">

		<?php
		$group_current = '';

		foreach ( $games as $ii => $game ) :
			if ( '' !== $args->group_by ) {

				$group_text = '';

				/*
				|--------------------------------------------------------------------
				| Group Options
				|--------------------------------------------------------------------
				*/
				if ( 'round_stage' === $args->group_by && ( $game->round_id . '_' . $game->stage_id ) !== $group_current ) {
					/*
					|--------------------------------------------------------------------
					| Round >> Stage
					|--------------------------------------------------------------------
					*/
					$tournament_obj = sports_leagues()->tournament->get_tournament( $game->tournament_id );
					$group_current  = $game->round_id . '_' . $game->stage_id;
					$group_text_arr = [];

					$stage_obj = array_values( wp_list_filter( $tournament_obj->stages, [ 'id' => $game->stage_id ] ) )[0];

					if ( $stage_obj ) {
						$group_text_arr[] = $stage_obj->title;

						$rounds_obj = array_values( wp_list_filter( $stage_obj->rounds, [ 'id' => $game->round_id ] ) )[0];

						if ( $rounds_obj && $rounds_obj->title && __( 'Round Title', 'sports-leagues' ) !== $rounds_obj->title && $rounds_obj->title !== $stage_obj->title ) {
							array_unshift( $group_text_arr, $rounds_obj->title );
						}
					}

					if ( ! empty( $group_text_arr ) ) {
						$group_text = implode( ' - ', $group_text_arr );
					}
				} elseif ( 'stage' === $args->group_by && $group_current !== $game->stage_id && intval( $game->stage_id ) ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Stage
					|--------------------------------------------------------------------
					*/
					$stage_post = get_post( $game->stage_id );

					if ( $stage_post ) {
						$group_text    = $stage_post->post_title;
						$group_current = $game->stage_id;
					}
				} elseif ( in_array( $args->group_by, [ 'game_day', 'gameday' ], true ) && $group_current !== $game->game_day && intval( $game->game_day ) ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Game Day
					|--------------------------------------------------------------------
					*/
					$group_text    = esc_html( Sports_Leagues_Text::get_value( 'shortcode__games__game_day', __( 'Game Day', 'sports-leagues' ) ) ) . ': ' . esc_html( $game->game_day );
					$group_current = $game->game_day;
				} elseif ( 'day' === $args->group_by ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Day
					|--------------------------------------------------------------------
					*/
					$day_to_compare = date( 'Y-m-d', strtotime( $game->kickoff ) );

					if ( $day_to_compare !== $group_current ) {
						$group_text    = date( 'j M Y', strtotime( $game->kickoff ) );
						$group_current = $day_to_compare;
					}
				} elseif ( 'month' === $args->group_by ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Month
					|--------------------------------------------------------------------
					*/
					$month_to_compare = date( 'Y-m', strtotime( $game->kickoff ) );

					if ( $month_to_compare !== $group_current ) {
						$group_text    = date( 'M Y', strtotime( $game->kickoff ) );
						$group_current = $month_to_compare;
					}
				}

				if ( $group_text ) {
					sports_leagues()->load_partial(
						[
							'text'  => $group_text,
							'class' => $args->header_class,
						],
						'general/' . sanitize_key( $args->header_style )
					);
				}
			}

			// Get game data to render
			$game_data = sports_leagues()->game->prepare_tmpl_game_data( $game, $args );

			sports_leagues()->load_partial( $game_data, 'game/game', $args->game_layout ?: 'slim' );

		endforeach;
		?>
	</div>

	<?php if ( $show_load_more ) : ?>
		<div class="anwp-sl-btn-wrapper d-flex justify-content-center mt-3">
			<div class="anwp-sl-btn anwp-cursor-pointer anwp-sl-btn__load-more d-flex align-items-center"
				data-sl-loaded-qty="<?php echo absint( count( $games ) ); ?>"
				data-sl-group="<?php echo esc_attr( $group_current ); ?>"
				data-sl-games-per-load="<?php echo absint( $args->load_more_per_load ); ?>"
				data-sl-load-more="<?php echo esc_attr( sports_leagues()->game->get_serialized_load_more_data( $args ) ); ?>"
			>
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'general__load_more', __( 'Load More', 'sports-leagues' ) ) ); ?>
				<img class="ml-2 my-n2 anwp-sl-spinner" src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>" alt="spinner">
			</div>
		</div>
	<?php endif; ?>
</div>
