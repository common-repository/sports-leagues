<?php
/**
 * Sports Leagues :: Block > Players Stats
 *
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Players_Stats {

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
			Sports_Leagues::dir( 'gutenberg/blocks/players-stats' ),
			[
				'title'           => 'SL Players Stats',
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

		$shortcode_attr = wp_parse_args(
			$attr,
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
				'limit'             => 10,
				'soft_limit'        => 0,
				'show_position'     => 1,
				'show_team'         => 1,
				'show_nationality'  => 1,
				'show_photo'        => 1,
				'show_games_played' => 0,
				'link_to_profile'   => 0,
				'class'             => '',
				'position'          => '',
				'show_full'         => 0,
			]
		);

		$boolean_attrs = [
			'soft_limit',
			'show_position',
			'show_team',
			'show_nationality',
			'show_photo',
			'show_games_played',
			'link_to_profile',
			'show_full',
		];

		foreach ( $boolean_attrs as $boolean_attr ) {
			$shortcode_attr[ $boolean_attr ] = Sports_Leagues::string_to_bool( $shortcode_attr[ $boolean_attr ] );
		}

		$html_output = sports_leagues()->template->shortcode_loader( 'players-stats', $shortcode_attr );

		if ( empty( $html_output ) && Sports_Leagues::is_editing_block_on_backend() ) {
			ob_start();
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'Output is empty. Try to change your arguments.', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		return $html_output;
	}
}

return new Sports_Leagues_Block_Players_Stats();
