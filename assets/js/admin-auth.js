( function () {
	'use strict';

	function initCopyButtons() {
		document.querySelectorAll( '.forwp-account-uri-field__copy' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				const targetId = btn.getAttribute( 'data-copy-target' );
				const input = targetId ? document.getElementById( targetId ) : null;

				if ( ! input ) {
					return;
				}

				input.select();
				input.setSelectionRange( 0, input.value.length );

				const copiedLabel = btn.getAttribute( 'data-copied-label' ) || 'Copied!';
				const defaultLabel = btn.textContent;

				const markCopied = function () {
					btn.textContent = copiedLabel;
					window.setTimeout( function () {
						btn.textContent = defaultLabel;
					}, 1500 );
				};

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( input.value ).then( markCopied ).catch( function () {
						document.execCommand( 'copy' );
						markCopied();
					} );
					return;
				}

				document.execCommand( 'copy' );
				markCopied();
			} );
		} );
	}

	function initProviderRegistry() {
		const wrap = document.getElementById( 'forwp-account-provider-detail-wrap' );
		const intro = document.getElementById( 'forwp-account-auth-intro' );
		const title = document.getElementById( 'forwp-account-provider-detail-title' );
		const back = document.getElementById( 'forwp-account-provider-back' );
		const openInput = document.getElementById( 'forwp_account_open_provider' );
		const cards = document.querySelectorAll( '.forwp-account-provider-card' );
		const details = wrap ? wrap.querySelectorAll( '.forwp-account-provider-detail' ) : [];

		if ( ! wrap || ! cards.length ) {
			return;
		}

		function showOverview() {
			if ( intro ) {
				intro.hidden = false;
			}
			wrap.hidden = true;
			details.forEach( function ( detail ) {
				detail.hidden = true;
			} );
			cards.forEach( function ( card ) {
				card.classList.remove( 'is-selected' );
				card.setAttribute( 'aria-pressed', 'false' );
			} );
			if ( openInput ) {
				openInput.value = '';
			}
		}

		function openProvider( slug ) {
			if ( ! slug ) {
				showOverview();
				return;
			}

			const card = document.querySelector( '.forwp-account-provider-card[data-provider="' + slug + '"]' );
			const detail = wrap.querySelector( '.forwp-account-provider-detail[data-provider="' + slug + '"]' );

			if ( ! detail ) {
				return;
			}

			if ( intro ) {
				intro.hidden = true;
			}
			wrap.hidden = false;
			details.forEach( function ( panel ) {
				panel.hidden = panel !== detail;
			} );
			cards.forEach( function ( item ) {
				const on = item.getAttribute( 'data-provider' ) === slug;
				item.classList.toggle( 'is-selected', on );
				item.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			} );
			if ( title ) {
				title.textContent = ( card && card.getAttribute( 'data-label' ) ) || slug;
			}
			if ( openInput ) {
				openInput.value = slug;
			}
		}

		cards.forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				openProvider( card.getAttribute( 'data-provider' ) );
			} );
			card.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					openProvider( card.getAttribute( 'data-provider' ) );
				}
			} );
		} );

		if ( back ) {
			back.addEventListener( 'click', showOverview );
		}

		function setCardEnabled( slug, on ) {
			const card = document.querySelector( '.forwp-account-provider-card[data-provider="' + slug + '"]' );
			if ( ! card ) {
				return;
			}

			card.classList.toggle( 'is-enabled', on );
			card.setAttribute( 'data-enabled', on ? '1' : '0' );

			const onBadge = card.querySelector( '.forwp-account-badge--on' );
			if ( onBadge ) {
				onBadge.hidden = ! on;
			}
		}

		wrap.querySelectorAll( 'input[name^="forwp_account_provider_enabled_"]' ).forEach( function ( input ) {
			if ( input.type !== 'checkbox' ) {
				return;
			}

			input.addEventListener( 'change', function () {
				const name = input.getAttribute( 'name' ) || '';
				const slug = name.replace( /^forwp_account_provider_enabled_/, '' );
				if ( slug ) {
					setCardEnabled( slug, input.checked );
				}
			} );
		} );

		openProvider( wrap.getAttribute( 'data-open-provider' ) || '' );
	}

	initCopyButtons();
	initProviderRegistry();
} )();
