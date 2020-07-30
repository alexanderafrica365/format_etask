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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The eTask topics course format. Display the whole course as "topics" made of modules.
 *
 * @package format_etask
 * @copyright 2006 The Open University
 * @author N.D.Freear@open.ac.uk, and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

use format_etask\dataprovider\course_settings;

// Horrible backwards compatible parameter aliasing.
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing.

$context = context_course::instance($course->id);
// Retrieve course format option fields and add them to the $course object.
$course = course_get_format($course)->get_course();

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_etask');

// Start eTask topics course format.
if (has_capability('moodle/course:viewparticipants', $context) === true) {

    // The position above the sections.
    if (course_get_format($PAGE->course)->get_placement() === format_etask::PLACEMENT_ABOVE) {
        $renderer->print_grading_table($context, $course);
    }

    // Sections.
    if ($displaysection > 0) {
        $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
    } else {
        $renderer->print_multiple_section_page($course, null, null, null, null);
    }

    // The position below the sections.
    if (course_get_format($PAGE->course)->get_placement() === format_etask::PLACEMENT_BELOW) {
        $renderer->print_grading_table($context, $course);
    }
} else {
    // Sections.
    if ($displaysection > 0) {
        $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
    } else {
        $renderer->print_multiple_section_page($course, null, null, null, null);
    }
}
// End eTask topics course format.

// Include course format js module.
$PAGE->requires->js('/course/format/topics/format.js');
$PAGE->requires->js('/course/format/etask/format.js');
