import { BlockControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Disabled, ToolbarButton, ToolbarGroup } from '@wordpress/components';

const ToolbarVisibilityGroup = ({ visible, onClick }) => {
	return (
		<ToolbarGroup>
			<ToolbarButton
				title={__('Visibility', 'mailchimp')}
				icon="hidden"
				onClick={onClick}
				className={!visible ? 'is-pressed' : undefined}
			/>
		</ToolbarGroup>
	);
};

export const MailchimpGroup = (props) => {
	const {
		attributes,
		setAttributes,
		context: { 'mailchimp/list_id': listId },
	} = props;
	const { id, label, visible } = attributes;
	const { mailchimpListData } = window;
	const group = mailchimpListData?.[listId]?.interestGroups?.[id] || {};

	if (!group) {
		return null;
	}

	const { type } = group;

	return (
		<div
			className={`${visible && type !== 'hidden' ? 'mailchimp_interest_group_visible' : 'mailchimp_interest_group_hidden'}`}
		>
			<div className="mc_interests_header">
				<RichText
					tagName="label"
					value={label}
					onChange={(label) => setAttributes({ label })}
					placeholder={__('Enter a label', 'mailchimp')}
				/>
			</div>
			{!!(visible && type !== 'hidden') && (
				<Disabled>
					<div className="mc_interest">
						{group.type === 'checkboxes' &&
							group.groups.map((choice) => (
								<>
									<label
										htmlFor={`mc_interest_${group.id}_${choice.id}`}
										className="mc_interest_label"
									>
										<input
											id={`mc_interest_${group.id}_${choice.id}`}
											type="checkbox"
											name={`group[${group.id}][${choice.id}]`}
											value={choice.id}
											className="mc_interest"
										/>
										{choice.name}
									</label>
									<br />
								</>
							))}
						{group.type === 'radio' &&
							group.groups.map((choice) => (
								<>
									<input
										id={`mc_interest_${group.id}_${choice.id}`}
										type="radio"
										name={`group[${group.id}]`}
										value={choice.id}
										className="mc_interest"
									/>
									<label
										htmlFor={`mc_interest_${group.id}_${choice.id}`}
										className="mc_interest_label"
									>
										{choice.name}
									</label>
									<br />
								</>
							))}
						{group.type === 'dropdown' && (
							<select name={`group[${group.id}]`}>
								{group.groups.map((choice) => (
									<option key={choice.id} value={choice.id}>
										{choice.name}
									</option>
								))}
							</select>
						)}
					</div>
				</Disabled>
			)}
		</div>
	);
};

export const BlockEdit = (props) => {
	const blockProps = useBlockProps();
	const {
		attributes,
		setAttributes,
		context: { 'mailchimp/list_id': listId },
	} = props;
	const { visible, id } = attributes;
	const { mailchimpListData } = window;
	const isHidden = mailchimpListData?.[listId]?.interestGroups?.[id]?.type === 'hidden';

	return (
		<div {...blockProps} style={{ color: 'inherit' }}>
			<MailchimpGroup {...props} />
			{!isHidden && (
				<BlockControls>
					<ToolbarVisibilityGroup
						visible={visible}
						onClick={() => setAttributes({ visible: !visible })}
					/>
				</BlockControls>
			)}
		</div>
	);
};
