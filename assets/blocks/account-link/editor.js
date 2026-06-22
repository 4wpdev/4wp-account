( function ( wp ) {
	'use strict';

	var blocks = wp.blocks;
	var blockEditor = wp.blockEditor;
	var element = wp.element;
	var i18n = wp.i18n;

	if ( ! blocks || ! blockEditor || ! element ) {
		return;
	}

	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var createElement = element.createElement;
	var __ = i18n.__;

	var guestIcon = createElement(
		'svg',
		{
			className: 'forwp-account-nav-icon forwp-account-nav-icon--guest',
			width: 20,
			height: 20,
			viewBox: '0 0 24 24',
			fill: 'none',
			'aria-hidden': true,
		},
		createElement( 'path', {
			d: 'M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z',
			stroke: 'currentColor',
			strokeWidth: '1.8',
		} ),
		createElement( 'path', {
			d: 'M4 20a8 8 0 0 1 16 0',
			stroke: 'currentColor',
			strokeWidth: '1.8',
			strokeLinecap: 'round',
		} )
	);

	registerBlockType( 'forwp/account-link', {
		edit: function Edit() {
			var blockProps = useBlockProps( {
				className: 'forwp-account-link-block-editor',
			} );

			return createElement(
				'a',
				Object.assign( {}, blockProps, {
					href: '#',
					className:
						( blockProps.className || '' ) +
						' forwp-account-nav-link forwp-account-nav-link--editor',
					onClick: function ( event ) {
						event.preventDefault();
					},
				} ),
				guestIcon,
				createElement(
					'span',
					{ className: 'forwp-account-nav-link__text' },
					__( 'Sign in / My account', '4wp-account' )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
