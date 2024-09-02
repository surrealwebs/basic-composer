/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 * @param {Object} props Block properties
 * @return {WPElement} Element to render.
 */

export default function Save( props ) {
	const { attributes } = props;
	const { title, intro, hasBorder, anchor } = attributes;

	const hasBorderClass = hasBorder ? 'c-block-accordion-has-border' : '';

	const propsObject = {
		className: classnames( 'c-block-accordion-wrapper', hasBorderClass ),
	};

	const blockProps = useBlockProps.save( { id: anchor } );
	return (
		<div { ...blockProps }>
			<div { ...propsObject }>
				{ ( title || intro ) &&
					<div className="c-block-accordion__header">
						{ title &&
							<RichText.Content className="c-block-accordion__title" tagName="h2" value={ title } />
						}
						{ intro &&
							<RichText.Content className="c-block-accordion__intro" tagName="div" value={ intro } />
						}
					</div>
				}
				<div className="c-block-accordion-list">
					<InnerBlocks.Content />
				</div>
			</div>
		</div>
	);
}
