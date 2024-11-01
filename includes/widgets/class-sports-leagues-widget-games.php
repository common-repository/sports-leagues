<?php
/**
 * Sports Leagues :: Widget >> Games
 *
 * @since   0.5.9
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Games extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-games';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Games', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show List of Games.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-games';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/101-games-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => '',
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
				'id'      => 'finished',
				'type'    => 'select',
				'label'   => esc_html__( 'Game Status', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''  => esc_html__( 'All', 'sports-leagues' ),
					'1' => esc_html__( 'Finished', 'sports-leagues' ),
					'0' => esc_html__( 'Upcoming', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'limit',
				'type'    => 'number',
				'label'   => esc_html__( 'Games Limit', 'sports-leagues' ) . ' (' . esc_html__( '0 - for all', 'sports-leagues' ) . ')',
				'default' => 10,
			],
			[
				'id'      => 'group_by',
				'type'    => 'select',
				'label'   => esc_html__( 'Group By', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''        => esc_html__( 'none', 'sports-leagues' ),
					'day'     => esc_html__( 'Day', 'sports-leagues' ),
					'month'   => esc_html__( 'Month', 'sports-leagues' ),
					'gameday' => esc_html__( 'GameDay', 'sports-leagues' ),
					'stage'   => esc_html__( 'Stage', 'sports-leagues' ),
				],
			],
			[
				'id'          => 'filter_by_team',
				'type'        => 'team_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Filter By Team IDs', 'sports-leagues' ),
				'description' => esc_html__( 'Comma separated list of options (if more than one).', 'sports-leagues' ),
			],
			[
				'id'          => 'filter_by_game_day',
				'type'        => 'text',
				'label'       => esc_html__( 'Filter By GameDay', 'sports-leagues' ),
				'description' => esc_html__( 'Comma separated list of options (if more than one).', 'sports-leagues' ),
			],
			[
				'id'          => 'date_from',
				'type'        => 'text',
				'label'       => esc_html__( 'Games from date', 'sports-leagues' ),
				'description' => esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ),
			],
			[
				'id'          => 'date_to',
				'type'        => 'text',
				'label'       => esc_html__( 'Games before date', 'sports-leagues' ),
				'description' => esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ),
			],
			[
				'id'          => 'days_offset',
				'type'        => 'text',
				'label'       => esc_html__( 'Dynamic days filter', 'sports-leagues' ),
				'description' => esc_html__( 'For example: "-2" from 2 days ago and newer; "2" from day after tomorrow and newer', 'sports-leagues' ),
			],
			[
				'id'          => 'days_offset_to',
				'type'        => 'text',
				'label'       => esc_html__( 'Dynamic days filter to', 'sports-leagues' ),
				'description' => esc_html__( 'For example: "1" - till tomorrow (tomorrow not included)', 'sports-leagues' ),
			],
			[
				'id'      => 'sort_by_date',
				'type'    => 'select',
				'label'   => esc_html__( 'Sort By Date', 'sports-leagues' ),
				'default' => 'desc',
				'options' => [
					''     => esc_html__( 'none', 'sports-leagues' ),
					'asc'  => esc_html__( 'Ascending', 'sports-leagues' ),
					'desc' => esc_html__( 'Descending', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'sort_by_game_day',
				'type'    => 'select',
				'label'   => esc_html__( 'Sort By GameDay', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''     => esc_html__( 'none', 'sports-leagues' ),
					'asc'  => esc_html__( 'Ascending', 'sports-leagues' ),
					'desc' => esc_html__( 'Descending', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'show_team_logo',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show team logo', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_team_name',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show team name', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_game_datetime',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show game datetime', 'sports-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'link_text',
				'type'    => 'text',
				'label'   => esc_html__( 'Bottom Link Text', 'sports-leagues' ),
				'default' => '',
			],
			[
				'id'      => 'link_target',
				'type'    => 'text',
				'label'   => esc_html__( 'Bottom Link Url', 'sports-leagues' ),
				'default' => '',
			],
			[
				'id'      => 'game_layout',
				'type'    => 'select',
				'label'   => esc_html__( 'Layout', 'sports-leagues' ),
				'default' => 'modern',
				'options' => [
					'modern'  => esc_html__( 'Modern', 'sports-leagues' ),
					'classic' => esc_html__( 'Classic', 'sports-leagues' ),
				],
			],
		];
	}
}
