<?php
// Shared chrome. xcribe's management UI is branded with Axe, the same way the
// browse drop-in is: it pulls Axe's stylesheets (and the site's own brand.css)
// from the root deployment, so it tracks the host site's palette and Axe's
// light/dark theming for free. theme.js applies the saved/system theme before
// paint and binds the sun/moon toggle. The authenticated pages get the Axe nav
// bar; the standalone gates (login, install, set-password) render bare.
$bare = in_array($currentPage, ['login', 'install', 'change-password'], true);
$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="/axe/default.css">
    <link rel="stylesheet" href="/brand.css"><!-- site brand; overrides default, 404s harmlessly when absent -->
    <link rel="stylesheet" href="/axe/axe.css">
    <link rel="stylesheet" href="public/css/xcribe.css?v=<?= @filemtime(APP_ROOT . '/public/css/xcribe.css') ?>">
    <script src="/axe/theme.js"></script>
</head>
<body>
<?php if (!$bare && $auth->isLoggedIn()): ?>
<nav>
    <ul>
        <li><a class="nav-brand" href="?page=dashboard"><?= htmlspecialchars($siteName) ?></a></li>
        <li><a href="?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Pages</a></li>
        <?php if ($auth->isOwner()): ?>
            <li><a href="?page=scribes" class="<?= $currentPage === 'scribes' ? 'active' : '' ?>">Scribes</a></li>
        <?php endif; ?>
        <li class="nav-right">
            <span class="who-name"><?= htmlspecialchars($auth->currentScribe()['display_name']) ?></span>
            <a href="?page=logout">Log out</a>
            <button class="theme-toggle" aria-label="Toggle light or dark theme"></button>
        </li>
    </ul>
</nav>
<?php elseif ($bare): ?>
<div class="gate-toggle"><button class="theme-toggle" aria-label="Toggle light or dark theme"></button></div>
<?php endif; ?>

<main class="<?= $bare ? 'gate' : 'content' ?>">
    <?php if (!empty($flash)): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    <?php include __DIR__ . '/pages/' . basename($pageTemplate); ?>
</main>
</body>
</html>
