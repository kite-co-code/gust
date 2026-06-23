# Gust Upstream Contribution Template

Use this template when contributing fixes or improvements to the Gust theme back to kite-co/gust.

## Pre-Contribution Checklist

- [ ] Issue identified and understood
- [ ] Fix developed and tested locally
- [ ] Changes committed with clear message
- [ ] Knock-on effects analyzed

## Issue Template

```markdown
## Problem

[Clear, non-project-specific description of what's broken or could be improved]

Example: "The WordPress admin bar does not appear in the header on pages using owned routes when a user is logged in."

## Root Cause

[Explanation of why this happens, including WordPress execution order or architectural details]

Example: "Owned routes are matched on the `parse_request` hook (priority 1), which fires before `wp_loaded`. The admin bar depends on `wp_loaded` to initialize."

## Solution

[How the fix works and why it solves the problem]

Example: "Move owned route matching to `template_redirect` hook (priority 0) to allow WordPress to fully initialize."

### Changes Made

- [Specific change 1]
- [Specific change 2]

## Impact Analysis

### Verified ✓
- [Tested behavior 1]
- [Tested behavior 2]

### Potential Considerations

1. **Consideration 1**: [Description and implications]
2. **Consideration 2**: [Description and implications]

## Testing Recommendations

- [ ] [Specific test case 1]
- [ ] [Specific test case 2]
```

## PR Template

```markdown
## Summary

[Brief explanation of what this PR does and why it's needed]

## Changes

- [Change 1]
- [Change 2]

## Testing

- ✓ [Test 1 - passed]
- ✓ [Test 2 - passed]

## Related Issues

Fixes #[issue number]

See issue #[issue number] for detailed analysis.
```

## Issue Content Examples

### Good Issue Description (Agnostic)

```
## Problem

"Decorated routes that render custom templates inherit WordPress page/post classes in the body tag, which can conflict with theme styling that assumes archive page semantics."

## Root Cause

"The `body_class()` function returns classes based on the global $wp_query, which reflects the matched post/page rather than the template intent."

## Solution

"Add filter to controlled decoration of body classes for decorated routes, allowing themes to define expected classes for each route type."
```

### Bad Issue Description (Project-Specific)

```
"The calendar page has the wrong CSS classes"
"Trip styles archive looks broken"
"Events page has styling issues"
```

## Common Issues to Watch For

1. **Rewrite Rules**: Does the change affect WordPress rewrite rule processing?
2. **Query Variables**: Does the change affect global $wp or $wp_query?
3. **Hook Priorities**: Are hook priorities correct relative to other WordPress callbacks?
4. **Post Types/Taxonomies**: Could this conflict with custom post types or taxonomies?
5. **Conditional Tags**: Will is_*() functions work correctly?
6. **Backwards Compatibility**: Will existing sites/projects continue to work?

## Commit Message Format

```
fix: [generic description of what was fixed]

[Extended explanation of the problem, solution, and why this approach was chosen]

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>
```

Example:

```
fix: ensure admin bar displays on owned route templated pages

Move owned route matching from parse_request hook to template_redirect hook with
higher priority. This allows WordPress to fully initialize before template
rendering, enabling the admin bar and other WordPress features to display
correctly on owned routes.

Previously, owned routes were matched and dispatched too early in the WordPress
execution cycle (parse_request), before wp_loaded had fired. This prevented the
admin bar from being initialized.

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>
```

## Reference

- Gust Repository: https://github.com/kite-co-code/gust
- Skill Documentation: `.claude/skills/gust-upstream-sync/README.md`
