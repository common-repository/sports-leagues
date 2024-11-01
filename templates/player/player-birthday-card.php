<?php
/**
 * The Template for displaying Player >> Birthday Card.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-birthday-card.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.7.0
 *
 * @version       0.10.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'ID'            => '',
		'current_team'  => '',
		'position'      => '',
		'date_of_birth' => '',
		'post_title'    => '',
		'post_type'     => '',
		'player_name'   => '',
		'photo'         => '',
		'permalink'     => true,
	]
);

if ( ! sports_leagues()->helper->validate_date( $data->date_of_birth, 'Y-m-d' ) ) {
	return;
}

$default_photo = sports_leagues()->helper->get_default_player_photo();
$position      = sports_leagues()->config->get_name_by_id( 'position', $data->position );

$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $data->date_of_birth );
$diff_date_obj  = DateTime::createFromFormat( 'Y-m-d', date( 'Y' ) . '-' . date( 'm-d', strtotime( $data->date_of_birth ) ) );
$age            = $birth_date_obj->diff( $diff_date_obj )->y;
?>
<div class="player__birthday-card player-birthday-card border">
	<div class="d-flex">
		<div class="position-relative player-birthday-card__photo-wrapper anwp-text-center d-flex align-items-center">
			<img class="player-birthday-card__photo" src="<?php echo esc_url( $data->photo ?: $default_photo ); ?>" alt="player photo">
		</div>
		<div class="d-flex flex-column flex-grow-1 player-birthday-card__meta py-2">
			<div class="player-birthday-card__name mb-1"><?php echo esc_html( $data->player_name ); ?></div>
			<div class="player-birthday-card__position"><?php echo esc_html( $position ); ?></div>
			<?php
			if ( absint( $data->current_team ) ) :
				$team = sports_leagues()->team->get_team_by_id( $data->current_team );
				if ( $team ) :
					?>
					<div class="player-birthday-card__team-wrapper d-flex align-items-center">
						<?php if ( $team->logo ) : ?>
							<span class="team-logo__cover team-logo__cover--mini mr-1 align-middle" style="background-image: url('<?php echo esc_url( $team->logo ); ?>')"></span>
						<?php endif; ?>
						<?php echo esc_html( $team->title ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<div class="player-birthday-card__date-wrapper d-flex align-items-end">
				<div class="player-birthday-card__date d-flex align-items-center">
					<svg class="anwp-icon mr-1">
						<use xlink:href="#icon-calendar"></use>
					</svg>
					<span class="player-birthday-card__date-text"><?php echo esc_html( date_i18n( 'M d', get_date_from_gmt( $data->date_of_birth, 'U' ) ) ); ?></span>
				</div>
				<div class="player-birthday-card__years ml-auto">
					<?php echo esc_html( Sports_Leagues_Text::get_value( 'player__birthdays__years', __( 'years', 'sports-leagues' ) ) ); ?>
				</div>
				<div class="player-birthday-card__age px-1 mt-n1"><?php echo absint( $age ); ?></div>
			</div>
		</div>
	</div>
</div>
