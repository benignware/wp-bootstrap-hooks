import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Internal dependencies
 */
// import deprecated from './deprecated';
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';
// import transforms from './transforms';

const { name } = metadata;

export { metadata, name };

export const settings = {
	title: __( 'Card' ),
	description: __( 'Flexible and extensible content container with multiple variants and options.' ),
	parent: 'bootstrap-hooks/card-deck',
	icon,
	keywords: [ __( 'image' ), __( 'video' ) ],
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	// transforms,
	edit,
	save,
	// deprecated,
	getEditWrapperProps( attributes ) {
		const { backgroundColor, textColor, parent } = attributes;

		if (!parent) {
			return null;
		}

		const classes = classnames(
			// className,
			'card',
			{
				[ backgroundColor && `bg-${backgroundColor}` ]: backgroundColor,
				[ textColor && `text-${textColor}` ]: textColor,
				// 'has-media-on-the-right': 'right' === mediaPosition,
				// 'is-selected': isSelected,
				// [ backgroundColor.class ]: backgroundColor.class,
				// 'is-stacked-on-mobile': isStackedOnMobile,
				// [ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
				// 'is-image-fill': imageFill,
			}
		);

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
};

registerBlockType(name, {
	...metadata,
	...settings
});
