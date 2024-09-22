import { Route, Routes } from 'react-router-dom';

import { default as Modules } from './module-manager';
import { default as Upsell } from './upsell';
import { default as Settings } from './settings';
import { default as Tools } from './settings/tools';

const AppRoutes = () => {
	return (
		<Routes>
			<Route path="/" element={<Modules />} />
			<Route path="/module-manager" element={<Modules />} />
			<Route path="/get-pro" element={<Upsell />} />
			<Route path="/settings" element={<Settings />}>
				<Route path="tools" element={<Tools />} />
			</Route>
		</Routes>
	);
};

export default AppRoutes;
