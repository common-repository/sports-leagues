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
 * @version       0.11.0
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
		'title_as_link' => 0,
		'tournament_id' => '',
		'stage_id'      => '',
		'breadcrumbs'   => 0,
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
	$link_post_id = $data->stage_id ? : $data->tournament_id;
}

$tournament_post = get_post( $data->tournament_id );
$stage_post      = intval( $data->stage_id ) ? get_post( $data->stage_id ) : false;
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
				<div class="sl-tournament-header__title"><?php echo $tournament_post ? esc_html( $tournament_post->post_title ) : ''; ?></div>
				<div class="sl-tournament-header__subtitle anwp-opacity-70"><?php echo esc_html( get_post_meta( $tournament_post->ID, '_sl_subtitle', true ) ); ?></div>

				<?php if ( $stage_post ) : ?>
					<span class="sl-tournament-header__stage-title d-inline-block px-2"><?php echo $stage_post ? esc_html( $stage_post->post_title ) : ''; ?></span>
				<?php elseif ( $tournament_post->_sl_date_from && $tournament_post->_sl_date_to ) : ?>
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

	<?php if ( Sports_Leagues::string_to_bool( $data->breadcrumbs ) ) : ?>
		<nav aria-label="breadcrumb">
			<?php if ( $stage_post ) : ?>
				<ol class="breadcrumb mb-0 mt-n2 p-1 px-2 small anwp-bg-light border ml-0">
					<li class="breadcrumb-item d-flex align-items-center">
						<svg class="anwp-icon anwp-icon--s14 mr-2">
							<use xlink:href="#icon-home"></use>
						</svg>
						<a class="anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $tournament_post ) ); ?>">
							<?php echo $tournament_post ? esc_html( $tournament_post->post_title ) : ''; ?>
						</a>
					</li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $stage_post ? esc_html( $stage_post->post_title ) : ''; ?></li>
				</ol>
			<?php else : ?>
				<ol class="breadcrumb mb-0 mt-n2 p-1 px-2 small anwp-bg-light border ml-0">
					<li class="breadcrumb-item d-flex align-items-center mr-1">
						<svg class="anwp-icon anwp-icon--s14 mr-2">
							<use xlink:href="#icon-home"></use>
						</svg>
						<a class="anwp-link-without-effects"><?php echo $tournament_post ? esc_html( $tournament_post->post_title . ':' ) : ''; ?></a>
					</li>
					<?php foreach ( sports_leagues()->tournament->get_tournament_stages( $tournament_post->ID ) as $tournament_stage ) : ?>
						<?php if ( ! empty( $tournament_stage ) && ! empty( $tournament_stage->id ) ) : ?>
							<li class="breadcrumb-item anwp-breadcrumb-list d-flex align-items-center"><a class="d-inline-block anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $tournament_stage->id ) ); ?>"><?php echo esc_html( $tournament_stage->title ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

</div>
