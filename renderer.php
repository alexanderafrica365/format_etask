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
     * @param int $cmid
     * @param int $completionexpected
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function render_activities_head(grade_item $gradeitem, int $itemnum, int $studentscount, array $progressbardata,
            int $cmid, int $completionexpected): string {

        // Get progress values.
        [$progresscompleted, $progresspassed] = course_get_format($this->page->course)->get_progress_values(course_get_format(
            $this->page->course)->show_activity_progress_bars(), $progressbardata, $studentscount);

        // Prepare module icon.
        $ico = html_writer::img($this->output->image_url('icon', $gradeitem->itemmodule), '', [
            'class' => 'icon itemicon mr-1'
        ]);

        // Get duedate timestamp and gradepass string.
        $duedate = course_get_format($this->page->course)->get_due_date($gradeitem, $completionexpected);
        $gradepass = grade_format_gradevalue($gradeitem->gradepass, $gradeitem, true, null, 0);
        $grademax = grade_format_gradevalue($gradeitem->grademax, $gradeitem, true, null, 0);

        // Create popover from template.
        $popover = new popover($gradeitem, $progresscompleted, $progresspassed, $duedate, $gradepass, $grademax, course_get_format(
            $this->page->course)->show_activity_progress_bars(), $cmid);

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
     * @param grade_grade $usergrade
     * @param grade_item $gradeitem
     * @param bool $activitycompletionstate
     * @param stdClass $user
     * @return array
     */
    private function render_activity_body(grade_grade $usergrade, grade_item $gradeitem, bool $activitycompletionstate,
            stdClass $user): array {
        $status = course_get_format($this->page->course)->get_grade_item_status((int) $gradeitem->gradepass, $usergrade->finalgrade ?? 0.0, $activitycompletionstate);
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
     * Render grade table.
     *
     * @param context_course $context
     * @param stdClass $course
     * @return void
     */
    public function render_grade_table(context_course $context, stdClass $course) {
        global $CFG;
        global $USER;
        global $SESSION;
        global $PAGE;

        echo '
            <style type="text/css" media="screen" title="Graphic layout" scoped>
            <!--
                @import "' . $CFG->wwwroot . '/course/format/etask/format_etask.css?v=30' . get_config('format_etask', 'version') . '";
            -->
            </style>'; // @todo remove it after moving styles to style.css

        // Get mod info and prepare mod items.
        $modinfo = get_fast_modinfo($course);
        $moditems = course_get_format($this->page->course)->get_mod_items($modinfo);

        // Get all allowed course students.
        $students = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', course_get_format(
            $this->page->course)->get_current_group_id(), 'u.*', null, 0, 0, true);
        // Students count for pagination.
        $studentscount = count($students);

        // Init grade items and students grades.
        $gradeitems = [];
        $usersgrades = [];
        // Collect students grades for all grade items.
        if (!empty($students)) {
            $gradeitems = grade_item::fetch_all(['courseid' => $course->id, 'itemtype' => 'mod', 'hidden' => false]);
            if ($gradeitems) {
                // Grade items num.
                $gradeitemsnum = [];
                foreach ($gradeitems as $gradeitem) {
                    //@todo this foreach is doubled, but we need prepare item num before ordering
                    if (!isset($initnum[$gradeitem->itemmodule])) {
                        $initnum[$gradeitem->itemmodule] = 0;
                    }

                    if (!isset($gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance])) {
                        $gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance] = ++$initnum[$gradeitem->itemmodule];
                    }
                }

                // Sorting activities by course settings.
                switch (course_get_format($this->page->course)->get_activities_sorting()) {
                    case format_etask::ACTIVITIES_SORTING_OLDEST:
                        ksort($gradeitems);
                        break;
                    case format_etask::ACTIVITIES_SORTING_INHERIT:
                        $gradeitems = course_get_format($this->page->course)->sort_grade_items_by_sections($gradeitems, $moditems,
                            $modinfo->sections);
                        break;
                    default:
                        krsort($gradeitems);
                        break;
                }

                // @todo this foreach is 3x in the code
                foreach ($gradeitems as $gradeitem) {
                    $usersgrades[$gradeitem->id] = grade_grade::fetch_users_grades($gradeitem, array_keys($students), true);
                }
            }
        }

        $privateview = false;
        $privateviewuserid = 0;
        // If private view is active, students can view only own grades.
        if (course_get_format($this->page->course)->is_student_privacy()) {
            $privateview = true;
            $privateviewuserid = $USER->id;
            $studentscount = 1;
        }

        $completion = new completion_info($this->page->course);
        $activitycompletionstates = [];
        $completionexpected = [];
        $data = [];
        $progressbardata = [];
        // Move logged in student at the first position in the grade table.
        if (isset($students[$USER->id]) && $privateview === false) {
            // @todo use array pop/shift?
            $loggedinstudent = isset($students[$USER->id]) ? $students[$USER->id] : null;
            unset($students[$USER->id]);
            array_unshift($students , $loggedinstudent);
        }
        foreach ($students as $user) {
            $bodycells = [];
            if ($privateview === false || ($privateview === true && $user->id === $privateviewuserid)) {
                $cell = new html_table_cell();
                $cell->text = $this->render_user($user);
                $cell->attributes = [
                    'class' => 'text-nowrap pr-2'
                ];
                $bodycells[] = $cell;
            }

            foreach ($modinfo->cms as $cm) {
                $completionexpected[$cm->id] = (int) $cm->completionexpected; // @todo comment - it is like due date
                $activitycompletionstates[$cm->id] = (bool) $completion->get_data(
                    $cm, true, $user->id, $modinfo
                )->completionstate;
            }

            foreach ($gradeitems as $gradeitem) {
                $activitycompletionstate = $activitycompletionstates[$moditems[$gradeitem->itemmodule][$gradeitem->iteminstance]];
                // @todo make list [text, status]
                $grade = $this->render_activity_body(
                    $usersgrades[$gradeitem->id][$user->id], $gradeitem, $activitycompletionstate, $user
                );
                $progressbardata[$gradeitem->id][] = $grade['status'];
                if ($privateview === false || ($privateview === true && $user->id === $privateviewuserid)) {
                    $cell = new html_table_cell();
                    $cell->text = $grade['text'];
                    $cell->attributes = [
                        'class' => 'position-relative text-center text-nowrap p-2 ' . $grade['status'],
                        'title' => $user->firstname . ' ' . $user->lastname . ': ' . $gradeitem->itemname
                    ];
                    $bodycells[] = $cell;
                }
            }

            if ($privateview === false || ($privateview === true && $user->id === $privateviewuserid)) {
                $row = new html_table_row($bodycells);
                $data[] = $row;
            }
        }
        // Table head.
        $headcells = ['']; // First cell of the head is empty.
        // Render table cells.
        foreach ($gradeitems as $gradeitem) {
            $cmid = (int) $moditems[$gradeitem->itemmodule][$gradeitem->iteminstance];
            $cell = new html_table_cell();
            $cell->text = $this->render_activities_head(
                $gradeitem,
                $gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance],
                count($students),
                $progressbardata[$gradeitem->id],
                $cmid,
                $completionexpected[$cmid]);
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
