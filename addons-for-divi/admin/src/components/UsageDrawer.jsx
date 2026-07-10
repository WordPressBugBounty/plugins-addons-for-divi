import { Drawer, EmptyState, IconButton, Row, RowList, PencilIcon, EyeIcon } from '@plugpress/ui';

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
                            description={p.post_type}
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
