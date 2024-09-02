/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import {
	ToolbarGroup,
	ToolbarButton,
	PanelBody,
	ToggleControl,
	Icon,
} from '@wordpress/components';
import { createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InnerBlocks,
	BlockControls,
	InspectorControls,
	RichText,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { useHasSelectedInnerBlock } from '../../utilis';

const ALLOWED_RICH_TEXT_FORMATS = [ 'core/bold', 'core/italic', 'core/superscript', 'core/subscript' ];

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @param {Object} props Block properties
 * @return {WPElement} Element to render.
 */

export default function Edit( props ) {
	const { clientId, attributes, setAttributes, isSelected } = props;
	const { title, intro, openFirstItem, hasBorder } = attributes;
	const { insertBlock, updateBlockAttributes } = useDispatch(
		'core/block-editor'
	);

	const hasChildBlockSelected = useHasSelectedInnerBlock();
	const hasSelection = isSelected || hasChildBlockSelected;

	const propsObject = {};
	propsObject.className = 'accordion-wrapper';
	const blockProps = useBlockProps( propsObject );

	const hasBorderClass = hasBorder ? 'accordion-list--has-border' : '';

	// Selects all nested Accordion-item blocks.
	const { blockIDs } = useSelect( ( select ) => {
		const blocksList = select( 'core/block-editor' ).getBlocks( clientId );
		return {
			blockIDs: blocksList.map( ( b ) => b.clientId ),
		};
	} );

	const addItemHandler = () => {
		// Collapses all Accordion-items when a new item is added.
		blockIDs.forEach( ( innerBlockId ) => {
			const isActive = false;
			updateBlockAttributes( innerBlockId, {
				isActive,
			} );
		} );
		// Creates paragraph block to be added.
		const paragraphBlock = createBlock( 'core/paragraph', {
			placeholder: __( 'Enter accordion content here', 'basic-composer' ),
		} );
		// Inserts Accordion-item with nested paragraph block.
		insertBlock(
			createBlock( 'surrealwebs-composer-blocks/accordion-item', { id: blockIDs.length }, [ paragraphBlock ] ),
			blockIDs.length + 1,
			clientId
		);
	};

	return (
		<div { ...blockProps }>
			<div className={ `${ hasBorderClass } accordion-list` }>
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon="plus"
							label={ __( 'Add a new item', 'basic-composer' ) }
							onClick={ addItemHandler }
						/>
					</ToolbarGroup>
				</BlockControls>
				<>
					<div className="accordion-list__header">
						<RichText
							value={ title }
							tagName="h2"
							placeholder={ __( 'Accordion Title…', 'basic-composer' ) }
							onChange={ ( value ) => setAttributes( { title: value } ) }
							className="accordion-list__title"
							allowedFormats={ [] }
						></RichText>
						<RichText
							value={ intro }
							tagName="div"
							placeholder={ __( 'Intro text here…', 'basic-composer' ) }
							onChange={ ( value ) => setAttributes( { intro: value } ) }
							className="accordion-list__intro"
							multiline="p"
							allowedFormats={ ALLOWED_RICH_TEXT_FORMATS }
						></RichText>
					</div>
					<InnerBlocks
						allowedBlocks={ [ 'surrealwebs-composer-blocks/accordion-item' ] }
						template={ [
							[
								'surrealwebs-composer-blocks/accordion-item',
								{},
								[
									[
										'core/paragraph',
										{
											placeholder: __( 'Enter accordion content here', 'basic-composer' ),
										},
									],
								],
							],
						] }
						renderAppender={ false }
					/>
					{ hasSelection &&
						<button
							type="button"
							onClick={ addItemHandler }
							className="accordion__button"
						>
							<Icon
								icon="plus"
								label={ __( 'Add a new item', 'basic-composer' ) }
								onClick={ addItemHandler }
							/>
							{ __( 'Add a new item', 'basic-composer' ) }
						</button>
					}
				</>
				<InspectorControls>
					<PanelBody
						title={ __( 'Accordion settings', 'basic-composer' ) }
					>
						<ToggleControl
							label={ __( 'Open the first item by default', 'basic-composer' ) }
							help={ __( 'Show the content of the first item on the first load', 'basic-composer' ) }
							checked={ openFirstItem }
							onChange={ () => {
								setAttributes( {
									openFirstItem: ! openFirstItem,
								} );
							} }
						/>
						<ToggleControl
							label={ __( 'Apply Border?', 'basic-composer' ) }
							help={ __( 'Adds border to Accordion lists.(8px)', 'basic-composer' ) }
							checked={ hasBorder }
							onChange={ () => {
								setAttributes( {
									hasBorder: ! hasBorder,
								} );
							} }
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		</div>
	);
}
