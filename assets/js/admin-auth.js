( function () {
	'use strict';

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
} )();
