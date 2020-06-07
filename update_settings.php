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
 * Updates settings.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require("../../../config.php");
require_once("../../lib.php");
require_once("../../../lib/grade/grade_item.php");
require_once("../../../lib/grade/constants.php");

$gradepass     = optional_param('gradepass', null, PARAM_INT);
$group         = optional_param('group', '', PARAM_INT);
$course        = required_param('course', PARAM_INT);

require_login();

if ($gradepass !== null && confirm_sesskey()) {
    $gradeitemid   = required_param('gradeitemid', PARAM_INT);
    $gradepass     = required_param('gradepass', PARAM_INT);
    $itemname      = required_param('itemname', PARAM_RAW);

    $cm     = get_coursemodule_from_id('', $course, 0, true, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);
    require_capability('moodle/course:manageactivities', $modcontext);

    $saved = course_get_format($PAGE->course)->update_grade_pass($gradeitemid, $gradepass);

    $message = get_string('gradepassunablesave', 'format_etask', $itemname);
    $messagetype = \core\notification::ERROR;
    if ($saved) {
        if ($gradepass > 0) {
            $message = get_string('gradepasschanged', 'format_etask', [
                'itemname' => $itemname,
                'gradepass' => $gradepass,
            ]);
        } else {
            $message = get_string('gradepassremoved', 'format_etask', $itemname);
        }
        $messagetype = \core\notification::SUCCESS;
    }

     redirect(course_get_url($course), $message, null, $messagetype);
} else if ($group !== null) {
    $SESSION->etask['group'] = $group;

    redirect(course_get_url($course));
}
