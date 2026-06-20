<?php
// Create a page. Any scribe may add one. The slug becomes the filename and the
// public address, so it is validated strictly; left blank, it is derived from
// the title.
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    if ($slug === '') {
        $slug = slugify($title);
    }

    if ($title === '')            $errors[] = 'A title is required.';
    if (!isValidSlug($slug))      $errors[] = 'Address must be lowercase letters, digits, and hyphens (e.g. spring-campout).';
    if (!$errors && getPageBySlug($slug)) $errors[] = 'A page with that address already exists.';

    if (!$errors) {
        createPage($slug, $title);
        flash('success', 'Created “' . $title . '”.');
        redirect('page=edit&slug=' . urlencode($slug));
    }
}
?>
<div class="card">
    <h1>New page</h1>
    <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    <form method="post" action="?page=page-new">
        <?= csrfField() ?>
        <label>Title
            <input type="text" name="title" placeholder="Spring Campout" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required autofocus>
        </label>
        <label>Address <span class="muted">(optional — made from the title if blank)</span>
            <input type="text" name="slug" placeholder="spring-campout" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
        </label>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create page</button>
            <a class="btn" href="?page=dashboard">Cancel</a>
        </div>
    </form>
</div>
