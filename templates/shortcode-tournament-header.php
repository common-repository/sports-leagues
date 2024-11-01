<?php
/**
 * The Template for displaying Tournament Header Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-tournament-header.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->tournament_id ) ) {
	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'title_as_link'   => 0,
		'tournament_id'   => '',
		'stage_id'        => '',
		'season_selector' => 0,
		'title'           => '',
		'title_field'     => '',
		'transparent_bg'  => 0,
	]
);

$logo = get_the_post_thumbnail_url( $data->tournament_id );

/*
|--------------------------------------------------------------------------
| Prepare link
|--------------------------------------------------------------------------
*/
$link_post_id = 0;

if ( Sports_Leagues::string_to_bool( $data->title_as_link ) ) {
	$link_post_id = $data->stage_id ?: $data->tournament_id;
}

$tournament_post = get_post( $data->tournament_id );
$season_selector = Sports_Leagues::string_to_bool( $data->season_selector );
$season_options  = [];

/*
|--------------------------------------------------------------------
| Season Selector
|--------------------------------------------------------------------
*/
$tournament_object = sports_leagues()->tournament->get_tournament( $data->tournament_id );

if ( $season_selector && absint( $tournament_object->league_id ) && absint( $tournament_object->season_id ) ) {

	$season_all = [];

	foreach ( sports_leagues()->season->get_seasons_list() as $season_item ) {
		$season_all[ $season_item->id ] = $season_item->title;
	}

	foreach ( sports_leagues()->tournament->get_tournaments() as $tournament_item ) {
		if ( absint( $tournament_object->league_id ) === absint( $tournament_item->league_id ) && isset( $season_all[ $tournament_item->season_id ] ) ) {
			$season_options[] = [
				'id'        => $tournament_item->id,
				'season'    => $season_all[ $tournament_item->season_id ],
				'permalink' => get_permalink( $tournament_item->id ),
			];
		}
	}

	if ( ! empty( $season_options ) ) {
		$season_options = wp_list_sort( $season_options, 'season', 'DESC' );
	}
}

$tournament_title = empty( $data->title ) ? ( 'league' === $data->title_field ? $tournament_object->league_text : $tournament_post->post_title ) : $data->title;
?>
<div class="anwp-b-wrap sl-tournament-header <?php echo esc_attr( $link_post_id ? 'sl-tournament-header__link' : '' ); ?>">
	<div class="position-relative sl-tournament-header__wrapper anwp-sl-border">
		<div class="d-flex align-items-center">
			<?php if ( $logo ) : ?>
				<div class="sl-tournament-header__logo-wrapper">
					<img class="sl-tournament-header__logo anwp-object-contain" loading="lazy"
						src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $tournament_post->post_title ); ?>">
				</div>
			<?php endif; ?>
			<div class="sl-tournament-header__title-wrapper">
				<div class="sl-tournament-header__title"><?php echo esc_html( $tournament_title ); ?></div>
				<div class="sl-tournament-header__subtitle anwp-opacity-70"><?php echo esc_html( get_post_meta( $tournament_post->ID, '_sl_subtitle', true ) ); ?></div>

				<?php if ( ! empty( $season_options ) && count( $season_options ) > 1 ) : ?>
					<div class="sl-tournament-header__selector my-2">
						<select class="anwp-season-dropdown anwp-text-sm">
							<?php foreach ( $season_options as $season_item ) : ?>
								<option <?php selected( $season_item['id'], $tournament_object->id ); ?>
									data-href="<?php echo esc_url( $season_item['permalink'] ); ?>"
									value="<?php echo esc_attr( $season_item['id'] ); ?>"><?php echo esc_attr( $season_item['season'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( $tournament_post->_sl_date_from && $tournament_post->_sl_date_to ) : ?>
					<div class="sl-tournament-header__date d-inline-block anwp-opacity-70 d-flex flex-wrap align-items-center">
						<svg class="anwp-icon d-inline-block mr-2 anwp-fill-current"><use xlink:href="#icon-calendar"></use></svg>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $tournament_post->_sl_date_from ) ) ); ?>
						<span class="mx-2">-</span>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $tournament_post->_sl_date_to ) ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $link_post_id ) : ?>
				<a href="<?php echo esc_url( get_permalink( $link_post_id ) ); ?>" class="anwp-link-cover anwp-link-without-effects"></a>
			<?php endif; ?>
		</div>
	</div>
</div>
