/**
 * WordPress dependencies
 */
const { createBlock } = wp.blocks;

const transforms = {
	from: [
		{
			type: 'block',
			blocks: [ 'core/image' ],
			transform: ( { alt, url, id } ) => (
				createBlock( 'bootstrap-hooks/card', {
					mediaAlt: alt,
					mediaId: id,
					mediaUrl: url,
					mediaType: 'image',
				} )
			),
		},
		{
			type: 'block',
			blocks: [ 'core/video' ],
			transform: ( { src, id } ) => (
				createBlock( 'bootstrap-hooks/card', {
					mediaId: id,
					mediaUrl: src,
					mediaType: 'video',
				} )
			),
		},
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/image' ],
			isMatch: ( { mediaType, mediaUrl } ) => {
				return ! mediaUrl || mediaType === 'image';
			},
			transform: ( { mediaAlt, mediaId, mediaUrl } ) => {
				return createBlock( 'core/image', {
					alt: mediaAlt,
					id: mediaId,
					url: mediaUrl,
				} );
			},
		},
		{
			type: 'block',
			blocks: [ 'core/video' ],
			isMatch: ( { mediaType, mediaUrl } ) => {
				return ! mediaUrl || mediaType === 'video';
			},
			transform: ( { mediaId, mediaUrl } ) => {
				return createBlock( 'core/video', {
					id: mediaId,
					src: mediaUrl,
				} );
			},
		},
	],
};

export default transforms;
