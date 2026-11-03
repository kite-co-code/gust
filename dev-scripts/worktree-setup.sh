#!/bin/bash -e

# -----------------------------------------------------------------------------
# Gust Worktree Setup
#
# Run inside a git worktree of this theme (.claude/worktrees/<name>) so it can be
# built and tested on the shared local site without merging:
#
#   pnpm worktree:setup
#
# - installs node_modules from pnpm's store
# - copies vendor from the main checkout and regenerates the autoloader, so PHP
#   classes resolve to THIS worktree (a symlinked vendor resolves to the main
#   checkout, because PHP realpaths __DIR__)
# - copies .env and auth.json, and builds assets into this worktree's public/
# - symlinks the worktree switch mu-plugin into WordPress if it's missing
#
# Safe to re-run. See .claude/agent_docs/worktrees.md.
# -----------------------------------------------------------------------------

# The shebang's -e is ignored when run as `bash worktree-setup.sh`.
set -eo pipefail

here="$(cd "$(dirname "$0")/.." && pwd)"
main="$(dirname "$(git -C "$here" rev-parse --path-format=absolute --git-common-dir)")"

if [ "$here" = "$main" ]; then
    echo "This is the main checkout, nothing to do."
    exit 0
fi

case "$here" in
    "$main/.claude/worktrees/"*) ;;
    *)
        echo "Worktrees must live in $main/.claude/worktrees/<name> for the site to serve them."
        exit 1
        ;;
esac

name="$(basename "$here")"
echo "Worktree: $name"

# node_modules: a real install, hard-linked from pnpm's store (a couple of seconds).
# pnpm refuses to run scripts against a symlinked node_modules.
(cd "$here" && pnpm install --prefer-offline --frozen-lockfile --silent)
echo "Installed node_modules"

# vendor: copy, never `composer install` here. composer.json's installer-paths are
# relative, so an install from a worktree would put plugins inside .claude/.
if [ ! -d "$here/vendor" ]; then
    rsync -a "$main/vendor/" "$here/vendor/"
    echo "Copied vendor"
fi
if ! cmp -s "$here/composer.lock" "$main/composer.lock"; then
    echo "Warning: composer.lock differs from main. vendor is main's copy; run composer install in the main checkout after merging."
fi
(cd "$here" && composer dump-autoload -q)
echo "Regenerated autoloader"

[ -f "$here/.env" ] || cp "$main/.env" "$here/.env"
[ -f "$main/auth.json" ] && [ ! -f "$here/auth.json" ] && cp "$main/auth.json" "$here/auth.json"

(cd "$here" && pnpm run --silent build > /dev/null)
echo "Built assets"

# The mu-plugin always links to the main checkout's copy.
mu_dir="$(cd "$main" && wp eval 'echo WPMU_PLUGIN_DIR;' 2>/dev/null || true)"
if [ -z "$mu_dir" ]; then
    echo "Warning: WP-CLI couldn't load WordPress from $main. Link dev-scripts/mu-plugins/gust-worktree-switch.php into mu-plugins yourself."
elif [ ! -e "$mu_dir/gust-worktree-switch.php" ]; then
    mkdir -p "$mu_dir"
    ln -s "$main/dev-scripts/mu-plugins/gust-worktree-switch.php" "$mu_dir/gust-worktree-switch.php"
    echo "Linked worktree switch mu-plugin into $mu_dir"
fi

app_url="$(grep '^APP_URL' "$here/.env" | cut -d= -f2)"
echo
echo "Ready. Browse: $app_url/?worktree=$name   (sets a cookie; ?worktree=main clears it)"
echo "       WP-CLI: run wp from inside this worktree"
