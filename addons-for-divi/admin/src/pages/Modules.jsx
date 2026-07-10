import { useMemo, useState } from 'react';
import {
    Button,
    EmptyState,
    FilterTabs,
    Notice,
    PageHeader,
    Popconfirm,
    RefreshIcon,
    SearchInput,
    SegmentedControl,
    SkeletonCard,
} from '@plugpress/ui';
import catalog from '../../../assets/module-catalog.json';
import { ModuleCard } from '../components/ModuleCard';
import { CATEGORIES, catalogEntry } from '../components/ModuleIcon';
import { UsageDrawer } from '../components/UsageDrawer';
import { useModules } from '../hooks/use-modules';
import { useModuleUsage } from '../hooks/use-module-usage';

const TIERS = [
    { value: 'all', label: 'All' },
    { value: 'free', label: 'Free' },
    { value: 'pro', label: 'Pro' },
];

export default function Modules() {
    const { modules, loading, error, isEnabled, toggle, setAll } = useModules();
    const { usage, scanning, rescan } = useModuleUsage();
    const [usageFor, setUsageFor] = useState(null);
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('all');
    const [tier, setTier] = useState('all');

    const categoryTabs = useMemo(
        () => [
            { value: 'all', label: 'All' },
            ...Object.entries(CATEGORIES).map(([value, c]) => ({ value, label: c.label })),
        ],
        []
    );

    // Real (toggleable) Lite modules + locked cards for the catalog's
    // pro-only modules — the strongest feature-discovery surface Lite has.
    const allCards = useMemo(() => {
        const have = new Set(modules.map((m) => m.catalog_slug || m.name));
        const locked = Object.entries(catalog.modules)
            .filter(([slug, entry]) => entry.pro && !have.has(slug))
            .map(([slug, entry]) => ({
                name: slug,
                catalog_slug: slug,
                title: entry.label,
                is_pro: true,
                locked: true,
            }));
        return [...modules.map((m) => ({ ...m, locked: false })), ...locked];
    }, [modules]);

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return allCards.filter((m) => {
            const entry = catalogEntry(m);
            if (category !== 'all' && entry?.cat !== category) {
                return false;
            }
            if (tier !== 'all' && (tier === 'pro') !== Boolean(m.is_pro)) {
                return false;
            }
            if (q) {
                const hay = `${m.title} ${m.name} ${entry?.desc || ''}`.toLowerCase();
                if (!hay.includes(q)) {
                    return false;
                }
            }
            return true;
        });
    }, [allCards, search, category, tier]);

    if (error) {
        return <Notice tone="danger">Could not load module settings. Refresh the page to try again.</Notice>;
    }

    return (
        <>
            <PageHeader
                title="Modules"
                description="Turn Divi Torque modules on or off. Disabled modules are removed from the Divi builder."
                actions={
                    <>
                        <Button variant="ghost" onClick={rescan} loading={scanning} title="Rescan module usage">
                            <RefreshIcon /> Usage
                        </Button>
                        <Popconfirm
                            trigger={<Button variant="secondary">Enable all</Button>}
                            title="Enable every module?"
                            confirmLabel="Enable all"
                            onConfirm={() => setAll(true)}
                        />
                        <Popconfirm
                            trigger={<Button variant="secondary">Disable all</Button>}
                            title="Disable every module?"
                            description="Pages using them will stop rendering those sections."
                            confirmLabel="Disable all"
                            danger
                            onConfirm={() => setAll(false)}
                        />
                    </>
                }
            />
            <div className="dt-modfilters">
                <SearchInput
                    placeholder="Search modules…"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    aria-label="Search modules"
                />
                <SegmentedControl options={TIERS} value={tier} onChange={setTier} aria-label="Filter by tier" />
            </div>
            <FilterTabs items={categoryTabs} value={category} onChange={setCategory} aria-label="Filter by category" />
            {loading ? (
                <div className="dt-modgrid">
                    {Array.from({ length: 9 }, (_, i) => (
                        <SkeletonCard key={i} />
                    ))}
                </div>
            ) : filtered.length ? (
                <div className="dt-modgrid">
                    {filtered.map((m) => (
                        <ModuleCard
                            key={m.name}
                            module={m}
                            enabled={!m.locked && isEnabled(m.name)}
                            onToggle={toggle}
                            usage={m.locked ? null : usage[m.name] || null}
                            onShowUsage={setUsageFor}
                            locked={m.locked}
                        />
                    ))}
                </div>
            ) : (
                <EmptyState
                    title="No modules match"
                    description="Try a different search or clear the filters."
                    actions={
                        <Button
                            variant="secondary"
                            onClick={() => {
                                setSearch('');
                                setCategory('all');
                                setTier('all');
                            }}
                        >
                            Clear filters
                        </Button>
                    }
                />
            )}
            <UsageDrawer
                module={usageFor}
                usage={usageFor ? usage[usageFor.name] : null}
                onClose={() => setUsageFor(null)}
            />
        </>
    );
}
