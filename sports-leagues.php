<?php
/**
 * Plugin Name: AnWP Sports Leagues
 * Plugin URI:  https://anwp.pro/
 * Description: Full-featured solution for any team sports website. Manage players, games, teams, rosters, tournaments, standings and leagues with ease.
 * Version:     0.13.4
 * Author:      Andrei Strekozov <anwppro>
 * Author URI:  https://anwp.pro
 * License:     GPLv2+
 * Requires PHP: 7.0
 * Text Domain: sports-leagues
 * Domain Path: /languages
 *
 * @package Sports_Leagues
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2018-2024 Andrei Strekozov <anwppro> (email: anwp.pro@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'ANWP_SPORTS_LEAGUES_VERSION', '0.13.4' );

// Check for required PHP version
if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {

	add_action( 'admin_notices', 'sports_leagues_php_not_met' );

} else {

	// Require the main plugin class
	require_once plugin_dir_path( __FILE__ ) . 'class-sports-leagues.php';

	// Kick it off.
	add_action( 'plugins_loaded', array( sports_leagues(), 'hooks' ) );

	// Activation and deactivation.
	register_activation_hook( __FILE__, array( sports_leagues(), 'activate' ) );
	register_deactivation_hook( __FILE__, array( sports_leagues(), 'deactivate' ) );
}

/**
 * Adds a notice to the dashboard if the plugin PHP version are not met.
 *
 * @since  0.1.0
 * @return void
 */
function sports_leagues_php_not_met() {
	?>
	<div id="message" class="error">
		<p><?php esc_html_e( 'Sports Leagues cannot run on PHP versions older than 7.0. Please contact your hosting provider to update your site.', 'sports-leagues' ); ?></p>
	</div>
	<?php
}

/**
 * Grab the Sports_Leagues object and return it.
 * Wrapper for Sports_Leagues::get_instance().
 *
 * @since  0.1.0
 * @return Sports_Leagues  Singleton instance of plugin class.
 */
function sports_leagues() {
	return Sports_Leagues::get_instance();
}
