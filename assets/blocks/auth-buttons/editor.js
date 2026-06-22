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

	registerBlockType( 'forwp/auth-buttons', {
		edit: function Edit( props ) {
			var providers = props.attributes.providers || 'gmail,github';
			var blockProps = useBlockProps( {
				className: 'forwp-account-auth-buttons-editor',
			} );

			return createElement(
				'div',
				blockProps,
				createElement( 'strong', null, __( '4WP Sign-in Buttons', '4wp-account' ) ),
				createElement( 'p', null, providers )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
