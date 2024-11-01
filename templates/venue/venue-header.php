<?php
/**
 * The Template for displaying Venue >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/venue/venue-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'venue_id'      => '',
		'season_id'     => '',
		'show_selector' => true,
	]
);

if ( ! absint( $data->venue_id ) ) {
	return;
}

$thumbnail_id = get_post_meta( $data->venue_id, '_thumbnail_id', true );
$venue        = get_post( $data->venue_id );
?>
<div class="anwp-row anwp-section">
	<?php
	if ( $thumbnail_id ) :
		$caption = wp_get_attachment_caption( $thumbnail_id );
		?>
		<div class="anwp-col-md-6">
			<?php echo wp_get_attachment_image( $thumbnail_id, 'large', false, [ 'class' => 'venue__main-photo' ] ); ?>
			<div class="mt-1 player__main-photo-caption anwp-opacity-70"><?php echo esc_html( $caption ); ?></div>
		</div>
	<?php endif; ?>
	<div class="anwp-col-md-6 mt-3 mt-sm-0">
		<table class="table bg-light table-bordered table-sm options-list mb-4">
			<tbody>
			<?php
			/**
			 * Hook: sports-leagues/tmpl-venue/fields_top
			 *
			 * @since 0.5.2
			 *
			 * @param WP_Post $venue
			 */
			do_action( 'sports-leagues/tmpl-venue/fields_top', $venue );
			?>
			<?php if ( $venue->_sl_city ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__city', __( 'City', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value"><?php echo esc_html( $venue->_sl_city ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $venue->_sl_teams && is_array( $venue->_sl_teams ) ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__teams', __( 'Teams', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value">
						<div class="d-flex flex-wrap">
							<?php
							foreach ( $venue->_sl_teams as $venue_team ) :
								$team_data = sports_leagues()->team->get_team_by_id( $venue_team );

								if ( empty( $team_data->id ) ) {
									continue;
								}
								?>
								<span class="d-flex align-items-center mr-3 text-nowrap">
										<?php if ( ! empty( $team_data->logo ) ) : ?>
											<span class="team-logo__cover team-logo__cover--mini mr-1" style="background-image: url('<?php echo esc_url( $team_data->logo ); ?>')"></span>
										<?php endif; ?>
										<a href="<?php echo esc_url( $team_data->link ); ?>"><?php echo esc_html( $team_data->title ); ?></a>
									</span>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( $venue->_sl_address ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__address', __( 'Address', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value"><?php echo esc_html( $venue->_sl_address ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $venue->_sl_capacity ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__capacity', __( 'Capacity', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value"><?php echo esc_html( $venue->_sl_capacity ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $venue->_sl_opened ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__opened', __( 'Opened', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value"><?php echo esc_html( $venue->_sl_opened ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $venue->_sl_website ) : ?>
				<tr>
					<th scope="row" class="options-list__term"><?php echo esc_html( Sports_Leagues_Text::get_value( 'venue__website', __( 'Website', 'sports-leagues' ) ) ); ?></th>
					<td class="options-list__value"><a target="_blank" rel="nofollow" href="<?php echo esc_attr( $venue->_sl_website ); ?>">
							<?php echo esc_html( str_replace( [ 'http://', 'https://' ], '', $venue->_sl_website ) ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php
			$custom_fields = get_post_meta( $venue->ID, '_sl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<tr>
						<th scope="row" class="options-list__term"><?php echo esc_html( $field_title ); ?></th>
						<td class="options-list__value"><?php echo esc_html( $field_text ); ?></td>
					</tr>
					<?php
				}
			}
			?>

			<?php
			/**
			 * Hook: sports-leagues/tmpl-venue/fields_bottom
			 *
			 * @since 0.5.2
			 *
			 * @param WP_Post $venue
			 */
			do_action( 'sports-leagues/tmpl-venue/fields_bottom', $venue );
			?>

			</tbody>
		</table>
	</div>
</div>
<?php
if ( $data->show_selector ) {
	sports_leagues()->load_partial(
		[
			'selector_context' => 'venue',
			'selector_id'      => $data->venue_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
