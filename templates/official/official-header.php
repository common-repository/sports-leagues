<?php
/**
 * The Template for displaying Official >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/official/official-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.13
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
		'official_id'   => '',
		'season_id'     => '',
		'show_selector' => true,
	]
);

// Populate Official data
$official                 = (object) [];
$official->photo_id       = get_post_meta( $data->official_id, '_thumbnail_id', true );
$official->group          = get_post_meta( $data->official_id, '_sl_group', true );
$official->place_of_birth = get_post_meta( $data->official_id, '_sl_place_of_birth', true );
$official->nationality    = maybe_unserialize( get_post_meta( $data->official_id, '_sl_nationality', true ) );
$official->birth_date     = get_post_meta( $data->official_id, '_sl_date_of_birth', true );

/**
 * Hook: sports-leagues/tmpl-official/before_header
 *
 * @param object $official
 * @param object $data
 *
 * @since 0.5.13
 *
 */
do_action( 'sports-leagues/tmpl-official/before_header', $official, $data );
?>
	<div class="official__header official-header anwp-section d-sm-flex anwp-bg-light">

		<?php
		if ( $official->photo_id ) :
			$caption = wp_get_attachment_caption( $official->photo_id );

			/**
			 * Rendering official main photo caption.
			 *
			 * @param bool $render_main_photo_caption
			 * @param int  $official_id
			 *
			 * @since 0.5.14
			 *
			 */
			$render_main_photo_caption = apply_filters( 'sports-leagues/tmpl-official/render_main_photo_caption', true, $data->official_id );

			$image_url = wp_get_attachment_image_url( $official->photo_id, apply_filters( 'sports-leagues/person/image_size', 'medium' ) );
			?>
			<div class="official-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
				<img loading="lazy" class="anwp-object-contain mr-sm-4 official-header__logo anwp-w-120 anwp-h-120"
					src="<?php echo esc_attr( $image_url ); ?>"
					alt="<?php echo esc_attr( get_the_title( $data->official_id ) ); ?>">

				<?php if ( $render_main_photo_caption && $caption ) : ?>
					<div class="mt-1 player__main-photo-caption text-muted"><?php echo esc_html( $caption ); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="anwp-flex-auto">
			<div class="anwp-grid-table official-header__options anwp-border-light">

				<?php if ( $official->group ) : ?>
					<div class="official-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'official__header__group', _x( 'Group', 'official group label', 'sports-leagues' ) ) ); ?></div>
					<div class="official-header__option-value"><?php echo esc_html( $official->group ); ?></div>
				<?php endif; ?>

				<?php if ( $official->nationality && is_array( $official->nationality ) ) : ?>
					<div class="official-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'official__header__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></div>
					<div class="official-header__option-value">
						<?php foreach ( $official->nationality as $country_code ) : ?>
							<span class="options__flag f32" data-toggle="anwp-sl-tooltip"
								data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>">
									<span class="flag <?php echo esc_attr( $country_code ); ?>"></span>
								</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $official->place_of_birth ) : ?>
					<div class="official-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'official__header__place_of_birth', __( 'Place of Birth', 'sports-leagues' ) ) ); ?></div>
					<div class="official-header__option-value"><?php echo esc_html( $official->place_of_birth ); ?></div>
				<?php endif; ?>

				<?php if ( $official->birth_date ) : ?>
					<div class="official-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'official__header__date_of_birth', __( 'Date Of Birth', 'sports-leagues' ) ) ); ?></div>
					<div class="official-header__option-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $official->birth_date ) ) ); ?></div>

					<?php
					try {
						$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $official->birth_date );
						$interval       = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
					} catch ( Exception $e ) {
						$interval = '-';
					}
					?>
					<div class="official-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'official__header__age', __( 'Age', 'sports-leagues' ) ) ); ?></div>
					<div class="official-header__option-value"><?php echo esc_html( $interval ); ?></div>
				<?php endif; ?>

				<?php
				$custom_fields = get_post_meta( $data->official_id, '_sl_custom_fields', true );

				if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
					foreach ( $custom_fields as $field_title => $field_text ) {
						if ( empty( $field_text ) ) {
							continue;
						}
						?>
							<div class="official-header__option-title"><?php echo esc_html( $field_title ); ?></div>
							<div class="official-header__option-value"><?php echo esc_html( $field_text ); ?></div>
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
			'selector_context' => 'official',
			'selector_id'      => $data->official_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
