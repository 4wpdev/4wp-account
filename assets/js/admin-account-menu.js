( function () {
	'use strict';

	document.querySelectorAll( '.forwp-account-add-custom-link' ).forEach( function ( btn ) {
		const tableId = btn.getAttribute( 'data-table' );
		const templateId = btn.getAttribute( 'data-template' );
		const table = tableId ? document.getElementById( tableId ) : null;
		const template = templateId ? document.getElementById( templateId ) : null;

		if ( ! table || ! template ) {
			return;
		}

		let nextIndex = table.querySelectorAll( 'tbody tr' ).length;

		btn.addEventListener( 'click', function () {
			const html = template.innerHTML.replaceAll( '__INDEX__', 'new_' + nextIndex );
			table.querySelector( 'tbody' ).insertAdjacentHTML( 'beforeend', html );
			nextIndex += 1;
		} );
	} );
} )();
