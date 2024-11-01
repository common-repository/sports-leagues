<?php
/**
 * The Template for displaying Standing Table Shortcode. Layout "mini". Used for widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-standing--mini.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Disable loading default standing layout.
 *
 * @param $data
 *
 * @since 0.5.14
 */
if ( apply_filters( 'sports-leagues/tmpl-standing/disable_default_layout', false, $data ) ) {

	/**
	 * Use this hook to load alternative layout.
	 *
	 * @param object $data
	 *
	 * @since 0.5.14
	 */
	do_action( 'sports-leagues/tmpl-standing/alternative_layout', $data );

	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'title'       => '',
		'id'          => '',
		'exclude_ids' => '',
		'context'     => '',
		'team_name'   => 'abbr',
		'partial'     => '',
		'bottom_link' => '',
		'link_text'   => '',
	]
);

$standing_id = $data->id;

if ( ! empty( $data->context ) && 'widget' === $data->context ) {
	$standing_id = $data->standing;
}

// Check for required data
if ( empty( $standing_id ) || 'sl_standing' !== get_post_type( $standing_id ) ) {
	return;
}

// Prepare data
$table        = json_decode( get_post_meta( $standing_id, '_sl_table_main', true ) );
$table_colors = json_decode( get_post_meta( $standing_id, '_sl_table_colors', true ) );
$table_notes  = get_post_meta( $standing_id, '_sl_table_notes', true );

// Check data is valid
if ( null === $table ) {
	// something went wrong
	return;
}

// Check table colors
if ( is_object( $table_colors ) ) {
	$table_colors = (array) $table_colors;
}

/**
 * Filter: sports-leagues/tmpl-standing/columns_order
 *
 * @since 0.1.0
 *
 * @param array
 * @param object  $standing_id
 * @param string  $layout
 */
$columns_order = apply_filters(
	'sports-leagues/tmpl-standing/columns_order',
	[ 'played', 'all_win', 'draw', 'all_loss', 'pts' ],
	$standing_id,
	'mini'
);

// Columns headers
$columns_config = sports_leagues()->config->get_standing_config();

$exclude_ids = [];
if ( ! empty( $data->exclude_ids ) ) {
	$exclude_ids = array_map( 'absint', explode( ',', $data->exclude_ids ) );
}

// Slice table if partial option is set
if ( $data->partial ) {
	$table = sports_leagues()->standing->get_standing_partial_data( $table, $data->partial );
}
?>

<div class="anwp-b-wrap standing standing--widget standing__inner standing-<?php echo (int) $standing_id; ?> context--<?php echo esc_attr( $data->context ); ?>">

	<?php if ( $data->title ) : ?>
		<h4 class="standing__title"><?php echo esc_html( $data->title ); ?></h4>
	<?php endif; ?>

	<div class="table-responsive mb-2">
		<table class="table table-sm table-bordered standing-table anwp-text-center mb-1 w-100">
			<thead class="anwp-bg-light">
			<tr>
				<th class="anwp-text-center" scope="col">#</th>
				<th scope="col" class="text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'shortcode__standing__team', __( 'Team', 'sports-leagues' ) ) ); ?></th>

				<?php foreach ( $columns_order as $col ) : ?>
					<th scope="col" class="anwp-text-center" data-toggle="anwp-sl-tooltip"
						data-tippy-content="<?php echo esc_html( empty( $columns_config[ $col ]['name'] ) ? '' : $columns_config[ $col ]['name'] ); ?>">
						<?php echo esc_html( empty( $columns_config[ $col ]['abbr'] ) ? $col : $columns_config[ $col ]['abbr'] ); ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>

			<tbody>
			<?php
			foreach ( $table as $row ) :

				if ( in_array( (int) $row->team_id, $exclude_ids, true ) ) {
					continue;
				}

				// Prepare Color Class
				$color_class = '';
				$color_style = '';

				if ( ! empty( $table_colors[ 'p' . $row->place ] ) ) {
					if ( '#' === mb_substr( $table_colors[ 'p' . $row->place ], 0, 1 ) ) {
						$color_style = 'background-color: ' . esc_attr( $table_colors[ 'p' . $row->place ] );
					} else {
						$color_class = 'anwp-bg-' . $table_colors[ 'p' . $row->place ] . '-light';
					}
				}

				if ( ! empty( $table_colors[ 't' . $row->team_id ] ) ) {
					if ( '#' === mb_substr( $table_colors[ 't' . $row->team_id ], 0, 1 ) ) {
						$color_style = 'background-color: ' . esc_attr( $table_colors[ 't' . $row->team_id ] );
					} else {
						$color_class = 'anwp-bg-' . $table_colors[ 't' . $row->team_id ] . '-light';
					}
				}

				?>
				<tr class="standing-table__row team-<?php echo (int) $row->team_id; ?> place-<?php echo (int) $row->place; ?>">
					<td class="px-0 align-middle <?php echo esc_attr( $color_class ); ?>" style="<?php echo esc_attr( $color_style ); ?>"><?php echo esc_html( $row->place ); ?></td>
					<td class="text-left">
						<div class="d-flex align-items-center">
							<?php $team = sports_leagues()->team->get_team_by_id( $row->team_id ); ?>

							<?php if ( $team->logo ) : ?>
								<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_url( $team->logo ); ?>')"></div>
							<?php endif; ?>

							<a class="team__link anwp-link align-middle" href="<?php echo esc_url( $team->link ); ?>">
								<?php echo esc_html( ( 'abbr' === $data->team_name && $team->abbr ) ? $team->abbr : $team->title ); ?>
							</a>
						</div>
					</td>

					<?php foreach ( $columns_order as $col ) : ?>
						<td class="align-middle">
							<?php echo esc_html( $row->{$col} ); ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>

			</tbody>
		</table>
	</div>

	<?php if ( $table_notes ) : ?>
		<div class="standing-table__notes mt-2 mb-3">
			<?php echo wp_kses_post( sports_leagues()->standing->prepare_table_notes( $table_notes ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data->bottom_link ) ) : ?>
		<div class="standing-table__tournament-link mt-2">
			<?php
			if ( 'tournament' === $data->bottom_link ) :
				$stage_id         = absint( get_post_meta( $standing_id, '_sl_stage_id', true ) );
				$stage_post       = get_post( $stage_id );
				$tournament_title = $stage_post->post_parent ? get_the_title( $stage_post->post_parent ) : '';
				?>
				<a href="<?php echo esc_url( get_permalink( $stage_post->post_parent ) ); ?>"><?php echo esc_html( $data->link_text ? $data->link_text : $tournament_title ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
