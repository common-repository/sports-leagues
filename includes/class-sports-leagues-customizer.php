<?php
/**
 * Sports Leagues :: Customizer.
 *
 * @package Sports_Leagues
 */

class Sports_Leagues_Customizer {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		$this->hooks();

		require_once Sports_Leagues::dir( 'includes/customizer/custom-controls.php' );
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'customize_register', [ $this, 'register_customizer_settings' ] );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( defined( 'SOCSS_VERSION' ) && ! is_admin() && isset( $_GET['so_css_preview'] ) && 'no' !== get_option( 'anwp_sl_customizer_mode' ) ) {
			add_filter( 'wp_enqueue_scripts', [ $this, 'enqueue_so_css_inspector_scripts' ], 20, 1 );
		}

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.11.0
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/v1',
			'/customize/toggle-mode',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'customize_toggle_mode' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Callback for the rest route "/helper/recalculate-matches-stats/"
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since 0.11.0
	 * @return mixed
	 */
	public function customize_toggle_mode( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Access Denied !!!' );
		}

		// Get Request params
		$params = $request->get_params();

		if ( ! isset( $params['mode_active'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect Data', [ 'status' => 400 ] );
		}

		if ( 'yes' === $params['mode_active'] ) {
			update_option( 'anwp_sl_customizer_mode', 'no' );
		} else {
			delete_option( 'anwp_sl_customizer_mode' );
		}

		return rest_ensure_response( [] );
	}

	/**
	 * @return void
	 */
	public function enqueue_so_css_inspector_scripts() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		wp_deregister_script( 'siteorigin-css-inspector' );

		wp_enqueue_script(
			'siteorigin-css-inspector',
			Sports_Leagues::url( 'includes/customizer/so-css-inspector-mod.min.js' ),
			[
				'jquery',
				'underscore',
				'backbone',
			],
			'1.5.1',
			true
		);

		wp_localize_script( 'siteorigin-css-inspector', 'socssOptions', array() );

		$plugin_customizer_classes = [
			'game-header',
			'game-header__top',
			'game-header__kickoff',
			'game-header__team-wrapper',
			'game-header__team-logo',
			'game-header__team',
			'game-header__team-link',
			'game-header__scores-number',
			'game-header__events',
			'game-header__finished-label',
			'game-header__footer',
			'player-header',
			'player-header__logo-wrapper',
			'player-header__logo',
			'player-header__options',
			'player-header__option-title',
			'player-header__option-value',
			'player-description',
			'player-gallery',
			'player-gallery__notes',
			'staff-header',
			'staff-header__logo-wrapper',
			'staff-header__logo',
			'staff-header__options',
			'staff-header__option-title',
			'staff-header__option-value',
			'team-header',
			'team-header__logo-wrapper',
			'team-header__logo',
			'team-header__options',
			'team-header__option-title',
			'team-header__option-value',
			'team-description',
			'anwp-sl-nodata',
			'anwp-block-header',
			'anwp-block-subheader',
			'team-gallery',
			'team-gallery__notes',
			'team__team-players-stats',
			'anwp-sl-season-selector',
			'anwp-season-dropdown',
			'team__roster',
			'team-roster',
			'team-roster__th-wrapper',
			'team-roster__th-number',
			'team-roster__th',
			'team-roster__number',
			'team-roster__photo',
			'team-roster__player',
			'team-roster__role',
			'team-roster__age-label',
			'team-roster__age',
			'team-roster__age-text',
			'team-roster__nationality',
			'team-roster-grid',
			'team-roster-grid__header',
			'team-roster-grid__block',
			'team-roster-grid__photo-wrapper',
			'team-roster-grid__photo',
			'team-roster-grid__player-number',
			'team-roster-grid__player-content',
			'team-roster-grid__status-badge',
			'team-roster-grid__name',
			'team-roster-grid__player-param',
			'team-roster-grid__player-param-title',
			'team-roster-grid__player-param-value',
			'team-roster-grid__role',
			'team-roster-grid__age',
			'team-roster-grid__nationality',
			'anwp-stats-table',
			'game-widget',
			'game-widget__venue',
			'game-widget__tournament',
			'game-widget__teams',
			'game-widget__team',
			'game-widget__team-title',
			'game-widget__team-logo',
			'anwp-game-preview-link',
			'anwp-sl-game-countdown__inner',
			'anwp-sl-game-countdown',
			'anwp-sl-game-countdown__item',
			'anwp-sl-game-countdown__label',
			'anwp-sl-game-countdown__value',
			'anwp-sl-game-countdown__separator',
		];

		$plugin_customizer_classes = apply_filters( 'sports-leagues/customizer/plugin-classes', $plugin_customizer_classes );

		wp_localize_script( 'siteorigin-css-inspector', '_AnWP_CSS_Classes', $plugin_customizer_classes );
		wp_localize_script(
			'siteorigin-css-inspector',
			'_AnWP_SOCSS_Mode',
			[
				'admin_url' => esc_url_raw( admin_url( 'admin.php?page=anwpsl-plugin-customize' ) ),
				'active'    => true,
			]
		);
	}

	/**
	 * Register Customizer settings
	 */
	public function register_customizer_settings( $wp_customize ) {

		$wp_customize->add_panel(
			'anwp_sl_panel',
			[
				'title'       => __( 'Sports Leagues', 'sports-leagues' ),
				'description' => '', // Include html tags such as <p>.
				'priority'    => 160, // Mixed with top-level-section hierarchy.
			]
		);

		//=================================
		//-- COLORS --
		//=================================
		$wp_customize->add_section(
			'sl_colors',
			[
				'title' => __( 'Colors', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-light
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[colors][bg-light]',
			[
				'default' => '#f8f9fa',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-sl-customizer[colors][bg-light]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'sports-leagues' ) . ': #f8f9fa',
					'label'              => __( 'Background Color', 'sports-leagues' ) . ' - Light',
					'section'            => 'sl_colors',
					'settings'           => 'anwp-sl-customizer[colors][bg-light]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-secondary
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[colors][bg-secondary]',
			[
				'default' => '#6c757d',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-sl-customizer[colors][bg-secondary]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'sports-leagues' ) . ': #6c757d',
					'label'              => __( 'Background Color', 'sports-leagues' ) . ' - Secondary',
					'section'            => 'sl_colors',
					'settings'           => 'anwp-sl-customizer[colors][bg-secondary]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-border-light
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[colors][border-light]',
			[
				'default' => '#ced4da',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-sl-customizer[colors][border-light]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'sports-leagues' ) . ': #ced4da',
					'label'              => __( 'Border Color', 'sports-leagues' ) . ' - Light',
					'section'            => 'sl_colors',
					'settings'           => 'anwp-sl-customizer[colors][border-light]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-sl-hover
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[colors][hover-bg]',
			[
				'default' => '#f0f6f1',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-sl-customizer[colors][hover-bg]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'sports-leagues' ) . ': #f0f6f1',
					'label'              => __( 'Hover Background', 'sports-leagues' ),
					'section'            => 'sl_colors',
					'settings'           => 'anwp-sl-customizer[colors][hover-bg]',
				]
			)
		);

		//=================================
		//-- General --
		//=================================
		$wp_customize->add_section(
			'sl_general',
			[
				'title' => __( 'General', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| load_alternative_page_layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[general][load_alternative_page_layout]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[general][load_alternative_page_layout]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Load alternative page layout', 'sports-leagues' ),
				'section'  => 'sl_general',
				'settings' => 'anwp-sl-customizer[general][load_alternative_page_layout]',
				'choices'  => [
					''  => __( 'No', 'sports-leagues' ),
					'a' => __( 'Layout', 'sports-leagues' ) . ' A',
					'b' => __( 'Layout', 'sports-leagues' ) . ' B',
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| hide_post_titles
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[general][hide_post_titles]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[general][hide_post_titles]',
			[
				'type'        => 'select',
				'label'       => esc_html__( 'Hide post title for Game and Tournament', 'sports-leagues' ),
				'section'     => 'sl_general',
				'description' => __( "it depends on your theme and sometimes wouldn't work", 'sports-leagues' ),
				'settings'    => 'anwp-sl-customizer[general][hide_post_titles]',
				'choices'     => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| stats_text_monospace
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[general][stats_text_monospace]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[general][stats_text_monospace]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Use monospace font in statistical tables', 'sports-leagues' ),
				'section'  => 'sl_general',
				'settings' => 'anwp-sl-customizer[general][stats_text_monospace]',
				'choices'  => [
					''    => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		//=================================
		//-- Layouts --
		//=================================
		$wp_customize->add_section(
			'sl_layouts',
			[
				'title' => __( 'Layouts', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| game_players_stats layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[layout][game_players_stats]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[layout][game_players_stats]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Game >> Players Statistics', 'sports-leagues' ),
				'section'  => 'sl_layouts',
				'settings' => 'anwp-sl-customizer[layout][game_players_stats]',
				'choices'  => apply_filters(
					'sports-leagues/configurator/game_players_stats_layout_options',
					[
						''   => __( 'Recommended', 'sports-leagues' ),
						'v0' => 'V0 - ' . __( 'Default', 'sports-leagues' ),
						'v1' => 'V1 - ' . __( '(deprecated)', 'sports-leagues' ),
						'v2' => 'V2 - ' . __( 'for Roster Groups', 'sports-leagues' ),
					]
				),
			]
		);

		/*
		|--------------------------------------------------------------------
		| team_players_stats layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[layout][team_players_stats]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[layout][team_players_stats]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team >> Players Statistics', 'sports-leagues' ),
				'section'  => 'sl_layouts',
				'settings' => 'anwp-sl-customizer[layout][team_players_stats]',
				'choices'  => apply_filters(
					'sports-leagues/configurator/team_players_stats_layout_options',
					[
						''   => __( 'Recommended', 'sports-leagues' ),
						'v0' => 'V0 - ' . __( 'Default', 'sports-leagues' ),
						'v1' => 'V1 - ' . __( '(deprecated)', 'sports-leagues' ),
						'v2' => 'V2 - ' . __( 'for Roster Groups', 'sports-leagues' ),
					]
				),
			]
		);

		//=================================
		//-- Team --
		//=================================
		$wp_customize->add_section(
			'sl_team',
			[
				'title' => __( 'Team', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		//=================================
		//-- Roster --
		//=================================
		$wp_customize->add_section(
			'sl_roster',
			[
				'title' => esc_html__( 'Team Roster', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| team_roster_layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[roster][team_roster_layout]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[roster][team_roster_layout]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team Roster layout', 'sports-leagues' ),
				'section'  => 'sl_roster',
				'settings' => 'anwp-sl-customizer[roster][team_roster_layout]',
				'choices'  => apply_filters(
					'sports-leagues/configurator/team_roster_layout_options',
					[
						''     => esc_html__( 'Table (default)', 'sports-leagues' ),
						'grid' => esc_html__( 'Grid', 'sports-leagues' ),
					]
				),
			]
		);

		//=================================
		//-- Venue --
		//=================================
		$wp_customize->add_section(
			'sl_venue',
			[
				'title' => esc_html__( 'Venue', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_required
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[venue][map_consent_required]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[venue][map_consent_required]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Require consent before loading Map', 'sports-leagues' ),
				'section'  => 'sl_venue',
				'settings' => 'anwp-sl-customizer[venue][map_consent_required]',
				'choices'  => [
					''    => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_text
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[venue][map_consent_text]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[venue][map_consent_text]',
			[
				'type'     => 'text',
				'label'    => esc_html__( 'Map consent - Text', 'sports-leagues' ),
				'section'  => 'sl_venue',
				'settings' => 'anwp-sl-customizer[venue][map_consent_text]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_btn_text
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[venue][map_consent_btn_text]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[venue][map_consent_btn_text]',
			[
				'type'     => 'text',
				'label'    => esc_html__( 'Map consent - Button Text', 'sports-leagues' ),
				'section'  => 'sl_venue',
				'settings' => 'anwp-sl-customizer[venue][map_consent_btn_text]',
			]
		);

		//=================================
		//-- Standing --
		//=================================
		$wp_customize->add_section(
			'sl_standing',
			[
				'title' => esc_html__( 'Standing', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_team_series
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[standing][show_team_series]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[standing][show_team_series]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show team series', 'sports-leagues' ),
				'section'  => 'sl_standing',
				'settings' => 'anwp-sl-customizer[standing][show_team_series]',
				'choices'  => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_standing_full_screen_link
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[standing][show_standing_full_screen_link]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[standing][show_standing_full_screen_link]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show "full screen" link', 'sports-leagues' ),
				'section'  => 'sl_standing',
				'settings' => 'anwp-sl-customizer[standing][show_standing_full_screen_link]',
				'choices'  => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		//=================================
		//-- Game --
		//=================================
		$wp_customize->add_section(
			'sl_game',
			[
				'title' => esc_html__( 'Game', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| colorize_team_header
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game][colorize_team_header]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game][colorize_team_header]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Colorize Team Header block', 'sports-leagues' ),
				'section'  => 'sl_game',
				'settings' => 'anwp-sl-customizer[game][colorize_team_header]',
				'choices'  => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_player_position
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game][show_player_position]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game][show_player_position]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show player position', 'sports-leagues' ),
				'section'  => 'sl_game',
				'settings' => 'anwp-sl-customizer[game][show_player_position]',
				'choices'  => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_staff_job
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game][show_staff_job]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game][show_staff_job]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show staff job', 'sports-leagues' ),
				'section'  => 'sl_game',
				'settings' => 'anwp-sl-customizer[game][show_staff_job]',
				'choices'  => [
					'no'  => __( 'No', 'sports-leagues' ),
					'yes' => __( 'Yes', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| team_series_game_header
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game][team_series_game_header]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game][team_series_game_header]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team Series (Form) in game header', 'sports-leagues' ),
				'section'  => 'sl_game',
				'settings' => 'anwp-sl-customizer[game][team_series_game_header]',
				'choices'  => [
					'hide' => __( 'Hide', 'sports-leagues' ),
					'show' => __( 'Show', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| game_period_scores
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game][game_period_scores]',
			[
				'default' => 'table',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game][game_period_scores]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Period Scores', 'sports-leagues' ),
				'section'  => 'sl_game',
				'settings' => 'anwp-sl-customizer[game][game_period_scores]',
				'choices'  => [
					'hide'  => esc_html__( 'hide', 'sports-leagues' ),
					'line'  => esc_html__( 'show in line', 'sports-leagues' ),
					'table' => esc_html__( 'show in table', 'sports-leagues' ),
				],
			]
		);

		//=================================
		//-- Game List --
		//=================================
		$wp_customize->add_section(
			'sl_game_list',
			[
				'title' => esc_html__( 'Game List', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| team_name_slim
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[game_list][team_name_slim]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[game_list][team_name_slim]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team Name', 'sports-leagues' ),
				'section'  => 'sl_game_list',
				'settings' => 'anwp-sl-customizer[game_list][team_name_slim]',
				'choices'  => [
					''      => esc_html__( 'abbreviation', 'sports-leagues' ),
					'title' => esc_html__( 'full title', 'sports-leagues' ),
				],
			]
		);

		//=================================
		//-- Tournament --
		//=================================
		$wp_customize->add_section(
			'sl_tournament',
			[
				'title' => esc_html__( 'Tournament', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_selector
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[tournament][show_selector]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[tournament][show_selector]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show Season Selector', 'sports-leagues' ),
				'section'  => 'sl_tournament',
				'settings' => 'anwp-sl-customizer[tournament][show_selector]',
				'choices'  => [
					''     => esc_html__( 'show', 'sports-leagues' ),
					'hide' => esc_html__( 'hide', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| stage_standings_only
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[tournament][stage_standings_only]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[tournament][stage_standings_only]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show Stage Standings Only', 'sports-leagues' ),
				'section'  => 'sl_tournament',
				'settings' => 'anwp-sl-customizer[tournament][stage_standings_only]',
				'choices'  => [
					'yes' => esc_html__( 'yes', 'sports-leagues' ),
					''    => esc_html__( 'no', 'sports-leagues' ),
				],
			]
		);

		//=================================
		//-- Player & Staff --
		//=================================
		$wp_customize->add_section(
			'sl_player',
			[
				'title' => esc_html__( 'Player & Staff', 'sports-leagues' ),
				'panel' => 'anwp_sl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| default_player_photo
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[player][default_player_photo]',
			[
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'anwp-sl-customizer[player][default_player_photo]',
				[
					'label'   => esc_html__( 'Default Player Photo', 'sports-leagues' ),
					'section' => 'sl_player',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| link_to_the_game_player_stats
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[player][link_to_the_game_player_stats]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[player][link_to_the_game_player_stats]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Link to the game in Player Stats', 'sports-leagues' ),
				'section'  => 'sl_player',
				'settings' => 'anwp-sl-customizer[player][link_to_the_game_player_stats]',
				'choices'  => [
					'yes' => __( 'Yes', 'sports-leagues' ),
					'no'  => __( 'No', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| date_of_birth_output
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-sl-customizer[player][date_of_birth_output]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-sl-customizer[player][date_of_birth_output]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Player Date Of Birth', 'sports-leagues' ),
				'section'  => 'sl_player',
				'settings' => 'anwp-sl-customizer[player][date_of_birth_output]',
				'choices'  => [
					''     => __( 'Full Date Of Birth', 'sports-leagues' ),
					'year' => __( 'Only Year', 'sports-leagues' ),
					'hide' => __( 'Hide', 'sports-leagues' ),
				],
			]
		);
	}

	/**
	 * Get Customizer CSS
	 *
	 * @return string
	 * @since 0.11.0
	 */
	public function get_customizer_css() {

		$output_css = '';

		$plugin_options = get_option( 'anwp-sl-customizer' );

		if ( empty( $plugin_options ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Load colors
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $plugin_options['colors']['border-light'] ) && '#ced4da' !== $plugin_options['colors']['border-light'] ) {
			$output_css .= sprintf( '.anwp-border-light, .anwp-block-header {border-color: %s !important;}', esc_attr( $plugin_options['colors']['border-light'] ) );
			$output_css .= sprintf( '.anwp-b-wrap .table-bordered, .anwp-b-wrap .table-bordered th, .anwp-b-wrap .table-bordered td {border-color: %s !important;}', esc_attr( $plugin_options['colors']['border-light'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-light'] ) && '#f8f9fa' !== $plugin_options['colors']['bg-light'] ) {
			$output_css .= sprintf( '.anwp-bg-light {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-light'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-secondary'] ) && '#6c757d' !== $plugin_options['colors']['bg-secondary'] ) {
			$output_css .= sprintf( '.anwp-bg-secondary {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-secondary'] ) );
		}

		if ( ! empty( $plugin_options['colors']['hover-bg'] ) && '#f0f6f1' !== $plugin_options['colors']['hover-bg'] ) {
			$output_css .= sprintf( '.anwp-sl-hover:hover, .anwp-sl-hover:hover .anwp-bg-light {background-color: %s !important;}', esc_attr( $plugin_options['colors']['hover-bg'] ) );
		}

		return $output_css;
	}

	/**
	 * Get Customizer saved option
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function get_value( $section, $key, $default = '' ) {

		static $customizer_options = null;

		if ( null === $customizer_options ) {
			$customizer_options = get_option( 'anwp-sl-customizer' );
		}

		if ( empty( $customizer_options ) || empty( $section ) || empty( $key ) ) {
			return $default;
		}

		if ( ']' === mb_substr( $key, -1 ) ) {
			$array_parsed = explode( '[', trim( $key, ']' ) );

			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ] ) || empty( $array_parsed[1] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ] ) ) {
				return $default;
			}

			return $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ];

		} else {
			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $key ] ) ) {
				return $default;
			}
		}

		return $customizer_options[ $section ][ $key ];
	}
}
