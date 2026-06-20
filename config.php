<?php
// xcribe configuration — the one file you edit per install.
//
// xcribe is a drop-in: plant this directory beside your static pages, point it
// at your Axe deployment, and it gives 2–4 non-technical people ("scribes") a
// password-protected way to edit a handful of Markdown pages in the browser.
// The pages themselves are public; the password only gates editing.

define('APP_NAME', 'xcribe');
define('APP_VERSION', '0.1.0');

// Where this directory is reachable from the web root, no trailing slash.
// If you drop xcribe at https://example.org/xcribe, leave this as '/xcribe'.
// This is how xcribe builds the public link to each rendered page.
define('WEB_BASE', '/xcribe');

// Where Axe's Markdown viewer lives, with a trailing slash. The usual
// fleet convention is the site root. xcribe sends pages here to be rendered.
define('AXE_VIEW', '/axe/view/');

// Filesystem locations. You normally do not need to change these.
define('APP_ROOT', __DIR__);
define('PAGES_DIR', APP_ROOT . '/pages');     // the editable .md files live here
define('DB_PATH', APP_ROOT . '/db/xcribe.db');
define('SCHEMA_PATH', APP_ROOT . '/db/schema.sql');
