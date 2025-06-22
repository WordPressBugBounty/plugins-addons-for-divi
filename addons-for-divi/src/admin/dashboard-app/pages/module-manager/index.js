import { __ } from '@wordpress/i18n';
import ModuleCard from './module-card';
import { Toast } from '@DashboardComponents';
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
		switch (filter) {
			case 'pro':
				return proModules;
			case 'lite':
				return liteModules;
			default:
				return sortedModules;
		}
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
			<div className="flex gap-6">
				{/* Main Content Area */}
				<div className="w-3/4">
					<div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						{/* Header */}
						<div className="flex items-center justify-between mb-6">
							<h2 className="font-bold text-3xl text-gray-900">
								{__('Modules', 'addons-for-divi')}
							</h2>
						</div>

						{/* Filter and Action Buttons */}
						<div className="flex items-center justify-between mb-8">
							{/* Filter Buttons */}
							<div className="flex items-center gap-3">
								{['all', 'pro', 'lite'].map((filterType) => (
									<button
										key={filterType}
										aria-label={__(
											filterType.charAt(0).toUpperCase() +
												filterType.slice(1),
											'addons-for-divi'
										)}
										type="button"
										className={`px-6 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ${
											filter === filterType
												? 'bg-de-app-color-dark text-white shadow-md'
												: 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'
										}`}
										onClick={() => setFilter(filterType)}
										disabled={isLoading}
									>
										{__(
											filterType.charAt(0).toUpperCase() +
												filterType.slice(1),
											'addons-for-divi'
										)}
										{filterType !== 'all' &&
											` (${
												filterType === 'pro'
													? proModules.length
													: liteModules.length
											})`}
									</button>
								))}
							</div>

							{/* Action Buttons */}
							<div className="flex items-center gap-3">
								<button
									aria-label={__(
										'Disable All',
										'addons-for-divi'
									)}
									type="button"
									className={`px-6 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 border ${
										allDisabled || isLoading
											? 'opacity-50 cursor-not-allowed bg-gray-100 border-gray-200 text-gray-400'
											: 'bg-white border-red-500 text-red-600 hover:bg-red-50 hover:border-red-600'
									}`}
									onClick={() => toggleModuleStatus(false)}
									disabled={
										allDisabled ||
										filter === 'pro' ||
										isLoading
									}
								>
									{isLoading
										? __('Processing...', 'addons-for-divi')
										: __('Disable All', 'addons-for-divi')}
								</button>
								<button
									aria-label={__(
										'Enable All',
										'addons-for-divi'
									)}
									type="button"
									className={`px-6 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ${
										allEnabled || isLoading
											? 'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500'
											: 'bg-de-app-color-dark text-white hover:bg-de-app-color shadow-md hover:shadow-lg'
									}`}
									onClick={() => toggleModuleStatus(true)}
									disabled={
										allEnabled ||
										filter === 'pro' ||
										isLoading
									}
								>
									{isLoading
										? __('Processing...', 'addons-for-divi')
										: __('Enable All', 'addons-for-divi')}
								</button>
							</div>
						</div>

						{/* Modules Grid */}
						<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
							{renderModules()}
						</div>
					</div>
				</div>

				{/* Sidebar */}
				<div className="w-1/4">
					<div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-10">
						{/* Header */}
						<div className="bg-gradient-to-r from-de-app-color-dark to-de-app-color px-6 py-8 text-white">
							{/* Summer Discount Banner */}
							<div className="bg-gradient-to-r from-orange-400 to-red-500 rounded-xl p-3 mb-6 text-center shadow-lg">
								<div className="flex items-center justify-center">
									<svg
										className="w-5 h-5 mr-2"
										fill="currentColor"
										viewBox="0 0 20 20"
									>
										<path
											fillRule="evenodd"
											d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"
											clipRule="evenodd"
										/>
									</svg>
									<span className="font-bold text-sm">
										{__(
											'SUMMER SALE - 40% OFF',
											'addons-for-divi'
										)}
									</span>
								</div>
								<div className="text-xs opacity-90 mt-1">
									{__(
										'Limited time offer',
										'addons-for-divi'
									)}
								</div>
							</div>

							<h2 className="text-xl font-bold text-white mb-2">
								{__('Divi Torque Pro', 'addons-for-divi')}
							</h2>
							<p className="text-base opacity-90 text-white leading-relaxed">
								{__(
									'Supercharge with 50+ PRO Divi Modules and 8+ Extensions',
									'addons-for-divi'
								)}
							</p>
						</div>

						{/* Features List */}
						<div className="p-6">
							<div className="space-y-4">
								{[
									__('50+ Pro Modules', 'addons-for-divi'),
									__('Popup Builder', 'addons-for-divi'),
									__('Megamenu Builder', 'addons-for-divi'),
									__('Maintenance Mode', 'addons-for-divi'),
									__('Dark Menu', 'addons-for-divi'),
									__('Google Reviews', 'addons-for-divi'),
									__('Instagram Feed', 'addons-for-divi'),
									__('Divi Duplicate', 'addons-for-divi'),
									__('Priority Support', 'addons-for-divi'),
								].map((feature, index) => (
									<div
										key={index}
										className="flex items-center"
									>
										<div className="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mr-3">
											<svg
												className="w-3 h-3 text-green-600"
												fill="currentColor"
												viewBox="0 0 20 20"
											>
												<path
													fillRule="evenodd"
													d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
													clipRule="evenodd"
												/>
											</svg>
										</div>
										<span className="text-sm text-gray-700">
											{feature}
										</span>
									</div>
								))}
							</div>

							{/* CTA Button */}
							<div className="mt-8">
								<a
									href="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
									className="block w-full bg-gradient-to-r from-de-app-color-dark to-de-app-color hover:from-de-app-color hover:to-de-app-color-dark text-white text-center py-4 px-6 rounded-xl font-medium transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
									target="_blank"
									rel="noopener noreferrer"
								>
									<div className="flex items-center justify-center">
										<svg
											className="w-5 h-5 mr-2"
											fill="none"
											stroke="currentColor"
											viewBox="0 0 24 24"
										>
											<path
												strokeLinecap="round"
												strokeLinejoin="round"
												strokeWidth={2}
												d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
											/>
										</svg>
										{__(
											'Upgrade to Pro',
											'addons-for-divi'
										)}
									</div>
								</a>
							</div>

							{/* Stats */}
							<div className="mt-6 grid grid-cols-2 gap-4">
								<div className="text-center p-4 bg-gray-50 rounded-lg">
									<div className="text-2xl font-bold text-de-app-color-dark">
										{__('50+', 'addons-for-divi')}
									</div>
									<div className="text-xs text-gray-600">
										{__('Pro Modules', 'addons-for-divi')}
									</div>
								</div>
								<div className="text-center p-4 bg-gray-50 rounded-lg">
									<div className="text-2xl font-bold text-de-app-color-dark">
										{__('8+', 'addons-for-divi')}
									</div>
									<div className="text-xs text-gray-600">
										{__('Extensions', 'addons-for-divi')}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default Modules;
