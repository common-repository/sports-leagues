<?php
/**
 * Sports Leagues :: Shortcode > Standing.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Standing {

	private $shortcode = 'sl-standing';

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
			'title'               => '',
			'id'                  => '',
			'exclude_ids'         => '',
			'layout'              => '',
			'partial'             => '',
			'partitions'          => '',
			'partitions_switcher' => 1,
			'bottom_link'         => '',
			'link_text'           => '',
			'subheader_text'      => '',
			'subheader_class'     => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'standing', $atts );
	}
}

// Bump
new Sports_Leagues_Shortcode_Standing();
