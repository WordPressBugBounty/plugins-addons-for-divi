/**
 * Assigns every Divi Torque module to the branded "Divi Torque" inserter
 * folder (registered in folder-register.js).
 *
 * Uses the `divi.moduleLibrary.moduleMapping` filter — must load BEFORE the
 * footer (deps: lodash + divi-vendor-wp-hooks) so the filter fires before the
 * modules are registered. Every `divitorque/*` module gets its
 * `metadata.folder` set to the folder slug.
 */
( function () {
	if ( ! ( window.vendor && window.vendor.wp && window.vendor.wp.hooks ) ) {
		// eslint-disable-next-line no-console
		console.error( '[DTL] folder-assign: window.vendor.wp.hooks unavailable' );
		return;
	}

	window.vendor.wp.hooks.addFilter(
		'divi.moduleLibrary.moduleMapping',
		'diviTorqueLite/folder',
		function ( modules ) {
			if ( ! modules ) {
				return modules;
			}
			Object.keys( modules ).forEach( function ( name ) {
				if ( name.indexOf( 'divitorque/' ) === 0 && modules[ name ] && modules[ name ].metadata ) {
					modules[ name ].metadata.folder = 'divi-torque';
				}
			} );
			return modules;
		}
	);
}() );
