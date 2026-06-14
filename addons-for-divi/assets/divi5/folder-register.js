/**
 * Registers a branded "Divi Torque" folder in the Divi 5 module inserter.
 *
 * Loads in the footer (after `divi-module-library` initialises) so
 * `window.divi.moduleLibrary.registerFolder` is available. The folder name
 * here MUST match the `folder` value set in folder-assign.js.
 */
( function () {
	if ( window.divi && window.divi.moduleLibrary && window.divi.moduleLibrary.registerFolder ) {
		window.divi.moduleLibrary.registerFolder( {
			name: 'divi-torque',
			path: '',
			title: 'Divi Torque',
			icon: '',
			category: 'module',
		} );
	} else {
		// eslint-disable-next-line no-console
		console.error( '[DTL] folder-register: window.divi.moduleLibrary.registerFolder unavailable' );
	}
}() );
