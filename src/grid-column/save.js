/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { InnerBlocks } = wp.editor;

export default function save( { attributes } ) {
	const { verticalAlignment, size, width } = attributes;

	const wrapperClasses = classnames(
		`col-sm-${size.sm}`,
		`col-md-${size.md}`,
		`col-lg-${size.lg}`,
		`col-xl-${size.xl}`,
		{
			[ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
		}
	);

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
