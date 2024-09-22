import { combineReducers, createReduxStore, register } from '@wordpress/data';
import {
	modulesReducer,
	modulesActions,
	modulesResolvers,
	modulesControls,
	modulesSelectors,
} from './modules';

const STORE_NAME = 'divitorque-lite/dashboard';

const createStore = (initialState) => {
	return createReduxStore(STORE_NAME, {
		actions: {
			...modulesActions,
		},
		selectors: {
			...modulesSelectors,
		},
		reducer: combineReducers({
			modulesReducer,
		}),
		resolvers: {
			...modulesResolvers,
		},
		controls: {
			...modulesControls,
		},
	});
};

const registerStore = ({ initialState = {} } = {}) => {
	register(createStore({ initialState }));
};

export default registerStore;
