---
name: brand-expert
description: SwimQuest brand compliance and review. Use when checking if something is on-brand, reviewing component styling, applying brand colours/typography, or recording brand changes. Trigger keywords: on-brand, brand check, brand review, brand colours, brand fonts, typography, colour context, brand update.
---

# SwimQuest Brand Expert

Reviews and enforces SwimQuest brand standards across the codebase.

## Source of Truth

Always read these files before making brand decisions:

1. **[swimquest-brand.md](../../agent_docs/swimquest-brand.md)** — canonical brand reference (colours, type, spacing)
2. **[assets/theme-config.json](../../../../assets/theme-config.json)** — live colour definitions
3. **[assets/styles/3-patterns/_type-styles.pcss](../../../../assets/styles/3-patterns/_type-styles.pcss)** — live type utilities
4. **[css.md](../../agent_docs/css.md)** — CSS architecture rules

## Workflows

### Brand Review

When asked to review a component or template for brand compliance:

1. Read the file(s) in question
2. Check against swimquest-brand.md for:
   - **Colours**: raw hex values used instead of `color-context-*` or `var(--color-*)` → flag and correct
   - **Typography**: raw font-size/font-family/font-weight instead of `type-*` utilities → flag and correct
   - **Spacing**: magic number px values instead of `space()`, `spaceFluid()`, or layout tokens → flag and correct
   - **Background colours**: `background-color` set without a matching `color-context-*` → flag and correct
3. Report issues clearly and offer to fix them

### Apply Brand

When asked to implement a design with brand values:

1. Read swimquest-brand.md for available colours and type utilities
2. Match Figma/design colours to the nearest brand colour key — never introduce new raw hex values
3. Use `color-context-{key}` for any section with a background colour
4. Use `type-*` utilities for all text — never raw Tailwind typography classes
5. Use `space()` / `spaceFluid()` / layout tokens for all spacing

### Update Brand

When brand values change (new colour, font change, type scale update):

1. Apply the change in the appropriate source file:
   - Colours → `assets/theme-config.json`
   - Type scale → `assets/styles/3-patterns/_type-styles.pcss`
   - Fonts → `assets/styles/2-base/_fonts.pcss` + `assets/styles/1-theme/_tailwind-theme.pcss`
2. **Immediately update `swimquest-brand.md`** to reflect the new values
3. Note in your response what changed and what downstream templates may be affected

### Visual Check

Use Chrome DevTools MCP to verify visually:

```
APP_URL=$(grep '^APP_URL' .env | cut -d= -f2)
# Navigate to /_dev/globals/ for colours
# Navigate to /_dev/utilities/ for type scale
# Navigate to /_dev/components/{name}/ for component
```

Take a screenshot if the user needs to confirm a visual change.

## Brand Rules (Quick Reference)

**Colours:**
- Dark backgrounds (navy, mid-blue, black): white text, white links
- Light backgrounds (bright-blue, orange, neutral, light, white): dark text, mid-blue or black links
- Always use `color-context-{key}` — never set `background-color` alone
- Never introduce raw hex values — map to existing palette keys

**Typography:**
- H1 / hero: League Gothic, always uppercase
- H2: Inter 700, always uppercase
- H3–H6: Inter 700, sentence case
- Body: Inter 400 (`type-base`, `type-large`, `type-small`)
- Never use `font-family`, `font-size`, or `font-weight` in templates — use `type-*` utilities

**Spacing:**
- All spacing via `space()`, `spaceFluid()`, or CSS custom properties
- Section gaps: `var(--space-layout)`
- Text gaps: `var(--space-base)`
- Never hard-code pixel values in component CSS
