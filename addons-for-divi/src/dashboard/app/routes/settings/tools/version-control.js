import React, { useState } from 'react';
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const VersionControl = () => {
	const currentVersion = window.diviTorqueLite?.currentVersion || '';
	const rollbackVersions = window.diviTorqueLite?.rollbackVersions || [];
	const [selectedVersion, setSelectedVersion] = useState();
	const [rollbackLoading, setRollbackLoading] = useState(false);
	const [isOpen, setOpen] = useState(false);

	let rollbackLink = window.diviTorqueLite?.rollbackLink;

	if (!selectedVersion) {
		setSelectedVersion(Object.keys(rollbackVersions)[0]);
	}

	const openModal = () => setOpen(true);
	const closeModal = () => setOpen(false);

	if ('undefined' !== typeof selectedVersion) {
		const url = new URL(rollbackLink);
		const searchParams = new URLSearchParams(url.search);
		searchParams.set('version', selectedVersion);
		url.search = searchParams.toString();
		const newUrl = url.toString();

		rollbackLink = newUrl;
	}

	const handleRollback = () => () => {
		setRollbackLoading(true);
		setTimeout(
			() => {
				window.location
					? (window.location.href = rollbackLink)
					: console.log('No window.location');
			},

			3000
		);

		closeModal();
	};

	return (
		<div className="flex items-center justify-between">
			<div className="flex flex-col gap-4 pr-8">
				<h2 className="text-xl font-semibold">
					{__('Rollback to Previous Version', 'addons-for-divi')}
				</h2>
				<p className="text-sm text-de-semidark-gray">
					{__(
						`Experiencing an issue with Divi Torque Lite v${currentVersion}? Roll back to a previous version to help troubleshoot the issue.`,
						'addons-for-divi'
					)}
				</p>
			</div>

			<div className="flex items-center gap-4">
				<select
					className="border border-solid border-slate-200 rounded-md px-4 py-2 lowercase"
					value={selectedVersion}
					onChange={(e) => setSelectedVersion(e.target.value)}
				>
					{Object.keys(rollbackVersions).map((version) => {
						return (
							<option key={version} value={version}>
								{version}
							</option>
						);
					})}
				</select>
				<button
					className="bg-de-app-color text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:bg-de-app-color-dark focus:ring-opacity-50 flex items-center gap-1"
					onClick={openModal}
				>
					{rollbackLoading && (
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							stroke-linecap="round"
							stroke-linejoin="round"
							className="animate-spin h-5 w-5 mr-3"
						>
							<polyline points="23 4 23 10 17 10"></polyline>
							<polyline points="1 20 1 14 7 14"></polyline>
							<path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
						</svg>
					)}
					{__('Rollback', 'addons-for-divi')}
				</button>

				{isOpen && (
					<Modal
						title={__(
							'Rollback to Previous Version',
							'addons-for-divi'
						)}
						onRequestClose={closeModal}
					>
						<div className="mb-5">
							<p>
								{__(
									`Are you sure you want to rollback to Divi Torque Lite v${selectedVersion}?`,
									'addons-for-divi'
								)}
							</p>
						</div>
						<Button
							onClick={handleRollback()}
							className="bg-red-600 text-white hover:text-[#e2e5ed] hover:bg-red-500 px-4 py-2 rounded-md"
						>
							{__('Rollback', 'addons-for-divi')}
						</Button>

						<Button
							onClick={closeModal}
							className="border border-de-light-gray border-solid text-de-black hover:text-[#354559] hover:bg-de-light-gray px-4 py-2 ml-2 rounded-md"
						>
							{__('Cancel', 'addons-for-divi')}
						</Button>
					</Modal>
				)}
			</div>
		</div>
	);
};

export default VersionControl;
