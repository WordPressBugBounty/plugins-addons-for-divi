import { useEffect } from '@wordpress/element';
import { Outlet, useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import SideMenu from './sidemenu';

const Settings = () => {
	const navigate = useNavigate();

	useEffect(() => {
		navigate('/settings/tools');
	}, [navigate]);

	return (
		<div className="dt-app-wrap">
			<div className="px-6 mx-auto lg:max-w-[80rem] mt-10 mb-8 flex items-center flex-row">
				<h1 className="font-semibold text-2xl flex-1">
					{__('Settings', 'divitorque')}
				</h1>
			</div>
			<div className="px-6 mx-auto max-w-[80rem] ">
				<main className="bg-white rounded-md shadow min-h-[36rem]">
					<div className="grid grid-cols-12 min-h-[36rem] h-full">
						<aside className="py-6 sm:px-6 lg:py-6 lg:px-0 lg:col-span-3">
							<SideMenu />
						</aside>

						<div className="col-span-9 border-l">
							<Outlet />
						</div>
					</div>
				</main>
			</div>
		</div>
	);
};

export default Settings;
