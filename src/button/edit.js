/**
 * External dependencies
 */
import classnames from 'classnames';
import { camelizeKeys } from 'humps';

const options = camelizeKeys(JSON.parse(BootstrapHooks.data));

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { compose } = wp.compose;
const {
	Dashicon,
	IconButton,
	withFallbackStyles,
	ToggleControl
} = wp.components;

const {
	URLInput,
	RichText,
	ContrastChecker,
	InspectorControls,
	withColors,
	PanelColorSettings,
} = wp.editor;

const { getComputedStyle } = window;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { textColor, backgroundColor } = ownProps;
	const backgroundColorValue = backgroundColor && backgroundColor.color;
	const textColorValue = textColor && textColor.color;
	//avoid the use of querySelector if textColor color is known and verify if node is available.
	const textNode = ! textColorValue && node ? node.querySelector( '[contenteditable="true"]' ) : null;
	return {
		fallbackBackgroundColor: backgroundColorValue || ! node ? undefined : getComputedStyle( node ).backgroundColor,
		fallbackTextColor: textColorValue || ! textNode ? undefined : getComputedStyle( textNode ).color,
	};
} );

class ButtonEdit extends Component {
	constructor() {
		super( ...arguments );
		this.nodeRef = null;
		this.bindRef = this.bindRef.bind( this );
	}

	bindRef( node ) {
		if ( ! node ) {
			return;
		}
		this.nodeRef = node;
	}

	render() {
		const {
			attributes,
			backgroundColor,
			textColor,
			setBackgroundColor,
			setTextColor,
			fallbackBackgroundColor,
			fallbackTextColor,
			setAttributes,
			isSelected,
			className,
		} = this.props;

		const {
			text,
			url,
			title,
			outline
		} = attributes;

		return (
			<Fragment>
				<div className={ className } title={ title } ref={ this.bindRef }>
					<RichText
						placeholder={ __( 'Add text…' ) }
						value={ text }
						onChange={ ( value ) => setAttributes( { text: value } ) }
						formattingControls={ [ 'bold', 'italic', 'strikethrough' ] }
						className={ classnames(
							'btn', {
								'has-background': backgroundColor.color,
								// [ backgroundColor.class ]: backgroundColor.class,
								[ `btn${outline ? '-outline' : ''}-${backgroundColor && backgroundColor.slug}` ]: backgroundColor,
								// 'has-text-color': textColor.color,
								// [ textColor.class ]: textColor.class,
							}
						) }
						style={ {
							// backgroundColor: backgroundColor.color,
							// color: textColor.color,
						} }
						keepPlaceholderOnFocus
					/>
					<InspectorControls>
						<PanelColorSettings
							title={ __( 'Color Settings' ) }
							colorSettings={ [
								{
									value: backgroundColor.color,
									onChange: setBackgroundColor,
									label: __( 'Background Color' ),
								},
								/*{
									value: textColor.color,
									onChange: setTextColor,
									label: __( 'Text Color' ),
								},*/
							] }
						>
							{/*
							<ContrastChecker
								{ ...{
									// Text is considered large if font size is greater or equal to 18pt or 24px,
									// currently that's not the case for button.
									isLargeText: false,
									textColor: textColor.color,
									backgroundColor: backgroundColor.color,
									fallbackBackgroundColor,
									fallbackTextColor,
								} }
							/>
							*/}
						</PanelColorSettings>
						<ToggleControl
							label={ __( 'Outline' ) }
							checked={ !! attributes.outline }
							onChange={ (value) => {
								this.props.setAttributes({
									...attributes,
									outline: value
								});
							} }
							help={ () => attributes.outline ? __('Outline style is applied') : __(`Outline style isn't applied`) }
						/>
					</InspectorControls>
				</div>
				{ isSelected && (
					<form
						className="block-library-button__inline-link"
						onSubmit={ ( event ) => event.preventDefault() }>
						<Dashicon icon="admin-links" />
						<URLInput
							value={ url }
							/* eslint-disable jsx-a11y/no-autofocus */
							// Disable Reason: The rule is meant to prevent enabling auto-focus, not disabling it.
							autoFocus={ false }
							/* eslint-enable jsx-a11y/no-autofocus */
							onChange={ ( value ) => setAttributes( { url: value } ) }
						/>
						<IconButton icon="editor-break" label={ __( 'Apply' ) } type="submit" />
					</form>
				) }
			</Fragment>
		);
	}
}

export default compose( [
	withColors( 'backgroundColor', { textColor: 'color' } ),
	applyFallbackStyles,
] )( ButtonEdit );
