import { AppNav, Badge, useConfirm, toast, HomeIcon, GridIcon } from '@plugpress/ui';
import { appData, post } from '../api';
import { visibleTabs } from '../routes';
import { BrandMark } from './icons';

/**
 * Left rail — the DS AppNav with icons (same pattern as Pro/Waggle), trimmed
 * to Lite's real destinations. No locked upsell tabs: upgrading is one quiet
 * footer link.
 */
const ICONS = {
    dashboard: HomeIcon,
    modules: GridIcon,
};

const navItem = (t) => {
    const Icon = ICONS[t.value];
    return {
        value: t.value,
        label: t.label,
        icon: Icon ? <Icon /> : undefined,
    };
};

export function Sidebar({ tab, onNavigate }) {
    const confirm = useConfirm();

    const items = visibleTabs().map(navItem);

    const switchToLegacy = async () => {
        const ok = await confirm({
            title: 'Switch to the legacy dashboard?',
            description:
                'You can come back anytime — the legacy dashboard shows a "Try the new dashboard" banner. The legacy UI will be retired in an upcoming release.',
            confirmLabel: 'Switch to legacy',
        });
        if (!ok) {
            return;
        }
        try {
            await post('switch_dashboard_ui', { ui: 'legacy' });
            window.location.href = `${appData.adminUrl}admin.php?page=divitorque`;
        } catch (err) {
            toast.error(err?.message || 'Could not switch dashboards.');
        }
    };

    return (
        <AppNav
            brand={
                <>
                    <BrandMark size={24} />
                    <span className="dt-brand__name">Divi Torque</span>
                    {appData.version && <Badge>v{appData.version}</Badge>}
                </>
            }
            items={items}
            value={tab}
            onChange={onNavigate}
            footer={
                <div className="dt-nav__footer">
                    {appData.upgradeUrl && (
                        <a
                            className="dt-nav__footer-link"
                            href={appData.upgradeUrl}
                            target="_blank"
                            rel="noreferrer"
                        >
                            Upgrade to Pro ↗
                        </a>
                    )}
                    <a className="dt-nav__footer-link" href={appData.docsUrl} target="_blank" rel="noreferrer">
                        Docs ↗
                    </a>
                    {appData.rollbackUrl && (
                        <a className="dt-nav__footer-link" href={appData.rollbackUrl}>
                            Rollback
                        </a>
                    )}
                    <button type="button" className="dt-nav__footer-link dt-nav__legacy" onClick={switchToLegacy}>
                        Switch to legacy dashboard
                    </button>
                </div>
            }
        />
    );
}
