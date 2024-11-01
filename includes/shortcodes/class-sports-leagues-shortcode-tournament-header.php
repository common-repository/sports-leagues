<?php
/**
 * Sports Leagues :: Shortcode > Tournament Header.
 *
 * @since   0.5.5
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Tournament_Header {

	private $shortcode = 'sl-tournament-header';

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
			'title_as_link'   => 0,
			'tournament_id'   => '',
			'stage_id'        => '',
			'season_selector' => 0,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'tournament-header', $atts );
	}
}

// Bump
new Sports_Leagues_Shortcode_Tournament_Header();
