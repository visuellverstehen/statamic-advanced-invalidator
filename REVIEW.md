# Repository Evaluation: Statamic Advanced Invalidator

**Date:** 2024-03-22  
**Scope:** v5 perspective (pre-v6 upgrade)

---

## Summary

This is a focused, single-purpose package that extends Statamic's static caching invalidator with additional capabilities (cache keys, named routes, and CacheTracker tags). It's a good concept with a clean API, but it has several gaps in testing, code quality, and project infrastructure.

---

## Critical Issues (Should Fix Before v6)

### 1. Broken Test Suite

- **phpunit.xml is invalid** - uses HTML entities (`&lt;?xml` instead of `<?xml`)
- **Test namespace mismatch** - `TestCase.php` uses `Visuellverstehen\StatamicAdvancedInvalidator\Tests` but `composer.json` defines `VV\AdvancedInvalidator\Tests`
- **Zero meaningful tests** - only a placeholder `assertTrue(true)` test exists

### 2. Fragile State Management in AdvancedInvalidator

The `resolveNamedRoutes()` method has problematic side effects:

```php
// It mutates $this->rules at runtime
Arr::set($this->rules, $this->rulePath . '.urls', $mergedUrls);
Arr::forget($this->rules, $this->rulePath . '.named_routes');
```

- Modifies config-derived state on each `invalidate()` call
- Relies on `$this->rulePath` being set by a previous method call
- If `invalidate()` is called multiple times, behavior changes (routes already resolved and forgotten)

### 3. Potential Bug in `invalidateTags()`

```php
$cacheTracker = '\Thoughtco\StatamicCacheTracker\Facades';
// ...
$cacheTracker::invalidate($tags);  // This won't work - $cacheTracker is a string
```

This should be `$cacheTracker::invalidate()` but PHP doesn't support dynamic static calls on string class names with `::` without `call_user_func()` or first checking if it's a facade.

---

## Code Quality Issues

| Issue | Location | Severity |
|-------|----------|----------|
| Missing return type hints | All methods | Low |
| Missing parameter type hints | `$item`, `$rules` | Low |
| `private` instead of `protected` | `$rulePath`, `getItemRules()`, `resolveNamedRoutes()` | Medium |
| Using `strpos() === false` instead of `str_contains()` | `invalidateKeys()` | Low |
| No validation that `$item` is a supported type | `invalidate()` | Medium |

---

## Missing Infrastructure

1. **No CI/CD** - No GitHub Actions for running tests
2. **No code style tooling** - No Pint, PHP CS Fixer, or PHPStan configuration
3. **No `.gitattributes`** - vendored tests, docs, and dev files included in releases
4. **No `.editorconfig`** - inconsistent code formatting
5. **No changelog** - users can't track changes between versions
6. **No GitHub templates** - issues, PRs have no structure

---

## Documentation Gaps

1. **README is minimal** - missing:
   - Requirements (PHP version, Statamic version)
   - Full configuration reference
   - How the `{locale}` placeholder works in cache keys
   - Examples for each rule type
   - Troubleshooting section

2. **No upgrade guide** - will be needed for v6 transition

---

## Suggestions for Improvement

### Immediate (Pre-v6)

1. **Fix the test infrastructure**
   ```xml
   <!-- Fix phpunit.xml - replace HTML entities -->
   <?xml version="1.0" encoding="UTF-8"?>
   ```

2. **Fix the namespace mismatch** in `TestCase.php`:
   ```php
   namespace VV\AdvancedInvalidator\Tests; // Not Visuellverstehen\...
   ```

3. **Refactor `resolveNamedRoutes()`** to not mutate state:
   ```php
   protected function resolveNamedRoutes(array $rules, string $rulePath): array
   {
       // Return resolved URLs instead of modifying $this->rules
   }
   ```

4. **Fix `invalidateTags()`** dynamic class resolution

5. **Add `.gitattributes`** to exclude dev files from releases:
   ```
   /tests export-ignore
   /phpunit.xml export-ignore
   /.gitignore export-ignore
   /.nova export-ignore
   ```

### Short-term

6. **Add proper test coverage** - test all invalidation paths (keys, tags, routes, all content types)

7. **Add code quality tools**:
   - `laravel/pint` for code style
   - `phpstan/phpstan` for static analysis

8. **Add GitHub Actions** workflow for CI

9. **Create a CHANGELOG.md** following Keep a Changelog format

10. **Expand README** with better examples and configuration reference

### Architectural Improvements

11. **Consider extracting rule resolution** into a separate class for better testability

12. **Add event dispatching** - allow users to hook into invalidation events

13. **Add logging/debug mode** - help users understand what's being invalidated and why

---

## Overall Assessment

| Category | Rating | Notes |
|----------|--------|-------|
| **Concept** | ⭐⭐⭐⭐⭐ | Solves a real problem well |
| **Implementation** | ⭐⭐⭐ | Works but has fragile patterns |
| **Testing** | ⭐ | Effectively non-existent |
| **Documentation** | ⭐⭐ | Basic but functional |
| **Maintainability** | ⭐⭐ | Hard to test, no CI |

**Verdict:** The package is functional for its intended purpose but needs attention to testing and code quality before it's "production-grade." The v6 update is a good opportunity to address these foundational issues.

---

## Files Reviewed

- `composer.json`
- `README.md`
- `phpunit.xml`
- `LICENSE.md`
- `src/AdvancedInvalidator.php`
- `src/ServiceProvider.php`
- `tests/ExampleTest.php`
- `tests/TestCase.php`
- `vendor/statamic/cms/src/StaticCaching/DefaultInvalidator.php` (parent class reference)
