<?php
/**
 * Sports Leagues :: Block > Game Countdown
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Game_Countdown {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		register_block_type(
			Sports_Leagues::dir( 'gutenberg/blocks/game-countdown' ),
			[
				'title'           => 'SL Game Countdown',
				'render_callback' => [ $this, 'server_side_render' ],
			]
		);
	}

	/**
	 * Register blocks.
	 *
	 * @param array    $attr           the block attributes
	 * @param string   $content        the block content
	 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered
	 */
	public function server_side_render( $attr, $content, $block_instance ) {

		$attr = wp_parse_args(
			$attr,
			[
				'tournament_id' => '',
				'team_id'       => '',
				'season_id'     => '',
				'exclude_ids'   => '',
				'include_ids'   => '',
				'offset'        => '',
				'label_size'    => '',
				'value_size'    => '',
			]
		);

		if ( empty( $attr['tournament_id'] ) && empty( $attr['team_id'] ) ) {
			ob_start();
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'Please specify Competition or Club ID', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$date_from = '';

		if ( function_exists( 'current_datetime' ) && empty( $attr['include_ids'] ) ) {
			$date_from = current_datetime()->format( 'Y-m-d' );
		}

		// Get competition matches
		$games = sports_leagues()->game->get_games_extended(
			[
				'tournament_id'  => $attr['tournament_id'],
				'season_id'      => $attr['season_id'],
				'finished'       => 0,
				'filter_by_team' => $attr['team_id'],
				'limit'          => 1,
				'sort_by_date'   => 'asc',
				'exclude_ids'    => $attr['exclude_ids'],
				'include_ids'    => $attr['include_ids'],
				'offset'         => $attr['offset'],
				'date_from'      => $date_from,
			]
		);

		if ( empty( $games ) || empty( $games[0]->game_id ) ) {
			return '';
		}

		$tmpl_data = [
			'kickoff'    => $games[0]->kickoff,
			'kickoff_c'  => date_i18n( 'c', strtotime( $games[0]->kickoff ) ),
			'label_size' => $attr['label_size'],
			'value_size' => $attr['value_size'],
		];

		ob_start();
		sports_leagues()->load_partial( $tmpl_data, 'game/game-countdown' );
		return ob_get_clean();
	}
}

return new Sports_Leagues_Block_Game_Countdown();
