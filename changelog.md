---
date: 2021-07-18
title: Changelog
description: Log of all notable changes for eTask topics format project.
type: Document
---

## 2021-07-18

**Changed (1 change)**

- Show help popover in the grading table footer only if the user has the capability to update course settings.

## 2021-05-26

**Fixed (1 change)**

- Missing `sesskey` on group change.

## 2021-05-11

**Changed (1 change)**

- Grading table and grade to pass accept decimals.

## 2020-11-11

**Changed (6 changes)**

- Replace most of the CSS classes with Bootstrap.
- Move specific CSS to the `styles.css`.
- Renderer logic to the output classes with `.mustache` templates.
- System of plugin versioning.
- Popover redesign (including grade to pass setting).
- Replace negative margin CSS with appropriate Bootstrap classes `mr-n*`/`ml-n*` (available from Moodle 3.8).

**Fixed (5 changes)**

- Responsive design fixes.
- Show only groups of which non-editing teacher is a part.
- Getting students for the grading table.
- Hide grade items with "deletion in progress" from the grading table.
- State when the grading table is empty (without students and/or without grade items).

**Security (1 change)**

- Secure form processing.

**Removed (2 changes)**

- Course format `styles.css` selectors (they are a part of core styles).
- `mform`s classes (replaced with single select component).

**Added (7 changes)**

- Last modified information in the grade item popover.
- Grade max. in the grade item popover.
- Support formatting of grade value, e.g. `B- (80%)`.
- Ability to change `Passed` and `Failed` label's texts.
- New `licence` and `readme.md`.
- Help link from the grading table to the plugin directory page.
- Code documentation.

**Other (5 changes)**

- Use `core/notification` instead of own renderer function.
- Refactor grade items sorting.
- Use existing core capabilities.
- Performance optimization.
- Code formatting.
