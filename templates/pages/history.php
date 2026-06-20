<?php
// Every save is kept. A scribe can read an earlier version and restore it;
// restoring writes that text back as a new save, so nothing is ever lost.
$slug = $_GET['slug'] ?? '';
$page = getPageBySlug($slug);
if (!$page) {
    flash('error', 'That page does not exist.');
    redirect('page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restore') {
    $rev = getRevision((int) ($_POST['revision_id'] ?? 0), (int) $page['id']);
    if ($rev) {
        savePage((int) $page['id'], $page['slug'], $rev['content'], (int) $auth->currentScribe()['id']);
        flash('success', 'Restored an earlier version.');
    }
    redirect('page=history&slug=' . urlencode($page['slug']));
}

$revisions = listRevisions((int) $page['id']);
$viewing = null;
if (isset($_GET['rev'])) {
    $viewing = getRevision((int) $_GET['rev'], (int) $page['id']);
}
?>
<div class="page-head">
    <h1>History: <?= htmlspecialchars($page['title']) ?></h1>
    <a class="btn" href="?page=edit&slug=<?= urlencode($page['slug']) ?>">Back to editing</a>
</div>

<?php if (!$revisions): ?>
    <p class="muted">No saved versions yet.</p>
<?php else: ?>
<div class="history">
    <table class="list">
        <thead><tr><th>Saved</th><th>By</th><th class="actions">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($revisions as $i => $r): ?>
            <tr class="<?= $viewing && $viewing['id'] == $r['id'] ? 'selected' : '' ?>">
                <td><?= htmlspecialchars($r['created_at']) ?><?= $i === 0 ? ' <span class="badge">current</span>' : '' ?></td>
                <td><?= htmlspecialchars($r['scribe_name'] ?? '—') ?></td>
                <td class="actions">
                    <a href="?page=history&slug=<?= urlencode($page['slug']) ?>&rev=<?= (int) $r['id'] ?>">View</a>
                    <?php if ($i !== 0): ?>
                        <form method="post" action="?page=history&slug=<?= urlencode($page['slug']) ?>" class="inline"
                              onsubmit="return confirm('Restore this version? Your current text is saved in history first.');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="revision_id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="link">Restore</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($viewing): ?>
        <div class="rev-view">
            <h2>Version from <?= htmlspecialchars($viewing['created_at']) ?></h2>
            <pre class="md-preview"><?= htmlspecialchars($viewing['content']) ?></pre>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
