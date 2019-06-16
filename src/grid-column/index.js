import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Internal dependencies
 */
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';

const { name } = metadata;

export { metadata, name };

registerBlockType(name, {
	...metadata,
	title: __( 'Grid Column' ),
	parent: [ 'bootstrap-hooks/grid' ],
	icon,
	description: __( 'A single column within a grid block.' ),
	supports: {
		inserter: false,
		reusable: false,
		html: false,
	},
	getEditWrapperProps( attributes ) {
		const { size } = attributes;

		const classes = classnames([
			`col-sm-${size.sm}`,
			`col-md-${size.md}`,
			`col-lg-${size.lg}`,
			`col-xl-${size.xl}`,
		])

		return {
			className: classes,
		};
		/*
		const { width } = attributes;
		if ( Number.isFinite( width ) ) {
			return {
				style: {
					flexBasis: width + '%',
				},
			};
		}
		*/
	},
	edit,
	save,
});
