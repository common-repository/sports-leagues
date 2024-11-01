( function( $ ) {

	'use strict';

	$( function() {

		if ( $( '#anwp_sl_map_wrapper' ).length ) {

			var map;
			var marker;
			var $longitude = $( '#anwp_cmb2_map_input_longitude' );
			var $latitude  = $( '#anwp_cmb2_map_input_latitude' );
			var $btnReset  = $( '#anwp_cmb2_map_reset_btn' );
			var $inputSearch  = $( '#anwp_cmb2_map_input_address' );

			var initialZoom     = $latitude.val() && $longitude.val() ? 15 : 8;
			var defaultPosition = {lat: $latitude.val() ? parseFloat( $latitude.val() ) : 51.556, lng: $longitude.val() ? parseFloat( $longitude.val() ) : -0.279575};

			$inputSearch.keypress( function( e ) {
				if ( 13 === parseInt( e.keyCode, 10 ) ) {
					e.preventDefault();
				}
			} );

			// Create new map
			map = new google.maps.Map( document.getElementById( 'anwp_sl_map_wrapper' ), {
				center: defaultPosition,
				zoom: initialZoom
			} );

			// Create new marker
			marker = new google.maps.Marker( {
				position: defaultPosition,
				draggable: true,
				map: map
			} );

			// Search
			var autocomplete = new google.maps.places.Autocomplete( $inputSearch[0] );
			autocomplete.bindTo( 'bounds', map );

			google.maps.event.addListener( autocomplete, 'place_changed', function() {
				const place = autocomplete.getPlace();

				if ( ! place.geometry ) {
					return;
				}

				if ( place.geometry.viewport ) {
					map.fitBounds( place.geometry.viewport );
				} else {
					map.setCenter( place.geometry.location );
					map.setZoom( 17 );
				}

				marker.setPosition( place.geometry.location );

				$latitude.val( place.geometry.location.lat() );
				$longitude.val( place.geometry.location.lng() );
			} );

			// Add listeners
			google.maps.event.addListener( map, 'click', function( e ) {
				marker.setPosition( e.latLng );
				$inputSearch.val( '' );
				$latitude.val( e.latLng.lat() );
				$longitude.val( e.latLng.lng() );
			} );

			google.maps.event.addListener( marker, 'dragend', function( e ) {
				$latitude.val( e.latLng.lat() );
				$longitude.val( e.latLng.lng() );
				$inputSearch.val( '' );
			} );

			$btnReset.on( 'click', function( e ) {
				e.preventDefault();

				$latitude.val( '' );
				$longitude.val( '' );
			} );
		}
	} );

}( jQuery ) );

/*!
 * gumshoejs v5.1.1
 * A simple, framework-agnostic scrollspy script.
 * (c) 2019 Chris Ferdinandi
 * MIT License
 * http://github.com/cferdinandi/gumshoe
 */

