import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import Admin from './admin/index';

// Function to setup the DiviEpic admin component
function setupDiviEpicAdmin() {
	const rootElement = document.getElementById('diviepic-root');
	if (!rootElement) return;

	const DiviEpic = () => (
		<div className="diviepic-admin">
			<Admin />
		</div>
	);

	render(<DiviEpic />, rootElement);
}

domReady(() => {
	setupDiviEpicAdmin();
});
