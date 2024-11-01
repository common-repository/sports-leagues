<?php
/**
 * Sports Leagues :: Shortcode > Teams.
 *
 * @since   0.5.5
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Teams {

	private $shortcode = 'sl-teams';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'shortcode_init' ] );
	}

	/**
	 * Add shortcode.
	 */
	public function shortcode_init() {
		add_shortcode( $this->shortcode, [ $this, 'render_shortcode' ] );
	}

	/**
	 * Rendering shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts ) {

		$defaults = [
			'stage_id'       => '',
			'layout'         => '',
			'logo_height'    => '50px',
			'logo_width'     => '50px',
			'exclude_ids'    => '',
			'include_ids'    => '',
			'show_team_name' => '1',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'teams', $atts );
	}

}

// Bump
new Sports_Leagues_Shortcode_Teams();
