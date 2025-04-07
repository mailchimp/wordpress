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

export const MailchimpFormField = (props) => {
	const {
		attributes,
		setAttributes,
		context: {
			'mailchimp/list_id': listId,
			'mailchimp/show_required_indicator': showRequiredIndicator,
		},
	} = props;
	const { tag, label, visible, type } = attributes;
	const { mailchimpListData } = window;
	const field = mailchimpListData?.[listId]?.mergeFields?.[tag] || {};

	if (!field) {
		return (
			<Disabled>
				{__('Something went wrong while rendering the field.', 'mailchimp')}
			</Disabled>
		);
	}

	const { required, help_text } = field;

	const renderInputField = () => {
		switch (type) {
			case 'date':
				return (
					<input
						type="text"
						size="18"
						placeholder={field?.default_value || ''}
						data-format={field?.options?.date_format}
						name={tag}
						id={tag}
						className="date-pick mc_input"
					/>
				);
			case 'radio':
				if (field?.options?.choices) {
					return (
						<ul className="mc_list">
							{field.options.choices.map((choice, index) => (
								<li key={choice}>
									<input
										type="radio"
										id={`${tag}_${index}`}
										name={tag}
										className="mc_radio"
										value={choice}
										checked={field?.default_value === choice}
									/>
									<label htmlFor={`${tag}_${index}`} className="mc_radio_label">
										{choice}
									</label>
								</li>
							))}
						</ul>
					);
				}
				break;

			case 'dropdown':
				if (field?.options?.choices) {
					return (
						<select id={tag} name={tag} className="mc_select">
							{field.options.choices.map((choice) => (
								<option
									key={choice}
									value={choice}
									selected={field?.default_value === choice}
								>
									{choice}
								</option>
							))}
						</select>
					);
				}
				break;

			case 'birthday':
				return (
					<input
						type="text"
						size="18"
						placeholder={field?.default_value || ''}
						data-format={field?.options?.date_format}
						name={tag}
						id={tag}
						className="birthdate-pick mc_input"
					/>
				);

			case 'birthday-old': {
				const days = Array.from({ length: 31 }, (_, i) => i + 1);
				const months = [
					__('January', 'mailchimp'),
					__('February', 'mailchimp'),
					__('March', 'mailchimp'),
					__('April', 'mailchimp'),
					__('May', 'mailchimp'),
					__('June', 'mailchimp'),
					__('July', 'mailchimp'),
					__('August', 'mailchimp'),
					__('September', 'mailchimp'),
					__('October', 'mailchimp'),
					__('November', 'mailchimp'),
					__('December', 'mailchimp'),
				];

				return (
					<>
						<select id={`${tag}-month`} name={`${tag}[month]`} className="mc_select">
							{months.map((month) => (
								<option key={month} value={month}>
									{month}
								</option>
							))}
						</select>
						<select id={`${tag}-day`} name={`${tag}[day]`} className="mc_select">
							{days.map((day) => (
								<option key={day} value={day}>
									{day}
								</option>
							))}
						</select>
					</>
				);
			}

			case 'address': {
				// Fields are disabled for now, So only USA added as of now.
				const countries = {
					164: __('USA', 'mailchimp'),
				};

				return (
					<>
						<label htmlFor={`${tag}-addr1`} className="mc_address_label">
							{__('Street Address', 'mailchimp')}
						</label>
						<input
							type="text"
							size="18"
							name={`${tag}[addr1]`}
							id={`${tag}-addr1`}
							className="mc_input"
						/>
						<label htmlFor={`${tag}-addr2`} className="mc_address_label">
							{__('Address Line 2', 'mailchimp')}
						</label>
						<input
							type="text"
							size="18"
							name={`${tag}[addr2]`}
							id={`${tag}-addr2`}
							className="mc_input"
						/>
						<label htmlFor={`${tag}-city`} className="mc_address_label">
							{__('City', 'mailchimp')}
						</label>
						<input
							type="text"
							size="18"
							name={`${tag}[city]`}
							id={`${tag}-city`}
							className="mc_input"
						/>
						<label htmlFor={`${tag}-state`} className="mc_address_label">
							{__('State', 'mailchimp')}
						</label>
						<input
							type="text"
							size="18"
							name={`${tag}[state]`}
							id={`${tag}-state`}
							className="mc_input"
						/>
						<label htmlFor={`${tag}-zip`} className="mc_address_label">
							{__('Zip / Postal', 'mailchimp')}
						</label>
						<input
							type="text"
							size="18"
							name={`${tag}[zip]`}
							id={`${tag}-zip`}
							className="mc_input"
						/>
						<label htmlFor={`${tag}-country`} className="mc_address_label">
							{__('Country', 'mailchimp')}
						</label>
						<select name={`${tag}[country]`} id={`${tag}-country`}>
							{Object.entries(countries).map(([country_code, country_name]) => (
								<option
									value={country_code}
									selected={field?.options?.default_country === country_code}
								>
									{country_name}
								</option>
							))}
						</select>
					</>
				);
			}

			case 'zip':
				return (
					<input
						type="text"
						size="18"
						maxLength="5"
						name={tag}
						id={tag}
						className="mc_input"
					/>
				);

			case 'phone':
				if (field?.options?.phone_format === 'US') {
					return (
						<>
							<input
								type="text"
								size="2"
								maxLength="3"
								name={`${tag}[area]`}
								id={`${tag}-area`}
								className="mc_input mc_phone"
							/>
							<input
								type="text"
								size="2"
								maxLength="3"
								name={`${tag}[detail1]`}
								id={`${tag}-detail1`}
								className="mc_input mc_phone"
							/>
							<input
								type="text"
								size="5"
								maxLength="4"
								name={`${tag}[detail2]`}
								id={`${tag}-detail2`}
								className="mc_input mc_phone"
							/>
						</>
					);
				}
				return <input type="text" size="18" name={tag} id={tag} className="mc_input" />;

			case 'email':
			case 'url':
			case 'imageurl':
			case 'text':
			case 'number':
			default:
				return (
					<input
						type="text"
						size="18"
						placeholder={field?.default_value || ''}
						name={tag}
						id={tag}
						className="mc_input"
					/>
				);
		}
		return null;
	};

	return (
		<div
			className={`mc_merge_var ${visible ? 'mailchimp_merge_field_visible' : 'mailchimp_merge_field_hidden'}`}
		>
			<label htmlFor={tag} className={`mc_var_label mc_header mc_header_${type}`}>
				<RichText
					tagName="label"
					value={label}
					onChange={(label) => setAttributes({ label })}
					placeholder={__('Enter a label', 'mailchimp')}
				/>
				{required && showRequiredIndicator && <span className="mc_required">*</span>}
			</label>
			{!!visible && (
				<Disabled>
					{renderInputField()}
					{help_text && <span className="mc_help">{help_text}</span>}
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
	const { visible, tag } = attributes;
	const { mailchimpListData } = window;
	const isRequired = mailchimpListData?.[listId]?.mergeFields?.[tag]?.required || false;

	return (
		<div {...blockProps} style={{ color: 'inherit' }}>
			<MailchimpFormField {...props} />
			{!isRequired && (
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
