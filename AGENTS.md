# AGENTS.md

## Cursor Cloud specific instructions

### What this repo is
This repo is **not a standalone app**. It is the PHP code for a **WordPress / WooCommerce child theme** ("WM Creations") built on the **GeneratePress** parent theme. The code lives in `wm-theme/`:
- `wm-theme/functions.php` — the active child-theme code (WhatsApp order flow, product-page redesign, product badges, custom fields, pack/box pricing, custom header/announcement bar, category menu, mobile toolbar).
- `wm-theme/wm-creations-functions-complete.php` — a byte-identical backup of `functions.php` (not loaded).
- `wm-theme/wm-product-page-redesign.php` — a snippet meant to be pasted into `functions.php` (not loaded on its own).

There is no package manager, lockfile, build step, or automated test suite in this repo.

### Local dev environment (already provisioned in the VM snapshot)
A full WordPress dev stack is pre-installed and persists in the VM snapshot:
- PHP 8.3 CLI + extensions, MariaDB 10.11, and WP-CLI (`wp`).
- WordPress core installed at `/workspace/.wp-dev/wordpress` (DB `wordpress`, user `wpuser`/`wppass` over TCP `127.0.0.1`).
- WooCommerce plugin + GeneratePress parent theme installed and active.
- The child theme is `wp-content/themes/wm-theme`, whose `functions.php` is a **symlink to `/workspace/wm-theme/functions.php`**, so repo edits are reflected live. Its `style.css` (WordPress child-theme header) only exists in the dev install, not in the repo.
- Admin login: `admin` / `admin123`. Site URL: `http://localhost:8080`.

### Starting the services (NOT done by the update script — do this each session)
1. Start MariaDB (data dir is the system default `/var/lib/mysql`):
   `sudo mariadbd-safe &` then wait a few seconds; verify with `sudo mariadb -e "SELECT 1;"`.
2. Start the WordPress dev server (PHP built-in server via WP-CLI):
   `cd /workspace/.wp-dev/wordpress && wp server --host=0.0.0.0 --port=8080`
   (run it in a tmux session so it keeps running). Then browse `http://localhost:8080`.

### Lint / test / build / run
- **Lint:** `php -l wm-theme/<file>.php` (PHP syntax check). There is no linter config; `php -l` is the check.
- **Test:** no automated tests exist. Validate by driving the storefront in a browser (e.g. product page → add to cart → cart).
- **Build:** none (interpreted PHP).
- **Run:** use the two service steps above.

### Non-obvious gotchas
- `DB_HOST` must be `127.0.0.1` (TCP), not `localhost`; the WP-CLI/PHP mysqli socket path does not match MariaDB's default socket, so `localhost` fails to connect.
- WooCommerce needs pretty permalinks (`wp rewrite structure '/%postname%/' --hard`); already configured in the snapshot.
- Known **pre-existing bug in the theme code** (do not "fix" as part of setup): the WhatsApp checkout gateway (`WM_WhatsApp_Gateway`) never registers because it is defined inside a `plugins_loaded` callback added from `functions.php`. A theme's `functions.php` loads *after* `plugins_loaded` has already fired, so the class is never defined and the gateway does not appear at checkout. Other theme features (announcement bar, custom "Customize Your Design" fields, pack pricing, badges, product-page redesign) work fine.
- The `.wp-dev/` directory is the local WordPress install; it is gitignored and must not be committed.
