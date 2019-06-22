/**
 * External dependencies
 */
import classnames from 'classnames';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
const {
	InnerBlocks,
	getColorClassName,
} = wp.editor;
const { Component } = wp.element;

/**
 * Internal dependencies
 */
import { imageFillStyles } from './media-container';

const { withSelect } = wp.data;

const DEFAULT_MEDIA_WIDTH = 50;

class ColumnSave extends Component {
	render() {
		const { attributes, image, ...props } = this.props;
		const {
			backgroundColor,
			textColor,
			overlayTextColor,
			customBackgroundColor,
			isStackedOnMobile,
			mediaAlt,
			mediaPosition,
			mediaType,
			mediaUrl,
			mediaWidth,
			mediaId,
			verticalAlignment,
			imageFill,
			focalPoint,
			mediaSize,
			mediaSizes,
			mediaOverlay
		} = attributes;

		let src = mediaUrl;

		if (mediaType === 'image' && mediaSize && mediaSizes[mediaSize]) {
			src = mediaSizes[mediaSize].url || mediaUrl;
		}

		const mediaTypeRenders = {
			image: () => (
				<img
					src={ src }
					alt={ mediaAlt }
					className={ ( mediaId && mediaType === 'image' ) ? `wp-image-${ mediaId } ${mediaOverlay ? 'card-img' : 'card-img-top'}` : null }
				/>
			),
			video: () => <video controls src={ mediaUrl } />,
		};
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );

		const classes = classnames(
			// className,
			'card',
			{
				[ `bg-${backgroundColor}` ]: backgroundColor,
				[ textColor && `text-${textColor}` ]: textColor
				// 'has-media-on-the-right': 'right' === mediaPosition,
				// 'is-selected': isSelected,
				// [ backgroundColor.class ]: backgroundColor.class,
				// 'is-stacked-on-mobile': isStackedOnMobile,
				// [ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
				// 'is-image-fill': imageFill,
			}
		);

		/*
		const className = classnames( {
			'has-media-on-the-right': 'right' === mediaPosition,
			[ backgroundClass ]: backgroundClass,
			'is-stacked-on-mobile': isStackedOnMobile,
			[ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
			'is-image-fill': imageFill,
		} );
		*/
		const backgroundStyles = imageFill ? imageFillStyles( mediaUrl, focalPoint ) : {};

		let gridTemplateColumns;
		if ( mediaWidth !== DEFAULT_MEDIA_WIDTH ) {
			gridTemplateColumns = 'right' === mediaPosition ? `auto ${ mediaWidth }%` : `${ mediaWidth }% auto`;
		}
		const style = {
			backgroundColor: backgroundClass ? undefined : customBackgroundColor,
			gridTemplateColumns,
		};

		const contentClasses = classnames(
			mediaOverlay ? 'card-img-overlay' : 'card-body',
			{
				[ mediaOverlay && overlayTextColor && `text-${overlayTextColor}` ]: mediaOverlay && overlayTextColor
			}
		);

		return (
			<div className={ classes }>
				{ mediaType && ( mediaTypeRenders[ mediaType ] || noop )() }
				<div className={contentClasses}>
					<InnerBlocks.Content />
				</div>
			</div>
		);
	}
}

export default ColumnSave;
