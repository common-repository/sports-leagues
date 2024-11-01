/**
 * Sports Leagues
 * https://anwp.pro
 *
 * Licensed under the GPLv2+ license.
 */

window.SportsLeaguesGlobal = window.SportsLeaguesGlobal || {};

( function( window, document, $, plugin ) {
	'use strict';

	const $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window   = $( window );
		$c.body     = $( document.body );
		$c.document = $( document );

		$c.searchData = {
			context: '',
			s: '',
		};

		$c.activeLink   = null;
		$c.singleSelect = true;

		$c.xhr = null;

		$c.selectorInGutenbergInitialized = false;
	};

	plugin.bindEvents = function() {
		if ( 'loading' !== document.readyState ) {
			plugin.onPageReady();
		} else {
			document.addEventListener( 'DOMContentLoaded', plugin.onPageReady );
		}

		$c.body.on( 'anwp-sl-admin-content-updated', plugin.initSelectorModaal );

		$c.document.on( 'widget-added', plugin.initSelectorModaal );
		$c.document.on( 'widget-updated', plugin.initSelectorModaal );

		if ( document.body.classList.contains( 'block-editor-page' ) ) {
			$c.body.on( 'click', '.anwp-sl-selector', function( e ) {
				const $this = $( this );

				if ( ! $this.closest( '.components-base-control' ).length ) {
					return false;
				}

				if ( ! $c.selectorInGutenbergInitialized ) {
					$c.btnCancel.on( 'click', function( env ) {
						env.preventDefault();
						$c.activeLink.modaal( 'close' );
					} );

					$c.resultContext.on( 'click', '.anwp-sl-selector-action', function( env ) {
						env.preventDefault();
						plugin.addSelected( $( this ).closest( 'tr' ).data( 'id' ), $( this ).closest( 'tr' ).data( 'name' ) );
					} );

					$c.selectedItems.on( 'click', '.anwp-sl-selector-action-no', function( env ) {
						env.preventDefault();
						$( this ).closest( '.anwp-sl-selector-modaal__selected-item' ).remove();
					} );

					$c.btnInsert.on( 'click', function( env ) {
						env.preventDefault();

						const output = [];

						$c.selectedItems.find( '.anwp-sl-selector-modaal__selected-item' ).each( function() {
							output.push( $( this ).find( '.anwp-sl-selector-action-no' ).data( 'id' ) );
						} );

						$c.activeLink.modaal( 'close' );
						$c.activeLink.closest( '.components-base-control__field' ).find( 'input.components-text-control__input' ).val( output.join( ',' ) );

						const newEvent = new Event( 'update-sl-selector' );
						$c.activeLink.closest( '.components-base-control__field' ).find( 'input.components-text-control__input' )[ 0 ].dispatchEvent( newEvent );
					} );

					$c.searchInput.on( 'keyup', () => setTimeout( () => plugin.sendSearchRequest(), 500 ) );
					$c.selectorInGutenbergInitialized = true;
				}

				// Initialize modaal
				$this.modaal(
					{
						content_source: '#anwp-sl-selector-modaal',
						custom_class: 'anwp-sl-shortcode-modal anwp-sl-selector-modal wp-core-ui',
						hide_close: true,
						animation: 'none',
						before_close: plugin.clearSelector,
					}
				);

				$c.activeLink = $this;
				$c.activeLink.modaal( 'open' );
				$c.singleSelect = $c.activeLink.data( 'single' ) === 'yes';
				plugin.initializeSelectorContent();

				e.preventDefault();
			} );
		}
	};

	plugin.onPageReady = function() {
		if ( 'undefined' !== typeof anwpslGlobals ) {
			$c.body.append( anwpslGlobals.selectorHtml );
		}

		$c.searchBar      = $c.body.find( '#anwp-sl-selector-modaal__search-bar' );
		$c.searchSpinner  = $c.body.find( '#anwp-sl-selector-modaal__search-spinner' );
		$c.initialSpinner = $c.body.find( '#anwp-sl-selector-modaal__initial-spinner' );
		$c.searchInput    = $c.body.find( '#anwp-sl-selector-modaal__search' );
		$c.headerContext  = $c.body.find( '#anwp-sl-selector-modaal__header-context' );
		$c.resultContext  = $c.body.find( '#anwp-sl-selector-modaal__content' );
		$c.selectedItems  = $c.body.find( '#anwp-sl-selector-modaal__selected' );
		$c.btnCancel      = $c.body.find( '#anwp-sl-selector-modaal__cancel' );
		$c.btnInsert      = $c.body.find( '#anwp-sl-selector-modaal__insert' );

		plugin.initSelectorModaal();
	};

	plugin.initSelectorModaal = function() {
		// Check modaal placeholder exists
		if ( ! $c.body.find( '#anwp-sl-selector-modaal' ).length || ! $c.body.find( '.anwp-sl-selector' ).length ) {
			return false;
		}

		var modalOpenLink = $c.body.find( '.anwp-sl-selector' );

		// Initialize modaal
		modalOpenLink.modaal(
			{
				content_source: '#anwp-sl-selector-modaal',
				custom_class: 'anwp-sl-shortcode-modal anwp-sl-selector-modal',
				hide_close: true,
				animation: 'none',
				before_close: plugin.clearSelector,
			}
		);

		modalOpenLink.on( 'click', function( e ) {
			e.preventDefault();

			$c.activeLink = $( this );
			$c.activeLink.modaal( 'open' );

			$c.singleSelect = 'yes' === $c.activeLink.data( 'single' );

			plugin.initializeSelectorContent();
		} );

		$c.btnCancel.on( 'click', function( e ) {
			e.preventDefault();
			$c.activeLink.modaal( 'close' );
		} );

		$c.resultContext.on( 'click', '.anwp-sl-selector-action', function( e ) {
			e.preventDefault();
			plugin.addSelected( $( this ).closest( 'tr' ).data( 'id' ), $( this ).closest( 'tr' ).data( 'name' ) );
		} );

		$c.selectedItems.on( 'click', '.anwp-sl-selector-action-no', function( e ) {
			e.preventDefault();
			$( this ).closest( '.anwp-sl-selector-modaal__selected-item' ).remove();
		} );

		$c.btnInsert.on( 'click', function( e ) {
			e.preventDefault();

			var output = [];

			$c.selectedItems.find( '.anwp-sl-selector-modaal__selected-item' ).each( function() {
				output.push( $( this ).find( '.anwp-sl-selector-action-no' ).data( 'id' ) );
			} );

			$c.activeLink.modaal( 'close' );
			$c.activeLink.prev( 'input' ).val( output.join( ',' ) );
			$c.activeLink.prev( 'input' ).trigger( 'change' );
			$c.activeLink.prev( 'input' )[ 0 ].dispatchEvent( new Event( 'anwp-sl-changed-selector' ) );
		} );

		$c.searchInput.on( 'keyup', () => setTimeout( () => plugin.sendSearchRequest(), 500 ) );
	};

	plugin.addSelected = function( id, name ) {
		if ( $c.selectedItems.find( '[data-id="' + id + '"]' ).length ) {
			return false;
		}

		var appendHTML = '<div class="anwp-sl-selector-modaal__selected-item"><button type="button" class="button button-small anwp-sl-selector-action-no" data-id="' + id + '"><span class="dashicons dashicons-no"></span></button><span>' + name + '</span></div>';

		if ( $c.singleSelect ) {
			$c.selectedItems.html( appendHTML );
		} else {
			$c.selectedItems.append( appendHTML );
		}
	};

	plugin.clearSelector = function() {
		$c.searchBar.find( '.anwp-selector-select2--active' ).val( '' );
		$c.searchBar.find( '.anwp-selector-select2--active' ).select2( 'destroy' );
		$c.searchBar.find( '.anwp-selector-select2--active' ).removeClass( 'anwp-selector-select2--active' );

		$c.searchBar.find( '.anwp-sl-selector-modaal__bar-group' ).addClass( 'd-none' );
	};

	plugin.initializeSelectorContent = function() {
		$c.searchData.context = $c.activeLink.data( 'context' );
		$c.searchData.s       = '';

		$c.initialSpinner.addClass( 'is-active' );

		// Load Initial Values
		if ( $c.activeLink.prev( 'input' ).val() ) {
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'anwp_sl_selector_initial',
					initial: $c.activeLink.prev( 'input' ).val(),
					nonce: anwpslGlobals.ajaxNonce,
					data_context: $c.searchData.context,
				},
			} ).done( function( response ) {
				if ( response.success && response.data.items ) {
					_.each( response.data.items, function( pp ) {
						plugin.addSelected( pp.id, pp.name );
					} );
				}
			} ).always( function() {
				$c.initialSpinner.removeClass( 'is-active' );
			} );
		} else {
			$c.initialSpinner.removeClass( 'is-active' );
		}

		// Update form
		$c.headerContext.html( $c.searchData.context );
		$c.resultContext.html( '' );
		$c.selectedItems.html( '' );
		$c.searchInput.val( '' );

		// Init Search Bar options
		$c.searchBar.find( '.anwp-sl-selector-modaal__bar-group' ).each( function() {
			const $this = $( this );

			if ( $this.hasClass( 'anwp-sl-selector-modaal__bar-group--' + $c.searchData.context ) ) {
				var $select = $this.find( 'select.anwp-selector-select2' );

				if ( $select.length ) {
					if ( anwpslGlobals && anwpslGlobals[ $select.attr( 'name' ) ] && anwpslGlobals[ $select.attr( 'name' ) ].length ) {
						var select2Options = {
							width: '170px',
							placeholder: {
								id: '',
								placeholder: '- select -',
							},
							allowClear: true,
							dropdownCssClass: 'anwp-sl-shortcode-modal__select2-dropdown',
						};

						// Show select el
						$this.removeClass( 'd-none' );

						// Add Select2 active class
						$select.addClass( 'anwp-selector-select2--active' );

						if ( 'yes' !== $select.data( 'anwp-s2-initialized' ) ) {
							select2Options.data = anwpslGlobals[ $select.attr( 'name' ) ];
							$select.data( 'anwp-s2-initialized', 'yes' );
						}

						// Init Select2
						$select.select2( select2Options );

						$select.on( 'change.select2', function() {
							plugin.sendSearchRequest();
						} );

						$select.on( 'select2:clear', function() {
							$( this ).on( 'select2:opening.cancelOpen', function( env ) {
								env.preventDefault();
								$( this ).off( 'select2:opening.cancelOpen' );
							} );
						} );
					}
				} else {
					$this.removeClass( 'd-none' );
				}
			}
		} );

		plugin.sendSearchRequest();
	};

	plugin.sendSearchRequest = function() {
		if ( $c.xhr && 4 !== $c.xhr.readyState ) {
			$c.xhr.abort();
		}

		$c.searchSpinner.addClass( 'is-active' );
		$c.resultContext.addClass( 'anwp-search-is-active' );
		$c.resultContext.html( '' );

		// Search Data
		$c.searchData.s       = $c.searchInput.val();
		$c.searchData.team    = $c.searchBar.find( '#anwp-sl-selector-modaal__search-team' ).val();
		$c.searchData.country = $c.searchBar.find( '#anwp-sl-selector-modaal__search-country' ).val();
		$c.searchData.team_a  = $c.searchBar.find( '#anwp-sl-selector-modaal__search-team-a' ).val();
		$c.searchData.team_b  = $c.searchBar.find( '#anwp-sl-selector-modaal__search-team-b' ).val();
		$c.searchData.season  = $c.searchBar.find( '#anwp-sl-selector-modaal__search-season' ).val();
		$c.searchData.league  = $c.searchBar.find( '#anwp-sl-selector-modaal__search-league' ).val();

		$c.xhr = $.ajax( {
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'anwp_sl_selector_data',
				nonce: anwpslGlobals.ajaxNonce,
				search_data: $c.searchData,
			},
		} ).done( function( response ) {
			if ( response.success ) {
				$c.resultContext.html( response.data.html );
			}
		} ).always( function() {
			$c.searchSpinner.removeClass( 'is-active' );
			$c.resultContext.removeClass( 'anwp-search-is-active' );
		} );
	};

	$( plugin.init );
}( window, document, jQuery, window.SportsLeaguesGlobal ) );
