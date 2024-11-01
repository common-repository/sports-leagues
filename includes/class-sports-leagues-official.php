<?php
/**
 * Sports Leagues :: Official
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Official {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Type.
	 *
	 * @since  0.1.0
	 * @param  Sports_Leagues $plugin Main plugin object.
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
		$permalink_slug      = empty( $permalink_structure['official'] ) ? 'official' : $permalink_structure['official'];

		// Register this CPT.
		$labels = [
			'name'                  => _x( 'Officials', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Official', 'Post type singular name', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Official', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'Add New Official', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Official', 'sports-leagues' ),
			'new_item'              => __( 'New Official', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Official', 'sports-leagues' ),
			'view_item'             => __( 'View Official', 'sports-leagues' ),
			'all_items'             => __( 'All Officials', 'sports-leagues' ),
			'search_items'          => __( 'Search Officials', 'sports-leagues' ),
			'not_found'             => __( 'No Officials found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No Officials found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Official photo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set Official photo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove Official photo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as Official photo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=sl_player',
			'query_var'          => true,
			'rewrite'            => [ 'slug' => $permalink_slug ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title', 'thumbnail', 'comments' ],
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

		register_post_type( 'sl_official', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Add tabs functionality for metabox
		add_action( 'cmb2_before_post_form_sl_official_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_official_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Filters the title field placeholder text.
		add_filter( 'enter_title_here', [ $this, 'title' ], 10, 2 );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-official/after_wrapper',
			function ( $person_id ) {

				$content_below = get_post_meta( $person_id, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Modifies columns in Admin tables
		add_action( 'manage_sl_official_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_official_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_official_sortable_columns', [ $this, 'sortable_columns' ] );
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.5.13
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_official_metabox',
				'title'        => esc_html__( 'Official Data', 'sports-leagues' ),
				'object_types' => [ 'sl_official' ],
				'context'      => 'normal',
				'classes'      => [ 'anwp-b-wrap' ],
				'priority'     => 'high',
				'show_names'   => true,
			]
		);

		// Short Name
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Short Name', 'sports-leagues' ),
				'id'         => $prefix . 'short_name',
				'type'       => 'text',
				'before_row' => '<div id="anwp-tabs-general-sl_official_metabox" class="anwp-metabox-tabs__content-item">',
			]
		);

		// Position
		$cmb->add_field(
			[
				'name'             => esc_html__( 'Group', 'sports-leagues' ),
				'id'               => $prefix . 'group',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this, 'get_official_groups' ],
			]
		);

		// Place of Birth
		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'sports-leagues' ),
				'id'   => $prefix . 'place_of_birth',
				'type' => 'text',
			]
		);

		// Date of Birth
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of Birth', 'sports-leagues' ),
				'id'          => $prefix . 'date_of_birth',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		// Nationality
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Nationality', 'sports-leagues' ),
				'id'         => $prefix . 'nationality',
				'type'       => 'anwp_sl_multiselect',
				'options_cb' => [ $this->plugin->data, 'get_countries' ],
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'sports-leagues' ),
				'id'          => $prefix . 'official_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'sports-leagues' ),
				'after_row'   => '</div>',
			]
		);

		// Player Bio
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Bio', 'sports-leagues' ),
				'id'         => $prefix . 'bio',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_official_bio_input',
					'textarea_rows' => 10,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '<div id="anwp-tabs-bio-sl_official_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		// Custom Fields
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Custom Fields', 'sports-leagues' ),
				'id'          => $prefix . 'custom_fields',
				'type'        => 'anwp_custom_fields',
				'option_slug' => 'official_custom_fields',
				'after_row'   => '</div>',
				'before_row'  => '<div id="anwp-tabs-custom_fields-sl_official_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Content', 'sports-leagues' ),
				'id'         => $prefix . 'bottom_content',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_bottom_post_content',
					'textarea_rows' => 5,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '<div id="anwp-tabs-bottom_content-sl_official_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.13
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/official', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Get list of registered Official groups.
	 *
	 * @return array
	 * @since 0.5.13
	 */
	public function get_official_groups() {

		$options = [];

		foreach ( Sports_Leagues_Config::get_value( 'official_groups', [] ) as $official ) {
			$options[ $official['id'] ] = $official['name'];
		}

		return $options;
	}

	/**
	 * Renders tabs for metabox. Helper HTML before.
	 *
	 * @since 0.1.0
	 */
	public function cmb2_before_metabox() {
		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-sl_official_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-person"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bio-sl_official_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bio', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-sl_official_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-sl_official_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of official tabs.
					 *
					 * @since 0.5.13
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/official' );
					?>
				</div>
				<div class="anwp-metabox-tabs__content pl-4 pb-4">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// @formatter:on
	}

	/**
	 * Renders tabs for metabox. Helper HTML after.
	 *
	 * @since 0.1.0
	 */
	public function cmb2_after_metabox() {
		// @formatter:off
		ob_start();
		?>
				</div>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// @formatter:on
	}

	/**
	 * Filters CPT title entry placeholder text
	 *
	 * @param string  $title Placeholder text. Default 'Enter title here'.
	 * @param WP_Post $post  Post object.
	 *
	 * @return string        Modified placeholder text
	 */
	public function title( $title, $post ) {

		if ( isset( $post->post_type ) && 'sl_official' === $post->post_type ) {
			return esc_html__( 'Official Name', 'sports-leagues' );
		}

		return $title;
	}


	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'official_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.5.13
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'sl_official_photo' => esc_html__( 'Photo', 'sports-leagues' ),
			'sl_official_group' => esc_html__( 'Group', 'sports-leagues' ),
			'official_id'       => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'sl_official_photo',
			'sl_official_group',
			'comments',
			'date',
			'official_id',
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
	 * Handles admin column display.
	 *
	 * @since  0.5.13
	 *
	 * @param array   $column_name Column currently being rendered.
	 * @param integer $post_id     ID of post to display column for.
	 */
	public function columns_display( $column_name, $post_id ) {
		switch ( $column_name ) {

			case 'sl_official_group':
				echo esc_html( get_post_meta( $post_id, '_sl_group', true ) );
				break;

			case 'sl_official_photo':
				echo get_the_post_thumbnail( $post_id, 'thumbnail' );
				break;

			case 'official_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Get all players from DB.
	 *
	 * @return array
	 */
	private function get_all_officials() {

		$cache_key = 'SL-OFFICIALS-LIST';

		if ( sports_leagues()->cache->get( $cache_key ) ) {
			return sports_leagues()->cache->get( $cache_key );
		}

		global $wpdb;

		$all_officials = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title pt,
				MAX( CASE WHEN pm.meta_key = '_sl_short_name' THEN pm.meta_value ELSE '' END) as sn,
				MAX( CASE WHEN pm.meta_key = '_sl_nationality' THEN pm.meta_value ELSE '' END) as nat,
				MAX( CASE WHEN pm.meta_key = '_sl_date_of_birth' THEN pm.meta_value ELSE '' END) as dob,
				MAX( CASE WHEN pm.meta_key = '_sl_group' THEN pm.meta_value ELSE '' END) as gr
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_official'
			GROUP BY p.ID
			ORDER BY p.post_title
			",
			OBJECT_K
		);

		if ( empty( $all_officials ) ) {
			return [];
		}

		foreach ( $all_officials as $official ) {
			if ( $official->nat ) {
				$countries = maybe_unserialize( $official->nat );

				if ( ! empty( $countries ) && is_array( $countries ) ) {
					$official->nat = implode( ',', $countries );
				}
			}
		}

		sports_leagues()->cache->set( $cache_key, $all_officials );

		return $all_officials;
	}

	/**
	 * Get officials.
	 *
	 * @return array $output_data -
	 * @since 0.5.13
	 */
	public function get_officials() {

		$all_officials = $this->get_all_officials();

		if ( empty( $all_officials ) ) {
			return [];
		}

		$officials_prepared = [];

		$all_officials = array_values( $all_officials );
		$all_officials = wp_list_sort( $all_officials, 'pt' );

		foreach ( $all_officials as $official ) {

			$official_prepared = (object) [
				'id'        => absint( $official->id ),
				'title'     => $official->pt,
				'group'     => $official->gr,
				'country'   => '',
				'type'      => 'official',
				'country2'  => '',
				'birthdate' => empty( $official->dob ) ? '' : date_i18n( 'M j, Y', strtotime( $official->dob ) ),
			];

			if ( $official->nat ) {
				$countries = explode( ',', $official->nat );

				if ( ! empty( $countries[0] ) ) {
					$official_prepared->country = mb_strtolower( $countries[0] );
				}

				if ( ! empty( $countries[1] ) ) {
					$official_prepared->country2 = mb_strtolower( $countries[1] );
				}
			}

			$officials_prepared[] = $official_prepared;
		}

		return $officials_prepared;
	}

	/**
	 * Rendering Officials for the game
	 *
	 * @param $post_id
	 *
	 * @since 0.5.13
	 */
	public function render_game_officials( $post_id ) {

		$game_officials = get_post_meta( $post_id, '_sl_officials', true );

		if ( empty( $game_officials ) ) {
			return;
		}

		$ids = [];

		$officials = explode( ',', $game_officials );

		foreach ( $officials as $item ) {
			if ( '_' === $item[0] ) {
				continue;
			}

			if ( absint( $item ) || mb_strpos( $item, 'temp__' ) !== false ) {
				$ids[] = $item;
			}
		}

		if ( empty( $ids ) ) {
			return;
		}

		$temp_officials = sports_leagues()->game->get_temp_officials( $post_id );

		// Populate Groups
		$official_groups = [];
		$last_group      = '_';

		foreach ( $officials as $item ) {
			if ( '_' === $item[0] ) {
				$last_group = mb_substr( $item, 1 );
			} elseif ( absint( $item ) || mb_strpos( $item, 'temp__' ) !== false ) {
				$official_groups[ $last_group ][] = $item;
			}
		}

		$output = '';

		foreach ( $official_groups as $group_title => $official_group ) {

			if ( '_' !== $group_title ) {
				$output .= '<span class="ml-3 font-weight-bold">' . esc_html( sports_leagues()->config->get_name_by_id( 'official_groups', $group_title ) ) . ':</span> ';
			}

			foreach ( $official_group as $item ) {
				$output .= '<span class="mr-2">';

				if ( mb_strpos( $item, 'temp__' ) !== false ) {
					$nationality = empty( $temp_officials[ $item ] ) ? false : $temp_officials[ $item ]->country;
				} else {
					$nationality = maybe_unserialize( get_post_meta( $item, '_sl_nationality', true ) );
				}

				if ( $nationality && is_array( $nationality ) ) {
					foreach ( $nationality as $country_code ) {
						$output .= '<span class="options__flag f16 mx-1" data-toggle="anwp-sl-tooltip" data-tippy-content="';
						$output .= esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ) . '">';
						$output .= '<span class="flag ' . esc_attr( $country_code ) . '"></span></span>';
					}
				}

				if ( mb_strpos( $item, 'temp__' ) !== false ) {
					$output .= empty( $temp_officials[ $item ] ) ? '' : $temp_officials[ $item ]->title;
				} else {
					$output .= '<a href="' . esc_url( get_permalink( $item ) ) . '" class="anwp-link-without-effects text-decoration-none">' . get_the_title( $item ) . '</a>;</span>';
				}
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

	/**
	 * Get array of official's games
	 *
	 * @param object|array $options
	 * @param string $result
	 *
	 * @since 0.10.2
	 * @return array|null|object
	 */
	public function get_official_games( $options, $result = '' ) {

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'official_id'   => '',
				'tournament_id' => '',
				'season_id'     => '',
				'stage_id'      => '',
				'league_id'     => '',
				'finished'      => '',
				'sort_by_date'  => '',
			]
		);

		if ( ! absint( $options->official_id ) ) {
			return [];
		}

		$cache_key = 'SL-OFFICIAL_get_official_games__' . md5( maybe_serialize( $options ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_game' );
		}

		$query = "
		SELECT g.*, pm.meta_value
		FROM {$wpdb->prefix}sl_games g
		LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = g.game_id AND pm.meta_key = '_sl_officials' )
		";

		//                                   "ref_id"                                  OR "ref_id,%"                                                     OR "%,ref_id"                                                     OR "%,ref_id,%"
		$query .= ' WHERE ( pm.meta_value LIKE "' . absint( $options->official_id ) . '" OR pm.meta_value LIKE "' . absint( $options->official_id ) . ',%" OR pm.meta_value LIKE "%,' . absint( $options->official_id ) . '" OR pm.meta_value LIKE "%,' . absint( $options->official_id ) . ',%" ) ';

		/*
		|--------------------------------------------------------------------
		| WHERE filter by tournament_id
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->tournament_id ) ) {
			$query .= $wpdb->prepare( ' AND g.tournament_id = %d ', $options->tournament_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by stage_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->stage_id ) {
			$query .= $wpdb->prepare( ' AND g.stage_id = %d ', $options->stage_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by season_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->season_id ) {
			$query .= $wpdb->prepare( ' AND g.season_id = %d ', $options->season_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by league_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->league_id ) {
			$query .= $wpdb->prepare( ' AND g.league_id = %d ', $options->league_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by finished
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->finished ) {
			$query .= $wpdb->prepare( ' AND g.finished = %d ', $options->finished );
		}

		/*
		|--------------------------------------------------------------------
		| ORDER BY date
		|--------------------------------------------------------------------
		*/
		if ( 'asc' === $options->sort_by_date ) {
			$query .= ' ORDER BY CASE WHEN g.kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, g.kickoff ASC';
		} elseif ( 'desc' === $options->sort_by_date ) {
			$query .= ' ORDER BY CASE WHEN g.kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, g.kickoff DESC';
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( 'stats' === $result ) {
			return $games;
		}

		$ids = wp_list_pluck( $games, 'game_id' );

		if ( 'ids' === $result ) {
			return $ids;
		}

		$game_posts = sports_leagues()->game->get_permalinks_by_ids( $ids );

		foreach ( $games as $game ) {
			$game->permalink = isset( $game_posts[ $game->game_id ] ) ? $game_posts[ $game->game_id ] : get_permalink( $game->game_id );
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $games ) ) {
			sports_leagues()->cache->set( $cache_key, $games, 'sl_game' );
		}

		return $games;
	}

	/**
	 * Get official grouped by role
	 *
	 * @return array
	 * @since 0.10.2
	 */
	public function get_official_games_grouped( $games, $official_id ) {

		$output = [];

		foreach ( $games as $game ) {

			$last_group = '_';

			if ( ! empty( $game->meta_value ) ) {
				foreach ( explode( ',', $game->meta_value ) as $official_item ) {

					if ( '_' === mb_substr( $official_item, 0, 1 ) ) {
						$last_group = $official_item;
					}

					if ( absint( $official_item ) && absint( $official_item ) === absint( $official_id ) ) {

						if ( ! isset( $output[ $last_group ] ) ) {
							$output[ $last_group ] = [];
						}

						$output[ $last_group ][] = $game;
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.10.3
	 */
	public function get_official_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_official_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}
}