(function (root, factory) {
	if ( typeof define === 'function' && define.amd ) {
		define([], (function () {
			return factory(root);
		}));
	} else if ( typeof exports === 'object' ) {
		module.exports = factory(root);
	} else {
		root.Gumshoe = factory(root);
	}
})(typeof global !== 'undefined' ? global : typeof window !== 'undefined' ? window : this, (function (window) {

	'use strict';

	//
	// Defaults
	//

	var defaults = {

		// Active classes
		navClass: 'active',
		contentClass: 'active',

		// Nested navigation
		nested: false,
		nestedClass: 'active',

		// Offset & reflow
		offset: 0,
		reflow: false,

		// Event support
		events: true

	};


	//
	// Methods
	//

	/**
	 * Merge two or more objects together.
	 * @param   {Object}   objects  The objects to merge together
	 * @returns {Object}            Merged values of defaults and options
	 */
	var extend = function () {
		var merged = {};
		Array.prototype.forEach.call(arguments, (function (obj) {
			for (var key in obj) {
				if (!obj.hasOwnProperty(key)) return;
				merged[key] = obj[key];
			}
		}));
		return merged;
	};

	/**
	 * Emit a custom event
	 * @param  {String} type   The event type
	 * @param  {Node}   elem   The element to attach the event to
	 * @param  {Object} detail Any details to pass along with the event
	 */
	var emitEvent = function (type, elem, detail) {

		// Make sure events are enabled
		if (!detail.settings.events) return;

		// Create a new event
		var event = new CustomEvent(type, {
			bubbles: true,
			cancelable: true,
			detail: detail
		});

		// Dispatch the event
		elem.dispatchEvent(event);

	};

	/**
	 * Get an element's distance from the top of the Document.
	 * @param  {Node} elem The element
	 * @return {Number}    Distance from the top in pixels
	 */
	var getOffsetTop = function (elem) {
		var location = 0;
		if (elem.offsetParent) {
			while (elem) {
				location += elem.offsetTop;
				elem = elem.offsetParent;
			}
		}
		return location >= 0 ? location : 0;
	};

	/**
	 * Sort content from first to last in the DOM
	 * @param  {Array} contents The content areas
	 */
	var sortContents = function (contents) {
		if(contents) {
			contents.sort((function (item1, item2) {
				var offset1 = getOffsetTop(item1.content);
				var offset2 = getOffsetTop(item2.content);
				if (offset1 < offset2) return -1;
				return 1;
			}));
		}
	};

	/**
	 * Get the offset to use for calculating position
	 * @param  {Object} settings The settings for this instantiation
	 * @return {Float}           The number of pixels to offset the calculations
	 */
	var getOffset = function (settings) {

		// if the offset is a function run it
		if (typeof settings.offset === 'function') {
			return parseFloat(settings.offset());
		}

		// Otherwise, return it as-is
		return parseFloat(settings.offset);

	};

	/**
	 * Get the document element's height
	 * @private
	 * @returns {Number}
	 */
	var getDocumentHeight = function () {
		return Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight
		);
	};

	/**
	 * Determine if an element is in view
	 * @param  {Node}    elem     The element
	 * @param  {Object}  settings The settings for this instantiation
	 * @param  {Boolean} bottom   If true, check if element is above bottom of viewport instead
	 * @return {Boolean}          Returns true if element is in the viewport
	 */
	var isInView = function (elem, settings, bottom) {
		var bounds = elem.getBoundingClientRect();
		var offset = getOffset(settings);
		if (bottom) {
			return parseInt(bounds.bottom, 10) < (window.innerHeight || document.documentElement.clientHeight);
		}
		return parseInt(bounds.top, 10) <= offset;
	};

	/**
	 * Check if at the bottom of the viewport
	 * @return {Boolean} If true, page is at the bottom of the viewport
	 */
	var isAtBottom = function () {
		if (window.innerHeight + window.pageYOffset >= getDocumentHeight()) return true;
		return false;
	};

	/**
	 * Check if the last item should be used (even if not at the top of the page)
	 * @param  {Object} item     The last item
	 * @param  {Object} settings The settings for this instantiation
	 * @return {Boolean}         If true, use the last item
	 */
	var useLastItem = function (item, settings) {
		if (isAtBottom() && isInView(item.content, settings, true)) return true;
		return false;
	};

	/**
	 * Get the active content
	 * @param  {Array}  contents The content areas
	 * @param  {Object} settings The settings for this instantiation
	 * @return {Object}          The content area and matching navigation link
	 */
	var getActive = function (contents, settings) {
		var last = contents[contents.length-1];
		if (useLastItem(last, settings)) return last;
		for (var i = contents.length - 1; i >= 0; i--) {
			if (isInView(contents[i].content, settings)) return contents[i];
		}
	};

	/**
	 * Deactivate parent navs in a nested navigation
	 * @param  {Node}   nav      The starting navigation element
	 * @param  {Object} settings The settings for this instantiation
	 */
	var deactivateNested = function (nav, settings) {

		// If nesting isn't activated, bail
		if (!settings.nested) return;

		// Get the parent navigation
		var li = nav.parentNode.closest('li');
		if (!li) return;

		// Remove the active class
		li.classList.remove(settings.nestedClass);

		// Apply recursively to any parent navigation elements
		deactivateNested(li, settings);

	};

	/**
	 * Deactivate a nav and content area
	 * @param  {Object} items    The nav item and content to deactivate
	 * @param  {Object} settings The settings for this instantiation
	 */
	var deactivate = function (items, settings) {

		// Make sure their are items to deactivate
		if (!items) return;

		// Get the parent list item
		var li = items.nav.closest('li');
		if (!li) return;

		// Remove the active class from the nav and content
		li.classList.remove(settings.navClass);
		items.content.classList.remove(settings.contentClass);

		// Deactivate any parent navs in a nested navigation
		deactivateNested(li, settings);

		// Emit a custom event
		emitEvent('gumshoeDeactivate', li, {
			link: items.nav,
			content: items.content,
			settings: settings
		});

	};


	/**
	 * Activate parent navs in a nested navigation
	 * @param  {Node}   nav      The starting navigation element
	 * @param  {Object} settings The settings for this instantiation
	 */
	var activateNested = function (nav, settings) {

		// If nesting isn't activated, bail
		if (!settings.nested) return;

		// Get the parent navigation
		var li = nav.parentNode.closest('li');
		if (!li) return;

		// Add the active class
		li.classList.add(settings.nestedClass);

		// Apply recursively to any parent navigation elements
		activateNested(li, settings);

	};

	/**
	 * Activate a nav and content area
	 * @param  {Object} items    The nav item and content to activate
	 * @param  {Object} settings The settings for this instantiation
	 */
	var activate = function (items, settings) {

		// Make sure their are items to activate
		if (!items) return;

		// Get the parent list item
		var li = items.nav.closest('li');
		if (!li) return;

		// Add the active class to the nav and content
		li.classList.add(settings.navClass);
		items.content.classList.add(settings.contentClass);

		// Activate any parent navs in a nested navigation
		activateNested(li, settings);

		// Emit a custom event
		emitEvent('gumshoeActivate', li, {
			link: items.nav,
			content: items.content,
			settings: settings
		});

	};

	/**
	 * Create the Constructor object
	 * @param {String} selector The selector to use for navigation items
	 * @param {Object} options  User options and settings
	 */
	var Constructor = function (selector, options) {

		//
		// Variables
		//

		var publicAPIs = {};
		var navItems, contents, current, timeout, settings;


		//
		// Methods
		//

		/**
		 * Set variables from DOM elements
		 */
		publicAPIs.setup = function () {

			// Get all nav items
			navItems = document.querySelectorAll(selector);

			// Create contents array
			contents = [];

			// Loop through each item, get it's matching content, and push to the array
			Array.prototype.forEach.call(navItems, (function (item) {

				// Get the content for the nav item
				var content = document.getElementById(decodeURIComponent(item.hash.substr(1)));
				if (!content) return;

				// Push to the contents array
				contents.push({
					nav: item,
					content: content
				});

			}));

			// Sort contents by the order they appear in the DOM
			sortContents(contents);

		};

		/**
		 * Detect which content is currently active
		 */
		publicAPIs.detect = function () {

			// Get the active content
			var active = getActive(contents, settings);

			// if there's no active content, deactivate and bail
			if (!active) {
				if (current) {
					deactivate(current, settings);
					current = null;
				}
				return;
			}

			// If the active content is the one currently active, do nothing
			if (current && active.content === current.content) return;

			// Deactivate the current content and activate the new content
			deactivate(current, settings);
			activate(active, settings);

			// Update the currently active content
			current = active;

		};

		/**
		 * Detect the active content on scroll
		 * Debounced for performance
		 */
		var scrollHandler = function (event) {

			// If there's a timer, cancel it
			if (timeout) {
				window.cancelAnimationFrame(timeout);
			}

			// Setup debounce callback
			timeout = window.requestAnimationFrame(publicAPIs.detect);

		};

		/**
		 * Update content sorting on resize
		 * Debounced for performance
		 */
		var resizeHandler = function (event) {

			// If there's a timer, cancel it
			if (timeout) {
				window.cancelAnimationFrame(timeout);
			}

			// Setup debounce callback
			timeout = window.requestAnimationFrame((function () {
				sortContents(contents);
				publicAPIs.detect();
			}));

		};

		/**
		 * Destroy the current instantiation
		 */
		publicAPIs.destroy = function () {

			// Undo DOM changes
			if (current) {
				deactivate(current, settings);
			}

			// Remove event listeners
			window.removeEventListener('scroll', scrollHandler, false);
			if (settings.reflow) {
				window.removeEventListener('resize', resizeHandler, false);
			}

			// Reset variables
			contents = null;
			navItems = null;
			current = null;
			timeout = null;
			settings = null;

		};

		/**
		 * Initialize the current instantiation
		 */
		var init = function () {

			// Merge user options into defaults
			settings = extend(defaults, options || {});

			// Setup variables based on the current DOM
			publicAPIs.setup();

			// Find the currently active content
			publicAPIs.detect();

			// Setup event listeners
			window.addEventListener('scroll', scrollHandler, false);
			if (settings.reflow) {
				window.addEventListener('resize', resizeHandler, false);
			}

		};


		//
		// Initialize and return the public APIs
		//

		init();
		return publicAPIs;

	};


	//
	// Return the Constructor
	//

	return Constructor;

}));
/**
 * Sports Leagues Admin Scripts
 * https://anwp.pro
 *
 * Licensed under the GPLv2+ license.
 */

