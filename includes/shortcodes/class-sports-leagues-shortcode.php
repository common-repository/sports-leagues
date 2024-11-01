<?php
/**
 * Sports Leagues :: Shortcode
 *
 * @since   0.5.11
 * @package Sports_Leagues
 */

class Sports_Leagues_Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initiate hooks.
	 */
	public function hooks() {

		// Initialize MCE button
		add_action( 'admin_init', [ $this, 'mce_button' ] );

		add_action( 'after_wp_tiny_mce', [ $this, 'tinymce_l10n_vars' ] );
		add_action( 'enqueue_block_assets', [ $this, 'add_scripts_to_gutenberg' ] );

		// Ajax request
		add_action( 'wp_ajax_sl_shortcodes_modal_structure', [ $this, 'get_modal_structure' ] );
		add_action( 'wp_ajax_sl_shortcodes_modal_form', [ $this, 'get_modal_form' ] );
	}

	/**
	 * Filter Functions with Hooks
	 *
	 * @since 0.5.11
	 */
	public function mce_button() {

		// Check if user have permission
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Check if WYSIWYG is enabled
		if ( 'true' === get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', [ $this, 'add_tinymce_plugin' ] );
			add_filter( 'mce_buttons', [ $this, 'register_tinymce_button' ] );
		}
	}

	/**
	 * @param $plugin_array
	 *
	 * @return mixed
	 * @since 0.5.11
	 */
	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array['sports_leagues'] = Sports_Leagues::url( 'admin/js/tinymce-plugin.js?ver=' . Sports_Leagues::VERSION );
		return $plugin_array;
	}

	/**
	 * @param $buttons
	 *
	 * @return mixed
	 * @since 0.5.11
	 */
	public function register_tinymce_button( $buttons ) {
		array_push( $buttons, 'sports_leagues_button' );
		return $buttons;
	}

	/**
	 * Load TinyMCE localized vars
	 *
	 * @since 0.5.11
	 */
	public function tinymce_l10n_vars() {

		$vars = [
			'sports_leagues' => esc_html__( 'Sports Leagues', 'sports-leagues' ),
			'nonce'          => wp_create_nonce( 'sl_shortcodes_nonce' ),
		];

		?>
		<script type="text/javascript">
			var _sl_shortcodes_l10n = <?php echo wp_json_encode( $vars ); ?>;
		</script>
		<?php
	}

	/**
	 * Get results for ajax request.
	 *
	 * @since 0.5.11
	 */
	public function get_modal_structure() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'sl_shortcodes_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		ob_start();
		?>
		<div class="anwp-sl-shortcode-modal__header">
			<label for="anwp-sl-shortcode-modal__selector"><?php echo esc_html__( 'Shortcode', 'sports-leagues' ); ?></label>
			<select id="anwp-sl-shortcode-modal__selector">
				<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
				<option value="games"><?php echo esc_html__( 'Games', 'sports-leagues' ); ?></option>
				<option value="players-stats"><?php echo esc_html__( 'Players Stats', 'sports-leagues' ); ?></option>
				<option value="standing"><?php echo esc_html__( 'Standing Table', 'sports-leagues' ); ?></option>
				<option value="teams"><?php echo esc_html__( 'Teams', 'sports-leagues' ); ?></option>
				<option value="tournament-header"><?php echo esc_html__( 'Tournament Header', 'sports-leagues' ); ?></option>
				<option value="tournament-list"><?php echo esc_html__( 'Tournament List', 'sports-leagues' ); ?></option>
				<?php
				/**
				 * Hook: sports-leagues/shortcodes/selector_bottom
				 *
				 * @since 0.5.11
				 */
				do_action( 'sports-leagues/shortcodes/selector_bottom' );
				?>
			</select>
			<span class="spinner"></span>
		</div>
		<div class="anwp-sl-shortcode-modal__content"></div>
		<div class="anwp-sl-shortcode-modal__footer">
			<button id="anwp-sl-shortcode-modal__cancel" class="button"><?php echo esc_html__( 'Close', 'sports-leagues' ); ?></button>
			<button id="anwp-sl-shortcode-modal__insert" class="button button-primary"><?php echo esc_html__( 'Insert Shortcode', 'sports-leagues' ); ?></button>
		</div>
		<?php
		$html_output = ob_get_clean();

		wp_send_json_success( [ 'html' => $html_output ] );
	}

	/**
	 * Get results for ajax request.
	 *
	 * @since 0.5.11
	 */
	public function get_modal_form() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'sl_shortcodes_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		$shortcode = isset( $_POST['shortcode'] ) ? sanitize_key( $_POST['shortcode'] ) : '';

		if ( ! $shortcode ) {
			wp_send_json_error( 'Error : Incorrect Data' );
		}

		ob_start();

		switch ( $shortcode ) {
			case 'standing':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'standing' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-standing-title"><?php echo esc_html__( 'Title', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="title" data-sl-type="text" type="text" id="sl-form-shortcode-standing-title" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-standing-id"><?php echo esc_html__( 'Standing Table', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="id" data-sl-type="select2" id="sl-form-shortcode-standing-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<?php foreach ( sports_leagues()->standing->get_standing_options() as $standing_id => $standing_title ) : ?>
									<option value="<?php echo esc_attr( $standing_id ); ?>"><?php echo esc_html( $standing_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-standing-exclude"><?php echo esc_html__( 'Exclude Teams', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="exclude_ids" data-sl-type="select2" id="sl-form-shortcode-standing-exclude" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->team->get_team_options() as $team_id => $team_title ) : ?>
									<option value="<?php echo esc_attr( $team_id ); ?>"><?php echo esc_html( $team_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-standing-layout"><?php echo esc_html__( 'Layout', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="layout" data-sl-type="select" id="sl-form-shortcode-standing-layout" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'default', 'sports-leagues' ); ?></option>
								<option value="mini"><?php echo esc_html__( 'mini', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-partial"><?php echo esc_html__( 'Show Partial Data', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="partial" data-sl-type="text" type="text" id="sl-form-shortcode-partial" value="" class="sl-shortcode-attr regular-text code">
							<span class="anwp-option-desc"><?php echo esc_html__( 'Eg.: "1-5" (show teams from 1 to 5 place), "45" - show table slice with specified team ID in the middle', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-standing-bottom_link"><?php echo esc_html__( 'Show link to', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<select name="bottom_link" data-sl-type="select" id="sl-form-shortcode-standing-bottom_link" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'none', 'sports-leagues' ); ?></option>
								<option value="tournament"><?php echo esc_html__( 'tournament', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-link_text"><?php echo esc_html__( 'Alternative bottom link text', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="link_text" data-sl-type="text" type="text" id="sl-form-shortcode-link_text" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-standing">
				<?php
				break;

			case 'teams':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'teams' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-stage"><?php echo esc_html__( 'Tournament Stage', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="stage_id" data-sl-type="select2" id="sl-form-shortcode-teams-stage" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->tournament->get_stage_options() as $stage_id => $stage_title ) : ?>
									<option value="<?php echo esc_attr( $stage_id ); ?>"><?php echo esc_html( $stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-teams-logo_height"><?php echo esc_html__( 'Logo Height', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Height value with units. Example: "50px" or "3rem".', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="logo_height" data-sl-type="text" type="text" id="sl-form-shortcode-teams-logo_height" value="50px" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-teams-logo_width"><?php echo esc_html__( 'Logo Width', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Width value with units. Example: "50px" or "3rem".', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="logo_width" data-sl-type="text" type="text" id="sl-form-shortcode-teams-logo_width" value="50px" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-show_team_name"><?php echo esc_html__( 'Show Team Name', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_team_name" data-sl-type="select" id="sl-form-shortcode-teams-show_team_name" class="postform sl-shortcode-attr">
								<option value="yes" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="no"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-layout"><?php echo esc_html__( 'Layout', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="layout" data-sl-type="select" id="sl-form-shortcode-teams-layout" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'Custom Height and Width', 'sports-leagues' ); ?></option>
								<option value="2col"><?php echo esc_html__( '2 Columns', 'sports-leagues' ); ?></option>
								<option value="3col"><?php echo esc_html__( '3 Columns', 'sports-leagues' ); ?></option>
								<option value="4col"><?php echo esc_html__( '4 Columns', 'sports-leagues' ); ?></option>
								<option value="6col"><?php echo esc_html__( '6 Columns', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-exclude"><?php echo esc_html__( 'Exclude Teams', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="exclude_ids" data-sl-type="select2" id="sl-form-shortcode-teams-exclude" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->team->get_team_options() as $team_id => $team_title ) : ?>
									<option value="<?php echo esc_attr( $team_id ); ?>"><?php echo esc_html( $team_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-include"><?php echo esc_html__( 'Include Teams', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="include_ids" data-sl-type="select2" id="sl-form-shortcode-teams-include" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->team->get_team_options() as $team_id => $team_title ) : ?>
									<option value="<?php echo esc_attr( $team_id ); ?>"><?php echo esc_html( $team_title ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="anwp-option-desc"><?php echo esc_html__( 'If this option is set, "Tournament Stage" option will be ignored.', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-teams">
				<?php
				break;

			case 'tournament-header':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'tournament-header' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament_id"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="tournament_id" data-sl-type="select2" id="sl-form-shortcode-tournament_id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<?php foreach ( sports_leagues()->tournament->get_root_tournament_options() as $stage_id => $stage_title ) : ?>
									<option value="<?php echo esc_attr( $stage_id ); ?>"><?php echo esc_html( $stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-stage_id"><?php echo esc_html__( 'Tournament Stage ID', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<input name="stage_id" data-sl-type="text" type="text" id="sl-form-shortcode-stage_id" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament-header-title_as_link"><?php echo esc_html__( 'Title as a Link', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="title_as_link" data-sl-type="select" id="sl-form-shortcode-tournament-header-title_as_link" class="postform sl-shortcode-attr">
								<option value="1"><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0" selected><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-tournament-header">
				<?php
				break;

			case 'tournament-list':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'tournament-list' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-tournament-list-status"><?php echo esc_html__( 'Tournament Status', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<select name="status" data-sl-type="select" id="sl-form-shortcode-tournament-list-status" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'All', 'sports-leagues' ); ?></option>
								<option value="finished"><?php echo esc_html__( 'Finished', 'sports-leagues' ); ?></option>
								<option value="active"><?php echo esc_html__( 'Active', 'sports-leagues' ); ?></option>
								<option value="upcoming"><?php echo esc_html__( 'Upcoming', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament-list-sort_by_date"><?php echo esc_html__( 'Sort By Date', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="sort_by_date" data-sl-type="select" id="sl-form-shortcode-tournament-list-sort_by_date" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'none', 'sports-leagues' ); ?></option>
								<option value="asc"><?php echo esc_html__( 'Ascending', 'sports-leagues' ); ?></option>
								<option value="desc"><?php echo esc_html__( 'Descending', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-tournament-list-limit"><?php echo esc_html__( 'Limit', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<input name="limit" data-sl-type="text" type="text" id="sl-form-shortcode-tournament-list-limit" value="0" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament-list-exclude"><?php echo esc_html__( 'Exclude Tournaments', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="exclude_ids" data-sl-type="select2" id="sl-form-shortcode-tournament-list-exclude" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->tournament->get_root_tournament_options() as $tournament_id => $tournament_title ) : ?>
									<option value="<?php echo esc_attr( $tournament_id ); ?>"><?php echo esc_html( $tournament_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament-list-include"><?php echo esc_html__( 'Include Tournaments', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="include_ids" data-sl-type="select2" id="sl-form-shortcode-tournament-list-include" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->tournament->get_root_tournament_options() as $tournament_id => $tournament_title ) : ?>
									<option value="<?php echo esc_attr( $tournament_id ); ?>"><?php echo esc_html( $tournament_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-tournament-list-date-from"><?php echo esc_html__( 'Date From', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="date_from" data-sl-type="text" type="text" id="sl-form-shortcode-tournament-list-date-from" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-tournament-list-date-to"><?php echo esc_html__( 'Date To', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="date_to" data-sl-type="text" type="text" id="sl-form-shortcode-tournament-list-date-to" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-tournament-list">
				<?php
				break;

			case 'players-stats':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'players-stats' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-stats_id"><?php echo esc_html__( 'Stats', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="stats_id" data-sl-type="select" id="sl-form-shortcode-stats_id" class="postform sl-shortcode-attr">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->player_stats->get_stats_game_countable_options() as $stats_id => $stats_option ) : ?>
									<option value="<?php echo esc_attr( $stats_id ); ?>"><?php echo esc_html( $stats_option ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-position"><?php echo esc_html__( 'Player Position', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="position" data-sl-type="select2" id="sl-form-shortcode-position" class="postform sl-shortcode-attr sl-shortcode-select2" multiple>
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->config->get_player_positions() as $position_id => $position ) : ?>
									<option value="<?php echo esc_attr( $position_id ); ?>"><?php echo esc_html( $position ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament_id"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="tournament_id" data-sl-type="select2" id="sl-form-shortcode-tournament_id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->tournament->get_root_tournament_options() as $tournament_stage_id => $tournament_stage_title ) : ?>
									<option value="<?php echo esc_attr( $tournament_stage_id ); ?>"><?php echo esc_html( $tournament_stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-stage-id"><?php echo esc_html__( 'Tournament Stage', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="stage_id" data-sl-type="select2" id="sl-form-shortcode-stage-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->tournament->get_stage_options() as $stage_id => $stage_title ) : ?>
									<option value="<?php echo esc_attr( $stage_id ); ?>"><?php echo esc_html( $stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-season-id"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="season_id" data-sl-type="select2" id="sl-form-shortcode-season-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->season->get_season_options() as $season_id => $season_title ) : ?>
									<option value="<?php echo esc_attr( $season_id ); ?>"><?php echo esc_html( $season_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-league-id"><?php echo esc_html__( 'League', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="league_id" data-sl-type="select2" id="sl-form-shortcode-league-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->league->get_league_options() as $league_id => $league_title ) : ?>
									<option value="<?php echo esc_attr( $league_id ); ?>"><?php echo esc_html( $league_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-group-id"><?php echo esc_html__( 'Group ID', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="group_id" data-sl-type="text" type="text" id="sl-form-shortcode-group-id" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-round-id"><?php echo esc_html__( 'Round ID', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="round_id" data-sl-type="text" type="text" id="sl-form-shortcode-round-id" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-venue-id"><?php echo esc_html__( 'Venue', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="venue_id" data-sl-type="select2" id="sl-form-shortcode-venue-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->venue->get_venue_options() as $venue_id => $venue_title ) : ?>
									<option value="<?php echo esc_attr( $venue_id ); ?>"><?php echo esc_html( $venue_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-team-id"><?php echo esc_html__( 'Team', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="team_id" data-sl-type="select2" id="sl-form-shortcode-team-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->team->get_team_options() as $team_id => $team_title ) : ?>
									<option value="<?php echo esc_attr( $team_id ); ?>"><?php echo esc_html( $team_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-game-id"><?php echo esc_html__( 'Game ID', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="game_id" data-sl-type="text" type="text" id="sl-form-shortcode-game-id" value="" class="sl-shortcode-attr code">
							<button type="button" class="button anwp-sl-selector" data-context="game" data-single="yes">
								<span class="dashicons dashicons-search"></span>
							</button>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-game-day"><?php echo esc_html__( 'Game Day', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="game_day" data-sl-type="text" type="text" id="sl-form-shortcode-game-day" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-order"><?php echo esc_html__( 'Order', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="order" data-sl-type="select" id="sl-form-shortcode-order" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'Descending', 'sports-leagues' ); ?></option>
								<option value="ASC"><?php echo esc_html__( 'Ascending', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-limit"><?php echo esc_html__( 'Limit', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="limit" data-sl-type="text" type="text" id="sl-form-shortcode-limit" value="10" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-soft_limit"><?php echo esc_html__( 'Soft Limit', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="soft_limit" data-sl-type="select" id="sl-form-shortcode-soft_limit" class="postform sl-shortcode-attr">
								<option value="1"><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0" selected><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_position"><?php echo esc_html__( 'Show Position', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_position" data-sl-type="select" id="sl-form-shortcode-show_position" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_team"><?php echo esc_html__( 'Show Team', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_team" data-sl-type="select" id="sl-form-shortcode-show_team" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_nationality"><?php echo esc_html__( 'Show Nationality', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_nationality" data-sl-type="select" id="sl-form-shortcode-show_nationality" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_photo"><?php echo esc_html__( 'Show Photo', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_photo" data-sl-type="select" id="sl-form-shortcode-show_photo" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_games_played"><?php echo esc_html__( 'Show Games Played', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_games_played" data-sl-type="select" id="sl-form-shortcode-show_games_played" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-link_to_profile"><?php echo esc_html__( 'Link to Profile', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="link_to_profile" data-sl-type="select" id="sl-form-shortcode-link_to_profile" class="postform sl-shortcode-attr">
								<option value="1"><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0" selected><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-teams-show_full"><?php echo esc_html__( 'Show Full List in Modal', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_full" data-sl-type="select" id="sl-form-shortcode-teams-show_full" class="postform sl-shortcode-attr">
								<option value="0" selected><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
								<option value="1"><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-players-stats">
				<?php
				break;

			case 'games':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_docs_link( 'games' );
				?>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament_id"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="tournament_id" data-sl-type="select2" id="sl-form-shortcode-tournament_id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->tournament->get_root_tournament_options() as $tournament_stage_id => $tournament_stage_title ) : ?>
									<option value="<?php echo esc_attr( $tournament_stage_id ); ?>"><?php echo esc_html( $tournament_stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-stage-id"><?php echo esc_html__( 'Tournament Stage', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="stage_id" data-sl-type="select2" id="sl-form-shortcode-stage-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->tournament->get_stage_options() as $stage_id => $stage_title ) : ?>
									<option value="<?php echo esc_attr( $stage_id ); ?>"><?php echo esc_html( $stage_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-season-id"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="season_id" data-sl-type="select2" id="sl-form-shortcode-season-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->season->get_season_options() as $season_id => $season_title ) : ?>
									<option value="<?php echo esc_attr( $season_id ); ?>"><?php echo esc_html( $season_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-league-id"><?php echo esc_html__( 'League', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="league_id" data-sl-type="select2" id="sl-form-shortcode-league-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->league->get_league_options() as $league_id => $league_title ) : ?>
									<option value="<?php echo esc_attr( $league_id ); ?>"><?php echo esc_html( $league_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-group-id"><?php echo esc_html__( 'Group ID', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="group_id" data-sl-type="text" type="text" id="sl-form-shortcode-group-id" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-round-id"><?php echo esc_html__( 'Round ID', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="round_id" data-sl-type="text" type="text" id="sl-form-shortcode-round-id" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-date-from"><?php echo esc_html__( 'Date From', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="date_from" data-sl-type="text" type="text" id="sl-form-shortcode-date-from" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-date-to"><?php echo esc_html__( 'Date To', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="date_to" data-sl-type="text" type="text" id="sl-form-shortcode-date-to" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-finished"><?php echo esc_html__( 'Game Status', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="finished" data-sl-type="select" id="sl-form-shortcode-finished" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'All', 'sports-leagues' ); ?></option>
								<option value="1"><?php echo esc_html__( 'Finished', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'Upcoming', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-venue-id"><?php echo esc_html__( 'Venue', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="venue_id" data-sl-type="select2" id="sl-form-shortcode-venue-id" class="postform sl-shortcode-attr sl-shortcode-select2">
								<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
								<?php foreach ( sports_leagues()->venue->get_venue_options() as $venue_id => $venue_title ) : ?>
									<option value="<?php echo esc_attr( $venue_id ); ?>"><?php echo esc_html( $venue_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-filter-by-team"><?php echo esc_html__( 'Filter By Teams', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="filter_by_team" data-sl-type="select2" id="sl-form-shortcode-filter-by-team" class="postform sl-shortcode-attr sl-shortcode-select2" multiple="multiple">
								<?php foreach ( sports_leagues()->team->get_team_options() as $team_id => $team_title ) : ?>
									<option value="<?php echo esc_attr( $team_id ); ?>"><?php echo esc_html( $team_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-filter_by_game_day"><?php echo esc_html__( 'Filter By GameDay', 'sports-leagues' ); ?></label>
							<span class="anwp-option-desc"><?php echo esc_html__( 'Comma separated list of options', 'sports-leagues' ); ?></span>
						</th>
						<td>
							<input name="filter_by_game_day" data-sl-type="text" type="text" id="sl-form-shortcode-filter_by_game_day" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-days_offset"><?php echo esc_html__( 'Dynamic days filter', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<input name="days_offset" data-sl-type="text" type="text" id="sl-form-shortcode-days_offset" value="" class="sl-shortcode-attr regular-text code">
							<span class="anwp-option-desc"><?php echo esc_html__( 'For example: "-2" from 2 days ago and newer; "2" from day after tomorrow and newer', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-days_offset_to"><?php echo esc_html__( 'Dynamic days filter to', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="days_offset_to" data-sl-type="text" type="text" id="sl-form-shortcode-days_offset_to" value="" class="sl-shortcode-attr regular-text code">
							<span class="anwp-option-desc"><?php echo esc_html__( 'For example: "1" till tomorrow (tomorrow not included)', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-limit"><?php echo esc_html__( 'Games Limit', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<input name="limit" data-sl-type="text" type="text" id="sl-form-shortcode-limit" value="" class="sl-shortcode-attr regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-sort_by_date"><?php echo esc_html__( 'Sort By Date', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="sort_by_date" data-sl-type="select" id="sl-form-shortcode-sort_by_date" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'none', 'sports-leagues' ); ?></option>
								<option value="asc"><?php echo esc_html__( 'Ascending', 'sports-leagues' ); ?></option>
								<option value="desc"><?php echo esc_html__( 'Descending', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-sort_by_game_day"><?php echo esc_html__( 'Sort By GameDay', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="sort_by_game_day" data-sl-type="select" id="sl-form-shortcode-sort_by_game_day" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'none', 'sports-leagues' ); ?></option>
								<option value="asc"><?php echo esc_html__( 'Ascending', 'sports-leagues' ); ?></option>
								<option value="desc"><?php echo esc_html__( 'Descending', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-group_by"><?php echo esc_html__( 'Group By', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="group_by" data-sl-type="select" id="sl-form-shortcode-group_by" class="postform sl-shortcode-attr">
								<option value="" selected><?php echo esc_html__( 'none', 'sports-leagues' ); ?></option>
								<option value="day"><?php echo esc_html__( 'Day', 'sports-leagues' ); ?></option>
								<option value="month"><?php echo esc_html__( 'Month', 'sports-leagues' ); ?></option>
								<option value="gameday"><?php echo esc_html__( 'GameDay', 'sports-leagues' ); ?></option>
								<option value="stage"><?php echo esc_html__( 'Stage', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_team_logo"><?php echo esc_html__( 'Show team logo', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_team_logo" data-sl-type="select" id="sl-form-shortcode-show_team_logo" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_game_datetime"><?php echo esc_html__( 'Show game datetime', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_game_datetime" data-sl-type="select" id="sl-form-shortcode-show_game_datetime" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-tournament_logo"><?php echo esc_html__( 'Show tournament logo', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="tournament_logo" data-sl-type="select" id="sl-form-shortcode-tournament_logo" class="postform sl-shortcode-attr">
								<option value="1" selected><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0"><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sl-form-shortcode-outcome_id"><?php echo esc_html__( 'Show Outcome for team ID', 'sports-leagues' ); ?></label>
						</th>
						<td>
							<input name="outcome_id" data-sl-type="text" type="text" id="sl-form-shortcode-outcome_id" value="" class="sl-shortcode-attr code">
							<button type="button" class="button anwp-sl-selector" data-context="team" data-single="yes">
								<span class="dashicons dashicons-search"></span>
							</button>
							<span class="anwp-option-desc"><?php echo esc_html__( 'works only in "slim" layout', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-include_ids"><?php echo esc_html__( 'Include IDs', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="include_ids" data-sl-type="text" type="text" id="sl-form-shortcode-include_ids" value="" class="sl-shortcode-attr code">
							<button type="button" class="button anwp-sl-selector" data-context="game" data-single="no">
								<span class="dashicons dashicons-search"></span>
							</button>
							<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of IDs', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-exclude_ids"><?php echo esc_html__( 'Exclude IDs', 'sports-leagues' ); ?></label></th>
						<td>
							<input name="exclude_ids" data-sl-type="text" type="text" id="sl-form-shortcode-exclude_ids" value="" class="sl-shortcode-attr code">
							<button type="button" class="button anwp-sl-selector" data-context="game" data-single="no">
								<span class="dashicons dashicons-search"></span>
							</button>
							<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of IDs', 'sports-leagues' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sl-form-shortcode-show_load_more"><?php echo esc_html__( 'Show Load More', 'sports-leagues' ); ?></label></th>
						<td>
							<select name="show_load_more" data-sl-type="select" id="sl-form-shortcode-show_load_more" class="postform sl-shortcode-attr">
								<option value="1"><?php echo esc_html__( 'Yes', 'sports-leagues' ); ?></option>
								<option value="0" selected><?php echo esc_html__( 'No', 'sports-leagues' ); ?></option>
							</select>
						</td>
					</tr>
					</tbody>
				</table>
				<input type="hidden" class="sl-shortcode-name" name="sl-slug" value="sl-games">
				<?php
				break;
		}

		/**
		 * Hook: sports-leagues/shortcodes/modal_form_shortcode
		 *
		 * @since 0.5.13
		 */
		do_action( 'sports-leagues/shortcodes/modal_form_shortcode', $shortcode );

		$html_output = ob_get_clean();

		wp_send_json_success( [ 'html' => $html_output ] );
	}

	/**
	 * Renders documentation link.
	 *
	 * @param string $shortcode
	 *
	 * @return string
	 * @since 0.5.11
	 */
	private function render_docs_link( $shortcode ) {

		$shortcode_link  = '';
		$shortcode_title = '';

		switch ( $shortcode ) {
			case 'standing':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/149-standing-table-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Standing Table', 'sports-leagues' );
				break;

			case 'teams':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/150-teams-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Teams', 'sports-leagues' );
				break;

			case 'tournament-header':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/151-tournament-header-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Tournament Header', 'sports-leagues' );
				break;

			case 'tournament-list':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/257-tournament-list-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Tournament List', 'sports-leagues' );
				break;

			case 'games':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/152-games-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Games', 'sports-leagues' );
				break;

			case 'players-stats':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/6/articles/611-players-stats-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'sports-leagues' ) . ' :: ' . esc_html__( 'Players Stats', 'sports-leagues' );
				break;
		}

		/**
		 * Modify shortcode documentation link.
		 *
		 * @param string $shortcode_link
		 * @param string $shortcode
		 *
		 * @since 0.5.11
		 */
		$shortcode_link = apply_filters( 'sports-leagues/shortcode/docs_link', $shortcode_link, $shortcode );

		/**
		 * Modify shortcode title.
		 *
		 * @param string $shortcode_title
		 * @param string $shortcode
		 *
		 * @since 0.5.11
		 */
		$shortcode_title = apply_filters( 'sports-leagues/shortcode/docs_title', $shortcode_title, $shortcode );

		$output = '<div class="anwp-shortcode-docs-link d-flex align-items-center table-info border p-2 border-info">';

		$output .= '<svg class="anwp-icon"><use xlink:href="#icon-book"></use></svg>';
		$output .= '<b class="mx-2">' . esc_html__( 'Documentation', 'sports-leagues' ) . ':</b> ';
		$output .= '<a target="_blank" href="' . esc_url( $shortcode_link ) . '">' . esc_html( $shortcode_title ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Added TinyMCE scripts to the Gutenberg Classic editor Block
	 *
	 * @since 0.5.11
	 */
	public function add_scripts_to_gutenberg() {
		global $current_screen;

		$is_gutenberg_old = function_exists( 'is_gutenberg_page' ) && is_gutenberg_page();
		$is_gutenberg_new = $current_screen instanceof WP_Screen && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();

		if ( is_admin() && ( $is_gutenberg_new || $is_gutenberg_old ) ) {
			$this->tinymce_l10n_vars();
		}
	}
}

// Bump
new Sports_Leagues_Shortcode();
