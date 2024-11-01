<?php
/**
 * The Template for displaying Team >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'abbr'          => '',
		'team_id'       => '',
		'city'          => '',
		'nationality'   => '',
		'address'       => '',
		'website'       => '',
		'founded'       => '',
		'venue'         => '',
		'twitter'       => '',
		'youtube'       => '',
		'facebook'      => '',
		'instagram'     => '',
		'vk'            => '',
		'tiktok'        => '',
		'linkedin'      => '',
		'discord'       => '',
		'twitch'        => '',
		'season_id'     => '',
		'show_selector' => true,
		'conference'    => '',
		'division'      => '',
	]
);

$team_post = get_post( $data->team_id );
$team_data = sports_leagues()->team->get_team_by_id( $data->team_id );

sports_leagues()->season->get_max_season_id();

/**
 * Hook: sports-leagues/tmpl-team/before_header
 *
 * @param object $data
 *
 * @since 0.1.0
 *
 */
do_action( 'sports-leagues/tmpl-team/before_header', $data );
?>
<div class="team__header team-header anwp-section d-sm-flex anwp-bg-light">

	<?php if ( $team_data->logo ) : ?>
		<div class="team-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
			<img loading="lazy" class="anwp-object-contain mr-sm-4 team-header__logo anwp-w-120 anwp-h-120"
				src="<?php echo esc_attr( $team_data->logo ); ?>"
				alt="<?php echo esc_attr( $team_data->title ); ?>">
		</div>
	<?php endif; ?>

	<div class="anwp-flex-auto">
		<div class="anwp-grid-table team-header__options anwp-border-light">

			<?php
			/**
			 * Fires before fields in Team header.
			 *
			 * @param WP_Post $team_post
			 * @param array   $data
			 *
			 * @since 0.1.0
			 *
			 */
			do_action( 'sports-leagues/tmpl-team/fields_top', $team_post, $data );
			?>

			<?php if ( $data->city ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__city', __( 'City', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value"><?php echo esc_html( $data->city ); ?></div>
			<?php endif; ?>

			<?php if ( $data->nationality ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__country', __( 'Country', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value">
					<div class="options__flag f32" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $data->nationality ) ); ?>">
						<span class="flag <?php echo esc_attr( $data->nationality ); ?>"></span>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $data->address ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__address', __( 'Address', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value"><?php echo esc_html( $data->address ); ?></div>
			<?php endif; ?>

			<?php if ( $data->website ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__website', __( 'Website', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value anwp-break-words">
					<a target="_blank" rel="nofollow" href="<?php echo esc_attr( $data->website ); ?>">
						<?php echo esc_html( trim( str_replace( [ 'http://', 'https://' ], '', $data->website ), '/' ) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $data->founded ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__founded', __( 'Founded', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value"><?php echo esc_html( $data->founded ); ?></div>
			<?php endif; ?>

			<?php if ( $data->conference ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__conference', __( 'Conference', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value"><?php echo esc_html( $data->conference ); ?></div>
			<?php endif; ?>

			<?php if ( $data->division ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__division', __( 'Division', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value"><?php echo esc_html( $data->division ); ?></div>
			<?php endif; ?>

			<?php if ( $data->venue ) : ?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__venue', __( 'Venue', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value">
					<a href="<?php echo esc_url( get_permalink( (int) $data->venue ) ); ?>"><?php echo esc_html( get_the_title( (int) $data->venue ) ); ?></a>
				</div>
			<?php endif; ?>

			<?php
			$custom_fields = get_post_meta( $team_post->ID, '_sl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<div class="team-header__option-title"><?php echo esc_html( $field_title ); ?></div>
					<div class="team-header__option-value"><?php echo do_shortcode( esc_html( $field_text ) ); ?></div>
					<?php
				}
			}

			if ( $data->twitter || $data->facebook || $data->youtube || $data->instagram || $data->twitch || $data->discord || $data->linkedin || $data->vk || $data->tiktok ) :
				?>
				<div class="team-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__header__social', __( 'Social', 'sports-leagues' ) ) ); ?></div>
				<div class="team-header__option-value d-flex flex-wrap align-items-center py-2">
					<?php if ( $data->twitter ) : ?>
						<a href="<?php echo esc_url( $data->twitter ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-twitter"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->youtube ) : ?>
						<a href="<?php echo esc_url( $data->youtube ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-youtube"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->facebook ) : ?>
						<a href="<?php echo esc_url( $data->facebook ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-facebook"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->instagram ) : ?>
						<a href="<?php echo esc_url( $data->instagram ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-instagram"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->twitch ) : ?>
						<a href="<?php echo esc_url( $data->twitch ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-twitch"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->discord ) : ?>
						<a href="<?php echo esc_url( $data->discord ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-discord"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->linkedin ) : ?>
						<a href="<?php echo esc_url( $data->linkedin ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24 anwp-text-white text-white anwp-fill-current">
								<use xlink:href="#icon-linkedin"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->tiktok ) : ?>
						<a href="<?php echo esc_url( $data->tiktok ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-tiktok"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->vk ) : ?>
						<a href="<?php echo esc_url( $data->vk ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-vk"></use>
							</svg>
						</a>
					<?php endif; ?>
				</div>
				<?php
			endif;

			/**
			 * Fires after fields in the Team header.
			 *
			 * @param WP_Post $team_post
			 * @param array   $data
			 *
			 * @since 0.1.0
			 *
			 */
			do_action( 'sports-leagues/tmpl-team/fields_bottom', $team_post, $data );
			?>
		</div>
		<?php
		/**
		 * Fires at the bottom of Team header.
		 *
		 * @param WP_Post $team_post
		 * @param array   $data
		 *
		 * @since 0.1.0
		 *
		 */
		do_action( 'sports-leagues/tmpl-team/header_bottom', $team_post, $data );
		?>
	</div>
</div>
<?php
if ( get_post_meta( $data->team_id, '_sl_subteams', true ) ) {
	sports_leagues()->load_partial(
		[
			'team_id' => $data->team_id,
		],
		'team/team-subteams'
	);
}

if ( $data->show_selector ) {
	sports_leagues()->load_partial(
		[
			'selector_context' => 'team',
			'selector_id'      => $data->team_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
