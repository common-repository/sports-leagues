<?php
/**
 * Sports Leagues :: Widget >> Standing.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Standing extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-standing';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Standing Table', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show standing table.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-standing';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/91-standing-table-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => esc_html_x( 'Standing Table', 'widget default title', 'sports-leagues' ),
			],
			[
				'id'         => 'standing',
				'type'       => 'select',
				'label'      => esc_html__( 'Standing ID', 'sports-leagues' ),
				'default'    => '',
				'show_empty' => esc_html__( '- select standing -', 'sports-leagues' ),
				'options_cb' => [ sports_leagues()->standing, 'get_standing_options' ],
			],
			[
				'id'          => 'exclude_ids',
				'type'        => 'team_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Exclude', 'sports-leagues' ),
				'description' => esc_html__( 'Team IDs, separated by commas.', 'sports-leagues' ),
			],
			[
				'id'      => 'team_name',
				'type'    => 'select',
				'label'   => esc_html__( 'Team Name', 'sports-leagues' ),
				'default' => 'abbr',
				'options' => [
					''     => esc_html__( 'Full', 'sports-leagues' ),
					'abbr' => esc_html__( 'Abbreviation', 'sports-leagues' ),
				],
			],
			[
				'id'          => 'partial',
				'type'        => 'text',
				'label'       => esc_html__( 'Show Partial Data', 'sports-leagues' ),
				'description' => esc_html__( 'Eg.: "1-5" (show teams from 1 to 5 place), "45" - show table slice with specified team ID in the middle', 'sports-leagues' ),
				'default'     => '',
			],
			[
				'id'      => 'bottom_link',
				'type'    => 'select',
				'label'   => esc_html__( 'Show link to', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''           => esc_html__( 'none', 'sports-leagues' ),
					'tournament' => esc_html__( 'tournament', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'link_text',
				'type'    => 'text',
				'label'   => esc_html__( 'Alternative bottom link text', 'sports-leagues' ),
				'default' => '',
			],
		];
	}
}
