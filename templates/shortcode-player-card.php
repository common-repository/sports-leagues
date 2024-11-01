<?php
/**
 * The Template for displaying Player Card.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-player-card.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports_Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'player_id'         => '',
		'options_text'      => '',
		'context'           => 'shortcode',
		'profile_link'      => 'yes',
		'profile_link_text' => 'profile',
		'show_team'         => 0,
	]
);

if ( empty( $data->player_id ) ) {
	return;
}

// Check player exists
$player = get_post( $data->player_id );

if ( empty( $player->post_type ) || ! in_array( $player->post_type, [ 'sl_player', 'sl_referee', 'sl_staff' ], true ) ) {
	return;
}

// Nationality
$nationality = maybe_unserialize( get_post_meta( $player->ID, '_sl_nationality', true ) );

$photo_id = get_post_meta( $player->ID, '_thumbnail_id', true );
$team_id  = get_post_meta( $player->ID, '_sl_current_team', true );
?>
<div class="anwp-b-wrap">
	<div class="player-block context--<?php echo esc_attr( $data->context ); ?> border">
		<div class="d-flex align-items-center p-2 anwp-bg-light player-block__header">
			<?php if ( $photo_id ) : ?>
				<?php echo wp_get_attachment_image( $photo_id, 'medium', false, [ 'class' => 'player-block__photo mr-2' ] ); ?>
			<?php endif; ?>
			<div class="flex-grow-1">
				<div class="player-block__name h4"><?php echo esc_html( $player->post_title ); ?></div>
				<div class="player-block__extra d-flex align-items-center mt-2">
					<?php if ( ! empty( $nationality ) && is_array( $nationality ) ) : ?>
						<?php foreach ( $nationality as $country_code ) : ?>
							<span class="options__flag f32 mr-1" data-toggle="anwp-sl-tooltip"
								data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $country_code ) ); ?>"><span class="flag <?php echo esc_attr( $country_code ); ?>"></span></span>
						<?php endforeach; ?>
					<?php endif; ?>
					<span class="ml-2"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', get_post_meta( $player->ID, '_sl_position', true ) ) ); ?></span>
				</div>
			</div>
		</div>

		<div class="player-block__options">

			<?php if ( Sports_Leagues::string_to_bool( $data->show_team ) && $team_id ) : ?>
				<div class="player-block__option d-flex align-items-center border-top">
					<div class="player-block__option-label flex-grow-1"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__player_card__team', __( 'Team', 'sports-leagues' ) ) ); ?></div>
					<div class="player-block__option-value player-block__option-value--wide px-1">
						<?php
						$team_title = sports_leagues()->team->get_team_title_by_id( $team_id );
						$team_logo  = get_the_post_thumbnail_url( $team_id );
						$team_link  = get_permalink( $team_id );

						if ( $team_logo ) :
							?>
							<span class="team-logo__cover team-logo__cover--small mr-1 align-middle" style="background-image: url('<?php echo esc_url( $team_logo ); ?>')"></span>
						<?php endif; ?>

						<a class="team__link anwp-link align-middle" href="<?php echo esc_url( $team_link ); ?>">
							<?php echo esc_html( $team_title ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( trim( $data->options_text ) ) : ?>

				<?php
				$player_options = explode( '|', $data->options_text );

				foreach ( $player_options as $player_option ) :

					if ( ! trim( $player_option ) ) {
						continue;
					}

					list( $label, $value ) = explode( ':', $player_option );
					?>
					<div class="player-block__option d-flex align-items-center border-top">
						<div class="player-block__option-label flex-grow-1"><?php echo esc_html( trim( $label ) ); ?></div>
						<div class="player-block__option-value border-left"><?php echo esc_html( trim( $value ) ); ?></div>
					</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>

		<?php if ( Sports_Leagues::string_to_bool( $data->profile_link ) ) : ?>
			<div class="player-block__profile-link border-top p-2">
				<a href="<?php echo esc_url( get_permalink( $player ) ); ?>" class="btn btn-outline-secondary w-100 anwp-text-center"><?php echo esc_html( $data->profile_link_text ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
