/**
 * React entry point for the Divi Torque admin UI (dashboard v2).
 */
import apiFetch from '@wordpress/api-fetch';
import { createRoot } from 'react-dom/client';
import { TooltipProvider, ConfirmProvider, Toaster } from '@plugpress/ui';
import App from './App';

// PlugPress design system: shared tokens + components, the Divi Torque blue
// accent, then our own styles (which alias the --pp-* tokens).
import '@plugpress/ui/ui.css';
import '@plugpress/ui/tokens/accents/divitorque.css';
import './style.scss';

const data = window.divitorqueData || {};

if (data.nonce) {
    apiFetch.use(apiFetch.createNonceMiddleware(data.nonce));
}
if (data.root) {
    apiFetch.use(apiFetch.createRootURLMiddleware(data.root));
}

const mount = () => {
    const el = document.getElementById('divitorque-root');
    if (!el) {
        return;
    }
    createRoot(el).render(
        <TooltipProvider>
            <ConfirmProvider>
                <div className="pp-app divitorque-app">
                    <Toaster />
                    <App />
                </div>
            </ConfirmProvider>
        </TooltipProvider>
    );
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
} else {
    mount();
}
