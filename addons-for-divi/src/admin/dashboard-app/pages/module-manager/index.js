import { __ } from '@wordpress/i18n';
import ModuleCard from './module-card';
import { Toast, Divi5Notice } from '@DashboardComponents';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

const Modules = () => {
	const {
		module_info: allLiteModules = [],
		pro_module_info: allProModules = [],
		is_lite_installed,
	} = window.diviTorqueLite || {};

	const allModules = [...allLiteModules, ...allProModules];

	const allModulesStatuses = useSelect((select) =>
		select('divitorque/dashboard').getModulesStatuses()
	);

	const [allEnabled, setAllEnabled] = useState(false);
	const [allDisabled, setAllDisabled] = useState(false);
	const [filter, setFilter] = useState('all');
	const [isLoading, setIsLoading] = useState(false);

	useEffect(() => {
		const liteModuleNames = allLiteModules.map((module) => module.name);
		const liteStatuses = Object.entries(allModulesStatuses)
			.filter(([key]) => liteModuleNames.includes(key))
			.map(([_, value]) => value);

		setAllDisabled(liteStatuses.every((status) => status === 'disabled'));
		setAllEnabled(liteStatuses.every((status) => status !== 'disabled'));
	}, [allModulesStatuses, allLiteModules]);

	// Sort modules based on the 'new' badge and title
	const sortedModules = allModules.sort((a, b) => {
		if (a.badge === 'new' && b.badge !== 'new') return -1;
		if (a.badge !== 'new' && b.badge === 'new') return 1;
		return a.title.localeCompare(b.title);
	});

	const proModules = sortedModules.filter((module) => module.is_pro);
	const liteModules = sortedModules.filter((module) => !module.is_pro);

	const dispatch = useDispatch('divitorque/dashboard');

	const getFilteredModules = () => {
		// Only show lite modules - no pro placeholders
		return filter === 'lite' ? liteModules : liteModules;
	};

	const toggleModuleStatus = async (status) => {
		if (isLoading) return;
		setIsLoading(true);

		const updatedStatuses = { ...allModulesStatuses };
		liteModules.forEach((module) => {
			updatedStatuses[module.name] = status ? module.name : 'disabled';
		});

		try {
			const res = await wp.apiFetch({
				path: '/divitorque-lite/v1/save_common_settings',
				method: 'POST',
				data: { modules_settings: updatedStatuses },
			});

			if (res.success) {
				dispatch.updateModuleStatuses(updatedStatuses);
				Toast(__('Successfully saved!', 'divitorque'), 'success');
			} else {
				Toast(__('Something went wrong!', 'divitorque'), 'error');
			}
		} catch (err) {
			Toast(err.message, 'error');
		} finally {
			setIsLoading(false);
		}
	};

	const renderModules = () => {
		return getFilteredModules().map((module, index) => (
			<ModuleCard
				key={module.name || index}
				moduleInfo={module}
				isLiteInstalled={is_lite_installed}
			/>
		));
	};

	return (
		<div className="divitorque-app">
			{/* Divi 5 Roadmap Notice */}
			<Divi5Notice />

			{/* Main Content Area - Full Width */}
			<div className="bg-white rounded-lg shadow-sm border border-gray-200">
				{/* Header */}
				<div className="px-6 py-4 border-b border-gray-200">
					<div className="flex items-center justify-between">
						<div>
							<h2 className="text-lg font-semibold text-gray-900">
								{__('Modules', 'addons-for-divi')}
							</h2>
							<p className="text-sm text-gray-500 mt-0.5">
								{__(
									'Manage your Divi Torque modules',
									'addons-for-divi'
								)}
							</p>
						</div>

						{/* Action Buttons */}
						<div className="flex items-center gap-2">
							<button
								type="button"
								className={`px-4 py-2 text-sm font-medium rounded-md transition-colors ${
									allDisabled || isLoading
										? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-400'
										: 'text-gray-700 hover:bg-gray-100'
								}`}
								onClick={() => toggleModuleStatus(false)}
								disabled={allDisabled || isLoading}
							>
								{isLoading
									? __('Processing...', 'addons-for-divi')
									: __('Disable all', 'addons-for-divi')}
							</button>
							<button
								type="button"
								className={`px-4 py-2 text-sm font-medium rounded-md transition-colors ${
									allEnabled || isLoading
										? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-500'
										: 'bg-indigo-600 text-white hover:bg-indigo-700'
								}`}
								onClick={() => toggleModuleStatus(true)}
								disabled={allEnabled || isLoading}
							>
								{isLoading
									? __('Processing...', 'addons-for-divi')
									: __('Enable all', 'addons-for-divi')}
							</button>
						</div>
					</div>
				</div>

				{/* Modules Grid - 5 columns */}
				<div className="p-6">
					<div className="grid grid-cols-5 gap-3">
						{renderModules()}
					</div>
				</div>
			</div>

			{/* Upgrade CTA - Minimal Footer */}
			<div className="mt-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border border-purple-100">
				<div className="flex items-center justify-between">
					<div>
						<h4 className="font-bold text-gray-900 mb-1">
							{__('Need More Power?', 'addons-for-divi')}
						</h4>
						<p className="text-sm text-gray-600">
							{__(
								'Get 50+ PRO modules, extensions, and priority support with DiviTorque Pro',
								'addons-for-divi'
							)}
						</p>
					</div>
					<a
						href="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=upgrade"
						target="_blank"
						rel="noopener noreferrer"
						className="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold text-sm rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg whitespace-nowrap"
					>
						{__('View Pro Features', 'addons-for-divi')}
					</a>
				</div>
			</div>
		</div>
	);
};

export default Modules;
