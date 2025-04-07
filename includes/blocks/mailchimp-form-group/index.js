import { registerBlockType } from '@wordpress/blocks';

import { BlockEdit } from './edit';
import metadata from './block.json';

registerBlockType(metadata, {
	icon: 'groups',
	edit: BlockEdit,
	save: () => null,
});
