<?php
/**
 * The Template for displaying Games widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/widget-games.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports_Leagues/Templates
 * @since         0.5.9
 *
 * @version       0.9.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'tournament_id'      => '',
		'stage_id'           => '',
		'finished'           => '',
		'limit'              => '',
		'group_by'           => '',
		'sort_by_date'       => '',
		'sort_by_game_day'   => '',
		'filter_by_team'     => '',
		'filter_by_game_day' => '',
		'date_from'          => '',
		'date_to'            => '',
		'days_offset'        => '',
		'days_offset_to'     => '',
		'show_team_logo'     => '1',
		'show_game_datetime' => '1',
		'tournament_logo'    => '0',
		'link_target'        => '',
		'link_text'          => '',
		'class'              => 'mt-2',
		'game_layout'        => 'classic',
		'context'            => 'widget',
	]
);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sports_leagues()->template->shortcode_loader( 'games', $data );

if ( ! empty( $data->link_text ) && ! empty( $data->link_target ) ) : ?>
	<div class="anwp-b-wrap">
		<p class="anwp-text-center mt-1">
			<a class="btn btn-sm btn-outline-secondary w-100" target="_blank" href="<?php echo esc_url( $data->link_target ); ?>"><?php echo esc_html( $data->link_text ); ?></a>
		</p>
	</div>
	<?php
endif;
