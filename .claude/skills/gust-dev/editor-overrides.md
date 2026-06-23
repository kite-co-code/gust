# Editor Style Overrides

How to fix component styles that break in the WordPress block editor.

## The Problem

The Gust `Button` component template always adds the `button` class:

```php
<!-- components/button/template.php -->
<button class="<?= classes('button', $this->classes) ?>" ...>
```

In the block editor, WordPress admin CSS applies `.wp-core-ui .button` to any element with the `button` class. This rule has specificity **(0,2,0)** and overrides theme editor styles — which use `:where()` to keep specificity at **(0,1,0)**.

**Symptoms:**
- Button-based interactive elements show admin theme color (red/orange on Midnight theme) instead of the brand color
- `display: inline-block` applied instead of `display: grid` or `display: flex`
- Pseudo-element layout (`::before`, `::after`) breaks because the parent is no longer a grid/flex container

## The Fix

Add overrides in `components/editor/_editor.pcss` with specificity **(0,3,0)** to beat `.wp-core-ui .button`:

```pcss
/* Override WP admin .button styles on [component] header */
.component-name .component-name__item .component-name__button {
    display: grid; /* or flex — whatever the component needs */
    color: var(--color-foreground);
    background-color: var(--color-neutral); /* if needed for visual consistency */
}
```

Three class selectors = (0,3,0) > (0,2,0). No `!important` needed.

## Diagnosis Steps

When a component looks wrong in the editor, run this in DevTools console:

```js
// Check computed styles and which rules are winning
const el = document.querySelector('.your-component__button');
const styles = getComputedStyle(el);
console.log({ display: styles.display, color: styles.color });

// Find the conflicting rule
Array.from(document.styleSheets).forEach(sheet => {
    try {
        Array.from(sheet.cssRules).forEach(rule => {
            if (rule.selectorText && el.matches(rule.selectorText)) {
                const d = rule.style?.display, c = rule.style?.color;
                if (d || c) console.log({ selector: rule.selectorText, display: d, color: c, href: sheet.href });
            }
        });
    } catch(e) {}
});
```

If you see `.wp-core-ui .button` in the results, apply the fix above.

## Why `:where()` Makes This Worse

Our component styles compile with `:where()` wrappers that drop specificity to 0:

```css
/* Compiled output — effective specificity: (0,1,0) */
:where(.editor-styles-wrapper) .accordion :where(.accordion__item) :where(.accordion__item__header) {
    display: grid;
}
```

This is intentional for front-end cascade flexibility, but means WP admin styles can easily win in the editor.

## Affected Components

Any component that renders via `Gust\Components\Button::make()` is affected when used inside a parent component (the `button` class clashes with `.wp-core-ui .button`). Top-level button components are fine — they only show on the front end or in the block toolbar.

Current fixes in `_editor.pcss`:
- **Accordion** — `.accordion .accordion__item .accordion__item__header`
