<?php
/**
 * Import Data page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.10.2
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

$import_options = sports_leagues()->data->get_import_options();

/*
|--------------------------------------------------------------------
| Players
|--------------------------------------------------------------------
*/
$columns_player = [
	[
		'name'    => 'player_name',
		'title'   => __( 'Player Name', 'sports-leagues' ),
		'sl_tool' => 'name',
		'visible' => true,
		'width'   => 120,
	],
	[
		'name'  => 'short_name',
		'title' => __( 'Short Name', 'sports-leagues' ),
		'width' => 120,
	],
	[
		'name'  => 'full_name',
		'title' => __( 'Full Name', 'sports-leagues' ),
		'width' => 150,
	],
	[
		'name'    => 'weight',
		'title'   => __( 'Weight', 'sports-leagues' ),
		'visible' => true,
	],
	[
		'name'    => 'height',
		'title'   => __( 'Height', 'sports-leagues' ),
		'visible' => true,
	],
	[
		'name'         => 'position',
		'title'        => __( 'Position', 'sports-leagues' ),
		'visible'      => true,
		'autocomplete' => true,
		'width'        => 150,
		'source'       => $import_options['positions'],
		'type'         => 'dropdown',
	],
	[
		'name'         => 'current_team',
		'title'        => __( 'Current Team', 'sports-leagues' ),
		'type'         => 'dropdown',
		'source'       => $import_options['teams'],
		'autocomplete' => true,
	],
	[
		'name'         => 'national_team',
		'title'        => __( 'National Team', 'sports-leagues' ),
		'type'         => 'dropdown',
		'source'       => $import_options['teams'],
		'autocomplete' => true,
	],
	[
		'name'  => 'place_of_birth',
		'title' => __( 'Place of Birth', 'sports-leagues' ),
	],
	[
		'name'  => 'date_of_birth',
		'title' => __( 'Date of Birth', 'sports-leagues' ),
		'mask'  => 'yyyy-mm-dd',
		'type'  => 'numeric',
	],
	[
		'name'  => 'date_of_death',
		'title' => __( 'Date of Death', 'sports-leagues' ),
		'mask'  => 'yyyy-mm-dd',
		'type'  => 'numeric',
	],
	[
		'name'         => 'country_of_birth',
		'title'        => __( 'Country of Birth', 'sports-leagues' ),
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'autocomplete' => true,
	],
	[
		'name'         => 'nationality_1',
		'title'        => __( 'Nationality', 'sports-leagues' ),
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'autocomplete' => true,
	],
	[
		'name'         => 'nationality_2',
		'title'        => __( 'Nationality 2', 'sports-leagues' ),
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'autocomplete' => true,
	],
	[
		'name'  => 'bio',
		'title' => __( 'Bio', 'sports-leagues' ),
	],
	[
		'name'    => 'player_id',
		'title'   => __( 'Player ID', 'sports-leagues' ),
		'type'    => 'numeric',
		'sl_tool' => 'id',
	],
	[
		'name'    => 'player_external_id',
		'title'   => __( 'Player External ID', 'sports-leagues' ),
		'sl_tool' => 'external_id',
	],
];

$player_custom_fields = sports_leagues()->get_option_value( 'player_custom_fields' );

