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
 * Class containing data for grading table.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

defined('MOODLE_INTERNAL') || die();

use format_etask;
use grade_item;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to prepare a grading table for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingtable implements renderable, templatable {

    /** @var string */
    private $table;

    /** @var string */
    private $footer;

    /** @var string */
    private $css;

    /**
     * Grading table constructor.
     */
    public function __construct() {
        global $COURSE;

        // Get all allowed course students.
        $students = course_get_format($COURSE)->get_gradable_students();
        // Get students count for pagination.
        $studentscount = course_get_format($COURSE)->get_students_count($students);
        // Get sorted grade items.
        $gradeitems = course_get_format($COURSE)->get_sorted_gradeitems();

        /** @var array<int, string[]> $gradeitemsstatuses */
        $gradeitemsstatuses = [];
        /** @var html_table_cell[] $headcells */
        $headcells = [new html_table_cell()]; // First cell of the head is always empty.
        /** @var html_table_row[] $rows */
        $rows = [];

        // No students were found to grade. Add row to the table body data.
        if ($studentscount === 0) {
            $rows[] = $this->get_no_students_row($gradeitems);
        }

        // No grade items were found. Add cell to the table head data.
        if (count($gradeitems) === 0) {
            $headcells[] = $this->get_no_gradeitems_cell();
        }

        foreach ($students as $user) {
            // Collect table cells by student privacy - either all or the current student only.
            $collectiblecell = course_get_format($COURSE)->is_collectible_cell($user);
            /** @var html_table_cell[] $bodycells */
            $bodycells = [];

            if ($collectiblecell === true) {
                // Add student cell at the first position of the row.
                $bodycells[] = $this->get_student_cell($user);

                // No grade items were found. Each student has empty grade cell in the column.
                if (count($gradeitems) === 0) {
                    $bodycells[] = $this->get_empty_cell();
                }
            }

            /** @var grade_item $gradeitem */
            foreach ($gradeitems as $gradeitem) {
                // Statuses must be collected regardless of whether the cell is collectible or not. Progress bars are calculated
                // even if the student's privacy is applied.
                $status = course_get_format($COURSE)->get_grade_item_status($gradeitem, $user);
                $gradeitemsstatuses[$gradeitem->id][] = $status;
                if ($collectiblecell === true) {
                    $bodycells[] = $this->get_gradeitem_body_cell($gradeitem, $user, $status);
                }
            }

            // If count of body cells is the same as count of gradeitems + 1 (user cell), collect them to row.
            if (count($bodycells) === count($gradeitems) + 1) {
                $rows[] = new html_table_row($bodycells);
            }
        }

        // Render table cells of grade items.
        foreach ($gradeitems as $shortcut => $gradeitem) {
            $headcells[] = $this->get_gradeitem_head_cell($gradeitem, $shortcut, $gradeitemsstatuses[$gradeitem->id] ?? null,
                $studentscount);
        }

        // If pagination is needed, get rows by limit and offset.
        $rows = $this->get_page_rows($studentscount, $rows);

        $this->table = $this->get_gradingtable($headcells, $rows);
        $this->footer = new gradingtable_footer($studentscount, course_get_format($COURSE->id)->get_groups(),
            course_get_format($COURSE)->get_current_group_id());
        $this->css = 'border-bottom mb-3 pb-3';
        if (course_get_format($COURSE)->get_placement() === format_etask::PLACEMENT_BELOW) {
            $this->css = 'border-top mt-4 pt-4';
        }
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->table = html_writer::table($this->table);
        $data->footer = $output->render($this->footer);
        $data->css = $this->css;

        return $data;
    }

    /**
     * Return no students found row, i.e. cell with message following by empty cells with slash for each grade item.
     *
     * @param grade_item[]
     *
     * @return html_table_row
     * @throws coding_exception
     */
    private function get_no_students_row(array $gradeitems): html_table_row {
        $cell = new html_table_cell();
        $cell->text = get_string('nostudentsfound', 'format_etask');
        $cell->attributes = ['class' => 'text-nowrap p-2 bg-white'];

        $bodycells[] = $cell;

        do {
            $bodycells[] = $this->get_empty_cell();
        } while (next($gradeitems));

        return new html_table_row($bodycells);
    }

    /**
     * Return no grade items found, i.e. cell with message.
     *
     * @return html_table_cell
     * @throws coding_exception
     */
    private function get_no_gradeitems_cell(): html_table_cell {
        $cell = new html_table_cell();
        $cell->text = get_string('nogradeitemsfound', 'format_etask');
        $cell->attributes = ['class' => 'text-nowrap p-2 font-weight-normal'];

        return $cell;
    }

    /**
     * Return empty cell containing slash.
     *
     * @return html_table_cell
     */
    private function get_empty_cell(): html_table_cell {
        $cell = new html_table_cell();
        $cell->text = '-';
        $cell->attributes = ['class' => 'text-center'];

        return $cell;
    }

    /**
     * Return student cell containing user picture and full name as a link to the profile detail.
     *
     * @param stdClass $user
     *
     * @return html_table_cell
     */
    private function get_student_cell(stdClass $user): html_table_cell {
        global $OUTPUT;

        $cell = new html_table_cell();
        $cell->text = $OUTPUT->user_picture($user, ['size' => 35, 'link' => true, 'includefullname' => true,
            'visibletoscreenreaders' => false]);
        $cell->attributes = ['class' => 'text-nowrap pr-2'];

        return $cell;
    }

    /**
     * Return grade item body cell containing grade value text (or link to edit grade if permissions).
     *
     * @param grade_item $gradeitem
     * @param stdClass $user
     * @param string $status
     *
     * @return html_table_cell
     */
    private function get_gradeitem_body_cell(grade_item $gradeitem, stdClass $user, string $status): html_table_cell {
        global $COURSE, $OUTPUT;

        $cell = new html_table_cell();
        $cell->text = $OUTPUT->render(new gradeitem_body($gradeitem, $user, $status));
        $cell->attributes = [
            'class' => 'position-relative text-center text-nowrap p-2 '
                . course_get_format($COURSE)->transform_status_to_css($status),
            'title' => fullname($user) . ', ' . $gradeitem->itemname
        ];

        return $cell;
    }

    /**
     * Return grade item head cell containing grade item shortcut with a popover containing grade item details (and settings if
     * permissions).
     *
     * @param grade_item $gradeitem
     * @param string $shortcut
     * @param array|null $gradeitemstatuses
     * @param int $studentscount
     *
     * @return html_table_cell
     */
    private function get_gradeitem_head_cell(grade_item $gradeitem, string $shortcut, ?array $gradeitemstatuses,
            int $studentscount): html_table_cell {
        global $OUTPUT;

        $cell = new html_table_cell();
        $cell->text = $OUTPUT->render(new gradeitem_head($gradeitem, $shortcut, $gradeitemstatuses, $studentscount));
        $cell->attributes = ['class' => 'text-center text-nowrap'];

        return $cell;
    }

    /**
     * Return html table containing table head and body.
     *
     * @param html_table_cell[] $headcells
     * @param html_table_row[] $rows
     *
     * @return html_table
     */
    private function get_gradingtable(array $headcells, array $rows): html_table {
        $table = new html_table();
        $table->attributes = ['class' => 'grade-table table-hover table-striped table-condensed table-responsive mb-3',
            'table-layout' => 'fixed'];
        $table->head = $headcells;
        $table->data = $rows;

        return $table;
    }

    /**
     * Return page rows as they are or by limit and offset if pagination is needed.
     *
     * @param int $studentscount
     * @param html_table_row[] $rows
     *
     * @return html_table_row[]
     */
    private function get_page_rows(int $studentscount, array $rows): array {
        global $COURSE;

        $studentsperpage = course_get_format($COURSE)->get_students_per_page();
        if ($studentscount <= $studentsperpage) {
            return $rows;
        }

        $currentpage = course_get_format($COURSE)->get_current_page($studentscount, $studentsperpage);

        return array_slice($rows, $currentpage * $studentsperpage, $studentsperpage, true);
    }
}
