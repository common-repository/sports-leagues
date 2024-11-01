<?php
/**
 * The Template for displaying aggregate Players Stats Widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/widget-players-stats.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports_Leagues/Templates
 * @since         0.7.0
 *
 * @version       0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data->context = 'widget';

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sports_leagues()->template->shortcode_loader( 'players-stats', (array) $data );
