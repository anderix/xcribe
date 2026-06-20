<?php
// The write surface: a plain Markdown textarea. Saving writes the .md file and
// snapshots a revision. Markdown is content, so there is nothing to police here
// — what a scribe types is what the page becomes.
$slug = $_GET['slug'] ?? '';
$page = getPageBySlug($slug);
if (!$page) {
    flash('error', 'That page does not exist.');
    redirect('page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    savePage((int) $page['id'], $page['slug'], $content, (int) $auth->currentScribe()['id']);
    flash('success', 'Saved.');
    redirect('page=edit&slug=' . urlencode($page['slug']));
}

$content = pageContent($page['slug']);
?>
<div class="page-head">
    <h1>Edit: <?= htmlspecialchars($page['title']) ?></h1>
    <div class="head-actions">
        <a class="btn" href="<?= htmlspecialchars(pageViewUrl($page['slug'])) ?>" target="_blank" rel="noopener">View page</a>
        <a class="btn" href="?page=history&slug=<?= urlencode($page['slug']) ?>">History</a>
    </div>
</div>

<form method="post" action="?page=edit&slug=<?= urlencode($page['slug']) ?>" class="editor">
    <?= csrfField() ?>
    <textarea name="content" class="md-input" spellcheck="true" autofocus><?= htmlspecialchars($content) ?></textarea>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="?page=dashboard">Back to pages</a>
        <span class="muted hint">Write in Markdown. Use <code>#</code> for a heading, <code>-</code> for a list, <code>**bold**</code> for emphasis.</span>
    </div>
</form>
