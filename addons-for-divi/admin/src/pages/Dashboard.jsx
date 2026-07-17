import { useEffect, useState } from 'react';
import {
    CalloutCard,
    CardGrid,
    InfoCard,
    PageHeader,
    StatCard,
    StatGrid,
    BookOpenIcon,
    GridIcon,
    LifeBuoyIcon,
    SparklesIcon,
} from '@plugpress/ui';
import { appData, get } from '../api';
import { useModuleUsage } from '../hooks/use-module-usage';

/** Fail-soft count fetcher — a dead endpoint shows "—", never breaks Home. */
function useCount(fetcher, deps = []) {
    const [count, setCount] = useState(null);
    useEffect(() => {
        let alive = true;
        fetcher()
            .then((n) => alive && setCount(n))
            .catch(() => alive && setCount(null));
        return () => {
            alive = false;
        };
    }, deps); // eslint-disable-line react-hooks/exhaustive-deps
    return count;
}

const dash = (n) => (n == null ? '—' : n);

export default function Dashboard({ navigate }) {
    const moduleTotal = Object.keys(appData.moduleInfo || {}).length;
    const modulesEnabled = useCount(() =>
        get('get_common_settings').then((res) => {
            const s = res?.modules_settings || {};
            return Object.values(appData.moduleInfo || {}).filter((m) => s[m.name] !== 'disabled').length;
        })
    );
    // Cached hourly server-side. The scan is cursor-resumed and may need
    // several polls to finish — useModuleUsage pumps until complete, and we
    // show "—" (not a misleading partial count) until it is.
    const { usage, complete } = useModuleUsage();
    const modulesInUse = complete ? Object.keys(usage).length : null;

    return (
        <>
            <PageHeader title="Dashboard" description="Everything Divi Torque adds to this site, at a glance." />
            <StatGrid columns={3}>
                <StatCard icon={<GridIcon />} label="Modules enabled" value={`${dash(modulesEnabled)} / ${moduleTotal}`} />
                <StatCard icon={<SparklesIcon />} label="Modules in use" value={dash(modulesInUse)} />
                <StatCard icon={<BookOpenIcon />} label="Version" value={`v${appData.version || '—'}`} />
            </StatGrid>
            <CardGrid min={220}>
                <InfoCard
                    icon={<GridIcon />}
                    title="Modules"
                    description="Turn modules on or off and see where each one is used."
                    onClick={() => navigate('modules')}
                    role="button"
                    tabIndex={0}
                />
                <InfoCard
                    icon={<BookOpenIcon />}
                    title="Documentation"
                    description="Guides and how-tos for every module."
                    href={appData.docsUrl}
                    external
                />
            </CardGrid>
            <div className="dt-callouts">
                <CalloutCard
                    icon={<LifeBuoyIcon />}
                    title="Support"
                    description="Stuck on something? We answer fast."
                    action={
                        <a href="https://divitorque.com/support/" target="_blank" rel="noreferrer">
                            Get support ↗
                        </a>
                    }
                />
                <CalloutCard
                    icon={<SparklesIcon />}
                    title="What's new"
                    description="See what shipped in the latest release."
                    action={
                        <a href="https://divitorque.com/changelog/" target="_blank" rel="noreferrer">
                            Changelog ↗
                        </a>
                    }
                />
            </div>
        </>
    );
}
