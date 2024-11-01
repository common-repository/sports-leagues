<?php
/**
 * Sports Leagues :: Main Class
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

/**
 * Autoloads files with classes when needed.
 *
 * @since  0.1.0
 *
 * @param  string $class_name Name of the class being requested.
 */
function sports_leagues_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'Sports_Leagues_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'Sports_Leagues_' ) ) ) );

	// Include our file.
	Sports_Leagues::include_file( 'includes/class-sports-leagues-' . $filename );
}

spl_autoload_register( 'sports_leagues_autoload_classes' );

/**
 * Main initiation class.
 *
 * @property-read Sports_Leagues_Blocks       $blocks
 * @property-read Sports_Leagues_Cache        $cache
 * @property-read Sports_Leagues_Config       $config
 * @property-read Sports_Leagues_Customizer   $customizer
 * @property-read Sports_Leagues_Data         $data
 * @property-read Sports_Leagues_Game         $game
 * @property-read Sports_Leagues_Helper       $helper
 * @property-read Sports_Leagues_Event        $event
 * @property-read Sports_Leagues_League       $league
 * @property-read Sports_Leagues_Options      $options
 * @property-read Sports_Leagues_Player       $player
 * @property-read Sports_Leagues_Player_Stats $player_stats
 * @property-read Sports_Leagues_Official     $official
 * @property-read Sports_Leagues_Staff        $staff
 * @property-read Sports_Leagues_Season       $season
 * @property-read Sports_Leagues_Standing     $standing
 * @property-read Sports_Leagues_Team         $team
 * @property-read Sports_Leagues_Template     $template
 * @property-read Sports_Leagues_Text         $text
 * @property-read Sports_Leagues_Tournament   $tournament
 * @property-read Sports_Leagues_Venue        $venue
 * @property-read string                      $path     Path of plugin directory
 *
 * @since  0.1.0
 */
final class Sports_Leagues {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const VERSION = '0.13.4';

	/**
	 * Current DB structure version.
	 *
	 * @var    int
	 * @since  0.1.0
	 */
	const DB_VERSION = 9;

	/**
	 * Menu Icon.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const SVG_CUP = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAuNDc5IDEuMDY0IDE5LjA5OSAxOC45MzYiPjxwYXRoIGZpbGw9Im5vbmUiIGQ9Ik02IDE5SDV2MWgxMHYtMWgtMXYtMWgtMS4xMzVhNy4xNjQgNy4xNjQgMCAwIDEtLjc2NS0xLjUgMTAuMTkgMTAuMTkgMCAwIDEtLjU1My0yLjQ3MWMxLjc4LS42MzkgMy4xMjMtMi4zNDcgMy4zOTEtNC40MzcgMS40NjctLjgyOCAzLjIyMi0yLjA2NyAzLjkwNi0zLjMwNS45OC0xLjY1Ny45OC0zLjMxNCAwLTQuMTQzLS45NTUtLjgwNy0yLjg0MS0uODI4LTMuODQ0LjcwM1YxLjA2NEg1LjAxNnYxLjcxOWMtMS4wMTQtMS40NjYtMi44Ni0xLjQzNC0zLjgwMS0uNjM5LS45ODEuODI5LS45ODEgMi40ODYgMCA0LjE0My42NzQgMS4yMjEgMi4zODkgMi40NDEgMy44NDIgMy4yNjguMjU3IDIuMTA3IDEuNjA2IDMuODMxIDMuMzk2IDQuNDc0QTEwLjE5IDEwLjE5IDAgMCAxIDcuOSAxNi41YTcuMTY0IDcuMTY0IDAgMCAxLS43NjUgMS41SDZ2MXoiLz48L3N2Zz4=';
	const SVG_VS  = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0eWxlPSJmaWxsOm5vbmUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGRlZnM+PGNsaXBQYXRoIGlkPSJhIj48cGF0aCBkPSJNMCAwaDIwdjIwSDB6Ii8+PC9jbGlwUGF0aD48L2RlZnM+PGcgY2xpcC1wYXRoPSJ1cmwoI2EpIj48cGF0aCBkPSJNNi42NjcgMXMyLjg1NyAyLjEyNSA2LjY2NiAyLjEyNUMxMy4zMzMgMTQuODEyIDYuNjY3IDE4IDYuNjY3IDE4UzAgMTQuODEyIDAgMy4xMjVDMy44MSAzLjEyNSA2LjY2NyAxIDYuNjY3IDF6Ii8+PHBhdGggZD0iTTEzLjMzMyAxUzE2LjE5IDMuMTI1IDIwIDMuMTI1QzIwIDE0LjgxMiAxMy4zMzMgMTggMTMuMzMzIDE4UzYuNjY3IDE0LjgxMiA2LjY2NyAzLjEyNUMxMC40NzYgMy4xMjUgMTMuMzMzIDEgMTMuMzMzIDF6IiBmaWxsLW9wYWNpdHk9Ii40Ii8+PC9nPjwvc3ZnPg==';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Instance of AnWPFL_Customizer
	 *
	 * @since 0.11.0
	 * @var Sports_Leagues_Customizer
	 */
	protected $customizer;

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    Sports_Leagues
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of Sports_Leagues_Team
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Team
	 */
	protected $team;

	/**
	 * Instance of Sports_Leagues_Game
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Game
	 */
	protected $game;

	/**
	 * Instance of Sports_Leagues_Helper
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Helper
	 */
	protected $helper;

	/**
	 * Instance of Sports_Leagues_Tournament
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Tournament
	 */
	protected $tournament;

	/**
	 * Instance of Sports_Leagues_League
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_League
	 */
	protected $league;

	/**
	 * Instance of Sports_Leagues_Season
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Season
	 */
	protected $season;

	/**
	 * Instance of Sports_Leagues_Options
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Options
	 */
	protected $options;

	/**
	 * Instance of Sports_Leagues_Config
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Config
	 */
	protected $config;

	/**
	 * Instance of Sports_Leagues_Cache
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Cache
	 */
	protected $cache;

	/**
	 * Instance of Sports_Leagues_Venue
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Venue
	 */
	protected $venue;

	/**
	 * Instance of Sports_Leagues_Player
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Player
	 */
	protected $player;

	/**
	 * Instance of Sports_Leagues_Event
	 *
	 * @since 0.5.15
	 * @var Sports_Leagues_Event
	 */
	protected $event;

	/**
	 * Instance of Sports_Leagues_Player_Stats
	 *
	 * @since 0.5.18
	 * @var Sports_Leagues_Player_Stats
	 */
	protected $player_stats;

	/**
	 * Instance of Sports_Leagues_Official
	 *
	 * @since 0.5.13
	 * @var Sports_Leagues_Official
	 */
	protected $official;

	/**
	 * Instance of Sports_Leagues_Staff
	 *
	 * @since 0.5.14
	 * @var Sports_Leagues_Staff
	 */
	protected $staff;

	/**
	 * Instance of Sports_Leagues_Text
	 *
	 * @since 0.5.14
	 * @var Sports_Leagues_Text
	 */
	protected $text;

	/**
	 * Instance of Sports_Leagues_Data
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Data
	 */
	protected $data;

	/**
	 * Instance of Sports_Leagues_Template
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Template
	 */
	protected $template;

	/**
	 * Instance of Sports_Leagues_Standing
	 *
	 * @since 0.1.0
	 * @var Sports_Leagues_Standing
	 */
	protected $standing;

	/**
	 * Instance of Sports_Leagues_Blocks
	 *
	 * @var Sports_Leagues_Blocks
	 */
	protected $blocks;

