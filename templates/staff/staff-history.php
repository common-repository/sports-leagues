<?php
/**
 * The Template for displaying Staff >> History Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/staff/staff-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'staff_id' => '',
		'header'   => true,
	]
);

$history = get_post_meta( $data->staff_id, '_sl_staff_history_metabox_group', true );

if ( ! empty( $history ) && is_array( $history ) ) : ?>
	<div class="staff__history anwp-section">
		<?php
		if ( Sports_Leagues::string_to_bool( $data->header ) ) {
			sports_leagues()->load_partial(
				[
					'text' => Sports_Leagues_Text::get_value( 'staff__history__career', __( 'Career', 'sports-leagues' ) ),
				],
				'general/header'
			);
		}
		?>
		<table class="table table-sm options-list table-bordered w-100">
			<thead>
			<tr>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__history__club', __( 'Club', 'sports-leagues' ) ) ); ?></th>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__history__job_title', __( 'Job Title', 'sports-leagues' ) ) ); ?></th>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__history__from', __( 'From', 'sports-leagues' ) ) ); ?></th>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'staff__history__to', __( 'To', 'sports-leagues' ) ) ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $history as $item ) :
				$item = wp_parse_args(
					$item,
					[
						'team' => '',
						'job'  => '',
						'from' => '',
						'to'   => '',
					]
				);
				?>
				<tr>
					<td class="options-list__value">
						<?php
						if ( absint( $item['team'] ) ) :
							$team = sports_leagues()->team->get_team_by_id( $item['team'] );

							if ( $team->logo ) :
								?>
								<div class="team-logo__cover team-logo__cover--mini mr-1 align-middle" style="background-image: url('<?php echo esc_url( $team->logo ); ?>')"></div>
								<?php
							endif;

							echo esc_html( $team->title );
						endif;
						?>
					</td>
					<td class="options-list__value"><?php echo esc_html( $item['job'] ); ?></td>
					<td class="options-list__value"><?php echo $item['from'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['from'] ) ) ) : ''; ?></td>
					<td class="options-list__value"><?php echo $item['to'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['to'] ) ) ) : ''; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
