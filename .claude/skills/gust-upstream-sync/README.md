# Gust Upstream Sync Skill

Streamlined workflow for identifying, fixing, and contributing core Gust theme functionality improvements back to the [kite-co/gust](https://github.com/kite-co-code/gust) upstream repository.

## Purpose

While building SwimQuest on top of Gust, you may discover bugs or improvements to core theme functionality. This skill provides a repeatable process to:

1. Identify issues in core theme behavior
2. Develop and test fixes locally
3. Document issues and solutions agnostically (not project-specific)
4. Create GitHub issues in kite-co/gust
5. Create pull requests for Jerome's review and merge

## Workflow

### 1. Identify the Issue

```
Problem: [Describe what's broken or could be improved]
Affected: [Which core theme components/features are affected]
Impact: [How does this affect theme usage]
```

### 2. Fix the Issue Locally

- Develop the fix in your local theme files
- Test thoroughly across different scenarios
- Commit the fix with clear message: `fix: [what was fixed]`

### 3. Audit Knock-On Effects

Before contributing, audit potential side effects:

- Check for rewrite rule conflicts
- Verify slug/post type interactions
- Test with different WordPress hooks and filters
- Ensure backwards compatibility

Common areas to check:
- Router behavior and priority
- Template loading and resolution
- Rewrite rules and post type registration
- Database queries and caching
- User permission checks

### 4. Create Generic Issue Documentation

Document the issue WITHOUT project-specific details:

**Good** (Agnostic):
```
"Admin bar does not appear on owned route pages when user is logged in"
```

**Bad** (Project-specific):
```
"Admin bar not showing on /calendar/ and /trip-styles/ pages"
```

Use examples like:
- "pages using owned routes" instead of specific routes
- "custom fields" instead of specific ACF field names
- "post type archives" instead of specific post types
- "templated pages" instead of specific template names

### 5. Create GitHub Issue

Use the issue template format:
- **Problem**: Clear description of the issue
- **Root Cause**: Why this happens
- **Solution**: How it's fixed
- **Impact Analysis**: What could break
- **Testing**: How to verify the fix works

### 6. Create Pull Request

- Base: `main`
- Head: Your feature branch from your fork
- Description: Links to the issue, explains changes

## Example

See the admin-bar fix for a complete example:

- Issue: https://github.com/kite-co-code/gust/issues/4
- PR: https://github.com/kite-co-code/gust/pull/5

## Commands

### Create Issue

```bash
gh issue create \
  --repo kite-co-code/gust \
  --title "fix: [generic description]" \
  --body "[issue content]" \
  --label "bug"
```

### Create Pull Request

```bash
gh pr create \
  --repo kite-co-code/gust \
  --base main \
  --head ben-meyer:[your-branch] \
  --title "fix: [description]" \
  --body "[PR content]"
```

## Best Practices

1. **Be Agnostic**: Don't mention SwimQuest, specific routes, or project details
2. **Be Thorough**: Document potential knock-on effects explicitly
3. **Be Clear**: Explain *why* the change is needed, not just *what* changed
4. **Be Testable**: Provide clear steps to verify the fix
5. **Be Backwards Compatible**: Ensure no existing functionality breaks

## Checklist

Before creating a PR:

- [ ] Fix is tested and working locally
- [ ] Commit message is clear and descriptive
- [ ] Issue description is agnostic/generic
- [ ] Knock-on effects are identified and documented
- [ ] PR includes testing recommendations
- [ ] No project-specific references in PR/issue
- [ ] Changes are backwards compatible

## Need Help?

Refer to previous contributions and the upstream repository's contribution guidelines.
