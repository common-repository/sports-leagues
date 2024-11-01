<?php
/**
 * Sports Leagues :: Game.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Game {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 *
	 * @since  0.1.0
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type( $plugin );

		// Run hooks
		$this->hooks();
	}

	/**
	 * Register Custom Post Type
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type( $plugin ) {

		$permalink_structure = $plugin->options->get_permalink_structure();
		$permalink_slug      = empty( $permalink_structure['game'] ) ? 'game' : $permalink_structure['game'];

		// Register this CPT.
		$labels = [
			'name'               => _x( 'Game', 'Post type general name', 'sports-leagues' ),
			'singular_name'      => _x( 'Game', 'Post type singular name', 'sports-leagues' ),
			'menu_name'          => _x( 'Game', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'     => _x( 'Game', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'            => __( 'Add New', 'sports-leagues' ),
			'add_new_item'       => __( 'Add New Game', 'sports-leagues' ),
			'new_item'           => __( 'New Game', 'sports-leagues' ),
			'edit_item'          => __( 'Edit Game', 'sports-leagues' ),
			'view_item'          => __( 'View Game', 'sports-leagues' ),
			'all_items'          => __( 'Games', 'sports-leagues' ),
			'search_items'       => __( 'Search Games', 'sports-leagues' ),
			'not_found'          => __( 'No Games found.', 'sports-leagues' ),
			'not_found_in_trash' => __( 'No Games found in Trash.', 'sports-leagues' ),
		];

		$args = [
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $labels,
			'menu_position'      => 46,
			'menu_icon'          => Sports_Leagues::SVG_VS,
			'public'             => true,
			'publicly_queryable' => true,
			'rewrite'            => [ 'slug' => $permalink_slug ],
			'show_in_admin_bar'  => true,
			'show_in_menu'       => true,
			'show_ui'            => true,
			'supports'           => [ 'thumbnail', 'comments' ],
		];

		if ( apply_filters( 'sports-leagues/config/cpt_only_admin_access', true ) ) {
			$args['capabilities'] = [
				'edit_post'         => 'manage_options',
				'read_post'         => 'manage_options',
				'delete_post'       => 'manage_options',
				'edit_posts'        => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts'      => 'manage_options',
				'publish_posts'     => 'manage_options',
			];
		}

		register_post_type( 'sl_game', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );
		add_action( 'save_post_sl_game', [ $this, 'save_metabox' ], 10, 2 );

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_game_metaboxes' ] );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_game_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_game_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_game_sortable_columns', [ $this, 'sortable_columns' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Recalculate stats on game untrashed
		add_action( 'untrashed_post', [ $this, 'on_game_untrashed' ] );
		add_action( 'admin_notices', [ $this, 'on_game_untrashed_notices' ] );

		// Remove stats on post delete
		add_action( 'trashed_post', [ $this, 'on_game_trashed' ] );
		add_action( 'delete_post', [ $this, 'on_game_deleted' ] );

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		// Modify quick actions & handle action request
		add_filter( 'post_row_actions', [ $this, 'modify_quick_actions' ], 10, 2 );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-game/after_wrapper',
			function ( $game ) {

				$content_below = get_post_meta( $game->ID, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Get Game Card
		add_action( 'wp_ajax_anwp_sl_get_game_card', [ $this, 'get_game_card' ] );
		add_action( 'wp_ajax_nopriv_anwp_sl_get_game_card', [ $this, 'get_game_card' ] );

		add_action( 'wp_ajax_nopriv_anwp_sl_load_more_games', [ $this, 'load_more_games' ] );
		add_action( 'wp_ajax_anwp_sl_load_more_games', [ $this, 'load_more_games' ] );
	}

	/**
	 * Get array of games for widgets and shortcodes.
	 *
	 * @param object|array $options
	 *
	 * @param string       $result
	 *
	 * @return array|null|object
	 * @since 0.5.5
	 */
	public function get_games_extended( $options, $result = '' ) {

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'tournament_id'      => '',
				'stage_id'           => '',
				'season_id'          => '',
				'league_id'          => '',
				'group_id'           => '',
				'round_id'           => '',
				'venue_id'           => '',
				'date_from'          => '',
				'date_to'            => '',
				'finished'           => '',
				'filter_by_team'     => '',
				'h2h'                => '',
				'filter_by_game_day' => '',
				'days_offset'        => '',
				'days_offset_to'     => '',
				'priority'           => '',
				'sort_by_date'       => '',
				'sort_by_game_day'   => '',
				'limit'              => '',
				'kickoff_before'     => '',
				'exclude_ids'        => '',
				'include_ids'        => '',
				'team_a'             => '',
				'team_b'             => '',
				'offset'             => '',
			]
		);

		$query = "
		SELECT *
		FROM {$wpdb->prefix}sl_games
		WHERE 1=1
		";

		/*
		|--------------------------------------------------------------------
		| WHERE filter by tournament_id
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->tournament_id ) ) {
			$query .= $wpdb->prepare( ' AND tournament_id = %d ', $options->tournament_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by stage_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->stage_id ) {
			$query .= $wpdb->prepare( ' AND stage_id = %d ', $options->stage_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by season_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->season_id ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $options->season_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by league_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->league_id ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', $options->league_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by group_id
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->group_id ) ) {
			$query .= $wpdb->prepare( ' AND group_id = %d ', $options->group_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by round_id
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->round_id ) ) {
			$query .= $wpdb->prepare( ' AND round_id = %d ', $options->round_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by venue_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->venue_id ) {
			$query .= $wpdb->prepare( ' AND venue_id = %d ', $options->venue_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE exclude ids
		|--------------------------------------------------------------------
		*/
		if ( trim( $options->exclude_ids ) ) {
			$exclude_ids = wp_parse_id_list( $options->exclude_ids );

			if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) && count( $exclude_ids ) ) {

				// Prepare exclude format and placeholders
				$exclude_placeholders = array_fill( 0, count( $exclude_ids ), '%s' );
				$exclude_format       = implode( ', ', $exclude_placeholders );

				$query .= $wpdb->prepare( " AND game_id NOT IN ({$exclude_format})", $exclude_ids ); // phpcs:ignore
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE include ids
		|--------------------------------------------------------------------
		*/
		if ( trim( $options->include_ids ) ) {
			$include_ids = wp_parse_id_list( $options->include_ids );

			if ( ! empty( $include_ids ) && is_array( $include_ids ) && count( $include_ids ) ) {

				// Prepare include format and placeholders
				$include_placeholders = array_fill( 0, count( $include_ids ), '%s' );
				$include_format       = implode( ', ', $include_placeholders );

				$query .= $wpdb->prepare( " AND game_id IN ({$include_format})", $include_ids ); // phpcs:ignore
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by date_to
		|--------------------------------------------------------------------
		*/
		if ( trim( $options->date_to ) ) {
			$date_to = explode( ' ', $options->date_to )[0];

			if ( sports_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND kickoff <= %s ', $date_to . ' 23:59:59' );
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by kickoff_before
		|--------------------------------------------------------------------
		*/
		if ( trim( $options->kickoff_before ) ) {

			if ( sports_leagues()->helper->validate_date( $options->kickoff_before ) ) {
				$query .= $wpdb->prepare( ' AND kickoff < %s ', $options->kickoff_before );
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by date_from
		|--------------------------------------------------------------------
		*/
		if ( trim( $options->date_from ) ) {
			$date_from = explode( ' ', $options->date_from )[0];

			if ( sports_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND kickoff >= %s ', $date_from . ' 00:00:00' );
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by finished
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->finished ) {
			$query .= $wpdb->prepare( ' AND finished = %d ', $options->finished );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by team
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->filter_by_team ) ) {

			$teams  = wp_parse_id_list( $options->filter_by_team );
			$format = implode( ', ', array_fill( 0, count( $teams ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( home_team IN ({$format}) OR away_team IN ({$format}) ) ", array_merge( $teams, $teams ) ); // phpcs:ignore
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by team a/b
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $options->team_a ) ) {
			$query .= $wpdb->prepare( ' AND home_team = %d ', $options->team_a );
		}

		if ( ! empty( $options->team_b ) ) {
			$query .= $wpdb->prepare( ' AND away_team = %d ', $options->team_b );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by h2h
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->h2h ) {

			$teams = explode( ',', $options->h2h );
			if ( ! empty( $teams[0] ) && ! empty( $teams[1] ) && absint( $teams[0] ) && absint( $teams[1] ) ) {
				$query .= $wpdb->prepare(
					' AND ( ( home_team = %d AND away_team = %d ) OR ( home_team = %d AND away_team = %d ) )',
					$teams[0],
					$teams[1],
					$teams[1],
					$teams[0]
				);
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by game_day
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->filter_by_game_day ) {

			$game_days = wp_parse_id_list( $options->filter_by_game_day );
			$format    = implode( ', ', array_fill( 0, count( $game_days ), '%d' ) );

			$query .= $wpdb->prepare( " AND game_day IN ({$format}) ", $game_days ); // phpcs:ignore
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by days offset
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->days_offset ) {

			$days_offset = intval( $options->days_offset );

			if ( $days_offset < 0 ) {
				$query .= $wpdb->prepare( " AND kickoff >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ", absint( $days_offset ) ); // phpcs:ignore
			} else {
				$query .= $wpdb->prepare( " AND kickoff >= DATE_ADD(CURDATE(), INTERVAL %d DAY) ", absint( $days_offset ) ); // phpcs:ignore
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by days offset to
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->days_offset_to ) {

			$days_offset_to = intval( $options->days_offset_to );

			if ( $days_offset_to < 0 ) {
				$query .= $wpdb->prepare( " AND kickoff < DATE_SUB(CURDATE(), INTERVAL %d DAY) ", absint( $days_offset_to ) ); // phpcs:ignore
			} else {
				$query .= $wpdb->prepare( " AND kickoff < DATE_ADD(CURDATE(), INTERVAL %d DAY) ", absint( $days_offset_to ) ); // phpcs:ignore
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by priority
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->priority ) {

			if ( absint( $options->priority ) ) {
				$query .= $wpdb->prepare( " AND priority >= %d ", absint( $options->priority ) ); // phpcs:ignore
			}
		}

		/*
		|--------------------------------------------------------------------
		| ORDER BY date, game day
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->sort_by_game_day ) {

			$game_day_order = mb_strtoupper( sanitize_key( $options->sort_by_game_day ) );

			if ( 'ASC' === $game_day_order ) {
				$query .= " ORDER BY game_day $game_day_order, CASE WHEN kickoff = '0000-00-00 00:00:00' THEN 1 ELSE 0 END, kickoff ASC";
			} elseif ( 'DESC' === $options->sort_by_date ) {
				$query .= " ORDER BY game_day $game_day_order, CASE WHEN kickoff = '0000-00-00 00:00:00' THEN 1 ELSE 0 END, kickoff DESC";
			} else {
				$query .= " ORDER BY game_day $game_day_order";
			}
		} else {
			if ( 'asc' === $options->sort_by_date ) {
				$query .= ' ORDER BY CASE WHEN kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, kickoff ASC, game_id ASC';
			} elseif ( 'desc' === $options->sort_by_date ) {
				$query .= ' ORDER BY CASE WHEN kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, kickoff DESC, game_id DESC';
			}
		}

		/*
		|--------------------------------------------------------------------
		| Limit
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->limit && $options->limit > 0 ) {
			if ( ! empty( $options->offset ) && absint( $options->offset ) ) {
				$query .= $wpdb->prepare( ' LIMIT %d,%d', $options->offset, $options->limit );
			} else {
				$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );
			}
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( 'stats' === $result ) {
			return $games;
		}

		// Populate Object Cache
		$ids = wp_list_pluck( $games, 'game_id' );

		if ( 'ids' === $result ) {
			return $ids;
		}

		// Get game links
		$game_posts = [];

		$args = [
			'include'       => $ids,
			'post_type'     => 'sl_game',
			'cache_results' => false,
		];

		/** @var WP_Post $game_post */
		foreach ( get_posts( $args ) as $game_post ) {
			$game_posts[ $game_post->ID ] = $game_post;
		}

		// Add extra data to game
		foreach ( $games as $game ) {
			$game->permalink = get_permalink( isset( $game_posts[ $game->game_id ] ) ? $game_posts[ $game->game_id ] : $game->game_id );
			$game->aggtext   = $this->get_game_aggtext( $game->game_id );
		}

		return $games;
	}

	/**
	 * Filters the array of row action links on the Pages list table.
	 *
	 * @param array   $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function modify_quick_actions( $actions, $post ) {

		if ( 'sl_game' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {

			// Create edit link
			$edit_link = admin_url( 'post.php?post=' . intval( $post->ID ) . '&action=edit&setup-game-structure=yes' );

			$actions['edit-game-header'] = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit structure', 'sports-leagues' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 * @since 0.1.0
	 */
	public function disable_months_dropdown( $disable, $post_type ) {

		return 'sl_game' === $post_type ? true : $disable;
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes_sl_game',
			function () {
				add_meta_box(
					'sl_game_metabox',
					esc_html__( 'Game', 'sports-leagues' ),
					[ $this, 'render_metabox' ],
					'sl_game',
					'normal',
					'high'
				);
			}
		);
	}

	/**
	 * Render Meta Box content for Game Data.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.1.0
	 */
	public function render_metabox( $post ) {

		// Error message on tournaments not exist
		if ( ! $this->plugin->tournament->get_tournaments() ) {
			echo '<div class="anwp-b-wrap"><div class="my-3 alert alert-warning border border-warning">' . esc_html__( 'Please, create a Tournament first.', 'sports-leagues' ) . '</div></div>';

			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$setup_game = isset( $_GET['setup-game-structure'] ) && 'yes' === $_GET['setup-game-structure'];

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		$app_id = apply_filters( 'sports-leagues/game/vue_app_id', 'sl-app-game-v3' );

		if ( ! $setup_game && 'yes' === get_post_meta( $post->ID, '_sl_fixed', true ) ) :

			// Initial data
			$home_team_id = (int) get_post_meta( $post->ID, '_sl_team_home', true );
			$away_team_id = (int) get_post_meta( $post->ID, '_sl_team_away', true );

			$home_team = $this->plugin->team->get_team_title_by_id( $home_team_id );
			$away_team = $this->plugin->team->get_team_title_by_id( $away_team_id );

			$home_logo = get_the_post_thumbnail_url( $home_team_id );
			$away_logo = get_the_post_thumbnail_url( $away_team_id );

			$stage_id      = (int) get_post_meta( $post->ID, '_sl_stage_id', true );
			$tournament_id = (int) get_post_meta( $post->ID, '_sl_tournament_id', true );
			$round_id      = (int) get_post_meta( $post->ID, '_sl_round_id', true );
			$round_title   = $this->plugin->tournament->get_round_title( $round_id, $stage_id );

			$season_id   = get_post_meta( $post->ID, '_sl_season_id', true );
			$season_term = get_term( $season_id, 'sl_season' );
			$league_term = get_term( get_post_meta( $post->ID, '_sl_league_id', true ), 'sl_league' );

			$is_menu_collapsed = 'yes' === get_user_setting( 'anwp-sl-collapsed-menu' );
			?>
			<div class="anwp-b-wrap">

				<div class="mb-3 border border-success bg-light px-3 py-2">
					<div class="d-flex flex-wrap align-items-center">
						<b class="mr-1"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?>:</b>
						<span><?php echo esc_html( get_the_title( $tournament_id ) ); ?></span>

						<span class="text-muted anwp-small mx-2">|</span>
						<b class="mr-1"><?php echo esc_html__( 'Stage', 'sports-leagues' ); ?>:</b>
						<span><?php echo esc_html( get_the_title( $stage_id ) ) ?: esc_html__( '- no title -', 'sports-leagues' ); ?></span>

						<?php if ( ! empty( $league_term->name ) ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'League', 'sports-leagues' ); ?>:</b>
							<span><?php echo esc_html( $league_term->name ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $season_term->name ) ) : ?>
							<span class="text-muted anwp-small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?>:</b>
							<span><?php echo esc_html( $season_term->name ); ?></span>
						<?php endif; ?>

						<?php if ( $round_title ) : ?>
							<span class="text-muted anwp-small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Round', 'sports-leagues' ); ?>:</b> <span><?php echo esc_html( $round_title ); ?></span>
						<?php endif; ?>

						<a class="ml-auto" target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit&setup-game-structure=yes' ) ); ?>">
							<?php echo esc_html__( 'Edit structure', 'sports-leagues' ); ?>
						</a>
					</div>

					<div class="d-flex flex-wrap pt-2 mt-2 align-items-center border-top">
						<div class="d-flex align-items-center pb-2">
							<?php if ( $home_logo ) : ?>
								<img class="anwp-object-contain anwp-w-60 anwp-h-60" src="<?php echo esc_attr( $home_logo ); ?>" alt="" />
							<?php endif; ?>
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $home_team_id ) . '&action=edit' ) ); ?>" target="_blank"
								data-anwpfl_tippy data-tippy-content="<?php echo esc_attr__( 'Edit Team', 'sports-leagues' ); ?>"
								class="text-decoration-none mx-3 d-inline-block anwp-text-xl anwp-text-gray-800"><?php echo esc_html( $home_team ); ?></a>
						</div>

						<div class="mx-3">
							<div class="anwp-text-gray-500 anwp-text-base d-inline-block my-0">-</div>
						</div>

						<div class="d-flex flex-sm-row-reverse align-items-center pb-2">
							<?php if ( $away_logo ) : ?>
								<img class="anwp-object-contain anwp-w-60 anwp-h-60" src="<?php echo esc_attr( $away_logo ); ?>" alt="" />
							<?php endif; ?>
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $away_team_id ) . '&action=edit' ) ); ?>" target="_blank"
								data-anwpfl_tippy data-tippy-content="<?php echo esc_attr__( 'Edit Team', 'sports-leagues' ); ?>"
								class="text-decoration-none mx-3 d-inline-block anwp-text-xl anwp-text-gray-800"><?php echo esc_html( $away_team ); ?></a>
						</div>
					</div>

					<?php if ( empty( $league_term->name ) || empty( $season_term->name ) || empty( get_post( $tournament_id )->post_title ) ) : ?>
						<div class="my-2 p-3 anwp-bg-orange-200 anwp-border anwp-border-orange-800 anwp-text-orange-900 d-flex align-items-center">
							<svg class="anwp-icon anwp-icon--octi anwp-icon--s24 mr-2 anwp-fill-current">
								<use xlink:href="#icon-alert"></use>
							</svg>
							<div>
								<?php echo esc_html__( 'Your Game Structure is invalid.', 'sports-leagues' ); ?><br>
								<?php echo esc_html__( 'Game should have published Tournament, Season and League.', 'sports-leagues' ); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="d-flex mt-2" id="anwp-sl-metabox-page-nav">
					<div class="anwp-sl-menu-wrapper mr-3 d-none d-md-block sticky-top align-self-start anwp-flex-none <?php echo esc_attr( $is_menu_collapsed ? 'anwp-sl-collapsed-menu' : '' ); ?>" style="top: 50px;">

						<button id="anwp-sl-publish-click-proxy" class="w-100 button button-primary py-2 mb-4 d-flex align-items-center justify-content-center" type="submit">
							<svg class="anwp-icon anwp-icon--feather anwp-icon--s16">
								<use xlink:href="#icon-save"></use>
							</svg>
							<span class="ml-2"><?php echo esc_html__( 'Save', 'sports-leagues' ); ?></span>
							<span class="spinner m-0"></span>
						</button>

						<ul class="m-0 p-0 list-unstyled">
							<?php
							$nav_items = [
								[
									'icon'  => 'gear',
									'label' => esc_html__( 'General Info', 'sports-leagues' ),
									'slug'  => 'anwp-sl-general-metabox',
								],
								[
									'icon'  => 'law',
									'label' => esc_html__( 'Outcome & Points', 'sports-leagues' ),
									'slug'  => 'anwp-sl-outcome-metabox',
								],
								[
									'icon'  => 'graph',
									'label' => esc_html__( 'Game Team Stats', 'sports-leagues' ),
									'slug'  => 'anwp-sl-team-stats-metabox',
								],
								[
									'icon'  => 'jersey',
									'label' => esc_html__( 'Players List', 'sports-leagues' ),
									'slug'  => 'anwp-sl-players-metabox',
								],
								[
									'icon'  => 'organization',
									'label' => esc_html__( 'Staff List', 'sports-leagues' ),
									'slug'  => 'anwp-sl-game-staff-metabox',
								],
								[
									'icon'  => 'organization',
									'label' => esc_html__( 'Officials', 'sports-leagues' ),
									'slug'  => 'anwp-sl-game-officials-metabox',
								],
								[
									'icon'  => 'pulse',
									'label' => esc_html__( 'Game Events', 'sports-leagues' ),
									'slug'  => 'anwp-sl-game-events-metabox',
								],
								[
									'icon'  => 'pulse',
									'label' => esc_html__( 'Players Statistics', 'sports-leagues' ),
									'slug'  => 'anwp-sl-game-custom-stats-metabox',
								],
								[
									'icon'  => 'x',
									'label' => esc_html__( 'Game Sidelines', 'sports-leagues' ),
									'slug'  => 'anwp-sl-game-sidelines-metabox',
								],
								[
									'icon'  => 'note',
									'label' => esc_html__( 'Text Content', 'sports-leagues' ),
									'slug'  => 'anwp-sl-summary-metabox',
								],
								[
									'icon'  => 'device-camera',
									'label' => esc_html__( 'Media', 'sports-leagues' ),
									'slug'  => 'anwp-sl-media-game-metabox',
								],
							];

							/**
							 * Modify metabox nav items
							 *
							 * @since 0.10.0
							 */
							$nav_items = apply_filters( 'sports-leagues/game/metabox_nav_items', $nav_items );

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo sports_leagues()->helper->create_metabox_navigation( $nav_items );

							/**
							 * Fires at the bottom of Metabox Nav.
							 *
							 * @since 0.10.0
							 */
							do_action( 'sports-leagues/game/metabox_nav_bottom' );
							?>
						</ul>

					</div>
					<div class="flex-grow-1 anwp-min-width-0 mb-4">

						<?php do_action( 'sports-leagues/game/before_app' ); ?>

						<div id="<?php echo esc_attr( $app_id ); ?>"></div>

						<?php cmb2_metabox_form( 'sl_game_cmb2_metabox' ); ?>

						<?php
						/**
						 * Fires at the bottom of Metabox.
						 *
						 * @since 0.10.0
						 */
						do_action( 'sports-leagues/game/metabox_bottom' );
						?>
					</div>
				</div>

				<input type="hidden" value="yes" name="_sl_fixed">

				<?php
				/**
				 * Fires at the bottom of Game edit form.
				 *
				 * @since 0.10.0
				 */
				do_action( 'sports-leagues/game/edit_form_bottom' );
				?>
			</div>
		<?php else : ?>
			<div class="anwp-b-wrap game_setup">
				<?php
				if ( $setup_game ) :

					$home_id = get_post_meta( $post->ID, '_sl_team_home', true );
					$away_id = get_post_meta( $post->ID, '_sl_team_away', true );

					$home_title = $this->plugin->team->get_team_title_by_id( $home_id );
					$away_title = $this->plugin->team->get_team_title_by_id( $away_id );

					$tournament_id = get_post_meta( $post->ID, '_sl_tournament_id', true );
					$stage_id      = get_post_meta( $post->ID, '_sl_stage_id', true );

					// Check for a Round title
					$is_knockout = 'knockout' === get_post_meta( $stage_id, '_sl_stage_system', true );
					$round_title = $is_knockout ? $this->plugin->tournament->get_round_title( get_post_meta( $post->ID, '_sl_round_id', true ), $stage_id ) : '';
					?>
					<div class="my-3 alert alert-warning my-2">
						<?php echo esc_html__( 'Use the Game Structure editing with caution.', 'sports-leagues' ); ?><br>
						<?php echo esc_html__( 'Save Game data on the next step to recalculate statistic.', 'sports-leagues' ); ?>
					</div>
					<div class="my-3 alert alert-info my-2">
						<h3 class="mt-0 mb-2"><?php echo esc_html__( 'Old Structure', 'sports-leagues' ); ?></h3>
						<b class="mr-1"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?>:</b>
						<span><?php echo esc_html( get_the_title( $tournament_id ) ); ?></span>

						<span class="text-muted small mx-2">|</span>
						<b class="mr-1"><?php echo esc_html__( 'Stage', 'sports-leagues' ); ?>:</b>
						<span><?php echo esc_html( get_the_title( $stage_id ) ); ?></span>

						<?php if ( $round_title ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Round', 'sports-leagues' ); ?>:</b> <span><?php echo esc_html( $round_title ); ?></span>
						<?php endif; ?>
						<br>
						<?php echo esc_html( $home_title . ' - ' . $away_title ); ?>
					</div>
				<?php endif; ?>
				<div id="<?php echo esc_attr( $app_id ); ?>"></div>
			</div>
			<?php
		endif;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post
	 *
	 * @return bool|int
	 * @since  0.1.0
	 */
	public function save_metabox( $post_id, $post ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'sl_game' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/** ---------------------------------------
		 * Save Game Data
		 *
		 * @since 0.1.0
		 * ---------------------------------------*/

		$fixed = empty( $_POST['_sl_fixed'] ) ? '' : sanitize_key( $_POST['_sl_fixed'] );

		$setup       = isset( $_POST['_sl_game_setup'] ) ? sanitize_key( $_POST['_sl_game_setup'] ) : '';
		$slug_update = false;
		$data        = [];

		if ( 'yes' === $setup ) {

			/** ---------------------------------------
			 * Game Setup Saving
			 *
			 * @since 0.1.0
			 * ---------------------------------------*/
			$data = [
				'tournament_id' => isset( $_POST['_sl_tournament_id'] ) ? intval( $_POST['_sl_tournament_id'] ) : '',
				'stage_id'      => isset( $_POST['_sl_stage_id'] ) ? intval( $_POST['_sl_stage_id'] ) : '',
				'round_id'      => isset( $_POST['_sl_round_id'] ) ? intval( $_POST['_sl_round_id'] ) : '',
				'group_id'      => isset( $_POST['_sl_group_id'] ) ? intval( $_POST['_sl_group_id'] ) : '',
				'team_home'     => isset( $_POST['_sl_team_home_id'] ) ? intval( $_POST['_sl_team_home_id'] ) : '',
				'team_away'     => isset( $_POST['_sl_team_away_id'] ) ? intval( $_POST['_sl_team_away_id'] ) : '',
			];

			if ( isset( $_POST['anwp-game-setup-submit'] ) && 'yes' === $_POST['anwp-game-setup-submit'] ) {
				if ( $data['tournament_id'] && $data['stage_id'] && $data['team_home'] && $data['team_away'] ) {
					$data['fixed'] = 'yes';
				}
			}

			// Get tournament taxonomies
			$post_seasons   = get_the_terms( $data['tournament_id'], 'sl_season' );
			$post_season_id = empty( $post_seasons[0]->term_id ) ? '' : $post_seasons[0]->term_id;

			$post_leagues   = get_the_terms( $data['tournament_id'], 'sl_league' );
			$post_league_id = empty( $post_leagues[0]->term_id ) ? '' : $post_leagues[0]->term_id;

			$data['season_id'] = $post_season_id;
			$data['league_id'] = $post_league_id;

			// Update Game meta
			foreach ( $data as $key => $value ) {
				update_post_meta( $post_id, '_sl_' . $key, $value );
			}

			// Check slug update needed
			if ( $data['team_home'] && $data['team_away'] ) {
				$slug_update = true;
			}
		} elseif ( 'yes' === $fixed ) {

			/** ---------------------------------------
			 * Game Data Saving
			 *
			 * @since 0.1.0
			 * ---------------------------------------*/

			$post_data = wp_unslash( $_POST );

			/*
			|--------------------------------------------------------------------
			| Publish Draft Game
			|--------------------------------------------------------------------
			*/
			if ( 'draft' === $_POST['post_status'] && 'draft' === $post->post_status && isset( $_POST['save-publish-game'] ) && 'yes' === $_POST['save-publish-game'] ) {
				remove_action( 'save_post_sl_game', [ $this, 'save_metabox' ] );
				wp_publish_post( $post );
				add_action( 'save_post_sl_game', [ $this, 'save_metabox' ] );
			}

			/*
			|--------------------------------------------------------------------
			| Additional Game Data
			|--------------------------------------------------------------------
			*/
			$data['status']      = isset( $_POST['_sl_status'] ) ? sanitize_key( $_POST['_sl_status'] ) : '';
			$data['venue_id']    = isset( $_POST['_sl_venue_id'] ) ? sanitize_key( $_POST['_sl_venue_id'] ) : '';
			$data['attendance']  = isset( $_POST['_sl_attendance'] ) ? sanitize_key( $_POST['_sl_attendance'] ) : '';
			$data['gameday']     = isset( $_POST['_sl_gameday'] ) ? sanitize_key( $_POST['_sl_gameday'] ) : '';
			$data['game_number'] = isset( $_POST['_sl_game_number'] ) ? sanitize_key( $_POST['_sl_game_number'] ) : '';
			$data['aggtext']     = isset( $_POST['_sl_aggtext'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_aggtext'] ) ) : '';

			$data['players_home'] = isset( $_POST['_sl_players_home'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_players_home'] ) ) : '';
			$data['players_away'] = isset( $_POST['_sl_players_away'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_players_away'] ) ) : '';
			$data['staff_home']   = isset( $_POST['_sl_staff_home'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_staff_home'] ) ) : '';
			$data['staff_away']   = isset( $_POST['_sl_staff_away'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_staff_away'] ) ) : '';
			$data['officials']    = isset( $_POST['_sl_officials'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_officials'] ) ) : '';

			// Save Special Status / Unset on game finished
			$data['special_status'] = ( ! empty( $_POST['_sl_special_status'] ) && 'finished' !== $_POST['_sl_status'] ) ? sanitize_text_field( $_POST['_sl_special_status'] ) : '';

			/*
			|--------------------------------------------------------------------
			| Update Meta!
			|--------------------------------------------------------------------
			*/
			foreach ( $data as $key => $value ) {
				update_post_meta( $post_id, '_sl_' . $key, $value );
			}

			/*
			|--------------------------------------------------------------------
			| Temporary players (text fields)
			|--------------------------------------------------------------------
			*/
			$temp_players = isset( $_POST['_sl_temp_players'] ) ? json_decode( stripslashes( $_POST['_sl_temp_players'] ) ) : [];

			if ( empty( $temp_players ) ) {
				delete_post_meta( $post_id, '_sl_temp_players' );
			} else {
				update_post_meta( $post_id, '_sl_temp_players', wp_slash( wp_json_encode( $temp_players ) ) );
			}

			/*
			|--------------------------------------------------------------------
			| Temporary staff (text fields)
			|--------------------------------------------------------------------
			*/
			$temp_staff = isset( $_POST['_sl_temp_staffs'] ) ? json_decode( stripslashes( $_POST['_sl_temp_staffs'] ) ) : [];

			if ( empty( $temp_staff ) ) {
				delete_post_meta( $post_id, '_sl_temp_staffs' );
			} else {
				update_post_meta( $post_id, '_sl_temp_staffs', wp_slash( wp_json_encode( $temp_staff ) ) );
			}

			/*
			|--------------------------------------------------------------------
			| Temporary officials (text fields)
			|--------------------------------------------------------------------
			*/
			$temp_officials = isset( $_POST['_sl_temp_officials'] ) ? json_decode( stripslashes( $_POST['_sl_temp_officials'] ) ) : [];

			if ( empty( $temp_officials ) ) {
				delete_post_meta( $post_id, '_sl_temp_officials' );
			} else {
				update_post_meta( $post_id, '_sl_temp_officials', wp_slash( wp_json_encode( $temp_officials ) ) );
			}

			/*
			|--------------------------------------------------------------------
			| General Data (already saved).
			|--------------------------------------------------------------------
			*/
			$data['tournament_id'] = get_post_meta( $post_id, '_sl_tournament_id', true );
			$data['stage_id']      = get_post_meta( $post_id, '_sl_stage_id', true );
			$data['league_id']     = get_post_meta( $post_id, '_sl_league_id', true );
			$data['season_id']     = get_post_meta( $post_id, '_sl_season_id', true );
			$data['round_id']      = get_post_meta( $post_id, '_sl_round_id', true );
			$data['group_id']      = get_post_meta( $post_id, '_sl_group_id', true );
			$data['team_home']     = get_post_meta( $post_id, '_sl_team_home', true );
			$data['team_away']     = get_post_meta( $post_id, '_sl_team_away', true );

			/*
			|--------------------------------------------------------------------
			| Complex fields with extra WP sanitization:
			|--------------------------------------------------------------------
			*/
			// Get scores data
			$home_scores = json_decode( wp_unslash( $_POST['_sl_scores_home'] ) );
			$away_scores = json_decode( wp_unslash( $_POST['_sl_scores_away'] ) );

			// Validate
			$home_scores = empty( $home_scores ) ? (object) [] : $home_scores;
			$away_scores = empty( $away_scores ) ? (object) [] : $away_scores;

			// Fetch some data from scores object
			$data['home_scores'] = isset( $home_scores->final ) ? sanitize_text_field( $home_scores->final ) : '';
			$data['away_scores'] = isset( $away_scores->final ) ? sanitize_text_field( $away_scores->final ) : '';

			$data['home_outcome'] = empty( $home_scores->outcome ) ? '' : sanitize_key( $home_scores->outcome );
			$data['away_outcome'] = empty( $away_scores->outcome ) ? '' : sanitize_key( $away_scores->outcome );

			$data['home_pts'] = empty( $home_scores->pts ) ? 0 : intval( $home_scores->pts );
			$data['away_pts'] = empty( $away_scores->pts ) ? 0 : intval( $away_scores->pts );

			$data['home_bpts'] = empty( $home_scores->bpts ) ? 0 : intval( $home_scores->bpts );
			$data['away_bpts'] = empty( $away_scores->bpts ) ? 0 : intval( $away_scores->bpts );

			/*
			|--------------------------------------------------------------------
			| Save All Scores and Final Scores to DB
			|--------------------------------------------------------------------
			*/
			update_post_meta( $post_id, '_sl_scores_home', wp_slash( wp_json_encode( $home_scores ) ) );
			update_post_meta( $post_id, '_sl_scores_away', wp_slash( wp_json_encode( $away_scores ) ) );

			update_post_meta( $post_id, '_sl_home_score', $data['home_scores'] );
			update_post_meta( $post_id, '_sl_away_score', $data['away_scores'] );

			/*
			|--------------------------------------------------------------------
			| Team Stats
			|--------------------------------------------------------------------
			*/
			$team_stats_home = wp_unslash( $_POST['_sl_team_stats_home'] ) ?: [];
			$team_stats_away = wp_unslash( $_POST['_sl_team_stats_away'] ) ?: [];

			$config_team_stats = wp_list_pluck( Sports_Leagues_Config::get_value( 'game_team_stats', [] ), 'id' );

			if ( ! empty( $team_stats_home ) ) {
				$team_stats_home = explode( '|', $team_stats_home );
			}

			if ( ! empty( $team_stats_away ) ) {
				$team_stats_away = explode( '|', $team_stats_away );
			}

			if ( count( $team_stats_home ) && count( $config_team_stats ) && count( $team_stats_home ) === count( $config_team_stats ) ) {
				$team_stats_home = array_combine( $config_team_stats, $team_stats_home );
			} else {
				$team_stats_home = [];
			}

			if ( count( $team_stats_away ) && count( $config_team_stats ) && count( $team_stats_away ) === count( $config_team_stats ) ) {
				$team_stats_away = array_combine( $config_team_stats, $team_stats_away );
			} else {
				$team_stats_away = [];
			}

			update_post_meta( $post_id, '_sl_team_stats_home', wp_slash( wp_json_encode( (object) $team_stats_home ) ) );
			update_post_meta( $post_id, '_sl_team_stats_away', wp_slash( wp_json_encode( (object) $team_stats_away ) ) );

			/*
			|--------------------------------------------------------------------
			| Custom Numbers
			|--------------------------------------------------------------------
			*/
			$custom_numbers      = isset( $_POST['_sl_custom_numbers'] ) ? json_decode( stripslashes( $_POST['_sl_custom_numbers'] ) ) : (object) [];
			$custom_numbers_json = wp_json_encode( $custom_numbers );

			if ( $custom_numbers_json ) {
				update_post_meta( $post_id, '_sl_custom_numbers', wp_slash( $custom_numbers_json ) );
			}

			/*
			|--------------------------------------------------------------------
			| Missing Players
			|--------------------------------------------------------------------
			*/
			$missing_players      = isset( $_POST['_sl_missing_players'] ) ? json_decode( wp_unslash( $_POST['_sl_missing_players'] ) ) : [];
			$missing_players_json = wp_json_encode( $missing_players );

			if ( $missing_players_json ) {
				update_post_meta( $post_id, '_sl_missing_players', wp_slash( $missing_players_json ) );
			}

			/*
			|--------------------------------------------------------------------
			| Validate and Save Kickoff time
			|--------------------------------------------------------------------
			*/
			$data['datetime']     = isset( $_POST['_sl_datetime'] ) ? sanitize_text_field( wp_unslash( $_POST['_sl_datetime'] ) . ':00' ) : '';
			$data['datetime']     = $this->plugin->helper->validate_date( $data['datetime'] ) ? $data['datetime'] : '';
			$data['datetime_gmt'] = $data['datetime'] ? get_gmt_from_date( $data['datetime'] ) : '';

			update_post_meta( $post_id, '_sl_datetime', $data['datetime'] );
			update_post_meta( $post_id, '_sl_datetime_gmt', $data['datetime_gmt'] );

			/*
			|--------------------------------------------------------------------
			| Some extra data needed for recalculating
			|--------------------------------------------------------------------
			*/
			$data['game_id']      = (int) $post_id;
			$data['stage_status'] = intval( $data['stage_id'] ) ? get_post_meta( $data['stage_id'], '_sl_stage_status', true ) : '';
			$data['priority']     = isset( $_POST['_sl_game_priority'] ) ? sanitize_text_field( $_POST['_sl_game_priority'] ) : 0;

			/*
			|--------------------------------------------------------------------
			| Save or delete game data
			|--------------------------------------------------------------------
			*/
			if ( 'publish' === $post->post_status ) {
				$this->save_game_statistics( $data );
				$this->save_missing_players( $missing_players, $post_id );
			} else {
				$this->remove_game_statistics( $post_id );
			}

			// Recalculate standing
			$this->plugin->standing->calculate_standing_prepare( $post_id );

			// Saving Events
			$this->plugin->event->save_game_events( $post, $data, $post_data );

			/**
			 * Fires after save game.
			 *
			 * @param WP_Post $post
			 * @param array   $data
			 * @param array   $post_data
			 *
			 * @since 0.5.15
			 */
			do_action( 'sports-leagues/game/after_save', $post, $data, $post_data );

			$slug_update = true;
		}

		/*
		|--------------------------------------------------------------------
		| Compose Game Slug
		|--------------------------------------------------------------------
		*/
		if ( $slug_update && ! empty( $data['team_home'] ) && ! empty( $data['team_away'] ) ) {

			/**
			 * Update Game title and slug.
			 *
			 * @since 0.1.0
			 */
			$post      = get_post( $post_id );
			$home_team = $this->plugin->team->get_team_title_by_id( $data['team_home'] );
			$away_team = $this->plugin->team->get_team_title_by_id( $data['team_away'] );

			if ( ! $home_team || ! $away_team ) {
				return $post_id;
			}

			if ( trim( Sports_Leagues_Options::get_value( 'game_title_generator' ) ) ) {
				$game_title = $this->get_game_title_generated( $data, $home_team, $away_team );
			} else {
				// Compose Game title
				$game_title = sanitize_text_field( $home_team . ' - ' . $away_team );

				/**
				 * Filters a game title before save.
				 *
				 * @param string  $game_title Game title to be returned.
				 * @param string  $home_team  Home team title.
				 * @param string  $away_team  Away team title.
				 * @param WP_Post $post       Game WP_Post object
				 * @param array   $data       Game data
				 *
				 * @since 0.1.0
				 */
				$game_title = apply_filters( 'sports-leagues/game/title_to_save', $game_title, $home_team, $away_team, $post, $data );
			}

			// Compose Game slug
			$game_slug = [ $home_team, $away_team ];
			if ( ! empty( $data['datetime'] ) ) {
				$game_slug[] = explode( ' ', $data['datetime'] )[0];
			}

			$game_slug = implode( ' ', $game_slug );

			/**
			 * Filters a game slug before save.
			 *
			 * @param string  $game_slug Game slug to be returned.
			 * @param WP_Post $post      Game WP_Post object
			 * @param array   $data      Game data
			 *
			 * @since 0.1.0
			 */
			$game_slug = apply_filters( 'sports-leagues/game/slug_to_save', $game_slug, $post, $data );

			// Make game slug unique
			$game_slug = wp_unique_post_slug( sanitize_title_with_dashes( $game_slug ), $post_id, $post->post_status, $post->post_type, $post->post_parent );

			if ( $post->post_name !== $game_slug || $post->post_title !== $game_title ) {

				remove_action( 'save_post_sl_game', [ $this, 'save_metabox' ] );

				wp_update_post(
					[
						'ID'         => $post_id,
						'post_name'  => $game_slug,
						'post_title' => $game_title,
					]
				);

				add_action( 'save_post_sl_game', [ $this, 'save_metabox' ] );
			}
		}

		return $post_id;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.1.0
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen    = get_current_screen();
		$is_game_edit_page = in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'sl_game' === $current_screen->id;

		if ( apply_filters( 'sports-leagues/admin/is_game_edit_page', $is_game_edit_page ) ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			$setup_game = isset( $_GET['setup-game-structure'] ) && 'yes' === $_GET['setup-game-structure'];

			$post_id = apply_filters( 'sports-leagues/admin/alt_game_edit_id', get_the_ID() );

			/*
			|--------------------------------------------------------------------
			| Game Edit
			|--------------------------------------------------------------------
			*/
			if ( ! $setup_game && 'yes' === get_post_meta( $post_id, '_sl_fixed', true ) ) {

				$season_id = get_post_meta( $post_id, '_sl_season_id', true );

				/*
				|--------------------------------------------------------------------
				| Prepare Teams Data
				|--------------------------------------------------------------------
				*/

				$team_home_id = get_post_meta( $post_id, '_sl_team_home', true );
				$team_away_id = get_post_meta( $post_id, '_sl_team_away', true );

				$team_home_obj = $this->plugin->team->get_team_by_id( $team_home_id );
				$team_away_obj = $this->plugin->team->get_team_by_id( $team_away_id );

				$team_home = (object) [
					'id'    => $team_home_id,
					'title' => $team_home_obj->title,
					'logo'  => $team_home_obj->logo,
				];

				$team_away = (object) [
					'id'    => $team_away_id,
					'title' => $team_away_obj->title,
					'logo'  => $team_away_obj->logo,
				];

				/*
				|--------------------------------------------------------------------
				| Prepare players
				|--------------------------------------------------------------------
				*/
				$home_players = $this->plugin->team->get_team_season_players(
					[
						'team_id'   => $team_home_id,
						'season_id' => $season_id,
					],
					'short'
				);

				$away_players = $this->plugin->team->get_team_season_players(
					[
						'team_id'   => $team_away_id,
						'season_id' => $season_id,
					],
					'short'
				);

				/*
				|--------------------------------------------------------------------
				| Players in Squad + Number
				|--------------------------------------------------------------------
				*/
				$home_squad_numbers = [];
				$away_squad_numbers = [];
				$squad_position_map = [];

				foreach ( $home_players as $player ) {
					$home_squad_numbers[ $player->id ] = isset( $player->number ) ? $player->number : '';

					if ( ! empty( $player->role ) ) {
						$squad_position_map[ $player->id ] = $player->role;
					}
				}

				foreach ( $away_players as $player ) {
					$away_squad_numbers[ $player->id ] = isset( $player->number ) ? $player->number : '';

					if ( ! empty( $player->role ) ) {
						$squad_position_map[ $player->id ] = $player->role;
					}
				}

				/*
				|--------------------------------------------------------------------
				| JS APP options
				|--------------------------------------------------------------------
				*/
				$player_actions = [
					[
						'name'  => 'Team Roster',
						'value' => 'team_roster',
					],
					[
						'name'  => 'Team Players',
						'value' => 'team_players',
					],
					[
						'name'  => 'Search By Name',
						'value' => 'search_by_name',
					],
				];

				$staff_actions = [
					[
						'name'  => 'Team Roster',
						'value' => 'team_roster',
					],
					[
						'name'  => 'Team Staff',
						'value' => 'team_staff',
					],
					[
						'name'  => 'Search By Name',
						'value' => 'search_by_name',
					],
				];

				$game_data = [
					'player_actions'         => $player_actions,
					'staff_actions'          => $staff_actions,
					'can_edit_temp_player'   => 'yes',
					'can_edit_temp_staff'    => 'yes',
					'can_edit_temp_official' => 'yes',
					'can_edit_player_number' => 'yes',
					'playersHome'            => get_post_meta( $post_id, '_sl_players_home', true ),
					'playersAway'            => get_post_meta( $post_id, '_sl_players_away', true ),
					'rosterHomeNumbers'      => empty( $home_squad_numbers ) ? (object) [] : $home_squad_numbers,
					'rosterHomeOrder'        => array_keys( $home_squad_numbers ),
					'rosterAwayNumbers'      => empty( $away_squad_numbers ) ? (object) [] : $away_squad_numbers,
					'rosterAwayOrder'        => array_keys( $away_squad_numbers ),
					'default_photo'          => sports_leagues()->helper->get_default_player_photo(),
					'gameDay'                => get_post_meta( $post_id, '_sl_gameday', true ),
					'gameNumber'             => get_post_meta( $post_id, '_sl_game_number', true ),
					'venue_id'               => get_post_meta( $post_id, '_sl_venue_id', true ),
					'attendance'             => get_post_meta( $post_id, '_sl_attendance', true ),
					'special_status'         => get_post_meta( $post_id, '_sl_special_status', true ),
					'aggtext'                => get_post_meta( $post_id, '_sl_aggtext', true ),
					'status'                 => get_post_meta( $post_id, '_sl_status', true ),
					'datetime'               => get_post_meta( $post_id, '_sl_datetime', true ),
					'scoresHome'             => get_post_meta( $post_id, '_sl_scores_home', true ),
					'scoresAway'             => get_post_meta( $post_id, '_sl_scores_away', true ),
					'customNumbers'          => get_post_meta( $post_id, '_sl_custom_numbers', true ),
					'optionsPlayers'         => $this->plugin->player->get_players_list( $squad_position_map ),
					'optionsTeamMap'         => $this->plugin->team->get_team_options(),
					'optionsPositionMap'     => $this->plugin->config->get_player_positions(),
					'optionsOfficials'       => $this->plugin->official->get_officials(),
					'playersHomeGame'        => get_post_meta( $post_id, '_sl_players_home', true ),
					'playersAwayGame'        => get_post_meta( $post_id, '_sl_players_away', true ),
					'optionsStaff'           => $this->plugin->staff->get_staff_list(),
					'team_home'              => $team_home,
					'team_away'              => $team_away,
					'homeStaff'              => get_post_meta( $post_id, '_sl_staff_home', true ),
					'awayStaff'              => get_post_meta( $post_id, '_sl_staff_away', true ),
					'homeRosterStaff'        => $this->plugin->staff->get_team_season_staff( $team_home_id, $season_id ),
					'awayRosterStaff'        => $this->plugin->staff->get_team_season_staff( $team_away_id, $season_id ),
					'teamStatsHome'          => get_post_meta( $post_id, '_sl_team_stats_home', true ),
					'teamStatsAway'          => get_post_meta( $post_id, '_sl_team_stats_away', true ),
					'officialsGame'          => get_post_meta( $post_id, '_sl_officials', true ),
					'gameEvents'             => $this->plugin->event->get_game_events_to_edit( $post_id ),
					'missingPlayers'         => get_post_meta( $post_id, '_sl_missing_players', true ),
					'tempPlayers'            => get_post_meta( $post_id, '_sl_temp_players', true ),
					'tempStaffs'             => get_post_meta( $post_id, '_sl_temp_staffs', true ),
					'tempOfficials'          => get_post_meta( $post_id, '_sl_temp_officials', true ),
				];

				/**
				 * Filters a game data.
				 *
				 * @param array $game_data Game data
				 * @param int   $post_id   Game Post ID
				 *
				 * @since 0.1.0
				 */
				$game_data = apply_filters( 'sports-leagues/game/edit_form_data', $game_data, $post_id );

				wp_localize_script( 'sl_admin', '_slGame', $game_data );
				wp_localize_script( 'sl_admin', '_slEventOptions', $this->plugin->event->get_options() );

				$game_points = sports_leagues()->helper->get_outcome_options();
				wp_localize_script( 'sl_admin', '_slGamePoints', $game_points );

				// Get number of overtimes
				$scores_home      = json_decode( get_post_meta( $post_id, '_sl_scores_home', true ) );
				$scores_away      = json_decode( get_post_meta( $post_id, '_sl_scores_away', true ) );
				$num_of_overtimes = $this->get_number_of_game_overtimes( $post_id, $scores_home, $scores_away );

				$game_config = [
					'l10n_datepicker'   => sports_leagues()->data->get_vue_datepicker_locale(),
					'num_of_periods'    => (int) Sports_Leagues_Config::get_value( 'num_of_periods', '3' ),
					'hide_points'       => Sports_Leagues_Config::get_value( 'standing_points_hide' ),
					'hide_bonus_points' => Sports_Leagues_Config::get_value( 'bonus_points_hide' ),
					'overtimes_hide'    => Sports_Leagues_Config::get_value( 'overtimes_hide' ),
					'penalty_hide'      => Sports_Leagues_Config::get_value( 'penalty_hide' ),
					'num_of_overtimes'  => $num_of_overtimes,
					'venues'            => $this->plugin->venue->get_venue_obj_options(),
					'venue_default'     => get_post_meta( 'away' === Sports_Leagues_Options::get_value( 'team_listed_first' ) ? $team_away_id : $team_home_id, '_sl_venue', true ),
					'game_groups'       => Sports_Leagues_Config::get_value( 'game_player_groups', [] ),
					'player_positions'  => array_keys( sports_leagues()->config->get_player_positions() ),
					'game_staff_groups' => Sports_Leagues_Config::get_value( 'game_staff_groups', [] ),
					'game_stats'        => Sports_Leagues_Config::get_value( 'game_team_stats', [] ),
					'official_groups'   => Sports_Leagues_Config::get_value( 'official_groups', [] ),
					'countries'         => sports_leagues()->data->get_countries(),
				];

				wp_localize_script( 'sl_admin', '_slGameConfig', $game_config );

				/*
				|--------------------------------------------------------------------
				| L10n
				|--------------------------------------------------------------------
				*/
				$game_l10n = [
					'add_official_as_text'   => esc_html__( 'Add official as text string without creating its profile in the site database.', 'sports-leagues' ),
					'add_player_as_text'     => esc_html__( 'Add player as text string without creating its profile in the site database.', 'sports-leagues' ),
					'add_staff_as_text'      => esc_html__( 'Add staff as text string without creating its profile in the site database.', 'sports-leagues' ),
					'add_temporary_official' => esc_html__( 'Add Temporary Official', 'sports-leagues' ),
					'add_temporary_player'   => esc_html__( 'Add Temporary Player', 'sports-leagues' ),
					'add_temporary_staff'    => esc_html__( 'Add Temporary Staff', 'sports-leagues' ),
					'group'                  => esc_html__( 'Group', 'sports-leagues' ),
					'job'                    => esc_html__( 'Job', 'sports-leagues' ),
					'nationality'            => esc_html__( 'Nationality', 'sports-leagues' ),
					'official_name'          => esc_html__( 'Official Name', 'sports-leagues' ),
					'player_name'            => esc_html__( 'Player Name', 'sports-leagues' ),
					'position'               => esc_html__( 'Position', 'sports-leagues' ),
					'saved_official'         => esc_html__( 'Saved Official', 'sports-leagues' ),
					'saved_players'          => esc_html__( 'Saved Players', 'sports-leagues' ),
					'saved_staff'            => esc_html__( 'Saved Staff', 'sports-leagues' ),
					'select_saved_official'  => esc_html__( 'Select from the list of officials saved on the site.', 'sports-leagues' ),
					'select_saved_players'   => esc_html__( 'Select from the list of players saved on the site.', 'sports-leagues' ),
					'select_saved_staff'     => esc_html__( 'Select from the list of staff saved on the site.', 'sports-leagues' ),
					'staff_name'             => esc_html__( 'Staff Name', 'sports-leagues' ),
					'temporary_official'     => esc_html__( 'Temporary Official', 'sports-leagues' ),
					'temporary_players'      => esc_html__( 'Temporary Player', 'sports-leagues' ),
					'temporary_staff'        => esc_html__( 'Temporary Staff', 'sports-leagues' ),
				];

				wp_localize_script( 'sl_admin', '_slGame_l10n', $game_l10n );
			} else {

				/*
				|--------------------------------------------------------------------
				| Game Structure Setup
				|--------------------------------------------------------------------
				*/
				$game_data = [
					'tournaments' => $this->plugin->tournament->get_tournaments(),
					'teamsList'   => $this->plugin->team->get_team_objects(),
					'tournament'  => get_post_meta( $post_id, '_sl_tournament_id', true ),
					'stage'       => get_post_meta( $post_id, '_sl_stage_id', true ),
					'group'       => get_post_meta( $post_id, '_sl_group_id', true ),
					'round'       => get_post_meta( $post_id, '_sl_round_id', true ),
					'teamHome'    => get_post_meta( $post_id, '_sl_team_home', true ),
					'teamAway'    => get_post_meta( $post_id, '_sl_team_away', true ),
				];

				/**
				 * Filters a game data to localize.
				 *
				 * @param array $game_data Game data
				 * @param int   $post_id   Game Post ID
				 *
				 * @since 0.1.0
				 */
				$game_data = apply_filters( 'sports-leagues/game/edit_form_setup_data', $game_data, $post_id );

				wp_localize_script( 'sl_admin', '_slGameSetup', $game_data );
			}
		}
	}


	/**
	 * Handles admin column display.
	 *
	 * @param array   $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 *
	 * @since  0.1.0
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {

			case 'game_tournament':
				$stage_id      = get_post_meta( $post_id, '_sl_stage_id', true );
				$tournament_id = get_post_meta( $post_id, '_sl_tournament_id', true );

				if ( ! $stage_id || ! $tournament_id ) {
					return;
				}

				echo '<span class="anwp-admin-tournament-icon"></span> <strong>' . esc_html( get_the_title( $tournament_id ) ) . '</strong><br>';

				// Stage title
				echo '<strong>' . esc_html__( 'Stage', 'sports-leagues' ) . ':</strong> ' . esc_html( get_the_title( $stage_id ) ) . '<br>';

				// Season
				$season_id      = (int) get_post_meta( $post_id, '_sl_season', true );
				$season_options = $this->plugin->season->get_season_options();

				if ( ! empty( $season_options[ $season_id ] ) ) {
					echo '<strong>' . esc_html__( 'Season', 'sports-leagues' ) . ':</strong> ' . esc_html( $season_options[ $season_id ] ) . '<br>';
				}

				if ( 'knockout' === get_post_meta( $stage_id, '_sl_stage_system', true ) ) {
					$round_id = get_post_meta( $post_id, '_sl_round_id', true ) ?: 1;

					if ( $round_id ) {
						$round_title = $this->plugin->tournament->get_round_title( $round_id, $stage_id );
						echo '<strong>' . esc_html__( 'Round', 'sports-leagues' ) . ' #' . intval( $round_id ) . ':</strong> ' . esc_html( $round_title ) . '<br>';
					}
				}

				// Game Day
				$game_day = get_post_meta( $post_id, '_sl_gameday', true );
				if ( $game_day ) {
					echo '<strong>' . esc_html__( 'Game Day', 'sports-leagues' ) . ':</strong> ' . esc_html( $game_day ) . '<br>';
				}

				break;

			case 'game_scores':
				// Initial values
				$home_score = '';
				$away_score = '';

				$home_outcome = '';
				$away_outcome = '';

				$is_finished   = 'finished' === get_post_meta( $post_id, '_sl_status', true );
				$teams_options = $this->plugin->team->get_team_options();

				if ( $is_finished ) :
					$home_score = get_post_meta( $post_id, '_sl_home_score', true );
					$away_score = get_post_meta( $post_id, '_sl_away_score', true );

					$scores_home = json_decode( get_post_meta( $post_id, '_sl_scores_home', true ) );
					$scores_away = json_decode( get_post_meta( $post_id, '_sl_scores_away', true ) );

					if ( ! empty( $scores_home ) && isset( $scores_home->outcome ) && trim( $scores_home->outcome ) ) {
						$home_outcome = $scores_home->outcome;
					}

					if ( ! empty( $scores_away ) && isset( $scores_away->outcome ) && trim( $scores_away->outcome ) ) {
						$away_outcome = $scores_away->outcome;
					}
				endif;
				?>
				<div class="anwp-b-wrap">
					<?php
					// Home Team
					$team_id = (int) get_post_meta( $post_id, '_sl_team_home', true );
					?>
					<div class="anwp-text-nowrap d-flex align-items-center">
						<span class="anwp-admin-table-scores"><?php echo esc_html( $is_finished ? $home_score : '-' ); ?></span>
						<div class="d-flex flex-column">
							<?php if ( ! empty( $teams_options[ $team_id ] ) ) : ?>
								<span class="anwp-admin-table-team"><?php echo esc_html( $teams_options[ $team_id ] ); ?></span>
							<?php endif; ?>
							<?php if ( $home_outcome ) : ?>
								<span class="anwp-admin-table-outcome"><?php echo esc_html( sports_leagues()->helper->get_outcome_option_by_slug( $home_outcome ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<?php
					// Away Team
					$team_id = (int) get_post_meta( $post_id, '_sl_team_away', true );
					?>
					<div class="anwp-text-nowrap d-flex align-items-center mt-2">
						<span class="anwp-admin-table-scores"><?php echo esc_html( $is_finished ? $away_score : '-' ); ?></span>

						<div class="d-flex flex-column">
							<?php if ( ! empty( $teams_options[ $team_id ] ) ) : ?>
								<span class="anwp-admin-table-team"><?php echo esc_html( $teams_options[ $team_id ] ); ?></span>
							<?php endif; ?>
							<?php if ( $away_outcome ) : ?>
								<span class="anwp-admin-table-outcome"><?php echo esc_html( sports_leagues()->helper->get_outcome_option_by_slug( $away_outcome ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( $is_finished && ( '' === $home_score || '' === $away_score || ! $home_outcome ) ) : ?>
						<div class="alert alert-warning py-1 px-2 mt-2 mb-0 d-flex align-items-center flex-wrap">
							<svg class="anwp-icon anwp-icon--warning mr-2">
								<use xlink:href="#icon-alert"></use>
							</svg>
							<?php echo esc_html__( 'Please fill final scores and outcomes for finished game!', 'sports-leagues' ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
				break;

			case 'game_datetime':
				$game_datetime = get_post_meta( $post_id, '_sl_datetime', true );

				if ( ! empty( $game_datetime ) ) {
					echo esc_html( date( 'M j, Y', strtotime( $game_datetime ) ) ) . '<br>' . esc_html( date( 'H:i', strtotime( $game_datetime ) ) );
				}
				break;

			case 'game_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'game_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 * @since  0.1.0
	 */
	public function columns( $columns ) {
		// Add new columns
		$new_columns = [
			'game_tournament' => esc_html__( 'Tournament', 'sports-leagues' ),
			'game_datetime'   => esc_html__( 'Kick Off', 'sports-leagues' ),
			'game_scores'     => esc_html__( 'Game', 'sports-leagues' ),
			'game_id'         => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'game_tournament',
			'game_datetime',
			'game_scores',
			'comments',
			'game_id',
		];

		$new_columns = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$new_columns[ $c ] = $columns[ $c ];
			}
		}

		return $new_columns;
	}


	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( $post_type ) {

		if ( 'sl_game' === $post_type ) {

			ob_start();
			/*
			|--------------------------------------------------------------------
			| Filter By Game State
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_status = empty( $_GET['_sl_current_status'] ) ? '' : sanitize_text_field( $_GET['_sl_current_status'] );
			?>
			<select name='_sl_current_status' id='anwp_status_filter' class='postform'>
				<option value=''><?php echo esc_html__( 'Status', 'sports-leagues' ); ?></option>
				<option value="finished" <?php selected( 'finished', $current_status ); ?>>- <?php echo esc_html__( 'finished', 'sports-leagues' ); ?></option>
				<option value="upcoming" <?php selected( 'upcoming', $current_status ); ?>>- <?php echo esc_html__( 'upcoming', 'sports-leagues' ); ?></option>
			</select>
			<?php
			// Leagues dropdown
			$leagues = get_terms(
				[
					'taxonomy'   => 'sl_league',
					'hide_empty' => false,
				]
			);

			if ( ! empty( $leagues ) && is_array( $leagues ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_league_filter = empty( $_GET['_sl_current_league'] ) ? '' : (int) $_GET['_sl_current_league'];
				?>

				<select name='_sl_current_league' id='anwp_league_filter' class='postform'>
					<option value=''><?php esc_html_e( 'All Leagues', 'sports-leagues' ); ?></option>
					<?php foreach ( $leagues as $league ) : ?>
						<option value="<?php echo esc_attr( $league->term_id ); ?>" <?php selected( $league->term_id, $current_league_filter ); ?>>
							<?php echo esc_html( $league->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			// Seasons dropdown
			$seasons = get_terms(
				[
					'taxonomy'   => 'sl_season',
					'hide_empty' => false,
				]
			);

			if ( ! empty( $seasons ) && is_array( $seasons ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_season_filter = empty( $_GET['_sl_current_season'] ) ? '' : (int) $_GET['_sl_current_season'];
				?>

				<select name='_sl_current_season' id='anwp_season_filter' class='postform'>
					<option value=''><?php esc_html_e( 'All Seasons', 'sports-leagues' ); ?></option>
					<?php foreach ( $seasons as $season ) : ?>
						<option value="<?php echo esc_attr( $season->term_id ); ?>" <?php selected( $season->term_id, $current_season_filter ); ?>>
							<?php echo esc_html( $season->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			/*
			|--------------------------------------------------------------------
			| Tournament ID
			|--------------------------------------------------------------------
			*/

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_stage_filter = empty( $_GET['_sl_tournament_id'] ) ? '' : (int) $_GET['_sl_tournament_id'];
			?>
			<input class='postform anwp-g-float-left anwp-g-admin-list-input anwp-w-120' name='_sl_tournament_id' type='text' value="<?php echo esc_attr( $current_stage_filter ); ?>"
				placeholder="<?php echo esc_attr__( 'Tournament ID', 'sports-leagues' ); ?>"/>

			<button type='button' class='button anwp-sl-selector anwp-sl-selector--visible anwp-mr-2 postform anwp-g-float-left'
				style='display: none;' data-context='tournament' data-single='yes'>
				<span class='dashicons dashicons-search'></span>
			</button>
			<?php

			/*
			|--------------------------------------------------------------------
			| Stage ID
			|--------------------------------------------------------------------
			*/

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_stage_filter = empty( $_GET['_sl_stage_id'] ) ? '' : (int) $_GET['_sl_stage_id'];
			?>
			<input class='postform anwp-g-float-left anwp-g-admin-list-input anwp-w-120' name='_sl_stage_id' type='text' value="<?php echo esc_attr( $current_stage_filter ); ?>"
				placeholder="<?php echo esc_attr__( 'Stage ID', 'sports-leagues' ); ?>"/>

			<button type='button' class='button anwp-sl-selector anwp-sl-selector--visible anwp-mr-2 postform anwp-g-float-left'
				style='display: none;' data-context='stage' data-single='yes'>
				<span class='dashicons dashicons-search'></span>
			</button>
			<?php

			// Teams dropdown
			$teams = $this->plugin->team->get_team_options();

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_team_filter = empty( $_GET['_sl_current_team'] ) ? '' : (int) $_GET['_sl_current_team'];
			?>
			<select name='_sl_current_team' id='anwp_team_filter' class='postform'>
				<option value=''><?php esc_html_e( 'All Teams', 'sports-leagues' ); ?></option>
				<?php foreach ( $teams as $team_id => $team_title ) : ?>
					<option value="<?php echo esc_attr( $team_id ); ?>" <?php selected( $team_id, $current_team_filter ); ?>>
						<?php echo esc_html( $team_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			/*
			|--------------------------------------------------------------------
			| Date From/To
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_from = empty( $_GET['_sl_date_from'] ) ? '' : sanitize_text_field( $_GET['_sl_date_from'] );
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_to = empty( $_GET['_sl_date_to'] ) ? '' : sanitize_text_field( $_GET['_sl_date_to'] );
			?>
			<input type="text" class="postform anwp-g-float-left anwp-g-admin-list-input" name="_sl_date_from"
				placeholder="<?php echo esc_attr__( 'Date From', 'sports-leagues' ); ?>" value="<?php echo esc_attr( $date_from ); ?>" />
			<input type="text" class="postform anwp-g-float-left anwp-g-admin-list-input" name="_sl_date_to"
				placeholder="<?php echo esc_attr__( 'Date To', 'sports-leagues' ); ?>" value="<?php echo esc_attr( $date_to ); ?>" />

			<?php

			/*
			|--------------------------------------------------------------------
			| Matchweek
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_gameday_filter = empty( $_GET['_sl_gameday'] ) ? '' : (int) $_GET['_sl_gameday'];
			?>
			<input class='postform anwp-g-float-left anwp-g-admin-list-input anwp-w-100' name='_sl_gameday' type='text' value="<?php echo esc_attr( $current_gameday_filter ); ?>"
				placeholder="<?php echo esc_attr__( 'Game Day', 'sports-leagues' ); ?>"/>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'edit.php' !== $pagenow || 'sl_game' !== $post_type ) {
			return;
		}

		$sub_query = [];

		/*
		|--------------------------------------------------------------------
		| Filter By Team
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_team = empty( $_GET['_sl_current_team'] ) ? '' : intval( $_GET['_sl_current_team'] );

		if ( $filter_by_team ) {
			$sub_query[] =
				[
					'relation' => 'OR',
					[
						'key'   => '_sl_team_home',
						'value' => $filter_by_team,
					],
					[
						'key'   => '_sl_team_away',
						'value' => $filter_by_team,
					],
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Season
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_season = empty( $_GET['_sl_current_season'] ) ? '' : intval( $_GET['_sl_current_season'] );

		if ( $filter_by_season ) {
			$sub_query[] =
				[
					'key'   => '_sl_season_id',
					'value' => $filter_by_season,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Tournament
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_tournament = empty( $_GET['_sl_tournament_id'] ) ? '' : intval( $_GET['_sl_tournament_id'] );

		if ( $filter_by_tournament ) {
			$sub_query[] =
				[
					'key'   => '_sl_tournament_id',
					'value' => $filter_by_tournament,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Stage
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_stage = empty( $_GET['_sl_stage_id'] ) ? '' : intval( $_GET['_sl_stage_id'] );

		if ( $filter_by_stage ) {
			$sub_query[] =
				[
					'key'   => '_sl_stage_id',
					'value' => $filter_by_stage,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Game Day
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_gameday = empty( $_GET['_sl_gameday'] ) ? '' : intval( $_GET['_sl_gameday'] );

		if ( $filter_by_gameday ) {
			$sub_query[] =
				[
					'key'   => '_sl_gameday',
					'value' => $filter_by_gameday,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By League
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_league = empty( $_GET['_sl_current_league'] ) ? '' : intval( $_GET['_sl_current_league'] );

		if ( $filter_by_league ) {
			$sub_query[] =
				[
					'key'   => '_sl_league_id',
					'value' => $filter_by_league,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Status
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_status = empty( $_GET['_sl_current_status'] ) ? '' : sanitize_text_field( $_GET['_sl_current_status'] );

		if ( $filter_by_status ) {
			$sub_query[] =
				[
					'key'   => '_sl_status',
					'value' => $filter_by_status,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Date From/To
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_date_from = empty( $_GET['_sl_date_from'] ) ? '' : sanitize_text_field( $_GET['_sl_date_from'] );

		if ( $filter_by_date_from ) {

			$sub_query[] =
				[
					'key'     => '_sl_datetime',
					'value'   => $filter_by_date_from . ' 00:00:00',
					'compare' => '>=',
				];
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_date_to = empty( $_GET['_sl_date_to'] ) ? '' : sanitize_text_field( $_GET['_sl_date_to'] );

		if ( $filter_by_date_to ) {

			$sub_query[] =
				[
					'key'     => '_sl_datetime',
					'value'   => $filter_by_date_to . ' 23:59:59',
					'compare' => '<=',
				];
		}

		if ( ! empty( $sub_query ) ) {
			$query->set(
				'meta_query',
				[
					array_merge( [ 'relation' => 'AND' ], $sub_query ),
				]
			);
		}
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.1.0
	 */
	public function init_cmb2_game_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		$cmb = new_cmb2_box(
			[
				'id'              => 'sl_game_cmb2_metabox',
				'object_types'    => [ 'sl_game' ],
				'context'         => 'advanced',
				'priority'        => 'high',
				'classes'         => [ 'anwp-b-wrap', 'anwp-cmb2-metabox' ],
				'save_button'     => '',
				'show_names'      => true,
				'remove_box_wrap' => true,
				'show_on'         => [ 'key' => 'sl_fixed' ],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Game Summary
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'            => esc_html__( 'Text 1', 'sports-leagues' ) . '<br>' . esc_html__( 'Game Summary', 'sports-leagues' ),
				'id'              => $prefix . 'summary',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'options'         => [
					'wpautop'       => true,
					'media_buttons' => true, // show insert/upload button(s)
					'textarea_name' => 'anwp_game_summary_input',
					'textarea_rows' => 5,
					'teeny'         => false, // output the minimal editor config used in Press This
					'dfw'           => false, // replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				],
				'before_row'      => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'note',
						'label' => __( 'Text Content', 'sports-leagues' ),
						'slug'  => 'anwp-sl-summary-metabox',
					]
				),
			]
		);

		$cmb->add_field(
			[
				'name'            => esc_html__( 'Text 2', 'sports-leagues' ) . '<br>' . esc_html__( 'Custom Content', 'sports-leagues' ),
				'id'              => $prefix . 'bottom_content',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'options'         => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_bottom_post_content',
					'textarea_rows' => 5,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'after_row'       => '</div></div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Video Review
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Video Source', 'sports-leagues' ),
				'id'         => $prefix . 'video_source',
				'type'       => 'select',
				'default'    => '',
				'options'    => [
					''        => esc_html__( '- select source -', 'sports-leagues' ),
					'site'    => esc_html__( 'Media Library', 'sports-leagues' ),
					'youtube' => esc_html__( 'Youtube', 'sports-leagues' ),
					'vimeo'   => esc_html__( 'Vimeo', 'sports-leagues' ),
				],
				'attributes' => [
					'data-name' => 'video_source',
					'class'     => 'anwp-parent-of-dependent',
				],
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'device-camera',
						'label' => __( 'Media', 'sports-leagues' ),
						'slug'  => 'anwp-sl-media-game-metabox',
					]
				),
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Video ID (or URL)', 'sports-leagues' ),
				'id'         => $prefix . 'video_id',
				'type'       => 'text',
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => __( 'for Youtube or Vimeo', 'sports-leagues' ),
			]
		);

		$cmb->add_field(
			[
				'name'         => esc_html__( 'Video File', 'sports-leagues' ),
				'id'           => $prefix . 'video_media_url',
				'type'         => 'file',
				'label_cb'     => [ $this->plugin, 'cmb2_field_label' ],
				'label_help'   => __( 'for Media Library', 'sports-leagues' ),
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => esc_html__( 'Open Media Library', 'sports-leagues' ),
				],
				'query_args'   => [
					'type' => 'video/mp4',
				],
				'preview_size' => 'large',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Video Description', 'sports-leagues' ),
				'id'   => $prefix . 'video_info',
				'type' => 'text',
			]
		);

		// Photo
		$cmb->add_field(
			[
				'name'         => esc_html__( 'Gallery', 'sports-leagues' ),
				'id'           => $prefix . 'gallery',
				'type'         => 'file_list',
				'options'      => [
					'url' => false, // Hide the text input for the url
				],
				// query_args are passed to wp.media's library query.
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium', // Image size to use when previewing in the admin.
			]
		);

		// Notes
		$cmb->add_field(
			[
				'name' => esc_html__( 'Text below gallery', 'sports-leagues' ),
				'id'   => $prefix . 'gallery_notes',
				'type' => 'textarea_small',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Additional Video
		|--------------------------------------------------------------------
		*/
		$group_field_id = $cmb->add_field(
			[
				'id'               => $prefix . 'additional_videos',
				'type'             => 'group',
				'after_group'      => '</div></div>',
				'classes'          => 'mt-0 pt-0',
				'before_group_row' => '<h4>' . esc_html__( 'Additional videos', 'sports-leagues' ) . '</h4>',
				'options'          => [
					'group_title'    => __( 'Additional Video', 'sports-leagues' ),
					'add_button'     => __( 'Add Another Video', 'sports-leagues' ),
					'remove_button'  => __( 'Remove Video', 'sports-leagues' ),
					'sortable'       => true,
					'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'    => esc_html__( 'Video Source', 'sports-leagues' ),
				'id'      => 'video_source',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''        => esc_html__( '- select source -', 'sports-leagues' ),
					'site'    => esc_html__( 'Media Library', 'sports-leagues' ),
					'youtube' => esc_html__( 'Youtube', 'sports-leagues' ),
					'vimeo'   => esc_html__( 'Vimeo', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'       => esc_html__( 'Video ID (or URL)', 'sports-leagues' ),
				'id'         => 'video_id',
				'type'       => 'text',
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => __( 'for Youtube or Vimeo', 'sports-leagues' ),
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'         => esc_html__( 'Video File', 'sports-leagues' ),
				'id'           => 'video_media_url',
				'type'         => 'file',
				'label_cb'     => [ $this->plugin, 'cmb2_field_label' ],
				'label_help'   => __( 'for Media Library', 'sports-leagues' ),
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => esc_html__( 'Open Media Library', 'sports-leagues' ),
				],
				'query_args'   => [
					'type' => 'video/mp4',
				],
				'preview_size' => 'large',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name' => esc_html__( 'Video Description', 'sports-leagues' ),
				'id'   => 'video_info',
				'type' => 'text',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.1.0
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/game', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Prepare untrashed notices.
	 *
	 * @param int $post_id
	 *
	 * @since 0.1.0
	 */
	public function on_game_untrashed( $post_id ) {
		if ( 'sl_game' === get_post_type( $post_id ) ) {
			$notice_text = esc_html__( 'Please re-save untrashed game to update statistics and events.', 'sports-leagues' );
			set_transient( 'anwp-admin-game-untrashed', $notice_text, 5 );
		}
	}

	/**
	 * Rendering untrashed notices.
	 *
	 * @since 0.1.0
	 */
	public function on_game_untrashed_notices() {
		if ( get_transient( 'anwp-admin-game-untrashed' ) ) :
			?>
			<div class="notice notice-info is-dismissible anwp-visible-after-header">
				<p><?php echo esc_html( get_transient( 'anwp-admin-game-untrashed' ) ); ?></p>
			</div>
			<?php
			delete_transient( 'anwp-admin-game-untrashed' );
		endif;
	}

	/**
	 * Fires post trashed.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 0.1.0
	 */
	public function on_game_trashed( $post_ID ) {

		// Check post type
		if ( 'sl_game' === get_post_type( $post_ID ) ) {

			// Remove game stats
			$this->remove_game_statistics( $post_ID );
		}
	}

	/**
	 * Fires after removing a post.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 0.6.1
	 */
	public function on_game_deleted( $post_ID ) {

		// Check post type
		if ( 'sl_game' === get_post_type( $post_ID ) ) {

			// Remove game stats
			$this->remove_game_statistics( $post_ID );

			// Remove missing players
			$this->remove_game_missing_players( $post_ID );

			// Remove game events
			$this->plugin->event->remove_game_events( $post_ID );

			// Remove game player stats
			$this->plugin->player_stats->remove_game_player_stats( $post_ID );
		}
	}

	/**
	 * Removes game statistics from DB.
	 *
	 * @param int $game_id
	 *
	 * @since 0.1.0
	 */
	private function remove_game_statistics( $game_id ) {

		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'sl_games', [ 'game_id' => (int) $game_id ] );

		// Recalculate standing
		$this->plugin->standing->calculate_standing_prepare( $game_id );
	}

	/**
	 * Method saves game statistics into DB.
	 *
	 * @param array $data - Array of data to save
	 *
	 * @return int|false The number of rows affected, or false on error.
	 * @since 0.1.0
	 */
	public function save_game_statistics( $data ) {

		global $wpdb;

		if ( ! isset( $data['game_id'] ) || ! intval( $data['game_id'] ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'sl_games';

		$game_data = [
			'game_id'           => intval( $data['game_id'] ),
			'tournament_id'     => isset( $data['tournament_id'] ) ? intval( $data['tournament_id'] ) : 0,
			'stage_id'          => isset( $data['stage_id'] ) ? intval( $data['stage_id'] ) : 0,
			'league_id'         => isset( $data['league_id'] ) ? intval( $data['league_id'] ) : 0,
			'season_id'         => isset( $data['season_id'] ) ? intval( $data['season_id'] ) : 0,
			'group_id'          => isset( $data['group_id'] ) ? intval( $data['group_id'] ) : 1,
			'round_id'          => isset( $data['round_id'] ) ? intval( $data['round_id'] ) : 1,
			'home_team'         => isset( $data['team_home'] ) ? intval( $data['team_home'] ) : 0,
			'away_team'         => isset( $data['team_away'] ) ? intval( $data['team_away'] ) : 0,
			'kickoff'           => isset( $data['datetime'] ) ? sanitize_text_field( $data['datetime'] ) : '0000-00-00 00:00:00',
			'kickoff_gmt'       => isset( $data['datetime_gmt'] ) ? sanitize_text_field( $data['datetime_gmt'] ) : '0000-00-00 00:00:00',
			'special_status'    => isset( $data['special_status'] ) ? $data['special_status'] : '',
			'venue_id'          => isset( $data['venue_id'] ) ? intval( $data['venue_id'] ) : 0,
			'game_day'          => isset( $data['gameday'] ) ? intval( $data['gameday'] ) : 0,
			'status'            => isset( $data['stage_status'] ) ? sanitize_text_field( $data['stage_status'] ) : '',
			'finished'          => ( isset( $data['status'] ) && 'finished' === $data['status'] ) ? 1 : 0,
			'priority'          => isset( $data['priority'] ) ? intval( $data['priority'] ) : 0,
			'home_scores'       => isset( $data['home_scores'] ) ? sanitize_text_field( $data['home_scores'] ) : '',
			'away_scores'       => isset( $data['away_scores'] ) ? sanitize_text_field( $data['away_scores'] ) : '',
			'home_outcome'      => isset( $data['home_outcome'] ) ? sanitize_text_field( $data['home_outcome'] ) : '',
			'away_outcome'      => isset( $data['away_outcome'] ) ? sanitize_text_field( $data['away_outcome'] ) : '',
			'home_points'       => isset( $data['home_pts'] ) ? intval( $data['home_pts'] ) : 0,
			'away_points'       => isset( $data['away_pts'] ) ? intval( $data['away_pts'] ) : 0,
			'home_bonus_points' => isset( $data['home_bpts'] ) ? intval( $data['home_bpts'] ) : 0,
			'away_bonus_points' => isset( $data['away_bpts'] ) ? intval( $data['away_bpts'] ) : 0,
		];

		/**
		 * Filter saving game data.
		 *
		 * @param array $data Game data
		 *
		 * @since 0.1.0
		 */
		$game_data = apply_filters( 'sports-leagues/game/data_save_db', $game_data );

		$result = $wpdb->replace( $table, $game_data );

		return $result;
	}

	/**
	 * Get game data.
	 *
	 * @param $game_id
	 *
	 * @return object|bool
	 * @since 0.1.0
	 */
	public function get_game_data( $game_id ) {
		global $wpdb;

		static $games = [];

		if ( ! empty( $games[ $game_id ] ) ) {
			return $games[ $game_id ];
		}

		$games[ $game_id ] = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM {$wpdb->prefix}sl_games
				WHERE game_id = %d
				",
				$game_id
			)
		);

		return $games[ $game_id ];
	}

	/**
	 * Get game data.
	 *
	 * @param object $game_data
	 * @param array  $args
	 *
	 * @return object
	 * @since 0.1.0
	 */
	public function prepare_tmpl_game_data( $game_data, $args = [] ) {

		$args = wp_parse_args(
			$args,
			[
				'show_team_logo'     => 1,
				'show_game_datetime' => 1,
				'show_team_name'     => 1,
				'team_links'         => 0,
				'tournament_logo'    => 1,
			]
		);

		$team_home = sports_leagues()->team->get_team_by_id( $game_data->home_team );
		$team_away = sports_leagues()->team->get_team_by_id( $game_data->away_team );

		if ( empty( $team_home ) || empty( $team_away ) ) {
			return $game_data;
		}

		/*
		|--------------------------------------------------------------------
		| Date and time formats
		|--------------------------------------------------------------------
		*/
		$custom_date_format = sports_leagues()->get_option_value( 'custom_game_date_format' ) ?: 'j M Y';
		$custom_time_format = sports_leagues()->get_option_value( 'custom_game_time_format' ) ?: get_option( 'time_format' );

		$game_data->game_date = date_i18n( $custom_date_format, get_date_from_gmt( $game_data->kickoff, 'U' ) );
		$game_data->game_time = date_i18n( $custom_time_format, get_date_from_gmt( $game_data->kickoff, 'U' ) );
		$game_data->kickoff_c = date_i18n( 'c', strtotime( $game_data->kickoff ) );

		/*
		|--------------------------------------------------------------------
		| Attach args
		|--------------------------------------------------------------------
		*/
		$game_data->show_team_name     = Sports_Leagues::string_to_bool( $args['show_team_name'] );
		$game_data->show_team_logo     = Sports_Leagues::string_to_bool( $args['show_team_logo'] );
		$game_data->show_game_datetime = Sports_Leagues::string_to_bool( $args['show_game_datetime'] );
		$game_data->team_links         = Sports_Leagues::string_to_bool( $args['team_links'] );
		$game_data->tournament_logo    = Sports_Leagues::string_to_bool( $args['tournament_logo'] );
		$game_data->outcome_id         = isset( $args['outcome_id'] ) ? $args['outcome_id'] : '';

		/*
		|--------------------------------------------------------------------
		| Logos
		|--------------------------------------------------------------------
		*/
		$game_data->home_logo = $game_data->show_team_logo ? $team_home->logo : '';
		$game_data->away_logo = $game_data->show_team_logo ? $team_away->logo : '';

		/*
		|--------------------------------------------------------------------
		| Titles
		|--------------------------------------------------------------------
		*/
		$game_data->home_title = $team_home->title;
		$game_data->away_title = $team_away->title;
		$game_data->home_abbr  = $team_home->abbr ?: $team_home->title;
		$game_data->away_abbr  = $team_away->abbr ?: $team_away->title;
		$game_data->home_code  = $team_home->code ?: $team_home->abbr;
		$game_data->away_code  = $team_away->code ?: $team_away->abbr;

		/*
		|--------------------------------------------------------------------
		| Links
		|--------------------------------------------------------------------
		*/
		$game_data->home_link = $team_home->link;
		$game_data->away_link = $team_away->link;

		return $game_data;
	}

	/**
	 * Prepare Players array for edit game form.
	 *
	 * @param array $players
	 *
	 * @return array
	 * @since 0.5.3
	 */
	protected function prepare_players_for_game_edit( $players ) {

		/*
		|--------------------------------------------------------------------
		| Sorting Players
		|--------------------------------------------------------------------
		*/
		$sorting = Sports_Leagues_Options::get_value( 'players_dropdown_sorting', 'number' );

		if ( in_array( $sorting, [ 'number', 'name' ], true ) ) {
			$players = wp_list_sort( $players, $sorting );
		}

		return $players;
	}

	/**
	 * Get scores for game periods.
	 *
	 * @param int    $game_id
	 * @param object $game_data
	 * @param string $output_type
	 *
	 * @return string
	 * @since 0.5.6
	 */
	public function get_scores_for_periods( $game_id, $game_data, $output_type = 'line' ) {
		$output = '';

		$scores_home = json_decode( get_post_meta( $game_id, '_sl_scores_home', true ) );
		$scores_away = json_decode( get_post_meta( $game_id, '_sl_scores_away', true ) );

		$num_of_periods   = (int) Sports_Leagues_Config::get_value( 'num_of_periods', '3' );
		$num_of_overtimes = $this->get_number_of_game_overtimes( $game_id, $scores_home, $scores_away );

		if ( empty( $scores_home ) || empty( $scores_away ) ) {
			return $output;
		}

		/*
		|--------------------------------------------------------------------
		| Table Output
		|--------------------------------------------------------------------
		*/
		if ( 'table' === $output_type ) {
			$table_data = [
				'home' => [],
				'away' => [],
			];

			for ( $p = 1; $p <= $num_of_periods; $p ++ ) {

				$prop = 'p' . $p;

				if ( isset( $scores_home->{$prop} ) && isset( $scores_away->{$prop} ) && ( '' !== $scores_away->{$prop} || '' !== $scores_home->{$prop} ) ) {
					$table_data['home'][] = $scores_home->{$prop};
					$table_data['away'][] = $scores_away->{$prop};
				}
			}

			ob_start();
			?>
			<table class="table table-bordered table-sm w-auto mx-auto anwp-border-0">
				<tr>
					<td class="pr-3"><?php echo esc_html( isset( $game_data->home_abbr ) ? $game_data->home_abbr : '' ); ?></td>
					<?php foreach ( $table_data['home'] as $home_scores ) : ?>
						<td><?php echo esc_html( $home_scores ); ?></td>
					<?php endforeach; ?>
					<td class="anwp-bg-gray-300"><?php echo esc_html( isset( $scores_home->final ) ? $scores_home->final : '' ); ?></td>
				</tr>
				<tr>
					<td class="pr-3"><?php echo esc_html( isset( $game_data->away_abbr ) ? $game_data->away_abbr : '' ); ?></td>
					<?php foreach ( $table_data['away'] as $away_scores ) : ?>
						<td><?php echo esc_html( $away_scores ); ?></td>
					<?php endforeach; ?>
					<td class="anwp-bg-gray-300"><?php echo esc_html( isset( $scores_away->final ) ? $scores_away->final : '' ); ?></td>
				</tr>
			</table>
			<?php
			return ob_get_clean();
		}

		/*
		|--------------------------------------------------------------------
		| Line Output
		|--------------------------------------------------------------------
		*/
		for ( $p = 1; $p <= $num_of_periods; $p ++ ) {

			$prop = 'p' . $p;
			if ( isset( $scores_home->{$prop} ) && '' !== $scores_home->{$prop} && isset( $scores_away->{$prop} ) && '' !== $scores_away->{$prop} ) {
				$output .= ' ' . $scores_home->{$prop} . '-' . $scores_away->{$prop} . ';';
			}
		}

		for ( $o = 1; $o <= $num_of_overtimes; $o ++ ) {

			$prop = 'ov' . $o;
			if ( isset( $scores_home->{$prop} ) && '' !== $scores_home->{$prop} && isset( $scores_away->{$prop} ) && '' !== $scores_away->{$prop} ) {
				$output .= ' ' . $scores_home->{$prop} . '-' . $scores_away->{$prop} . ';';
			}
		}

		return rtrim( $output, '; ' );

	}

	/**
	 * Generate Match title
	 *
	 * @param array  $data
	 * @param string $home_team
	 * @param string $away_team
	 *
	 * @return string
	 * @since 0.6.4
	 */
	public function get_game_title_generated( $data, $home_team, $away_team ) {

		$data = wp_parse_args(
			$data,
			[
				'status'        => '',
				'home_scores'   => '',
				'away_scores'   => '',
				'tournament_id' => '',
				'datetime'      => '',
			]
		);

		// %team_home% - %team_away% - %scores_home% - %scores_away% - %tournament% - %kickoff%
		$game_title = trim( Sports_Leagues_Options::get_value( 'game_title_generator' ) );

		if ( false !== mb_strpos( $game_title, '%team_a%' ) ) {
			$game_title = str_ireplace( '%team_a%', $home_team, $game_title );
		}

		if ( false !== mb_strpos( $game_title, '%team_b%' ) ) {
			$game_title = str_ireplace( '%team_b%', $away_team, $game_title );
		}

		if ( false !== mb_strpos( $game_title, '%scores_a%' ) || false !== mb_strpos( $game_title, '%scores_b%' ) ) {
			$scores_home = '?';
			$scores_away = '?';

			if ( 'finished' === $data['status'] ) {
				$scores_home = $data['home_scores'];
				$scores_away = $data['away_scores'];
			}

			$game_title = str_ireplace( '%scores_a%', $scores_home, $game_title );
			$game_title = str_ireplace( '%scores_b%', $scores_away, $game_title );
		}

		if ( false !== mb_strpos( $game_title, '%tournament%' ) ) {
			$tournament_title = get_the_title( $data['tournament_id'] );
			$game_title       = str_ireplace( '%tournament%', $tournament_title, $game_title );
		}

		if ( false !== mb_strpos( $game_title, '%kickoff%' ) ) {
			$custom_date_format = sports_leagues()->get_option_value( 'custom_game_date_format' );
			$game_title         = str_ireplace( '%kickoff%', date_i18n( $custom_date_format ?: 'Y-m-d', get_date_from_gmt( $data['datetime'], 'U' ) ), $game_title );
		}

		return sanitize_text_field( $game_title );
	}

	/**
	 * Get all Games with video
	 *
	 * @return array
	 * @since 0.8.0
	 */
	public function get_games_with_video() {

		static $ids = null;

		if ( null === $ids ) {
			global $wpdb;

			$ids = $wpdb->get_col(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_video_source' AND meta_value != ''
				"
			);

			$ids = array_unique( array_map( 'absint', $ids ) );
		}

		return $ids;
	}

	/**
	 * Get number of game overtimes.
	 *
	 * @param $game_id
	 * @param $home_scores
	 * @param $away_scores
	 *
	 * @return int
	 * @since 0.8.0
	 */
	public function get_number_of_game_overtimes( $game_id, $home_scores, $away_scores ) {
		$game_status = get_post_meta( $game_id, '_sl_status', true );

		if ( 'finished' !== $game_status ) {
			return 0;
		}

		$ov_numbers = [ 0 ];

		foreach ( [ $home_scores, $away_scores ] as $score_group ) {
			if ( ! empty( $score_group ) && is_object( $score_group ) ) {
				foreach ( $score_group as $key => $value ) {
					if ( 0 === strpos( $key, 'ov' ) && '' !== $value ) {
						$number = ltrim( $key, 'ov' );

						if ( absint( $number ) ) {
							$ov_numbers[] = absint( $number );
						}
					}
				}
			}
		}

		return max( $ov_numbers );
	}

	/**
	 * Save Missing Players
	 *
	 * @param array $data
	 * @param int   $game_id
	 *
	 * @return bool
	 * @since 0.9.1
	 */
	public function save_missing_players( $data, $game_id ) {

		global $wpdb;

		if ( ! absint( $game_id ) ) {
			return false;
		}

		$this->remove_game_missing_players( $game_id );

		/*
		|--------------------------------------------------------------------
		| Prepare data for save
		|--------------------------------------------------------------------
		*/
		$table = $wpdb->prefix . 'sl_missing_players';

		foreach ( $data as $missing_player ) {

			if ( ! absint( $missing_player->player ) ) {
				continue;
			}

			// Prepare data to insert
			$data = [
				'reason'    => $missing_player->reason,
				'game_id'   => $game_id,
				'team_id'   => absint( $missing_player->team ),
				'player_id' => absint( $missing_player->player ),
				'comment'   => sanitize_textarea_field( $missing_player->comment ),
			];

			// Insert data to DB
			$wpdb->insert( $table, $data );
		}

		return true;
	}

	/**
	 * Remove game missing players.
	 *
	 * @param $game_id
	 *
	 * @return bool
	 * @since 0.9.1
	 */
	public function remove_game_missing_players( $game_id ) {
		global $wpdb;

		if ( ! absint( $game_id ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'sl_missing_players';

		return $wpdb->delete( $table, [ 'game_id' => absint( $game_id ) ] );
	}

	/**
	 * Get Missed games by player ID and season ID.
	 *
	 * @param $player_id
	 * @param $season_id
	 *
	 * @return array
	 * @since 0.9.1
	 */
	public function get_player_missed_games_by_season( $player_id, $season_id ) {
		global $wpdb;

		if ( ! absint( $player_id ) || ! absint( $season_id ) ) {
			return [];
		}

		// Get games with custom outcome
		$query = "
		SELECT p.game_id, p.player_id, p.team_id, p.reason, p.comment, m.kickoff, m.tournament_id, m.stage_id, m.home_team, m.away_team, m.home_scores, m.away_scores
		FROM {$wpdb->prefix}sl_missing_players p
		LEFT JOIN {$wpdb->prefix}sl_games m ON p.game_id = m.game_id
		";

		$query .= $wpdb->prepare( ' WHERE m.season_id = %d AND p.player_id = %d', $season_id, $player_id );
		$query .= ' ORDER BY m.kickoff DESC';

		$matches = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $matches ) ) {
			return [];
		}

		return $matches;
	}

	/**
	 * Get game outcome label
	 *
	 * @param object $data
	 * @param string $class
	 *
	 * @return string
	 * @since 0.9.2
	 */
	public function get_game_outcome_label( $data, $class = '' ) {

		// Merge with default params
		$data = (object) wp_parse_args(
			$data,
			[
				'outcome_id'   => '',
				'home_team'    => '',
				'away_team'    => '',
				'home_outcome' => '',
				'away_outcome' => '',
			]
		);

		$outcome_id = absint( $data->outcome_id );
		$home_id    = absint( $data->home_team );
		$away_id    = absint( $data->away_team );

		if ( ! absint( $data->finished ) || ( $outcome_id !== $home_id && $outcome_id !== $away_id ) ) {
			return '<span class="anwp-sl-outcome-label anwp-w-30 anwp-h-30 ' . esc_attr( $class ) . '"></span>';
		}

		$series_map = [
			'w' => Sports_Leagues_Config::get_value( 'team_series_w', 'w' ),
			'd' => Sports_Leagues_Config::get_value( 'team_series_d', 'd' ),
			'l' => Sports_Leagues_Config::get_value( 'team_series_l', 'l' ),
		];

		$outcome = $outcome_id === $home_id ? $data->home_outcome : $data->away_outcome;

		switch ( $outcome ) {
			case 'draw':
				$outcome_label = $series_map['d'];
				$outcome_class = 'anwp-bg-warning';
				break;

			case 'pen_loss':
			case 'ov_loss':
			case 'ft_loss':
				$outcome_label = $series_map['l'];
				$outcome_class = 'anwp-bg-danger';
				break;

			default:
				$outcome_label = $series_map['w'];
				$outcome_class = 'anwp-bg-success';
		}

		ob_start();
		?>
		<span class="anwp-sl-outcome-label anwp-w-30 anwp-h-30 text-uppercase anwp-opacity-90 text-monospace text-white <?php echo esc_attr( $outcome_class . ' ' . $class ); ?>">
			<?php echo esc_html( $outcome_label ); ?>
		</span>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get games links
	 *
	 * @param array $ids
	 *
	 * @return array
	 * @since 0.9.3
	 */
	public function get_permalinks_by_ids( $ids ) {

		$ids = wp_parse_id_list( $ids );

		$args = [
			'include'                => $ids,
			'post_type'              => 'sl_game',
			'update_post_meta_cache' => false,
		];

		$output = [];

		/** @var WP_Post $game_post */
		foreach ( get_posts( $args ) as $game_post ) {
			$output[ $game_post->ID ] = get_permalink( $game_post );
		}

		return $output;
	}

	/**
	 * Render period scores
	 *
	 * @param object $game_id
	 *
	 * @return string
	 * @since 0.9.5
	 */
	public function render_period_scores( $game_data ) {

		$output_type = Sports_Leagues_Customizer::get_value( 'game', 'game_period_scores' );

		if ( 'hide' === $output_type ) {
			return '';
		}

		$scores_for_period = sports_leagues()->game->get_scores_for_periods( $game_data->game_id, $game_data, $output_type );

		if ( empty( $scores_for_period ) ) {
			return '';
		}

		return '<div class="game-header__period_scores anwp-text-center mt-2">' . $scores_for_period . '</div>';
	}

	/**
	 * Game Card tooltip Output
	 *
	 * @since 0.9.5
	 */
	public function get_game_card() {

		if ( apply_filters( 'sports-leagues/config/check_public_nonce', false ) ) {
			check_ajax_referer( 'sl-public-nonce' );
		}

		$game_id = intval( $_POST['game_id'] );

		if ( ! $game_id ) {
			wp_send_json_error();
		}

		$game = sports_leagues()->game->get_game_data( $game_id );

		if ( empty( $game ) ) {
			wp_send_json_error();
		}

		$game->permalink = get_permalink( $game_id );

		ob_start();

		$tmpl_data = sports_leagues()->game->prepare_tmpl_game_data( $game, [] );
		sports_leagues()->load_partial( $tmpl_data, 'game/game', 'card-a' );

		$html_output = ob_get_clean();

		wp_send_json_success( [ 'html' => $html_output ] );
	}

	/**
	 * Get aggregate texts for the selected game.
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function get_game_aggtext( $game_id ) {

		static $all_agg_texts = null;

		if ( null === $all_agg_texts ) {

			global $wpdb;

			$all_agg_texts = $wpdb->get_results(
				"
					SELECT post_id, meta_value
					FROM $wpdb->postmeta
					WHERE meta_key = '_sl_aggtext' AND meta_value != ''
				",
				OBJECT_K
			);
		}

		return isset( $all_agg_texts[ $game_id ]->meta_value ) ? $all_agg_texts[ $game_id ]->meta_value : '';
	}

	/**
	 * Get prepared list of temporary players
	 *
	 * @param $game_id
	 *
	 * @return array
	 */
	public function get_temp_players( $game_id ) {

		$temp_players = get_post_meta( $game_id, '_sl_temp_players', true ) ? json_decode( get_post_meta( $game_id, '_sl_temp_players', true ) ) : [];

		if ( empty( $temp_players ) ) {
			return [];
		}

		$output = [];

		foreach ( $temp_players as $temp_player ) {
			$output[ $temp_player->id ] = $temp_player;
		}

		return $output;
	}

	/**
	 * Get prepared list of temporary staff
	 *
	 * @param $game_id
	 *
	 * @return array
	 */
	public function get_temp_staff( $game_id ) {

		$temp_staffs = get_post_meta( $game_id, '_sl_temp_staffs', true ) ? json_decode( get_post_meta( $game_id, '_sl_temp_staffs', true ) ) : [];

		if ( empty( $temp_staffs ) ) {
			return [];
		}

		$output = [];

		foreach ( $temp_staffs as $temp_staff ) {
			$output[ $temp_staff->id ] = $temp_staff;
		}

		return $output;
	}

	/**
	 * Get prepared list of temporary officials
	 *
	 * @param $game_id
	 *
	 * @return array
	 */
	public function get_temp_officials( $game_id ) {

		$temp_officials = get_post_meta( $game_id, '_sl_temp_officials', true ) ? json_decode( get_post_meta( $game_id, '_sl_temp_officials', true ) ) : [];

		if ( empty( $temp_officials ) ) {
			return [];
		}

		$output = [];

		foreach ( $temp_officials as $temp_official ) {
			$output[ $temp_official->id ] = $temp_official;
		}

		return $output;
	}

	/**
	 * Get "load more" data
	 *
	 * @param $data
	 *
	 * @return string
	 * @since 0.12.0
	 */
	public function get_serialized_load_more_data( $data ) {

		$default_data = [
			'tournament_id'      => '',
			'stage_id'           => '',
			'season_id'          => '',
			'league_id'          => '',
			'group_id'           => '',
			'round_id'           => '',
			'venue_id'           => '',
			'date_from'          => '',
			'date_to'            => '',
			'finished'           => '',
			'filter_by_team'     => '',
			'h2h'                => '',
			'filter_by_game_day' => '',
			'days_offset'        => '',
			'days_offset_to'     => '',
			'priority'           => '',
			'sort_by_date'       => '',
			'sort_by_game_day'   => '',
			'limit'              => '',
			'kickoff_before'     => '',
			'exclude_ids'        => '',
			'include_ids'        => '',
			'team_a'             => '',
			'team_b'             => '',
			'offset'             => '',
			'header_style'       => 'header',
			'header_class'       => '',
			'group_by'           => '',
		];

		$options = wp_parse_args( $data, $default_data );
		$output  = array_intersect_key( $options, $default_data );

		// Replace null with empty string
		$output = array_map(
			function ( $e ) {
				return is_null( $e ) ? '' : $e;
			},
			$output
		);

		return wp_json_encode( $output );
	}

	/**
	 * Handle ajax request and provide posts to load.
	 *
	 * @since 0.12.0
	 */
	public function load_more_games() {

		// Activate referer check with hook (optional)
		if ( apply_filters( 'anwp-pg-el/config/check_public_nonce', false ) ) {
			check_ajax_referer( 'anwp-pg-public-nonce' );
		}

		$post_loaded = absint( $_POST['loaded'] );
		$post_qty    = absint( $_POST['qty'] );

		// Parse with default values
		$args = wp_parse_args(
			wp_unslash( $_POST['args'] ),
			[
				'tournament_id'      => '',
				'stage_id'           => '',
				'season_id'          => '',
				'league_id'          => '',
				'group_id'           => '',
				'round_id'           => '',
				'venue_id'           => '',
				'date_from'          => '',
				'date_to'            => '',
				'finished'           => '',
				'filter_by_team'     => '',
				'h2h'                => '',
				'filter_by_game_day' => '',
				'days_offset'        => '',
				'days_offset_to'     => '',
				'priority'           => '',
				'sort_by_date'       => '',
				'sort_by_game_day'   => '',
				'limit'              => '',
				'kickoff_before'     => '',
				'exclude_ids'        => '',
				'include_ids'        => '',
				'team_a'             => '',
				'team_b'             => '',
				'header_style'       => 'header',
				'header_class'       => '',
				'group_by'           => '',
			]
		);

		$data = [];

		foreach ( $args as $arg_key => $arg_value ) {
			$data[ $arg_key ] = sanitize_text_field( $arg_value );
		}

		$data['limit']  = $post_qty + 1;
		$data['offset'] = $post_loaded;

		// Get games
		$games = sports_leagues()->game->get_games_extended( $data );

		// Check next time "load more"
		$next_load = count( $games ) > $post_qty;

		if ( $next_load ) {
			array_pop( $games );
		}

		$group_current = isset( $_POST['group'] ) ? sanitize_text_field( $_POST['group'] ) : '';

		// Start output
		ob_start();

		foreach ( $games as $ii => $game ) {

			if ( '' !== $data['group_by'] ) {
				$group_text = '';

				/*
				|--------------------------------------------------------------------
				| Group Options
				|--------------------------------------------------------------------
				*/
				if ( 'round_stage' === $data['group_by'] && ( $game->round_id . '_' . $game->stage_id ) !== $group_current ) {
					/*
					|--------------------------------------------------------------------
					| Round >> Stage
					|--------------------------------------------------------------------
					*/
					$tournament_obj = sports_leagues()->tournament->get_tournament( $game->tournament_id );
					$group_current  = $game->round_id . '_' . $game->stage_id;
					$group_text_arr = [];

					$stage_obj = array_values( wp_list_filter( $tournament_obj->stages, [ 'id' => $game->stage_id ] ) )[0];

					if ( $stage_obj ) {
						$group_text_arr[] = $stage_obj->title;

						$rounds_obj = array_values( wp_list_filter( $stage_obj->rounds, [ 'id' => $game->round_id ] ) )[0];

						if ( $rounds_obj && $rounds_obj->title && __( 'Round Title', 'sports-leagues' ) !== $rounds_obj->title && $rounds_obj->title !== $stage_obj->title ) {
							array_unshift( $group_text_arr, $rounds_obj->title );
						}
					}

					if ( ! empty( $group_text_arr ) ) {
						$group_text = implode( ' - ', $group_text_arr );
					}
				} elseif ( 'stage' === $data['group_by'] && $group_current !== $game->stage_id && intval( $game->stage_id ) ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Stage
					|--------------------------------------------------------------------
					*/
					$stage_post = get_post( $game->stage_id );

					if ( $stage_post ) {
						$group_text    = $stage_post->post_title;
						$group_current = $game->stage_id;
					}
				} elseif ( in_array( $data['group_by'], [ 'game_day', 'gameday' ], true ) && $group_current !== $game->game_day && intval( $game->game_day ) ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Game Day
					|--------------------------------------------------------------------
					*/
					$group_text    = esc_html( Sports_Leagues_Text::get_value( 'shortcode__games__game_day', __( 'Game Day', 'sports-leagues' ) ) ) . ': ' . esc_html( $game->game_day );
					$group_current = $game->game_day;
				} elseif ( 'day' === $data['group_by'] ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Day
					|--------------------------------------------------------------------
					*/
					$day_to_compare = date( 'Y-m-d', strtotime( $game->kickoff ) );

					if ( $day_to_compare !== $group_current ) {
						$group_text    = date( 'j M Y', strtotime( $game->kickoff ) );
						$group_current = $day_to_compare;
					}
				} elseif ( 'month' === $data['group_by'] ) {
					/*
					|--------------------------------------------------------------------
					| Group >> Month
					|--------------------------------------------------------------------
					*/
					$month_to_compare = date( 'Y-m', strtotime( $game->kickoff ) );

					if ( $month_to_compare !== $group_current ) {
						$group_text    = date( 'M Y', strtotime( $game->kickoff ) );
						$group_current = $month_to_compare;
					}
				}

				if ( $group_text ) {
					sports_leagues()->load_partial(
						[
							'text'  => $group_text,
							'class' => $data['header_class'],
						],
						'general/' . sanitize_key( $data['header_style'] )
					);
				}
			}

			$game_data = sports_leagues()->game->prepare_tmpl_game_data( $game, $data );

			sports_leagues()->load_partial( $game_data, 'game/game', 'slim' );
		}

		$html_output = ob_get_clean();

		wp_send_json_success(
			[
				'html'   => $html_output,
				'next'   => $next_load,
				'group'  => $group_current,
				'offset' => $post_loaded + count( $games ),
			]
		);
	}
}
