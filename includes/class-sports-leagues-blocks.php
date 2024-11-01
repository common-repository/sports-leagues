<?php
/**
 * Sports Leagues :: Blocks.
 *
 * @since   0.12.4
 * @package Sports_Leagues
 *
 */

class Sports_Leagues_Blocks {

	/**
	 * Blocks.
	 *
	 * @var array
	 */
	public $blocks = [];

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		if ( 'no' !== Sports_Leagues_Options::get_value( 'gutenberg_blocks' ) ) {

			// Register Blocks
			$this->blocks['tournament_header'] = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-tournament-header' );
			$this->blocks['game_countdown']    = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-game-countdown' );
			$this->blocks['next_game']         = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-next-game' );
			$this->blocks['last_game']         = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-last-game' );
			$this->blocks['teams']             = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-teams' );
			$this->blocks['games']             = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-games' );
			$this->blocks['players_stats']     = Sports_Leagues::include_file( 'includes/blocks/class-sports-leagues-block-players-stats' );

			// Run Hooks
			$this->hooks();
		}
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'add_block_editor_assets' ] ); // add_editor_style
		add_filter( 'block_categories_all', [ $this, 'add_block_category' ] );
	}

	/**
	 * Add Block Category
	 */
	public function add_block_category( $categories ) {
		return array_merge(
			[
				[
					'slug'  => 'anwp-sl',
					'title' => __( 'Sports Leagues', 'sports-leagues' ),
				],
			],
			$categories
		);
	}

	/**
	 * Register blocks.
	 */
	public function add_block_editor_assets() {

		$assets = Sports_Leagues::include_file( 'gutenberg/blocks.asset' );

		wp_enqueue_script(
			'anwp-sl-blocks',
			Sports_Leagues::url( 'gutenberg/blocks.js' ),
			$assets['dependencies'],
			$assets['version'],
			true
		);

		wp_enqueue_style(
			'anwp-sl-blocks',
			Sports_Leagues::url( 'gutenberg/blocks.css' ),
			[],
			$assets['version']
		);

		wp_enqueue_style(
			'anwp-sl-blocks-editor',
			Sports_Leagues::url( 'admin/css/editor-styles.css' ),
			[],
			Sports_Leagues::VERSION
		);

		sports_leagues()->public_enqueue_scripts();

		if ( method_exists( 'Sports_Leagues_Premium', 'public_enqueue_scripts' ) ) {
			sports_leagues_premium()->public_enqueue_scripts();
		}

		wp_localize_script(
			'anwp-sl-blocks',
			'AnWP_SL_Blocks_Data',
			[
				'game_countable_options' => sports_leagues()->player_stats->get_stats_game_countable_options(),
				'player_positions'       => sports_leagues()->config->get_player_positions(),
			]
		);
	}
}
