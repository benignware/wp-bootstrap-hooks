/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Internal dependencies
 */
// import deprecated from './deprecated';
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';

const { name } = metadata;

export { metadata, name };

registerBlockType(name, {
	...metadata,
	title: __( 'Card Deck' ),
	icon,
	description: __( 'Add a block that displays content in multiple cards, then add whatever content blocks you’d like.' ),
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	// deprecated,
	edit,
	save,
});
