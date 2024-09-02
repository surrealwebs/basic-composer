/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './js/edit';
import Save from './js/save';
import './editor.scss';

registerBlockType(
	metadata.name,
	{
		edit: Edit,
		save: Save,
	}
);
