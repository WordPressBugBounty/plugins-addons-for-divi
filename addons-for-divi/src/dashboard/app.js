import { render } from '@wordpress/element';
import { HashRouter } from 'react-router-dom';
import domReady from '@wordpress/dom-ready';
import registerStore from './app/store';
import { ToastContainer } from 'react-toastify';
import { setUtm } from './utils/helper-functions';

import AppRoutes from './app/routes';
import { __ } from '@wordpress/i18n';
import { Logo, Menu, MenuItem, Header } from '@DashboardComponents';

// Styler
import 'react-toastify/dist/ReactToastify.css';

const menuItems = [
	{ name: __('Module Manager', 'addons-for-divi'), path: '/module-manager' },
	{ name: __('Settings', 'addons-for-divi'), path: '/settings' },
	{ name: __('Get Pro', 'addons-for-divi'), path: '/get-pro' },
];

domReady(() => {
	const rootElement = document.getElementById('divitorque-root');
	if (!rootElement) return;

	registerStore();

	const pro_link = setUtm(window.diviTorqueLite.upgradeLink, 'viewalldtpf');

	const Dashboard = () => (
		<div className="divitorque-app">
			<HashRouter>
				<Header pro_link={pro_link}>
					<div className="flex items-center justify-start gap-1">
						<Logo />{' '}
						<span className="font-bold text-base text-de-black">
							{__('Divi Torque Lite', 'addons-for-divi')}
						</span>
					</div>

					<Menu>
						{menuItems.map((item) => (
							<MenuItem
								key={item.path}
								path={item.path}
								name={item.name}
							/>
						))}
					</Menu>
				</Header>

				<AppRoutes />
				<ToastContainer />
			</HashRouter>
		</div>
	);

	render(<Dashboard />, rootElement);
});
