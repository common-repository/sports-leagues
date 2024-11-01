<?php
/**
 * Sports Leagues :: Shortcode > Games.
 *
 * @since   0.5.5
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Games {

	private $shortcode = 'sl-games';

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
			'tournament_id'         => '',
			'stage_id'              => '',
			'season_id'             => '',
			'league_id'             => '',
			'group_id'              => '',
			'round_id'              => '',
			'venue_id'              => '',
			'date_from'             => '',
			'date_to'               => '',
			'finished'              => '',
			'filter_by_team'        => '',
			'filter_by_game_day'    => '',
			'limit'                 => '',
			'days_offset'           => '',
			'days_offset_to'        => '',
			'priority'              => '',
			'sort_by_date'          => '',
			'sort_by_game_day'      => '',
			'group_by'              => '',
			'group_by_header_style' => '',
			'show_team_logo'        => 1,
			'show_game_datetime'    => 1,
			'tournament_logo'       => 1,
			'class'                 => 'mt-4',
			'exclude_ids'           => '',
			'include_ids'           => '',
			'outcome_id'            => '',
			'header_style'          => 'header',
			'header_class'          => '',
			'game_layout'           => '',
			'show_load_more'        => false,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'games', $atts );
	}
}

// Bump
new Sports_Leagues_Shortcode_Games();
