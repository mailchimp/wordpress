import {
	__experimentalBlockVariationPicker as BlockVariationPicker, // eslint-disable-line @wordpress/no-unsafe-wp-apis
	store as blockEditorStore,
	useBlockProps,
} from '@wordpress/block-editor';
import { createBlocksFromInnerBlocksTemplate, store as blocksStore } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';
import { formFields, formFieldTitles } from './variations';

const getMissingFields = (variation) => {
	const variationName = variation.name;
	const variationFields = formFields[variationName] || [];
	const formFieldsTags = variation.innerBlocks
		.filter((block) => block[0] === 'mailchimp/mailchimp-form-field')
		.map((block) => {
			const [, attributes] = block;
			const { tag } = attributes;
			return tag;
		});

	return variationFields.filter((field) => !formFieldsTags.includes(field));
};

export const VariationPicker = ({ name, setAttributes, clientId }) => {
	const { blockType, defaultVariation, variations } = useSelect(
		(select) => {
			const { getBlockVariations, getBlockType, getDefaultBlockVariation } =
				select(blocksStore);

			return {
				blockType: getBlockType(name),
				defaultVariation: getDefaultBlockVariation(name, 'block'),
				variations: getBlockVariations(name, 'block'),
			};
		},
		[name],
	);
	const { replaceInnerBlocks } = useDispatch(blockEditorStore);
	const { createNotice } = useDispatch('core/notices');
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<BlockVariationPicker
				icon={blockType?.icon?.src}
				label={blockType?.title}
				instructions={__('Start by selecting one of these templates', 'mailchimp')}
				variations={variations}
				onSelect={(nextVariation = defaultVariation) => {
					if (nextVariation.attributes) {
						setAttributes(nextVariation.attributes);
					}

					if (nextVariation.innerBlocks) {
						const missingFields = getMissingFields(nextVariation);

						replaceInnerBlocks(
							clientId,
							createBlocksFromInnerBlocksTemplate(nextVariation.innerBlocks),
						);

						// Add a notice if there are missing fields from the selected form template.
						if (missingFields.length > 0) {
							createNotice(
								'warning',
								sprintf(
									_n(
										"%s form field is missing from the selected form template. Please create this field in the Mailchimp dashboard, then click the 'Fetch list settings' button on the plugin settings page to update the list and include the missing field.",
										"Some form fields are missing from the selected form template: %s. Please create these fields in the Mailchimp dashboard, then click the 'Fetch list settings' button on the plugin settings page to update the list and include the missing fields.",
										missingFields.length,
										'mailchimp',
									),
									missingFields
										.map(
											(field) =>
												`${formFieldTitles[field] || field} (${field})`,
										)
										.join(', '),
								),
								{
									id: 'mailchimp-form-template-field-notice',
									isDismissible: true,
								},
							);
						}
					}
				}}
				allowSkip
			/>
		</div>
	);
};
