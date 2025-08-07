import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

import { BlockEdit } from './edit';
import metadata from './block.json';
import Icon from './icon';
import { variations } from './variations';

registerBlockType(metadata, {
	icon: Icon,
	variations,
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
	save: () => <InnerBlocks.Content />,
});
