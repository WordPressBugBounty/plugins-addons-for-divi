# 05 · PLUGIN — Build & Lite Release

Plugin-side work that makes the launch real and fixes what's hurting trust. Plugin: `addons-for-divi` (Lite on wordpress.org + Pro via Freemius/Lemon Squeezy). Confirm namespace/paths against the repo.

> **Phase 0 gates the whole launch.** Don't drive launch traffic into broken code, and don't ship "lifetime forever" copy before the licensing can keep it.

---

## P0 — Stop the bleeding (before any launch traffic)

**Task 1 — Fatal in `includes/assets.php`** (`Call to undefined function …\et_core_is_fb_enabled()`):
call the global with a leading backslash and guard — `if ( function_exists('et_core_is_fb_enabled') && \et_core_is_fb_enabled() )`; enqueue on a hook that fires after Divi loads (`wp_enqueue_scripts`, priority ≥20).
*Accept:* clean activation with Divi and without; passes WP Hive / plugintests.com, no fatal.

**Task 2 — Asset bloat** (free build loads Pro-only + unminified JS — the perf review):
enqueue each module's assets only when that module is on the page; never enqueue Pro-only assets in free; serve `.min.js/.css` in prod (unminified only under `SCRIPT_DEBUG`).
*Accept:* a one-module page loads only that module's assets; no unused Pro payload on free; all prod scripts minified.

**Task 6 — Lifetime licensing honored forever** (the trust fix, must precede lifetime copy):
configure the capped/launch Lifetime plan; lifetime licenses NEVER auto-expire or lose access on a renewal-date check; document the policy in-product.
*Accept:* a lifetime license never enters expired/locked state; policy visible in-app.

---

## P1 — Upgrade path (highest-leverage channel: users are already in wp-admin)

**Task 3 — Launch notice (wordpress.org-COMPLIANT).** Per guideline 11 ("don't hijack the admin"):
- **DO:** show contextually — the plugin's own settings screen + an inline link on the Plugins-list row; **permanently dismissible** (user meta); only to `current_user_can('manage_options')`; time-boxed by a server flag so it ends at the real Divi 5 deadline; short copy, one offer, one link.
- **DON'T:** site-wide notice on every admin page; non-dismissible/“reappear later”; screen-takeover banner; show to subscribers/editors; interfere with normal update notices.
- *Optional* single dashboard notice at launch: dismissible-forever, admin-only, used once.
*Accept:* free users see it once until dismissed; Pro/subscribers never; toggling the flag turns it off live; gone after deadline. Same deadline/price/seats as the site banner (CLAUDE invariant #1).

**Task 4 — Locked-feature teasers.** In the module list, show Pro items visible-but-locked with a one-line outcome + `Unlock with Pro` (on the plugin's own screens — allowed). Never fake-cripple a free module (earns 1-star reviews, hurts ranking).

---

## P2 — Divi 5 native parity (not launch-blocking)

**Task 5 — Convert remaining D4 dynamic modules to D5:** Post List, Contact Form 7, News Ticker, Twitter Feed. Follow the Divi 5 module-dev workflow (D4→D5): set `d4Shortcode` in `module.json`; build VB side (`module.json`, `edit.jsx`, `styles.jsx`, `conversion-outline.js`) + PHP side (`RenderCallbackTrait`, `ModuleStylesTrait`); keep dynamic data fetching in PHP and pass via `ModuleScriptData`; mirror the closest core module.
*Accept:* all four appear in the D5 Visual Builder; saved D4 layouts migrate; frontend output matches D4.

---

## Lite — wordpress.org release

Lite is the acquisition front door, SEO engine, and a trust signal (its reviews are read by Pro buyers). Fixing it is the cheapest trust-repair you have. Never cripple free.

**Two ships** (don't bundle an urgent fix with a risky feature):
- **`4.5.1` patch (now):** Task 1 + Task 2 + changelog/“Tested up to” bump.
- **`4.6.0` feature (launch):** Task 3 notice · Task 4 teasers · any D5 modules ready · readme polish.

**readme.txt:** Stable Tag = new version (bump LAST in SVN); set "Tested up to" to the current WordPress release (verify); short description outcome-led; changelog leads with the fatal + perf fixes; set the **Upgrade Notice** line; FAQ mirrors the landing trust answers; honest free-vs-Pro section; real builder screenshots + clean banner/icon in `/assets`; relevant tags only.

**Review health:** reply to every negative review — calm, specific, owning the fix ("the fatal is fixed in 4.5.1; unminified asset resolved"). On "lifetime vanished," state the current honest policy + link. Ask for reviews only on the plugin's own screens, after real use, permanently dismissible. A steady changelog of real fixes is the strongest "alive & trustworthy" signal.

**SVN release:** land code+readme in `/trunk` (bump header version) → run **Plugin Check (PCP)**, fix all flags → test on clean WP with **Divi 4 AND Divi 5**, `WP_DEBUG`+`E_ALL` → `svn cp trunk tags/x.y.z` → set **Stable Tag** in `trunk/readme.txt` LAST → commit → verify listing + auto-update.

## Pre-release checklist
- [ ] No fatals/notices (Divi 4 + 5, `WP_DEBUG`+`E_ALL`).
- [ ] One-module page loads only that module's assets; minified in prod.
- [ ] Launch notice: contextual, admin-only, permanently dismissible, server-flag time-boxed; never subscribers; never site-wide-persistent.
- [ ] Locked teasers only on plugin screens; no free module crippled.
- [ ] Plugin Check (PCP) passes.
- [ ] Stable Tag bumped last; Tested-up-to current; changelog leads with fixes; Upgrade Notice set.
- [ ] Lifetime licensing verified never-expire (Task 6) before any lifetime copy ships.
- [ ] Notice deadline/price/seats == site banner.

## Measure
Rating trend + new-review sentiment (60 days) · active-install retention post-update · free→Pro from the in-plugin path · support volume on the two fixed issues (should hit ~0).
