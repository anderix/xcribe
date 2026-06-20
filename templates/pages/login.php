<?php
if ($auth->isLoggedIn()) {
    redirect('page=dashboard');
}
$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        redirect('page=dashboard');
    }
    $loginError = 'Wrong username or password.';
}
?>
<div class="card">
    <h1><?= htmlspecialchars(getSetting('site_name', APP_NAME)) ?></h1>
    <p class="muted">Sign in to edit pages.</p>
    <?php if ($loginError): ?><div class="flash flash-error"><?= htmlspecialchars($loginError) ?></div><?php endif; ?>
    <form method="post" action="?page=login">
        <?= csrfField() ?>
        <label>Username
            <input type="text" name="username" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary">Sign in</button>
    </form>
</div>
