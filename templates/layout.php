<?php
// Shared chrome. Authenticated management pages get the top bar; the standalone
// gates (login, install, set-password) render bare.
$bare = in_array($currentPage, ['login', 'install', 'change-password'], true);
$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="public/css/xcribe.css">
</head>
<body>
<?php if (!$bare && $auth->isLoggedIn()): ?>
<header class="topbar">
    <a class="brand" href="?page=dashboard"><?= htmlspecialchars($siteName) ?></a>
    <nav class="topnav">
        <a href="?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Pages</a>
        <?php if ($auth->isOwner()): ?>
            <a href="?page=scribes" class="<?= $currentPage === 'scribes' ? 'active' : '' ?>">Scribes</a>
        <?php endif; ?>
    </nav>
    <div class="who">
        <span class="who-name"><?= htmlspecialchars($auth->currentScribe()['display_name']) ?></span>
        <a class="logout" href="?page=logout">Log out</a>
    </div>
</header>
<?php endif; ?>

<main class="<?= $bare ? 'gate' : 'content' ?>">
    <?php if (!empty($flash)): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    <?php include __DIR__ . '/pages/' . basename($pageTemplate); ?>
</main>
</body>
</html>
