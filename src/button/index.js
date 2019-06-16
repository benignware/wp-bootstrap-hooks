/**
 * WordPress dependencies
 */
const { __, _x } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
/**
 * Internal dependencies
 */
import deprecated from './deprecated';
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';

const { name } = metadata;

export { metadata, name };

export const settings = {
	title: __( 'Button' ),
	description: __( 'Prompt visitors to take action with a button-style link.' ),
	icon,
	keywords: [ __( 'link' ) ],
	supports: {
		align: true,
		alignWide: false,
	},
	/*styles: [
		{ name: 'default', label: _x( 'Default', 'block style' ), isDefault: true },
		{ name: 'outline', label: __( 'Outline' ) },
		// { name: 'squared', label: _x( 'Squared', 'block style' ) },
	],*/
	edit,
	save,
	deprecated,
};

registerBlockType( name, {
	...metadata,
	...settings
});
