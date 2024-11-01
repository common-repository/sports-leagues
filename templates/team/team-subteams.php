<?php
/**
 * The Template for displaying Team >> Subteams Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-subteams.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.1
 *
 * @version       0.12.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'team_id' => '',
	]
);

$subteam_status = get_post_meta( $data->team_id, '_sl_subteams', true );
$root_team_id   = 'root' === $subteam_status ? $data->team_id : get_post_meta( $data->team_id, '_sl_root_team', true );

if ( ! absint( $root_team_id ) ) {
	return;
}

$subteam_list    = get_post_meta( $root_team_id, '_sl_subteam_list', true );
$root_team_title = get_post_meta( $root_team_id, '_sl_root_team_title', true );

if ( empty( $subteam_list ) ) {
	return;
}
?>
<div class="team-subteams anwp-bg-light px-3 pb-3 d-flex flex-wrap">
	<div class="m-1 team-subteams__item anwp-sl-btn d-flex align-items-center position-relative py-0 <?php echo 'root' === $subteam_status ? 'team-subteams__item--active anwp-cursor-default' : ''; ?>">
		<?php echo esc_html( $root_team_title ); ?>
		<?php if ( 'root' !== $subteam_status ) : ?>
			<a href="<?php echo esc_url( get_permalink( $root_team_id ) ); ?>" class="anwp-link-without-effects text-decoration-none anwp-link-cover"></a>
		<?php endif; ?>
	</div>

	<?php foreach ( $subteam_list as $subteam_item ) : ?>
		<div class="m-1 team-subteams__item anwp-sl-btn d-flex align-items-center position-relative py-0 <?php echo absint( $data->team_id ) === absint( $subteam_item['subteam'] ) ? 'team-subteams__item--active anwp-cursor-default' : ''; ?>">
			<?php echo esc_html( $subteam_item['title'] ); ?>
			<?php if ( absint( $data->team_id ) !== absint( $subteam_item['subteam'] ) ) : ?>
				<a href="<?php echo esc_url( get_permalink( $subteam_item['subteam'] ) ); ?>" class="anwp-link-without-effects text-decoration-none anwp-link-cover"></a>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
