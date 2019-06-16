/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { InnerBlocks } = wp.editor;

export default function save( { attributes } ) {
	const { verticalAlignment, width } = attributes;

	const wrapperClasses = classnames( {
		[ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
	} );

	let style;
	if ( Number.isFinite( width ) ) {
		style = { flexBasis: width + '%' };
	}

	return (
		<div className={ wrapperClasses } style={ style }>
			<InnerBlocks.Content />
		</div>
	);
}
