import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Placeholder, Button, Disabled } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import Icon from './icon';

const disallowedThemesSSR = [
	'twentytwentyone',
	'twentytwenty',
	'twentynineteen',
	'twentyeighteen',
	'twentyseventeen',
	'twentysixteen',
	'twentyfifteen',
	'twentyfourteen',
];

export const BlockEdit = ({ isSelected }) => {
	const blockProps = useBlockProps();
	const isDisallowedThemeSSR = useSelect((select) => {
		const currentTheme = select('core').getCurrentTheme();
		if (!currentTheme || (!'template') in currentTheme) {
			return false;
		}
		return disallowedThemesSSR.includes(currentTheme.template);
	});

	return (
		<div {...blockProps}>
			{isSelected || isDisallowedThemeSSR ? (
				<Placeholder
					icon={Icon}
					label={__('Mailchimp Block', 'mailchimp')}
					instructions={__('Great work! Your block is ready to go.', 'mailchimp')}
				>
					<div>
						<Button
							style={{ paddingLeft: 0 }}
							variant="link"
							href={window.MAILCHIMP_ADMIN_SETTINGS_URL}
						>
							{__(
								"Head over here if you'd like to adjust your settings.",
								'mailchimp',
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
