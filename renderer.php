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
require_once($CFG->dirroot.'/course/format/etask/classes/dataprovider/course_settings.php');
require_once($CFG->dirroot.'/course/format/etask/classes/output/popover.php');
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
     * @var course_settings
     */
    private $coursesettings;

    /**
     * Set course settings.
     *
     * @param course_settings $coursesettings
     */
    public function set_course_settings(course_settings $coursesettings): void {
        $this->coursesettings = $coursesettings;
    }

    /**
     * HTML representation of user picture and name with link to the profile.
     *
     * @param stdClass $user
     * @return string
     */
    private function render_user(stdClass $user): string {
        return $this->output->user_picture($user, [
            'size' => 35,
            'link' => true,
            'includefullname' => true,
            'visibletoscreenreaders' => false,
        ]);
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
        [$progresscompleted, $progresspassed] = course_get_format($this->page->course)->get_progress_values($this->coursesettings->show_progress_bars(),
            $progressbardata, $studentscount);

        // Prepare module icon.
        $ico = html_writer::img($this->output->image_url('icon', $gradeitem->itemmodule), '', [
            'class' => 'icon itemicon mr-1'
        ]);

        // Get duedate timestamp and gradepass string.
        $duedate = course_get_format($this->page->course)->get_due_date($gradeitem, $completionexpected);
        $gradepass = round($gradeitem->gradepass, 0);
        if ($gradeitem->scaleid && $gradepass > 0) {
            $gradepass = course_get_format($this->page->course)->get_scale_text_value($gradeitem, $gradepass);
        }

        // Create popover from template.
        $popover = new popover($gradeitem, $progresscompleted, $progresspassed, $duedate, $gradepass, $this->coursesettings->show_progress_bars(),
            $this->coursesettings->show_settings(), $cmid);

        // Return grade item head link with popover.
        return html_writer::div(
            html_writer::link('#', implode('', [$ico, strtoupper(substr($gradeitem->itemmodule, 0, 1)), $itemnum]),
                [
                    'class' => 'd-inline-block p-2 dropdown-toggle font-weight-normal',
                    'data-toggle' => 'etask-popover',
                    'data-content' => $this->render($popover),
                ]
            )
        );
    }

    /**
     * Create grade table form.
     *
     * @param array $groups
     * @param int $studentscount
     * @param int $selectedgroup
     * @return string
     */
    private function render_grade_table_footer(array $groups, int $studentscount, int $selectedgroup = null): string {
        global $SESSION;

        $pagingbar = $this->paging_bar($studentscount, $SESSION->etask['page'] ?? 0,
            $this->coursesettings->get_students_per_page(), $this->page->url);

        return $this->render(
            new footer($pagingbar, $this->coursesettings->show_group_select($groups), $groups, $selectedgroup)
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
        $finalgrade = round($usergrade->finalgrade, 0);
        $status = course_get_format($this->page->course)->get_grade_item_status($gradeitem, $finalgrade, $activitycompletionstate);
        if (empty($usergrade->rawscaleid) && $finalgrade > 0.0) {
            $gradevalue = $finalgrade;
        } else if (!empty($usergrade->rawscaleid) && $finalgrade > 0.0) {
            $gradevalue = course_get_format($this->page->course)->get_scale_text_value($gradeitem, $finalgrade);
        } else if ($status === format_etask::STATUS_COMPLETED) {
            $gradevalue = html_writer::tag('i', '', [
                'class' => 'fa fa-check-square-o',
                'area-hidden' => 'true'
            ]);
        } else {
            $gradevalue = '&ndash;';
        }

        if (has_capability('format/etask:teacher', $this->page->context)) {
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

        course_get_format($this->page->course)->set_pagination_page(); // @todo call only if pagingbar is available by permissions

        echo '
            <style type="text/css" media="screen" title="Graphic layout" scoped>
            <!--
                @import "' . $CFG->wwwroot . '/course/format/etask/format_etask.css?v=29' . get_config('format_etask', 'version') . '";
            -->
            </style>'; // @todo remove it after moving styles to style.css

        // Pagination page into session.
        $page = optional_param('page', null, PARAM_INT);
        if (!isset($SESSION->etask['page']) && !isset($page)) {
            $SESSION->etask['page'] = 0;
        } else if (isset($SESSION->etask['page']) && isset($page)) {
            $SESSION->etask['page'] = $page;
        }

        // Get all course groups and selected group to the group filter form.
        $allcoursegroups = course_get_format($this->page->course)->get_course_groups((int)$course->id);
        $allusergroups = current(groups_get_user_groups($course->id, $USER->id));
        $selectedgroup = null;
        if (has_capability('format/etask:teacher', $context)
            || has_capability('format/etask:noneditingteacher', $context)) {
            if (!empty($SESSION->etask['group'])) {
                $selectedgroup = $SESSION->etask['group'];
            } else if (!empty($allusergroups)) {
                $selectedgroup = current($allusergroups);
            } else {
                $selectedgroup = key($allcoursegroups);
            }
        } else {
            $selectedgroup = key($allcoursegroups);
        }

        // Get mod info and prepare mod items.
        $modinfo = get_fast_modinfo($course);
        $moditems = course_get_format($this->page->course)->get_mod_items($modinfo);

        // Get all allowed course students.
        $students = course_get_format($this->page->course)->get_students($context, $course, $selectedgroup);
        // Students count for pagination.
        $studentscount = count($students);
        // Init grade items and students grades.
        $gradeitems = [];
        $usersgrades = [];
        // Collect students grades for all grade items.
        if (!empty($students)) {
            $gradeitems = grade_item::fetch_all(['courseid' => $course->id, 'itemtype' => 'mod', 'hidden' => 0]);
            if ($gradeitems === false) {
                $gradeitems = [];
            }

            if (!empty($gradeitems)) {
                // Grade items num.
                $gradeitemsnum = [];
                foreach ($gradeitems as $gradeitem) {
                    if (!isset($initnum[$gradeitem->itemmodule])) {
                        $initnum[$gradeitem->itemmodule] = 0;
                    }

                    if (!isset($gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance])) {
                        $gradeitemsnum[$gradeitem->itemmodule][$gradeitem->iteminstance] = ++$initnum[$gradeitem->itemmodule];
                    }
                }

                // Sorting activities by course settings.
                switch ($this->coursesettings->get_activities_sorting()) {
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

                foreach ($gradeitems as $gradeitem) {
                    $usersgrades[$gradeitem->id] = grade_grade::fetch_users_grades($gradeitem, array_keys($students), true);
                }
            }
        }

        $privateview = false;
        $privateviewuserid = 0;
        // If private view is active, students can view only own grades.
        if ($this->coursesettings->is_private_view()) {
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
                $completionexpected[$cm->id] = (int) $cm->completionexpected;
                $activitycompletionstates[$cm->id] = (bool) $completion->get_data(
                    $cm, true, $user->id, $modinfo
                )->completionstate;
            }

            foreach ($gradeitems as $gradeitem) {
                $activitycompletionstate = $activitycompletionstates[$moditems[$gradeitem->itemmodule][$gradeitem->iteminstance]];
                $grade = $this->render_activity_body(
                    $usersgrades[$gradeitem->id][$user->id], $gradeitem, $activitycompletionstate, $user
                );
                $progressbardata[$gradeitem->id][] = $grade['status'];
                if ($privateview === false || ($privateview === true && $user->id === $privateviewuserid)) {
                    $cell = new html_table_cell();
                    $cell->text = $grade['text'];
                    $cell->attributes = [
                        'class' => 'position-relative text-center ' . $grade['status'],
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
                'class' => 'text-ecenter text-nowrap'
            ];
            $headcells[] = $cell;
        }

        // Slice of students by paging after geting progres bar data.
        $studentsperpage = $this->coursesettings->get_students_per_page();
        $SESSION->etask['page'] = $studentscount <= $SESSION->etask['page'] * $studentsperpage
            ? 0
            : $SESSION->etask['page'];
        $data = array_slice(
            $data,
            $SESSION->etask['page'] * $studentsperpage,
            $studentsperpage,
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
        $gradetablefooter = $this->render_grade_table_footer($allcoursegroups, $studentscount, $selectedgroup);
        $css = 'border-bottom mb-3 pb-3';
        if (!$this->coursesettings->is_placement_above()) {
            $css = 'border-top mt-4 pt-4';
        }
        echo html_writer::div(
            html_writer::table($gradetable) . $gradetablefooter,
            'etask-grade-table border-secondary ' . $css
        );
    }
}
