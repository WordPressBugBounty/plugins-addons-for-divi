import { __ } from '@wordpress/i18n';

export const setUtm = (urlAdress, linkArea) => {
	const urlLink = new URL(urlAdress);
	urlLink.searchParams.set('utm_campaign', linkArea);
	return urlLink.toString();
};
