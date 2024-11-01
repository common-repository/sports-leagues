<?php
/**
 * Tutorial page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.1.0
 *
 * @package    Sports_Leagues
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}
?>

<div class="wrap">

	<h1 class="entry-title text-primary mb-5">Quick Start</h1>

	<div class="anwp-b-wrap">
		<div class="my-4 p-3 bg-white">
			<div class="my-2">
				More tutorials at - <a href="https://anwppro.userecho.com/communities/4-sports-leagues" target="_blank"><?php echo esc_html_x( 'Support forum and Knowledge bases for Sports Leagues plugin', 'support page', 'sports-leagues' ); ?></a>
			</div>

			<div class="entry-content">
				<div class="alert alert-info" role="alert">
					<p><b>Plugin Requires:</b></p>
					<ul>
						<li>PHP 5.6+</li>
						<li>WordPress 4.7+</li>
						<li>Enabled&nbsp;WordPress REST API</li>
						<li><a href="https://wordpress.org/plugins/cmb2/">CMB2 plugin</a></li>
					</ul>
				</div>

				<?php
				/*
				|--------------------------------------------------------------------
				| Preparation
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Preparation</div>
					<h2>Install required CMB2 plugin</h2>
				</div>
				<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/YkPuXEVkmD8?rel=0&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 1 - Create league
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 1</div>
					<h2>Create league</h2>
				</div>
				<p>Go to the <b>"Tournaments"</b> &gt;&gt; <b>"Leagues"</b> and create a new league.</p>
				<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/M1W56LM975k?rel=0&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 2 - Create season
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 2</div>
					<h2>Create season</h2>
				</div>
				<p>Go to the <b>"Tournaments"</b> &gt;&gt; <b>"Seasons"</b> and create a new season.</p>
				<p>Recommended season name is "YYYY" or "YYYY-YYYY".</p>
				<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/sw7WPowgr2o?rel=0&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 3 - Create teams
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 3</div>
					<h3 class="col">Create teams</h3>
				</div>
				<p>Recommended using batch creation tool. It will help you to create a lot of teams faster.</p>
				<p>Go to the “Sports Leagues” &gt;&gt; “Import Data”. Set the “teams” option in the top dropdown. Apply columns you like.</p>
				<p>Then you can enter team data manually or copy from Excel-like spreadsheets.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/step_3.png' ) ); ?>"></p>
				<p>( Optional ) You can add additional team data in “Sports Leagues” &gt;&gt; “Teams” menu.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/step_4.png' ) ); ?>"></p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 4 - Sport Configurator
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 4</div>
					<h3 class="col">Sport Configurator</h3>
				</div>

				<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
					<svg class="anwp-icon">
						<use xlink:href="#icon-book"></use>
					</svg>
					<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
					<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/421-sport-configurator"><?php echo esc_html__( 'Sport Configurator in details', 'sports-leagues' ); ?></a>
				</div>

				<p>Go to the <b>"Sports Settings"</b> &gt;&gt; <b>"Sport Configurator"</b>. Load recommended settings for the most closest sport. Edit and save.</p>
				<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/jn0VujlVBSE?rel=0&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 5 - Player Stats
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 5 (optional)</div>
					<h3 class="col">Player Stats</h3>
				</div>

				<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
					<svg class="anwp-icon">
						<use xlink:href="#icon-book"></use>
					</svg>
					<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
					<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/288-player-stats-game">Game Player Stats</a>
				</div>

				<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
					<svg class="anwp-icon">
						<use xlink:href="#icon-book"></use>
					</svg>
					<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
					<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/289-player-stats-season">Season Player Stats</a>
				</div>

				<span class="anwp-info-badge">optional</span> Miss this step if you don't need player statistic for every game.

				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/player-stats.png' ) ); ?>"></p>

				<p>Go to the <b>"Sports Settings"</b> &gt;&gt; <b>"Player Stats"</b>. Create stats columns you like and save.</p>


				<?php
				/*
				|--------------------------------------------------------------------
				| Step 6 - Game Events
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 6 (optional)</div>
					<h3 class="col">Game Events</h3>
				</div>

				<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
					<svg class="anwp-icon">
						<use xlink:href="#icon-book"></use>
					</svg>
					<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
					<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/205-game-events">Game Events</a>
				</div>

				<span class="anwp-info-badge">optional</span> Miss this step if you don't plan to use game events.

				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/game-events.png' ) ); ?>"></p>

				<p>Go to the <b>"Sports Settings"</b> &gt;&gt; <b>"Game Events"</b>. Create events and save.</p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 7 - Create Players
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters mb-3">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 7 (optional)</div>
					<h3 class="col">Create Players</h3>
				</div>
				<span class="anwp-info-badge">optional</span> Miss this step if you don't plan to have players.

				<p>Recommended using batch creation tool. It will help you to create a lot of players faster.</p>
				<p>Go to the <b>"Sports Leagues"</b> &gt;&gt; <b>"Import Data"</b>. Set the "players" option in the top dropdown. Apply columns you like.</p>
				<p>Then you can enter player data manually or copy from Excel-like spreadsheets.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/players-batch.png' ) ); ?>"></p>
				<p>You may also add players one-by-one in <b>"Persons"</b> &gt;&gt; <b>"All Players"</b> menu.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/players-manually.png' ) ); ?>"></p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 8 - Create Team Roster
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 8 (optional)</div>
					<h3 class="col">Create Team Roster</h3>
				</div>
				<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
					<svg class="anwp-icon">
						<use xlink:href="#icon-book"></use>
					</svg>
					<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
					<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/252-create-team-roster">Create Team Roster</a>
				</div>

				<p><span class="anwp-info-badge">optional</span> If you plan to use players in game, you have to create team roster.</p>
				<p>Go to team edit page, select season, set player number, role and status.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/roster-1.png' ) ); ?>"></p>
				<p>You may see created roster at the Team page in frontend.</p>
				<p><img class="d-block my-2" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/roster-2.png' ) ); ?>"></p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 9 - Create Tournament
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 9</div>
					<h3 class="col">Create Tournament</h3>
				</div>
				<p><span style="font-size: 1rem;">To create a Tournament go to the <b>"Tournaments"</b> &gt;&gt; <b>"New Tournament"</b>. Enter title, select league and season.</span></p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/step_5.png' ) ); ?>"></p>
				<p>On the next step add stage title, system, status and assign clubs.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/step_6.png' ) ); ?>"></p>
				<p>Now your tournament looks like.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-02_13-14-25.png' ) ); ?>"></p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 10 - Create Tournament
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 10</div>
					<h3 class="col">Standing (optional)</h3>
				</div>
				<p>You have to create and configure Standing if your tournament system is a "group".</p>
				<p>Go to the “Tournaments” &gt;&gt; “Standings” and click “Add New”.</p>
				<p>Then select appropriate Tournament and Stage.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-01_20-43-26.png' ) ); ?>"></p>
				<p>You can modify table colors for places or teams.</p>
				<p><img class="d-block my-2" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-01_21-20-22.png' ) ); ?>"></p>
				<p>Set ranking criteria.</p>
				<p><img class="d-block my-2" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-01_21-20-44.png' ) ); ?>"></p>
				<p>And configure columns order and visibility.</p>
				<p><img class="d-block my-2" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-01_21-21-03.png' ) ); ?>"></p>
				<p>Below is a screenshot of the automatically calculated Standing table after entering a few games.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-02_13-20-10.png' ) ); ?>"></p>

				<?php
				/*
				|--------------------------------------------------------------------
				| Step 11 - Create Game
				|--------------------------------------------------------------------
				*/
				?>
				<div class="mt-5 row align-items-center no-gutters">
					<div class="bg-success text-light mr-3 col-auto py-2 px-2 h5 text-uppercase">Step 11</div>
					<h3 class="col">Create a Game</h3>
				</div>
				<p>Congrats! Now you are ready to create your first game.</p>
				<p>Go to the “Game” &gt;&gt; “Add New Game”. Select tournament, stage, teams and click “Save and Continue”.</p>
				<p><img class="my-2 d-block" src="<?php echo esc_attr( Sports_Leagues::url( 'admin/img/tutorial/2019-04-01_21-26-01.png' ) ); ?>"></p>
			</div>
		</div>
	</div>
</div>