	/**
	 * Plugin Post Types
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $plugin_post_types = [];

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.1.0
	 * @return  Sports_Leagues A single instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( self::dir( 'sports-leagues.php' ) );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		/**
		 * Set available plugin post types.
		 *
		 * @param array $array List of plugin pages to load styles.
		 *
		 * @since 0.1.0
		 */
		$this->plugin_post_types = apply_filters(
			'sports-leagues/config/plugin_post_types',
			[
				'sl_player',
				'sl_venue',
				'sl_team',
				'sl_tournament',
				'sl_standing',
				'sl_game',
				'sl_official',
				'sl_staff',
			]
		);
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 */
	public function plugin_classes() {

		// Options
		$this->options      = new Sports_Leagues_Options( $this );
		$this->config       = new Sports_Leagues_Config( $this );
		$this->event        = new Sports_Leagues_Event( $this );
		$this->player_stats = new Sports_Leagues_Player_Stats( $this );
		$this->text         = new Sports_Leagues_Text( $this );

		// Taxonomies
		$this->league = new Sports_Leagues_League( $this );
		$this->season = new Sports_Leagues_Season( $this );

		// CPT
		$this->tournament = new Sports_Leagues_Tournament( $this );
		$this->player     = new Sports_Leagues_Player( $this );
		$this->team       = new Sports_Leagues_Team( $this );
		$this->venue      = new Sports_Leagues_Venue( $this );
		$this->standing   = new Sports_Leagues_Standing( $this );
		$this->game       = new Sports_Leagues_Game( $this );
		$this->official   = new Sports_Leagues_Official( $this );
		$this->staff      = new Sports_Leagues_Staff( $this );

		// Others
		$this->data       = new Sports_Leagues_Data( $this );
		$this->template   = new Sports_Leagues_Template( $this );
		$this->helper     = new Sports_Leagues_Helper( $this );
		$this->cache      = new Sports_Leagues_Cache( $this );
		$this->customizer = new Sports_Leagues_Customizer( $this );
		$this->blocks     = new Sports_Leagues_Blocks( $this );

		new Sports_Leagues_Upgrade( $this );

		// Shortcodes
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-standing.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-teams.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-tournament-header.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-tournament-list.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-games.php' );
		require self::dir( 'includes/shortcodes/class-sports-leagues-shortcode-players-stats.php' );
	}

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Initialize plugin
		add_action( 'init', [ $this, 'init' ], 0 );

		// Initialize widgets
		add_action( 'widgets_init', [ $this, 'register_widgets' ], 0 );

		/**
		 * Register menu pages
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_menu', [ $this, 'register_menus' ], 5 );
		add_action( 'admin_menu', [ $this, 'register_alt_menus' ], 11 );

		/**
		 * Enqueue admin scripts
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		/**
		 * Add svg icons to the footer
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_footer', [ $this, 'include_admin_svg_icons' ], 99 );

		/**
		 * Add svg icons to the public side
		 *
		 * @since 0.1.0
		 */
		add_action( 'wp_footer', [ $this, 'include_public_svg_icons' ], 99 );

