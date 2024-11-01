<?php
/**
 * Sports Leagues :: Block > Next Game
 *
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Next_Game {

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
			Sports_Leagues::dir( 'gutenberg/blocks/next-game' ),
			[
				'title'           => 'SL Next Game',
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
				'exclude_ids'    => '',
				'game_link_text' => '',
				'include_ids'    => '',
				'limit'          => 1,
				'max_size'       => '',
				'offset'         => '',
				'season_id'      => '',
				'show_team_name' => '',
				'stage_id'       => '',
				'team_id'        => '',
				'tournament_id'  => '',
				'transparent_bg' => '',
				'team_a'         => '',
				'team_b'         => '',
			]
		);

		$shortcode_attr = [
			'exclude_ids'    => $attr['exclude_ids'],
			'game_link_text' => $attr['game_link_text'],
			'include_ids'    => $attr['include_ids'],
			'limit'          => $attr['limit'],
			'max_size'       => $attr['max_size'],
			'offset'         => $attr['offset'],
			'season_id'      => $attr['season_id'],
			'show_team_name' => Sports_Leagues::string_to_bool( $attr['show_team_name'] ),
			'stage_id'       => $attr['stage_id'],
			'team_a'         => $attr['team_a'],
			'team_b'         => $attr['team_b'],
			'team_id'        => $attr['team_id'],
			'tournament_id'  => $attr['tournament_id'],
			'transparent_bg' => $attr['transparent_bg'],
			'before_widget'  => '',
			'title'          => '',
			'after_widget'   => '',
		];

		$html_output = sports_leagues()->template->widget_loader( 'next-game', $shortcode_attr );

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

return new Sports_Leagues_Block_Next_Game();
