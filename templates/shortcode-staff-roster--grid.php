<?php
/**
 * The Template for displaying Staff Team Roster.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-staff-roster--grid.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.10
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check required params
if ( empty( $data->team_id ) || empty( $data->season_id ) ) {
	return;
}

// Prevent errors with new params
$data = (object) wp_parse_args(
	$data,
	[
		'team_id'   => '',
		'season_id' => '',
		'class'     => '',
		'header'    => true,
	]
);

// Prepare roster
$roster = sports_leagues()->team->tmpl_prepare_staff_team_roster( $data->team_id, $data->season_id );

if ( empty( wp_list_filter( $roster, [ 'type' => 'staff' ] ) ) ) {
	return;
}

// Default photo
$default_photo = sports_leagues()->helper->get_default_player_photo();
?>
<div class="anwp-b-wrap roster roster--shortcode roster-grid <?php echo esc_attr( $data->class ); ?>">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'shortcode__staff__staff', __( 'Staff', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="anwp-grid-table team-roster-grid">
		<?php
		/*
		|--------------------------------------------------------------------
		| Prepare output
		|--------------------------------------------------------------------
		*/
		$roster_output    = [];
		$last_group_index = 0;

		foreach ( $roster as $item_key => $roster_item ) :

			ob_start();

			if ( 'group' === $roster_item['type'] ) :
				?>
				<div class="team-roster-grid__header anwp-bg-light">
					<?php echo esc_html( sports_leagues()->config->get_name_by_id( 'staff_roster_groups', $roster_item['title'] ) ); ?>
				</div>
				<?php
				$last_group_index ++;
				$roster_output[ $last_group_index ]['title'] = ob_get_clean();
				continue;
			endif;
			?>
			<div class="team-roster-grid__block position-relative d-flex flex-column anwp-border-light">
				<div class="team-roster-grid__photo-wrapper anwp-text-center d-flex justify-content-between align-items-center anwp-bg-light anwp-border-light">
					<img loading="lazy" class="team-roster-grid__photo anwp-object-contain anwp-w-70 anwp-h-70" alt="<?php echo esc_attr( $roster_item['name'] ); ?>" src="<?php echo esc_url( $roster_item['photo'] ?: $default_photo ); ?>">
				</div>
				<div class="d-flex flex-column position-relative team-roster-grid__player-content">

					<div class="team-roster-grid__name mb-auto"><?php echo esc_html( $roster_item['name'] ); ?></div>

					<?php if ( $roster_item['job'] ) : ?>
						<div class="team-roster-grid__player-param d-flex anwp-border-light team-roster-grid__role">
							<span class="team-roster-grid__player-param-title anwp-opacity-70"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__staff__job', __( 'Job', 'sports-leagues' ) ) ); ?></span>
							<span class="team-roster-grid__player-param-value ml-auto"><?php echo esc_html( $roster_item['job'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $roster_item['age'] ) : ?>
						<div class="team-roster-grid__player-param d-flex anwp-border-light team-roster-grid__age">
							<span class="team-roster-grid__player-param-title anwp-opacity-70"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__staff__age', __( 'Age', 'sports-leagues' ) ) ); ?></span>
							<span class="team-roster-grid__player-param-value ml-auto"><?php echo esc_html( $roster_item['age'] ?: '-' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $roster_item['nationality'] ) && is_array( $roster_item['nationality'] ) ) : ?>
						<div class="team-roster-grid__player-param d-flex team-roster-grid__nationality">
							<span class="team-roster-grid__player-param-title anwp-opacity-70"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__staff__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></span>
							<div class="team-roster-grid__player-param-value ml-auto">
								<?php foreach ( $roster_item ['nationality'] as $country_code ) : ?>
									<span class="options__flag f32" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>"><span class="flag <?php echo esc_attr( $country_code ); ?>"></span></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<a href="<?php echo esc_url( get_permalink( $item_key ) ); ?>" class="anwp-link-without-effects anwp-link-cover"></a>
			</div>
			<?php

			if ( ! isset( $roster_output[ $last_group_index ]['items'] ) ) {
				$roster_output[ $last_group_index ]['items'] = [];
			}

			$roster_output[ $last_group_index ]['items'][] = ob_get_clean();
		endforeach;

		/*
		|--------------------------------------------------------------------
		| Generate output
		|--------------------------------------------------------------------
		*/
		foreach ( $roster_output as $output_group ) {
			if ( empty( $output_group['items'] ) ) {
				continue;
			}

			if ( ! empty( $output_group['title'] ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $output_group['title'];
			}

			foreach ( $output_group['items'] as $output_group_item ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $output_group_item;
			}
		}
		?>
	</div>
</div>
