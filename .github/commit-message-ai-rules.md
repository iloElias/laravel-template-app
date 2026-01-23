# Commit Message AI Rules

This document defines the rules and guidelines for AI-generated commit messages in this project.

## Format

All commit messages MUST follow this format:

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

## Commit Types

Use one of the following types (lowercase, required):

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Code style changes (formatting, whitespace, missing semicolons, etc.) that do not affect code meaning
- **refactor**: Code changes that neither fix bugs nor add features (code restructuring, improving readability)
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Maintenance tasks, dependency updates, build configuration changes
- **ci**: Continuous integration or pipeline changes
- **build**: Build system or external dependencies changes
- **revert**: Reverts a previous commit

## Scope

The scope (optional but recommended) should indicate the area of the codebase affected:

- Use lowercase
- Use hyphens to separate words (e.g., `auth`, `ui`, `api`, `form`, `layout`)
- Common scopes: `auth`, `ui`, `api`, `form`, `layout`, `config`, `deps`, `types`, `utils`
- If the change affects multiple areas, omit the scope or use a general term
- If the change is too broad, omit the scope

## Subject

The subject line (required):

- MUST be in imperative mood, present tense (e.g., "add" not "added" or "adds")
- MUST be in English
- MUST be lowercase (except for proper nouns, acronyms, or technical terms)
- MUST NOT end with a period
- MUST be concise (50-72 characters recommended)
- MUST describe what the commit does, not why or how
- MUST NOT include issue numbers in the subject (use footer instead)

### Good Examples:

- `feat(auth): add user login flow`
- `fix(ui): resolve crash on mobile devices`
- `chore(deps): update React to v18`
- `docs(readme): clarify development setup`
- `refactor(api): restructure fetch logic for readability`
- `style(button): standardize padding across variants`

### Bad Examples:

- `feat: Added new feature` (past tense, no scope)
- `fix bug` (missing type prefix)
- `FEAT(AUTH): ADD LOGIN` (all caps)
- `feat(auth): add user login flow.` (ends with period)
- `feat(auth): implement user authentication system with JWT tokens and refresh token mechanism` (too long)

## Body (Optional)

The body (optional):

- MUST be separated from the subject by a blank line
- SHOULD explain the "why" and "how" of the change, not the "what"
- MAY include details about the implementation
- MAY reference related issues or discussions
- SHOULD wrap at 72 characters per line
- MAY use bullet points for multiple changes

### Example:

```
feat(auth): add user login flow

Implement JWT-based authentication with refresh token support.
Add login form validation using Zod schema.
Include error handling for invalid credentials.

Closes #123
```

## Footer (Optional)

The footer (optional):

- MAY reference issues: `Closes #123`, `Fixes #456`, `Refs #789`
- MAY include breaking changes: `BREAKING CHANGE: description`
- MAY include co-authors: `Co-authored-by: Name <email>`

### Breaking Changes Format:

```
BREAKING CHANGE: <description of what changed and migration path>
```

## Rules Summary

1. **Always use the format**: `<type>(<scope>): <subject>`
2. **Type is required** and must be one of the listed types
3. **Scope is optional** but recommended when the change is localized
4. **Subject must be imperative mood, present tense, lowercase**
5. **Subject should be concise** (50-72 characters)
6. **Use English** for all commit messages
7. **No period** at the end of the subject
8. **Body and footer are optional** but useful for complex changes
9. **Reference issues** in the footer, not the subject
10. **Be descriptive but concise** - focus on what changed

## Context-Aware Rules

When generating commit messages:

1. **Analyze the diff** to determine the appropriate type and scope
2. **Group related changes** - if multiple files change for one feature, use one commit
3. **Be specific** - "fix button alignment" is better than "fix bug"
4. **Match the branch type** - if branch is `feature/login`, commit should be `feat`
5. **Consider the impact** - breaking changes must be marked in the footer
6. **Use technical terms correctly** - match the terminology used in the codebase

## Examples by Change Type

### New Feature

```
feat(form): add date picker component
```

### Bug Fix

```
fix(ui): resolve button alignment issue on mobile
```

### Documentation

```
docs(readme): update installation instructions
```

### Refactoring

```
refactor(api): extract authentication logic into separate module
```

### Performance

```
perf(api): optimize database query for user lookup
```

### Test

```
test(auth): add unit tests for login flow
```

### Chore

```
chore(deps): update Next.js to 16.0.3
```

### Breaking Change

```
feat(api): change authentication endpoint structure

BREAKING CHANGE: The /auth/login endpoint now requires email instead of username.
Update all API clients to use the new field name.
```

## Validation Checklist

Before generating a commit message, verify:

- [ ] Type is one of the allowed types
- [ ] Format follows `<type>(<scope>): <subject>`
- [ ] Subject is in imperative mood, present tense
- [ ] Subject is lowercase (except proper nouns)
- [ ] Subject does not end with a period
- [ ] Subject is concise (under 72 characters)
- [ ] Message is in English
- [ ] Scope is appropriate or omitted
- [ ] Body explains "why" if provided
- [ ] Breaking changes are marked in footer if applicable
