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
 * Renderer for outputting the eTask topics course format.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/etask/classes/output/gradeitem_body.php');
require_once($CFG->dirroot.'/course/format/etask/classes/output/gradeitem_head.php');
require_once($CFG->dirroot.'/course/format/etask/classes/output/popover.php');
require_once($CFG->dirroot.'/course/format/topics/renderer.php');

use format_etask\dataprovider\course_settings;
use format_etask\form\group_form;
use format_etask\form\settings_form;
use format_etask\output\footer;
use format_etask\output\gradeitem_body;
use format_etask\output\gradeitem_head;

/**
 * Basic renderer for eTask topics format.
 *
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_etask_renderer extends format_topics_renderer {

    /**
     * Print grading table.
     *
     * @param context_course $context
     * @param stdClass $course
     * @return void
     */
    public function print_grading_table(context_course $context, stdClass $course) {
        global $USER;

        // Get all allowed course students.
        $students = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', course_get_format(
            $this->page->course)->get_current_group_id(), 'u.*', null, 0, 0, true);
        // Get students count for pagination.
        $studentscount = course_get_format($this->page->course)->is_student_privacy() ? 1 : count($students);
        // Get sorted grade items.
        $gradeitems = count($students) ? course_get_format($this->page->course)->get_sorted_gradeitems() : [];

        /** @var html_table_row[] $data */
        $data = [];
        /** @var array<int, string[]> $gradeitemsstatuses */
        $gradeitemsstatuses = [];
        // Move logged in student at the first position in the grade table.
        if (isset($students[$USER->id]) && !course_get_format($this->page->course)->is_student_privacy()) {
            $currentuser = $students[$USER->id];
            unset($students[$USER->id]);
            array_unshift($students , $currentuser);
        }
        foreach ($students as $user) {
            // Collect table cells by student privacy. Either all or the current student only.
            $collectcell = !course_get_format($this->page->course)->is_student_privacy() || (course_get_format($this->page->course)->is_student_privacy() && $user->id === $USER->id);
            $bodycells = [];
            if ($collectcell) {
                $cell = new html_table_cell();
                $cell->text = $this->output->user_picture($user, ['size' => 35, 'link' => true, 'includefullname' => true,
                    'visibletoscreenreaders' => false]);
                $cell->attributes = [
                    'class' => 'text-nowrap pr-2'
                ];
                $bodycells[] = $cell;
            }

            /** @var grade_item $gradeitem */
            foreach ($gradeitems as $gradeitem) {
                $status = course_get_format($this->page->course)->get_grade_item_status($gradeitem, $user);
                $gradeitemsstatuses[$gradeitem->id][] = $status;
                if ($collectcell) {
                    $cell = new html_table_cell();
                    $cell->text = $this->render(new gradeitem_body($gradeitem, $user, $status));
                    $cell->attributes = [
                        'class' => 'position-relative text-center text-nowrap p-2 ' . course_get_format($this->page->course)->transform_status_to_css($status),
                        'title' => fullname($user) . ', ' . $gradeitem->itemname
                    ];
                    $bodycells[] = $cell;
                }
            }

            if (count($bodycells) > 0) {
                $row = new html_table_row($bodycells);
                $data[] = $row;
            }
        }
        // Table head.
        $headcells = ['']; // First cell of the head is empty.
        // Render table cells.
        foreach ($gradeitems as $shortcut => $gradeitem) {
            $cell = new html_table_cell();
            $cell->text = $this->render(new gradeitem_head($gradeitem, $shortcut, $gradeitemsstatuses[$gradeitem->id],
                count($students)));
            $cell->attributes = [
                'class' => 'text-center text-nowrap'
            ];
            $headcells[] = $cell;
        }

        // Slice of students by paging after getting progress bar data.
        $studentsperpage = course_get_format($this->page->course)->get_students_per_page();
        if ($studentscount > $studentsperpage) {
            $currentpage = course_get_format($this->page->course)->get_current_page($studentscount, $studentsperpage);
            $data = array_slice($data, $currentpage * $studentsperpage, $studentsperpage, true);
        }

        // Html table.
        $gradetable = new html_table();
        $gradetable->attributes = [
            'class' => 'grade-table table-hover table-striped table-condensed table-responsive mb-3',
            'table-layout' => 'fixed'
        ];
        $gradetable->head = $headcells;
        $gradetable->data = $data;

        // Grade table footer: groups filter, pagination and legend.
        $gradetablefooter = $this->render(new footer($studentscount, course_get_format($course->id)->get_groups(),
            course_get_format($this->page->course)->get_current_group_id()));
        $css = 'border-bottom mb-3 pb-3';
        if (course_get_format($this->page->course)->get_placement() === format_etask::PLACEMENT_BELOW) {
            $css = 'border-top mt-4 pt-4';
        }
        echo html_writer::div(
            html_writer::table($gradetable) . $gradetablefooter,
            'etask-grade-table ' . $css
        );
    }
}
