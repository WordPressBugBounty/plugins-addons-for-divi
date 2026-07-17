/**
 * Route table — Lite variant. Same shape as Pro's (hash tabs + WP `?path=`
 * bridge + sidebar nav map), trimmed to the destinations Lite actually has —
 * no locked upsell tabs. Upgrading is a single quiet link in the sidebar
 * footer instead.
 */
export const TABS = [
    { value: 'dashboard', path: '', group: null, label: 'Dashboard' },
    { value: 'modules', path: 'module-manager', group: null, label: 'Modules' },
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
