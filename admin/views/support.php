<?php
/**
 * Support page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.1.0
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}

global $wp_version, $wpdb;

$database_tables = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT table_name AS 'name'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				ORDER BY name ASC;",
		DB_NAME
	)
);
?>

<div class="about-wrap anwp-b-wrap">
	<div class="postbox">
		<div class="inside">
			<h2 class="text-left text-uppercase"><?php echo esc_html__( 'Plugin Support', 'sports-leagues' ); ?></h2>

			<hr>
			<p>
				<?php echo esc_html_x( 'If you find a bug, need help, or would like to request a feature, please visit', 'support page', 'sports-leagues' ); ?>
				- <a href="https://anwppro.userecho.com/communities/4-sports-leagues" target="_blank"><?php echo esc_html_x( 'Support forum and Knowledge bases for Sports Leagues plugin', 'support page', 'sports-leagues' ); ?></a>
			</p>

			<h3>Technical Info</h3>
			<ul>
				<li>============================================</li>
				<li>
					<b>Plugin Version:</b> Sports Leagues <?php echo esc_html( Sports_Leagues::VERSION ); ?>
				</li>
				<li>
					<b>Plugin DB version:</b> <?php echo esc_html( get_option( 'sl_db_version' ) ); ?>
				</li>

				<li>
					<b>Server Time:</b> <?php echo esc_html( date_default_timezone_get() ); ?>
				</li>

				<li>
					<b>WP Time:</b> <?php echo esc_html( get_option( 'timezone_string' ) ); ?>
				</li>

				<li>
					<b>WordPress version:</b> <?php echo esc_html( $wp_version ); ?>
				</li>

				<li>
					<b>PHP version:</b> <?php echo esc_html( phpversion() ); ?>
				</li>

				<li>
					<b>Active Plugins:</b>
					<?php
					foreach ( get_option( 'active_plugins' ) as $value ) {
						$string = explode( '/', $value );
						echo '<br>--- ' . esc_html( $string[0] );
					}
					?>
				</li>

				<li>
					<b><?php echo esc_html_x( 'List of DB tables', 'support page', 'sports-leagues' ); ?>:</b><br>
					<?php
					if ( ! empty( $database_tables ) && is_array( $database_tables ) ) {
						$database_tables = wp_list_pluck( $database_tables, 'name' );

						if ( is_array( $database_tables ) ) {
							echo esc_html( implode( ', ', $database_tables ) );
						}
					}
					?>
				</li>
				<li>============================================</li>
			</ul>

		</div>
	</div>
</div>
