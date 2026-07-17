import { useCallback, useEffect, useState } from 'react';
import { AppShell, AppContent, WIDTH } from '@plugpress/ui';
import { Sidebar } from './components/Sidebar';
import { ForeignNotices } from './components/ForeignNotices';
import { initialRoute, parseHash, syncQueryArg } from './routes';
import Dashboard from './pages/Dashboard';
import Modules from './pages/Modules';

const PAGES = {
    dashboard: { component: Dashboard, width: WIDTH.wide },
    modules: { component: Modules, width: WIDTH.wide },
};

export default function App() {
    const [route, setRoute] = useState(initialRoute);

    useEffect(() => {
        const onHash = () => {
            const next = parseHash();
            if (next.tab) {
                setRoute(next);
            } else {
                // Unknown/removed tab (stale bookmark like #/popups): normalize
                // the hash to dashboard instead of leaving URL and view desynced.
                window.location.hash = 'dashboard';
            }
        };
        window.addEventListener('hashchange', onHash);
        return () => window.removeEventListener('hashchange', onHash);
    }, []);

    useEffect(() => {
        syncQueryArg(route.tab);
    }, [route.tab]);

    const navigate = useCallback((tab, sub = '') => {
        window.location.hash = sub ? `${tab}/${sub}` : tab;
    }, []);

    const page = PAGES[route.tab] || PAGES.dashboard;
    const Page = page.component;

    return (
        <AppShell variant="sidebar" nav={<Sidebar tab={route.tab} onNavigate={navigate} />}>
            <AppContent width={page.width || WIDTH.wide}>
                <ForeignNotices />
                <Page sub={route.sub} navigate={navigate} />
            </AppContent>
        </AppShell>
    );
}
