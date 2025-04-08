import { registerBlockType } from '@wordpress/blocks';
import { SVG, Path } from '@wordpress/components';

import { __ } from '@wordpress/i18n';
import { BlockEdit } from './edit';
import metadata from './block.json';

const renderIcon = (svg, width = 24, height = 24, viewbox = '0 -960 960 960') => (
	<SVG
		xmlns="http://www.w3.org/2000/svg"
		width={width}
		height={height}
		viewBox={viewbox}
		fill="currentColor"
	>
		{svg}
	</SVG>
);

const variations = [
	{
		name: 'email',
		title: __('Email Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M168-192q-29.7 0-50.85-21.16Q96-234.32 96-264.04v-432.24Q96-726 117.15-747T168-768h624q29.7 0 50.85 21.16Q864-725.68 864-695.96v432.24Q864-234 842.85-213T792-192H168Zm312-240L168-611v347h624v-347L480-432Zm0-85 312-179H168l312 179Zm-312-94v-85 432-347Z" />,
		),
	},
	{
		name: 'text',
		title: __('Text Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M288-192v-480H96v-96h480v96H384v480h-96Zm360 0v-288H528v-96h336v96H744v288h-96Z" />,
		),
	},
	{
		name: 'number',
		title: __('Number Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M240-384v-144h-48v-48h96v192h-48Zm144 0v-96q0-10.2 6.9-17.1 6.9-6.9 17.1-6.9h72v-24h-96v-48h120q10.2 0 17.1 6.9 6.9 6.9 6.9 17.1v72q0 10.2-6.9 17.1-6.9 6.9-17.1 6.9h-72v24h96v48H384Zm216 0v-48h96v-24h-48v-48h48v-24h-96v-48h120q10.2 0 17.1 6.9 6.9 6.9 6.9 17.1v144q0 10.2-6.9 17.1-6.9 6.9-17.1 6.9H600Z" />,
		),
	},
	{
		name: 'radio',
		title: __('Radio buttons Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M480.23-288Q560-288 616-344.23q56-56.22 56-136Q672-560 615.77-616q-56.22-56-136-56Q400-672 344-615.77q-56 56.22-56 136Q288-400 344.23-344q56.22 56 136 56Zm.05 192Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Zm-.28-72q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z" />,
		),
	},
	{
		name: 'dropdown',
		title: __('Dropdown Field', 'mailchimp'),
		icon: renderIcon(<Path d="M480-333 240-573l51-51 189 189 189-189 51 51-240 240Z" />),
	},
	{
		name: 'date',
		title: __('Date Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M576.23-240Q536-240 508-267.77q-28-27.78-28-68Q480-376 507.77-404q27.78-28 68-28Q616-432 644-404.23q28 27.78 28 68Q672-296 644.23-268q-27.78 28-68 28ZM216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Zm0-432h528v-96H216v96Zm0 0v-96 96Z" />,
		),
	},
	{
		name: 'birthday',
		title: __('Birthday Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M576.23-240Q536-240 508-267.77q-28-27.78-28-68Q480-376 507.77-404q27.78-28 68-28Q616-432 644-404.23q28 27.78 28 68Q672-296 644.23-268q-27.78 28-68 28ZM216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Zm0-432h528v-96H216v96Zm0 0v-96 96Z" />,
		),
	},
	{
		name: 'address',
		title: __('Address', 'mailchimp'),
		icon: renderIcon(
			<Path d="M264-216h96v-240h240v240h96v-348L480-726 264-564v348Zm-72 72v-456l288-216 288 216v456H528v-240h-96v240H192Zm288-327Z" />,
		),
	},
	{
		name: 'zip',
		title: __('Zip Code', 'mailchimp'),
		icon: renderIcon(
			<Path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-191q119-107 179.5-197T720-549q0-105-68.5-174T480-792q-103 0-171.5 69T240-549q0 71 60.5 161T480-191Zm0 95Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Zm0-456Z" />,
		),
	},
	{
		name: 'phone',
		title: __('Phone Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M264-48q-29.7 0-50.85-21.15Q192-90.3 192-120v-720q0-29.7 21.15-50.85Q234.3-912 264-912h432q29.7 0 50.85 21.15Q768-869.7 768-840v720q0 29.7-21.15 50.85Q725.7-48 696-48H264Zm0-120v48h432v-48H264Zm0-72h432v-480H264v480Zm0-552h432v-48H264v48Zm0 0v-48 48Zm0 624v48-48Z" />,
		),
	},
	{
		name: 'url',
		title: __('Website Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M480-96q-79 0-149-30t-122.5-82.5Q156-261 126-331T96-480q0-80 30-149.5t82.5-122Q261-804 331-834t149-30q80 0 149.5 30t122 82.5Q804-699 834-629.5T864-480q0 79-30 149t-82.5 122.5Q699-156 629.5-126T480-96Zm0-75q17-17 34-63.5T540-336H420q9 55 26 101.5t34 63.5Zm-91-10q-14-30-24.5-69T347-336H204q29 57 77 97.5T389-181Zm182 0q60-17 108-57.5t77-97.5H613q-7 47-17.5 86T571-181ZM177-408h161q-2-19-2.5-37.5T335-482q0-18 .5-35.5T338-552H177q-5 19-7 36.5t-2 35.5q0 18 2 35.5t7 36.5Zm234 0h138q2-20 2.5-37.5t.5-34.5q0-17-.5-35t-2.5-37H411q-2 19-2.5 37t-.5 35q0 17 .5 35t2.5 37Zm211 0h161q5-19 7-36.5t2-35.5q0-18-2-36t-7-36H622q2 19 2.5 37.5t.5 36.5q0 18-.5 35.5T622-408Zm-9-216h143q-29-57-77-97.5T571-779q14 30 24.5 69t17.5 86Zm-193 0h120q-9-55-26-101.5T480-789q-17 17-34 63.5T420-624Zm-216 0h143q7-47 17.5-86t24.5-69q-60 17-108 57.5T204-624Z" />,
		),
	},
	{
		name: 'imageurl',
		title: __('Image URL Field', 'mailchimp'),
		icon: renderIcon(
			<Path d="M216-144q-29.7 0-50.85-21.5Q144-187 144-216v-528q0-29 21.15-50.5T216-816h528q29.7 0 50.85 21.5Q816-773 816-744v528q0 29-21.15 50.5T744-144H216Zm0-72h528v-528H216v528Zm48-72h432L552-480 444-336l-72-96-108 144Zm-48 72v-528 528Z" />,
		),
	},
];

variations.forEach((variation, index) => {
	variations[index].attributes = { type: variation.name };
	variations[index].isActive = ['type'];
});

registerBlockType(metadata, {
	edit: BlockEdit,
	save: () => null,
	variations,
});
