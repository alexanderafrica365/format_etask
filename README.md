# eTask topics format 1.4

**eTask topics format** is based on the topics format and **includes grading table** (above or below the course sections) with aditional functionality such as a highlighting of grading values, grade to pass settings, motivational progress charts and much more.

![eTask_mdl_33](https://bitbucket.org/repo/obeE8n/images/3001788952-eTask_mdl_33.png)

## Changelog

- Old configuration compatibility removed (no longer needed).

## Installation

1. Download files as a ZIP archive,
2. extract files to the folder named `etask`,
3. copy the `etask` folder to `course/format/` in your Moodle installation,
4. login to the administration and run the installation,
5. optionally update the settings of this module,
6. set course format to `eTask topics format`.

## Settings

### Activity completion

Activity completion is enabled by default in Moodle 3.3. You can manage `Activity completion` in each of the course activity.

### Plugin settings

You can edit plugin setting by visiting `Site administration` -> `Plugins` -> `Course formats` -> `eTask topics format`. There is **one configurable field**.

#### Registered due date modules

![eTask-plugin-settings_mdl_33](https://bitbucket.org/repo/obeE8n/images/252286885-eTask-pluginSettings_mdl_33.png)

**Registered due date modules** provide a list of activity modules and specifies in which module's database field is the due date value stored. It helps you to menage modules with due date information used in activity popover.

### Course format settings

You can edit course format settings by `Edit settings` in the course. There are **five configurable fields**.

![eTask-course-format-settings_mdl_33](https://bitbucket.org/repo/obeE8n/images/2743431468-eTask-courseFormatSettings_mdl_33.png)

#### eTask private view

**By default**, private view **is active -- students can see only their own grades**. Othervise they see grades of all the students.

#### eTask progress charts 

**Motivates your students** even more with **progress charts Completed and Passed** placed in the activity popover. Students can see progress of completed activities as well as passed activities. Private view is back in a play! No private data like grades of the other students -- motivational progress charts only. Progress charts are **calculated by default**. Because of the activity popover consistency, progress charts are visible all the time -- if progress charts are not allowed, they are not calculated but still visible with zero values.

#### eTask students per page

**Number of students per page** allows to change the number of students visible on each page of eTask grading table (default value is `10`). **Pagination is displayed** below the eTask grading table.

#### eTask activities sorting

**Customize the course settings** by your preferences and sort activities by the latest, by the oldest or as they are in the course.

#### eTask placement

**Above or below the course sections?** Choose your ideal grading table placement.

## Features

### Grading table of many activities

Provides grading table of many activity types such as `assign`, `quiz`, `scorm`, `worksop` etc.

![eTask_mdl_33](https://bitbucket.org/repo/obeE8n/images/3001788952-eTask_mdl_33.png)

### Activity popover

Shows **activity popover** with `due date` (expected completion date if due date is missing), `grade to pass` and optionally calculated `completed` and `passed` progress charts (see [course format settings](#markdown-header-course-format-settings) for [eTask progress charts](#markdown-header-etask-progress-charts)).

![eTask-popover_mdl_33](https://bitbucket.org/repo/obeE8n/images/3289160212-eTask-popover_mdl_33.png)

### Grade to pass settings

Allows **set up grade to pass** in a course editing mode. It includes scales as well!

![eTask-gradeSettingsModal_mdl_33](https://bitbucket.org/repo/obeE8n/images/1984496429-eTask-gradeSettingsModal_mdl_33.png)

### Highlighting of grading values

**Highlights grading value** by different statuses (`completed`, `passed`, `failed` or without highlighting if grade to pass is not defined). Completed status is applied through the activity completion in the activity settings. The status completed is displayed until a grade is entered, then 'passed' or 'failed' status is shown according to the grade to pass. Otherwise no status if grade to pass is not set.

![eTask-gradeToPassMessage_mdl_33](https://bitbucket.org/repo/obeE8n/images/2273389916-eTask-gradeToPassMessage_mdl_33.png)

### Private view

**Private view** allows students to see only own grades (see [course format settings](#markdown-header-course-format-settings) for [eTask private view](#markdown-header-etask-private-view)).

![eTask-privateView_mdl_33](https://bitbucket.org/repo/obeE8n/images/3438527201-eTask-privateView_mdl_33.png)

### Permissions

**Strictly defined access permissions** for all versions of eTask. **GDPR ready plugin.** It is possible to manage permissions in course. There are three access levels:

- **teacher** (full management; includes manager, course creator and teacher roles),
- **non-editing teacher** (read only for all the data) and
- **student** (read only for the data in the context of a private view).

Switching the user roles provide real view on grading table in permissions context.

![eTask-permissions_mdl_33](https://bitbucket.org/repo/obeE8n/images/568563207-eTask-permissions_mdl_33.png)

### Scales

It is possible to use user defined scales in the grading activities. It is necessary to **define scales ascending** (from the worst to the best value), e.g. `No, Yes` or `F, D, C, B, A`!

### Other features

- **Support for groups mode** in a course, it means student can see only students from the same group and teacher can **filter eTask by all groups**,
- **table pagination**; [eTask students per page](#markdown-header-etask-students-per-page) can be defined in the eTask [course format settings](#markdown-header-course-format-settings),
- all **activities** in an eTask grading table **are sorted from the newest by default** because of the information value of the latest activities -- **you can change it** by your preferences in the [course format settings](#markdown-header-course-format-settings) and sort activities by the latest, by the oldest or as they are in the course, see [eTask activities sorting](#markdown-header-etask-activities-sorting),
- you can change **grading table placement** -- above or below the course topics, see [eTask placement](#markdown-header-etask-placement),
- **logged in student is always at the first row** of the grading table,
- **activity completion support** (completed status in the table, completed progress chart, expected completion date as due date if due date is not set),
- there **three types of access permissions**:
    - **teacher** can edit activities by clicking on activity headers; grade to pass setting is available; links from activity headers goes to activity editation; links from grade table cells goes to activity grading; filtering by groups is available as well as pagination,
    - **non-editing teacher** can click only on activity header; links goes to activity detail; filtering by groups is available as well as pagination and
    - **student** can click only on activity headers; links goes to activity detail; pagination is available; if student is part of defined group, only students from the same group are shown in the eTask table.

### Mobile ready

One more thing. **We are mobile ready!** You can simply swipe the eTask grading table or set up grade to pass.

![eTask-mobile_mdl_33](https://bitbucket.org/repo/obeE8n/images/884595084-eTask-mobile_mdl_33.png)
