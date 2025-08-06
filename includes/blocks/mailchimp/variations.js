/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const { mailchimp_sf_block_data } = window;
const {
	merge_fields = [],
	interest_groups = [],
	merge_fields_visibility = {},
	interest_groups_visibility = {},
} = mailchimp_sf_block_data;

/** @typedef {import('@wordpress/blocks').WPBlockVariation} WPBlockVariation */

export const formFields = {
	'email-only-form': ['EMAIL'],
	'name-and-email-form': ['FNAME', 'LNAME', 'EMAIL'],
	'contact-form': ['FNAME', 'LNAME', 'EMAIL', 'PHONE', 'ADDRESS'],
	default: [],
};

export const formFieldTitles = {
	FNAME: __('First Name', 'mailchimp'),
	LNAME: __('Last Name', 'mailchimp'),
	EMAIL: __('Email', 'mailchimp'),
	PHONE: __('Phone', 'mailchimp'),
	ADDRESS: __('Address', 'mailchimp'),
};

const prepareInnerBlocks = (merge_fields = [], template = 'default') => {
	const fields = formFields[template] || [];
	const fieldInnerBlocks = [...merge_fields]
		.sort((a, b) => {
			const aIndex = fields.indexOf(a.tag);
			const bIndex = fields.indexOf(b.tag);
			return (aIndex === -1 ? Infinity : aIndex) - (bIndex === -1 ? Infinity : bIndex);
		})
		.map((field) => {
			let visible =
				(template === 'default' && field.required) ||
				fields.includes(field.tag) ||
				fields.includes(field.type?.toUpperCase());
			if (fields.length === 0) {
				visible = field.required || merge_fields_visibility?.[field.tag] === 'on';
			}
			return [
				'mailchimp/mailchimp-form-field',
				{
					tag: field.tag,
					label: field.name,
					type: field.type,
					visible,
				},
			];
		});
	const groupInnerBlocks = interest_groups.map((group) => [
		'mailchimp/mailchimp-audience-group',
		{
			id: group.id,
			label: group.title,
			visible:
				template !== 'default'
					? false
					: interest_groups_visibility?.[group.id] === 'on' && group.type !== 'hidden',
		},
	]);
	return [...fieldInnerBlocks, ...groupInnerBlocks];
};

/**
 * Template option choices for predefined columns layouts.
 *
 * @type {WPBlockVariation[]}
 */
export const variations = [
	{
		name: 'email-only-form',
		title: __('Quick Signup (Email Only)', 'mailchimp'),
		description: __('A quick signup form with only an email field.', 'mailchimp'),
		icon: 'email',
		attributes: {
			template: 'email-only-form',
		},
		innerBlocks: prepareInnerBlocks(merge_fields, 'email-only-form'),
		scope: ['block'],
	},
	{
		name: 'name-and-email-form',
		title: __('Personal Signup (Name and Email)', 'mailchimp'),
		description: __('A personal signup form with only a name and email fields', 'mailchimp'),
		icon: 'admin-users',
		attributes: {
			template: 'name-and-email-form',
		},
		innerBlocks: prepareInnerBlocks(merge_fields, 'name-and-email-form'),
		scope: ['block'],
	},
	{
		name: 'contact-form',
		title: __('Contact Form (Contact Details)', 'mailchimp'),
		description: __(
			'A full contact details form with name, email, phone and address fields',
			'mailchimp',
		),
		icon: 'id',
		attributes: {
			template: 'contact-form',
		},
		innerBlocks: prepareInnerBlocks(merge_fields, 'contact-form'),
		scope: ['block'],
	},
	{
		name: 'default',
		title: __('Default Form (All Fields)', 'mailchimp'),
		description: __('A default form, Fields based on settings.', 'mailchimp'),
		icon: 'admin-settings',
		attributes: {
			template: 'default',
		},
		isDefault: true,
		innerBlocks: prepareInnerBlocks(merge_fields, 'default'),
		scope: ['block'],
	},
];
