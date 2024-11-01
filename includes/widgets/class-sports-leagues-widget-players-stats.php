<?php
/**
 * Sports Leagues :: Widget >> Players Stats.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Players_Stats extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-players-stats';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Players Stats', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show aggregate players statistics.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-players-stats';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/608-players-stats-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => esc_html_x( 'Players Stats', 'widget default title', 'sports-leagues' ),
			],
			[
				'id'         => 'stats_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Stats', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->player_stats, 'get_stats_game_countable_options' ],
			],
			[
				'id'         => 'tournament_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Tournament', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->tournament, 'get_root_tournament_options' ],
			],
			[
				'id'         => 'stage_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Tournament Stage', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->tournament, 'get_stage_options' ],
			],
			[
				'id'         => 'season_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Season', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->season, 'get_season_options' ],
			],
			[
				'id'         => 'league_id',
				'type'       => 'select',
				'label'      => esc_html__( 'League', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->league, 'get_league_options' ],
			],
			[
				'id'         => 'team_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Team', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => '- ' . esc_html__( 'select', 'sports-leagues' ) . ' -',
				'options_cb' => [ sports_leagues()->team, 'get_team_options' ],
			],
			[
				'id'          => 'position',
				'type'        => 'text',
				'label'       => esc_html__( 'Filter by Player Position', 'sports-leagues' ),
				'default'     => '',
				'description' => 'comma-separated list of positions',
			],
			[
				'id'      => 'order',
				'type'    => 'select',
				'label'   => esc_html__( 'Order', 'sports-leagues' ),
				'default' => 'DESC',
				'options' => [
					'DESC' => esc_html__( 'Descending', 'sports-leagues' ),
					'ASC'  => esc_html__( 'Ascending', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'limit',
				'type'    => 'number',
				'label'   => esc_html__( 'Limit', 'sports-leagues' ) . ' (' . esc_html__( '0 - for all', 'sports-leagues' ) . ')',
				'default' => 10,
			],
			[
				'id'      => 'soft_limit',
				'type'    => 'select',
				'label'   => esc_html__( 'Soft Limit', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''  => esc_html__( 'No', 'sports-leagues' ),
					'1' => esc_html__( 'Yes', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'show_position',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Position', 'sports-leagues' ),
				'default' => 0,
			],
			[
				'id'      => 'show_team',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Team', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_nationality',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Nationality', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_photo',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Photo', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_games_played',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Games Played', 'sports-leagues' ),
				'default' => 0,
			],
			[
				'id'      => 'link_to_profile',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Link to Profile', 'sports-leagues' ),
				'default' => 0,
			],
		];
	}
}
