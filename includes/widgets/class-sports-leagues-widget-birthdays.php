<?php
/**
 * Sports Leagues :: Widget >> Birthdays.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Birthdays extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-birthdays';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Birthdays', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Upcoming Birthdays.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-birthdays';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/605-birthdays-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => '',
			],
			[
				'id'          => 'team_id',
				'type'        => 'select',
				'label'       => esc_html__( 'Team', 'sports-leagues' ),
				'default'     => '',
				'show_empty'  => esc_html__( '- select team -', 'sports-leagues' ),
				'options_cb'  => [ sports_leagues()->team, 'get_team_options' ],
				'description' => esc_html__( 'Optional, leave empty for all', 'sports-leagues' ),
			],
			[
				'id'      => 'type',
				'type'    => 'select',
				'label'   => esc_html__( 'Type', 'sports-leagues' ),
				'default' => 'players',
				'options' => [
					'players' => esc_html__( 'Players only', 'sports-leagues' ),
					'staff'   => esc_html__( 'Staff only', 'sports-leagues' ),
					'all'     => esc_html__( 'Players and Staff', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'days_before',
				'type'    => 'number',
				'label'   => esc_html__( 'Days before birthday', 'sports-leagues' ),
				'default' => 5,
			],
			[
				'id'      => 'days_after',
				'type'    => 'number',
				'label'   => esc_html__( 'Days after birthday', 'sports-leagues' ),
				'default' => 3,
			],
			[
				'id'      => 'group_by_date',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Group by date', 'sports-leagues' ),
				'default' => 0,
			],
		];
	}
}
