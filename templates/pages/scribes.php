<?php
// Owner-only. Add a scribe and the system shows a one-time temporary password
// to hand off; remove a scribe with a confirm. The last owner can't be removed.
$auth->requireOwner();
$db = getDb();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username    = trim($_POST['username'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        if (!preg_match('/^[a-z0-9_-]{2,}$/i', $username)) $errors[] = 'Username must be 2+ letters, digits, - or _.';
        if ($displayName === '')                          $errors[] = 'A name is required.';
        $stmt = $db->prepare('SELECT 1 FROM scribes WHERE username = ?');
        $stmt->execute([strtolower($username)]);
        if ($stmt->fetch()) $errors[] = 'That username is taken.';

        if (!$errors) {
            $temp = generateTempPassword();
            $auth->createScribe($username, $temp, $displayName, false, true);
            // Surfaced once on the next render, then forgotten.
            $_SESSION['new_credential'] = ['username' => strtolower($username), 'password' => $temp];
            redirect('page=scribes');
        }
    }

    if ($action === 'remove') {
        $id = (int) ($_POST['scribe_id'] ?? 0);
        $target = $db->prepare('SELECT * FROM scribes WHERE id = ?');
        $target->execute([$id]);
        $target = $target->fetch();
        $owners = (int) $db->query('SELECT COUNT(*) FROM scribes WHERE is_owner = 1')->fetchColumn();
        if ($target && $target['is_owner'] && $owners <= 1) {
            flash('error', 'You can’t remove the only owner.');
        } elseif ($target) {
            $stmt = $db->prepare('DELETE FROM scribes WHERE id = ?');
            $stmt->execute([$id]);
            flash('success', 'Removed ' . $target['display_name'] . '.');
        }
        redirect('page=scribes');
    }
}

$newCred = $_SESSION['new_credential'] ?? null;
unset($_SESSION['new_credential']);

$scribes = $db->query('SELECT * FROM scribes ORDER BY is_owner DESC, display_name COLLATE NOCASE')->fetchAll();
$me = $auth->currentScribe();
?>
<div class="page-head"><h1>Scribes</h1></div>

<?php if ($newCred): ?>
<div class="credential">
    <h2>New scribe added</h2>
    <p>Give these details to <strong><?= htmlspecialchars($newCred['username']) ?></strong>. The temporary password is shown only now; they’ll set their own when they first sign in.</p>
    <dl class="cred-grid">
        <dt>Username</dt><dd><code><?= htmlspecialchars($newCred['username']) ?></code></dd>
        <dt>Temporary password</dt><dd><code class="temp-pw"><?= htmlspecialchars($newCred['password']) ?></code></dd>
    </dl>
</div>
<?php endif; ?>

<?php foreach ($errors as $e): ?><div class="flash flash-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<table class="list">
    <thead><tr><th>Name</th><th>Username</th><th>Role</th><th class="actions">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($scribes as $s): ?>
        <tr>
            <td><strong><?= htmlspecialchars($s['display_name']) ?></strong></td>
            <td><code><?= htmlspecialchars($s['username']) ?></code></td>
            <td><?= $s['is_owner'] ? 'Owner' : 'Scribe' ?></td>
            <td class="actions">
                <?php if ($s['id'] == $me['id']): ?>
                    <span class="muted">you</span>
                <?php else: ?>
                    <form method="post" action="?page=scribes" class="inline"
                          onsubmit="return confirm(<?= jsAttr('Remove ' . $s['display_name'] . '?') ?>);">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="scribe_id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" class="link-danger">Remove</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="card">
    <h2>Add a scribe</h2>
    <form method="post" action="?page=scribes">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add">
        <label>Name
            <input type="text" name="display_name" placeholder="Kayla Smith" required>
        </label>
        <label>Username
            <input type="text" name="username" placeholder="kayla" required>
        </label>
        <button type="submit" class="btn btn-primary">Add scribe</button>
    </form>
</div>