		/**
		 * Enqueue public scripts & styles
		 *
		 * @since 0.1.0
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );

		/**
		 * Add body classes
		 *
		 * @since 0.1.0
		 */
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );

		/**
		 * Filters the retrieved excerpt.
		 *
		 * @since 0.1.0
		 */
		add_filter( 'get_the_excerpt', [ $this, 'get_the_excerpt' ], 5, 2 );

		/**
		 * Renders notice if CMB2 not installed.
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_notices', [ $this, 'notice_cmb_not_installed' ] );

		add_action( 'admin_notices', [ $this, 'notice_data_migration_required' ] );

		/**
		 * Add plugin meta links.
		 *
		 * @since 0.1.0
		 */
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );

		/**
		 * Hide thumbnail image on plugin pages
		 *
		 * @since 0.1.0
		 */
		add_filter( 'post_thumbnail_html', [ $this, 'filter_post_thumbnail' ], 10, 2 );

		/**
		 * Remove empty <p> tags around shortcodes.
		 *
		 * @since 0.5.3
		 */
		add_filter( 'the_content', [ $this, 'shortcode_empty_paragraph_fix' ], 10 );

		/**
		 * Add redirect to premium page.
		 *
		 * @since 0.5.8
		 */
		add_action( 'admin_init', [ $this, 'docs_page_redirect' ] );

		/**
		 * Fix Divi content duplication
		 *
		 * @since 0.6.2
		 */
		add_filter( 'et_first_image_use_custom_content', [ $this, 'fix_divi_duplicate_content' ], 20, 3 );

		/**
		 * Add modal wrappers
		 *
		 * @since 0.12.4
		 */
		add_action( 'wp_footer', [ $this, 'add_modal_wrappers' ], 99 );

		add_action( 'admin_notices', [ $this, 'display_admin_pre_remove_notice' ] );
		add_action( 'pre_delete_term', [ $this, 'maybe_prevent_delete_term' ], 10, 2 );
		add_filter( 'pre_delete_post', [ $this, 'maybe_prevent_delete_tournament' ], 10, 2 );
		add_filter( 'pre_trash_post', [ $this, 'maybe_prevent_delete_tournament' ], 10, 2 );
	}

	/**
	 * Check the possibility to delete Tournament
	 *
	 * @param WP_Post|false|null $delete Whether to go forward with deletion.
	 * @param WP_Post            $post   Post object.
	 *
	 * @since 0.13.0
	 */
	public function maybe_prevent_delete_tournament( $delete, $post ) {

		if ( ! empty( $post->post_type ) && 'sl_tournament' === $post->post_type ) {

			$games = sports_leagues()->game->get_games_extended(
				[
					'tournament_id' => $post->ID,
				],
				'ids'
			);

			$stage_games = sports_leagues()->game->get_games_extended(
				[
					'stage_id' => $post->ID,
				],
				'ids'
			);

			if ( count( $games ) || count( $stage_games ) ) {
				set_transient( 'anwp-admin-pre-remove-warning', esc_html__( 'It is prohibited to delete a Tournament with Games. First, remove the attached Games.', 'sports-leagues' ), 10 );

				return $post->ID;
			}
		}

		return $delete;
	}

	/**
	 * Check the possibility to delete Season or League
	 *
	 * @param int    $term_id     Term ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @since 0.13.0
	 */
	public function maybe_prevent_delete_term( int $term_id, string $taxonomy ) {

		if ( in_array( $taxonomy, [ 'sl_season', 'sl_league' ], true ) ) {

			$posts = get_posts(
				[
					'post_type'      => 'sl_tournament',
					'posts_per_page' => - 1,
					'tax_query'      => [
						[
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => $term_id,
						],
					],
				]
			);

			if ( count( $posts ) ) {
				set_transient( 'anwp-admin-pre-remove-warning', esc_html__( 'It is prohibited to delete a League or a Season that has Competitions linked to it.', 'sports-leagues' ), 10 );
				wp_die();
			}
		}
	}

	/**
	 * Display pre-remove warning message
	 *
	 * @since 0.13.0
	 */
	public function display_admin_pre_remove_notice() {

		if ( get_transient( 'anwp-admin-pre-remove-warning' ) ) :
			?>
			<div class="notice notice-error is-dismissible notice-alt anwp-visible-after-header">
				<p><?php echo esc_html( get_transient( 'anwp-admin-pre-remove-warning' ) ); ?></p>
			</div>
			<?php
			delete_transient( 'anwp-admin-pre-remove-warning' );
		endif;
	}

	/**
	 * Fix duplicate content on Divi sometimes.
	 *
	 * @param         $bool
	 * @param         $content
	 * @param WP_Post $post
	 *
	 * @return bool|string
	 * @since 0.6.2
	 */
	public function fix_divi_duplicate_content( $bool, $content, $post ) {

		if ( in_array( $post->post_type, $this->plugin_post_types, true ) ) {
			return '';
		}

		return $bool;
	}

	/**
	 * Filters the content to remove any extra paragraph or break tags
	 * caused by shortcodes.
	 *
	 * @param string $content String of HTML content.
	 *
	 * @return string $content Amended string of HTML content.
	 * @since 0.5.6
	 *
	 */
	public function shortcode_empty_paragraph_fix( $content ) {

		if ( apply_filters( 'sports-leagues/config/disable_shortcode_empty_paragraph_fix', false ) ) {
			return $content;
		}

		$array = [
			'<p>['    => '[',
			']</p>'   => ']',
			']<br />' => ']',
		];

		return strtr( $content, $array );
	}

	/**
	 * Add body classes.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function add_body_classes( $classes ) {
		global $is_IE;

		// If it's IE, add a class.
		if ( $is_IE ) {
			$classes[] = 'ie';
		}

		if ( 'no' !== Sports_Leagues_Customizer::get_value( 'general', 'hide_post_titles' ) ) {
			$classes[] = 'anwp-hide-titles';
		}

		$classes[] = 'theme--' . wp_get_theme()->get_template();

		return $classes;
	}

	/**
	 * Hide post thumbnail at the post top.
	 * @param $html
	 *
	 * @return string
	 */
	public function filter_post_thumbnail( $html ) {

		if ( ! is_main_query() || ! is_singular( [ 'sl_player', 'sl_official', 'sl_staff', 'sl_team', 'sl_tournament', 'sl_venue' ] ) ) {
			return $html;
		}

		/**
		 * Disable this filter for supported themes.
		 *
		 * @since 0.1.0
		 *
		 * @param bool
		 */
		$disable_theme_thumb = apply_filters( 'sports-leagues/config/disable_theme_thumb', true );

		return $disable_theme_thumb ? '' : $html;
	}

	/**
	 * Renders notice if CMB2 not installed.
	 *
	 * @since 0.1.0
	 */
	public function notice_cmb_not_installed() {

		if ( defined( 'CMB2_LOADED' ) ) {
			return;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['action'] ) && 'install-plugin' === $_GET['action'] ) {
			return;
		}

		// Check CMB2 installed
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins      = get_plugins();
		$plugin_installed = isset( $all_plugins['cmb2/init.php'] );
		?>
		<div class="notice anwp-sl-cmb2-notice">
			<img src="<?php echo esc_url( self::url( 'admin/img/sl-icon.png' ) ); ?>" alt="">
			<img src="<?php echo esc_url( self::url( 'admin/img/cmb2-icon.png' ) ); ?>" alt="">
			<h3>Please install and activate CMB2 plugin</h3>
			<p>CMB2 is required for proper work of AnWP Sports Leagues, and is used for building metaboxes and custom fields.</p>
			<p>

				<?php if ( $plugin_installed && current_user_can( 'activate_plugins' ) ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=activate&plugin=' . rawurlencode( 'cmb2/init.php' ), 'activate-plugin_cmb2/init.php' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Activate plugin', 'sports-leagues' ); ?></a>
				<?php elseif ( current_user_can( 'install_plugins' ) ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=cmb2' ), 'install-plugin_cmb2' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Install plugin', 'sports-leagues' ); ?></a>
				<?php endif; ?>

				<a class="button" href="https://wordpress.org/plugins/cmb2/" target="_blank"><?php echo esc_html__( 'Plugin page at wp.org', 'sports-leagues' ); ?></a>
			</p>
			<p class="anwp-notice-clear-both"></p>
		</div>
		<?php
	}

	/**
	 * Filters the retrieved excerpt.
	 *
	 * @param string  $post_excerpt The post excerpt.
	 * @param WP_Post $post         Post object.
	 *
	 * @since 0.1.0
	 * @return string Modified post excerpt
	 */
	public function get_the_excerpt( $post_excerpt, $post ) {

		if ( in_array( $post->post_type, $this->plugin_post_types, true ) && empty( $post_excerpt ) ) {
			$post_excerpt = $post->post_title;
		}

		return $post_excerpt;
	}

	/**
	 * Add plugin meta links.
	 *
	 * @param array  $links       An array of the plugin's metadata,
	 *                            including the version, author,
	 *                            author URI, and plugin URI.
	 * @param string $file        Path to the plugin file, relative to the plugins directory.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {

		if ( false !== strpos( $file, $this->basename ) ) {
			$new_links = [
				'doc'       => '<a href="https://anwppro.userecho.com/communities/4-sports-leagues" target="_blank">' . esc_html__( 'Documentation', 'sports-leagues' ) . '</a>',
				'changelog' => '<a href="https://anwppro.userecho.com/knowledge-bases/12-sl-changelog/categories/72-basic-version/articles" target="_blank">' . esc_html__( 'Changelog', 'sports-leagues' ) . '</a>',
				'premium'   => '<a href="https://anwp.pro/sports-leagues-premium-addon/" target="_blank">' . esc_html__( 'Go Premium', 'sports-leagues' ) . '</a>',
			];

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * Register menu pages.
	 *
	 * @since 0.1.0
	 */
	public function register_menus() {

		add_menu_page(
			esc_html_x( 'Sports Leagues', 'admin page title', 'sports-leagues' ),
			esc_html_x( 'Sports Leagues', 'admin menu title', 'sports-leagues' ),
			'manage_options',
			'sports-leagues',
			[ $this, 'render_dashboard_page' ],
			'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNy4wMzkyMiA1LjE4NkM3LjY4MzIyIDUuMTg2IDguMjMzODkgNS4yNzkzNCA4LjY5MTIyIDUuNDY2QzkuMTQ4NTYgNS42NDMzNCA5LjU5MTg5IDUuOTIzMzQgMTAuMDIxMiA2LjMwNkw5LjI5MzIyIDcuMTE4QzguOTI5MjIgNi44Mjg2NyA4LjU2OTg5IDYuNjE4NjcgOC4yMTUyMiA2LjQ4OEM3Ljg2OTg5IDYuMzQ4IDcuNDk2NTYgNi4yNzggNy4wOTUyMiA2LjI3OEM2LjU5MTIyIDYuMjc4IDYuMTc1ODkgNi4zOTQ2NyA1Ljg0OTIyIDYuNjI4QzUuNTIyNTYgNi44NjEzNCA1LjM1OTIyIDcuMjAyIDUuMzU5MjIgNy42NUM1LjM1OTIyIDcuOTMgNS40MTUyMiA4LjE2OCA1LjUyNzIyIDguMzY0QzUuNjM5MjIgOC41NTA2NyA1Ljg0NDU2IDguNzIzMzQgNi4xNDMyMiA4Ljg4MkM2LjQ1MTIyIDkuMDQwNjcgNi44OTQ1NiA5LjIwODY3IDcuNDczMjIgOS4zODZDOC4wNzk4OSA5LjU3MjY3IDguNTgzODkgOS43NzMzNCA4Ljk4NTIyIDkuOTg4MDFDOS4zODY1NiAxMC4yMDI3IDkuNzA4NTYgMTAuNTAxMyA5Ljk1MTIyIDEwLjg4NEMxMC4yMDMyIDExLjI1NzMgMTAuMzI5MiAxMS43MzMzIDEwLjMyOTIgMTIuMzEyQzEwLjMyOTIgMTIuODcyIDEwLjE4OTIgMTMuMzY2NyA5LjkwOTIyIDEzLjc5NkM5LjYzODU2IDE0LjIyNTMgOS4yNDE4OSAxNC41NjEzIDguNzE5MjIgMTQuODA0QzguMjA1ODkgMTUuMDQ2NyA3LjU5NDU2IDE1LjE2OCA2Ljg4NTIyIDE1LjE2OEM1LjU1MDU2IDE1LjE2OCA0LjQ1ODU2IDE0Ljc1MjcgMy42MDkyMiAxMy45MjJMNC4zMzcyMiAxMy4xMUM0LjcyOTIyIDEzLjQyNzMgNS4xMjU4OSAxMy42NyA1LjUyNzIyIDEzLjgzOEM1LjkyODU2IDEzLjk5NjcgNi4zNzY1NiAxNC4wNzYgNi44NzEyMiAxNC4wNzZDNy40Njg1NiAxNC4wNzYgNy45NjMyMiAxMy45MzEzIDguMzU1MjIgMTMuNjQyQzguNzQ3MjIgMTMuMzQzMyA4Ljk0MzIyIDEyLjkxNCA4Ljk0MzIyIDEyLjM1NEM4Ljk0MzIyIDEyLjAzNjcgOC44ODI1NiAxMS43NzUzIDguNzYxMjIgMTEuNTdDOC42Mzk4OSAxMS4zNTUzIDguNDI5ODkgMTEuMTY0IDguMTMxMjIgMTAuOTk2QzcuODQxODkgMTAuODI4IDcuNDE3MjIgMTAuNjYgNi44NTcyMiAxMC40OTJDNS44Njc4OSAxMC4xOTMzIDUuMTQ0NTYgOS44MzQgNC42ODcyMiA5LjQxNEM0LjIyOTg5IDguOTk0IDQuMDAxMjIgOC40MjQ2NyA0LjAwMTIyIDcuNzA2QzQuMDAxMjIgNy4yMjA2NyA0LjEyNzIyIDYuNzg2NjcgNC4zNzkyMiA2LjQwNEM0LjY0MDU2IDYuMDIxMzQgNC45OTk4OSA1LjcyMjY3IDUuNDU3MjIgNS41MDhDNS45MjM4OSA1LjI5MzM0IDYuNDUxMjIgNS4xODYgNy4wMzkyMiA1LjE4NloiIGZpbGw9ImJsYWNrIi8+PHBhdGggZD0iTTEzLjA1ODEgNS4zNTRWMTMuODM4SDE3LjAyMDFMMTYuODY2MSAxNUgxMS43MjgxVjUuMzU0SDEzLjA1ODFaIiBmaWxsPSJibGFjayIvPjxwYXRoIGQ9Ik0wIC04Ljc0MjI4ZS0wN0wyMCAwTDIwIDFMLTQuMzcxMTRlLTA4IDAuOTk5OTk5TDAgLTguNzQyMjhlLTA3WiIgZmlsbD0iYmxhY2siLz48cGF0aCBkPSJNMCAxOUwyMCAxOUwyMCAyMEwtNC4zNzExNGUtMDggMjBMMCAxOVoiIGZpbGw9ImJsYWNrIi8+PHBhdGggZD0iTTIwIDBMMjAgMjBMMTkgMjBMMTkgLTguNzQyMjllLTA4TDIwIDBaIiBmaWxsPSJibGFjayIvPjxwYXRoIGQ9Ik0xIDBMMSAyMEwtMS43ODgxNGUtMDYgMjBMOS4xMzk5MWUtMDcgLTguNzQyMjllLTA4TDEgMFoiIGZpbGw9ImJsYWNrIi8+PC9zdmc+',
			42
		);

		/*
		|--------------------------------------------------------------------------
		| Prepare submenu pages
		|--------------------------------------------------------------------------
		*/
		$submenu_pages = [
			'sl-dashboard' => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Dashboard', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Dashboard', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sports-leagues',
				'output_func' => '',
			],
			'sl-tutorials' => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Quick Start', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Quick Start', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-tutorials',
				'output_func' => [ $this, 'render_tutorials_page' ],
			],
			'docs'         => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => '',
				'menu_title'  => esc_html__( 'Documentation', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'redirect_sl_docs',
				'output_func' => [ $this, 'docs_page_redirect' ],
			],
			'shortcodes'   => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Shortcodes', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Shortcodes', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-shortcodes',
				'output_func' => [ $this, 'render_shortcode_page' ],
			],
			'toolbox'      => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Toolbox', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Toolbox', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-toolbox',
				'output_func' => [ $this, 'render_toolbox_page' ],
			],
			'support'      => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Support', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Support', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'support',
				'output_func' => [ $this, 'render_support_page' ],
			],
			'premium'      => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => '',
				'menu_title'  => '<span style="color: #fd7e14">' . esc_html__( 'Go Premium', 'sports-leagues' ) . '</span>',
				'capability'  => 'manage_options',
				'menu_slug'   => 'redirect_sl_premium',
				'output_func' => [ $this, 'docs_page_redirect' ],
			],
			'customize'    => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Customize', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Customize', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-plugin-customize',
				'output_func' => [ $this, 'render_customize_page' ],
			],
			'sl-tools'     => [
				'parent_slug' => 'sports-leagues',
				'page_title'  => esc_html__( 'Import Data', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Import Data Tool', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-import-tool',
				'output_func' => [ $this, 'render_import_tool_page' ],
			],
		];

		/**
		 * Filters loaded submenu pages.
		 *
		 * @since 0.1.0
		 *
		 * @param array Array of submenus
		 */
		$submenu_pages = apply_filters( 'sports-leagues/admin/submenu_pages', $submenu_pages );

		foreach ( $submenu_pages as $m ) {
			add_submenu_page( $m['parent_slug'], $m['page_title'], $m['menu_title'], $m['capability'], $m['menu_slug'], $m['output_func'] );
		}
	}

	/**
	 * Rendering Customize Page
	 *
	 * @since 0.11.0
	 */
	public function render_customize_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/customize' );
	}

	/**
	 * Rendering Optimizer Page
	 */
	public function render_toolbox_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/toolbox' );
	}

	/**
	 * Register settings menu pages.
	 *
	 * @since 0.6.0
	 */
	public function register_alt_menus() {
		/*
		|--------------------------------------------------------------------------
		| Settings Menu
		|--------------------------------------------------------------------------
		*/
		$submenu_settings_pages = [
			'sl-configurator' => [
				'parent_slug' => 'sports_leagues_settings',
				'page_title'  => esc_html__( 'Sport Configurator', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Sport Configurator', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-configurator',
				'output_func' => [ $this, 'render_configurator_page' ],
			],
			'stats'           => [
				'parent_slug' => 'sports_leagues_settings',
				'page_title'  => esc_html__( 'Player Stats', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Player Stats', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sl-player-stats',
				'output_func' => [ $this, 'render_player_stats_page' ],
			],
			'sl-events'       => [
				'parent_slug' => 'sports_leagues_settings',
				'page_title'  => esc_html__( 'Game Events', 'sports-leagues' ),
				'menu_title'  => esc_html__( 'Game Events', 'sports-leagues' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sports_leagues_event',
				'output_func' => [ $this, 'render_events_config_page' ],
			],
		];

		/**
		 * Filters loaded submenu pages.
		 *
		 * @since 0.6.0
		 *
		 * @param array Array of submenus
		 */
		$submenu_settings_pages = apply_filters( 'sports-leagues/admin/submenu_settings_pages', $submenu_settings_pages );

		foreach ( $submenu_settings_pages as $m ) {
			add_submenu_page( $m['parent_slug'], $m['page_title'], $m['menu_title'], $m['capability'], $m['menu_slug'], $m['output_func'] );
		}
	}

	/**
	 * Rendering Configurator Page
	 *
	 * @since 0.10.0
	 */
	public function render_configurator_page() {

		// Must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/configurator' );
	}

	/**
	 * Rendering Docs page
	 *
	 * @since 0.5.8
	 */
	public function docs_page_redirect() {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'redirect_sl_docs' === $_GET['page'] ) {
			// phpcs:ignore WordPress.Security.SafeRedirect
			wp_redirect( 'https://anwppro.userecho.com/communities/4-sports-leagues' );
			die;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'redirect_sl_premium' === $_GET['page'] ) {
			// phpcs:ignore WordPress.Security.SafeRedirect
			wp_redirect( 'https://www.anwp.pro/sports-leagues-premium-addon/' );
			die;
		}
	}

	/**
	 * Rendering Shortcodes page
	 *
	 * @since 0.5.5
	 */
	public function render_shortcode_page() {

		// Check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/shortcodes' );
	}

	/**
	 * Rendering Player Stats Config page
	 *
	 * @since 0.5.18
	 */
	public function render_player_stats_page() {

		// Must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/player-stats' );
	}

	/**
	 * Rendering Events Config
	 *
	 * @since 0.7.1
	 */
	public function render_events_config_page() {

		// Must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/game-events' );
	}

	/**
	 * Rendering Tools page
	 *
	 * @since 0.12.2
	 */
	public function render_import_tool_page() {

		// Check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/tools' );
	}

	/**
	 * Rendering Tutorials page
	 *
	 * @since 0.1.0
	 */
	public function render_tutorials_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/tutorials' );
	}

	/**
	 * Rendering Dashboard page
	 *
	 * @since 0.11.0
	 */
	public function render_dashboard_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/dashboard' );
	}

	/**
	 * Rendering Tutorials page
	 *
	 * @since 0.1.0
	 */
	public function render_support_page() {

		// check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		self::include_file( 'admin/views/support' );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.1.0
	 */
	public function activate() {

	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.1.0
	 */
	public function deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 */
	public function init() {

		// Load translated strings for plugin.
		load_plugin_textdomain( 'sports-leagues', false, dirname( $this->basename ) . '/languages/' );

		/*
		|--------------------------------------------------------------------------
		| Gamajo_Template_Loader
		|
		| @link      http://github.com/GaryJones/Gamajo-Template-Loader
		| @license   GPL-2.0-or-later
		| @version   1.3.1
		|--------------------------------------------------------------------------
		*/
		require_once self::dir( 'vendor/class-gamajo-template-loader.php' );

		// Initialize plugin classes.
		$this->plugin_classes();

		// Include Extra CMB2 fields.
		$this->cmb2_init();
	}

	/**
	 * Init CMB2 and extra fields
	 *
	 * @since  0.1.0
	 */
	public function cmb2_init() {

		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		/*
		|--------------------------------------------------------------------------
		| CMB2 Fields
		|--------------------------------------------------------------------------
		*/
		require_once self::dir( 'includes/cmb2-fields/cmb-field-map.php' );
		require_once self::dir( 'includes/cmb2-fields/cmb-field-simple-trigger.php' );
		require_once self::dir( 'includes/cmb2-fields/cmb-anwp-custom-fields.php' );
		require_once self::dir( 'includes/cmb2-fields/cmb-anwp-repeated.php' );
		require_once self::dir( 'includes/cmb2-fields/cmb-field-translated-text.php' );

		/*
		|--------------------------------------------------------------------------
		| CMB2 Select2
		|--------------------------------------------------------------------------
		*/
		require_once self::dir( 'includes/cmb2-fields/class-anwp-sl-cmb2-field-select2.php' );
		wp_enqueue_script( 'anwp-sl-cmb-select2-init', self::url( 'admin/js/cmb-select2.js' ), [ 'cmb2-scripts', 'jquery-ui-sortable' ], self::VERSION, false );
	}

	/**
	 * Overrides CMB2 label layout.
	 *
	 * @param            $field_args
	 * @param CMB2_Field $field
	 *
	 * @return string Label html markup.
	 * @since  0.1.0
	 */
	public function cmb2_field_label( $field_args, $field ) {

		if ( ! $field->args( 'name' ) ) {
			return '';
		}

		$output = sprintf( "\n" . '<label class="anwp-cmb2-label" for="%1$s">%2$s', $field->id(), $field->args( 'name' ) );

		// Check tooltip
		if ( ! empty( $field->args( 'label_tooltip' ) ) ) {
			$output .= '<span data-sl_tippy data-tippy-content="' . esc_attr( $field->args( 'label_tooltip' ) ) . '"><svg class="anwp-icon"><use xlink:href="#icon-info"></use></svg></span>';
		}

		$output .= '</label>' . "\n";

		// Check helper text
		if ( ! empty( $field->args( 'label_help' ) ) ) {
			$output .= "\n" . '<span class="anwp-cmb2-label__helper">' . $field->args( 'label_help' ) . '</span>';
		}

		return $output;
	}

	/**
	 * Add SVG definitions to the admin footer.
	 *
	 * @since 0.1.0
	 */
	public function include_admin_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = self::dir( 'admin/img/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}

	/**
	 * Add SVG definitions to the public footer.
	 *
	 * @since 0.1.0
	 */
	public function include_public_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = self::dir( 'public/img/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function public_enqueue_scripts() {

		/*
		|--------------------------------------------------------------------------
		| Basic Styles
		|--------------------------------------------------------------------------
		*/
		$dynamic_css = $this->customizer->get_customizer_css();

		if ( is_rtl() ) {
			wp_enqueue_style( 'sl_styles_rtl', self::url( 'public/css/styles-rtl.css' ), [], self::VERSION );
			wp_enqueue_style( 'sl_styles_rtl_extra', self::url( 'public/css/styles-rtl-extra.css' ), [], self::VERSION );

			if ( $dynamic_css ) {
				wp_add_inline_style( 'sl_styles_rtl', wp_strip_all_tags( $dynamic_css ) );
			}
		} else {
			wp_enqueue_style( 'sl_styles', self::url( 'public/css/styles.min.css' ), [], self::VERSION );

			if ( $dynamic_css ) {
				wp_add_inline_style( 'sl_styles', wp_strip_all_tags( $dynamic_css ) );
			}
		}

		/*
		|--------------------------------------------------------------------------
		| World Flags Sprite
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_style( 'sl_flags', self::url( 'vendor/world-flags-sprite/stylesheets/flags32.css' ), [], self::VERSION );
		wp_enqueue_style( 'sl_flags_16', self::url( 'vendor/world-flags-sprite/stylesheets/flags16.css' ), [], self::VERSION );

		/*
		|--------------------------------------------------------------------------
		| Modaal
		|
		| @license  MIT
		| @link     https://github.com/humaan/Modaal
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'modaal', self::url( 'vendor/modaal/modaal.min.js' ), [ 'jquery' ], self::VERSION, false );
		wp_enqueue_style( 'modaal', self::url( 'vendor/modaal/modaal.min.css' ), [], self::VERSION );

		if ( in_array( Sports_Leagues_Options::get_value( 'preferred_video_player' ), [ 'youtube', 'mixed' ], true ) ) {
			wp_enqueue_script( 'sl-yt-video', self::url( 'public/js/anwp-yt-video.js' ), [ 'jquery', 'underscore' ], self::VERSION, true );
		}

		/*
		|--------------------------------------------------------------------------
		| Plyr
		| @licence - MIT
		| @url - https://plyr.io/
		|--------------------------------------------------------------------------
		*/
		if ( apply_filters( 'sports-leagues/config/load_plyr', true ) && 'youtube' !== Sports_Leagues_Options::get_value( 'preferred_video_player' ) ) {
			wp_register_script( 'plyr', self::url( 'vendor/plyr/plyr.polyfilled.min.js' ), [], '3.7.8', false );
			wp_register_style( 'plyr', self::url( 'vendor/plyr/plyr.css' ), [], '3.7.8' );
		}

		/*
		|--------------------------------------------------------------------------
		| Justified Gallery
		|--------------------------------------------------------------------------
		*/
		wp_register_script( 'anwp-sl-justified-gallery', self::url( 'vendor/flickr-justified-gallery/fjGallery.min.js' ), [], self::VERSION, true );
		wp_register_script( 'anwp-sl-justified-gallery-modal', self::url( 'vendor/baguette-box/baguetteBox.min.js' ), [], self::VERSION, true );

		/*
		|--------------------------------------------------------------------------
		| Data Tables
		| @license: MIT - https://datatables.net/
		|--------------------------------------------------------------------------
		*/
		if ( apply_filters( 'sports-leagues/config/load_datatables', true ) ) {
			wp_enqueue_style( 'anwp-data-tables', self::url( 'vendor/datatables/datatables.min.css' ), [], self::VERSION );
			wp_enqueue_script( 'anwp-data-tables', self::url( 'vendor/datatables/datatables.min.js' ), [ 'jquery' ], self::VERSION, false );
		}

		/*
		|--------------------------------------------------------------------------
		| Main JS
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'anwp-sl-public', self::url( 'public/js/sl-public.min.js' ), [ 'jquery' ], self::VERSION, true );

		// sl_l10n
		wp_add_inline_script(
			'anwp-sl-public',
			'window.AnWPSL = ' . wp_json_encode(
				[
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'spinner_url'  => admin_url( '/images/spinner.gif' ),
					'loader'       => includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ),
					'public_nonce' => wp_create_nonce( 'sl-public-nonce' ),
					'rest_root'    => esc_url_raw( rest_url() ),
				]
			),
			'before'
		);

		/*
		|--------------------------------------------------------------------------
		| Micromodal
		|
		| @license  MIT
		| @link     https://github.com/ghosh/Micromodal
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'micromodal', self::url( 'vendor/micromodal/micromodal.min.js' ), [], '0.4.10', false );

		/*
		|--------------------------------------------------------------------------
		| Modaal
		|
		| @license     MIT
		| @link        https://github.com/humaan/Modaal
		| @deprecated  will be replaced with micromodal soon
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'modaal', self::url( 'vendor/modaal/modaal.min.js' ), [ 'jquery' ], self::VERSION, false );
		wp_enqueue_style( 'modaal', self::url( 'vendor/modaal/modaal.min.css' ), [], self::VERSION );
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function admin_enqueue_scripts() {

		// Load global styles
		if ( is_rtl() ) {
			wp_enqueue_style( 'sl_styles_global_rtl', self::url( 'admin/css/global-rtl.css' ), [], self::VERSION );
			wp_enqueue_style( 'sl_styles_global_rtl_extra', self::url( 'admin/css/global-rtl-extra.css' ), [], self::VERSION );
		} else {
			wp_enqueue_style( 'sl_styles_global', self::url( 'admin/css/global.css' ), [], self::VERSION );
		}

		/*
		|--------------------------------------------------------------------------
		| Modaal
		|
		| @license  MIT
		| @link     https://github.com/humaan/Modaal
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'modaal', self::url( 'vendor/modaal/modaal.min.js' ), [ 'jquery', 'underscore' ], self::VERSION, false );

		/*
		|--------------------------------------------------------------------------
		| Global JS
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'anwp-sl-js-global', self::url( 'admin/js/anwp-sl-global.js' ), [ 'jquery', 'underscore', 'modaal' ], self::VERSION, false );

		wp_localize_script(
			'anwp-sl-js-global',
			'anwpslGlobals',
			[
				'ajaxNonce'    => wp_create_nonce( 'ajax_anwpsl_nonce' ),
				'selectorHtml' => $this->include_selector_modaal(),
				'countries'    => sports_leagues()->helper->get_select2_formatted_options( sports_leagues()->data->get_countries() ),
				'teams'        => sports_leagues()->helper->get_select2_formatted_options( sports_leagues()->team->get_team_options() ),
				'seasons'      => sports_leagues()->helper->get_select2_formatted_options( sports_leagues()->season->get_season_options() ),
				'leagues'      => sports_leagues()->helper->get_select2_formatted_options( sports_leagues()->league->get_league_options() ),
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Select2 - 4.0.13
		|
		| @license  MIT
		| @link     https://select2.github.io
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'anwp-select2', self::url( 'vendor/select2/select2.full.min.js' ), [ 'jquery' ], '4.0.13', false );
		wp_enqueue_style( 'anwp-select2', self::url( 'vendor/select2/select2.min.css' ), [], '4.0.13' );

		// Load styles and scripts (limit to plugin pages)
		$current_screen = get_current_screen();

		$page_prefix          = sanitize_title( _x( 'Sports Leagues', 'admin menu title', 'sports-leagues' ) );
		$page_settings_prefix = sanitize_title( _x( 'Sports Settings', 'admin menu title', 'sports-leagues' ) );

		$plugin_pages = [
			'sl_player',
			'sl_team',
			'sl_venue',
			'sl_tournament',
			'sl_standing',
			'sl_game',
			'sl_official',
			'sl_staff',
			'toplevel_page_sports-leagues',

			// Settings
			'toplevel_page_sports_leagues_settings',
			'sl_standing_page_sl-standing-settings',

			'sports-settings_page_sports_leagues_config',
			$page_settings_prefix . '_page_sports_leagues_config',

			// toolbox
			'sports-leagues_page_sl-toolbox',
			$page_prefix . '_page_sl-toolbox',

			// Text Options
			'sports-settings_page_sports_leagues_text',
			$page_settings_prefix . '_page_sports_leagues_text',

			// Game Events
			'sports-settings_page_sports_leagues_event',
			$page_settings_prefix . '_page_sports_leagues_event',

			'sports-leagues_page_sl-plugin-customize',
			$page_prefix . '_page_sl-plugin-customize',

			'sports-leagues_page_sl-import-tool',
			$page_prefix . '_page_sl-import-tool',

			// Player Stats
			'sports-settings_page_sl-player-stats',
			$page_settings_prefix . '_page_sl-player-stats',

			// Shortcodes page
			'sports-leagues_page_sl-shortcodes',
			$page_prefix . '_page_sl-shortcodes',

			// Configurator page
			'sports-settings_page_sl-configurator',
			$page_settings_prefix . '_page_sl-configurator',

			// Game Admin List
			'edit-sl_game',

			// Tournament Admin List
			'edit-sl_tournament',
		];

		/**
		 * Filters plugin pages.
		 *
		 * @param array $plugin_pages List of plugin pages to load styles.
		 *
		 * @since 0.1.0
		 */
		$plugin_pages = apply_filters( 'sports-leagues/admin/plugin_pages', $plugin_pages );

		if ( in_array( $current_screen->id, [ 'sports-settings_page_sports_leagues_event', $page_settings_prefix . '_page_sports_leagues_event' ], true ) ) {
			wp_enqueue_media();
		}

		// Load Common files
		if ( in_array( $current_screen->id, $plugin_pages, true ) ) {

			/*
			|--------------------------------------------------------------------------
			| CSS Styles
			|--------------------------------------------------------------------------
			*/
			if ( is_rtl() ) {
				wp_enqueue_style( 'sl_styles_rtl', self::url( 'admin/css/styles-rtl.css' ), [], self::VERSION );
			} else {
				wp_enqueue_style( 'sl_styles', self::url( 'admin/css/styles.min.css' ), [], self::VERSION );
			}

			wp_enqueue_style( 'microtip', self::url( 'admin/css/microtip.min.css' ), [], self::VERSION );

			/*
			|--------------------------------------------------------------------------
			| World Flags Sprite
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_style( 'sl_flags', self::url( 'vendor/world-flags-sprite/stylesheets/flags32.css' ), [], self::VERSION );
			wp_enqueue_style( 'sl_flags_16', self::url( 'vendor/world-flags-sprite/stylesheets/flags16.css' ), [], self::VERSION );

			/*
			|--------------------------------------------------------------------------
			| FlatPickrStyles
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_style( 'sl_flatpickr', self::url( 'admin/css/flatpickr.min.css' ), [], '4.6.3' );
			wp_enqueue_style( 'sl_flatpickr_theme', self::url( 'admin/css/flatpickr_airbnb.css' ), [], '4.6.3' );

			/*
			|--------------------------------------------------------------------------
			| jExcel
			| * Author: Paul Hodel <paul.hodel@gmail.com>
			| * Website: https://bossanova.uk/jspreadsheet/v4/
			| * MIT License
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_style( 'jexcel-v4', self::url( 'vendor/jexcel/jexcel.css' ), [], '4.11.1' );
			wp_enqueue_script( 'jexcel-v4', self::url( 'vendor/jexcel/jexcel.js' ), [ 'jexcel-suites-v4' ], '4.11.1', true );
			wp_enqueue_style( 'jexcel-suites-v4', self::url( 'vendor/jexcel/jsuites.css' ), [], '5.0.14' );
			wp_enqueue_script( 'jexcel-suites-v4', self::url( 'vendor/jexcel/jsuites.js' ), [], '5.0.14', true );

			/*
			|--------------------------------------------------------------------------
			| Toastr
			|
			| @license  MIT
			| @link     https://github.com/CodeSeven/toastr
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'toastr', self::url( 'vendor/toastr/toastr.min.js' ), [], '2.1.4', false ); // ToDo deprecated - will be removed soon (replaced with notyf)

			/*
			|--------------------------------------------------------------------------
			| notyf
			|
			| @license  MIT
			| @link     https://github.com/caroso1222/notyf
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'notyf', self::url( 'vendor/notyf/notyf.min.js' ), [], '3.10.0', false );

			/*
			|--------------------------------------------------------------------------
			| Tippy.js
			| * (c) atomiks
			| * MIT
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'tippy', self::url( 'vendor/tippy/tippy-bundle.umd.min.js' ), [ 'popperjs' ], '6.2.3', true );
			wp_enqueue_style( 'tippy-light-border', self::url( 'vendor/tippy/light-border.css' ), [], '6.1.1' );

			/*
			|--------------------------------------------------------------------------
			| Popper.js (UMD)
			| * (c) Federico Zivolo
			| * MIT
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'popperjs', self::url( 'vendor/popperjs/popper.min.js' ), [], '2.2.3', true );

			/*
			|--------------------------------------------------------------------------
			| Main admin JS
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'sl_admin', self::url( 'admin/js/sl-admin.min.js' ), [ 'jquery', 'underscore', 'jquery-ui-sortable' ], self::VERSION, true );

			wp_localize_script(
				'sl_admin',
				'anwp',
				[
					'rest_root'   => esc_url_raw( rest_url() ),
					'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
					'admin_url'   => admin_url(),
					'spinner_url' => admin_url( '/images/spinner.gif' ),
				]
			);

			wp_localize_script( 'sl_admin', 'sl_admin_l10n', $this->data->get_l10n_admin() );

			/*
			|--------------------------------------------------------------------------
			| Load Apps
			|--------------------------------------------------------------------------
			*/
			$this->app_scripts_loader( $current_screen->id );
		}

		// Load Google Maps only for Venue page
		if ( 'sl_venue' === $current_screen->id ) {

			if ( Sports_Leagues_Options::get_value( 'google_maps_api' ) ) {
				$google_maps_api_key = '?key=' . Sports_Leagues_Options::get_value( 'google_maps_api' );

				wp_enqueue_script( 'google-maps-api-3', '//maps.googleapis.com/maps/api/js' . $google_maps_api_key . '&libraries=places', [], 3, false );
			}
		}

		if ( 'edit-sl_game' === $current_screen->id ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		if ( 'toplevel_page_sports-leagues' === $current_screen->id ) {
			wp_enqueue_script(
				'sl-admin-dashboard',
				self::url( 'admin/js/dashboard/dashboard.js' ),
				[ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-api-fetch', 'react', 'react-dom' ],
				self::VERSION,
				true
			);

			wp_enqueue_style(
				'sl-admin-dashboard',
				self::url( 'admin/js/dashboard/dashboard.css' ),
				[ 'wp-components' ],
				self::VERSION
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'sl-admin-dashboard', 'sports-leagues' );
			}

			wp_localize_script(
				'sl-admin-dashboard',
				'slDashboardData',
				sports_leagues()->data->get_dashboard_data()
			);
		}

		$events_page = [
			'sports-settings_page_sports_leagues_event',
			$page_settings_prefix . '_page_sports_leagues_event',
		];

		if ( in_array( $current_screen->id, $events_page, true ) && ! function_exists( 'sports_leagues_premium' ) ) {
			wp_enqueue_script(
				'sl-admin-events',
				self::url( 'admin/js/events/events.js' ),
				[ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-api-fetch', 'react', 'react-dom' ],
				self::VERSION,
				true
			);

			wp_enqueue_style(
				'sl-admin-events',
				self::url( 'admin/js/events/events.css' ),
				[ 'wp-components' ],
				self::VERSION
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'sl-admin-events', 'sports-leagues' );
			}

			wp_localize_script(
				'sl-admin-events',
				'slEventsData',
				sports_leagues()->data->get_events_data()
			);
		}
	}

	public function app_scripts_loader( $current_screen_id ) {

		wp_register_script( 'vuejs-sl-3', self::url( 'vendor/vuejs/vue.runtime.global.prod.min.js' ), [], '3.3.4', false );

		$loader_data     = [];
		$page_prefix     = sanitize_title( _x( 'Sports Leagues', 'admin menu title', 'sports-leagues' ) );
		$settings_prefix = sanitize_title( _x( 'Sports Settings', 'admin menu title', 'sports-leagues' ) );

		if ( in_array( $current_screen_id, [ 'sports-leagues_page_sl-toolbox', $page_prefix . '_page_sl-toolbox' ], true ) ) {
			/*
			|--------------------------------------------------------------------------
			| Toolbox
			|--------------------------------------------------------------------------
			*/
			$loader_data['src']  = self::url( 'admin/js/app/toolbox.min.js' );
			$loader_data['deps'] = [ 'vuejs-sl-3' ];

		} elseif ( in_array( $current_screen_id, [ 'sports-settings_page_sl-player-stats', $settings_prefix . '_page_sl-player-stats' ], true ) ) {
			/*
			|--------------------------------------------------------------------------
			| Player Stats
			|--------------------------------------------------------------------------
			*/
			$loader_data['src']  = self::url( 'admin/js/app/player-stats.min.js' );
			$loader_data['deps'] = [ 'vuejs-sl-3' ];

		} elseif ( in_array( $current_screen_id, [ 'sports-settings_page_sl-configurator', $settings_prefix . '_page_sl-configurator' ], true ) ) {
			/*
			|--------------------------------------------------------------------------
			| Configurator
			|--------------------------------------------------------------------------
			*/
			$loader_data['src']  = self::url( 'admin/js/app/configurator.min.js' );
			$loader_data['deps'] = [ 'vuejs-sl-3' ];

		} elseif ( in_array( $current_screen_id, [ 'sports-leagues_page_sl-import-tool', $page_prefix . '_page_sl-import-tool' ], true ) ) {
			/*
			|--------------------------------------------------------------------------
			| Import Tool
			|--------------------------------------------------------------------------
			*/
			$loader_data['src']  = self::url( 'admin/js/app/import-tool.min.js' );
			$loader_data['deps'] = [ 'vuejs-sl-3' ];

		} elseif ( 'sl_game' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| Game Edit
			|--------------------------------------------------------------------------
			*/
			$loader_data['src']  = self::url( 'admin/js/app/game.min.js' );
			$loader_data['deps'] = [ 'vuejs-sl-3', 'sl_admin', 'jexcel-v4' ];

		} elseif ( 'sl_team' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| Team Edit
			|--------------------------------------------------------------------------
			*/
			$loader_data['src'] = self::url( 'admin/js/app/roster.min.js' );
		} elseif ( 'sl_standing_page_sl-standing-settings' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| Standing Settings
			|--------------------------------------------------------------------------
			*/
			$loader_data['src'] = self::url( 'admin/js/app/standing-settings.min.js' );
		} elseif ( 'sl_tournament' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| Game Edit
			|--------------------------------------------------------------------------
			*/
			$loader_data['src'] = self::url( 'admin/js/app/tournament.min.js' );
		} elseif ( 'sl_standing' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| Standing
			|--------------------------------------------------------------------------
			*/
			if ( 'yes' !== get_post_meta( get_the_ID(), '_sl_fixed', true ) ) {
				$loader_data['src'] = self::url( 'admin/js/app/standing-setup.min.js' );
			} else {
				$loader_data['src']  = self::url( 'admin/js/app/standing.min.js' );
				$loader_data['deps'] = [ 'vuejs-sl-3', 'sl_admin', 'wp-color-picker' ];
				wp_enqueue_style( 'wp-color-picker' );
			}
		}

		/**
		 * Modify App loader scripts and data
		 *
		 * @since 0.11.1
		 *
		 * @param array $loader_data
		 * @param string $current_screen_id
		 */
		$loader_data = apply_filters( 'sports-leagues/admin/app_js_loader', $loader_data, $current_screen_id );

		/*
		|--------------------------------------------------------------------------
		| Try to load script
		|--------------------------------------------------------------------------
		*/
		if ( is_array( $loader_data ) && ! empty( $loader_data['src'] ) ) {
			$script_data = wp_parse_args(
				$loader_data,
				[
					'src'       => '',
					'deps'      => [ 'vuejs-sl-3', 'sl_admin' ],
					'in_footer' => true,
				]
			);

			wp_enqueue_script( 'sl-admin-vue', $script_data['src'], $script_data['deps'], self::VERSION, $script_data['in_footer'] );
		}
	}

	/**
	 * Register widgets.
	 *
	 * @since 0.1.0
	 */
	public function register_widgets() {

		// include classes
		self::include_file( 'includes/widgets/class-sports-leagues-widget' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-player' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-standing' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-next-game' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-last-game' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-teams' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-games' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-birthdays' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-players-stats' );
		self::include_file( 'includes/widgets/class-sports-leagues-widget-video' );

		// register widgets
		register_widget( 'Sports_Leagues_Widget_Player' );
		register_widget( 'Sports_Leagues_Widget_Standing' );
		register_widget( 'Sports_Leagues_Widget_Next_Game' );
		register_widget( 'Sports_Leagues_Widget_Last_Game' );
		register_widget( 'Sports_Leagues_Widget_Teams' );
		register_widget( 'Sports_Leagues_Widget_Games' );
		register_widget( 'Sports_Leagues_Widget_Birthdays' );
		register_widget( 'Sports_Leagues_Widget_Players_Stats' );
		register_widget( 'Sports_Leagues_Widget_Video' );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'cache':
			case 'config':
			case 'customizer':
			case 'game':
			case 'data':
			case 'event':
			case 'helper':
			case 'league':
			case 'options':
			case 'path':
			case 'player':
			case 'player_stats':
			case 'official':
			case 'season':
			case 'standing':
			case 'staff':
			case 'team':
			case 'template':
			case 'text':
			case 'tournament':
			case 'url':
			case 'venue':
			case 'blocks':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @param  string $filename Name of the file to be included.
	 *
	 * @return boolean          Result of include call.
	 * @since  0.1.0
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );

		if ( file_exists( $file ) ) {
			return include_once $file;
		}

		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       Directory and path.
	 * @since  0.1.0
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? : trailingslashit( dirname( __FILE__ ) );

		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? : trailingslashit( plugin_dir_url( __FILE__ ) );

		return $url . $path;
	}

	/**
	 * Load template partial.
	 * Proxy for template rendering class method.
	 *
	 * @param array|object $atts
	 * @param string       $slug
	 * @param string       $layout
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function load_partial( $atts, $slug, $layout = '' ) {

		$layout = empty( $layout ) ? '' : ( '-' . sanitize_key( $layout ) );
		return $this->template->set_template_data( $atts )->get_template_part( $slug, $layout );
	}

	/**
	 * Get list of plugin post types.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_post_types() {
		return $this->plugin_post_types;
	}

	/**
	 * Get Options value.
	 *
	 * @param  string $value
	 * @param  mixed $default
	 *
	 * @return string
	 * @since  0.1.0
	 */
	public function get_option_value( $value, $default = false ) {
		return Sports_Leagues_Options::get_value( $value, $default );
	}

	/**
	 * Get active season.
	 *
	 * @return int
	 * @since 0.1.0
	 */
	public function get_active_season() {

		return absint( $this->get_option_value( 'active_season' ) ? : sports_leagues()->season->get_max_season_id() );
	}

	/**
	 * Get POST season.
	 *
	 * @return int
	 * @since 0.6.2
	 */
	public function get_post_season() {

		static $season_id = null;

		if ( null === $season_id ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $_GET['season'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$maybe_season_id = sports_leagues()->season->get_season_id_by_slug( sanitize_key( $_GET['season'] ) );

				if ( ! empty( $maybe_season_id ) ) {
					$season_id = $maybe_season_id;
				}
			}

			if ( empty( $season_id ) ) {
				$season_id = $this->get_active_season();
			}
		}

		return absint( $season_id );
	}

	/**
	 * Converts a string to a bool.
	 * From WOO
	 *
	 * @since 0.1.0
	 * @param string $string String to convert.
	 * @return bool
	 */
	public static function string_to_bool( $string ) {
		return is_bool( $string ) ? $string : ( 1 === $string || 'yes' === $string || 'true' === $string || '1' === $string );
	}

	/**
	 * Return localized menu prefix.
	 *
	 * @return string
	 * @since 0.5.12
	 */
	public function get_l10n_menu_prefix() {
		return sanitize_title( _x( 'Sports Leagues', 'admin menu title', 'sports-leagues' ) );
	}

	/**
	 * Return localized settings menu prefix.
	 *
	 * @return string
	 * @since 0.6.0
	 */
	public function get_l10n_menu_settings_prefix() {
		return sanitize_title( _x( 'Sports Settings', 'admin menu title', 'sports-leagues' ) );
	}

	/**
	 * Load selector modal
	 *
	 * @return string
	 * @since 0.9.0
	 */
	public function include_selector_modaal() {
		ob_start();
		?>
		<div id="anwp-sl-selector-modaal">
			<div class="anwp-sl-shortcode-modal__header">
				<h3 style="margin: 0">AnWP Selector: <span id="anwp-sl-selector-modaal__header-context"></span></h3>
			</div>
			<div class="anwp-sl-shortcode-modal__content" id="anwp-sl-selector-modaal__search-bar">
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--player anwp-sl-selector-modaal__bar-group--team anwp-sl-selector-modaal__bar-group--tournament anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search"><?php echo esc_html__( 'start typing name or title ...', 'sports-leagues' ); ?></label>
					<input name="s" type="text" id="anwp-sl-selector-modaal__search" value="" class="sl-shortcode-attr code">
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--player anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-team"><?php echo esc_html__( 'Team', 'sports-leagues' ); ?></label>
					<select name="teams" id="anwp-sl-selector-modaal__search-team" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--player anwp-sl-selector-modaal__bar-group--team anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-country"><?php echo esc_html__( 'Country/Nationality', 'sports-leagues' ); ?></label>
					<select name="countries" id="anwp-sl-selector-modaal__search-country" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--game anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-team-a"><?php echo esc_html__( 'Team A', 'sports-leagues' ); ?></label>
					<select name="teams" id="anwp-sl-selector-modaal__team-a" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--game anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-team-b"><?php echo esc_html__( 'Team B', 'sports-leagues' ); ?></label>
					<select name="teams" id="anwp-sl-selector-modaal__search-team-b" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--game anwp-sl-selector-modaal__bar-group--tournament anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-season"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></label>
					<select name="seasons" id="anwp-sl-selector-modaal__search-season" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
				<div class="anwp-sl-selector-modaal__bar-group d-none anwp-sl-selector-modaal__bar-group--tournament anwp-mr-2 anwp-mt-2">
					<label for="anwp-sl-selector-modaal__search-league"><?php echo esc_html__( 'League', 'sports-leagues' ); ?></label>
					<select name="leagues" id="anwp-sl-selector-modaal__search-league" class="anwp-selector-select2">
						<option value="">- select -</option>
					</select>
				</div>
			</div>
			<div class="anwp-sl-shortcode-modal__footer">
				<h4 style="margin: 0"><?php echo esc_html__( 'Selected Values', 'sports-leagues' ); ?>:
					<span class="spinner" id="anwp-sl-selector-modaal__initial-spinner" style="float: none; margin-top: 0;"></span>
				</h4>
				<div id="anwp-sl-selector-modaal__selected"></div>
				<div id="anwp-sl-selector-modaal__selected-none">- <?php echo esc_html__( 'none', 'sports-leagues' ); ?> -</div>
			</div>
			<div class="anwp-sl-shortcode-modal__content" id="anwp-sl-selector-modaal__content"></div>
			<span class="spinner" id="anwp-sl-selector-modaal__search-spinner"></span>
			<div class="anwp-sl-shortcode-modal__footer" id="anwp-sl-selector-modaal__footer">
				<button id="anwp-sl-selector-modaal__cancel" class="button"><?php echo esc_html__( 'Cancel', 'sports-leagues' ); ?></button>
				<button id="anwp-sl-selector-modaal__insert" class="button button-primary"><?php echo esc_html__( 'Insert', 'sports-leagues' ); ?></button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get active season.
	 *
	 * @param int    $instance_id
	 * @param string $instance_type
	 *
	 * @return int
	 * @since 0.11.0
	 */
	public function get_active_instance_season( $instance_id, $instance_type ) {

		// Get season ID from plugin options.
		$season_id = Sports_Leagues_Options::get_value( 'active_season' );

		if ( 'yes' !== Sports_Leagues_Options::get_value( 'hide_not_used_seasons' ) ) {

			return absint( $season_id ? : sports_leagues()->season->get_max_season_id() );

		} elseif ( absint( $instance_id ) ) {

			$filtered_season_slugs = sports_leagues()->helper->get_filtered_seasons( $instance_type, $instance_id );

			// Check if active system season exists in team's seasons
			if ( $season_id ) {
				$season_slug = sports_leagues()->season->get_season_slug_by_id( $season_id );

				if ( in_array( $season_slug, $filtered_season_slugs, true ) ) {
					return (int) $season_id;
				}
			}

			if ( ! empty( $filtered_season_slugs ) ) {
				rsort( $filtered_season_slugs, SORT_NUMERIC );
				$season_id = sports_leagues()->season->get_season_id_by_slug( $filtered_season_slugs[0] );
			}
		}

		return (int) $season_id;
	}

	/**
	 * Add modaal wrappers.
	 *
	 * @return void
	 * @since 0.12.4
	 */
	public function add_modal_wrappers() {
		?>
		<div id="anwp-sl-modal--stat-players" class="anwp-sl-modal" aria-hidden="true">
			<div class="anwp-sl-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="anwp-sl-modal__container anwp-b-wrap anwp-overflow-y-auto anwp-w-500 px-3" role="dialog" aria-modal="true">
					<button tabindex="-1" class="anwp-sl-modal__close" aria-label="Close modal" type="button" data-micromodal-close></button>

					<div id="anwp-sl-modal-stat-players__players" class="shortcode-stat_players stat-players anwp-sl-border anwp-border-light"></div>
					<div id="anwp-sl-modal-stat-players__loader" class="anwp-text-center mt-3 d-none d-print-none">
						<img src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>" alt="spinner" class="d-inline-block">
					</div>
					<div class="d-none mt-2 anwp-text-center" id="anwp-sl-modal-stat-players__load-more">
						<div class="position-relative anwp-sl-btn anwp-text-base w-100 anwp-cursor-pointer">
							<?php echo esc_html( Sports_Leagues_Text::get_value( 'general__load_more', __( 'load more', 'sports-leagues' ) ) ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function is_editing_block_on_backend() {
		return defined( 'REST_REQUEST' ) && true === REST_REQUEST && 'edit' === filter_input( INPUT_GET, 'context', FILTER_SANITIZE_SPECIAL_CHARS );
	}

	/**
	 * Renders notice if Data Migration is required
	 *
	 * @since 0.13.0
	 */
	public function notice_data_migration_required() {

		$active_page = $_GET['page'] ?? ''; // phpcs:ignore

		/*
		|--------------------------------------------------------------------
		| v0.13.0
		|--------------------------------------------------------------------
		*/
		if ( absint( get_option( 'sl_data_schema' ) ) < 13 && 'sl-toolbox' !== $active_page ) {
			?>
			<div class="notice anwp-sl-cmb2-notice">
				<img src="<?php echo esc_url( self::url( 'admin/img/sl-icon.png' ) ); ?>" alt="fl icon">
				<h3>Important Notice: Data Migration Required</h3>
				<p>v0.13.0 introduces new player statistics structure that enhances performance and add possibilities to make complex queries. Open the Database Updater tool to migrate your data to the new format.</p>
				<p>
					<a href="<?php echo esc_url( self_admin_url( 'admin.php?page=sl-toolbox' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Database Updater', 'sports-leagues' ); ?></a>
				</p>
				<p class="anwp-notice-clear-both"></p>
			</div>
			<?php
		}
	}


}
