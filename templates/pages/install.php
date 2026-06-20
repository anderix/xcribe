<?php
// First-run setup: create the owner account. Reachable only while no scribe exists.
if ($scribeCount > 0) {
    redirect('page=login');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName    = trim($_POST['site_name'] ?? '');
    $displayName = trim($_POST['display_name'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($siteName === '')                      $errors[] = 'A site name is required.';
    if ($displayName === '')                   $errors[] = 'Your name is required.';
    if (!preg_match('/^[a-z0-9_-]{2,}$/i', $username)) $errors[] = 'Username must be 2+ letters, digits, - or _.';
    if (strlen($password) < 8)                 $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        setSetting('site_name', $siteName);
        $auth->createScribe($username, $password, $displayName, true, false);
        $auth->login($username, $password);
        flash('success', 'Welcome to xcribe. Create your first page to get started.');
        redirect('page=dashboard');
    }
}
?>
<div class="card">
    <h1>xcribe setup</h1>
    <p class="muted">Create the owner account. The owner can add and remove scribes.</p>
    <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    <form method="post" action="?page=install">
        <?= csrfField() ?>
        <label>Site name
            <input type="text" name="site_name" placeholder="Troop 99" value="<?= htmlspecialchars($_POST['site_name'] ?? '') ?>" required>
        </label>
        <label>Your name
            <input type="text" name="display_name" placeholder="Jane Smith" value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>" required>
        </label>
        <label>Username
            <input type="text" name="username" placeholder="jane" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" minlength="8" required>
        </label>
        <button type="submit" class="btn btn-primary">Create owner account</button>
    </form>
</div>
