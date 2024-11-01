<?php
/**
 * Sports Leagues :: Widget >> Teams.
 *
 * @since         0.5.6
 * @package       Sports-Leagues/Templates
 */
class Sports_Leagues_Widget_Teams extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-teams';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Teams', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Teams Grid', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-teams';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/93-teams-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => esc_html_x( 'Teams', 'widget default title', 'sports-leagues' ),
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
				'id'      => 'layout',
				'type'    => 'select',
				'label'   => esc_html__( 'Layout', 'sports-leagues' ),
				'default' => '',
				'options' => [
					''     => esc_html__( 'Custom Height and Width', 'sports-leagues' ),
					'2col' => esc_html__( '2 Columns', 'sports-leagues' ),
					'3col' => esc_html__( '3 Columns', 'sports-leagues' ),
					'4col' => esc_html__( '4 Columns', 'sports-leagues' ),
					'6col' => esc_html__( '6 Columns', 'sports-leagues' ),
				],
			],
			[
				'id'          => 'logo_height',
				'type'        => 'text',
				'label'       => esc_html__( 'Logo Height', 'sports-leagues' ),
				'description' => esc_html__( 'Height value with units. Example: "50px" or "3rem".', 'sports-leagues' ),
				'default'     => '50px',
			],
			[
				'id'          => 'logo_width',
				'type'        => 'text',
				'label'       => esc_html__( 'Logo Width', 'sports-leagues' ),
				'description' => esc_html__( 'Width value with units. Example: "50px" or "3rem".', 'sports-leagues' ),
				'default'     => '50px',
			],
			[
				'id'          => 'exclude_ids',
				'type'        => 'team_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Exclude', 'sports-leagues' ),
				'description' => esc_html__( 'Team IDs, separated by commas.', 'sports-leagues' ),
			],
			[
				'id'          => 'include_ids',
				'type'        => 'team_id',
				'single'      => 'no',
				'label'       => esc_html__( 'Include', 'sports-leagues' ),
				'description' => esc_html__( 'Team IDs, separated by commas.', 'sports-leagues' ),
			],
			[
				'id'      => 'show_team_name',
				'type'    => 'select',
				'label'   => esc_html__( 'Show Team Name', 'sports-leagues' ),
				'default' => 'yes',
				'options' => [
					'no'  => esc_html__( 'No', 'sports-leagues' ),
					'yes' => esc_html__( 'Yes', 'sports-leagues' ),
				],
			],

		];
	}
}
