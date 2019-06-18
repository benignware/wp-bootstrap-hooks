/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { InnerBlocks } = wp.editor;

export default function save( { attributes } ) {
	const { columns, verticalAlignment } = attributes;

	const wrapperClasses = classnames(
		'card-deck',
		`has-${ columns }-columns`,
		{
			[ `are-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
		}
	);

	return (
		<div className={ wrapperClasses }>
			<InnerBlocks.Content />
		</div>
	);
}
