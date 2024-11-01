<?php
/**
 * The Template for displaying Teams Widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/widget-teams.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         Sports-Leagues/Templates
 * @since           0.5.6
 * @version         0.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->stage_id ) && empty( $data->include_ids ) ) {
	return;
}

$data->context = 'widget';

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sports_leagues()->template->shortcode_loader( 'teams', $data );
