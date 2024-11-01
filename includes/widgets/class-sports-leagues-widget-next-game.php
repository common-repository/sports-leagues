<?php
/**
 * Sports Leagues :: Widget >> Next Game
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Next_Game extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-next-game';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Next Game', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Next Game.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-next-game';
	}

	/**
	 * Get widget options fields.
	 *
	 * @return array
	 */
	protected function get_widget_fields() {
		return [
			[
				'id'   => 'docs',
				'type' => 'docs',
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/92-next-game-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => esc_html_x( 'Next Game', 'widget default title', 'sports-leagues' ),
			],
			[
				'id'         => 'team_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Team', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => esc_html__( '- select team -', 'sports-leagues' ),
				'options_cb' => [ sports_leagues()->team, 'get_team_options' ],
			],
			[
				'id'         => 'tournament_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Tournament', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => esc_html__( '- select tournament -', 'sports-leagues' ),
				'options_cb' => [ sports_leagues()->tournament, 'get_root_tournament_options' ],
			],
			[
				'id'         => 'stage_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Tournament Stage', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => esc_html__( '- select stage -', 'sports-leagues' ),
				'options_cb' => [ sports_leagues()->tournament, 'get_stage_options' ],
			],
			[
				'id'         => 'season_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Season', 'sports-leagues' ),
				'show_empty' => esc_html__( '- select season -', 'sports-leagues' ),
				'default'    => '',
				'options_cb' => [ sports_leagues()->season, 'get_season_options' ],
			],
			[
				'id'      => 'show_team_name',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'show team name', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'game_link_text',
				'type'    => 'text',
				'label'   => esc_html__( 'Game link text', 'sports-leagues' ),
				'default' => esc_html__( '- game preview -', 'sports-leagues' ),
			],
			[
				'id'          => 'exclude_ids',
				'type'        => 'game_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Exclude', 'sports-leagues' ),
				'description' => esc_html__( 'Game IDs, separated by commas.', 'sports-leagues' ),
			],
			[
				'id'      => 'limit',
				'type'    => 'number',
				'default' => 1,
				'min'     => 1,
				'label'   => esc_html__( 'Limit', 'sports-leagues' ),
			],
		];
	}
}
