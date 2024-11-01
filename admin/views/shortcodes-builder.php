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

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}
?>

<div class="anwp-b-wrap">
	<div class="inside p-3 container ml-0">
		<h1 class="mb-4"><?php echo esc_html__( 'Shortcode Builder', 'sports-leagues' ); ?></h1>

		<div id="anwp-shortcode-builder__header" class="d-flex align-items-center">
			<label for="anwp-shortcode-builder__selector"><?php echo esc_html__( 'Shortcode', 'sports-leagues' ); ?></label>
			<select id="anwp-shortcode-builder__selector" class="mx-2">
				<option value="">- <?php echo esc_html__( 'select', 'sports-leagues' ); ?> -</option>
				<option value="games"><?php echo esc_html__( 'Games', 'sports-leagues' ); ?></option>
				<option value="players-stats"><?php echo esc_html__( 'Players Stats', 'sports-leagues' ); ?></option>
				<option value="standing"><?php echo esc_html__( 'Standing Table', 'sports-leagues' ); ?></option>
				<option value="teams"><?php echo esc_html__( 'Teams', 'sports-leagues' ); ?></option>
				<option value="tournament-header"><?php echo esc_html__( 'Tournament Header', 'sports-leagues' ); ?></option>
				<option value="tournament-list"><?php echo esc_html__( 'Tournament List', 'sports-leagues' ); ?></option>
				<?php
				/**
				 * Hook: sports-leagues/shortcodes/selector_bottom
				 *
				 * @since 0.5.11
				 */
				do_action( 'sports-leagues/shortcodes/selector_bottom' );
				?>
			</select>
			<span class="spinner"></span>
		</div>
		<div id="anwp-shortcode-builder__composed" class="d-none">
			<hr>
			<a class="font-weight-bold text-muted" href="#" id="anwp-shortcode-builder__copy"><?php echo esc_html__( 'copy code', 'sports-leagues' ); ?></a>
			<pre class="p-2 bg-white border mt-1" style="white-space: normal;"></pre>
			<hr class="mb-0">
		</div>
		<div id="anwp-shortcode-builder__content" class="py-3"></div>
	</div>
</div>

<script>
	<?php
	$vars = [
		'nonce' => wp_create_nonce( 'sl_shortcodes_nonce' ),
	];
	?>
	var _sl_shortcode_builder_l10n = <?php echo wp_json_encode( $vars ); ?>;

	window.SportsLeaguesShortcodeBuilder = window.SportsLeaguesShortcodeBuilder || {};
	( function( window, document, $, plugin ) {

		'use strict';

		var $c = {};

		plugin.init = function() {
			plugin.cache();
			plugin.initBuilderControls();
		};

		plugin.cache = function() {
			$c.window = $( window );
			$c.body   = $( document.body );
			$c.xhr    = null;
		};

		plugin.builtShortcode = function() {
			// Shortcode params
			var shortcodeTitle = $c.builderFormWrap.find( '.sl-shortcode-name' ).val();
			var shortcodeAttrs = [];

			$c.builderFormWrap.find( '.sl-shortcode-attr' ).each( function() {
				var $thisAttr = $( this );

				switch ( $thisAttr.data( 'sl-type' ) ) {
					case 'text':
					case 'select':
						shortcodeAttrs.push( $thisAttr.attr( 'name' ) + '="' + $thisAttr.val() + '"' );
						break;

					case 'select2':
						if ( $thisAttr.val() && _.isArray( $thisAttr.val() ) ) {
							shortcodeAttrs.push( $thisAttr.attr( 'name' ) + '="' + $thisAttr.val().toString() + '"' );
						} else if ( $thisAttr.val() ) {
							shortcodeAttrs.push( $thisAttr.attr( 'name' ) + '="' + $thisAttr.val() + '"' );
						} else {
							shortcodeAttrs.push( $thisAttr.attr( 'name' ) + '=""' );
						}
						break;

					default:
						shortcodeAttrs.push( $thisAttr.attr( 'name' ) + '="' + $thisAttr.val() + '"' );
				}
			} );

			$c.builderComposed.text( '[' + shortcodeTitle + ' ' + shortcodeAttrs.join( ' ' ) + ']' );
		};

		plugin.initBuilderControls = function() {
			$c.builderSelector     = $c.body.find( '#anwp-shortcode-builder__selector' );
			$c.builderSpinner      = $c.body.find( '#anwp-shortcode-builder__header .spinner' );
			$c.builderFormWrap     = $c.body.find( '#anwp-shortcode-builder__content' );
			$c.builderComposedWrap = $c.body.find( '#anwp-shortcode-builder__composed' );
			$c.builderComposed     = $c.builderComposedWrap.find( 'pre' );

			$c.builderFormWrap.on( 'change input', '.sl-shortcode-attr', function( e ) {
				e.preventDefault();
				plugin.builtShortcode();
			} );

			$( '#anwp-shortcode-builder__copy' ).on( 'click', function( e ) {

				e.preventDefault();

				var $temp = $( '<input>' );
				$c.body.append( $temp );
				$temp.val( $c.builderComposed.text() ).select();
				document.execCommand( 'copy' );
				$temp.remove();

				toastr.success( 'Copied to Clipboard' );
			} );

			$c.builderSelector.on( 'change', function() {
				var $this = $( this );

				$c.builderFormWrap.empty();
				$c.builderComposed.empty();
				$c.builderComposedWrap.addClass( 'd-none' );

				if ( ! $this.val() ) {
					return false;
				}

				$c.builderSpinner.addClass( 'is-active' );

				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'sl_shortcodes_modal_form',
						nonce: _sl_shortcode_builder_l10n.nonce,
						shortcode: $this.val()
					}
				} ).done( function( response ) {
					if ( response.success ) {
						$c.builderComposedWrap.removeClass( 'd-none' );
						$c.builderFormWrap.html( response.data.html );

						if ( $c.builderFormWrap.find( '.sl-shortcode-select2' ).length && $.fn.select2 ) {
							$c.builderFormWrap.find( '.sl-shortcode-select2' ).each(
								function() {
									$( this ).select2( {
										width: '25em'
									} );
								}
							);
						}

						plugin.builtShortcode();
						$c.body.trigger( 'anwp-sl-admin-content-updated' );
					}
				} ).always( function() {
					$c.builderSpinner.removeClass( 'is-active' );
				} );
			} );
		};

		$( plugin.init );
	}( window, document, jQuery, window.SportsLeaguesShortcodeBuilder ) );
</script>
