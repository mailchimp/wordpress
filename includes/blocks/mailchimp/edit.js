import {
	RichText,
	InspectorControls,
	useBlockProps,
	store as blockEditorStore,
	InnerBlocks,
} from '@wordpress/block-editor';
import { __, sprintf } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	Spinner,
	Placeholder,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch, useSelect } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import Icon from './icon';

const SelectListPlaceholder = () => {
	return (
		<Placeholder
			icon={Icon}
			label={__('Mailchimp Block', 'mailchimp')}
			instructions={__(
				'Please select the Mailchimp list in the block settings sidebar.',
				'mailchimp',
			)}
		/>
	);
};

export const BlockEdit = (props) => {
	const { clientId, attributes, setAttributes } = props;
	const { mailchimp_sf_block_data } = window;
	const {
		lists,
		merge_fields_visibility,
		interest_groups_visibility,
		list_id: listId,
		header_text,
		sub_header_text,
		submit_text: submitText,
		show_unsubscribe_link: showUnsubscribeLink,
		update_existing_subscribers: updateExistingSubscribers,
		double_opt_in: doubleOptIn,
	} = mailchimp_sf_block_data;

	const {
		header = header_text,
		sub_header = sub_header_text,
		list_id = listId,
		submit_text = submitText,
		double_opt_in = doubleOptIn,
		update_existing_subscribers = updateExistingSubscribers,
		show_unsubscribe_link = showUnsubscribeLink,
		unsubscribe_link_text,
		show_required_indicator = true,
		required_indicator_text,
	} = attributes;

	const [listData, setListData] = useState({});
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState('');
	const blockProps = useBlockProps();
	const { replaceInnerBlocks } = useDispatch(blockEditorStore);

	// Select current innerBlocks
	const innerBlocks = useSelect(
		(select) => select(blockEditorStore).getBlocksByClientId(clientId)?.[0]?.innerBlocks || [],
		[clientId],
	);
	const exisingTags = innerBlocks.map((block) => block?.attributes?.tag).filter(Boolean);
	const exisingGroups = innerBlocks.map((block) => block?.attributes?.id).filter(Boolean);
	const visibleFieldsCount = innerBlocks.filter((block) => block?.attributes?.visible).length;

	const listOptions = [];
	// Check if selected list is not in the list of available lists.
	const listIds = lists?.map((list) => list.id) || [];
	if (!listIds.includes(list_id) && listIds.length > 0) {
		listOptions.push({
			label: __('Select a list', 'mailchimp'),
			value: '',
		});
		setAttributes({ list_id: '' });
	}

	listOptions.push(
		...(lists?.map((list) => ({
			label: list.name,
			value: list.id,
		})) || []),
	);

	// Fetch list data and update innerBlocks if needed.
	const updateList = (listId, replaceBlocks = false) => {
		setError('');
		setIsLoading(true);

		// Fetch data from API.
		apiFetch({ path: `/mailchimp/v1/list-data/${listId}` })
			.then((data) => {
				if (!data) {
					setError(__('Error fetching list data.', 'mailchimp'));
					setIsLoading(false);
					return;
				}

				if (replaceBlocks) {
					// Replace all innerBlocks with new ones on list change.
					const listFieldsBlocks =
						data?.merge_fields?.map((field) =>
							createBlock('mailchimp/mailchimp-form-field', {
								tag: field.tag,
								label: field.name,
								type: field.type,
								visible:
									(field.required ||
										merge_fields_visibility?.[field.tag] === 'on') &&
									field.public,
							}),
						) || [];
					const listGroupsBlocks =
						data?.interest_groups?.map((group) =>
							createBlock('mailchimp/mailchimp-audience-group', {
								id: group.id,
								label: group.title,
								visible:
									interest_groups_visibility?.[group.id] === 'on' &&
									group.type !== 'hidden',
							}),
						) || [];
					replaceInnerBlocks(clientId, [...listFieldsBlocks, ...listGroupsBlocks], false);
				} else if (exisingTags && exisingTags.length > 0) {
					// Update existing innerBlocks with if new fields are added to the list or removed from the list.
					const newFormFields =
						data?.merge_fields?.filter((field) => !exisingTags.includes(field.tag)) ||
						[];
					const newFormGroups =
						data?.interest_groups?.filter(
							(group) => !exisingGroups.includes(group.id),
						) || [];
					const updatedInnerBlocks = innerBlocks.filter((block) => {
						const { tag, id } = block.attributes;
						if (tag) {
							return data?.merge_fields?.find((field) => field.tag === tag);
						}
						return data?.interest_groups?.find((group) => group.id === id);
					});

					if (
						newFormFields.length > 0 ||
						newFormGroups.length > 0 ||
						updatedInnerBlocks.length !== innerBlocks.length
					) {
						// Create new blocks for newly added fields and groups.
						const newBlocks = newFormFields.map((field) =>
							createBlock('mailchimp/mailchimp-form-field', {
								tag: field.tag,
								label: field.name,
								type: field.type,
								visible:
									(field.required ||
										merge_fields_visibility?.[field.tag] === 'on') &&
									field.public,
							}),
						);
						const newGroupBlocks = newFormGroups.map((group) =>
							createBlock('mailchimp/mailchimp-audience-group', {
								id: group.id,
								label: group.title,
								visible:
									interest_groups_visibility?.[group.id] === 'on' &&
									group.type !== 'hidden',
							}),
						);

						// Replace innerBlocks with updated ones.
						replaceInnerBlocks(
							clientId,
							[...updatedInnerBlocks, ...newBlocks, ...newGroupBlocks],
							false,
						);
					}
				}

				setListData(data);

				// Set list data in global variable to be used in form field block.
				if (!window.mailchimpListData) {
					window.mailchimpListData = {};
				}
				const mergeFields =
					data?.merge_fields?.reduce((acc, field) => {
						acc[field.tag] = field;
						return acc;
					}, {}) || {};
				const interestGroups =
					data?.interest_groups?.reduce((acc, group) => {
						acc[group.id] = group;
						return acc;
					}, {}) || {};
				window.mailchimpListData[listId] = { mergeFields, interestGroups };
				setIsLoading(false);
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Error fetching list data:', error);
				setError(error.message);
				setIsLoading(false);
			});
	};

	// Update the show_required_indicator attribute based on the number of visible fields.
	useEffect(() => {
		if (visibleFieldsCount > 1) {
			setAttributes({ show_required_indicator: true });
		} else {
			setAttributes({ show_required_indicator: false });
		}
	}, [setAttributes, visibleFieldsCount]);

	// Update the innerBlocks on initial render if needed.
	useEffect(() => {
		const listIds = lists?.map((list) => list.id) || [];
		if (!list_id || !listIds.includes(list_id)) {
			setListData({});
			setIsLoading(false);
			return;
		}
		setError('');
		setIsLoading(true);

		updateList(list_id, false);

		// Set the attributes from global settings initially, if it's already not set.
		if (attributes.list_id === undefined) {
			const attributeUpdates = { list_id: listId };
			if (attributes.header === undefined) {
				attributeUpdates.header = header_text;
			}
			if (attributes.sub_header === undefined) {
				attributeUpdates.sub_header = sub_header_text;
			}
			if (attributes.submit_text === undefined) {
				attributeUpdates.submit_text = submitText;
			}
			if (attributes.double_opt_in === undefined) {
				attributeUpdates.double_opt_in = doubleOptIn;
			}
			if (attributes.update_existing_subscribers === undefined) {
				attributeUpdates.update_existing_subscribers = updateExistingSubscribers;
			}
			if (attributes.show_unsubscribe_link === undefined) {
				attributeUpdates.show_unsubscribe_link = showUnsubscribeLink;
			}

			setAttributes(attributeUpdates);
		}
	}, []); // eslint-disable-line react-hooks/exhaustive-deps -- Only run on initial render.

	if (isLoading) {
		return (
			<div style={{ position: 'relative' }}>
				<div
					style={{
						position: 'absolute',
						top: '50%',
						left: '50%',
						marginTop: '-9px',
						marginLeft: '-9px',
					}}
				>
					<Spinner />
				</div>
			</div>
		);
	}

	// Create a template for innerBlocks based on list data and visibility settings.
	const templateFields =
		listData?.merge_fields?.map((field) => [
			'mailchimp/mailchimp-form-field',
			{
				tag: field.tag,
				label: field.name,
				type: field.type,
				visible:
					(field.required || merge_fields_visibility?.[field.tag] === 'on') &&
					field.public,
			},
		]) || [];
	const templateGroups =
		listData?.interest_groups?.map((group) => [
			'mailchimp/mailchimp-audience-group',
			{
				id: group.id,
				label: group.title,
				visible: interest_groups_visibility?.[group.id] === 'on' && group.type !== 'hidden',
			},
		]) || [];
	const template = [...templateFields, ...templateGroups];

	return (
		<>
			<div {...blockProps}>
				{!list_id && <SelectListPlaceholder />}
				{list_id && (
					<>
						<RichText
							className="mailchimp-block__header mc_custom_border_hdr"
							tagName="h2"
							placeholder={__('Enter a header (optional)', 'mailchimp')}
							value={header}
							onChange={(header) => setAttributes({ header })}
						/>
						<div id="mc_signup">
							<div id="mc_signup_form">
								<div id="mc_subheader">
									<RichText
										className="mailchimp-block__sub-header"
										tagName="h3"
										placeholder={__(
											'Enter a sub header (optional)',
											'mailchimp',
										)}
										value={sub_header}
										onChange={(sub_header) => setAttributes({ sub_header })}
									/>
								</div>
								{error && (
									<Placeholder>
										{sprintf(
											// translators: %s: error message describing the problem
											__('Error fetching list data: %s'),
											error,
										)}
									</Placeholder>
								)}
								<div className="mc_form_inside">
									<InnerBlocks
										allowedBlocks={['mailchimp/mailchimp-form-field']}
										orientation="vertical"
										template={template}
										templateLock="insert"
									/>
									{show_required_indicator && (
										<div id="mc-indicates-required">
											<RichText
												tagName="span"
												value={required_indicator_text}
												placeholder={__('* = required field', 'mailchimp')}
												onChange={(required_indicator_text) =>
													setAttributes({ required_indicator_text })
												}
											/>
										</div>
									)}
									<div className="mc_signup_submit">
										<RichText
											id="mc_signup_submit"
											className="button"
											tagName="button"
											placeholder={__('Enter button text.', 'mailchimp')}
											value={submit_text}
											onChange={(submit_text) =>
												setAttributes({ submit_text })
											}
										/>
									</div>
									{!!show_unsubscribe_link && (
										<div id="mc_unsub_link">
											<RichText
												tagName="a"
												value={unsubscribe_link_text}
												onChange={(unsubscribe_link_text) =>
													setAttributes({ unsubscribe_link_text })
												}
											/>
										</div>
									)}
								</div>
							</div>
						</div>
					</>
				)}
			</div>
			<InspectorControls>
				<PanelBody title={__('Settings', 'mailchimp')} initialOpen>
					<SelectControl
						label={__('Select a list', 'mailchimp')}
						value={list_id}
						options={listOptions}
						className="mailchimp-list-select"
						onChange={(list_id) => {
							setIsLoading(true);
							setAttributes({ list_id });
							updateList(list_id, true);
						}}
						help={__(
							"Please select the Mailchimp list you'd like to connect to your form.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={__('Form Settings', 'mailchimp')} initialOpen={false}>
					<ToggleControl
						label={__('Double opt-in', 'mailchimp')}
						checked={double_opt_in}
						className="mailchimp-double-opt-in"
						onChange={() => setAttributes({ double_opt_in: !double_opt_in })}
						help={__(
							"Before new subscribers are added to your list, they'll need to confirm their email address.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={__('Update existing subscribers', 'mailchimp')}
						checked={update_existing_subscribers}
						className="mailchimp-update-existing-subscribers"
						onChange={() =>
							setAttributes({
								update_existing_subscribers: !update_existing_subscribers,
							})
						}
						help={__(
							"If an existing subscriber submits the form, their information will be updated with what's provided.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={__('Include unsubscribe link', 'mailchimp')}
						checked={show_unsubscribe_link}
						className="mailchimp-unsubscribe-link"
						onChange={() =>
							setAttributes({ show_unsubscribe_link: !show_unsubscribe_link })
						}
						help={__(
							"Automatically add a link to your list's unsubscribe form.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};
