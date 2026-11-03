# Worktrees

Agents can work in git worktrees and test their branch on the one local WordPress site, without merging and without a database per branch. A dev-only mu-plugin serves a worktree's theme code for any request that asks for it. Everything else (database, uploads, plugins) stays shared.

## Quick start

```bash
pnpm worktree:setup                 # first thing, from inside the worktree
APP_URL=$(grep '^APP_URL' .env | cut -d= -f2)
open "$APP_URL/?worktree=<name>"    # <name> = the worktree's folder name
```

Worktrees must live in `.claude/worktrees/<name>` (Claude Code's default). That folder is gitignored, and `.deployignore` already excludes `.claude`.

## Setup

`pnpm worktree:setup` (`dev-scripts/worktree-setup.sh`) is safe to re-run:

- runs `pnpm install` from pnpm's store (a couple of seconds)
- copies `vendor/` from the main checkout and runs `composer dump-autoload`, so classes load from this worktree. Never run `composer install` in a worktree: `composer.json`'s relative installer paths would put plugins inside `.claude/`. If the branch changes `composer.lock`, run `composer install` in the main checkout after merging.
- copies `.env` and `auth.json`, and builds assets into the worktree's `public/`
- links `dev-scripts/mu-plugins/gust-worktree-switch.php` into `mu-plugins/` if it isn't there (one-time, per site)

Re-run it after pulling dependency changes. Rebuild with `pnpm build`, or run `pnpm dev`: Vite picks a free port and writes the worktree's own `public/hot`.

## Picking the worktree

| Where   | How                                                                                                                                                                  |
| ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Browser | `?worktree=<name>` on any URL sets a session cookie, so links, forms, redirects, REST, wp-admin and the editor all stay on the worktree. `?worktree=main` clears it. |
| curl    | Add `?worktree=<name>` to every request, or keep the cookie with `-c`/`-b`.                                                                                          |
| WP-CLI  | Run `wp` from inside the worktree; it finds the site by itself. `GUST_WORKTREE=<name>` picks one from anywhere, and `GUST_WORKTREE=main` forces the main checkout.   |

Check what you're seeing: responses carry an `X-Gust-Worktree: <name>` header, the admin bar shows "Worktree: <name>", and logged-out pages show a badge (`[data-gust-worktree]`). An unknown name returns a 404 "Worktree not found" page instead of quietly serving main.

**Use your own browser session.** The cookie belongs to the browser, so agents sharing one browser would switch each other's worktree. Each agent needs its own browser context: run Chrome DevTools MCP with `--isolated`, or use a new Playwright context. That also keeps logins and other cookies separate. Don't send the worktree as a request header from a browser: extra headers go to every site the page loads, and third-party embeds then fail their CORS checks.

## What's per worktree and what's shared

| Per worktree                                              | Shared by every worktree                           |
| --------------------------------------------------------- | -------------------------------------------------- |
| Theme PHP, templates, components                          | Posts, pages, terms, media, users                  |
| Built assets and the Vite dev server                      | Site options, ACF option-page values, field values |
| ACF field groups (`acf-json/`)                            | Plugins and their settings                         |
| Rewrite rules (stored as `rewrite_rules_worktree_<name>`) | Theme mods (menu locations, logo)                  |

Rewrite rules are kept per worktree because WordPress compiles them from the post types, taxonomies and routes the theme registers. With one shared copy, a branch that adds a post type would leak its rules to main, then lose them the next time main flushes. A worktree's copy rebuilds itself when it's empty, so you don't need to flush after adding a post type or route.

## Rules for the shared database

- Don't change site options which might affect other worktrees, activate or deactivate plugins, change the permalink structure or run `wp db` commands. Ask the user.
- Give test content a recognisable title prefix (`[wt-<name>]`) and delete it when you're done.
- Name test users `agent-<name>`, not `agent-user`, so parallel agents don't delete each other's login.
- Saving a field group in wp-admin while on a worktree writes to that worktree's `acf-json/`. Field values are still shared.
- A branch that needs a schema or content migration should use its own database, not this setup.

## Dev-only safety

- The mu-plugin's source lives in `dev-scripts/`, which is in `.deployignore`. It's only ever symlinked into a local `mu-plugins/`.
- It does nothing unless the environment is `development`: the `WP_ENVIRONMENT_TYPE` constant, then the env var, then the theme's `.env`. It never calls `wp_get_environment_type()`, which caches its first answer before Gust has read `.env`.
- It only filters options for the current request. It never activates a theme or changes the `stylesheet` option, so nothing leaks into a `wp-sync` database push.

## Cleaning up

After removing a worktree, delete its rewrite rules copy:

```bash
wp option delete rewrite_rules_worktree_<name>
```

## Troubleshooting

- **Unstyled page:** the worktree has no build. Run `pnpm build` in it.
- **"Worktree not found":** the folder name is wrong, or the worktree has been removed. Visit `?worktree=main` to clear the cookie.
- **Main checkout reloads when a worktree changes:** `vite.config.js` ignores `.claude/worktrees/**`. Restart `pnpm dev` in main if it was started before that change.
- **Class not found:** a new class or component was added. Run `composer dump-autoload` in the worktree (or re-run setup).
