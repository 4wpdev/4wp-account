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

	var userIcon = createElement(
		'svg',
		{
			className: 'forwp-account-nav-icon forwp-account-nav-icon--user',
			width: 20,
			height: 20,
			viewBox: '0 0 24 24',
			fill: 'none',
			'aria-hidden': true,
		},
		createElement( 'path', {
			d: 'M12 3a5 5 0 1 1-5 5 5 5 0 0 1 5-5Z',
			fill: 'currentColor',
			opacity: '.25',
		} ),
		createElement( 'path', {
			d: 'M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z',
			stroke: 'currentColor',
			strokeWidth: 1.8,
		} ),
		createElement( 'path', {
			d: 'M4 20a8 8 0 0 1 16 0',
			stroke: 'currentColor',
			strokeWidth: 1.8,
			strokeLinecap: 'round',
		} ),
		createElement( 'circle', { cx: 18, cy: 6, r: 3, fill: 'currentColor' } )
	);

	registerBlockType( 'forwp/account-menu', {
		edit: function Edit() {
			var blockProps = useBlockProps( {
				className: 'forwp-account-menu forwp-account-menu--editor',
			} );

			return createElement(
				'div',
				blockProps,
				createElement(
					'span',
					{
						className: 'forwp-account-menu__button forwp-account-menu__button--editor',
						'aria-hidden': true,
					},
					createElement( 'span', { className: 'forwp-account-menu__icon' }, userIcon )
				),
				createElement(
					'p',
					{ className: 'forwp-account-menu__hint' },
					__( 'Account menu dropdown on frontend', '4wp-account' )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
