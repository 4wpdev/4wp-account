( function () {
	'use strict';

	const card = document.getElementById( 'forwp-account-preview-card' );
	const stage = document.getElementById( 'forwp-account-preview-stage' );
	if ( ! card ) {
		return;
	}

	const varMap = {
		bg: '--forwp-account-bg',
		border: '--forwp-account-border',
		'border-width': '--forwp-account-border-width',
		radius: '--forwp-account-radius',
	};
	const previewWidth = document.getElementById( 'forwp-account-preview-width' );
	const socialWrap = card.querySelector( '.forwp-account-signin' );

	function hexOrDefault( value, fallback ) {
		return /^#[0-9a-fA-F]{6}$/.test( value || '' ) ? value : fallback;
	}

	function applyFromControl( control ) {
		const key = control.getAttribute( 'data-appearance-key' );
		const cssVar = varMap[ key ];
		if ( ! cssVar ) {
			return;
		}

		let value = ( control.value || '' ).trim();
		const fallback = control.getAttribute( 'data-default' ) || '';

		if ( key === 'bg' || key === 'border' ) {
			value = hexOrDefault( value, fallback );
		} else {
			value = ( value || fallback ) + 'px';
		}

		card.style.setProperty( cssVar, value );
	}

	function refreshPreview() {
		document.querySelectorAll( '[data-appearance-key]' ).forEach( applyFromControl );
	}

	function setPreviewWidth( width ) {
		const narrow = width === 'narrow';
		if ( stage ) {
			stage.classList.toggle( 'is-narrow', narrow );
		}
		document.querySelectorAll( '[data-preview-width]' ).forEach( function ( other ) {
			other.classList.toggle( 'is-active', other.getAttribute( 'data-preview-width' ) === width );
		} );
		if ( previewWidth ) {
			previewWidth.value = narrow ? 'narrow' : 'wide';
		}
	}

	function setSocialStyle( style ) {
		if ( ! socialWrap ) {
			return;
		}
		socialWrap.classList.remove(
			'forwp-account-signin--style-native',
			'forwp-account-signin--style-solid',
			'forwp-account-signin--style-icons'
		);
		socialWrap.classList.add( 'forwp-account-signin--style-' + style );
		socialWrap.querySelectorAll( '.forwp-account-signin-btn' ).forEach( function ( btn ) {
			btn.classList.remove(
				'forwp-account-signin-btn--native',
				'forwp-account-signin-btn--solid',
				'forwp-account-signin-btn--icons'
			);
			btn.classList.add( 'forwp-account-signin-btn--' + style );
		} );
	}

	document.querySelectorAll( 'input[data-sync-text]' ).forEach( function ( swatch ) {
		swatch.addEventListener( 'input', function () {
			const text = document.getElementById( swatch.getAttribute( 'data-sync-text' ) );
			if ( text ) {
				text.value = swatch.value;
				applyFromControl( text );
			}
		} );
	} );

	document.querySelectorAll( '[data-appearance-key]' ).forEach( function ( control ) {
		control.addEventListener( 'input', function () {
			applyFromControl( control );

			if ( control.type === 'text' ) {
				const swatch = document.getElementById( control.id + '_swatch' );
				if ( swatch && /^#[0-9a-fA-F]{6}$/.test( control.value ) ) {
					swatch.value = control.value;
				}
			}

			const readout = document.querySelector( '[data-range-for="' + control.id + '"]' );
			if ( readout && control.type === 'range' ) {
				readout.textContent = control.value + 'px';
			}
		} );
	} );

	document.querySelectorAll( '[data-preview-width]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			setPreviewWidth( btn.getAttribute( 'data-preview-width' ) === 'narrow' ? 'narrow' : 'wide' );
		} );
	} );

	document.querySelectorAll( '[data-social-style]' ).forEach( function ( radio ) {
		radio.addEventListener( 'change', function () {
			if ( radio.checked ) {
				setSocialStyle( radio.value );
			}
		} );
	} );

	const reset = document.getElementById( 'forwp-account-appearance-reset' );
	if ( reset ) {
		reset.addEventListener( 'click', function () {
			document.querySelectorAll( '[data-appearance-key]' ).forEach( function ( control ) {
				const fallback = control.getAttribute( 'data-default' ) || '';
				if ( control.type === 'text' ) {
					control.value = '';
					const swatch = document.getElementById( control.id + '_swatch' );
					if ( swatch && /^#[0-9a-fA-F]{6}$/.test( fallback ) ) {
						swatch.value = fallback;
					}
				} else {
					control.value = fallback;
					const readout = document.querySelector( '[data-range-for="' + control.id + '"]' );
					if ( readout ) {
						readout.textContent = fallback + 'px';
					}
				}
			} );

			const native = document.querySelector( '[data-social-style="native"]' );
			if ( native ) {
				native.checked = true;
				setSocialStyle( 'native' );
			}
			setPreviewWidth( 'wide' );
			refreshPreview();
		} );
	}
} )();
