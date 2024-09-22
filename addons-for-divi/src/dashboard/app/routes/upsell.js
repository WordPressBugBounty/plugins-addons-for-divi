/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { setUtm } from '../../utils/helper-functions';

const crossIcon = (
	<svg
		width="31"
		height="31"
		viewBox="0 0 31 31"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
		role="img"
		aria-hidden="true"
	>
		<path
			d="M22.5326 10.5767L17.2226 15.8867L22.5326 21.1967L20.4176 23.3117L15.1076 18.0167L9.81262 23.3117L7.68262 21.1817L12.9776 15.8867L7.68262 10.5917L9.81262 8.46167L15.1076 13.7567L20.4176 8.46167L22.5326 10.5767Z"
			fill="#ff550e"
		></path>
	</svg>
);

const checkIcon = (
	<svg
		width="31"
		height="31"
		viewBox="0 0 31 31"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
		role="img"
		aria-hidden="true"
	>
		<path
			d="M22.9863 7.99243L12.7863 18.1924L8.58633 13.9924L6.48633 16.0924L12.7863 22.3924L25.0863 10.0924"
			fill="#0e40ff"
		></path>
	</svg>
);

const FEATURES = [
	{
		name: 'lite-modules',
		title: __('More than 20 Custom Modules', 'addons-for-divi'),
		description: __(
			'Unlock the full capabilities of the Divi theme with our expansive collection of over 20 page-building modules, designed to provide everything you need to create a stunning website.',
			'addons-for-divi'
		),
		inFree: true,
	},
	{
		name: 'premium-modules',
		title: __('40 Premium Modules', 'addons-for-divi'),
		description: __(
			`Enhance your website's design with powerful 40 Pro Modules, like the Instagram Feed, Filterable Gallery Module and more modules are coming soon`,
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'contact-form7',
		title: 'Contact Form 7 Styler',
		description: __(
			'Say goodbye to plain Contact Form 7 forms and embrace stylish designs, enhanced with the styler.',
			'addons-for-divi'
		),
		inFree: true,
	},
	{
		name: 'image-carousel',
		title: 'Carousel Module',
		description: __(
			`The Image Carousel module lets users showcase images in a sliding carousel format. You can add multiple images to the module, and they will scroll horizontally according to your settings. Customize the carousel's position, navigation controls, auto-play settings, and more for a personalized display.`,
			'addons-for-divi'
		),
		inFree: true,
	},
	{
		name: 'gravity-forms',
		title: 'Gravity Forms Styler',
		description: __(
			'Improve the appearance of your Gravity form to make it stand out. Customize each field, including buttons, success/error messages, colors, typography, and more.',
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'filterable-gallery',
		title: 'Filterable Gallery Module',
		description: __(
			'Display as many images as you like with Filterable Gallery, ensuring top-notch quality and arranging them in an exceptional layout.',
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'instagram-feed',
		title: 'Instagram Feed Module',
		description: __(
			'Enhance user experience and boost brand visibility with the easy-to-use and versatile Instagram Feed module in Divi Torque Pro.',
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'whatsapp-chat',
		title: 'WhatsApp Chat',
		description: __(
			'The WhatsApp Chat module enables your website visitors to start a conversation directly with your WhatsApp number with just one click.',
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'social-share',
		title: 'Social Share Buttons',
		description: __(
			'The Social Share Buttons for Divi lets you add beautiful social sharing buttons to your WordPress posts/pages.',
			'addons-for-divi'
		),
		is_pro: true,
	},
	{
		name: 'priority-support',
		title: 'Priority Support & Updates',
		description: __(
			'Receive top-tier attention and expert guidance from our dedicated team whenever you need it. Your support tickets will receive the highest priority for faster resolutions.',
			'addons-for-divi'
		),
		is_pro: true,
	},
];

const Upsell = () => {
	return (
		<div className="dt-app-wrap">
			<div className="px-6 mx-auto lg:max-w-[80rem] mt-10 mb-8">
				<h1 className="font-semibold text-2xl">
					{__(
						'Powerful features available only in PRO',
						'addons-for-divi'
					)}
				</h1>
			</div>
			<div className="px-6 mx-auto lg:max-w-[80rem] mt-10 mb-8 overflow-hidden">
				<div className="bg-gray-50 p-5 grid grid-cols-[5fr_1fr_1fr] gap-4 text-center text-de-black">
					<div></div>
					<div className="font-bold text-xl text-center">Free</div>
					<div className="font-bold text-xl text-center">Pro</div>
				</div>

				{FEATURES.map((feature, i) => {
					const even_odd = i % 2 === 0 ? 'bg-white' : 'bg-gray-100';

					return (
						<div
							key={i}
							className={`${even_odd} grid grid-cols-[5fr_1fr_1fr] gap-4 text-center border border-[#e2e5ed] border-b-0`}
						>
							<div className="text-left px-[40px] py-[20px]">
								<h3 className="py-3 font-bold text-lg text-de-black">
									{feature.title}
								</h3>
								<p className="text-sm text-de-semidark-gray py-3">
									{feature.description}
								</p>
							</div>

							<div className="flex justify-center border-l border-[#e2e5ed] items-center p-6">
								{feature?.inFree ? checkIcon : crossIcon}
							</div>
							<div className="flex justify-center border-l border-[#e2e5ed] items-center p-6">
								{checkIcon}
							</div>
						</div>
					);
				})}
			</div>
			<div className="px-6 mx-auto lg:max-w-[80rem] mt-10 mb-8">
				<Button
					variant="primary"
					href={setUtm(
						window.diviTorqueLite.upgradeLink,
						'viewalldtpf'
					)}
					target="_blank"
					className="w-full rounded flex items-center justify-center text-lg font-medium px-3 py-8"
				>
					{__('View all 40 Pro Modules', 'addons-for-divi')}
				</Button>
			</div>
		</div>
	);
};

export default Upsell;
