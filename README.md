# xcribe

A drop-in that lets a few non-technical people edit a handful of Markdown pages in the browser. You plant it beside your static pages, give two to four people a username and password, and they can change the page text themselves — no editor to install, no Git, no deploy step. The pages they write are public; the password only gates editing.

xcribe is the prose counterpart to a directory browser like [browse](https://github.com/anderix/browse): the same "drop a folder on the site and point it at [Axe](https://github.com/excelano/axe)" shape, except here the tool also *writes* the files. Editing is content-only, because Markdown is content — there is no code surface for a scribe to break.

The people you grant access to are **scribes**. The first account, created at setup, is the **owner**; the owner adds and removes scribes.

## How it works

A scribe signs in and sees a list of pages. Each page is a real Markdown file on disk at `pages/<slug>.md`. Editing a page is a plain textarea; saving writes the file and snapshots a version into a small SQLite database, so any earlier version can be restored. Reading a page is not handled by xcribe at all — it links out to the Axe viewer at `/axe/view/?url=…`, which fetches and renders the file under Axe's own sanitizer. xcribe never renders page content itself, so it carries no document-parsing attack surface.

```
config.php           The one file you edit: where xcribe lives and where Axe is.
index.php            Front controller; ?page= selects a screen.
src/                 db, auth (local password, bcrypt), and the page model.
templates/           Login, setup, dashboard, editor, history, scribe management.
pages/               The editable Markdown files — public, rendered by Axe.
db/                  SQLite: scribes, page index, and version history.
public/css/          The management-screen styling (the admin UI, not the pages).
```

## Setup

Drop the directory onto a PHP host (PHP 8+, the SQLite extension, which is standard) and serve it. The first visit shows a one-time setup screen: name the site and create the owner account. From there the owner creates pages and adds scribes.

xcribe expects Axe to be deployed at the site root as `/axe/`, the usual convention; that is where it sends pages to be rendered. If your install lives somewhere other than `/xcribe`, or your Axe is elsewhere, set `WEB_BASE` and `AXE_VIEW` in `config.php`.

```bash
# Try it locally
php -S localhost:8000 -t xcribe/
```

## Adding a scribe

The owner opens **Scribes**, enters a name and username, and clicks **Add scribe**. xcribe generates a friendly one-time password (for example `mint-otter-sail-94`) and shows it once — the owner reads it off to the new scribe. On first sign-in the scribe is required to set a password only they know.

To remove someone, the owner clicks **Remove** next to their name. The last owner can't be removed.

## Versions and undo

Every save is kept. A page's **History** lists each saved version, who saved it, and when; any earlier version can be viewed and restored. Restoring writes that text back as a new save, so nothing is ever lost — the safety net a non-technical editor needs.

## Attribution

Author: David M. Anderson. Built with AI assistance (Claude, Anthropic).
