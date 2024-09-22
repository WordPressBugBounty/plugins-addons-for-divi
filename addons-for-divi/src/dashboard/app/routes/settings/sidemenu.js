import React from 'react';
import { __ } from '@wordpress/i18n';
import { NavLink } from 'react-router-dom';

const SideMenu = () => {
	const menuItems = [{ name: 'Tools', path: '/tools' }];

	return (
		<nav className="space-y-1">
			{menuItems.map(({ name, path }) => (
				<NavLink
					key={path}
					to={path}
					className={({ isActive }) =>
						isActive
							? `border-de-app-color text-de-app-color hover:text-black border-l-4 py-3 pl-5 flex items-center text-base font-medium`
							: `border-white text-black hover:text-de-app-color border-l-4 py-3 pl-5 flex items-center text-base font-medium`
					}
				>
					{name}
				</NavLink>
			))}
		</nav>
	);
};

export default SideMenu;
