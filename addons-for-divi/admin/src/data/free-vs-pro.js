/**
 * Lite vs Pro comparison rows (FeatureComparisonTable shape) and the
 * per-feature upsell bullets for LockedPage. Counts follow the synced
 * module catalog (assets/module-catalog.json): 34 free / 38 pro modules.
 */
export const COMPARISON = {
    columns: ['Lite', 'Pro'],
    highlightColumn: 1,
    features: [
        { name: '34 free Divi modules', values: [true, true] },
        { name: '38 additional pro modules', values: [false, true] },
        { name: 'Popup Maker — scroll, exit, time & click triggers', values: [false, true] },
        { name: 'Form submissions — store, browse & export Divi form entries', values: [false, true] },
        { name: 'Google Reviews — live reviews on your site', values: [false, true] },
        { name: 'Mega Menu builder', values: [false, true] },
        { name: 'Dark Mode for visitors', values: [false, true] },
        { name: 'SMTP Mailer — reliable email delivery', values: [false, true] },
        { name: 'Site visibility — coming soon & maintenance pages', values: [false, true] },
        { name: 'AI Connection — WordPress Abilities + MCP for AI agents', values: [false, true] },
        { name: 'Priority support', values: ['Community', 'Priority'] },
    ],
};

export const LOCKED_COPY = {
    popups: {
        title: 'Popups',
        description: 'Build popups that fire on scroll, exit intent, timers, or clicks — designed in Divi itself.',
        features: ['Any Divi layout as popup content', 'Scroll / exit / time / click triggers', 'Per-page display rules'],
    },
    submissions: {
        title: 'Submissions',
        description: 'Every Divi contact form entry, stored, searchable, and exportable.',
        features: ['Browse entries per form', 'CSV export', 'Bulk delete'],
    },
    'google-reviews': {
        title: 'Google Reviews',
        description: 'Show real Google reviews on your site and keep them fresh automatically.',
        features: ['Live business reviews', 'Hide individual reviews', 'Multiple businesses'],
    },
    mailer: {
        title: 'SMTP Mailer',
        description: 'Stop WordPress email landing in spam — route it through reliable SMTP.',
        features: ['Any SMTP provider', 'Test email delivery', 'Custom sender identity'],
    },
    'dark-mode': {
        title: 'Dark Mode',
        description: 'Give every visitor a one-click dark mode, on-brand across your whole Divi site.',
        features: ['Auto or curated palettes', 'OS-aware & scheduled switching', 'Floating toggle switch'],
    },
    'ai-connection': {
        title: 'AI Connection',
        description: 'Let AI agents like Claude Code or ChatGPT manage the plugin through WordPress Abilities and MCP.',
        features: ['16 typed, permission-checked abilities', 'Works over MCP or plain REST', 'Read-only vs destructive clearly annotated'],
    },
    settings: {
        title: 'Settings',
        description: 'Integrations, safe file uploads, and site visibility — part of Divi Torque Pro.',
        features: ['Instagram & Google integrations', 'SVG / JSON / CSV uploads with sanitization', 'Coming soon & maintenance mode'],
    },
};
