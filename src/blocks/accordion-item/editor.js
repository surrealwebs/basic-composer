import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import './editor.scss';

import metadata from './block.json';
import Edit from './js/edit';
import Save from './js/save';

registerBlockType(
	metadata.name,
	{
		edit: Edit,
		save: Save,
	}
);
