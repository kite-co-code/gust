#!/bin/bash

# Rsync deploy script
# Reads config from wp-sync.yml, falls back to .env
# Usage: ./dev-scripts/deploy.sh <environment> [--build] [--dry-run]
#
# Refuses to deploy uncommitted or untracked files. Production must be on main
# and match origin/main.
#
# --build runs `pnpm run build:production` after the git checks and restores
# dev dependencies (`composer install`) afterwards, even if the deploy fails.
# The pnpm deploy:* scripts use it.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$THEME_DIR/wp-sync.yml"
ENV_FILE="$THEME_DIR/.env"
ENV=""
DRY_RUN=""
BUILD=""
PRODUCTION_BRANCH="main"

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN="--dry-run" ;;
        --build) BUILD="1" ;;
        -*)
            echo "Error: Unknown option '$arg'"
            exit 1
            ;;
        *) ENV="$arg" ;;
    esac
done
ENV="${ENV:-staging}"

# Fail unless git status is empty: no uncommitted changes, no untracked
# (non-ignored) files. rsync deploys the working copy, not the commit.
require_clean_tree() {
    local changes
    changes=$(git -C "$THEME_DIR" status --porcelain)
    if [[ -n "$changes" ]]; then
        echo "Error: $1"
        echo ""
        echo "$changes"
        echo ""
        echo "Commit, stash or remove these files, then deploy again."
        exit 1
    fi
}

preflight() {
    if ! git -C "$THEME_DIR" rev-parse --git-dir > /dev/null 2>&1; then
        echo "Error: $THEME_DIR is not a git repository. Deploys must come from a git checkout."
        exit 1
    fi

    # A worktree deploys under its folder name and shares the main checkout's
    # composer paths, so only deploy from the main checkout.
    if [[ "$(git -C "$THEME_DIR" rev-parse --absolute-git-dir)" != "$(git -C "$THEME_DIR" rev-parse --path-format=absolute --git-common-dir)" ]]; then
        echo "Error: This is a git worktree. Deploy from the main checkout."
        exit 1
    fi

    require_clean_tree "The working tree has uncommitted or untracked files:"

    local branch commit
    branch=$(git -C "$THEME_DIR" symbolic-ref --short -q HEAD || echo "(detached HEAD)")
    commit=$(git -C "$THEME_DIR" log -1 --format='%h %s')

    if [[ "$ENV" == "production" ]]; then
        if [[ "$branch" != "$PRODUCTION_BRANCH" ]]; then
            echo "Error: Production deploys must be on '$PRODUCTION_BRANCH' (currently on '$branch')."
            exit 1
        fi

        echo "Fetching origin/$PRODUCTION_BRANCH..."
        if ! git -C "$THEME_DIR" fetch --quiet origin "$PRODUCTION_BRANCH"; then
            echo "Error: Couldn't fetch origin/$PRODUCTION_BRANCH."
            exit 1
        fi

        local ahead behind
        ahead=$(git -C "$THEME_DIR" rev-list --count "origin/$PRODUCTION_BRANCH..HEAD")
        behind=$(git -C "$THEME_DIR" rev-list --count "HEAD..origin/$PRODUCTION_BRANCH")
        if [[ "$ahead" != "0" ]] || [[ "$behind" != "0" ]]; then
            echo "Error: '$PRODUCTION_BRANCH' doesn't match origin/$PRODUCTION_BRANCH ($ahead ahead, $behind behind). Push or pull first."
            exit 1
        fi
    fi

    echo "Deploying branch: $branch"
    echo "           commit: $commit"
    echo ""
}

# After a --build, put back the dev packages that `composer install --no-dev` removed
# (Faker, Pint, stubs, Query Monitor). Keeps the deploy's exit status.
restore_dev_dependencies() {
    local status=$?
    echo ""
    echo "Restoring dev dependencies (composer install)..."
    if ! (cd "$THEME_DIR" && composer install --no-interaction --quiet); then
        echo "Warning: composer install failed. Run it yourself to restore dev dependencies."
    fi
    exit $status
}

