<?php
/**
 * The Template for displaying Team >> SubHeader Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-subheader.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'is_home'         => '',
		'bg_color'        => '',
		'team_color'      => '',
		'colorize_header' => '',
		'team_logo'       => '',
		'team_title'      => '',
	]
);

?>
<div class="p-2 my-2 d-flex align-items-center team_header <?php echo $data->is_home ? '' : 'flex-row-reverse'; ?>"
	style="<?php echo $data->colorize_header ? esc_html( 'background-color: ' . $data->bg_color ) : ''; ?>">
	<div class="team-logo__cover team-logo__cover--large d-block" style="background-image: url('<?php echo esc_attr( $data->team_logo ); ?>')"></div>
	<div class="mx-3 d-inline-block"><?php echo esc_html( $data->team_title ); ?></div>
</div>

