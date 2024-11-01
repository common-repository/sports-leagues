<?php
/**
 * Sports Leagues :: Widget >> Video
 *
 * @since   0.7.2
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Video extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-video';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Game Video', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Last Game Video.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-video';
	}

	/**
	 * Get widget options fields.
	 *
	 * @return array
	 */
	protected function get_widget_fields() {
		return [
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => esc_html_x( 'Game Video', 'widget default title', 'sports-leagues' ),
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
				'id'          => 'include_ids',
				'type'        => 'game_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Game ID', 'sports-leagues' ),
				'description' => esc_html__( 'Fill to show video of the specified game', 'sports-leagues' ),
			],
		];
	}
}