window.SportsLeaguesAdmin = window.SportsLeaguesAdmin || {};

( function( window, document, $, plugin ) {

	'use strict';

	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window = $( window );
		$c.body   = $( document.body );
	};

	plugin.bindEvents = function() {

		if ( 'loading' !== document.readyState ) {
			plugin.onPageReady();
		} else {
			document.addEventListener( 'DOMContentLoaded', plugin.onPageReady );
		}

		$c.body.on( 'click', '[data-sl-recalculate-index-tables]', function( e ) {
			e.preventDefault();

			var $this = $( this );
			$this.data( 'oldText', $this.text() );

			jQuery.ajax( {
				dataType: 'json',
				method: 'GET',
				data: { option: $this.siblings( 'select' ).val() },
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
					$this.text( 'processing request ...' );
				},
				url: anwp.rest_root + 'sports-leagues/v1/helper/recalculate-index-tables'
			} ).always( function() {
				$this.text( $this.data( 'oldText' ) );
			} ).fail( function() {
				toastr.error( 'ERROR !!!' );
			} );
		} );

		$c.body.on( 'click', '[data-sl-flush-plugin-cache]', function( e ) {
			e.preventDefault();

			var $this = $( this );
			$this.data( 'oldText', $this.text() );

			jQuery.ajax( {
				dataType: 'json',
				method: 'GET',
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
					$this.text( 'processing request ...' );
				},
				url: anwp.rest_root + 'sports-leagues/v1/helper/flush-plugin-cache'
			} ).always( function() {
				$this.text( $this.data( 'oldText' ) );
			} ).fail( function() {
				toastr.error( 'ERROR !!!' );
			} );
		} );
	};

	plugin.onPageReady = function() {
		plugin.initNavbarTabs();
		plugin.initTooltips();
		plugin.initGameListHelper();
		plugin.initGameEventModaal();
		plugin.initTextSearch();
		plugin.tableInputNavigation();
		plugin.initDependentOptions();
		plugin.initBtnPublishClick();
		plugin.initMetaboxScroll();
		plugin.initCollapseMenuClick();
		plugin.initTournamentCloneModaal();
	};

	plugin.initCollapseMenuClick = function() {
		var btnCollapse   = $( '.anwp-sl-collapse-menu' );

		if ( btnCollapse.length ) {
			btnCollapse.on( 'click', function( e ) {
				e.preventDefault();

				setUserSetting( 'anwp-sl-collapsed-menu', btnCollapse.closest( '.anwp-sl-menu-wrapper' ).hasClass( 'anwp-sl-collapsed-menu' ) ? '' : 'yes' );
				btnCollapse.closest( '.anwp-sl-menu-wrapper' ).toggleClass( 'anwp-sl-collapsed-menu' );
			} );
		}
	};

	plugin.initMetaboxScroll = function() {
		var metaboxNav = $( '#anwp-sl-metabox-page-nav' );

		if ( 'undefined' !== typeof Gumshoe && metaboxNav.length ) {
			new Gumshoe( '#anwp-sl-metabox-page-nav a', {

				// Active classes
				navClass: 'anwp-sl-metabox-page-nav--active', // applied to the nav list item
				contentClass: 'anwp-scroll-content--active', // applied to the content

				// Nested navigation
				nested: false, // if true, add classes to parents of active link
				nestedClass: '', // applied to the parent items

				// Offset & reflow
				offset: 60, // how far from the top of the page to activate a content area
				reflow: true, // if true, listen for reflows

				// Event support
				events: false // if true, emit custom events
			} );
		}

		if ( 'undefined' !== typeof SmoothScroll && metaboxNav.length ) {
			new SmoothScroll( '.anwp-sl-smooth-scroll', {
				speed: 300,
				speedAsDuration: true,
				offset: 50
			} );
		}
	};

	plugin.initBtnPublishClick = function() {
		var btnClick   = $( '#anwp-publish-click-proxy' );
		var btnPublish = $( '#publish' );

		if ( btnClick.length ) {
			btnClick.on( 'click', function( e ) {
				e.preventDefault();

				if ( btnClick.prop( 'disabled' ) ) {
					return false;
				}

				btnClick.prop( 'disabled', true );
				btnClick.next( '.spinner' ).addClass( 'is-active' );

				if ( btnPublish.length ) {
					btnPublish.trigger( 'click' );
				}
			} );
		}

		var btnClickNew = $( '#anwp-sl-publish-click-proxy' );

		if ( btnClickNew.length ) {
			btnClickNew.on( 'click', function( e ) {
				e.preventDefault();

				if ( btnClickNew.prop( 'disabled' ) ) {
					return false;
				}

				btnClickNew.prop( 'disabled', true );
				btnClickNew.find( '.spinner' ).addClass( 'is-active' );

				if ( btnPublish.length ) {
					btnPublish.trigger( 'click' );
				}
			} );
		}
	};

	plugin.tableInputNavigation = function() {
		$( '.anwp-input-table' ).find( 'td input' ).on( 'keyup', function( e ) {
			if ( 39 === e.which  ) { // right arrow
				$( this ).closest( 'td' ).next().find( 'input' ).focus();
			} else if ( 37 === e.which ) { // left arrow
				$( this ).closest( 'td' ).prev().find( 'input' ).focus();
			} else if ( 40 === e.which ) { // down arrow
				$( this ).closest( 'tr' ).next().find( 'td:eq(' + $( this ).closest( 'td' ).index() + ')' ).find( 'input' ).focus();
			} else if ( 38 === e.which ) { // up arrow
				$( this ).closest( 'tr' ).prev().find( 'td:eq(' + $( this ).closest( 'td' ).index() + ')' ).find( 'input' ).focus();
			}
		} );
	};

	plugin.initTextSearch = function() {
		var $input = $c.body.find( '#anwp-sl-live-text-search' );

		if ( ! $input.length ) {
			return false;
		}

		$input.on( 'input', function( e ) {
			e.preventDefault();

			var filter = $input.val().toLowerCase();

			$c.body.find( '#sports_leagues_text_metabox .anwp-sl-search-data' ).each( function() {
				var $this   = $( this );
				var search1 = $this.data( 'search-origin' );
				var search2 = $this.data( 'search-modified' );

				if ( search1.indexOf( filter ) !== -1 || search2.indexOf( filter ) !== -1 ) {
					$this.closest( '.cmb-type-anwp-text' ).removeClass( 'd-none' );
				} else {
					$this.closest( '.cmb-type-anwp-text' ).addClass( 'd-none' );
				}
			} );

			return false;
		} );
	};

	plugin.initGameListHelper = function() {
		if ( $c.body.find( 'input[name="_sl_date_from"]' ).length && typeof jQuery.datepicker !== 'undefined' ) {
			var inputFrom = $c.body.find( 'input[name="_sl_date_from"]' );
			var inputTo   = $c.body.find( 'input[name="_sl_date_to"]' );

			$( inputFrom ).add( inputTo ).datepicker( {
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
				beforeShow: function( input, inst ) {
					inst.dpDiv.addClass( 'cmb2-element' );
				}
			} );

			inputFrom.on( 'change', function() {
				inputTo.datepicker( 'option', 'minDate', inputFrom.val() );
			} );

			inputTo.on( 'change', function() {
				inputFrom.datepicker( 'option', 'maxDate', inputTo.val() );
			} );
		}
	};

	plugin.initGameEventModaal = function() {
		if ( ! $c.body.find( '.anwp-event-modaal' ).length || typeof $.fn.modaal === 'undefined' ) {
			return false;
		}

		$c.body.on( 'click', 'a.anwp-event-modaal', function( e ) {

			var $this = $( this );

			e.preventDefault();

			$c.body.find( '.anwp-event-modaal--active' ).removeClass( 'anwp-event-modaal--active' );
			$this.addClass( 'anwp-event-modaal--active' );

			$this.modaal( {start_open: true, type: 'inline', custom_class: 'anwp-b-wrap'} );
		} );

		$c.body.on( 'click', '.anwp-event-icon-field-icon', function( e ) {

			var $this     = $( this );
			var $openLink = $c.body.find( '.anwp-event-modaal--active' );

			e.preventDefault();

			$openLink.removeClass( 'anwp-event-modaal--active' );

			$openLink.closest( 'div.cmb-td' ).find( '.anwp-event-icon-field-value' ).val( $this.data( 'slug' ) );
			$openLink.closest( 'div.cmb-td' ).find( '.anwp-event-icon-field-wrap' ).css( 'background-image', $this.css( 'background-image' ) );

			$openLink.modaal( 'close' );
		} );

		$c.body.on( 'click', 'a.anwp-event-modaal__remove', function( e ) {
			e.preventDefault();

			var $this = $( this );

			$this.closest( 'div.cmb-td' ).find( '.anwp-event-icon-field-value' ).val( '' );
			$this.closest( 'div.cmb-td' ).find( '.anwp-event-icon-field-wrap' ).css( 'background-image', '' );
		} );

		$c.body.find( '#events_repeat' ).on( 'cmb2_add_row', function( e, $row ) {
			$row.find( '.anwp-event-modaal__remove' ).click();
		} );
	};

	plugin.initDependentOptions = function() {
		var $wrapper = $( '.anwp-b-wrap' );

		$wrapper.on( 'change', '.anwp-sl-parent-of-dependent', function() {

			var $parent = $( this );

			$wrapper.find( '.anwp-sl-dependent-field[data-parent="' + $parent.data( 'name' ) + '"]' ).each( function() {

				var childWrapper   = $( this );
				var childDataValue = childWrapper.data( 'value' ).split( ',' );

				if ( ( _.contains( childDataValue, $parent.val() ) && 'show' === childWrapper.data( 'action' ) ) || ( ! _.contains( childDataValue, $parent.val() ) && 'hide' === childWrapper.data( 'action' ) ) ) {
					childWrapper.closest( '.cmb-row' ).removeClass( 'd-none' );
				} else {
					childWrapper.closest( '.cmb-row' ).addClass( 'd-none' );
				}
			} );
		} );

		$wrapper.find( '.anwp-sl-parent-of-dependent--hidden' ).removeClass( 'anwp-sl-parent-of-dependent--hidden' );
		$wrapper.find( '.anwp-sl-parent-of-dependent' ).trigger( 'change' );
	};

	plugin.initTooltips = function() {
		tippy( '[data-sl_tippy]', {
			arrow: true,
			size: 'small'
		} );
	};

	plugin.initNavbarTabs = function() {
		if ( ! $c.body.find( '.anwp-metabox-tabs' ).length ) {
			return false;
		}

		$c.body.find( '.anwp-metabox-tabs' ).on( 'click', '.anwp-metabox-tabs__control-item', function( e ) {

			var $this  = $( this );
			var target = $( $this.data( 'target' ) );

			e.preventDefault();

			if ( $this.hasClass( 'anwp-active-tab' ) ) {
				return false;
			}

			$this.addClass( 'anwp-active-tab' ).siblings( '.anwp-metabox-tabs__control-item' ).removeClass( 'anwp-active-tab' );
			target.removeClass( 'd-none invisible' ).siblings( '.anwp-metabox-tabs__content-item' ).addClass( 'd-none' );
		} );

		$c.body.find( '.anwp-metabox-tabs .anwp-metabox-tabs__control-item:first-child' ).trigger( 'click' );
	};

	plugin.getChildStagesWithGroups = function( tournamentId, targetSelect ) {

		targetSelect.closest( 'div' ).find( '.spinner' ).addClass( 'is-active' );

		$.ajax( {
			dataType: 'json',
			method: 'GET',
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
			}.bind( this ),
			url: anwp.rest_root + 'sports-leagues/v1/get-tournament-groups/' + tournamentId
		} ).done( function( response ) {
			targetSelect.empty().append( response );
		}.bind( this ) ).fail( function( response ) {
			toastr.error( response.responseJSON.message ? response.responseJSON.message : 'Data Error' );
		} ).always( function() {
			targetSelect.closest( 'div' ).find( '.spinner' ).removeClass( 'is-active' );
		}.bind( this ) );
	};

	plugin.initTournamentCloneModaal = function() {
		const cloneLink  = $c.body.find( '.anwp-sl-tournament-clone-action' );
		const activeData = {
			link: false,
			process: false,
		};

		if ( $c.body.find( '#anwp-sl-tournament-clone-modaal' ).length && cloneLink.length ) {

			cloneLink.modaal(
				{
					content_source: '#anwp-sl-tournament-clone-modaal',
					custom_class: 'anwp-sl-shortcode-modal',
					hide_close: true,
					animation: 'none',
				}
			);

			cloneLink.on( 'click', function( e ) {
				e.preventDefault();
				activeData.link = $( this );
				activeData.link.modaal( 'open' );
			} );

			$( '#anwp-sl-tournament-clone-modaal__cancel' ).on( 'click', function( e ) {
				e.preventDefault();
				activeData.link.modaal( 'close' );
			} );

			$( '#anwp-sl-tournament-clone-modaal__clone' ).on( 'click', function( e ) {
				if ( activeData.process ) {
					return false;
				}

				activeData.process = true;
				e.preventDefault();

				const $this = $( this );
				$this.next( '.spinner' ).addClass( 'is-active' );

				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'sl_clone_tournament',
						nonce: anwpslGlobals.ajaxNonce,
						tournament_id: activeData.link.data( 'tournament-id' ),
						season_id: $c.body.find( '#anwp-sl-clone-season-id' ).val(),
					},
				} ).done( function( response ) {
					if ( response.success ) {
						location.href = response.data.link;
					} else {
						location.reload();
					}

					$this.next( '.spinner' ).removeClass( 'is-active' );
				} );
			} );
		}
	};

	$( plugin.init );
}( window, document, jQuery, window.SportsLeaguesAdmin ) );

