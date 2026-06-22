( function () {
	'use strict';

	var DROPDOWN_ACTIVE_CLASS = 'forwp-account-menu__dropdown--active';

	function bindDropdownToggle( wrapper ) {
		var btn = wrapper.querySelector( '.forwp-account-menu__button' );
		var dropdown = wrapper.querySelector( '.forwp-account-menu__dropdown' );
		if ( ! btn || ! dropdown ) {
			return;
		}

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			var isOpen = dropdown.classList.contains( DROPDOWN_ACTIVE_CLASS );
			if ( isOpen ) {
				dropdown.classList.remove( DROPDOWN_ACTIVE_CLASS );
				btn.setAttribute( 'aria-expanded', 'false' );
			} else {
				dropdown.classList.add( DROPDOWN_ACTIVE_CLASS );
				btn.setAttribute( 'aria-expanded', 'true' );
			}
		} );

		dropdown.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! wrapper.contains( e.target ) ) {
				dropdown.classList.remove( DROPDOWN_ACTIVE_CLASS );
				btn.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	function initWidget( wrapper ) {
		if ( wrapper.getAttribute( 'data-forwp-account-menu-inited' ) === '1' ) {
			return;
		}
		wrapper.setAttribute( 'data-forwp-account-menu-inited', '1' );
		bindDropdownToggle( wrapper );
	}

	function init() {
		document.querySelectorAll( '.forwp-account-menu[data-forwp-account-menu="1"]' ).forEach( initWidget );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
	window.addEventListener( 'load', init );
} )();
