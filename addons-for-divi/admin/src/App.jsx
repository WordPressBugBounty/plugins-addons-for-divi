import { useCallback, useEffect, useState } from 'react';
import { AppShell, AppContent, WIDTH } from '@plugpress/ui';
import { Sidebar } from './components/Sidebar';
import { ForeignNotices } from './components/ForeignNotices';
import { LockedPage } from './components/LockedPage';
import { initialRoute, parseHash, syncQueryArg, TABS } from './routes';
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

    const tabDef = TABS.find((t) => t.value === route.tab);
    const page = PAGES[route.tab];

    return (
        <AppShell variant="sidebar" nav={<Sidebar tab={route.tab} onNavigate={navigate} />}>
            <AppContent width={tabDef?.locked ? WIDTH.content : page?.width || WIDTH.wide}>
                <ForeignNotices />
                {tabDef?.locked ? (
                    <LockedPage tab={route.tab} />
                ) : (
                    (() => {
                        const Page = (page || PAGES.dashboard).component;
                        return <Page sub={route.sub} navigate={navigate} />;
                    })()
                )}
            </AppContent>
        </AppShell>
    );
}
