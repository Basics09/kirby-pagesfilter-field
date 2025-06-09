# Kirby Pages Filter Field

A simple Kirby CMS plugin that extends the pages field to apply filters to subpages in the page picker dialog.

## What it does

This plugin adds a `pagesfilter` field type that works like the standard `pages` field, but applies your filter rules to all subpages shown in the picker dialog. It uses a `filter` parameter instead of `query` and shows subpages with the filter applied recursively.

## Motivation

When building navigation menus, you often need to exclude certain pages (like panel structure pages or non-public pages) from the entire site hierarchy. The standard `pages` field only filters top-level pages, not subpages. This plugin applies filters at every level for cleaner page selection.

## Installation

### Via Composer

```bash
composer require basics09/kirby-pagesfilter-field
```

## Requirements

- PHP 8.2 or higher
- Kirby CMS 4.3.1+ or 5.0+

## Usage

Use the `pagesfilter` field type in your blueprints:

```yaml
fields:
  navigation:
    type: pagesfilter
    filter: "filterBy('intendedTemplate', 'article')"
    parent: site
    label: Navigation
```

### Filter Examples

The `filter` parameter uses Kirby's query language:

```yaml
# Only published pages
filter: "filterBy('status', 'listed')"

# Pages with specific template
filter: "filterBy('intendedTemplate', 'article')"

# Exclude certain templates
filter: "filterBy('intendedTemplate', 'not in', ['error', 'please-donot-list-me'])"

# Custom field filtering
filter: "filterBy('featured', true)"

```

## Field Options

| Option     | Type     | Default | Description                                 |
| ---------- | -------- | ------- | ------------------------------------------- |
| `filter`   | `string` | `null`  | Filter query to apply to pages and subpages |
| `subpages` | `bool`   | `true`  | Always `true` for this field type           |
| `query`    | `string` | `null`  | Not supported - use `filter` instead        |

All other options from Kirby's `pages` field are supported.

## Differences from Core Pages Field

- Uses `filter` instead of `query` parameter
- Applies the filter to all subpage levels, not just immediate children
- Always shows subpages (use core `pages` field if you don't need this)

## How It Works

The plugin extends Kirby's `PagePicker` to apply your filter query to each parent's `children` property at all page levels, and shows which pages have filtered children available.

## Disclaimer

This plugin is provided as-is with no guarantee. Use at your own risk. Always test thoroughly in a development environment before using in production.
If you encounter any issues or have questions, please [open an issue](https://github.com/basics09/kirby-pagesfilter-field/issues) on GitHub.
