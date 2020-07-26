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

use format_etask;
use html_table;
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
        $students = course_get_format($this->page->course)->get_gradable_students();
        // Get students count for pagination.
        $studentscount = course_get_format($this->page->course)->get_students_count($students);
        // Get sorted grade items.
        $gradeitems = course_get_format($this->page->course)->get_sorted_gradeitems();

        /** @var html_table_row[] $data */
        $data = [];
        /** @var array<int, string[]> $gradeitemsstatuses */
        $gradeitemsstatuses = [];
        // Table head.
        $headcells = [new html_table_cell()]; // First cell of the head is empty.



        //@todo move renderer logic



        // ----- DONE ----
//        $table = new html_table();
//        $table->attributes = [
//            'class' => 'grade-table table-hover table-striped table-condensed table-responsive mb-3',
//            'table-layout' => 'fixed'
//        ];
//        $table->head = $headcells;
//        $table->data = $data;
//        $this->table = $table;

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
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->table = html_writer::table($this->table);
        $data->footer = $output->render($this->footer);
        $data->css = $this->css;

        return $data;
    }
}