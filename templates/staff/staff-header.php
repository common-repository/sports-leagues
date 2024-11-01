<?php
/**
 * The Template for displaying Staff >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/staff/staff-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'staff_id'      => '',
		'season_id'     => '',
		'team_title'    => '',
		'team_link'     => '',
		'show_selector' => true,
	]
);

// Populate Staff data
$staff                 = (object) [];
$staff->photo_id       = get_post_meta( $data->staff_id, '_thumbnail_id', true );
$staff->place_of_birth = get_post_meta( $data->staff_id, '_sl_place_of_birth', true );
$staff->nationality    = maybe_unserialize( get_post_meta( $data->staff_id, '_sl_nationality', true ) );
$staff->birth_date     = get_post_meta( $data->staff_id, '_sl_date_of_birth', true );
$staff->job            = get_post_meta( $data->staff_id, '_sl_job_title', true );

/**
 * Hook: sports-leagues/tmpl-staff/before_header
 *
 * @since 0.5.14
 *
 * @param object $staff
 * @param object $data
 */
do_action( 'sports-leagues/tmpl-staff/before_header', $staff, $data );
?>
<div class="staff__header anwp-section staff-header d-sm-flex anwp-bg-light">

	<?php
	if ( $staff->photo_id ) :
		$caption = wp_get_attachment_caption( $staff->photo_id );

		/**
		 * Rendering staff main photo caption.
		 *
		 * @param bool $render_main_photo_caption
		 * @param int  $staff_id
		 *
		 * @since 0.5.14
		 *
		 */
		$render_main_photo_caption = apply_filters( 'sports-leagues/tmpl-staff/render_main_photo_caption', true, $data->staff_id );

		$image_url = wp_get_attachment_image_url( $staff->photo_id, apply_filters( 'sports-leagues/person/image_size', 'medium' ) );
		?>
		<div class="staff-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
			<img loading="lazy" class="anwp-object-contain mr-sm-4 staff-header__logo anwp-w-120 anwp-h-120"
				src="<?php echo esc_attr( $image_url ); ?>"
				alt="<?php echo esc_attr( get_the_title( $data->staff_id ) ); ?>">

			<?php if ( $render_main_photo_caption && $caption ) : ?>
				<div class="mt-1 player__main-photo-caption text-muted"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

		<div class="anwp-flex-auto">
			<div class="anwp-grid-table staff-header__options anwp-border-light">

				<?php if ( $staff->job ) : ?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__job', __( 'Job', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value"><?php echo esc_html( $staff->job ); ?></div>
				<?php endif; ?>

				<?php if ( $data->team_title ) : ?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__current_team', __( 'Current Team', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value"><a href="<?php echo esc_url( $data->team_link ); ?>"><?php echo esc_html( $data->team_title ); ?></a></div>
				<?php endif; ?>

				<?php if ( $staff->nationality && is_array( $staff->nationality ) ) : ?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value">
						<?php foreach ( $staff->nationality as $country_code ) : ?>
							<span class="options__flag f32" data-toggle="anwp-sl-tooltip"
								data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>">
									<span class="flag <?php echo esc_attr( $country_code ); ?>"></span>
								</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $staff->place_of_birth ) : ?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__place_of_birth', __( 'Place of Birth', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value"><?php echo esc_html( $staff->place_of_birth ); ?></div>
				<?php endif; ?>

				<?php if ( $staff->birth_date ) : ?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__date_of_birth', __( 'Date Of Birth', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $staff->birth_date ) ) ); ?></div>

					<?php
					try {
						$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $staff->birth_date );
						$interval       = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
					} catch ( Exception $e ) {
						$interval = '-';
					}
					?>
					<div class="staff-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__header__age', __( 'Age', 'sports-leagues' ) ) ); ?></div>
					<div class="staff-header__option-value"><?php echo esc_html( $interval ); ?></div>
				<?php endif; ?>

				<?php
				$custom_fields = get_post_meta( $data->staff_id, '_sl_custom_fields', true );

				if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
					foreach ( $custom_fields as $field_title => $field_text ) {
						if ( empty( $field_text ) ) {
							continue;
						}
						?>
						<div class="staff-header__option-title"><?php echo esc_html( $field_title ); ?></div>
						<div class="staff-header__option-value"><?php echo esc_html( $field_text ); ?></div>
						<?php
					}
				}
				?>
			</div>
		</div>
</div>
<?php
if ( $data->show_selector ) {
	sports_leagues()->load_partial(
		[
			'selector_context' => 'staff',
			'selector_id'      => $data->staff_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
