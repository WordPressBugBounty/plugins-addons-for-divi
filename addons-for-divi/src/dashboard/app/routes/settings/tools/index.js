import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

import { default as VersionControl } from './version-control';

const Tools = () => {
	return (
		<div className="block border-b border-solid border-slate-200 px-12 py-8 justify-between">
			<VersionControl />
		</div>
	);
};

export default Tools;
