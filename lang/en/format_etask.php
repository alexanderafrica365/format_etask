<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component eTask topics course format.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Topics format strings.
$string['addsections'] = 'Add topics';
$string['currentsection'] = 'This topic';
$string['editsection'] = 'Edit topic';
$string['editsectionname'] = 'Edit topic name';
$string['deletesection'] = 'Delete topic';
$string['newsectionname'] = 'New name for topic {$a}';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'eTask topics format';
$string['section0name'] = 'General';
$string['page-course-view-topics'] = 'Any course main page in eTask topics format';
$string['page-course-view-topics-x'] = 'Any course page in eTask topics format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';
$string['privacy:metadata'] = 'The eTask topics format plugin does not store any personal data.';
// Course format settings strings.
$string['privateview'] = 'Private view';
$string['privateview_help'] = 'This settings determines whether all the students can see each other\'s grades in the eTask grading table or not.';
$string['privateview_no'] = 'Each student can see the grades of the other students in the course/group';
$string['privateview_yes'] = 'Logged in student can see own grades only';
$string['progressbars'] = 'Progress bars';
$string['progressbars_help'] = 'This settings determines whether progress bars Completed and Passed are shown in the eTask grading table activity popover or not.';
$string['progressbars_donotshow'] = 'Do not show progress bars Completed and Passed';
$string['progressbars_show'] = 'Show progress bars Completed and Passed';
$string['studentsperpage'] = 'Students per page';
$string['studentsperpage_help'] = 'This settings determines the number of students per page in the eTask grading table.';
$string['activitiessorting'] = 'Activities sorting';
$string['activitiessorting_help'] = 'This settings determines whether activities of eTask grading table are sorted by the latest, by the oldest or as they are in the course.';
$string['activitiessorting_latest'] = 'Sort the activities by the latest';
$string['activitiessorting_oldest'] = 'Sort the activities by the oldest';
$string['activitiessorting_inherit'] = 'Sort the activities as they are in the course';
$string['placement'] = 'Placement';
$string['placement_help'] = 'This settings determines placement of the eTask grading table above or below the course topics.';
$string['placement_above'] = 'Place the grading table above the course topics';
$string['placement_below'] = 'Place the grading table below the course topics';
// Plugin settings strings.
$string['registeredduedatemodules'] = 'Registered due date modules';
$string['registeredduedatemodules_help'] = 'Specifies in which module\'s database field the due date value is stored.';
// Legend strings.
$string['legend'] = 'Legend';
$string['activitycompleted'] = 'Completed';
$string['activitypassed'] = 'Passed';
$string['activityfailed'] = 'Failed';
// Popover strings.
$string['timemodified'] = 'Last modified on {$a}';
// Flash messages.
$string['gradepasschanged'] = 'Grade to pass for activity <strong>{$a->itemname}</strong> has been successfully changed to <strong>{$a->gradepass}</strong>.';
$string['gradepassremoved'] = 'Grade to pass for activity <strong>{$a}</strong> has been successfully removed.';
$string['gradepassunablesave'] = 'Unable to change grade to pass for activity <strong>{$a}</strong>. Please, try it again later or contact plugin developer.';
// Permissions in course.
$string['etask:teacher'] = 'Full management';
$string['etask:noneditingteacher'] = 'Read only (all the data)';
$string['etask:student'] = 'Read only (data in the context of a private view)';
