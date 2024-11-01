<?php
/**
 * Sports Leagues Options.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

/**
 * Sports_Leagues_Options class.
 *
 * @since 0.1.0
 */
class Sports_Leagues_Options {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Permalink settings.
	 *
	 * @var array
	 * @since 0.5.11
	 */
	protected $permalinks = [];

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected static $key = 'sports_leagues_settings';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected static $metabox_id = 'sports_leagues_settings_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  0.1.0
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
	 * @since  0.1.0
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Set our title.
		$this->title = esc_html__( 'Sports Leagues :: Settings', 'sports-leagues' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		add_action( 'cmb2_admin_init', [ $this, 'add_setting_page_main_metabox' ] );

		add_action( 'cmb2_before_options-page_form_sports_leagues_settings_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_options-page_form_sports_leagues_settings_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Override permalink structure
		add_action( 'current_screen', [ $this, 'manage_permalink_structure' ] );
	}

	/**
	 * Manage permalink structure.
	 *
	 * @since 0.5.11
	 */
	public function manage_permalink_structure() {
		$screen = get_current_screen();

		if ( 'options-permalink' !== $screen->id ) {
			return;
		}

		// Get saved permalinks structure
		$this->permalinks = $this->get_permalink_structure();

		$this->permalinks_options();
		$this->save_permalinks_options();
	}

	/**
	 * Rendering permalinks input fields.
	 *
	 * @since 0.5.11
	 */
	public function permalinks_options() {

		add_settings_field(
			'sl_game_base_slug',
			esc_html__( 'SL Game base', 'sports-leagues' ),
			[ $this, 'permalink_game_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_tournament_base_slug',
			esc_html__( 'SL Tournament base', 'sports-leagues' ),
			[ $this, 'permalink_tournament_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_team_base_slug',
			esc_html__( 'SL Team base', 'sports-leagues' ),
			[ $this, 'permalink_team_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_player_base_slug',
			esc_html__( 'SL Player base', 'sports-leagues' ),
			[ $this, 'permalink_player_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_official_base_slug',
			esc_html__( 'SL Official base', 'sports-leagues' ),
			[ $this, 'permalink_official_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_staff_base_slug',
			esc_html__( 'SL Staff base', 'sports-leagues' ),
			[ $this, 'permalink_staff_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'sl_venue_base_slug',
			esc_html__( 'SL Venue base', 'sports-leagues' ),
			[ $this, 'permalink_venue_slug_input' ],
			'permalink',
			'optional'
		);
	}

	/**
	 * Rendering Game input field.
	 *
	 * @since 0.5.11
	 */
	public function permalink_game_slug_input() {
		?>
		<input name="sl_game_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['game'] ); ?>" placeholder="<?php echo esc_attr_x( 'game', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Tournament input field.
	 *
	 * @since 0.5.11
	 */
	public function permalink_tournament_slug_input() {
		?>
		<input name="sl_tournament_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['tournament'] ); ?>" placeholder="<?php echo esc_attr_x( 'tournament', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Team input field.
	 *
	 * @since 0.5.11
	 */
	public function permalink_team_slug_input() {
		?>
		<input name="sl_team_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['team'] ); ?>" placeholder="<?php echo esc_attr_x( 'team', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Player input field.
	 *
	 * @since 0.5.11
	 */
	public function permalink_player_slug_input() {
		?>
		<input name="sl_player_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['player'] ); ?>" placeholder="<?php echo esc_attr_x( 'player', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Official input field.
	 *
	 * @since 0.5.13
	 */
	public function permalink_official_slug_input() {
		?>
		<input name="sl_official_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['official'] ); ?>" placeholder="<?php echo esc_attr_x( 'official', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Staff input field.
	 *
	 * @since 0.5.14
	 */
	public function permalink_staff_slug_input() {
		?>
		<input name="sl_staff_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['staff'] ); ?>" placeholder="<?php echo esc_attr_x( 'staff', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Rendering Venue input field.
	 *
	 * @since 0.5.11
	 */
	public function permalink_venue_slug_input() {
		?>
		<input name="sl_venue_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['venue'] ); ?>" placeholder="<?php echo esc_attr_x( 'venue', 'slug', 'sports-leagues' ); ?>"/>
		<?php
	}

	/**
	 * Save the permalinks settings.
	 *
	 * @since 0.5.11
	 */
	public function save_permalinks_options() {
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_POST['permalink_structure'] ) ) {

			$permalink_settings = wp_parse_args(
				$this->get_permalink_structure(),
				[
					'game'       => 'game',
					'tournament' => 'tournament',
					'team'       => 'team',
					'player'     => 'player',
					'official'   => 'official',
					'staff'      => 'staff',
					'venue'      => 'venue',
				]
			);

			$permalink_settings['game']       = isset( $_POST['sl_game_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_game_base_slug'], $permalink_settings['game'] ) : $permalink_settings['game'];
			$permalink_settings['tournament'] = isset( $_POST['sl_tournament_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_tournament_base_slug'], $permalink_settings['tournament'] ) : $permalink_settings['tournament'];
			$permalink_settings['team']       = isset( $_POST['sl_team_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_team_base_slug'], $permalink_settings['team'] ) : $permalink_settings['team'];
			$permalink_settings['player']     = isset( $_POST['sl_player_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_player_base_slug'], $permalink_settings['player'] ) : $permalink_settings['player'];
			$permalink_settings['official']   = isset( $_POST['sl_official_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_official_base_slug'], $permalink_settings['official'] ) : $permalink_settings['official'];
			$permalink_settings['staff']      = isset( $_POST['sl_staff_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_staff_base_slug'], $permalink_settings['staff'] ) : $permalink_settings['staff'];
			$permalink_settings['venue']      = isset( $_POST['sl_venue_base_slug'] ) ? $this->sanitize_permalink( $_POST['sl_venue_base_slug'], $permalink_settings['venue'] ) : $permalink_settings['venue'];

			update_option( 'sl_permalink_structure', wp_json_encode( $permalink_settings ) );
			flush_rewrite_rules();
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Sanitize permalink.
	 *
	 * @param string $value   -
	 * @param string $default -
	 *
	 * @return string
	 * @since  0.5.11
	 */
	private function sanitize_permalink( $value, $default ) {
		global $wpdb;

		$value = $wpdb->strip_invalid_text_for_column( $wpdb->options, 'option_value', $value );

		if ( is_wp_error( $value ) ) {
			return $default;
		} else {
			$value = esc_url_raw( $value );
			$value = str_replace( 'http://', '', $value );
		}

		return untrailingslashit( $value );
	}

	/**
	 * Method returns permalink structure.
	 *
	 * @since 0.5.11
	 */
	public function get_permalink_structure() {

		$permalink_settings = (array) json_decode( get_option( 'sl_permalink_structure', '' ), true );

		$permalinks = wp_parse_args(
			$permalink_settings,
			[
				'game'       => 'game',
				'tournament' => 'tournament',
				'team'       => 'team',
				'player'     => 'player',
				'official'   => 'official',
				'staff'      => 'staff',
				'venue'      => 'venue',
			]
		);

		$permalinks = array_map( 'wp_unslash', $permalinks );

		/**
		 * Filter permalink structure.
		 *
		 * @since 0.5.11
		 */
		$permalinks = apply_filters( 'sports-leagues/config/permalinks', $permalinks );

		return $permalinks;
	}

	public function cmb2_before_metabox() {

		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap cmb2-postbox">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-settings_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-gear"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-display-settings_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-eye"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Display', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-settings_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-service-settings_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-tools"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Service Links', 'sports-leagues' ); ?></span>
					</div>
				</div>
				<div class="anwp-metabox-tabs__content p-4 flex-grow-1">
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	public function cmb2_after_metabox() {

		// @formatter:off
		ob_start();
		?>
				</div><!-- end of div.anwp-metabox-tabs__controls -->
			</div><!-- end of div.anwp-b-wrap -->
		</div><!-- end of div.anwp-metabox-tabs__content -->
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  0.1.0
	 */
	public function add_setting_page_main_metabox() {

		// Add our CMB2 metabox.
		$cmb = new_cmb2_box(
			[
				'id'           => self::$metabox_id,
				'title'        => $this->title,
				'object_types' => [ 'options-page' ],
				'classes'      => 'anwp-b-wrap anwp-settings',
				'option_key'   => self::$key,
				'menu_title'   => esc_html_x( 'Sports Settings', 'admin menu title', 'sports-leagues' ),
				'icon_url'     => 'dashicons-admin-tools',
				'position'     => 43,
				'capability'   => 'manage_options',
			]
		);

		$cmb->add_field(
			[
				'name'             => esc_html__( 'Active Season', 'sports-leagues' ),
				'id'               => 'active_season',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this->plugin->season, 'get_season_options' ],
				'before_row'       => '<div id="anwp-tabs-general-settings_metabox" class="anwp-metabox-tabs__content-item">' . $this->render_docs_link( 'general' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Hide not used seasons', 'sports-leagues' ),
				'desc'    => esc_html__( 'Hide not used seasons in the Seasons Dropdown', 'sports-leagues' ),
				'id'      => 'hide_not_used_seasons',
				'type'    => 'anwp_sl_simple_trigger',
				'default' => '',
				'options' => [
					''    => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'sports-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'sports-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Team listed first (Team A)', 'sports-leagues' ),
				'id'      => 'team_listed_first',
				'type'    => 'select',
				'default' => 'home',
				'options' => [
					'home' => esc_html__( 'Home', 'sports-leagues' ),
					'away' => esc_html__( 'Away', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'  => esc_html__( 'Game title generation rules', 'sports-leagues' ),
				'id'    => 'game_title_generator',
				'type'  => 'text',
				'after' => '<p class="cmb2-metabox-description">' . __( 'Available placeholders', 'sports-leagues' ) . ':<br> %team_a% %team_b% %scores_a% %scores_b% %tournament% %kickoff%' . '<br>' . __( 'E.g.: %team_a% - %team_b% %scores_a%:%scores_b% - %kickoff%', 'sports-leagues' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Video Player', 'sports-leagues' ),
				'id'      => 'preferred_video_player',
				'type'    => 'select',
				'default' => 'plyr',
				'options' => [
					'youtube' => esc_html__( 'Use YouTube player only (Vimeo and custom videos will be ignored)', 'sports-leagues' ),
					'mixed'   => esc_html__( 'Use YouTube player for own video and Plyr player for Vimeo and custom videos', 'sports-leagues' ),
					'plyr'    => esc_html__( 'Use Plyr player for all video types (YouTube, Vimeo, custom)', 'sports-leagues' ),
				],
			]
		);

		// Google Maps API
		$cmb->add_field(
			[
				'name'      => esc_html__( 'Google Maps API Key', 'sports-leagues' ),
				'desc'      => sprintf( ' %s <a href="%s" target="_blank">get Google Maps API Key</a>', esc_html__( 'Google Map is used to locate venues. You can get key here - ', 'sports-leagues' ), esc_url( 'https://developers.google.com/maps/documentation/javascript/get-api-key#--api' ) ),
				'id'        => 'google_maps_api',
				'type'      => 'text',
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Display Section
		|--------------------------------------------------------------------------
		*/
		$html_display_options_customizer  = '<div class="my-2 alert alert-info border-info border d-flex flex-wrap align-items-center">';
		$html_display_options_customizer .= esc_html__( 'Most of the plugin display settings are available in Customizer.', 'sports-leagues' );
		$html_display_options_customizer .= '<a class="button button-secondary ml-auto" target="_blank" href="' . esc_url( admin_url( 'customize.php?autofocus[panel]=anwp_sl_panel' ) ) . '">' . esc_html__( 'Open Customizer', 'sports-leagues' ) . '</a>';
		$html_display_options_customizer .= '</div>';

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Custom Game Date format', 'sports-leagues' ),
				'id'         => 'custom_game_date_format',
				'type'       => 'text_small',
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => '<a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time">' . esc_html__( 'Documentation', 'sports-leagues' ) . '</a>',
				'before_row' => '<div id="anwp-tabs-display-settings_metabox" class="anwp-metabox-tabs__content-item d-none">' . $html_display_options_customizer,
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Custom Game Time format', 'sports-leagues' ),
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => '<a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time">' . esc_html__( 'Documentation', 'sports-leagues' ) . '</a>',
				'id'         => 'custom_game_time_format',
				'type'       => 'text_small',
				'after_row'  => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| ## Custom fields ##
		|--------------------------------------------------------------------------
		*/

		$html_custom_fields_top  = '<div id="anwp-tabs-custom_fields-settings_metabox" class="anwp-metabox-tabs__content-item d-none">';
		$html_custom_fields_top .= $this->render_docs_link( 'custom_fields' );
		$html_custom_fields_top .= '<h3>' . esc_html__( 'Set Custom Fields for', 'sports-leagues' ) . '</h3>';

		// Player Custom Fields
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Player', 'sports-leagues' ),
				'id'         => 'player_custom_fields',
				'type'       => 'text',
				'repeatable' => true,
				'before_row' => $html_custom_fields_top,
				'text'       => [
					'add_row_text' => esc_html__( 'Add field', 'sports-leagues' ),
				],
			]
		);

		// Team Custom Fields
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Team', 'sports-leagues' ),
				'id'         => 'team_custom_fields',
				'type'       => 'text',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add field', 'sports-leagues' ),
				],
			]
		);

		// Venue Custom Fields
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Venue', 'sports-leagues' ),
				'id'         => 'venue_custom_fields',
				'type'       => 'text',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add field', 'sports-leagues' ),
				],
			]
		);

		// Staff Custom Fields
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Staff', 'sports-leagues' ),
				'id'         => 'staff_custom_fields',
				'type'       => 'text',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add field', 'sports-leagues' ),
				],
			]
		);

		// Official Custom Fields
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Official', 'sports-leagues' ),
				'id'         => 'official_custom_fields',
				'type'       => 'text',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add field', 'sports-leagues' ),
				],
				'after_row'  => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| ## Service Links ##
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Service Links', 'sports-leagues' ),
				'type'       => 'title',
				'before_row' => '<div id="anwp-tabs-service-settings_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'id'         => 'section_title_service_links',
				'after_row'  => [ $this, 'service_links_html' ],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Load Gutenberg Blocks', 'sports-leagues' ),
				'id'      => 'gutenberg_blocks',
				'type'    => 'anwp_sl_simple_trigger',
				'default' => 'yes',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'sports-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'sports-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Plugin Cache System', 'sports-leagues' ),
				'id'        => 'cache_active',
				'type'      => 'anwp_sl_simple_trigger',
				'default'   => 'yes',
				'options'   => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'Disabled', 'sports-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Active', 'sports-leagues' ),
					],
				],
				'after_row' => '</div>',
			]
		);
	}

	/**
	 * Render service links.
	 *
	 * @since 0.5.3
	 * @return string
	 */
	public function service_links_html() {

		global $wpdb;

		try {
			$games = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_game',
					'post_status' => 'publish',
					'fields'      => 'ids',
				]
			);

			$games_qty = is_array( $games ) ? count( $games ) : 0;

			$stats_qty = $wpdb->get_var(
				"
				SELECT COUNT(*)
				FROM {$wpdb->prefix}sl_games
				"
			);
		} catch ( RuntimeException $e ) {
			$games_qty = 0;
			$stats_qty = 0;
		}

		ob_start();
		?>
		<div class="alert alert-warning p-2 my-2"><?php echo esc_html__( 'Only use it if you know what you\'re doing.', 'sports-leagues' ); ?></div>
		<div class="cmb-row cmb-type-text table-layout">
			<div class="cmb-th">
				<label for=""><?php echo esc_html__( 'Recalculate Sports Leagues DB index tables', 'sports-leagues' ); ?></label>
				<span class="d-block text-muted small">(games/stats - <?php echo absint( $games_qty ); ?>/<?php echo intval( $stats_qty ); ?>)</span>
			</div>

			<div class="cmb-td d-flex align-items-center">

				<?php if ( absint( $games_qty ) === absint( $stats_qty ) ) : ?>
					<div class="w-100"></div>
					<span class="d-block text-info anwp-text-xs mt-1">statistics look good, no need to recalculate</span>
				<?php else : ?>
					<select name="" id="">
						<option value="">all (with DB table truncate)</option>
						<option value="-20">+ 20</option>
						<option value="-50">+ 50</option>
					</select>
					<button class="button button mt-1 mx-2" data-sl-recalculate-index-tables>start</button>
					<span class="spinner mx-0"></span>
				<?php endif; ?>

			</div>
		</div>
		<div class="cmb-row cmb-type-text table-layout">
			<div class="cmb-th">
				<label for=""><?php echo esc_html__( 'Flush plugin cache (transients)', 'sports-leagues' ); ?></label>
			</div>

			<div class="cmb-td d-flex align-items-center">
				<button class="button button mt-1 mx-2" data-sl-flush-plugin-cache>flush</button>
				<span class="spinner mx-0"></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders documentation link.
	 *
	 * @param $section
	 *
	 * @return string
	 * @since 0.5.8
	 */
	private function render_docs_link( $section ) {

		$section_link  = '';
		$section_title = '';

		switch ( $section ) {
			case 'display':
				$section_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/95-display-tab-settings';
				$section_title = esc_html__( 'Settings', 'sports-leagues' ) . ' :: ' . esc_html__( 'Display Tab', 'sports-leagues' );
				break;

			case 'general':
				$section_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/420-setting-general';
				$section_title = esc_html__( 'Settings', 'sports-leagues' ) . ' :: ' . esc_html__( 'General', 'sports-leagues' );
				break;

			case 'custom_fields':
				$section_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/180-custom-fields';
				$section_title = esc_html__( 'Settings', 'sports-leagues' ) . ' :: ' . esc_html__( 'Custom Fields', 'sports-leagues' );
				break;
		}

		$output = '<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info">';

		$output .= '<svg class="anwp-icon"><use xlink:href="#icon-book"></use></svg>';
		$output .= '<b class="mx-2">' . esc_html__( 'Documentation', 'sports-leagues' ) . ':</b> ';
		$output .= '<a target="_blank" href="' . esc_url( $section_link ) . '">' . esc_html( $section_title ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  0.1.0
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
}
