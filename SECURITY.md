# Security Policy

## Reporting a vulnerability

Please report suspected vulnerabilities privately through GitHub Security Advisories at https://github.com/anderix/xcribe/security/advisories/new. If you would rather not use GitHub, email david.anderson@excelano.com instead. I aim to respond within seven days.

Please do not open public issues for security problems.

## Supported versions

xcribe is built from source and self-hosted by each operator. Security fixes ship through `main`; pull and redeploy to apply them. There are no maintained release branches.

## Security model — read this before deploying

xcribe has two surfaces with very different exposure, and it is worth being precise about each.

**The pages are public.** Files in `pages/` are served as ordinary static Markdown so the Axe viewer can fetch and render them. Anyone who can reach the site can read them. The password protects *editing*, not *reading* — do not put anything in an xcribe page that you would not publish.

**Editing is authenticated.** Every screen except login and first-run setup requires a signed-in scribe. Passwords are stored as bcrypt hashes via PHP's `password_hash()`; there is no Microsoft or third-party login and no password stored in plaintext anywhere, including the one-time temporary password the owner hands a new scribe (only its hash is kept). Sessions are PHP sessions; the session id is regenerated on login. Every state-changing request — saving a page, adding or removing a scribe, restoring a version — is a POST guarded by a per-session CSRF token, and destructive actions confirm in the browser first.

**Page slugs cannot escape `pages/`.** A slug is validated against `^[a-z0-9]+(?:-[a-z0-9]+)*$` before it is ever used as a filename, so a request can never traverse out of the pages directory.

**xcribe does not render page content.** Editing is a plain textarea and saving writes the raw file. Reading links out to the Axe viewer, which renders under its own sanitizer — so whatever exposure comes from rendering Markdown is Axe's surface, not xcribe's. See Axe's `SECURITY.md`.

## Operator responsibilities

A few things only the deployer can do, and xcribe cannot enforce them for you:

- **Serve over HTTPS.** Passwords and session cookies cross the wire on every sign-in.
- **Protect the internals.** The bundled `.htaccess` denies web access to `src/`, `templates/`, `db/`, and `config.php`, and refuses to serve any `*.db` file. This relies on Apache honoring `.htaccess`. On nginx or other servers, deny those paths in your server configuration — the SQLite database holds every password hash and must never be web-readable.
- **Keep scribes few and trusted.** All scribes can edit all pages; the model is a small group of known people, not public registration. There is no rate limiting on login, so put xcribe behind your host's protections if it faces the open internet.
- **Hand off temporary passwords out of band.** Read the one-time password to the new scribe directly; it is shown in the browser only once and is never emailed.
