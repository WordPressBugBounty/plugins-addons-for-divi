import { Drawer, EmptyState, IconButton, Row, RowList, PencilIcon, EyeIcon } from '@plugpress/ui';

// Post types whose content renders outside a normal page (so "used" can be
// true even when no live page shows the module).
const POST_TYPE_LABELS = {
    et_pb_layout: 'Divi Library / Theme Builder layout',
    wp_block: 'Reusable block',
    page: 'Page',
    post: 'Post',
};

const describeEntry = (p) => {
    const label = POST_TYPE_LABELS[p.post_type] || p.post_type;
    return p.post_status && p.post_status !== 'publish' ? `${label} (${p.post_status})` : label;
};

/** Pages using a module — opened from the usage badge on a ModuleCard. */
export function UsageDrawer({ module, usage, onClose }) {
    const open = Boolean(module);
    const pages = usage?.pages || [];
    const count = usage?.count || 0;

    return (
        <Drawer
            open={open}
            onOpenChange={(next) => !next && onClose()}
            title={module ? `Where ${module.title} is used` : ''}
            description={
                count > pages.length
                    ? `Showing ${pages.length} of ${count} pages.`
                    : `${count} ${count === 1 ? 'page uses' : 'pages use'} this module.`
            }
        >
            {pages.length ? (
                <RowList>
                    {pages.map((p) => (
                        <Row
                            key={p.id}
                            title={p.title}
                            description={describeEntry(p)}
                            actions={
                                <>
                                    {p.edit_url && (
                                        <IconButton aria-label="Edit" href={p.edit_url} target="_blank" rel="noreferrer">
                                            <PencilIcon />
                                        </IconButton>
                                    )}
                                    {p.view_url && (
                                        <IconButton aria-label="View" href={p.view_url} target="_blank" rel="noreferrer">
                                            <EyeIcon />
                                        </IconButton>
                                    )}
                                </>
                            }
                        />
                    ))}
                </RowList>
            ) : (
                <EmptyState title="Not used yet" description="No pages reference this module." />
            )}
        </Drawer>
    );
}
