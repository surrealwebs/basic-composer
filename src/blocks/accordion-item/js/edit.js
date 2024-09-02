/**
 * WordPress dependencies
 */

import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	useBlockProps,
	InnerBlocks,
	RichText,
} from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @param {Object} props Block properties
 * @return {WPElement} Element to render.
 */

export default function Edit( props ) {
	const { attributes, setAttributes, clientId } = props;
	const { title, subTitle, isActive } = attributes;

	const { updateBlockAttributes } = useDispatch( 'core/block-editor' );

	// Selects nested Accordion-item blocks and index of each Accordion-item block.
	const { blockIDs, itemIndex } = useSelect( ( select ) => {
		// Gets ID of Accordion block
		const parentOfSelectedBlock = select(
			'core/block-editor'
		).getBlockRootClientId( clientId );

		// Gets all nested Accordion item blocks.
		const blocksList = select( 'core/block-editor' ).getBlocks(
			parentOfSelectedBlock
		);
		return {
			blockIDs: blocksList.map( ( b ) => b.clientId ),
			itemIndex: wp.data
				.select( 'core/block-editor' )
				.getBlockIndex( clientId, parentOfSelectedBlock ),
		};
	} );

	// Assigns id used for ordering items should blocks be reordered using editor.
	// This is used to identify the first item to be opened on page load.
	useEffect( () => {
		setAttributes( { id: itemIndex } );
	}, [ itemIndex ] );

	const propsObject = {};
	if ( isActive ) {
		propsObject.className = 'accordion-item accordion-item--active';
	} else {
		propsObject.className = 'accordion-item';
	}

	// Sets item as active once it is clicked and expands the content.
	const onClickHandler = () => {
		blockIDs.forEach( ( innerBlockId ) => {
			const isActiveBlock = false;
			updateBlockAttributes( innerBlockId, {
				isActive: isActiveBlock,
			} );
		} );
		setAttributes( { isActive: true } );
	};

	const blockProps = useBlockProps( propsObject );

	return (
		<div { ...blockProps }>
			<div className="accordion-item__header">
				<svg
					width="16"
					height="16"
					viewBox="0 0 16 16"
					xmlns="http://www.w3.org/2000/svg"
					className="accordion-item__arrow"
				>
					<path
						d="M13.8983 5.64407L13.3831 5.10169C13.2475 4.9661 13.0305 4.9661 12.922 5.10169L8.01356 10.0102L3.07797 5.10169C2.96949 4.9661 2.75254 4.9661 2.61695 5.10169L2.10169 5.64407C1.9661 5.75254 1.9661 5.96949 2.10169 6.10508L7.76949 11.7729C7.90508 11.9085 8.09492 11.9085 8.23051 11.7729L13.8983 6.10508C14.0339 5.96949 14.0339 5.75254 13.8983 5.64407Z"
					></path>
				</svg>
				<div
					role="tab"
					tabIndex="-1"
					className="accordion-item__title"
					onClick={ onClickHandler }
					onKeyDown={ onClickHandler }
				>
					<RichText
						value={ title }
						placeholder={ __( 'Enter your title', 'basic-composer' ) }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
				</div>
				<div
					role="tab"
					tabIndex="-1"
					className="accordion-item__sub-title"
					onClick={ onClickHandler }
					onKeyDown={ onClickHandler }
				>
					<RichText
						value={ subTitle }
						placeholder={ __( 'Enter your sub title', 'basic-composer' ) }
						onChange={ ( value ) =>
							setAttributes( { subTitle: value } )
						}
					/>
				</div>
			</div>
			<div
				className="accordion-item__content"
				style={ { display: isActive ? 'block' : 'none' } }
			>
				<InnerBlocks />
			</div>
		</div>
	);
}
