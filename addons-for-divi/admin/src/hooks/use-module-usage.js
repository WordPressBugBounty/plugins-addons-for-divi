import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from '@plugpress/ui';
import { get, post } from '../api';

/**
 * Per-module page-usage data. The PHP scan is time-budgeted and may return
 * partial results with a cursor; keep polling GET until complete.
 */
export function useModuleUsage() {
    const [state, setState] = useState(null);
    const [scanning, setScanning] = useState(false);
    const alive = useRef(true);

    useEffect(() => {
        alive.current = true;
        return () => {
            alive.current = false;
        };
    }, []);

    const pump = useCallback(async (first) => {
        setScanning(true);
        try {
            let res = first ? await first : await get('module_usage');
            while (alive.current && res && !res.complete) {
                setState(res);
                res = await get('module_usage');
            }
            if (alive.current && res) {
                setState(res);
            }
        } catch {
            // usage is decoration on the Modules page — fail quiet
        } finally {
            if (alive.current) {
                setScanning(false);
            }
        }
    }, []);

    useEffect(() => {
        pump();
    }, [pump]);

    const rescan = useCallback(() => {
        toast.message('Rescanning module usage…');
        pump(post('module_usage/rescan'));
    }, [pump]);

    return {
        usage: state?.modules || {},
        scannedAt: state?.scanned_at || null,
        complete: Boolean(state?.complete),
        scanning,
        rescan,
    };
}