if ( ! empty( $player_custom_fields ) && is_array( $player_custom_fields ) ) {
	foreach ( $player_custom_fields as $custom_field ) {

		$columns_player[] = [
			'name'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}

/*
|--------------------------------------------------------------------
| Venues
|--------------------------------------------------------------------
*/
$columns_venue = [
	[
		'name'    => 'venue_title',
		'title'   => __( 'Venue Title', 'sports-leagues' ),
		'visible' => true,
		'sl_tool' => 'name',
	],
	[
		'name'  => 'address',
		'title' => __( 'Address', 'sports-leagues' ),
	],
	[
		'name'    => 'city',
		'title'   => __( 'City', 'sports-leagues' ),
		'visible' => true,
	],
	[
		'name'  => 'website',
		'title' => __( 'Website', 'sports-leagues' ),
	],
	[
		'name'  => 'capacity',
		'title' => __( 'Capacity', 'sports-leagues' ),
	],
	[
		'name'  => 'opened',
		'title' => __( 'Opened', 'sports-leagues' ),
	],
	[
		'name'  => 'description',
		'title' => __( 'Description', 'sports-leagues' ),
	],
	[
		'name'    => 'venue_id',
		'title'   => __( 'Venue ID', 'sports-leagues' ),
		'type'    => 'numeric',
		'sl_tool' => 'id',
	],
	[
		'name'    => 'venue_external_id',
		'title'   => __( 'Venue External ID', 'sports-leagues' ),
		'sl_tool' => 'external_id',
	],
];

$venue_custom_fields = sports_leagues()->get_option_value( 'venue_custom_fields' );

if ( ! empty( $venue_custom_fields ) && is_array( $venue_custom_fields ) ) {
	foreach ( $venue_custom_fields as $custom_field ) {

		$columns_venue[] = [
			'name'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}

/*
|--------------------------------------------------------------------
| Staff
|--------------------------------------------------------------------
*/
$columns_staff = [
	[
		'name'    => 'staff_name',
		'title'   => __( 'Staff Name', 'sports-leagues' ),
		'visible' => true,
		'sl_tool' => 'name',
	],
	[
		'name'  => 'short_name',
		'title' => __( 'Short Name', 'sports-leagues' ),
	],
	[
		'name'         => 'current_team',
		'title'        => __( 'Current Team', 'sports-leagues' ),
		'type'         => 'dropdown',
		'source'       => $import_options['teams'],
		'autocomplete' => true,
	],
	[
		'name'    => 'job_title',
		'title'   => __( 'Job Title', 'sports-leagues' ),
		'visible' => true,
	],
	[
		'name'  => 'place_of_birth',
		'title' => __( 'Place of Birth', 'sports-leagues' ),
	],
	[
		'name'  => 'date_of_birth',
		'title' => __( 'Date of Birth', 'sports-leagues' ),
		'type'  => 'numeric',
		'mask'  => 'yyyy-mm-dd',
	],
	[
		'name'         => 'nationality_1',
		'title'        => __( 'Nationality', 'sports-leagues' ),
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'autocomplete' => true,
	],
	[
		'name'         => 'nationality_2',
		'title'        => __( 'Nationality 2', 'sports-leagues' ),
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'autocomplete' => true,
	],
	[
		'name'  => 'bio',
		'title' => __( 'Bio', 'sports-leagues' ),
	],
	[
		'name'    => 'staff_id',
		'title'   => __( 'Staff ID', 'sports-leagues' ),
		'sl_tool' => 'id',
		'type'    => 'numeric',
	],
	[
		'name'    => 'staff_external_id',
		'title'   => __( 'Staff External ID', 'sports-leagues' ),
		'sl_tool' => 'external_id',
	],
];

$staff_custom_fields = sports_leagues()->get_option_value( 'staff_custom_fields' );

if ( ! empty( $staff_custom_fields ) && is_array( $staff_custom_fields ) ) {
	foreach ( $staff_custom_fields as $custom_field ) {
		$columns_staff[] = [
			'name'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}

/*
|--------------------------------------------------------------------
| Teams
|--------------------------------------------------------------------
*/
$columns_club = [
	[
		'name'    => 'team_title',
		'title'   => __( 'Team Title', 'sports-leagues' ),
		'visible' => true,
		'sl_tool' => 'name',
	],
	[
		'name'  => 'abbreviation',
		'title' => __( 'Abbreviation', 'sports-leagues' ),
	],
	[
		'name'    => 'city',
		'title'   => __( 'City', 'sports-leagues' ),
		'visible' => true,
	],
	[
		'name'         => 'country',
		'title'        => __( 'Country', 'sports-leagues' ),
		'type'         => 'dropdown',
		'autocomplete' => true,
		'source'       => $import_options['countries'],
	],
	[
		'name'  => 'address',
		'title' => __( 'Address', 'sports-leagues' ),
	],
	[
		'name'  => 'website',
		'title' => __( 'Website', 'sports-leagues' ),
	],
	[
		'name'  => 'founded',
		'title' => __( 'Founded', 'sports-leagues' ),
	],
	[
		'name'   => 'is_national_team',
		'title'  => __( 'National Team', 'sports-leagues' ),
		'type'   => 'dropdown',
		'source' => [ 'yes', 'no' ],
	],
	[
		'name'    => 'team_id',
		'title'   => __( 'Team ID', 'sports-leagues' ),
		'sl_tool' => 'id',
		'type'    => 'numeric',
	],
	[
		'name'    => 'team_external_id',
		'title'   => __( 'Team External ID', 'sports-leagues' ),
		'sl_tool' => 'external_id',
	],
];

$team_custom_fields = sports_leagues()->get_option_value( 'team_custom_fields' );

if ( ! empty( $team_custom_fields ) && is_array( $team_custom_fields ) ) {
	foreach ( $team_custom_fields as $custom_field ) {

		$columns_club[] = [
			'name'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}

/*
|--------------------------------------------------------------------
| Officials
|--------------------------------------------------------------------
*/
$columns_official = [
	[
		'name'    => 'official_name',
		'title'   => __( 'Official Name', 'sports-leagues' ),
		'visible' => true,
		'sl_tool' => 'name',
	],
	[
		'name'  => 'short_name',
		'title' => __( 'Short Name', 'sports-leagues' ),
	],
	[
		'name'  => 'place_of_birth',
		'title' => __( 'Place of Birth', 'sports-leagues' ),
	],
	[
		'name'  => 'date_of_birth',
		'title' => __( 'Date of Birth', 'sports-leagues' ),
		'mask'  => 'yyyy-mm-dd',
		'type'  => 'numeric',
	],
	[
		'name'         => 'nationality_1',
		'title'        => __( 'Nationality', 'sports-leagues' ),
		'type'         => 'dropdown',
		'source'       => $import_options['countries'],
		'autocomplete' => true,
	],
	[
		'name'         => 'nationality_2',
		'title'        => __( 'Nationality 2', 'sports-leagues' ),
		'type'         => 'dropdown',
		'source'       => $import_options['countries'],
		'autocomplete' => true,
	],
	[
		'name'  => 'bio',
		'title' => __( 'Bio', 'sports-leagues' ),
	],
	[
		'name'    => 'official_id',
		'title'   => __( 'Official ID', 'sports-leagues' ),
		'sl_tool' => 'id',
		'type'    => 'numeric',
	],
	[
		'name'    => 'official_external_id',
		'title'   => __( 'Official External ID', 'sports-leagues' ),
		'sl_tool' => 'external_id',
	],
];

$official_custom_fields = sports_leagues()->get_option_value( 'official_custom_fields' );

if ( ! empty( $official_custom_fields ) && is_array( $official_custom_fields ) ) {
	foreach ( $official_custom_fields as $custom_field ) {

		$columns_official[] = [
			'name'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}

/*
|--------------------------------------------------------------------
| Generate Options
|--------------------------------------------------------------------
*/
$available_options = [
	[
		'slug'    => 'players',
		'title'   => __( 'players', 'sports-leagues' ),
		'columns' => $columns_player,
	],
	[
		'slug'    => 'teams',
		'title'   => __( 'teams', 'sports-leagues' ),
		'columns' => $columns_club,
	],
	[
		'slug'    => 'venues',
		'title'   => __( 'venues', 'sports-leagues' ),
		'columns' => $columns_venue,
	],
	[
		'slug'    => 'staff',
		'title'   => __( 'staff', 'sports-leagues' ),
		'columns' => $columns_staff,
	],
	[
		'slug'    => 'officials',
		'title'   => __( 'officials', 'sports-leagues' ),
		'columns' => $columns_official,
	],
];
?>
<script type="text/javascript">
	window._slImportTool            = {};
	window._slImportTool.pages      = <?php echo wp_json_encode( $available_options ); ?>;
	window._slImportTool.rest_root  = '<?php echo esc_url_raw( rest_url() ); ?>';
	window._slImportTool.rest_nonce = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';
</script>
<style>
    table.jexcel {
        table-layout: auto;
    }

    table.jexcel thead td[data-x] {
        padding-left: 20px;
        padding-right: 20px;
        white-space: nowrap;
    }

    table.jexcel td.jexcel_dropdown {
        padding-right: 35px;
        text-align: left;
    }

    .sl-toggle-green-blue {
        --toggle-bg-on: #00733a;
        --toggle-border-on: #00733a;
        --toggle-bg-off: #0d37a1;
        --toggle-border-off: #0d37a1;
        --toggle-text-off: #fff;
    }

    .anwp-toggle-w-80 .toggle {
        width: 80px !important;
    }

    .toggle-label {
        width: auto !important;
        padding-left: 5px;
        padding-right: 5px;
    }

    .jexcel > tbody > tr > td.readonly {
        color: rgb(0 0 0 / 50%) !important;
    }
</style>
<div class="alert alert-info my-4" role="alert">
	<div class="d-block mb-1 w-100">
		<?php echo esc_html__( 'Select import type. Then copy and paste data from your source into the table below.', 'sports-leagues' ); ?>
	</div>
	<div class="d-flex align-items-center">
		<svg class="anwp-icon anwp-icon--s14 anwp-icon--octi mr-1">
			<use xlink:href="#icon-info"></use>
		</svg>
		<a href="https://anwppro.userecho.com/knowledge-bases/6/articles/86-data-import-tool" target="_blank"><?php echo esc_html__( 'more info', 'sports-leagues' ); ?></a><br>
	</div>
</div>

<div id="anwp-sl-batch-import-tool-app"></div>
