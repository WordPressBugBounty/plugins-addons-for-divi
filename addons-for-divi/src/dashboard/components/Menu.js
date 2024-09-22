import React from 'react';
import { __ } from '@wordpress/i18n';

const Menu = ({ children }) => {
	return (
		<div className="flex items-center flex-row ml-[2rem]">{children}</div>
	);
};

export default Menu;
