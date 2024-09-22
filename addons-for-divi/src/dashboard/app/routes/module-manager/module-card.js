import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';
import { Toast } from '@DashboardComponents';
import { ReactSVG } from 'react-svg';

const ModuleCard = ({
	moduleInfo: { name, title, icon, demo_link, is_pro },
}) => {
	const modulesStatuses = useSelect((select) =>
		select('divitorque-lite/dashboard').getModulesStatuses()
	);

	const dispatch = useDispatch('divitorque-lite/dashboard');

	const isModuleActive = modulesStatuses[name] === name;

	const [isLoading, setIsLoading] = useState(false);

	const toggleModuleStatus = async () => {
		setIsLoading(true);

		const newStatus = isModuleActive ? 'disabled' : name;
		const updatedStatuses = { ...modulesStatuses, [name]: newStatus };

		wp.apiFetch({
			path: '/divitorque-lite/v1/save_common_settings',
			method: 'POST',
			data: { modules_settings: updatedStatuses },
		})
			.then((res) => {
				if (res.success) {
					dispatch.updateModuleStatuses(updatedStatuses);
					Toast(__('Successfully saved!', 'divitorque'), 'success');
				} else {
					Toast(__('Something went wrong!', 'divitorque'), 'error');
				}
			})
			.catch((err) => {
				Toast(err.message, 'error');
			})
			.finally(() => {
				setIsLoading(false);
			});
	};

	const moduleIconPath = window.diviTorqueLite?.module_icon_path || '';
	const moduleIcon = `${moduleIconPath}/${icon}`;
	const cardClass = `border-white bg-white shadow hover:shadow-lg p-4 rounded-md flex items-center gap-x-4 relative border ${
		is_pro ? 'dt-pro' : ''
	}`;

	return (
		<div className={cardClass}>
			<div className="flex-shrink-0">
				<ReactSVG
					className={`${is_pro ? 'opacity-70' : ''}`}
					src={moduleIcon}
					title={title}
					wrapper="span"
					beforeInjection={(svg) => {
						svg.setAttribute(
							'style',
							'width: auto; height: 40px; fill: none;'
						);
					}}
				/>
			</div>
			<div className="flex-1 min-w-0">
				<p
					className={`text-base font-medium text-de-black ${
						is_pro ? 'opacity-70' : ''
					}`}
				>
					{title}
				</p>
				<div className="flex items-center gap-2">
					{demo_link && (
						<a
							href={demo_link}
							target="_blank"
							rel="noreferrer noopener"
							className="focus-visible:text-slate-500 active:text-slate-500 hover:text-slate-500 focus:text-slate-400 text-slate-400 text-sm truncate"
						>
							{__('Live Demo', 'addons-for-divi')}
						</a>
					)}
					{is_pro && (
						<a
							href="https://diviepic.com/torque-pro/"
							target="_blank"
							rel="noreferrer noopener"
							className="focus-visible:text-slate-500 active:text-slate-500 hover:text-slate-500 focus:text-slate-400 text-slate-400 text-sm truncate"
						>
							{__('Get Pro', 'addons-for-divi')}
						</a>
					)}
				</div>
			</div>
			{!is_pro && (
				<div className="dt-toggle-control">
					<ToggleControl
						checked={isModuleActive}
						onChange={toggleModuleStatus}
						disabled={isLoading}
					/>
				</div>
			)}
			{is_pro && (
				<div className="text-[10px] leading-[10px] border border-[#354559] bg-[#354559] text-white rounded px-1.5 py-[2px]">
					{__('Pro', 'addons-for-divi')}
				</div>
			)}
		</div>
	);
};

export default ModuleCard;
