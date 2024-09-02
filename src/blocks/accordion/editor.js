import { registerBlockType } from '@wordpress/blocks';

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
