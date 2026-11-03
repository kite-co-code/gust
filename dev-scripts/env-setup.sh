#!/bin/bash -e

# -----------------------------------------------------------------------------
# Gust Environment Setup
#
# Writes WP_ENVIRONMENT_TYPE into wp-config.php from the theme's .env (default:
# development), so it's defined before plugins and mu-plugins load and call
# wp_get_environment_type(), which caches its first answer.
#
#   pnpm env:setup
#   bash dev-scripts/env-setup.sh [--env-file=<path>] [--config-file=<path>]
#
# --env-file reads another .env, --config-file writes another wp-config.php
# (handy for testing against a copy). Safe to re-run: it only writes when the
# value changes. Also run by dev-scripts/site-setup.sh.
# -----------------------------------------------------------------------------

# The shebang's -e is ignored when run as `bash env-setup.sh`.
set -eo pipefail

here="$(cd "$(dirname "$0")/.." && pwd)"
env_file="$here/.env"
config_args=()
config_label="wp-config.php"

for arg in "$@"; do
    case "$arg" in
        --env-file=*) env_file="${arg#--env-file=}" ;;
        --config-file=*)
            config_args+=("$arg")
            config_label="${arg#--config-file=}"
            ;;
        *)
            echo "Unknown option: $arg"
            exit 1
            ;;
    esac
done

# The WordPress root is the nearest parent with wp-load.php. Works from the main
# checkout and from a worktree (.claude/worktrees/<name>) alike.
wp_root="$here"
while [ "$wp_root" != "/" ] && [ ! -f "$wp_root/wp-load.php" ]; do
    wp_root="$(dirname "$wp_root")"
done
if [ ! -f "$wp_root/wp-load.php" ]; then
    echo "Couldn't find WordPress (wp-load.php) above $here."
    exit 1
fi

value=""
if [ ! -f "$env_file" ]; then
    echo "No $env_file, using development."
else
    value="$(grep '^WP_ENVIRONMENT_TYPE=' "$env_file" | tail -1 | cut -d= -f2- | tr -d "\"' \r\t" || true)"
    [ -n "$value" ] || echo "No WP_ENVIRONMENT_TYPE in $env_file, using development."
fi
value="${value:-development}"

case "$value" in
    local | development | staging | production) ;;
    *)
        echo "Invalid WP_ENVIRONMENT_TYPE '$value' in $env_file. Use local, development, staging or production."
        exit 1
        ;;
esac

wp_config() {
    wp config "$@" "${config_args[@]}" --path="$wp_root"
}

current=""
if wp_config has WP_ENVIRONMENT_TYPE 2>/dev/null; then
    current="$(wp_config get WP_ENVIRONMENT_TYPE 2>/dev/null)"
fi

if [ "$current" = "$value" ]; then
    echo "WP_ENVIRONMENT_TYPE is already '$value' in $config_label. Nothing to do."
    exit 0
fi

wp_config set WP_ENVIRONMENT_TYPE "$value" --type=constant --quiet

if [ -z "$current" ]; then
    echo "Added WP_ENVIRONMENT_TYPE '$value' to $config_label."
else
    echo "Changed WP_ENVIRONMENT_TYPE in $config_label from '$current' to '$value'."
fi
