/**
 * Shared helpers for the Divi Torque Lite admin app (dashboard v2).
 */
import apiFetch from '@wordpress/api-fetch';

export const appData = window.divitorqueData || {};

// Build a namespaced REST path, e.g. ns('get_common_settings').
export const ns = (path) => `${appData.ns || 'divitorque-lite/v1'}/${path}`;

// apiFetch against a namespaced Divi Torque Lite route.
export const api = (path, options = {}) => apiFetch({ path: ns(path), ...options });

export const get = (path) => api(path);
export const post = (path, data) => api(path, { method: 'POST', data });
export const del = (path, data) => api(path, { method: 'DELETE', data });
