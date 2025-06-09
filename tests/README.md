# PagePickerFilter Tests

This directory contains comprehensive tests for the `Basics09\PagePickerFilter` class, which extends Kirby's `PagePicker` to add filtering capabilities.

## Test Coverage

The test suite covers all public methods and functionality:

### Core Methods

- `defaults()` - Tests that the class properly extends parent defaults with filter-specific options
- `filterQuery()` - Tests getter for the filter option parameter
- `itemsForParent()` - Tests page retrieval with and without filters applied
- `applyFilterQueryToChildren()` - Tests the core filtering logic using Kirby's Query system
- `pageHasFilteredChildren()` - Tests whether a page has children matching the filter
- `itemsToArray()` - Tests the modification of hasChildren property based on filtered results

### Edge Cases Tested

- Empty filters (returns all children)
- Invalid filter queries (returns empty Pages collection)
- Multiple filter conditions chained together
- Sorting within filter queries
- Different content field types (title, template, etc.)
- Pages with no children
- Empty collections

### Filter Query Format

The tests use Kirby's Query system format for filters. Examples:

```php
// Filter by template
"filterBy('intendedTemplate', 'in', ['default'])"

// Filter by content field
"filterBy('title', '==', 'About')"

// Multiple conditions
"filterBy('intendedTemplate', '==', 'default').filterBy('title', '!=', 'Contact')"

// With sorting
"filterBy('intendedTemplate', '==', 'default').sortBy('title', 'desc')"
```

## Running Tests

```bash
# Run all PagePickerFilter tests
./vendor/bin/pest tests/Unit/PagePickerFilterTest.php

# Run specific test
./vendor/bin/pest tests/Unit/PagePickerFilterTest.php --filter="test name"

# Run all tests
./vendor/bin/pest
```

## Test Data Structure

The tests create a Kirby site with the following structure:

- Home (template: home)
- About (template: default)
  - Team (template: default)
  - History (template: default)
- Contact (template: default)

This structure allows testing of various filtering scenarios including parent-child relationships and template-based filtering.
