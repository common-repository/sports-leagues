<?php
/**
 * Sports Leagues :: Block > Teams
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Block_Teams {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		register_block_type(
			Sports_Leagues::dir( 'gutenberg/blocks/teams' ),
			[
				'title'           => 'SL Teams',
				'render_callback' => [ $this, 'server_side_render' ],
			]
		);
	}

	/**
	 * Register blocks.
	 *
	 * @param array    $attr           the block attributes
	 * @param string   $content        the block content
	 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered
	 */
	public function server_side_render( $attr, $content, $block_instance ) {

		$attr = wp_parse_args(
			$attr,
			[
				'tournament_id'  => '',
				'stage_id'       => '',
				'logo_size'      => 'big',
				'layout'         => '',
				'show_team_name' => 1,
				'exclude_ids'    => '',
				'include_ids'    => '',
				'team_title'     => '',
				'text_size'      => '',
				'width'          => '50',
			]
		);

		if ( empty( $attr['tournament_id'] ) && empty( $attr['stage_id'] ) && empty( $attr['include_ids'] ) && Sports_Leagues::is_editing_block_on_backend() ) {
			ob_start();
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'Please specify Tournament, Stage or Include IDs', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$teams_array = [];

		if ( ! empty( $attr['include_ids'] ) ) {
			$teams_array = wp_parse_id_list( $attr['include_ids'] );
		} elseif ( ! empty( $attr['stage_id'] ) ) {
			$teams_array = sports_leagues()->tournament->get_stage_teams( $attr['stage_id'], 'all' );

			// Check exclude ids
			if ( ! empty( $attr['exclude_ids'] ) ) {
				$teams_array = array_diff( $teams_array, wp_parse_id_list( $attr['exclude_ids'] ) );
			}
		} elseif ( ! empty( $attr['tournament_id'] ) ) {
			$tournament_obj = sports_leagues()->tournament->get_tournament( $attr['tournament_id'] );

			if ( ! empty( $tournament_obj->stages ) ) {
				foreach ( $tournament_obj->stages as $stage ) {
					$teams_array = array_merge( $teams_array, sports_leagues()->tournament->get_stage_teams( $stage->id, 'all' ) );
				}

				$teams_array = array_unique( array_map( 'absint', $teams_array ) );
			}

			// Check exclude ids
			if ( ! empty( $attr['exclude_ids'] ) ) {
				$teams_array = array_diff( $teams_array, wp_parse_id_list( $attr['exclude_ids'] ) );
			}
		}

		ob_start();

		if ( empty( $teams_array ) && Sports_Leagues::is_editing_block_on_backend() ) {
			sports_leagues()->load_partial(
				[
					'no_data_text' => __( 'No teams', 'sports-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$attr['width']   = ! absint( $attr['width'] ) ? 50 : absint( $attr['width'] );
		$style_font_size = absint( $attr['text_size'] ) ? ( 'font-size:' . absint( $attr['text_size'] ) . 'px' ) : '';
		?>
		<div class="anwp-b-wrap teams-gutenblock">
			<?php
			if ( 'list' === $attr['layout'] ) :
				foreach ( $teams_array as $team_id ) :
					$team_obj = sports_leagues()->team->get_team_by_id( $team_id );

					if ( empty( $team_obj ) ) {
						continue;
					}

					$logo  = empty( $attr['logo_size'] ) && $team_obj->logo_small ? $team_obj->logo_small : $team_obj->logo;
					$title = 'title' === $attr['team_title'] ? $team_obj->title : $team_obj->abbr;
					?>
					<div class="d-flex align-items-center teams-shortcode__wrapper position-relative anwp-border-bottom anwp-border-light py-2">
						<img loading="lazy" class="teams-shortcode__logo anwp-object-contain mr-2"
								style="width: <?php echo absint( $attr['width'] ); ?>px; height: <?php echo esc_attr( $attr['width'] ); ?>px;"
								src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $team_obj->title ); ?>">

						<?php if ( Sports_Leagues::string_to_bool( $attr['show_team_name'] ) ) : ?>
							<div class="teams-shortcode__text anwp-leading-1" style="<?php echo esc_attr( $style_font_size ); ?>">
								<?php echo esc_html( $title ); ?>
							</div>
						<?php endif; ?>

						<a class="anwp-link-without-effects anwp-link-cover" href="<?php echo esc_url( $team_obj->link ); ?>"></a>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="d-flex flex-wrap">
					<?php
					foreach ( $teams_array as $team_id ) :
						$team_obj = sports_leagues()->team->get_team_by_id( $team_id );

						if ( empty( $team_obj ) ) {
							continue;
						}

						$logo  = empty( $attr['logo_size'] ) && $team_obj->logo_small ? $team_obj->logo_small : $team_obj->logo;
						$title = 'title' === $attr['team_title'] ? $team_obj->title : $team_obj->abbr;
						?>
						<div class="teams-shortcode__wrapper team-logo position-relative anwp-text-center p-2 m-1 anwp-border anwp-border-light d-flex flex-column">
							<img loading="lazy" class="teams-shortcode__logo anwp-object-contain mx-auto"
									style="width: <?php echo absint( $attr['width'] ); ?>px; height: <?php echo esc_attr( $attr['width'] ); ?>px;"
									src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $team_obj->title ); ?>">

							<?php if ( Sports_Leagues::string_to_bool( $attr['show_team_name'] ) ) : ?>
								<div class="teams-shortcode__text anwp-text-center mt-auto anwp-leading-1 pt-1"
										style="width: <?php echo esc_attr( $attr['width'] ); ?>px; <?php echo esc_attr( $style_font_size ); ?>">
									<?php echo esc_html( $title ); ?>
								</div>
							<?php endif; ?>

							<a class="anwp-link-without-effects anwp-link-cover" href="<?php echo esc_url( $team_obj->link ); ?>"></a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

return new Sports_Leagues_Block_Teams();
