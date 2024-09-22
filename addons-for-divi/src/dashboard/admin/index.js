import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { Logo, Header, PluginCard } from '@DashboardComponents';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const Admin = () => {
	const [isLoading, setIsLoading] = useState(true);
	const [sortedPlugins, setSortedPlugins] = useState([]);

	useEffect(() => {
		const timeout = setTimeout(() => {
			const plugins = window.diviEpic.plugins || [];

			const allPlugins = plugins.filter(
				(plugin) =>
					!window.diviepic?.active_plugins?.includes(plugin.slug)
			);

			setSortedPlugins([...allPlugins]);
			setIsLoading(false);
		}, 2000);

		return () => clearTimeout(timeout);
	}, []);

	return (
		<div className="diviepic-app">
			<Header>
				<div className="flex-shrink-0 flex items-center justify-start gap-1">
					<Logo />{' '}
					<span className="font-bold text-base text-de-black">
						{__('DiviEpic', 'addons-for-divi')}
					</span>
				</div>
			</Header>
			<div className="px-8 mx-auto lg:max-w-[80rem] mt-12 flex items-center flex-col justify-center text-center">
				<h2 className="font-semibold text-2xl flex-1">
					{__(
						'Powerful Add-ons, New Possibilities.',
						'addons-for-divi'
					)}
				</h2>
			</div>
			<div className="p-8">
				{isLoading ? (
					<p className="p-8 text-center animate-[pulse_2s_ease-in-out_infinite] text-lg text-de-black">
						{__('Loading...', 'addons-for-divi')}
					</p>
				) : sortedPlugins.length > 0 ? (
					<div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
						{sortedPlugins.map((plugin) => (
							<PluginCard key={plugin.slug} plugin={plugin} />
						))}
					</div>
				) : (
					<p>{__('No plugins found.', 'addons-for-divi')}</p>
				)}
			</div>
			<ToastContainer />
		</div>
	);
};

export default Admin;
