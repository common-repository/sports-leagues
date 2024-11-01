<?php
/**
 * Sports Leagues :: Block > Games
 *
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Games {

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
			Sports_Leagues::dir( 'gutenberg/blocks/games' ),
			[
				'title'           => 'SL Games',
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
				'tournament_id'         => '',
				'stage_id'              => '',
				'season_id'             => '',
				'league_id'             => '',
				'group_id'              => '',
				'round_id'              => '',
				'venue_id'              => '',
				'date_from'             => '',
				'date_to'               => '',
				'finished'              => '',
				'filter_by_team'        => '',
				'filter_by_game_day'    => '',
				'limit'                 => 10,
				'days_offset'           => '',
				'days_offset_to'        => '',
				'priority'              => '',
				'sort_by_date'          => '',
				'sort_by_game_day'      => '',
				'group_by'              => '',
				'group_by_header_style' => '',
				'show_team_logo'        => 1,
				'show_game_datetime'    => 1,
				'tournament_logo'       => 1,
				'class'                 => '',
				'exclude_ids'           => '',
				'include_ids'           => '',
				'outcome_id'            => '',
				'header_style'          => 'header',
				'header_class'          => '',
				'show_load_more'        => 0,
				'game_layout'           => 'slim',
			]
		);

		$boolean_attrs = [
			'show_team_logo',
			'show_game_datetime',
			'tournament_logo',
			'show_load_more',
		];

		foreach ( $boolean_attrs as $boolean_attr ) {
			$shortcode_attr[ $boolean_attr ] = Sports_Leagues::string_to_bool( $shortcode_attr[ $boolean_attr ] );
		}

		$html_output = sports_leagues()->template->shortcode_loader( 'games', $shortcode_attr );

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

return new Sports_Leagues_Block_Games();
