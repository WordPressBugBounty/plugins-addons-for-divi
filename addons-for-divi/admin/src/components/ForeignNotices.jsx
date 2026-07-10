import { useEffect, useRef, useState } from 'react';
import { Collapsible } from '@plugpress/ui';

/**
 * Other plugins' admin notices, quarantined by PHP into a hidden
 * #dtl-foreign-notices container so they can't shove our app around.
 * Surfaces them behind a quiet disclosure (Saddle's pattern).
 */
export function ForeignNotices() {
    const hostRef = useRef(null);
    const [count, setCount] = useState(0);

    useEffect(() => {
        const stash = document.getElementById('dtl-foreign-notices');
        if (!stash || !hostRef.current) {
            return;
        }
        const notices = stash.querySelectorAll('.notice, .updated, .error, .update-nag');
        if (!notices.length) {
            return;
        }
        setCount(notices.length);
        hostRef.current.appendChild(stash);
        stash.style.display = 'block';
    }, []);

    return (
        <div className="dt-foreign" style={{ display: count ? 'block' : 'none' }}>
            <Collapsible trigger={`Notices from other plugins (${count})`} defaultOpen={false}>
                <div ref={hostRef} className="dt-foreign__list" />
            </Collapsible>
        </div>
    );
}
