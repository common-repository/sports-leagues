<?php
/**
 * The Template for displaying Player >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'player_id'           => '',
		'team_id'             => '',
		'team_title'          => '',
		'team_link'           => '',
		'national_team_title' => '',
		'national_team_link'  => '',
		'twitter'             => '',
		'youtube'             => '',
		'facebook'            => '',
		'instagram'           => '',
		'season_id'           => '',
		'show_selector'       => true,
	]
);

// Populate Player data
$player                   = (object) [];
$player->photo_id         = get_post_meta( $data->player_id, '_thumbnail_id', true );
$player->weight           = get_post_meta( $data->player_id, '_sl_weight', true );
$player->full_name        = get_post_meta( $data->player_id, '_sl_full_name', true );
$player->position         = get_post_meta( $data->player_id, '_sl_position', true );
$player->height           = get_post_meta( $data->player_id, '_sl_height', true );
$player->place_of_birth   = get_post_meta( $data->player_id, '_sl_place_of_birth', true );
$player->country_of_birth = get_post_meta( $data->player_id, '_sl_country_of_birth', true );
$player->nationality      = maybe_unserialize( get_post_meta( $data->player_id, '_sl_nationality', true ) );
$player->birth_date       = get_post_meta( $data->player_id, '_sl_date_of_birth', true );
$player->death_date       = get_post_meta( $data->player_id, '_sl_date_of_death', true );

// Socials
$player->twitter   = get_post_meta( $data->player_id, '_sl_twitter', true );
$player->youtube   = get_post_meta( $data->player_id, '_sl_youtube', true );
$player->facebook  = get_post_meta( $data->player_id, '_sl_facebook', true );
$player->instagram = get_post_meta( $data->player_id, '_sl_instagram', true );
$player->twitch    = get_post_meta( $data->player_id, '_sl_twitch', true );
$player->discord   = get_post_meta( $data->player_id, '_sl_discord', true );
$player->linkedin  = get_post_meta( $data->player_id, '_sl_linkedin', true );
$player->tiktok    = get_post_meta( $data->player_id, '_sl_tiktok', true );
$player->vk        = get_post_meta( $data->player_id, '_sl_vk', true );

/**
 * Hook: sports-leagues/tmpl-player/before_header
 *
 * @since 0.1.0
 *
 * @param object $player
 * @param object $data
 */
