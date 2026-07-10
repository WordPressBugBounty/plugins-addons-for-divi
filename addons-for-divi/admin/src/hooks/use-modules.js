import { useCallback, useEffect, useMemo, useState } from 'react';
import { toast } from '@plugpress/ui';
import { appData, get, post } from '../api';

const DISABLED = 'disabled';

/**
 * Module list + on/off statuses over the existing endpoints:
 * GET get_common_settings → { modules_settings }, POST save_common_settings.
 * Toggles save instantly (optimistic, rolled back on error) — same statuses
 * option (_divitorque_modules) the builder loaders read.
 */
export function useModules() {
    const info = useMemo(() => Object.values(appData.moduleInfo || {}), []);
    const [statuses, setStatuses] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        let alive = true;
        get('get_common_settings')
            .then((res) => alive && setStatuses(res?.modules_settings || {}))
            .catch((err) => alive && setError(err));
        return () => {
            alive = false;
        };
    }, []);

    const save = useCallback(
        async (next, prev, okMessage) => {
            setStatuses(next);
            try {
                await post('save_common_settings', { modules_settings: next });
                toast.success(okMessage);
            } catch (err) {
                setStatuses(prev);
                toast.error(err?.message || 'Could not save module settings.');
            }
        },
        []
    );

    const isEnabled = useCallback(
        (name) => Boolean(statuses) && statuses[name] !== DISABLED,
        [statuses]
    );

    const toggle = useCallback(
        (module, on) => {
            const prev = statuses;
            const next = { ...statuses, [module.name]: on ? module.name : DISABLED };
            if (module.child_name) {
                next[module.child_name] = on ? module.child_name : DISABLED;
            }
            save(next, prev, `${module.title} ${on ? 'enabled' : 'disabled'}.`);
        },
        [statuses, save]
    );

    const setAll = useCallback(
        (on) => {
            const prev = statuses;
            const next = { ...statuses };
            for (const m of info) {
                next[m.name] = on ? m.name : DISABLED;
                if (m.child_name) {
                    next[m.child_name] = on ? m.child_name : DISABLED;
                }
            }
            save(next, prev, on ? 'All modules enabled.' : 'All modules disabled.');
        },
        [statuses, info, save]
    );

    return { modules: info, statuses, loading: statuses === null && !error, error, isEnabled, toggle, setAll };
}
