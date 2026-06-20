<?php
// Forced on first login for a scribe the owner created with a temp password,
// and reachable any time a logged-in scribe wants to change their own.
$me = $auth->currentScribe();
$forced = $auth->mustChangePassword();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new     = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($new) < 8)  $errors[] = 'Password must be at least 8 characters.';
    if ($new !== $confirm) $errors[] = 'The two passwords do not match.';

    if (!$errors) {
        $auth->changePassword((int) $me['id'], $new);
        flash('success', 'Password updated.');
        redirect('page=dashboard');
    }
}
?>
<div class="card">
    <h1>Set your password</h1>
    <p class="muted">
        <?= $forced
            ? 'Choose a password only you know. You were given a temporary one to sign in.'
            : 'Choose a new password.' ?>
    </p>
    <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    <form method="post" action="?page=change-password">
        <?= csrfField() ?>
        <label>New password
            <input type="password" name="password" minlength="8" required autofocus>
        </label>
        <label>Confirm password
            <input type="password" name="confirm" minlength="8" required>
        </label>
        <button type="submit" class="btn btn-primary">Save password</button>
    </form>
</div>
