<?php
/**
 * Sports Leagues :: Widget >> Player.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Widget_Player extends Sports_Leagues_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'sl-widget-player';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'Player', 'sports-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Single Player.', 'sports-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'sl-widget-player';
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
				'url'  => 'https://anwppro.userecho.com/knowledge-bases/6/articles/105-player-widget',
			],
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title', 'sports-leagues' ),
				'default' => '',
			],
			[
				'id'      => 'player_id',
				'type'    => 'player_id',
				'label'   => esc_html__( 'Player ID', 'sports-leagues' ),
				'default' => '',
			],
			[
				'id'          => 'options_text',
				'type'        => 'text',
				'label'       => esc_html__( 'Options Text', 'sports-leagues' ),
				'description' => esc_html__( 'Separate line by "|", number and label - with ":". E.q.: "Goals: 8 | Assists: 5"', 'sports-leagues' ),
			],
			[
				'id'      => 'show_team',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Team', 'sports-leagues' ),
				'default' => 0,
			],
			[
				'id'      => 'profile_link',
				'type'    => 'select',
				'label'   => esc_html__( 'Show Link to Profile', 'sports-leagues' ),
				'default' => 'yes',
				'options' => [
					'no'  => esc_html__( 'No', 'sports-leagues' ),
					'yes' => esc_html__( 'Yes', 'sports-leagues' ),
				],
			],
			[
				'id'      => 'profile_link_text',
				'type'    => 'text',
				'label'   => esc_html__( 'Profile link text', 'sports-leagues' ),
				'default' => esc_html__( 'profile', 'sports-leagues' ),
			],
		];
	}
}
