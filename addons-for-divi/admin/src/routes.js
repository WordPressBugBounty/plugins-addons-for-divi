/**
 * Route table — Lite variant. Same shape as Pro's (hash tabs + WP `?path=`
 * bridge + sidebar nav map), but the Pro-only features render locked
 * upsell pages and there is no Labs.
 */
export const TABS = [
    { value: 'dashboard', path: '', group: null, label: 'Dashboard' },
    { value: 'modules', path: 'module-manager', group: null, label: 'Modules' },
    { value: 'popups', path: 'popups', group: 'features', label: 'Popups', locked: true },
    { value: 'submissions', path: 'form-submissions', group: 'features', label: 'Submissions', locked: true },
    { value: 'google-reviews', path: 'google-reviews', group: 'features', label: 'Google Reviews', locked: true },
    { value: 'mailer', path: 'divi-mailer', group: 'features', label: 'Mailer', locked: true },
    { value: 'dark-mode', path: 'dark-mode', group: 'features', label: 'Dark Mode', locked: true },
    { value: 'ai-connection', path: 'ai-connection', group: 'features', label: 'AI Connection', locked: true },
    { value: 'settings', path: 'settings', group: 'bottom', label: 'Settings', locked: true },
];

const byPath = Object.fromEntries(TABS.filter((t) => t.path !== null).map((t) => [t.path, t.value]));
const byValue = Object.fromEntries(TABS.map((t) => [t.value, t]));

export const visibleTabs = () => TABS;

export const parseHash = () => {
    const raw = window.location.hash.replace(/^#\/?/, '');
    const [tab, sub = ''] = raw.split('/');
    return byValue[tab] ? { tab, sub } : { tab: null, sub: '' };
};

/** Initial route: hash wins; else translate the WP submenu `?path=` arg. */
export const initialRoute = () => {
    const fromHash = parseHash();
    if (fromHash.tab) {
        return fromHash;
    }
    const params = new URLSearchParams(window.location.search);
    const tab = byPath[params.get('path') || ''];
    return { tab: tab || 'dashboard', sub: '' };
};

/** Keep `?path=` in sync so refresh restores the view + WP submenu highlight. */
export const syncQueryArg = (tab) => {
    const t = byValue[tab];
    if (!t || t.path === null) {
        return;
    }
    const params = new URLSearchParams(window.location.search);
    if ((params.get('path') || '') === t.path) {
        return;
    }
    if (t.path === '') {
        params.delete('path');
    } else {
        params.set('path', t.path);
    }
    const query = params.toString();
    window.history.replaceState(null, '', `${window.location.pathname}?${query}${window.location.hash}`);
};
