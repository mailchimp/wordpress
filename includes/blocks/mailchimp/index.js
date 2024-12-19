import { registerBlockType } from '@wordpress/blocks';

import { BlockEdit } from './edit';
import metadata from './block.json';
import Icon from './icon';

registerBlockType(metadata, {
	icon: Icon,
	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'mailchimpsf_form',
				attributes: {
					// No attributes, but attributes property is required
				},
			},
		],
	},
	edit: BlockEdit,
	save: () => null,
});
