<?php
/**
 * Sports Leagues Text.
 *
 * @since   0.5.14
 * @package Sports_Leagues
 */

/**
 * Sports_Leagues_Text class.
 */
class Sports_Leagues_Text {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 */
	protected static $key = 'sports_leagues_text';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 */
	protected static $metabox_id = 'sports_leagues_text_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor.
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Set our title.
		$this->title = esc_html__( 'Sports Leagues', 'sports-leagues' ) . ' :: ' . esc_html__( 'Text Options', 'sports-leagues' );
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		add_action( 'cmb2_admin_init', [ $this, 'add_config_page_metabox' ] );

		// Inject some HTML before CMB2 form
		add_action( 'cmb2_before_options-page_form_sports_leagues_text_metabox', [ $this, 'cmb2_before_metabox' ] );
	}

	/**
	 * Special HTML before CMB2 metabox.
	 */
	public function cmb2_before_metabox() {

		/*
		|--------------------------------------------------------------------
		| Start Output
		|--------------------------------------------------------------------
		*/
		ob_start();
		?>
		<div class="cmb2-wrap form-table anwp-b-wrap anwp-settings">

			<div class="d-flex align-items-center mb-2">
				<span class="mr-2"><?php echo esc_html__( 'Search Text String', 'sports-leagues' ); ?>:</span>
				<input type="text" id="anwp-sl-live-text-search">
			</div>

			<div class="cmb2-metabox cmb-field-list">
				<div class="cmb-row bg-light">
					<div class="row align-items-center">
						<div class="col-sm-4"><?php echo esc_html__( 'Default', 'sports-leagues' ); ?></div>
						<div class="col-sm-4"><?php echo esc_html__( 'New', 'sports-leagues' ); ?></div>
						<div class="col-sm-4"><?php echo esc_html__( 'Context', 'sports-leagues' ); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add custom fields to the options page.
	 */
	public function add_config_page_metabox() {

		// Add our CMB2 metabox.
		$cmb = new_cmb2_box(
			[
				'id'           => self::$metabox_id,
				'title'        => $this->title,
				'object_types' => [ 'options-page' ],
				'classes'      => 'anwp-b-wrap anwp-settings',
				'option_key'   => self::$key,
				'show_names'   => false,
				'capability'   => 'manage_options',
				'parent_slug'  => 'sports_leagues_settings',
				'menu_title'   => esc_html__( 'Text Options', 'sports-leagues' ),
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Game Day', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: header', 'sports-leagues' ),
				'id'   => 'game__header__game_day',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Attendance', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: header', 'sports-leagues' ),
				'id'   => 'game__header__attendance',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'game preview', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: header', 'sports-leagues' ),
				'id'   => 'game__header__game_preview',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Full Time', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: header', 'sports-leagues' ),
				'id'   => 'game__header__full_time',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Latest Games', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: latest', 'sports-leagues' ),
				'id'   => 'game__latest__latest_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Players', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: players', 'sports-leagues' ),
				'id'   => 'game__players__players',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Staff', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: staff', 'sports-leagues' ),
				'id'   => 'game__staff__staff',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Game Summary', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: summary', 'sports-leagues' ),
				'id'   => 'game__summary__game_summary',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Team Statistics', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: team-stats', 'sports-leagues' ),
				'id'   => 'game__team_stats__team_statistics',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Players Statistics', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: player-stats', 'sports-leagues' ),
				'id'   => 'game__player_stats__players_statistics',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Player', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: player-stats', 'sports-leagues' ),
				'id'   => 'game__player_stats__player',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Video', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: video', 'sports-leagues' ),
				'id'   => 'game__video__video',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Gallery', 'sports-leagues' ),
				'desc' => esc_html__( 'game :: gallery', 'sports-leagues' ),
				'id'   => 'game__game_gallery',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Gallery', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: gallery', 'sports-leagues' ),
				'id'   => 'team__team_gallery',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Gallery', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: gallery', 'sports-leagues' ),
				'id'   => 'player__player_gallery',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Played Games', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: player games', 'sports-leagues' ),
				'id'   => 'player__player_games__played_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html_x( 'Group', 'official group label', 'sports-leagues' ),
				'desc' => esc_html__( 'official :: header', 'sports-leagues' ),
				'id'   => 'official__header__group',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Nationality', 'sports-leagues' ),
				'desc' => esc_html__( 'official :: header', 'sports-leagues' ),
				'id'   => 'official__header__nationality',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'official :: header', 'sports-leagues' ),
				'id'   => 'official__header__place_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date Of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'official :: header', 'sports-leagues' ),
				'id'   => 'official__header__date_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Age', 'sports-leagues' ),
				'desc' => esc_html__( 'official :: header', 'sports-leagues' ),
				'id'   => 'official__header__age',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Position', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__position',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Full Name', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__full_name',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'National Team', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__national_team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Current Team', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__current_team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Nationality', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__nationality',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__place_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date Of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__date_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date Of Death', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__date_of_death',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Age', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__age',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Weight', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__weight',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Height', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: header', 'sports-leagues' ),
				'id'   => 'player__header__height',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'years', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: birthdays', 'sports-leagues' ),
				'id'   => 'player__birthdays__years',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: player stats', 'sports-leagues' ),
				'id'   => 'player__player_stats__date',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'VS', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: player stats', 'sports-leagues' ),
				'id'   => 'player__player_stats__vs',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Player Stats', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: player stats', 'sports-leagues' ),
				'id'   => 'player__player_stats__player_stats',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No Data', 'sports-leagues' ),
				'desc' => esc_html__( 'player :: player stats', 'sports-leagues' ),
				'id'   => 'player__player_stats__no_data',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Job', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__job',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Current Team', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__current_team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Nationality', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__nationality',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__place_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date Of Birth', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__date_of_birth',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Age', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: header', 'sports-leagues' ),
				'id'   => 'staff__header__age',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Career', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__career',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Career', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__career',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Club', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__club',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Job Title', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__job_title',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'From', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__from',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'To', 'sports-leagues' ),
				'desc' => esc_html__( 'staff :: history', 'sports-leagues' ),
				'id'   => 'staff__history__to',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Finished Games', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: finished', 'sports-leagues' ),
				'id'   => 'team__finished__finished_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No games', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: finished', 'sports-leagues' ),
				'id'   => 'team__finished__no_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'City', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__city',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Country', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__country',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Address', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__address',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Website', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__website',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Founded', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__founded',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Conference', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__conference',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Division', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__division',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Venue', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__venue',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Social', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: header', 'sports-leagues' ),
				'id'   => 'team__header__social',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Upcoming', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: upcoming', 'sports-leagues' ),
				'id'   => 'team__upcoming__upcoming',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No games', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: upcoming', 'sports-leagues' ),
				'id'   => 'team__upcoming__no_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No data', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: players stats', 'sports-leagues' ),
				'id'   => 'team__team_players_stats__no_data',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Player', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: players stats', 'sports-leagues' ),
				'id'   => 'team__team_players_stats__player',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Players Stats', 'sports-leagues' ),
				'desc' => esc_html__( 'team :: players stats', 'sports-leagues' ),
				'id'   => 'team__team_players_stats__players_stats',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Game Day', 'sports-leagues' ),
				'desc' => esc_html__( 'tournament :: stage', 'sports-leagues' ),
				'id'   => 'tournament__stage__game_day',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'City', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__city',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Teams', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__teams',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Address', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__address',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Capacity', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__capacity',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Opened', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__opened',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Website', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__website',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Upcoming Games', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__upcoming_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Finished Games', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__finished_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Venue Gallery', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__venue_gallery',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Location', 'sports-leagues' ),
				'desc' => esc_html__( 'venue', 'sports-leagues' ),
				'id'   => 'venue__location',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No Upcoming Birthdays', 'sports-leagues' ),
				'desc' => esc_html__( 'widget :: birthdays', 'sports-leagues' ),
				'id'   => 'widget__birthdays__no_upcoming_birthdays',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( '#', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: players stats', 'sports-leagues' ),
				'id'   => 'shortcode__players_stats__rank',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Player', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: players stats', 'sports-leagues' ),
				'id'   => 'shortcode__players_stats__player',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Games Played', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: players stats', 'sports-leagues' ),
				'id'   => 'shortcode__players_stats__games_played',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'GP', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: players stats', 'sports-leagues' ),
				'id'   => 'shortcode__players_stats__gp',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Game Day', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: games', 'sports-leagues' ),
				'id'   => 'shortcode__games__game_day',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Team', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: player card', 'sports-leagues' ),
				'id'   => 'shortcode__player_card__team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Roster', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: roster', 'sports-leagues' ),
				'id'   => 'shortcode__roster__roster',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No players in roster', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: roster', 'sports-leagues' ),
				'id'   => 'shortcode__roster__no_players_in_roster',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Age', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: roster', 'sports-leagues' ),
				'id'   => 'shortcode__roster__age',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Nationality', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: roster', 'sports-leagues' ),
				'id'   => 'shortcode__roster__nationality',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Position', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: roster', 'sports-leagues' ),
				'id'   => 'shortcode__roster__position',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Staff', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: staff', 'sports-leagues' ),
				'id'   => 'shortcode__staff__staff',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Age', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: staff', 'sports-leagues' ),
				'id'   => 'shortcode__staff__age',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Nationality', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: staff', 'sports-leagues' ),
				'id'   => 'shortcode__staff__nationality',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Job', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: staff', 'sports-leagues' ),
				'id'   => 'shortcode__staff__job',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'show in full screen', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: standing', 'sports-leagues' ),
				'id'   => 'shortcode__standing__show_in_full_screen',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Team', 'sports-leagues' ),
				'desc' => esc_html__( 'shortcode :: standing', 'sports-leagues' ),
				'id'   => 'shortcode__standing__team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Suspended', 'sports-leagues' ),
				'desc' => 'game :: missing players',
				'id'   => 'game__missing__suspended',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Injured', 'sports-leagues' ),
				'desc' => 'game :: missing players',
				'id'   => 'game__missing__injured',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Missing Players', 'sports-leagues' ),
				'desc' => 'game :: missing players',
				'id'   => 'game__missing__missing_players',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Missed Games', 'sports-leagues' ),
				'desc' => 'player :: missed games',
				'id'   => 'player__missed__missed_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Reason', 'sports-leagues' ),
				'desc' => 'player :: missed games',
				'id'   => 'player__missed__reason',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Date', 'sports-leagues' ),
				'desc' => 'player :: missed games',
				'id'   => 'player__missed__date',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Against', 'sports-leagues' ),
				'desc' => 'player :: missed games',
				'id'   => 'player__missed__against',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Game Postponed', 'sports-leagues' ),
				'desc' => 'game :: header',
				'id'   => 'game__game__game_postponed',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Total Stats', 'sports-leagues' ),
				'desc' => 'player :: total stats',
				'id'   => 'player__player_total_stats__total_stats',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Tournament Stage', 'sports-leagues' ),
				'desc' => 'player :: total stats',
				'id'   => 'player__player_total_stats__tournament_stage',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Team', 'sports-leagues' ),
				'desc' => 'player :: total stats',
				'id'   => 'player__player_total_stats__team',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Totals', 'sports-leagues' ),
				'desc' => 'player :: total stats',
				'id'   => 'player__player_total_stats__totals',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No Data', 'sports-leagues' ),
				'desc' => 'player :: total stats',
				'id'   => 'player__player_total_stats__no_data',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Upcoming', 'sports-leagues' ),
				'desc' => 'official :: fixtures',
				'id'   => 'official__fixtures__upcoming',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Finished Games', 'sports-leagues' ),
				'desc' => 'official :: games',
				'id'   => 'official__games__finished',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No Data', 'sports-leagues' ),
				'desc' => 'official :: games',
				'id'   => 'official__games__no_data',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'No Data', 'sports-leagues' ),
				'desc' => 'official :: games',
				'id'   => 'staff__games__no_data',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Upcoming', 'sports-leagues' ),
				'desc' => 'staff :: fixtures',
				'id'   => 'staff__fixtures__upcoming',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Finished Games', 'sports-leagues' ),
				'desc' => 'staff :: games',
				'id'   => 'staff__games__finished',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html_x( 'days', 'flip countdown', 'sports-leagues' ),
				'desc' => 'game :: countdown',
				'id'   => 'data__countdown__days',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html_x( 'hours', 'flip countdown', 'sports-leagues' ),
				'desc' => 'game :: countdown',
				'id'   => 'data__countdown__hours',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html_x( 'minutes', 'flip countdown', 'sports-leagues' ),
				'desc' => 'game :: countdown',
				'id'   => 'data__countdown__minutes',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html_x( 'seconds', 'flip countdown', 'sports-leagues' ),
				'desc' => 'game :: countdown',
				'id'   => 'data__countdown__seconds',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'All', 'sports-leagues' ),
				'desc' => 'tournament :: tabs',
				'id'   => 'tournament__tabs__all',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Latest Scores', 'sports-leagues' ),
				'desc' => 'tournament :: structure',
				'id'   => 'tournament__structure__latest_scores',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Upcoming Games', 'sports-leagues' ),
				'desc' => 'tournament :: structure',
				'id'   => 'tournament__structure__upcoming_games',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Standing Tables', 'sports-leagues' ),
				'desc' => 'tournament :: structure',
				'id'   => 'tournament__structure__standing_tables',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Load More', 'sports-leagues' ),
				'desc' => 'general',
				'id'   => 'general__load_more',
				'type' => 'anwp_text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Show full list', 'sports-leagues' ),
				'desc' => 'players :: stats',
				'id'   => 'players__stats__show_full_list',
				'type' => 'anwp_text',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.15
		 */
		$extra_fields = apply_filters( 'sports-leagues/text/text_extra_options', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {

			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( self::$key, $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( self::$key, $default );

		$val = $default;

		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

	/**
	 * Returns config options for selected value.
	 *
	 * @param string $value
	 *
	 * @return array
	 */
	public function get_options( $value ) {

		$options = self::get_value( $value );

		if ( ! empty( $options ) && is_array( $options ) ) {
			return $options;
		}

		return [];
	}
}
