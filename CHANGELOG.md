## 2.0 (2020-??-??)

### Changed (5 changes)

- Replace most of the CSS classes by Bootstrap.
- Move specific CSS to the `styles.css`.
- Renderer logic to the output classes with `.mustache` templates.
- System of plugin versioning.
- Popover redesign.

### Fixed (5 changes)

- Responsive design fixes.
- Show only groups of which non-editing teacher is a part.
- Getting of students for grading table.
- Hide grade items with "deletion in progress" from the grading table.
- State when the grading table is empty (without students and/or without grade items).

### Security (1 change)

- Secure form processing.

### Removed (2 changes)

- Course format `styles.css` selectors (from Moodle 3.8 they are a part of core styles).
- `mform`s classes (replaced with single select component).

### Added (4 changes)

- Support formating of grade value, e.g `B- (80%)`.
- New `licence`, `changelog.md`, `contributing.md` and `readme.md`.
- Help link from the grading table to plugin directory page.
- Code documentation.

### Other (5 changes)

- Use `core/notification` instead of own renderer function.
- Refactor grade items sorting.
- Use existing core capabilities.
- Performance optimization.
- Code formatting.
