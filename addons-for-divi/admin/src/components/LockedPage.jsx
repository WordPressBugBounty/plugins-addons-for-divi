import { Button, FeatureComparisonTable, PageHeader, SectionTitle, UpgradeCard } from '@plugpress/ui';
import { appData } from '../api';
import { COMPARISON, LOCKED_COPY } from '../data/free-vs-pro';

/** Upsell page for a Pro-only feature (sidebar parity with Pro's v2). */
export function LockedPage({ tab }) {
    const copy = LOCKED_COPY[tab] || LOCKED_COPY.settings;
    const upgradeUrl = `${appData.upgradeUrl}${appData.upgradeUrl?.includes('?') ? '&' : '?'}utm_content=${tab}`;

    return (
        <>
            <PageHeader title={copy.title} description="Included in Divi Torque Pro." />
            <UpgradeCard
                eyebrow="PRO"
                title={copy.title}
                description={copy.description}
                features={copy.features}
                action={
                    <Button href={upgradeUrl} target="_blank" rel="noreferrer">
                        Upgrade to Pro
                    </Button>
                }
            />
            <SectionTitle>Lite vs Pro</SectionTitle>
            <FeatureComparisonTable
                columns={COMPARISON.columns}
                features={COMPARISON.features}
                highlightColumn={COMPARISON.highlightColumn}
            />
        </>
    );
}
