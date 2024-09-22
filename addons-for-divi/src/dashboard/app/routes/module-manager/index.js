import { __ } from '@wordpress/i18n';
import ModuleCard from './module-card';
import { Toast } from '@DashboardComponents';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

const Modules = () => {
	const isProInstalled = window.diviTorqueLite?.is_pro_installed;

	const { module_info: liteModules = [], pro_module_info: proModules = [] } =
		window.diviTorqueLite || {};

	const liteModulesStatuses = useSelect((select) =>
		select('divitorque-lite/dashboard').getModulesStatuses()
	);

	const [allEnabled, setAllEnabled] = useState(false);
	const [allDisabled, setAllDisabled] = useState(false);

	useEffect(() => {
		const statuses = Object.values(liteModulesStatuses); // Get all status values from the object
		const allDisabled = statuses.every((status) => status === 'disabled');
		const allEnabled = statuses.every((status) => status !== 'disabled');

		setAllDisabled(allDisabled);
		setAllEnabled(allEnabled);
	}, [liteModulesStatuses]);

	const moduleMap = new Map();

	liteModules.forEach((module) => {
		moduleMap.set(module.name, { ...module, is_pro: false });
	});

	if (!isProInstalled) {
		proModules.forEach((module) => {
			if (!moduleMap.has(module.name)) {
				moduleMap.set(module.name, { ...module, is_pro: true });
			}
		});
	}

	const allModules = Array.from(moduleMap.values());

	allModules.sort((a, b) => a.title.localeCompare(b.title));

	const dispatch = useDispatch('divitorque-lite/dashboard');

	const toggleModuleStatus = async (status) => {
		const updatedStatuses = liteModules.reduce((acc, module) => {
			acc[module.name] = status ? module.name : 'disabled';
			return acc;
		}, {});

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
			});
	};

	return (
		<div className="dt-app-wrap">
			<div className="px-6 mx-auto lg:max-w-[80rem] mt-10 mb-8 flex items-center flex-row">
				<h2 className="font-semibold text-2xl flex-1">
					{__('Module Manager', 'addons-for-divi')}
				</h2>
				<div className="flex items-center gap-2">
					<button
						aria-label={__('Disable All', 'addons-for-divi')}
						type="button"
						className={`focus:text-de-black hover:bg-white relative inline-flex items-center px-4 py-2 border border-de-app-color-dark bg-transparent text-sm font-medium text-de-app-color-dark focus:z-10 focus:outline-none rounded-md transition ${
							allDisabled ? 'opacity-50' : ''
						}`}
						onClick={() => toggleModuleStatus(false)}
						disabled={allDisabled}
					>
						{__('Disable All', 'addons-for-divi')}
					</button>
					<button
						aria-label={__('Enable All', 'addons-for-divi')}
						type="button"
						className={`focus:text-slate-200 relative inline-flex items-center px-4 py-2 border border-de-app-color-dark bg-de-app-color-dark text-sm font-medium text-white focus:z-10 focus:outline-none rounded-md transition ${
							allEnabled ? 'opacity-50' : ''
						}`}
						onClick={() => toggleModuleStatus(true)}
						disabled={allEnabled}
					>
						{__('Enable All', 'addons-for-divi')}
					</button>
				</div>
			</div>
			<div className="px-6 mx-auto lg:max-w-[80rem] grid grid-flow-row grid-cols-3 gap-6">
				{allModules.map((module, index) => (
					<ModuleCard key={index} moduleInfo={module} />
				))}
			</div>
		</div>
	);
};

export default Modules;
