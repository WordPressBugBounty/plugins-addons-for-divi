import { useEffect, useState } from 'react';
import {
    Button,
    CalloutCard,
    CardGrid,
    Hero,
    InfoCard,
    PageHeader,
    StatCard,
    StatGrid,
    BookOpenIcon,
    GridIcon,
    LifeBuoyIcon,
    RocketIcon,
    SparklesIcon,
} from '@plugpress/ui';
import catalog from '../../../assets/module-catalog.json';
import { appData, get } from '../api';

const dash = (n) => (n == null ? '—' : n);

export default function Dashboard({ navigate }) {
    const moduleTotal = Object.keys(appData.moduleInfo || {}).length;
    const proAvailable = Object.values(catalog.modules).filter((m) => m.pro).length;
    const [enabled, setEnabled] = useState(null);

    useEffect(() => {
        let alive = true;
        get('get_common_settings')
            .then((res) => {
                if (!alive) {
                    return;
                }
                const s = res?.modules_settings || {};
                setEnabled(
                    Object.values(appData.moduleInfo || {}).filter((m) => s[m.name] !== 'disabled').length
                );
            })
            .catch(() => {});
        return () => {
            alive = false;
        };
    }, []);

    return (
        <>
            <PageHeader title="Dashboard" description="Everything Divi Torque adds to this site, at a glance." />
            <Hero
                eyebrow={`Divi Torque Lite v${appData.version || ''}`}
                title="Welcome back"
                description="Manage your free modules here — and see everything Pro adds."
                actions={
                    <Button href={appData.upgradeUrl} target="_blank" rel="noreferrer">
                        Upgrade to Pro
                    </Button>
                }
            />
            <StatGrid columns={3}>
                <StatCard icon={<GridIcon />} label="Modules enabled" value={`${dash(enabled)} / ${moduleTotal}`} />
                <StatCard icon={<SparklesIcon />} label="Pro modules available" value={proAvailable} />
                <StatCard icon={<RocketIcon />} label="Pro features" value="Popups · Reviews · Dark Mode +" />
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
                    icon={<SparklesIcon />}
                    title="AI Connection"
                    description="See how Pro connects AI agents like Claude Code."
                    onClick={() => navigate('ai-connection')}
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
                    icon={<RocketIcon />}
                    title="Unlock the full toolkit"
                    description={`${proAvailable} more modules plus Popups, Submissions, Google Reviews, Mega Menu, Dark Mode, and SMTP.`}
                    tone="accent"
                    action={
                        <a href={appData.upgradeUrl} target="_blank" rel="noreferrer">
                            See pricing ↗
                        </a>
                    }
                />
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
            </div>
        </>
    );
}
