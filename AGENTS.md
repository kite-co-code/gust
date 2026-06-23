# CLAUDE.md

Gust is a WordPress development framework built on top of the WordPress ecosystem, designed to provide a modern development experience while adhering to WordPress best practices.

Key features include:
- **Components**: Typed PHP classes (`Gust\Components\*`) with `::make()` factories, optional `validate()` and `transform()` hooks. Auto-discovered from `components/`. Editor integration via ACF blocks (`block.json` per component).
- **Module System**: Encapsulated theme features in `Theme/Modules/*/module.php`, each with a static `init()`. Auto-loaded; disable via `gust/modules/disabled` filter.
- **Router**: `Theme/Routes/routes.php` handles owned routes and WordPress archive decoration via `Gust\Router`.
- **ACF Pro**: Custom fields, option pages, and editor blocks. Field groups managed as local JSON in `acf-json/`. Blocks render via `ComponentName::fromBlock()`.
- **CSS Architecture**: Tailwind + BEM hybrid. Tailwind for layout utilities in templates (`flex`, `gap-4`); per-component `styles.pcss` (BEM) for component styles. Custom type, color, and spacing systems — never use raw Tailwind typography or color classes in components.
- **Build System**: Vite with per-component entry points. Component `styles.pcss` and `scripts.js` are auto-bundled into the main CSS/JS.
- **Deployment**: Single-command build and deploy to staging/production; database and asset sync between environments via WP Sync CLI. Don't run these unless specifically requested.

### MCPs to use:
- **context7** — look up library/framework docs before implementing with any SDK or package
- **chrome-devtools** — browser testing; navigate, snapshot DOM, check console/network

### Testing
- **PHP errors**: `cat ../../debug.log` after page load
- **WP Login**: If you need to log in but don't have credentials, create a user via WP CLI: `wp user create testadmin --role=administrator --user_pass=strongpassword`
- **WP data**: WP CLI — run with `--path=../../../../` (e.g. `wp post-type list`)
- **Rendering/UI**: Chrome DevTools MCP — navigate, snapshot DOM, check console. Prefer DOM over screenshots unless visual testing is needed.
- **Visual changes**: Take a screenshot and ask the user to confirm before marking done.
- Get `APP_URL` from `.env`: `APP_URL=$(grep '^APP_URL' .env | cut -d= -f2)`
- Component previews: `$APP_URL/_dev/`


### Reference

| Doc | When to read |
|-----|-------------|
| [architecture.md](.claude/agent_docs/architecture.md) | Theme init flow, module system internals, WP integration |
| [build-system.md](.claude/agent_docs/build-system.md) | Vite setup, entry points, component asset bundling, static assets, versioning |
| [components.md](.claude/agent_docs/components.md) | Component structure, class patterns, templates, ACF fields, blocks |
| [css.md](.claude/agent_docs/css.md) | CSS architecture, color system, spacing, type utilities, page grid |

## Skills
- `.claude/skills/gust-dev/` — Dev workflows (components, CSS, testing, setup, build)
- `.claude/skills/website-spec/` — Used for writing a WEBSITE_SPEC.md defining pages, routes, templates, and components for a project.
- `.claude/skills/brand-expert/` — SwimQuest brand compliance: colour contexts, type utilities, spacing rules. Use when reviewing components for brand, applying brand values, or recording brand changes. **When brand values change, always invoke this skill to update swimquest-brand.md.**
- `.claude/skills/gust-upstream-sync/` — Contributing core theme fixes back to [kite-co/gust](https://github.com/kite-co-code/gust). Use when you identify and fix bugs or improvements to core Gust functionality. Ensures fixes are documented agnostically (not project-specific) and properly contributed for Jerome to review and merge.
- `.claude/skills/spec-audit/` — Audit codebase against the website spec. Splits work across parallel sub-agents, reports discrepancies, and updates the spec to match reality. Use when syncing the spec after development work.
