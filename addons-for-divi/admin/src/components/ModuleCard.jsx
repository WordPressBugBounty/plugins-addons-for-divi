import { Badge, Switch, Tooltip } from '@plugpress/ui';
import { ModuleIcon, catalogEntry } from './ModuleIcon';

/**
 * One module in the manager grid: mesh icon, title, on/off switch, and a meta
 * row with usage count + demo/docs links from the site catalog.
 */
export function ModuleCard({ module, enabled, onToggle, usage, onShowUsage, locked = false }) {
    const entry = catalogEntry(module);
    const utm = '?utm_source=wp-admin&utm_medium=module-manager&utm_campaign=divi-torque';

    return (
        <div className={`dt-modcard${locked ? ' dt-modcard--locked' : ''}`}>
            <div className="dt-modcard__main">
                <ModuleIcon module={module} />
                <div className="dt-modcard__text">
                    <span className="dt-modcard__title">
                        {module.title}
                        {locked && <Badge tone="neutral">Pro</Badge>}
                    </span>
                    {entry?.desc && <span className="dt-modcard__desc">{entry.desc}</span>}
                </div>
                {locked ? (
                    <Tooltip content="Included in Divi Torque Pro">
                        <span>
                            <Switch checked={false} disabled aria-label={`${module.title} (Pro only)`} />
                        </span>
                    </Tooltip>
                ) : (
                    <Switch
                        checked={enabled}
                        onChange={(next) => onToggle(module, next)}
                        aria-label={`Enable ${module.title}`}
                    />
                )}
            </div>
            <div className="dt-modcard__meta">
                {usage != null && usage.count > 0 && (
                    <button type="button" className="dt-modcard__usage" onClick={() => onShowUsage(module)}>
                        <Badge tone="info">{usage.count === 1 ? 'Used on 1 page' : `Used on ${usage.count} pages`}</Badge>
                    </button>
                )}
                <span className="dt-modcard__links">
                    {entry?.demo_url && (
                        <a href={entry.demo_url + utm} target="_blank" rel="noreferrer">
                            Demo ↗
                        </a>
                    )}
                    {entry?.docs_url && (
                        <a href={entry.docs_url + utm} target="_blank" rel="noreferrer">
                            Docs ↗
                        </a>
                    )}
                </span>
            </div>
        </div>
    );
}
