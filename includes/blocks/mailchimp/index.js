import { registerBlockType } from '@wordpress/blocks';

import { BlockEdit } from './edit';
import metadata from './block.json';
import Icon from './icon';

registerBlockType(metadata, {
	icon: Icon,
	edit: BlockEdit,
	save: () => null,
});
