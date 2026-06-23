# SwimQuest Brand Reference

Canonical brand reference for development. Keep this in sync with `assets/theme-config.json` and `assets/styles/3-patterns/_type-styles.pcss`. When brand changes are made, update this document.

---

## Colors

Defined in `assets/theme-config.json`. Processed at build time into CSS custom properties and utility classes.

### Palette

| Key | Hex | Name | Foreground | Links | Link Hover |
|-----|-----|------|------------|-------|------------|
| `navy` | `#274472` | Navy | white | white | white |
| `mid-blue` | `#057499` | Mid Blue | white | white | white |
| `bright-blue` | `#46BAEB` | Bright Blue | black | black | white |
| `orange` | `#FFBD59` | Orange | black | black | white |
| `neutral` | `#FFF9F0` | Neutral | black | black | white |
| `light` | `#E5E5E5` | Light | navy | mid-blue | navy |
| `white` | `#ffffff` | White | navy | mid-blue | navy |
| `black` | `#24323E` | Near Black | white | bright-blue | white |

### Named Aliases

| Alias | Points to | Use for |
|-------|-----------|---------|
| `accent` | `mid-blue` | Interactive colour, buttons, focus rings |
| `brand-2` | `bright-blue` | Secondary accent |
| `foreground` | `navy` | Default body text |
| `background` | `white` | Default page background |

### Usage in CSS

```pcss
/* Always use color-context for background sections — sets bg + fg + links together */
.my-section {
    @apply color-context-navy;
}

/* Raw colour variable — only for specific one-off values */
.my-element {
    border-color: var(--color-light);
}

/* Text colour only, no background */
.my-label {
    @apply foreground-from-navy;
}
```

**Available utilities per colour:**
- `.color-context-{key}` — background + foreground + links
- `.has-{key}-background-color` — WordPress block editor alias (same as above)
- `.foreground-from-{key}` — text/link colour only, no background
- `var(--color-{key})` — raw hex
- `var(--color-{key}--foreground)` — contrasting text colour

---

## Typography

### Fonts

| Variable | Font | File | Use |
|----------|------|------|-----|
| `--font-display` | League Gothic 400 | `assets/fonts/LeagueGothic-Regular.woff2` | H1 / hero display |
| `--font-sans` | Inter (variable 100–900) | `assets/fonts/Inter.woff2` | All body + H2–H6 |

### Type Scale

Defined in `assets/styles/3-patterns/_type-styles.pcss`. Use `@apply` in component CSS or directly as classes in templates.

| Utility | Font | Mobile | Desktop | Weight | Notes |
|---------|------|--------|---------|--------|-------|
| `type-h1` / `type-hero` | League Gothic | 64px | 96px | 400 | uppercase, lh 0.9 |
| `type-h2` | Inter | 26px | 44px | 700 | uppercase, lh 1.31→1.0 |
| `type-h3` | Inter | 24px | 40px | 700 | lh 1.25, ls -0.5px |
| `type-h4` | Inter | 22px | 32px | 700 | lh 1.25, ls -0.5px |
| `type-h5` | Inter | 18px | 24px | 700 | lh 1.33 |
| `type-h6` | Inter | 16px | 18px | 700 | lh 1.44 |
| `type-large` | Inter | 18px | 22px | 400 | body large, lh 1.32, ls -0.5px |
| `type-regular` | Inter | 18px | — | 400 | body regular, lh 1.39, ls -0.5px |
| `type-small` | Inter | 16px | — | 400 | body small, lh 1.375, ls -0.2px |
| `type-meta` | Inter | 16px | — | 500 | small-medium, lh 1.375, ls -0.2px |
| `type-button` | Inter | 18px | — | 700 | lh 1.33 |

### Usage

```pcss
/* In component CSS */
.component__heading {
    @apply type-h2;
}

.component__body {
    @apply type-regular;
}
```

```php
<!-- In templates — layout classes only alongside type utilities -->
<h2 class="type-h2"><?= $title ?></h2>
<p class="type-large"><?= $lead ?></p>
```

**Rules:**
- Never use raw Tailwind font/size classes (`text-xl`, `font-bold`) — always use `type-*` utilities
- `type-h1` and `type-hero` are identical — prefer `type-h1` for headings, `type-hero` for marketing hero sections

---

## Spacing

```pcss
.component {
    padding: space(16);            /* 1rem — px to rem */
    gap: spaceFluid(16, 32);       /* fluid clamp between breakpoints */
}
```

| Token | Value | Use |
|-------|-------|-----|
| `var(--space-layout)` | 32px → 64px fluid | Between sections/blocks |
| `var(--container-padding)` | 16px → 30px fluid | Page side padding |
| `var(--content-gutter)` | 20px → 80px responsive | Content grid and fluid width gutters |
| `var(--content-grid--gap)` | 1.25rem (20px) | Content grid column gap |
| `var(--space-base)` | 16px | Between inline text elements |

**Mobile alignment:** At mobile, `--content-gutter` (20px) matches `--content-grid--gap` (20px), so the content-grid's wide area starts 20px from the viewport edge. Components outside the grid that need to align with grid content on mobile should constrain their inner wrapper with `max-width: calc(100% - 2 * var(--content-grid--gap))` when they are intentionally using the smaller mobile-only inset.

**Desktop alignment:** At large screens, `--content-gutter` becomes 80px, and both `content-grid` and `content-width-fluid-*` utilities use that gutter for their side inset.

---

## Component Patterns

### Cards on taxonomy term pages

Use the taxonomy-only cards treatment for term archives that list card links:

- `cards--taxonomy-term-grid` on the `cards` block
- 1 column on small, 2 columns on `md`, 3 columns on `lg`
- cards should fill their grid cell, so remove the default card width cap for this context

Keep the generic Gutenberg cards block unchanged elsewhere. It can continue using the standard 4-column desktop layout outside term pages.

---

## Dev Kit

Visual reference at `/_dev/` (development environment only).

| Route | Shows |
|-------|-------|
| `/_dev/globals/` | Colour swatches + all colour contexts |
| `/_dev/utilities/` | Type scale, buttons, patterns, colour contexts |
| `/_dev/components/` | Individual component examples |
| `/_dev/all-components/` | All components on one page |

---

## Update Protocol

When brand values change, update this file to stay in sync:

- **Colors changed** → Update the Palette table + Named Aliases
- **Type scale changed** → Update the Type Scale table
- **New font added** → Update the Fonts table + note the file path
- **Spacing tokens changed** → Update the Spacing table

Also check that `assets/theme-config.json` and `assets/styles/3-patterns/_type-styles.pcss` match this document.