do_action( 'sports-leagues/tmpl-player/before_header', $player, $data );
?>
<div class="player__header anwp-section player-header d-sm-flex anwp-bg-light">

	<?php
	if ( $player->photo_id ) :
		$caption = wp_get_attachment_caption( $player->photo_id );

		/**
		 * Rendering player main photo caption.
		 *
		 * @param bool $render_main_photo_caption
		 * @param int  $player_id
		 *
		 * @since 0.5.14
		 *
		 */
		$render_main_photo_caption = apply_filters( 'sports-leagues/tmpl-player/render_main_photo_caption', true, $data->player_id );

		$image_url = wp_get_attachment_image_url( $player->photo_id, apply_filters( 'sports-leagues/person/image_size', 'medium' ) );
		?>
		<div class="player-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
			<img loading="lazy" class="anwp-object-contain mr-sm-4 player-header__logo anwp-w-120 anwp-h-120"
				src="<?php echo esc_attr( $image_url ); ?>"
				alt="<?php echo esc_attr( get_the_title( $data->player_id ) ); ?>">

			<?php if ( $render_main_photo_caption && $caption ) : ?>
				<div class="mt-1 player__main-photo-caption text-muted"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="anwp-flex-auto">
		<div class="anwp-grid-table player-header__options anwp-border-light">

			<?php if ( $player->full_name ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__full_name', __( 'Full Name', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><?php echo esc_html( $player->full_name ); ?></div>
			<?php endif; ?>

			<?php if ( $player->position ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__position', __( 'Position', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player->position ) ); ?></div>
			<?php endif; ?>

			<?php if ( $data->team_title ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__current_team', __( 'Current Team', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><a href="<?php echo esc_url( $data->team_link ); ?>"><?php echo esc_html( $data->team_title ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $data->national_team_title ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__national_team', __( 'National Team', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value">
					<a href="<?php echo esc_url( $data->national_team_link ); ?>"><?php echo esc_html( $data->national_team_title ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $player->nationality && is_array( $player->nationality ) ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__nationality', __( 'Nationality', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value">
					<?php foreach ( $player->nationality as $country_code ) : ?>
						<span class="options__flag f32" data-toggle="anwp-sl-tooltip"
							data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>">
									<span class="flag <?php echo esc_attr( $country_code ); ?>"></span>
								</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->place_of_birth ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__place_of_birth', __( 'Place of Birth', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value">
					<?php if ( $player->country_of_birth ) : ?>
						<span class="options__flag f32 mr-2"
							data-toggle="anwp-sl-tooltip"
							data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $player->country_of_birth ) ); ?>">
								<span class="flag <?php echo esc_attr( $player->country_of_birth ); ?>"></span>
						</span>
					<?php endif; ?>
					<?php echo esc_html( $player->place_of_birth ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->birth_date && 'hide' !== Sports_Leagues_Customizer::get_value( 'player', 'date_of_birth_output' ) ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__date_of_birth', __( 'Date Of Birth', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><?php echo esc_html( date_i18n( 'year' !== Sports_Leagues_Customizer::get_value( 'player', 'date_of_birth_output' ) ? get_option( 'date_format' ) : 'Y', strtotime( $player->birth_date ) ) ); ?></div>

				<?php
				if ( ! $player->death_date && 'year' !== Sports_Leagues_Customizer::get_value( 'player', 'date_of_birth_output' ) ) :
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->birth_date );
					$interval       = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
					?>
					<div class="player-header__option-title">
						<?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__age', __( 'Age', 'sports-leagues' ) ) ); ?>
					</div>
					<div class="player-header__option-value">
						<?php echo esc_html( $interval ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			if ( $player->death_date ) :
				$death_age = '';

				if ( $player->birth_date ) {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->birth_date );
					$death_age      = $birth_date_obj ? $birth_date_obj->diff( DateTime::createFromFormat( 'Y-m-d', $player->death_date ) )->y : '-';
				}

				?>
				<div class="player-header__option-title">
					<?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__date_of_death', __( 'Date Of Death', 'sports-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option-value">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $player->death_date ) ) ); ?>
					<?php echo intval( $death_age ) ? esc_html( ' (' . intval( $death_age ) . ')' ) : ''; ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->weight ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__weight', __( 'Weight', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><?php echo esc_html( $player->weight ); ?></div>
			<?php endif; ?>

			<?php if ( $player->height ) : ?>
				<div class="player-header__option-title"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__header__height', __( 'Height', 'sports-leagues' ) ) ); ?></div>
				<div class="player-header__option-value"><?php echo esc_html( $player->height ); ?></div>
			<?php endif; ?>

			<?php if ( $player->twitter || $player->facebook || $player->youtube || $player->instagram || $player->vk || $player->tiktok || $player->linkedin || $player->discord || $player->twitch ) : ?>
				<div class="player-header__option-title">
					<?php echo esc_html( Sports_Leagues_Text::get_value( 'club__header__social', __( 'Social', 'sports-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option-value d-flex flex-wrap align-items-center py-2">
					<?php if ( $player->twitter ) : ?>
						<a href="<?php echo esc_url( $player->twitter ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-twitter"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->youtube ) : ?>
						<a href="<?php echo esc_url( $player->youtube ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-youtube"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->facebook ) : ?>
						<a href="<?php echo esc_url( $player->facebook ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-facebook"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->instagram ) : ?>
						<a href="<?php echo esc_url( $player->instagram ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-instagram"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->twitch ) : ?>
						<a href="<?php echo esc_url( $player->twitch ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-twitch"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->discord ) : ?>
						<a href="<?php echo esc_url( $player->discord ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-discord"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->linkedin ) : ?>
						<a href="<?php echo esc_url( $player->linkedin ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24 anwp-text-white text-white anwp-fill-current">
								<use xlink:href="#icon-linkedin"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->tiktok ) : ?>
						<a href="<?php echo esc_url( $player->tiktok ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-tiktok"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->vk ) : ?>
						<a href="<?php echo esc_url( $player->vk ); ?>" class="anwp-link-without-effects ml-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s24">
								<use xlink:href="#icon-vk"></use>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php
			$custom_fields = get_post_meta( $data->player_id, '_sl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<div class="player-header__option-title"><?php echo esc_html( $field_title ); ?></div>
					<div class="player-header__option-value"><?php echo esc_html( $field_text ); ?></div>
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
			'selector_context' => 'player',
			'selector_id'      => $data->player_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
