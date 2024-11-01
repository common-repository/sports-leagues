<?php
/**
 * Sports Leagues :: Block > Tournament Header
 *
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Tournament_Header {

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
			Sports_Leagues::dir( 'gutenberg/blocks/tournament-header' ),
			[
				'title'           => 'SL Tournament Header',
				'render_callback' => [ $this, 'render_tournament_header' ],
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
	public function render_tournament_header( $attr, $content, $block_instance ) {

		$attr = wp_parse_args(
			$attr,
			[
				'tournament_id'   => '',
				'stage_id'        => '',
				'season_selector' => '',
				'title_field'     => '',
				'title_as_link'   => '',
				'title'           => '',
			]
		);

		$tournament_id = absint( $attr['tournament_id'] );

		if ( ! $tournament_id && Sports_Leagues::is_editing_block_on_backend() ) {
			ob_start();
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'Tournament ID is not set', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		if ( 'sl_tournament' !== get_post_type( $tournament_id ) && Sports_Leagues::is_editing_block_on_backend() ) {
			ob_start();
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'Incorrect ID', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$tournament_shortcode_attr = [
			'tournament_id'   => $tournament_id,
			'stage_id'        => $attr['stage_id'],
			'season_selector' => Sports_Leagues::string_to_bool( $attr['season_selector'] ),
			'title_as_link'   => Sports_Leagues::string_to_bool( $attr['title_as_link'] ),
			'title_field'     => $attr['title_field'],
			'title'           => $attr['title'],
		];

		return sports_leagues()->template->shortcode_loader( 'tournament-header', $tournament_shortcode_attr );
	}
}

return new Sports_Leagues_Block_Tournament_Header();
