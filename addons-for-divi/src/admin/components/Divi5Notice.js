import React from 'react';
import { __ } from '@wordpress/i18n';

const Divi5Notice = () => {
	return (
		<div className="flex items-center justify-between gap-4 px-4 py-3 mb-6 bg-white border border-gray-200 rounded-lg">
			<div className="flex items-center gap-3 min-w-0">
				<span className="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-700 text-[11px] font-medium rounded">
					{__('Coming soon', 'addons-for-divi')}
				</span>
				<p className="text-sm text-gray-700 truncate">
					{__(
						'Divi 5 compatibility is in progress — vote for the modules you need most.',
						'addons-for-divi'
					)}
				</p>
			</div>
			<a
				href="https://divitorque.com/divi5-roadmap/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=divi5-roadmap"
				target="_blank"
				rel="noopener noreferrer"
				className="flex-shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-700"
			>
				{__('View roadmap →', 'addons-for-divi')}
			</a>
		</div>
	);
};

export default Divi5Notice;
