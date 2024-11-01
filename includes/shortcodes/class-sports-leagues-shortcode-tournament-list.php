<?php
/**
 * Sports Leagues :: Shortcode > Tournament List.
 *
 * @since   0.5.17
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Tournament_List {

	private $shortcode = 'sl-tournament-list';

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
			'status'       => '',
			'sort_by_date' => '',
			'limit'        => 0,
			'exclude_ids'  => '',
			'include_ids'  => '',
			'date_from'    => '',
			'date_to'      => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'tournament-list', $atts );
	}
}

// Bump
new Sports_Leagues_Shortcode_Tournament_List();
