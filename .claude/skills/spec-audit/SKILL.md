---
name: spec-audit
description: Audit the codebase against the website specification and update the spec to match reality. Use when syncing the spec with implemented code, checking for drift, or identifying outstanding work. Trigger keywords: audit, spec audit, sync spec, spec drift, outstanding.
---

# Spec Audit

Audits the codebase against `.docs/_WEBSITE-SPEC.md` and brings the spec inline with what has been implemented. The **code is the source of truth** — discrepancies mean the spec needs updating, not the code.

## How to Run

1. Read the spec (`.docs/_WEBSITE-SPEC.md`)
2. List all components (`ls components/`)
3. Split work across **parallel sub-agents** (4-8 components per agent) plus one agent for non-component sections
4. Synthesise findings and present to the user
5. On approval, apply updates to the spec

## Sub-Agent Groups

Use `feature-dev:code-explorer` agents. Each agent should read the PHP class, template, block.json, and ACF field group JSON for its assigned components.

### Agent 1: Trip Single Components (group 1)
- `trip-page-header`, `trip-section-nav`, `trip-highlights`, `trip-itinerary-preview`, `trip-accommodation-preview`, `trip-single`

### Agent 2: Trip Single Components (group 2)
- `trip-includes`, `trip-getting-there`, `trip-reviews`, `trip-related-stories`, `trip-related-trips`, `trip-dates`, `trip-get-in-touch`

### Agent 3: Cards & Block Components
- `cards`, `card`, `trip-cards`, `trip-card`, `accordion`, `banner`

### Agent 4: Remaining Spec Components + Unspecced Components
- Audit spec-listed components: `logo-grid`, `media-content`, `quote`, `page-header`, `taxonomy-filters`
- Identify all components that exist in code but are NOT in the spec — categorise as "theme infrastructure" vs "content components that should be in the spec"

### Agent 5: Content Types, Taxonomies, Routes, Settings, Menus
- Check `Theme/PostTypes/` (or `Theme/Modules/*/PostType.php`) against spec Content Types
- Check `Theme/Taxonomies/` (or `Theme/Modules/*/...Taxonomy.php`) against spec Taxonomies
- Check `Theme/Routes/routes.php` against spec Routes
- Check ACF options pages (`acf-json/group_*.json`) against spec Site Settings
- Check menu registration (`Theme/Modules/Core/Menus.php`) against spec Menus

## Per-Component Audit Checklist

For each component, the agent should check:

- [ ] Does the component exist and function as the spec describes?
- [ ] Is the block status correct (`[Block]` vs `[Partial]`)? Check for `block.json`.
- [ ] Are the ACF field names, types, and options accurate? Cross-reference `group_component_*.json`.
- [ ] Are there fields/features in the code NOT mentioned in the spec?
- [ ] Are there fields/features in the spec NOT implemented in code?
- [ ] Is the render rule / validation logic accurate?
- [ ] Is the layout description accurate?

## Output Format

Each agent should return a structured report per component:

```
### Component Name
**Status**: Matches spec / Spec needs update / Missing from spec
**Discrepancies**: [specific differences]
**Outstanding items**: [things the spec says should exist but don't in code]
```

## After Audit

1. Present a consolidated summary to the user with:
   - All spec updates needed (grouped by section)
   - Genuinely outstanding items (spec describes it but code hasn't implemented it)
2. Wait for user confirmation before applying changes
3. Apply updates section by section, tracking via tasks
