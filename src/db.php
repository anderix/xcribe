<?php
// Database access. Mirrors the campfire pattern: one lazily-opened PDO handle,
// schema applied on first creation, plus tiny settings helpers.

function getDb(): PDO {
    static $db = null;
    if ($db !== null) {
        return $db;
    }

    $isNew = !file_exists(DB_PATH);
    @mkdir(dirname(DB_PATH), 0755, true);
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    if ($isNew) {
        $db->exec(file_get_contents(SCHEMA_PATH));
    }

    return $db;
}

function getSetting(string $key, ?string $default = null): ?string {
    $stmt = getDb()->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function setSetting(string $key, ?string $value): void {
    $stmt = getDb()->prepare(
        'INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = ?'
    );
    $stmt->execute([$key, $value, $value]);
}
