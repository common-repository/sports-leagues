<?php
/**
 * The Template for displaying Match Countdown.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-countdown.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.0
 *
 * @version       0.12.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'kickoff'        => '',
		'kickoff_c'      => '',
		'special_status' => '',
		'context'        => '',
		'label_size'     => '',
		'value_size'     => '',
	]
);

$kickoff_diff = ( date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) ) > 0 ? date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) : 0;

if ( $kickoff_diff > 0 ) :
	$label_style = '';
	$value_style = '';

	if ( absint( $data->label_size ) ) {
		$label_style .= 'font-size: ' . absint( $data->label_size ) . 'px;';
	}

	if ( absint( $data->value_size ) ) {
		$value_style .= 'font-size: ' . absint( $data->value_size ) . 'px;';
	}
	?>
	<div class="anwp-text-center py-3 anwp-sl-game-countdown anwp-sl-game-countdown--<?php echo esc_attr( $data->context ); ?> d-none"
		data-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>">
		<div class="d-flex justify-content-center anwp-sl-game-countdown__inner">
			<div class="anwp-sl-game-countdown__item anwp-sl-game-countdown__days">
				<div class="anwp-sl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( Sports_Leagues_Text::get_value( 'data__countdown__days', esc_html_x( 'days', 'flip countdown', 'sports-leagues' ) ) ); ?></div>
				<div class="anwp-sl-game-countdown__value anwp-sl-game-countdown__value-days" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-sl-game-countdown__separator"></div>
			<div class="anwp-sl-game-countdown__item anwp-sl-game-countdown__hours">
				<div class="anwp-sl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( Sports_Leagues_Text::get_value( 'data__countdown__hours', esc_html_x( 'hours', 'flip countdown', 'sports-leagues' ) ) ); ?></div>
				<div class="anwp-sl-game-countdown__value anwp-sl-game-countdown__value-hours" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-sl-game-countdown__separator"></div>
			<div class="anwp-sl-game-countdown__item anwp-sl-game-countdown__minutes">
				<div class="anwp-sl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( Sports_Leagues_Text::get_value( 'data__countdown__minutes', esc_html_x( 'minutes', 'flip countdown', 'sports-leagues' ) ) ); ?></div>
				<div class="anwp-sl-game-countdown__value anwp-sl-game-countdown__value-minutes" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-sl-game-countdown__separator"></div>
			<div class="anwp-sl-game-countdown__item anwp-sl-game-countdown__seconds">
				<div class="anwp-sl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( Sports_Leagues_Text::get_value( 'data__countdown__seconds', esc_html_x( 'seconds', 'flip countdown', 'sports-leagues' ) ) ); ?></div>
				<div class="anwp-sl-game-countdown__value anwp-sl-game-countdown__value-seconds" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
		</div>
	</div>
<?php endif; ?>
