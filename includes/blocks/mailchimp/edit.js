import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Placeholder, Button, Disabled } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import Icon from './icon';

export const BlockEdit = ({ isSelected }) => {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			{isSelected ? (
				<Placeholder
					icon={Icon}
					label={__('Mailchimp Block', 'mailchimp_i18n')}
					instructions={__('Great work! Your block is ready to go.', 'mailchimp_i18n')}
				>
					<div>
						<Button
							style={{ paddingLeft: 0 }}
							variant="link"
							href={window.MAILCHIMP_ADMIN_SETTINGS_URL}
						>
							{__(
								"Head over here if you'd like to adjust your settings.",
								'mailchimp_i18n',
							)}
						</Button>
					</div>
				</Placeholder>
			) : (
				<Disabled>
					<ServerSideRender block="mailchimp/mailchimp" />
				</Disabled>
			)}
		</div>
	);
};
