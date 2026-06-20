<?php
// The hub: every page a scribe can edit, plus owner-only page removal.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete-page') {
    $auth->requireOwner();
    $page = getPageBySlug($_POST['slug'] ?? '');
    if ($page) {
        deletePage((int) $page['id'], $page['slug']);
        flash('success', 'Deleted “' . $page['title'] . '”.');
    }
    redirect('page=dashboard');
}

$pages = listPages();
?>
<div class="page-head">
    <h1>Pages</h1>
    <a class="ghost" href="?page=page-new">New page</a>
</div>

<?php if (!$pages): ?>
    <p class="muted">No pages yet. Create your first one.</p>
<?php else: ?>
<table class="list">
    <thead>
        <tr><th>Page</th><th>Address</th><th class="actions">Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
        <tr>
            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
            <td><code><?= htmlspecialchars($p['slug']) ?></code></td>
            <td class="actions">
                <a href="?page=edit&slug=<?= urlencode($p['slug']) ?>">Edit</a>
                <a href="<?= htmlspecialchars(pageViewUrl($p['slug'])) ?>" target="_blank" rel="noopener">View</a>
                <a href="?page=history&slug=<?= urlencode($p['slug']) ?>">History</a>
                <?php if ($auth->isOwner()): ?>
                    <form method="post" action="?page=dashboard" class="inline"
                          onsubmit="return confirm(<?= jsAttr('Delete “' . $p['title'] . '” and its history? This cannot be undone.') ?>);">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete-page">
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($p['slug']) ?>">
                        <button type="submit" class="link-danger">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
