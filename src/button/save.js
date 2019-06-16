/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const {
	RichText,
	getColorClassName,
} = wp.editor;

export default function save( { attributes } ) {
	const {
		url,
		text,
		title,
		backgroundColor,
		textColor,
		customBackgroundColor,
		customTextColor,
		outline
	} = attributes;

	const textClass = getColorClassName( 'color', textColor );
	const backgroundClass = getColorClassName( 'background-color', backgroundColor );

	const buttonClasses = classnames( 'btn', {
		'has-text-color': textColor || customTextColor,
		[ textClass ]: textClass,
		'has-background': backgroundColor || customBackgroundColor,
		// [ backgroundClass ]: backgroundClass,
		//[ `btn-${backgroundColor && backgroundColor.slug}` ]: backgroundColor,
		[ `btn${outline ? '-outline' : ''}-${backgroundColor && backgroundColor.slug}` ]: backgroundColor,
	} );

	const buttonStyle = {
		backgroundColor: backgroundClass ? undefined : customBackgroundColor,
		color: textClass ? undefined : customTextColor,
	};

	return (
		<div>
			<RichText.Content
				tagName="a"
				className={ buttonClasses }
				href={ url }
				title={ title }
				style={ buttonStyle }
				value={ text }
			/>
		</div>
	);
}
