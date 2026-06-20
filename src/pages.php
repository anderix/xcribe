<?php
// Page model. A page is a row in the pages table plus a file at
// pages/<slug>.md. The file is the published artifact the Axe viewer renders;
// the table tracks title and ordering, and revisions records every save.

// Slugs are the page's filename and public URL, so they are deliberately
// strict: lowercase letters, digits, and single hyphens. This also makes path
// traversal impossible — a slug can never escape the pages directory.
function isValidSlug(string $slug): bool {
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function slugify(string $text): string {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function pageFilePath(string $slug): string {
    return PAGES_DIR . '/' . $slug . '.md';
}

// The public link a scribe shares: the Markdown file handed to the Axe viewer.
function pageViewUrl(string $slug): string {
    $path = ltrim(WEB_BASE, '/') . '/pages/' . $slug . '.md';
    return AXE_VIEW . '?url=' . rawurlencode($path);
}

function listPages(): array {
    return getDb()->query('SELECT * FROM pages ORDER BY title COLLATE NOCASE')->fetchAll();
}

function getPageBySlug(string $slug): ?array {
    $stmt = getDb()->prepare('SELECT * FROM pages WHERE slug = ?');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function pageContent(string $slug): string {
    $file = pageFilePath($slug);
    return is_file($file) ? file_get_contents($file) : '';
}

function createPage(string $slug, string $title): int {
    $db = getDb();
    $stmt = $db->prepare('INSERT INTO pages (slug, title) VALUES (?, ?)');
    $stmt->execute([$slug, trim($title)]);
    @mkdir(PAGES_DIR, 0755, true);
    $starter = "# " . trim($title) . "\n\nStart writing here.\n";
    file_put_contents(pageFilePath($slug), $starter);
    return (int) $db->lastInsertId();
}

// Write the page file and snapshot it into revisions in one step. Content is
// normalized to a trailing newline so successive saves diff cleanly.
function savePage(int $pageId, string $slug, string $content, ?int $scribeId): void {
    $content = rtrim($content, "\r\n") . "\n";
    file_put_contents(pageFilePath($slug), $content);
    $stmt = getDb()->prepare(
        'INSERT INTO revisions (page_id, content, scribe_id) VALUES (?, ?, ?)'
    );
    $stmt->execute([$pageId, $content, $scribeId]);
}

function deletePage(int $pageId, string $slug): void {
    $stmt = getDb()->prepare('DELETE FROM pages WHERE id = ?');
    $stmt->execute([$pageId]);   // revisions cascade
    @unlink(pageFilePath($slug));
}

function listRevisions(int $pageId): array {
    $stmt = getDb()->prepare(
        'SELECT r.*, s.display_name AS scribe_name
           FROM revisions r
           LEFT JOIN scribes s ON s.id = r.scribe_id
          WHERE r.page_id = ?
          ORDER BY r.id DESC'
    );
    $stmt->execute([$pageId]);
    return $stmt->fetchAll();
}

function getRevision(int $revisionId, int $pageId): ?array {
    $stmt = getDb()->prepare('SELECT * FROM revisions WHERE id = ? AND page_id = ?');
    $stmt->execute([$revisionId, $pageId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
