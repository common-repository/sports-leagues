<?php
/**
 * Sports Leagues :: Shortcode > Players Stats.
 *
 * @since   0.7.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode_Players_Stats {

	private $shortcode = 'sl-players-stats';

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
			'stats_id'          => '',
			'game_id'           => '',
			'team_id'           => '',
			'tournament_id'     => '',
			'stage_id'          => '',
			'league_id'         => '',
			'season_id'         => '',
			'group_id'          => '',
			'round_id'          => '',
			'venue_id'          => '',
			'game_day'          => '',
			'order'             => '',
			'limit'             => 10,
			'soft_limit'        => 0,
			'show_position'     => 1,
			'show_team'         => 1,
			'show_nationality'  => 1,
			'show_photo'        => 1,
			'show_games_played' => 0,
			'link_to_profile'   => 0,
			'class'             => '',
			'position'          => '',
			'show_full'         => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return sports_leagues()->template->shortcode_loader( 'players-stats', $atts );
	}
}

// Bump
new Sports_Leagues_Shortcode_Players_Stats();
