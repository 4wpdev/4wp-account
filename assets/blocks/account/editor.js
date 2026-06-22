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

	registerBlockType( 'forwp/account', {
		edit: function Edit() {
			var blockProps = useBlockProps( {
				className: 'forwp-account-block-editor',
			} );

			return createElement(
				'div',
				blockProps,
				createElement( 'strong', null, __( '4WP Account', '4wp-account' ) ),
				createElement(
					'p',
					null,
					__(
						'Sign-in for guests or account cabinet for logged-in users.',
						'4wp-account'
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
