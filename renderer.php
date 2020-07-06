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
require_once($CFG->dirroot.'/course/format/topics/renderer.php');
require_once($CFG->dirroot.'/course/format/etask/classes/output/popover.php');

use format_etask\dataprovider\course_settings;
use format_etask\form\group_form;
use format_etask\form\settings_form;
use format_etask\output\footer;
use format_etask\output\popover;

/**
 * Basic renderer for eTask topics format.
 *
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_etask_renderer extends format_topics_renderer {

    /**
     * HTML representation of user picture and name with link to the profile.
     *
     * @param stdClass $user
     * @return string
     */
    private function render_user(stdClass $user): string {
        //@todo move this method to separated output class (see footer/popover)
        return $this->output->user_picture($user, ['size' => 35, 'link' => true, 'includefullname' => true,
            'visibletoscreenreaders' => false]);
    }

    /**
     * HTML representation of activities head.
     *
     * @param grade_item $gradeitem
     * @param int $itemnum
     * @param int $studentscount
     * @param array $progressbardata
     *
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function render_activities_head(grade_item $gradeitem, int $itemnum, int $studentscount, array $progressbardata): string {

        // Get progress values.
        [$progresscompleted, $progresspassed] = course_get_format($this->page->course)->get_progress_values(course_get_format(
            $this->page->course)->show_grade_item_progress_bars(), $progressbardata, $studentscount);

        // Prepare module icon.
        $ico = html_writer::img($this->output->image_url('icon', $gradeitem->itemmodule), '', [
            'class' => 'icon itemicon mr-1'
        ]);

        // Get duedate timestamp and gradepass string.
        $duedate = course_get_format($this->page->course)->get_due_date($gradeitem);
        $gradepass = grade_format_gradevalue($gradeitem->gradepass, $gradeitem, true, null, 0);
        $grademax = grade_format_gradevalue($gradeitem->grademax, $gradeitem, true, null, 0);

        // Create popover from template.
        $popover = new popover($gradeitem, $progresscompleted, $progresspassed, $duedate, $gradepass, $grademax, course_get_format(
            $this->page->course)->show_grade_item_progress_bars());

        // Return grade item head link with popover.
        return html_writer::link('javascript:void(null)', implode('', [$ico, strtoupper(substr($gradeitem->itemmodule, 0, 1)),
            $itemnum]), [
                'class' => 'd-inline-block p-2 dropdown-toggle font-weight-normal',
                'data-toggle' => 'etask-popover',
                'data-content' => $this->render($popover),
            ]
        );
    }

    /**
     * Html representation of activity body.
     *
     * @param grade_item $gradeitem
     * @param stdClass $user
     * @return array
     */
    private function render_activity_body(grade_item $gradeitem, stdClass $user): array {
        global $COURSE;

        $completion = new completion_info($COURSE);
        $gradeitemcompletionstate = $completion->get_data(
            get_fast_modinfo($COURSE->id, $user->id)->instances[$gradeitem->itemmodule][$gradeitem->iteminstance],
            false,
            $user->id
        )->completionstate;
        $usergrade = $gradeitem->get_grade($user->id);
        $status = course_get_format($this->page->course)->get_grade_item_status((int) $gradeitem->gradepass, $usergrade->finalgrade ?? 0.0, $gradeitemcompletionstate);
        $gradevalue = grade_format_gradevalue($usergrade->finalgrade, $gradeitem, true, null, 0);
        if ($status === format_etask::STATUS_COMPLETED) {
            // @todo render it in the template
            $gradevalue = html_writer::tag('i', '', [
                'class' => 'fa fa-check-square-o',
                'area-hidden' => 'true'
            ]);
        }

        if (has_capability('moodle/grade:edit', $this->page->context)) {
            $gradelinkparams = [
                'courseid' => $this->page->course->id,
                'id' => $usergrade->id,
                'gpr_type' => 'report',
                'gpr_plugin' => 'grader',
                'gpr_courseid' => $this->page->course->id
            ];

            if (empty($usergrade->id)) {
                $gradelinkparams['userid'] = $user->id;
                $gradelinkparams['itemid'] = $gradeitem->id;
            }

            $gradelink = html_writer::link(new moodle_url('/grade/edit/tree/grade.php', $gradelinkparams), $gradevalue, [
                'class' => 'd-block stretched-link',
                'title' => fullname($user) . ': ' . $gradeitem->itemname
            ]);
        } else {
            $gradelink = $gradevalue;
        }

        return [
            'text' => $gradelink,
            'status' => $status
        ];
    }

    /**
     * Print grading table.
     *
     * @param context_course $context
     * @param stdClass $course
     * @return void
     */
    public function print_grading_table(context_course $context, stdClass $course) {
        global $CFG, $USER;

        echo '
            <style type="text/css" media="screen" title="Graphic layout" scoped>
            <!--
                @import "' . $CFG->wwwroot . '/course/format/etask/format_etask.css?v=30' . get_config('format_etask', 'version') . '";
            -->
            </style>'; // @todo remove it after moving styles to style.css

        // Get all allowed course students.
        $students = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', course_get_format(
            $this->page->course)->get_current_group_id(), 'u.*', null, 0, 0, true);
        // Students count for pagination.
        $studentscount = course_get_format($this->page->course)->is_student_privacy() ? 1 : count($students);

        // Init grade items and students grades.
        $gradeitems = [];
        // Collect students grades for all grade items.
        if (!empty($students)) {
            $gradeitems = grade_item::fetch_all(['courseid' => $course->id, 'itemtype' => 'mod', 'hidden' => false]);
            if ($gradeitems) {
                // Grade items num.
                $gradeitemsnum = [];
                $items = [];
                foreach ($gradeitems as $gradeitem) {
                    //@todo place it somewhere to remove deletion in progress items!
//                    $deletioninprogress = (bool) get_fast_modinfo($course->id)->instances[$gradeitem->itemmodule][$gradeitem->iteminstance]->deletioninprogress;
//                    if ($deletioninprogress) {
//                        continue;
//                    }

                    //@todo this foreach is doubled, but we need prepare item num before ordering
                    $initnum[$gradeitem->itemmodule] = $initnum[$gradeitem->itemmodule] ?? 0;
                    $gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance] = ++$initnum[$gradeitem->itemmodule];
                }

                // Sort grade items by course setting.
                $gradeitems = course_get_format($this->page->course)->sort_gradeitems($gradeitems);
            }
        }

        $data = [];
        $progressbardata = [];
        // Move logged in student at the first position in the grade table.
        if (isset($students[$USER->id]) && !course_get_format($this->page->course)->is_student_privacy()) {
            $currentuser = $students[$USER->id];
            unset($students[$USER->id]);
            array_unshift($students , $currentuser);
        }
        foreach ($students as $user) {
            $bodycells = [];
            if (!course_get_format($this->page->course)->is_student_privacy() || (course_get_format($this->page->course)->is_student_privacy() && $user->id === $USER->id)) {
                $cell = new html_table_cell();
                $cell->text = $this->render_user($user);
                $cell->attributes = [
                    'class' => 'text-nowrap pr-2'
                ];
                $bodycells[] = $cell;
            }

            foreach ($gradeitems as $gradeitem) {
                // @todo make list [text, status]
                $grade = $this->render_activity_body($gradeitem, $user);
                $progressbardata[$gradeitem->id][] = $grade['status'];
                if (!course_get_format($this->page->course)->is_student_privacy() || (course_get_format($this->page->course)->is_student_privacy() && $user->id === $USER->id)) {
                    $cell = new html_table_cell();
                    $cell->text = $grade['text'];
                    $cell->attributes = [
                        'class' => 'position-relative text-center text-nowrap p-2 ' . $grade['status'],
                        'title' => $user->firstname . ' ' . $user->lastname . ': ' . $gradeitem->itemname
                    ];
                    $bodycells[] = $cell;
                }
            }

            //@todo this condition is 3x in this file
            if (!course_get_format($this->page->course)->is_student_privacy() || (course_get_format($this->page->course)->is_student_privacy() && $user->id === $USER->id)) {
                $row = new html_table_row($bodycells);
                $data[] = $row;
            }
        }
        // Table head.
        $headcells = ['']; // First cell of the head is empty.
        // Render table cells.
        foreach ($gradeitems as $gradeitem) {
            $cell = new html_table_cell();
            $cell->text = $this->render_activities_head(
                $gradeitem,
                $gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance],
                count($students),
                $progressbardata[$gradeitem->id]
            );
            $cell->attributes = [
                'class' => 'text-center text-nowrap'
            ];
            $headcells[] = $cell;
        }

        // Slice of students by paging after geting progres bar data.
        $currentpage = course_get_format($this->page->course)->get_current_page($studentscount, course_get_format(
            $this->page->course)->get_students_per_page());
        $data = array_slice(
            $data,
            $currentpage * course_get_format($this->page->course)->get_students_per_page(),
            course_get_format($this->page->course)->get_students_per_page(),
            true
        );

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
