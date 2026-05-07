// External Dependencies
import $ from 'jquery';

// Internal Dependencies
import modules from 'src/divi-4/modules/index.js';

$(window).on('et_builder_api_ready', (event, API) => {
	API.registerModules(modules);
});
