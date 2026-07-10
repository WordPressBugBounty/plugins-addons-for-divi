import catalog from '../../../assets/module-catalog.json';

/**
 * Mesh module icon — the same squircle + gradient + line-glyph system the
 * divitorque.com theme renders (dt_render_icon in dt-website/inc/icons.php).
 * Glyph markup comes from our generated catalog JSON (theme-authored,
 * trusted), hence dangerouslySetInnerHTML.
 *
 * Resolution order for a plugin module: catalog entry (via catalog_slug or
 * name) → extra_glyphs[name] → per-category fallback glyph.
 */
export function ModuleIcon({ module, size = 44 }) {
    const slug = module.catalog_slug || module.name;
    const entry = catalog.modules[slug] || catalog.extensions[slug];
    const glyph =
        entry?.glyph ||
        catalog.extra_glyphs[module.name] ||
        catalog.fallback_glyphs[entry?.cat] ||
        catalog.fallback_glyphs.utility;
    const grad = entry?.grad || ['#9C7CFF', '#6E8BFF', '#ECECFF'];

    return (
        <span
            className="dt-modicon"
            role="img"
            aria-label={module.title || entry?.label || slug}
            style={{ '--g1': grad[0], '--g2': grad[1], '--g3': grad[2], width: size, flexBasis: size }}
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.9"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
                focusable="false"
                dangerouslySetInnerHTML={{ __html: glyph }}
            />
        </span>
    );
}

/** Catalog record for a plugin module (desc, demo/docs links, category). */
export function catalogEntry(module) {
    const slug = module.catalog_slug || module.name;
    return catalog.modules[slug] || null;
}

export const CATEGORIES = catalog.categories;