/*! SmoothScroll v16.1.4 | (c) 2020 Chris Ferdinandi | MIT License | http://github.com/cferdinandi/smooth-scroll */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global = global || self, global.SmoothScroll = factory());
}(this, (function () { 'use strict';

	//
	// Default settings
	//

	var defaults = {

		// Selectors
		ignore: '[data-scroll-ignore]',
		header: null,
		topOnEmptyHash: true,

		// Speed & Duration
		speed: 500,
		speedAsDuration: false,
		durationMax: null,
		durationMin: null,
		clip: true,
		offset: 0,

		// Easing
		easing: 'easeInOutCubic',
		customEasing: null,

		// History
		updateURL: true,
		popstate: true,

		// Custom Events
		emitEvents: true

	};


	//
	// Utility Methods
	//

	/**
	 * Check if browser supports required methods
	 * @return {Boolean} Returns true if all required methods are supported
	 */
	var supports = function () {
		return (
			'querySelector' in document &&
			'addEventListener' in window &&
			'requestAnimationFrame' in window &&
			'closest' in window.Element.prototype
		);
	};

	/**
	 * Merge two or more objects together.
	 * @param   {Object}   objects  The objects to merge together
	 * @returns {Object}            Merged values of defaults and options
	 */
	var extend = function () {
		var merged = {};
		Array.prototype.forEach.call(arguments, function (obj) {
			for (var key in obj) {
				if (!obj.hasOwnProperty(key)) return;
				merged[key] = obj[key];
			}
		});
		return merged;
	};

	/**
	 * Check to see if user prefers reduced motion
	 * @param  {Object} settings Script settings
	 */
	var reduceMotion = function () {
		if ('matchMedia' in window && window.matchMedia('(prefers-reduced-motion)').matches) {
			return true;
		}
		return false;
	};

	/**
	 * Get the height of an element.
	 * @param  {Node} elem The element to get the height of
	 * @return {Number}    The element's height in pixels
	 */
	var getHeight = function (elem) {
		return parseInt(window.getComputedStyle(elem).height, 10);
	};

	/**
	 * Escape special characters for use with querySelector
	 * @author Mathias Bynens
	 * @link https://github.com/mathiasbynens/CSS.escape
	 * @param {String} id The anchor ID to escape
	 */
	var escapeCharacters = function (id) {

		// Remove leading hash
		if (id.charAt(0) === '#') {
			id = id.substr(1);
		}

		var string = String(id);
		var length = string.length;
		var index = -1;
		var codeUnit;
		var result = '';
		var firstCodeUnit = string.charCodeAt(0);
		while (++index < length) {
			codeUnit = string.charCodeAt(index);
			// Note: there’s no need to special-case astral symbols, surrogate
			// pairs, or lone surrogates.

			// If the character is NULL (U+0000), then throw an
			// `InvalidCharacterError` exception and terminate these steps.
			if (codeUnit === 0x0000) {
				throw new InvalidCharacterError(
					'Invalid character: the input contains U+0000.'
				);
			}

			if (
				// If the character is in the range [\1-\1F] (U+0001 to U+001F) or is
				// U+007F, […]
				(codeUnit >= 0x0001 && codeUnit <= 0x001F) || codeUnit == 0x007F ||
				// If the character is the first character and is in the range [0-9]
				// (U+0030 to U+0039), […]
				(index === 0 && codeUnit >= 0x0030 && codeUnit <= 0x0039) ||
				// If the character is the second character and is in the range [0-9]
				// (U+0030 to U+0039) and the first character is a `-` (U+002D), […]
				(
					index === 1 &&
					codeUnit >= 0x0030 && codeUnit <= 0x0039 &&
					firstCodeUnit === 0x002D
				)
			) {
				// http://dev.w3.org/csswg/cssom/#escape-a-character-as-code-point
				result += '\\' + codeUnit.toString(16) + ' ';
				continue;
			}

			// If the character is not handled by one of the above rules and is
			// greater than or equal to U+0080, is `-` (U+002D) or `_` (U+005F), or
			// is in one of the ranges [0-9] (U+0030 to U+0039), [A-Z] (U+0041 to
			// U+005A), or [a-z] (U+0061 to U+007A), […]
			if (
				codeUnit >= 0x0080 ||
				codeUnit === 0x002D ||
				codeUnit === 0x005F ||
				codeUnit >= 0x0030 && codeUnit <= 0x0039 ||
				codeUnit >= 0x0041 && codeUnit <= 0x005A ||
				codeUnit >= 0x0061 && codeUnit <= 0x007A
			) {
				// the character itself
				result += string.charAt(index);
				continue;
			}

			// Otherwise, the escaped character.
			// http://dev.w3.org/csswg/cssom/#escape-a-character
			result += '\\' + string.charAt(index);

		}

		// Return sanitized hash
		return '#' + result;

	};

	/**
	 * Calculate the easing pattern
	 * @link https://gist.github.com/gre/1650294
	 * @param   {Object} settings Easing pattern
	 * @param   {Number} time     Time animation should take to complete
	 * @returns {Number}
	 */
	var easingPattern = function (settings, time) {
		var pattern;

		// Default Easing Patterns
		if (settings.easing === 'easeInQuad') pattern = time * time; // accelerating from zero velocity
		if (settings.easing === 'easeOutQuad') pattern = time * (2 - time); // decelerating to zero velocity
		if (settings.easing === 'easeInOutQuad') pattern = time < 0.5 ? 2 * time * time : -1 + (4 - 2 * time) * time; // acceleration until halfway, then deceleration
		if (settings.easing === 'easeInCubic') pattern = time * time * time; // accelerating from zero velocity
		if (settings.easing === 'easeOutCubic') pattern = (--time) * time * time + 1; // decelerating to zero velocity
		if (settings.easing === 'easeInOutCubic') pattern = time < 0.5 ? 4 * time * time * time : (time - 1) * (2 * time - 2) * (2 * time - 2) + 1; // acceleration until halfway, then deceleration
		if (settings.easing === 'easeInQuart') pattern = time * time * time * time; // accelerating from zero velocity
		if (settings.easing === 'easeOutQuart') pattern = 1 - (--time) * time * time * time; // decelerating to zero velocity
		if (settings.easing === 'easeInOutQuart') pattern = time < 0.5 ? 8 * time * time * time * time : 1 - 8 * (--time) * time * time * time; // acceleration until halfway, then deceleration
		if (settings.easing === 'easeInQuint') pattern = time * time * time * time * time; // accelerating from zero velocity
		if (settings.easing === 'easeOutQuint') pattern = 1 + (--time) * time * time * time * time; // decelerating to zero velocity
		if (settings.easing === 'easeInOutQuint') pattern = time < 0.5 ? 16 * time * time * time * time * time : 1 + 16 * (--time) * time * time * time * time; // acceleration until halfway, then deceleration

		// Custom Easing Patterns
		if (!!settings.customEasing) pattern = settings.customEasing(time);

		return pattern || time; // no easing, no acceleration
	};

	/**
	 * Determine the document's height
	 * @returns {Number}
	 */
	var getDocumentHeight = function () {
		return Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight
		);
	};

	/**
	 * Calculate how far to scroll
	 * Clip support added by robjtede - https://github.com/cferdinandi/smooth-scroll/issues/405
	 * @param {Element} anchor       The anchor element to scroll to
	 * @param {Number}  headerHeight Height of a fixed header, if any
	 * @param {Number}  offset       Number of pixels by which to offset scroll
	 * @param {Boolean} clip         If true, adjust scroll distance to prevent abrupt stops near the bottom of the page
	 * @returns {Number}
	 */
	var getEndLocation = function (anchor, headerHeight, offset, clip) {
		var location = 0;
		if (anchor.offsetParent) {
			do {
				location += anchor.offsetTop;
				anchor = anchor.offsetParent;
			} while (anchor);
		}
		location = Math.max(location - headerHeight - offset, 0);
		if (clip) {
			location = Math.min(location, getDocumentHeight() - window.innerHeight);
		}
			return location;
	};

	/**
	 * Get the height of the fixed header
	 * @param  {Node}   header The header
	 * @return {Number}        The height of the header
	 */
	var getHeaderHeight = function (header) {
		return !header ? 0 : (getHeight(header) + header.offsetTop);
	};

	/**
	 * Calculate the speed to use for the animation
	 * @param  {Number} distance The distance to travel
	 * @param  {Object} settings The plugin settings
	 * @return {Number}          How fast to animate
	 */
	var getSpeed = function (distance, settings) {
		var speed = settings.speedAsDuration ? settings.speed : Math.abs(distance / 1000 * settings.speed);
		if (settings.durationMax && speed > settings.durationMax) return settings.durationMax;
		if (settings.durationMin && speed < settings.durationMin) return settings.durationMin;
		return parseInt(speed, 10);
	};

	var setHistory = function (options) {

		// Make sure this should run
		if (!history.replaceState || !options.updateURL || history.state) return;

		// Get the hash to use
		var hash = window.location.hash;
		hash = hash ? hash : '';

		// Set a default history
		history.replaceState(
			{
				smoothScroll: JSON.stringify(options),
				anchor: hash ? hash : window.pageYOffset
			},
			document.title,
			hash ? hash : window.location.href
		);

	};

	/**
	 * Update the URL
	 * @param  {Node}    anchor  The anchor that was scrolled to
	 * @param  {Boolean} isNum   If true, anchor is a number
	 * @param  {Object}  options Settings for Smooth Scroll
	 */
	var updateURL = function (anchor, isNum, options) {

		// Bail if the anchor is a number
		if (isNum) return;

		// Verify that pushState is supported and the updateURL option is enabled
		if (!history.pushState || !options.updateURL) return;

		// Update URL
		history.pushState(
			{
				smoothScroll: JSON.stringify(options),
				anchor: anchor.id
			},
			document.title,
			anchor === document.documentElement ? '#top' : '#' + anchor.id
		);

	};

	/**
	 * Bring the anchored element into focus
	 * @param {Node}     anchor      The anchor element
	 * @param {Number}   endLocation The end location to scroll to
	 * @param {Boolean}  isNum       If true, scroll is to a position rather than an element
	 */
	var adjustFocus = function (anchor, endLocation, isNum) {

		// Is scrolling to top of page, blur
		if (anchor === 0) {
			document.body.focus();
		}

		// Don't run if scrolling to a number on the page
		if (isNum) return;

		// Otherwise, bring anchor element into focus
		anchor.focus();
		if (document.activeElement !== anchor) {
			anchor.setAttribute('tabindex', '-1');
			anchor.focus();
			anchor.style.outline = 'none';
		}
		window.scrollTo(0 , endLocation);

	};

	/**
	 * Emit a custom event
	 * @param  {String} type    The event type
	 * @param  {Object} options The settings object
	 * @param  {Node}   anchor  The anchor element
	 * @param  {Node}   toggle  The toggle element
	 */
	var emitEvent = function (type, options, anchor, toggle) {
		if (!options.emitEvents || typeof window.CustomEvent !== 'function') return;
		var event = new CustomEvent(type, {
			bubbles: true,
			detail: {
				anchor: anchor,
				toggle: toggle
			}
		});
		document.dispatchEvent(event);
	};


	//
	// SmoothScroll Constructor
	//

	var SmoothScroll = function (selector, options) {

		//
		// Variables
		//

		var smoothScroll = {}; // Object for public APIs
		var settings, toggle, fixedHeader, animationInterval;


		//
		// Methods
		//

		/**
		 * Cancel a scroll-in-progress
		 */
		smoothScroll.cancelScroll = function (noEvent) {
			cancelAnimationFrame(animationInterval);
			animationInterval = null;
			if (noEvent) return;
			emitEvent('scrollCancel', settings);
		};

		/**
		 * Start/stop the scrolling animation
		 * @param {Node|Number} anchor  The element or position to scroll to
		 * @param {Element}     toggle  The element that toggled the scroll event
		 * @param {Object}      options
		 */
		smoothScroll.animateScroll = function (anchor, toggle, options) {

			// Cancel any in progress scrolls
			smoothScroll.cancelScroll();

			// Local settings
			var _settings = extend(settings || defaults, options || {}); // Merge user options with defaults

			// Selectors and variables
			var isNum = Object.prototype.toString.call(anchor) === '[object Number]' ? true : false;
			var anchorElem = isNum || !anchor.tagName ? null : anchor;
			if (!isNum && !anchorElem) return;
			var startLocation = window.pageYOffset; // Current location on the page
			if (_settings.header && !fixedHeader) {
				// Get the fixed header if not already set
				fixedHeader = document.querySelector(_settings.header);
			}
			var headerHeight = getHeaderHeight(fixedHeader);
			var endLocation = isNum ? anchor : getEndLocation(anchorElem, headerHeight, parseInt((typeof _settings.offset === 'function' ? _settings.offset(anchor, toggle) : _settings.offset), 10), _settings.clip); // Location to scroll to
			var distance = endLocation - startLocation; // distance to travel
			var documentHeight = getDocumentHeight();
			var timeLapsed = 0;
			var speed = getSpeed(distance, _settings);
			var start, percentage, position;

			/**
			 * Stop the scroll animation when it reaches its target (or the bottom/top of page)
			 * @param {Number} position Current position on the page
			 * @param {Number} endLocation Scroll to location
			 * @param {Number} animationInterval How much to scroll on this loop
			 */
			var stopAnimateScroll = function (position, endLocation) {

				// Get the current location
				var currentLocation = window.pageYOffset;

				// Check if the end location has been reached yet (or we've hit the end of the document)
				if (position == endLocation || currentLocation == endLocation || ((startLocation < endLocation && window.innerHeight + currentLocation) >= documentHeight)) {

					// Clear the animation timer
					smoothScroll.cancelScroll(true);

					// Bring the anchored element into focus
					adjustFocus(anchor, endLocation, isNum);

					// Emit a custom event
					emitEvent('scrollStop', _settings, anchor, toggle);

					// Reset start
					start = null;
					animationInterval = null;

					return true;

				}
			};

			/**
			 * Loop scrolling animation
			 */
			var loopAnimateScroll = function (timestamp) {
				if (!start) { start = timestamp; }
				timeLapsed += timestamp - start;
				percentage = speed === 0 ? 0 : (timeLapsed / speed);
				percentage = (percentage > 1) ? 1 : percentage;
				position = startLocation + (distance * easingPattern(_settings, percentage));
				window.scrollTo(0, Math.floor(position));
				if (!stopAnimateScroll(position, endLocation)) {
					animationInterval = window.requestAnimationFrame(loopAnimateScroll);
					start = timestamp;
				}
			};

			/**
			 * Reset position to fix weird iOS bug
			 * @link https://github.com/cferdinandi/smooth-scroll/issues/45
			 */
			if (window.pageYOffset === 0) {
				window.scrollTo(0, 0);
			}

			// Update the URL
			updateURL(anchor, isNum, _settings);

			// If the user prefers reduced motion, jump to location
			if (reduceMotion()) {
				adjustFocus(anchor, Math.floor(endLocation), false);
				return;
			}

			// Emit a custom event
			emitEvent('scrollStart', _settings, anchor, toggle);

			// Start scrolling animation
			smoothScroll.cancelScroll(true);
			window.requestAnimationFrame(loopAnimateScroll);

		};

		/**
		 * If smooth scroll element clicked, animate scroll
		 */
		var clickHandler = function (event) {

			// Don't run if event was canceled but still bubbled up
			// By @mgreter - https://github.com/cferdinandi/smooth-scroll/pull/462/
			if (event.defaultPrevented) return;

			// Don't run if right-click or command/control + click or shift + click
			if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey) return;

			// Check if event.target has closest() method
			// By @totegi - https://github.com/cferdinandi/smooth-scroll/pull/401/
			if (!('closest' in event.target)) return;

			// Check if a smooth scroll link was clicked
			toggle = event.target.closest(selector);
			if (!toggle || toggle.tagName.toLowerCase() !== 'a' || event.target.closest(settings.ignore)) return;

			// Only run if link is an anchor and points to the current page
			if (toggle.hostname !== window.location.hostname || toggle.pathname !== window.location.pathname || !/#/.test(toggle.href)) return;

			// Get an escaped version of the hash
			var hash;
			try {
				hash = escapeCharacters(decodeURIComponent(toggle.hash));
			} catch(e) {
				hash = escapeCharacters(toggle.hash);
			}

			// Get the anchored element
			var anchor;
			if (hash === '#') {
				if (!settings.topOnEmptyHash) return;
				anchor = document.documentElement;
			} else {
				anchor = document.querySelector(hash);
			}
			anchor = !anchor && hash === '#top' ? document.documentElement : anchor;

			// If anchored element exists, scroll to it
			if (!anchor) return;
			event.preventDefault();
			setHistory(settings);
			smoothScroll.animateScroll(anchor, toggle);

		};

		/**
		 * Animate scroll on popstate events
		 */
		var popstateHandler = function () {

			// Stop if history.state doesn't exist (ex. if clicking on a broken anchor link).
			// fixes `Cannot read property 'smoothScroll' of null` error getting thrown.
			if (history.state === null) return;

			// Only run if state is a popstate record for this instantiation
			if (!history.state.smoothScroll || history.state.smoothScroll !== JSON.stringify(settings)) return;

			// Get the anchor
			var anchor = history.state.anchor;
			if (typeof anchor === 'string' && anchor) {
				anchor = document.querySelector(escapeCharacters(history.state.anchor));
				if (!anchor) return;
			}

			// Animate scroll to anchor link
			smoothScroll.animateScroll(anchor, null, {updateURL: false});

		};

		/**
		 * Destroy the current initialization.
		 */
		smoothScroll.destroy = function () {

			// If plugin isn't already initialized, stop
			if (!settings) return;

			// Remove event listeners
			document.removeEventListener('click', clickHandler, false);
			window.removeEventListener('popstate', popstateHandler, false);

			// Cancel any scrolls-in-progress
			smoothScroll.cancelScroll();

			// Reset variables
			settings = null;
			toggle = null;
			fixedHeader = null;
			animationInterval = null;

		};

		/**
		 * Initialize Smooth Scroll
		 * @param {Object} options User settings
		 */
		var init = function () {

			// feature test
			if (!supports()) throw 'Smooth Scroll: This browser does not support the required JavaScript methods and browser APIs.';

			// Destroy any existing initializations
			smoothScroll.destroy();

			// Selectors and variables
			settings = extend(defaults, options || {}); // Merge user options with defaults
			fixedHeader = settings.header ? document.querySelector(settings.header) : null; // Get the fixed header

			// When a toggle is clicked, run the click handler
			document.addEventListener('click', clickHandler, false);

			// If updateURL and popState are enabled, listen for pop events
			if (settings.updateURL && settings.popstate) {
				window.addEventListener('popstate', popstateHandler, false);
			}

		};


		//
		// Initialize plugin
		//

		init();


		//
		// Public APIs
		//

		return smoothScroll;

	};

	return SmoothScroll;

})));