# Parse wp-sync.yml for environment config
parse_yaml_env() {
    local env=$1
    local field=$2
    if [[ -f "$CONFIG_FILE" ]]; then
        grep -A 10 "^[[:space:]]*${env}:" "$CONFIG_FILE" 2>/dev/null | grep "^[[:space:]]*${field}:" | head -1 | sed 's/.*:[[:space:]]*//' | tr -d '"' | tr -d "'"
    fi
}

# Get value from .env file
get_env_var() {
    local var_name=$1
    if [[ -f "$ENV_FILE" ]]; then
        grep "^${var_name}=" "$ENV_FILE" 2>/dev/null | cut -d '=' -f2- | tr -d '"' | tr -d "'"
    fi
}

# Get config: try wp-sync.yml first, then .env
ENV_UPPER=$(echo "$ENV" | tr '[:lower:]' '[:upper:]')

HOST=$(parse_yaml_env "$ENV" "host")
[[ -z "$HOST" ]] && HOST=$(get_env_var "DEPLOY_${ENV_UPPER}_HOST")

PORT=$(parse_yaml_env "$ENV" "port")
[[ -z "$PORT" ]] && PORT=$(get_env_var "DEPLOY_${ENV_UPPER}_PORT")

REMOTE_PATH=$(parse_yaml_env "$ENV" "path")
[[ -z "$REMOTE_PATH" ]] && REMOTE_PATH=$(get_env_var "DEPLOY_${ENV_UPPER}_PATH")

THEME_NAME=$(get_env_var "THEME_NAME")
[[ -z "$THEME_NAME" ]] && THEME_NAME=$(basename "$THEME_DIR")

if [[ -z "$HOST" ]] || [[ -z "$REMOTE_PATH" ]]; then
    echo "Error: Environment '$ENV' not configured"
    echo ""
    echo "Configure in wp-sync.yml:"
    echo "  environments:"
    echo "    $ENV:"
    echo "      host: user@hostname"
    echo "      port: 22  # optional"
    echo "      path: /path/to/wordpress"
    echo ""
    echo "Or in .env:"
    echo "  DEPLOY_${ENV_UPPER}_HOST=user@hostname"
    echo "  DEPLOY_${ENV_UPPER}_PORT=22"
    echo "  DEPLOY_${ENV_UPPER}_PATH=/path/to/wordpress"
    echo "  THEME_NAME=my-theme"
    exit 1
fi

preflight

if [[ -n "$BUILD" ]]; then
    trap restore_dev_dependencies EXIT
    (cd "$THEME_DIR" && pnpm run build:production)
    echo ""
    require_clean_tree "The build changed tracked files. Commit the result and deploy again:"
fi

REMOTE_THEME_PATH="$REMOTE_PATH/wp-content/themes/$THEME_NAME"

# Build rsync command
RSYNC_OPTS="-avz --delete"
SSH_OPTS=""
if [[ -n "$PORT" ]]; then
    SSH_OPTS="-e 'ssh -p $PORT'"
fi

echo "Deploying to $ENV..."
echo "  Host: $HOST"
echo "  Path: $REMOTE_THEME_PATH"
if [[ -n "$DRY_RUN" ]]; then
    echo "  Mode: DRY RUN"
fi
echo ""

# Run rsync
eval rsync $RSYNC_OPTS $SSH_OPTS $DRY_RUN \
    --exclude-from="$THEME_DIR/.deployignore" \
    "$THEME_DIR/" \
    "$HOST:$REMOTE_THEME_PATH"

# Flush cache (skip on dry run)
if [[ -z "$DRY_RUN" ]]; then
    echo ""
    echo "Flushing WP cache..."
    if [[ -n "$PORT" ]]; then
        ssh -p "$PORT" "$HOST" "cd $REMOTE_PATH && wp cache flush" 2>/dev/null || echo "Cache flush skipped"
    else
        ssh "$HOST" "cd $REMOTE_PATH && wp cache flush" 2>/dev/null || echo "Cache flush skipped"
    fi
fi

echo ""
echo "Deploy to $ENV complete!"
