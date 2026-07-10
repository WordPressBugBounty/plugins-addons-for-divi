import { Badge, Button, useConfirm, toast } from '@plugpress/ui';
import { appData, post } from '../api';
import { visibleTabs } from '../routes';
import { BrandMark } from './icons';

/**
 * Left rail — AppNav is flat (no groups), so this composes the same pp-nav
 * classes with a FEATURES group heading, plus the footer links (Account,
 * Docs, switch-to-legacy).
 */
export function Sidebar({ tab, onNavigate }) {
    const confirm = useConfirm();

    const item = (t) => {
        if (t.external) {
            return (
                <a key={t.value} className="pp-nav__item" href={t.external()}>
                    <span className="pp-nav__label">{t.label}</span>
                </a>
            );
        }
        return (
            <button
                key={t.value}
                type="button"
                className="pp-nav__item"
                aria-current={tab === t.value ? 'page' : undefined}
                onClick={() => onNavigate(t.value)}
            >
                <span className="pp-nav__label">{t.label}</span>
            </button>
        );
    };

    const tabs = visibleTabs();
    const top = tabs.filter((t) => t.group === null);
    const features = tabs.filter((t) => t.group === 'features');
    const bottom = tabs.filter((t) => t.group === 'bottom');

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
        <nav className="pp-nav" aria-label="Main">
            <div className="pp-nav__brand">
                <BrandMark size={24} />
                <span className="dt-brand__name">Divi Torque</span>
                {appData.version && <Badge>v{appData.version}</Badge>}
            </div>
            <div className="pp-nav__items">
                {top.map(item)}
                <div className="dt-nav__group" aria-hidden="true">
                    Features
                </div>
                {features.map(item)}
                <div className="dt-nav__group" aria-hidden="true" />
                {bottom.map(item)}
            </div>
            <div className="pp-nav__footer dt-nav__footer">
                {appData.isLite ? (
                    <Button
                        variant="primary"
                        className="dt-nav__upgrade"
                        href={appData.upgradeUrl}
                        target="_blank"
                        rel="noreferrer"
                    >
                        Upgrade to Pro
                    </Button>
                ) : (
                    appData.accountUrl && (
                        <a className="dt-nav__footer-link" href={appData.accountUrl}>
                            Account
                        </a>
                    )
                )}
                <a className="dt-nav__footer-link" href={appData.docsUrl} target="_blank" rel="noreferrer">
                    Docs ↗
                </a>
                {appData.isLite && appData.rollbackUrl && (
                    <a className="dt-nav__footer-link" href={appData.rollbackUrl}>
                        Rollback
                    </a>
                )}
                <button type="button" className="dt-nav__footer-link dt-nav__legacy" onClick={switchToLegacy}>
                    Switch to legacy dashboard
                </button>
            </div>
        </nav>
    );
}
