/**
 * External dependencies
 */
import classnames from 'classnames';
import { dropRight } from 'lodash';
import { createRef } from 'react';

import './editor.scss';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	RangeControl,
} = wp.components;

const {
	InspectorControls,
	InnerBlocks,
	BlockControls,
	BlockVerticalAlignmentToolbar,
} = wp.editor;

const { withDispatch } = wp.data;
const { createBlock } = wp.blocks;

/**
 * Internal dependencies
 */
import {
	getColumnsTemplate,
	hasExplicitColumnWidths,
	getMappedColumnWidths,
	getRedistributedColumnWidths,
	toWidthPrecision,
} from './utils';

/**
 * Allowed blocks constant is passed to InnerBlocks precisely as specified here.
 * The contents of the array should never change.
 * The array should contain the name of each block that is allowed.
 * In columns block, the only block we allow is 'bootstrap-hooks/card'.
 *
 * @constant
 * @type {string[]}
*/
const ALLOWED_BLOCKS = [ 'bootstrap-hooks/card' ];

class ColumnsEdit extends Component {

	static defaultProps = {
		blockListSelector: '.editor-block-list__layout',
		containerClass: 'card-deck'
	}

	constructor() {
		super(...arguments);

		this.ref = createRef();
	}

	componentDidMount() {
		requestAnimationFrame(() => this.update());
	}

	componentDidUpdate() {
		this.update();
	}

	update() {
		const { blockListSelector, containerClass } = this.props;
		const { current: element } = this.ref;

		const blockListElement = element.querySelector(blockListSelector);

		if (blockListElement) {
			blockListElement.classList.add(containerClass);
		}
	}

	render() {
		const {
			clientId,
			attributes,
			className,
			updateAlignment,
			updateColumns,
		} = this.props;

		const { columns, verticalAlignment } = attributes;

		const classes = classnames( className, `has-${ columns }-columns`, {
			[ `are-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
		} );


		return (
			<Fragment>
				<InspectorControls>
					<PanelBody>
						<RangeControl
							label={ __( 'Columns' ) }
							value={ columns }
							onChange={ updateColumns }
							min={ 2 }
							max={ 6 }
						/>
					</PanelBody>
				</InspectorControls>
				{/*
				<BlockControls>
					<BlockVerticalAlignmentToolbar
						onChange={ updateAlignment }
						value={ verticalAlignment }
					/>
				</BlockControls>
				*/}
				<div className={ classes } ref={this.ref}>
					<InnerBlocks
						template={ getColumnsTemplate( columns, clientId ) }
						templateLock="all"
						allowedBlocks={ ALLOWED_BLOCKS } />
				</div>
			</Fragment>
		);
	}
}

export default withDispatch( ( dispatch, ownProps, registry ) => ( {
	/**
	 * Update all child Column blocks with a new vertical alignment setting
	 * based on whatever alignment is passed in. This allows change to parent
	 * to overide anything set on a individual column basis.
	 *
	 * @param {string} verticalAlignment the vertical alignment setting
	 */
	updateAlignment( verticalAlignment ) {
		const { clientId, setAttributes } = ownProps;
		const { updateBlockAttributes } = dispatch( 'core/block-editor' );
		const { getBlockOrder } = registry.select( 'core/block-editor' );

		// Update own alignment.
		setAttributes( { verticalAlignment } );

		// Update all child Column Blocks to match
		const innerBlockClientIds = getBlockOrder( clientId );
		innerBlockClientIds.forEach( ( innerBlockClientId ) => {
			updateBlockAttributes( innerBlockClientId, {
				verticalAlignment,
			} );
		} );
	},

	/**
	 * Updates the column count, including necessary revisions to child Column
	 * blocks to grant required or redistribute available space.
	 *
	 * @param {number} columns New column count.
	 */
	updateColumns( columns ) {
		const { clientId, setAttributes, attributes } = ownProps;
		const { replaceInnerBlocks } = dispatch( 'core/block-editor' );
		const { getBlocks } = registry.select( 'core/block-editor' );

		// Update columns count.
		setAttributes( { columns } );

		let innerBlocks = getBlocks( clientId );
		if ( ! hasExplicitColumnWidths( innerBlocks ) ) {
			return;
		}

		// Redistribute available width for existing inner blocks.
		const { columns: previousColumns } = attributes;
		const isAddingColumn = columns > previousColumns;

		if ( isAddingColumn ) {
			// If adding a new column, assign width to the new column equal to
			// as if it were `1 / columns` of the total available space.
			const newColumnWidth = toWidthPrecision( 100 / columns );

			// Redistribute in consideration of pending block insertion as
			// constraining the available working width.
			const widths = getRedistributedColumnWidths( innerBlocks, 100 - newColumnWidth );

			innerBlocks = [
				...getMappedColumnWidths( innerBlocks, widths ),
				createBlock( 'bootstrap-hooks/card', {
					width: newColumnWidth,
					parent: clientId
				} ),
			];
		} else {
			// The removed column will be the last of the inner blocks.
			innerBlocks = dropRight( innerBlocks );

			// Redistribute as if block is already removed.
			const widths = getRedistributedColumnWidths( innerBlocks, 100 );

			innerBlocks = getMappedColumnWidths( innerBlocks, widths );
		}

		replaceInnerBlocks( clientId, innerBlocks, false );
	},
} ) )( ColumnsEdit );
