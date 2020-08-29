## Description

eTask topics format extends Topics format and provides the shortest way to manage activities and their comfortable grading. In addition to its clarity, it creates a motivating and competitive environment supporting a positive educational experience.

## Changes

All information about changes, fixes, or new features you can find in the [changelog file](CHANGELOG.md).

## Installation

Choose the [version](https://moodle.org/plugins/pluginversions.php?plugin=format_etask) compatible with your Moodle installation. For registered sites, use the `Install now` button. You can also install it from the Moodle `Site administration`/`Plugins`/`Install plugins` by the upload of a ZIP file. For manual installation, use the steps below.

1. Download the plugin as a ZIP file,
2. extract the files to the folder named `etask`,
3. copy the `etask` folder to `course/format/` directory on your server with Moodle installation,
4. login to the administration and run the installation,
5. optionally update the settings of this module and
6. set course format to `eTask topics format`.

### After installation

After installation (or later by visiting `Site administration`/`Plugins`/`Course formats`/`eTask topics format`), you can optionally configure the plugin. Only one configuration field is available. Do not remove default value - extend it if necessary.

#### Configuration

| Setting                     | Description                                                              | Example           |
| --------------------------- | ------------------------------------------------------------------------ | ----------------- |
| Registered due date modules | Specifies in which module's database field is the due date value stored. | `lucson:deadline` |

#### Additional course format settings

Except for basic Topics format course settings, the following are available in the context of the grading table.

| Setting                  | Description                     | Options                       | Default                         |
| ------------------------ | ------------------------------- | ----------------------------- | ------------------------------- |
| Student privacy          | Turns student privacy off/on.   | `0`, `1`                      | `1` (turned on)                 |
| Grade item progress bars | Hide or show progress bars.     | `0`, `1`                      | `1` (shown)                     |
| Students per page        | Number of students per page.    | `<1, ...>`                    | `10`                            |
| Grade items sorting      | Sorting of the grade items.     | `latest`, `oldest`, `inherit` | `latest` (new activities first) |
| Placement                | Placement of the grading table. | `above`, `below`              | `above` (above topics)          |

## Documentation

You can find fully specified user documentation on the [plugin page](https://moodle.org/plugins/format_etask).

## Common problems

### Teachers can access groups of which they are not a member

Non-editing teachers do not have the capability `moodle/site:accessallgroups` and so cannot by default access groups of which they are not a member.

The teachers have this capability allowed by default, but you can disallow it for this role manually in `Site administration`/`Users`/`Permissions`/`Define roles`. Edit the `Teacher` role and into the `Filter` type `moodle/site:accessallgroups`. You can see `Access all groups` is allowed so uncheck the checkbox for disallowing and click the `Save changes` button.

### Incorrect colors of graded items using scales

If you are using scales, and the colors of graded items are not correct, maybe you have incorrectly defined scales. It is necessary to define them ascending (from the worst to the best), e.g. `No`, `Yes` or `F`, `D`, `C`, `B`, `A`.

### Cannot apply completed status

Completed status in the grading table is applied automatically. To use this feature, you have to enable `completion tracking` in the course settings. If you cannot find this option, allow it in `Site administration`/`Advanced features`/`Enable completion tracking` first.

## Contributing

If you have any issue, or you are requesting a new feature, report the [new issue](https://gitlab.com/drlikm/format_etask/-/issues/new). Do not create security issues publicly, contact [plugin maintainer](https://moodle.org/user/profile.php?id=1566618) instead.

If you are reporting a bug, use a clear title. Description should contain short summary, steps to reproduce, current and expected behavior, optionally link to the theme you are using. Attache the relevant logs and/or screenshots. Provide the eTask topics release and version as well as your Moodle installation version.

## License

eTask topics format is a Free Open Source software package available under the [GNU General Public License](LICENSE), designed to help educators create effective online courses based on sound pedagogical principles.
