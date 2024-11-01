<?php
/**
 * Shortcodes page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.5.11
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="anwp-b-wrap">
	<div class="inside p-3">
		<h1 class="mb-4">Available Shortcodes</h1>
		<?php
		/*
		|--------------------------------------------------------------------------
		| Standing Table
		|--------------------------------------------------------------------------
		*/
		?>
		<div class="bg-info text-white py-1 px-3 mb-2 d-inline-block">Standing Table</div>
		<p><code class="border py-2 px-3 bg-light d-inline-block text-dark">[sl-standing id="" title="" exclude_ids="" layout=""]</code></p>
		<ul class="list-unstyled">
			<li><strong>id</strong> – (required) Standing table ID</li>
			<li><strong>title</strong> – text title for this shortcode</li>
			<li><strong>exclude_ids</strong> – list of comma-separated team ID’s to exclude</li>
			<li><strong>layout</strong> – '' or 'mini'. Empty ('') for regular layout or mini - for small (widget) layout. </li>
		</ul>
		<hr>

		<?php
		/*
		|--------------------------------------------------------------------------
		| Teams
		|--------------------------------------------------------------------------
		*/
		?>
		<div class="bg-info text-white py-1 px-3 mb-1 d-inline-block">Teams</div>
		<p>Grid of team logos</p>
		<p>
			<code class="border py-2 px-3 bg-light d-inline-block text-dark">[sl-teams stage_id="" logo_height="50px" logo_width="50px" exclude_ids="" show_team_name="yes" layout=""]</code>
		</p>
		<ul class="list-unstyled">
			<li><strong>stage_id</strong> – (required) tournament stage ID</li>
			<li><strong>logo_height</strong> – Height value with units. Example: "50px" or "3rem".</li>
			<li><strong>logo_width</strong> – Width value with units. Example: "50px" or "3rem".</li>
			<li><strong>show_team_name</strong> – "yes" or "no". Default: "yes"</li>
			<li><strong>exclude_ids</strong> – list of comma separated club ID’s to exclude</li>
			<li><strong>layout</strong> – "2col", "3col", "4col", "6col" or " "(empty). If empty – applied ‘logo_height’ and ‘logo_width’</li>
		</ul>
		<hr>

		<?php
		/*
		|--------------------------------------------------------------------------
		| Tournament Header
		|--------------------------------------------------------------------------
		*/
		?>
		<div class="bg-info text-white py-1 px-3 d-inline-block">Tournament Header Block</div>
		<p><code class="border py-2 px-3 bg-light d-inline-block text-dark">[sl-tournament-header tournament_id="" stage_id="" title_as_link="0"]</code></p>
		<ul class="list-unstyled">
			<li><strong>tournament_id</strong> – (required) tournament ID</li>
			<li><strong>stage_id</strong> – stage ID</li>
			<li><strong>title_as_link</strong> – "0" or "1". Default: 0. Show - 1 or hide - 0.</li>
		</ul>
		<hr>

		<?php
		/*
		|--------------------------------------------------------------------------
		| Tournament List
		|--------------------------------------------------------------------------
		*/
		?>
		<div class="bg-info text-white py-1 px-3 d-inline-block">Tournament List</div>
		<p><code class="border py-2 px-3 bg-light d-inline-block text-dark">[sl-tournament-list status="" sort_by_date="" limit="0" exclude_ids="" date_from="" date_to=""]</code></p>
		<ul class="list-unstyled">
			<li><strong>status</strong> (optional) - "finished", "active", "upcoming" or empty for all (default).</li>
			<li><strong>sort_by_date</strong> (optional) - "asc", "desc" or empty to none. If you set this argument, tournaments without dates will be ignored.</li>
			<li><strong>limit</strong> (optional) - limit number of tournaments; 0 - for all.
			<li><strong>exclude_ids</strong> (optional) - comma-separated list of tournament ids to exclude.
			<li><strong>date_from</strong> (optional) - filter tournaments by date
			<li><strong>date_to</strong> (optional) - filter tournaments by date
		</ul>
		<hr>

		<?php
		/*
		|--------------------------------------------------------------------------
		| Games list
		|--------------------------------------------------------------------------
		*/
		?>
		<div class="bg-info text-white py-1 px-3 d-inline-block">Games</div>
		<p>List of games (slim game layout)</p>
		<p>
			<code class="border d-inline-block py-2 px-3 bg-light text-dark">[sl-games tournament_id="" stage_id="" season_id="" league_id="" date_from="" date_to="" venue_id="" finished="" limit=""]</code>
		</p>
		<ul class="list-unstyled">
			<li><strong>tournament_id</strong> – tournament ID</li>
			<li><strong>stage_id</strong> – tournament stage ID</li>
			<li><strong>season_id</strong> – season term ID</li>
			<li><strong>league_id</strong> – league term ID</li>
			<li><strong>date_from</strong> – games from date. Format: YYYY-MM-DD.</li>
			<li><strong>date_to</strong> – games before date. Format: YYYY-MM-DD.</li>
			<li><strong>venue_id</strong> – filter by venue ID</li>
			<li><strong>finished</strong> – "1" or "0" – show finished (1) or upcoming (0) matches. Empty to show all. Default value is " ".</li>
			<li><strong>filter_by_team</strong> – comma separated list of team IDs to filter</li>
			<li><strong>filter_by_game_day</strong> – comma separated list of game days to filter</li>
			<li><strong>days_offset</strong> – dynamic days filter. For example: "-2" from 2 days ago and newer; "2" from day after tomorrow and newer</li>
			<li><strong>limit</strong> – limit number of games. 0 for all. Default: 0</li>
			<li><strong>sort_by_date</strong> – available options: "asc" or "desc". Show oldest or latest matches. Default: "".</li>
			<li><strong>sort_by_game_day</strong> – "asc" or "desc". Set priority sorting by game day. "sort_by_date" will be ignored. Default: " "</li>
			<li><strong>group_by</strong> – Available options: "day", "month", "game_day", "stage".</li>
			<li><strong>show_team_logo</strong> – "0" or "1". Default: "1"</li>
			<li><strong>show_game_datetime</strong> – "0" or "1". Default: "1"</li>
			<li><strong>tournament_logo</strong> – "0" or "1". Show (1) or hide (0) tournament logo. Default: "1"</li>
			<li><strong>class</strong> – wrapper class. Default: "mt-4"</li>
		</ul>
		<hr>
	</div>
</div>
