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
 * This file contains main class for eTask topics course format.
 *
 * @since     Moodle 2.0
 * @package   format_etask
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/topics/lib.php');

use core\notification;
use core\output\inplace_editable;
use format_etask\dataprovider\course_settings;

/**
 * Main class for the eTask topics course format.
 *
 * @package    format_etask
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_etask extends format_topics {

    // @todo change constants to private (if possible) after lib migration

    /** @var string */
    public const STATUS_COMPLETED = 'completed';

    /** @var string */
    public const STATUS_PASSED = 'passed';

    /** @var string */
    public const STATUS_FAILED = 'failed';

    /** @var string */
    public const STATUS_NONE = 'none';

    /** @var int */
    public const STUDENTS_PER_PAGE_DEFAULT = 10;

    /** @var string */
    public const GRADEITEMS_SORTING_LATEST = 'latest';

    /** @var string */
    public const GRADEITEMS_SORTING_OLDEST = 'oldest';

    /** @var string */
    public const GRADEITEMS_SORTING_INHERIT = 'inherit';

    /** @var string */
    public const PLACEMENT_ABOVE = 'above';

    /** @var string */
    public const PLACEMENT_BELOW = 'below';

    /**
     * Definitions of the additional options that this course format uses for course.
     *
     * eTask topics format uses the following options:
     * - coursedisplay
     * - hiddensections
     * - privateview
     * - progressbars
     * - studentsperpage
     * - gradeitemssorting
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false): array {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = [
                'hiddensections' => [
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ],
                'coursedisplay' => [
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ],
                'studentprivacy' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'gradeitemprogressbars' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'studentsperpage' => [
                    'default' => self::STUDENTS_PER_PAGE_DEFAULT,
                    'type' => PARAM_INT,
                ],
                'gradeitemssorting' => [
                    'default' => self::GRADEITEMS_SORTING_LATEST,
                    'type' => PARAM_ALPHA,
                ],
                'placement' => [
                    'default' => self::PLACEMENT_ABOVE,
                    'type' => PARAM_ALPHA,
                ],
            ];
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseformatoptionsedit = [
                'hiddensections' => [
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        ],
                    ],
                ],
                'coursedisplay' => [
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi'),
                        ],
                    ],
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ],
            ];
            // The eTask topics course format settings.
            $etasksettings = [
                'studentprivacy' => [
                    'label' => new lang_string('studentprivacy', 'format_etask'),
                    'help' => 'studentprivacy',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('studentprivacy_no', 'format_etask'),
                            1 => new lang_string('studentprivacy_yes', 'format_etask'),
                        ]
                    ],
                ],
                'gradeitemprogressbars' => [
                    'label' => new lang_string('gradeitemprogressbars', 'format_etask'),
                    'help' => 'gradeitemprogressbars',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('gradeitemprogressbars_no', 'format_etask'),
                            1 => new lang_string('gradeitemprogressbars_yes', 'format_etask'),
                        ],
                    ],
                ],
                'studentsperpage' => [
                    'label' => new lang_string('studentsperpage', 'format_etask'),
                    'help' => 'studentsperpage',
                    'help_component' => 'format_etask',
                    'element_type' => 'text',
                ],
                'gradeitemssorting' => [
                    'label' => new lang_string('gradeitemssorting', 'format_etask'),
                    'help' => 'gradeitemssorting',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::GRADEITEMS_SORTING_LATEST => new lang_string(
                                'gradeitemssorting_latest', 'format_etask'
                            ),
                            self::GRADEITEMS_SORTING_OLDEST => new lang_string(
                                'gradeitemssorting_oldest', 'format_etask'
                            ),
                            self::GRADEITEMS_SORTING_INHERIT => new lang_string(
                                'gradeitemssorting_inherit', 'format_etask'
                            ),
                        ],
                    ],
                ],
                'placement' => [
                    'label' => new lang_string('placement', 'format_etask'),
                    'help' => 'placement',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::PLACEMENT_ABOVE => new lang_string(
                                'placement_above', 'format_etask'
                            ),
                            self::PLACEMENT_BELOW => new lang_string(
                                'placement_below', 'format_etask'
                            ),
                        ],
                    ],
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit, $etasksettings);
        }
        return $courseformatoptions;
    }

    /**
     * Get completed and passed percentage of grade item.
     *
     * @param bool $showprogressbars
     * @param array $progressbardata
     * @param int $studentscount
     *
     * @return array<float, float>
     */
    public function get_progress_values(bool $showprogressbars, array $progressbardata, int $studentscount): array {
        if (!$showprogressbars) {
            return [0.0, 0.0];
        }

        // Init progress bars data.
        $progressbardatainit = [
            'passed' => 0,
            'completed' => 0,
            'failed' => 0
        ];

        $progressbardatacount = array_merge($progressbardatainit, array_count_values($progressbardata));
        $progresscompleted = round(100 * (
                array_sum([
                    $progressbardatacount['completed'],
                    $progressbardatacount['passed'], $progressbardatacount['failed']
                ]) / $studentscount));
        $progresspassed = round(100 * ($progressbardatacount['passed'] / $studentscount));

        return [$progresscompleted, $progresspassed];
    }

    //-------------------------------- DONE ----------------------------------------

    /**
     * Return true if student privacy is required.
     *
     * @return bool
     */
    public function is_student_privacy(): bool {
        global $PAGE;

        if (has_capability('moodle/grade:viewall', $PAGE->context)) {
            return false;
        }

        return (bool) $this->course->studentprivacy;
    }

    /**
     * Return true if grade item progress bars can be shown.
     *
     * @return bool
     */
    public function show_grade_item_progress_bars(): bool {
        global $PAGE;

        if (has_capability('moodle/grade:viewall', $PAGE->context)) {
            return true;
        }

        return (bool) $this->course->gradeitemprogressbars;
    }

    /**
     * Return students per page.
     *
     * @return int
     */
    public function get_students_per_page(): int {
        return $this->course->studentsperpage ?? self::STUDENTS_PER_PAGE_DEFAULT;
    }

    /**
     * Return grade items sorting.
     *
     * @return string
     */
    public function get_grade_items_sorting(): string {
        return $this->course->gradeitemssorting ?? self::GRADEITEMS_SORTING_LATEST;
    }

    /**
     * Return grading table placement.
     *
     * @return string
     */
    public function get_placement(): string {
        return $this->course->placement ?? self::PLACEMENT_ABOVE;
    }

    /**
     * Return array of groups (id => name). If user has not capability to access all groups, only groups for specific user are
     * returned.
     *
     * @return array<int, string>
     * @throws coding_exception
     */
    public function get_groups(): array {
        global $PAGE, $USER;

        $userid = has_capability('moodle/site:accessallgroups', $PAGE->context) ? 0 : $USER->id;
        $groups = groups_get_all_groups($PAGE->course->id, $userid, 0, 'g.id, g.name', false);

        foreach ($groups as $id => $group) {
            /** @var array<int, string> $transformedgroups */
            $transformedgroups[$id] = $group->name;
        }

        return $transformedgroups ?? [];
    }

    /**
     * Return current group id. If user has not capability to access all groups, only groups for specific user are returned.
     *
     * @return int
     * @throws coding_exception
     */
    public function get_current_group_id(): int {
        global $SESSION, $PAGE, $USER;

        $userid = has_capability('moodle/site:accessallgroups', $PAGE->context) ? 0 : $USER->id;
        /** @var array<int, int> $groupids */
        $groupids = array_keys(groups_get_all_groups($PAGE->course->id, $userid, 0, 'g.id', false));

        if (isset($SESSION->format_etask['currentgroup']) && in_array($SESSION->format_etask['currentgroup'], $groupids)) {
            // Groupid is in the session and this session is valid with the groupids.
            $currentgroupid = $SESSION->format_etask['currentgroup'];
        } else if (count($groupids) > 0) {
            // Groupid is not in the session or is not valid with the groupids.
            $currentgroupid = $SESSION->format_etask['currentgroup'] = current($groupids);
        }

        return (int) $currentgroupid ?? 0;
    }

    /**
     * Return current pagination page.
     *
     * @return int
     * @throws coding_exception
     */
    public function get_current_page(int $studentscount, int $studentsperpage): int {
        global $COURSE, $SESSION;

        $currentpage = optional_param('page', null, PARAM_INT);
        if ($currentpage !== null) {
            return (int) $SESSION->format_etask['currentpage'] = $currentpage;
        }

        // Use "<=" because pages are numbered from 0.
        if (isset($SESSION->format_etask['currentpage']) && $studentscount <= $SESSION->format_etask['currentpage']
            * $studentsperpage && !course_get_format($COURSE)->is_student_privacy()) {
            // Set current page to last page.
            return (int) $SESSION->format_etask['currentpage'] = round($studentscount / $studentsperpage, 0) - 1;
        }

        return isset($SESSION->format_etask['currentpage']) && $SESSION->format_etask['currentpage'] > 0 ? $SESSION->format_etask['currentpage'] : 0;
    }

    /**
     * Update gradepass of grade item.
     *
     * @param int $gradeitemid
     * @param int $gradepass
     *
     * @return bool
     */
    public function update_grade_pass(int $gradeitemid, int $gradepass): bool {
        global $DB;

        $gradeitem = (new grade_item())->fetch(['id' => $gradeitemid]);
        $gradeitem->id = $gradeitemid;
        $gradeitem->gradepass = $gradepass;
        $gradeitem->timemodified = time();

        return $DB->update_record('grade_items', $gradeitem);
    }

    /**
     * Get sorted grade items.
     *
     * @return array<string, grade_item>
     */
    public function get_sorted_gradeitems(): ?array {
        global $COURSE;

        // Fetch all grade item instances.
        $gradeiteminstances = grade_item::fetch_all(['courseid' => $COURSE->id, 'itemtype' => 'mod', 'hidden' => false]);
        $gradeitems = [];

        // If grade item instances return false, e.g. no grade items -> return empty array.
        if (!$gradeiteminstances) {
            return [];
        }

        /** @var grade_item $gradeiteminstance */
        foreach ($gradeiteminstances as $gradeiteminstance) {
            // If grade item has deletion in progress, continue.
            $deletioninprogress = (bool) get_fast_modinfo($COURSE->id)->instances[$gradeiteminstance->itemmodule][$gradeiteminstance->iteminstance]->deletioninprogress;
            if ($deletioninprogress) {
                continue;
            }

            // Initialize grade item number.
            $initnum[$gradeiteminstance->itemmodule] = $initnum[$gradeiteminstance->itemmodule] ?? 0;

            if ($gradeiteminstance->itemnumber > 0) {
                $shortcut = sprintf('%s%d.%d', strtoupper(substr($gradeiteminstance->itemmodule, 0, 1)), $initnum[$gradeiteminstance->itemmodule], $gradeiteminstance->itemnumber);
            } else {
                $shortcut = sprintf('%s%d', strtoupper(substr($gradeiteminstance->itemmodule, 0, 1)), ++$initnum[$gradeiteminstance->itemmodule]);
            }

            // Collect grade items with numbering.
            $gradeitems[$shortcut] = $gradeiteminstance;
        }

        // Sort grade items by course setting.
        switch ($this->get_grade_items_sorting()) {
            case self::GRADEITEMS_SORTING_OLDEST:
                uasort($gradeitems, function($a, $b) {
                    return $a->id > $b->id;
                });
                break;
            case self::GRADEITEMS_SORTING_INHERIT:
                $gradeitems = $this->sort_grade_items_by_sections($gradeitems);
                break;
            default:
                uasort($gradeitems, function($a, $b) {
                    return $a->id < $b->id;
                });
                break;
        }

        return $gradeitems;
    }

    /**
     * Return due date of grade item.
     *
     * @param grade_item $gradeitem
     *
     * @return int|null
     */
    public function get_due_date(grade_item $gradeitem): ?int {
        global $DB, $COURSE;

        $time = null;
        $gradedatefields = $this->get_due_date_fields();

        if (isset($gradedatefields[$gradeitem->itemmodule])) {
            $time = (int) $DB->get_field($gradeitem->itemmodule, $gradedatefields[$gradeitem->itemmodule], [
                'id' => $gradeitem->iteminstance
            ], IGNORE_MISSING);
        }

        if ($time > 0) {
            return $time;
        }

        $completionexpected = (int) get_fast_modinfo($COURSE->id)->instances[$gradeitem->itemmodule][$gradeitem->iteminstance]->completionexpected;

        return $completionexpected > 0 ? $completionexpected : null;
    }

    /**
     * Return grade stasus.
     *
     * @param float $gradepass
     * @param float $grade
     * @param bool $activitycompletionstate
     *
     * @return string
     */
    public function get_grade_item_status(
        float $gradepass,
        float $grade,
        bool $activitycompletionstate): string {

        if ($grade === 0.0 && $activitycompletionstate === true) {
            // Activity no have grade value and have completed status or is marked as completed.
            $status = self::STATUS_COMPLETED;
        } else if ($grade === 0.0 || $gradepass === 0.0) {
            // Activity no have grade value and is not completed or grade to pass is not set.
            $status = self::STATUS_NONE;
        } else if ($grade >= $gradepass) {
            // Activity grade value is higher then grade to pass.
            $status = self::STATUS_PASSED;
        } else {
            // Activity grade value is lower then grade to pass ($grade < $gradepass).
            $status = self::STATUS_FAILED;
        }

        return $status;
    }

    /**
     * Sort grade items by sections.
     *
     * @param array $gradeitems
     * @return array
     */
    private function sort_grade_items_by_sections(array $gradeitems): array {
        global $COURSE;

        $sections = get_fast_modinfo($COURSE)->get_sections();
        $cmids = [];
        // Prepare order of cmids by sections.
        foreach ($sections as $section) {
            $cmids = array_merge($cmids, $section);
        }

        // Sort grade items by cmids.
        uasort($gradeitems, function($a, $b) use ($cmids, $COURSE) {
            $cmida = get_fast_modinfo($COURSE->id)->instances[$a->itemmodule][$a->iteminstance]->id;
            $cmidb = get_fast_modinfo($COURSE->id)->instances[$b->itemmodule][$b->iteminstance]->id;

            $cmpa = array_search($cmida, $cmids);
            $cmpb = array_search($cmidb, $cmids);

            return $cmpa > $cmpb;
        });

        return $gradeitems;
    }

    /**
     * Return registered due date modules.
     *
     * @return array
     */
    private function get_due_date_fields(): array {
        /** @var array<int, string> $registeredduedatemodules */
        $registeredduedatemodules = explode(',', get_config('format_etask', 'registered_due_date_modules'));

        $duedatefields = [];
        foreach ($registeredduedatemodules as $registeredduedatemodules) {
            if ($registeredduedatemodules) {
                [$module, $duedate] = explode(':', $registeredduedatemodules);
                $duedatefields[trim($module)] = trim($duedate);
            }
        }
        return $duedatefields;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return inplace_editable
 */
function format_etask_inplace_editable($itemtype, $itemid, $newvalue): inplace_editable {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'etask'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
