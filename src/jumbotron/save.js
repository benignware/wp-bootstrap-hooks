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

class JumbotronSave extends Component {
	render() {
		const { attributes, image, ...props } = this.props;
		const {
			backgroundColor,
			textColor,
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
			mediaSizes
		} = attributes;

		let src = mediaUrl;

		if (mediaType === 'image')  {
			src =  mediaSize && mediaSizes[mediaSize] ? mediaSizes[mediaSize].url : mediaUrl;
		}

		const mediaTypeRenders = {
			image: () => (
				<img
					src={ src }
					alt={ mediaAlt }
					className={ ( mediaId && mediaType === 'image' ) ? `wp-image-${ mediaId } img-fluid w-100` : null }
					style={{ objectFit: 'cover' }}
				/>
			),
			video: () => <video controls src={ mediaUrl } />,
		};
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );

		const classes = classnames(
			// className,
			'jumbotron',
			'p-0',
			{
				[ `bg-${backgroundColor}` ]: backgroundColor,
				[ textColor && `text-${textColor}` ]: textColor,
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
		//  style={ style }
		return (
			<div className={ classes }>
				<div className="d-flex">
					<div className="w-100 flex-shrink-0 d-flex jumbotron m-0 p-0" style={{
						background: 'none',
						overflow: 'hidden'
					}}>
						{ mediaType && ( mediaTypeRenders[ mediaType ] || noop )() }
					</div>
					<div className="w-100 flex-shrink-0 d-flex jumbotron m-0 rounded-0" style={{
						background: 'none',
						borderRadius: 'none',
						transform: 'translateX(-100%)'
					}}>
						<div className="container mt-auto">
							<InnerBlocks.Content />
						</div>
					</div>
				</div>
			</div>
		);
	}
}

export default JumbotronSave;
