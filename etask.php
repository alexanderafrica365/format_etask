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
 * eTask course format. Display grading table with all functionality. 
 *
 * External file of course format which renders gradebook table. Table
 * contains students, assignments and grading. Grade to pass can be set.
 * The table cells are coloured by status unsubmitted, submitted, passed
 * and failed. From table is direct link to grade assignment of student.
 *
 * @package    format_etask
 * @copyright  2013 Martin Drlik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// external styles
echo '<style type="text/css" media="screen" title="Graphic layout" scoped>
		<!--
			@import "../course/format/etask/etaskstyles.css";
		-->
		</style>';


// libraries and forms
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/grader/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/format/etask/gradepass_form.php');

// external javascript
$PAGE->requires->js('/course/format/etask/etask.js');


// variables
$sesskey = $USER->sesskey;
$sectionreturn = optional_param('sr', 0, PARAM_INT);
$courseid = required_param('id', PARAM_INT); // course id
$page = optional_param('page', 0, PARAM_INT); // active page

// update grade to pass
update_gradepass($context, $courseid);

// render gradebook table with all functionality
$assignments = get_etask_assignments($courseid);
$users = get_etask_users($context, $courseid, $page);
$gradinginfo = get_etask_grade($courseid, $assignments, $users);
echo '<div class="overflowtable etaskcontainer"><div class="directionrtl"><div class="directionltr">' .
   get_etask_gradebook($assignments, $context, $courseid, $sesskey, $sectionreturn, $users, $gradinginfo) .
   '</div></div></div>
   <p class="legend"><strong>' . get_string('legend', 'format_etask') . ':</strong>&nbsp;<span class="submitted">' .
      get_string('submitted', 'format_etask') . '</span> <span class="passed">' .
      get_string('passed', 'format_etask') . '</span> <span class="failed">' .
      get_string('failed', 'format_etask') . '</span></p>';
