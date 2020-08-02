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
$string['studentprivacy'] = 'Student privacy';
$string['studentprivacy_help'] = 'This setting determines whether the student can see the grades of others in the grading table or not.';
$string['studentprivacy_no'] = 'The student can see the grades of others in the grading table';
$string['studentprivacy_yes'] = 'The student can only see his/her grades in the grading table';
$string['gradeitemprogressbars'] = 'Grade item progress bars';
$string['gradeitemprogressbars_help'] = 'This setting determines whether the student should see a grade item progress bars in the grading table or not.';
$string['gradeitemprogressbars_no'] = 'Hide the student\'s grade item progress bars in the grading table';
$string['gradeitemprogressbars_yes'] = 'Show the student\'s grade item progress bars in the grading table';
$string['studentsperpage'] = 'Students per page';
$string['studentsperpage_help'] = 'This setting determines the number of students per page in the grading table.';
$string['gradeitemssorting'] = 'Grade items sorting';
$string['gradeitemssorting_help'] = 'This setting determines whether the grade items in the grading table are sorted by the latest, oldest, or as they are in the course.';
$string['gradeitemssorting_latest'] = 'Sort grade items in the grading table by the latest';
$string['gradeitemssorting_oldest'] = 'Sort grade items in the grading table by the oldest';
$string['gradeitemssorting_inherit'] = 'Sort grade items in the grading table as they are in the course';
$string['placement'] = 'Placement';
$string['placement_help'] = 'This setting determines the placement of the grading table above or below the course topics.';
$string['placement_above'] = 'Place the grading table above the course topics';
$string['placement_below'] = 'Place the grading table below the course topics';
// Plugin settings strings.
$string['registeredduedatemodules'] = 'Registered due date modules';
$string['registeredduedatemodules_help'] = 'Specifies in which module\'s database field the due date value is stored.';
// Grading table strings.
$string['nostudentsfound'] = 'No students were found to grade.';
$string['nogradeitemsfound'] = 'No grade items were found.';
// Grading table footer strings.
$string['legend'] = 'Legend';
$string['gradeitemcompleted'] = 'Completed';
$string['gradeitempassed'] = 'Passed';
$string['gradeitemfailed'] = 'Failed';
// Grade item popover strings.
$string['timemodified'] = 'Last modified on {$a}';
$string['max'] = 'max.';
$string['progresspercentage'] = '{$a} % <span class="text-black-50">of all students</span>';
$string['choose'] = 'Choose ...';
$string['showmore'] = 'Show more ...';
// Grading table help strings.
$string['about'] = 'Free Open Source software package designed to help educators create effective online courses based on sound pedagogical principles.';
$string['questionnaireshead'] = 'Questionnaires';
$string['questionnaires'] = 'You have no questionnaires available now to help us improve this plugin.';
// Flash messages.
$string['gradepasschanged'] = 'Grade to pass for grade item <strong>{$a->itemname}</strong> has been successfully changed to <strong>{$a->gradepass}</strong>.';
$string['gradepassremoved'] = 'Grade to pass for grade item <strong>{$a}</strong> has been successfully removed.';
$string['gradepassunablesave'] = 'Unable to change grade to pass for grade item <strong>{$a}</strong>. Please, try it again later or contact plugin developer.';
