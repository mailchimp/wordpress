import { RichText, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	Disabled,
	CheckboxControl,
	SelectControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const BlockEdit = (props) => {
	const { attributes, setAttributes } = props;
	const {
		header,
		sub_header,
		list_id,
		submit_text,
		show_default_fields,
		double_opt_in,
		update_existing_subscribers,
		show_unsubscribe_link,
		unsubscribe_link_text,
		merge_fields_visibility,
		interest_groups_visibility,
	} = attributes;
	const blockProps = useBlockProps();

	const { mailchimp_sf_block_data } = window;
	const listOptions =
		mailchimp_sf_block_data?.lists.map((list) => ({
			label: list.name,
			value: list.id,
		})) || [];

	const [listData, setListData] = useState({});

	useEffect(() => {
		// Fetch data from your API
		apiFetch({ path: `/mailchimp/v1/list-data/${list_id}` })
			.then((data) => {
				setListData(data);
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Error fetching list data:', error);
			});
	}, [list_id]);

	return (
		<>
			<div {...blockProps}>
				<RichText
					className="wp-block-example-block__header"
					tagName="h2"
					placeholder={__('Please enter a header text.', 'mailchimp')}
					value={header}
					onChange={(header) => setAttributes({ header })}
				/>
				<RichText
					className="wp-block-example-block__sub-header"
					tagName="h3"
					placeholder={__('Please enter a sub header text.', 'mailchimp')}
					value={sub_header}
					onChange={(sub_header) => setAttributes({ sub_header })}
				/>
				<Disabled>
					<ServerSideRender
						attributes={{
							list_id,
							show_default_fields,
							merge_fields_visibility,
							interest_groups_visibility,
							is_preview: true,
						}}
						block="mailchimp/mailchimp"
					/>
				</Disabled>
				<div className="mc_signup_submit">
					<RichText
						id="mc_signup_submit"
						className="button"
						tagName="button"
						placeholder={__('Enter button text.', 'mailchimp')}
						value={submit_text}
						onChange={(submit_text) => setAttributes({ submit_text })}
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

			<InspectorControls>
				<PanelBody title={__('Settings', 'mailchimp')} initialOpen>
					<SelectControl
						label={__('Select a list', 'mailchimp')}
						value={list_id}
						options={listOptions}
						onChange={(list_id) => setAttributes({ list_id })}
						help={__(
							"Please select the Mailchimp list you'd like to connect to your form.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					<h3>{__('Form Fields', 'mailchimp')}</h3>
					<ToggleControl
						label={__('Show default fields', 'mailchimp')}
						checked={show_default_fields}
						onChange={(show_default_fields) => {
							setAttributes({ show_default_fields });
						}}
						help={__(
							'Show fields marked as visible in Mailchimp settings.',
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					{!show_default_fields &&
						listData?.merge_fields?.map((field) => (
							<div>
								<CheckboxControl
									key={field.tag}
									label={`${field.name} (${field.tag})`}
									checked={
										merge_fields_visibility?.[field.tag] === 'on' ||
										field.required
									}
									onChange={(checked) => {
										setAttributes({
											merge_fields_visibility: {
												...merge_fields_visibility,
												[field.tag]: checked ? 'on' : 'off',
											},
										});
									}}
									className="mailchimp-merge-field-checkbox"
									disabled={field.required}
									__nextHasNoMarginBottom
								/>
							</div>
						))}
					{listData?.interest_groups?.length > 0 && (
						<div style={{ marginTop: '20px' }}>
							<h3>{__('Groups Settings', 'mailchimp')}</h3>
							{listData?.interest_groups?.map((group) => (
								<CheckboxControl
									key={group.id}
									label={group.title}
									checked={interest_groups_visibility?.[group.id] === 'on'}
									onChange={(checked) => {
										setAttributes({
											interest_groups_visibility: {
												...interest_groups_visibility,
												[group.id]: checked ? 'on' : 'off',
											},
										});
									}}
									className="mailchimp-group-checkbox"
									__nextHasNoMarginBottom
								/>
							))}
						</div>
					)}
				</PanelBody>
				<PanelBody title={__('Advanced Settings', 'mailchimp')} initialOpen={false}>
					<ToggleControl
						label={__('Double Opt-In', 'mailchimp')}
						checked={double_opt_in}
						onChange={() => setAttributes({ double_opt_in: !double_opt_in })}
						help={__(
							"Before new your subscribers are added via the plugin, they'll need to confirm their email address.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={__('Update existing subscribers', 'mailchimp')}
						checked={update_existing_subscribers}
						onChange={() =>
							setAttributes({
								update_existing_subscribers: !update_existing_subscribers,
							})
						}
						help={__(
							"If an existing subscriber fills out this form, we will update their information with what's provided.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={__('Include Unsubscribe link', 'mailchimp')}
						checked={show_unsubscribe_link}
						onChange={() =>
							setAttributes({ show_unsubscribe_link: !show_unsubscribe_link })
						}
						help={__(
							"We'll automatically add a link to your list's unsubscribe form.",
							'mailchimp',
						)}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};
