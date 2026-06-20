<?php
// Scribe authentication. A trimmed, local-password-only descendant of the
// campfire / Xinglet auth scheme — no Microsoft provider, no email. Passwords
// are bcrypt via password_hash(); the session cookie is the whole token story.

class ScribeAuth {

    // A real bcrypt hash used only to keep login timing constant when the
    // submitted username does not exist. No password produces it.
    private const DUMMY_HASH = '$2y$12$HQnqDB63nPxoM15unoh.uOhhRmXbAig9uAI9l.F8BN9d.JuqJQAny';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Harden the session cookie before it is issued: never readable from
            // JavaScript, Secure whenever the request arrived over HTTPS (so a
            // production deploy can't silently leak it over plain HTTP, while
            // local http dev still works), and SameSite=Lax as CSRF defence in
            // depth on top of the per-request token.
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public function login(string $username, string $password): bool {
        $stmt = getDb()->prepare('SELECT * FROM scribes WHERE username = ?');
        $stmt->execute([self::normalize($username)]);
        $scribe = $stmt->fetch();

        // Always spend roughly the same time whether or not the username exists,
        // so response timing can't be used to enumerate valid scribes. The dummy
        // hash is a real bcrypt hash that nothing will ever match.
        if (!$scribe) {
            password_verify($password, self::DUMMY_HASH);
            return false;
        }
        if (!password_verify($password, $scribe['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['scribe_id'] = $scribe['id'];
        $_SESSION['username'] = $scribe['username'];
        $_SESSION['display_name'] = $scribe['display_name'];
        $_SESSION['is_owner'] = (int) $scribe['is_owner'];
        $_SESSION['must_change_password'] = (int) $scribe['must_change_password'];
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['scribe_id']);
    }

    public function isOwner(): bool {
        return !empty($_SESSION['is_owner']);
    }

    public function mustChangePassword(): bool {
        return !empty($_SESSION['must_change_password']);
    }

    public function currentScribe(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['scribe_id'],
            'username' => $_SESSION['username'],
            'display_name' => $_SESSION['display_name'],
            'is_owner' => (int) $_SESSION['is_owner'],
        ];
    }

    public function requireAuth(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ?page=login');
            exit;
        }
    }

    public function requireOwner(): void {
        $this->requireAuth();
        if (!$this->isOwner()) {
            http_response_code(403);
            exit('Only the page owner can manage scribes.');
        }
    }

    public function createScribe(string $username, string $password, string $displayName, bool $isOwner, bool $mustChange): int {
        $db = getDb();
        $stmt = $db->prepare(
            'INSERT INTO scribes (username, password_hash, display_name, is_owner, must_change_password)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            self::normalize($username),
            password_hash($password, PASSWORD_DEFAULT),
            trim($displayName) !== '' ? trim($displayName) : self::normalize($username),
            $isOwner ? 1 : 0,
            $mustChange ? 1 : 0,
        ]);
        return (int) $db->lastInsertId();
    }

    public function changePassword(int $scribeId, string $newPassword): void {
        $stmt = getDb()->prepare(
            'UPDATE scribes SET password_hash = ?, must_change_password = 0 WHERE id = ?'
        );
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $scribeId]);
        if (($_SESSION['scribe_id'] ?? null) === $scribeId) {
            $_SESSION['must_change_password'] = 0;
        }
    }

    private static function normalize(string $username): string {
        return strtolower(trim($username));
    }
}

// A friendly one-time password the owner can read aloud or paste: four short
// lowercase words plus two digits, e.g. "mint-otter-cedar-finch-94". Easy to
// hand off, while four picks from this 60-word list plus the suffix give about
// 30 bits of entropy — enough that the temporary secret can sit unused until
// the scribe's first login without being guessable.
function generateTempPassword(): string {
    $words = ['mint','otter','sail','river','maple','cloud','ember','quartz',
              'willow','cedar','harbor','pebble','meadow','finch','cobalt',
              'lumen','thicket','marigold','juniper','sparrow','basalt','tundra',
              'amber','birch','canyon','dune','fern','glade','hollow','ivory',
              'kelp','lichen','moss','nectar','opal','prairie','reef','slate',
              'timber','umber','valley','walnut','yarrow','zephyr','acorn','brook',
              'coral','delta','elm','frost','garnet','heather','indigo','jade',
              'larch','mesa','onyx','pine','ridge','spruce'];
    $pick = function () use ($words) {
        return $words[random_int(0, count($words) - 1)];
    };
    return $pick() . '-' . $pick() . '-' . $pick() . '-' . $pick() . '-' . random_int(10, 99);
}
