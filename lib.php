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
    public const ACTIVITIES_SORTING_LATEST = 'latest';

    /** @var string */
    public const ACTIVITIES_SORTING_OLDEST = 'oldest';

    /** @var string */
    public const ACTIVITIES_SORTING_INHERIT = 'inherit';

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
     * - activitiessorting
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
                'privateview' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'progressbars' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'studentsperpage' => [
                    'default' => self::STUDENTS_PER_PAGE_DEFAULT,
                    'type' => PARAM_INT,
                ],
                'activitiessorting' => [
                    'default' => self::ACTIVITIES_SORTING_LATEST,
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
            // The eTask settings.
            $etasksettings = [
                'privateview' => [
                    'label' => new lang_string('privateview', 'format_etask'),
                    'help' => 'privateview',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('privateview_no', 'format_etask'),
                            1 => new lang_string('privateview_yes', 'format_etask'),
                        ]
                    ],
                ],
                'progressbars' => [
                    'label' => new lang_string('progressbars', 'format_etask'),
                    'help' => 'progressbars',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('progressbars_donotcalculate', 'format_etask'),
                            1 => new lang_string('progressbars_calculate', 'format_etask'),
                        ],
                    ],
                ],
                'studentsperpage' => [
                    'label' => new lang_string('studentsperpage', 'format_etask'),
                    'help' => 'studentsperpage',
                    'help_component' => 'format_etask',
                    'element_type' => 'text',
                ],
                'activitiessorting' => [
                    'label' => new lang_string('activitiessorting', 'format_etask'),
                    'help' => 'activitiessorting',
                    'help_component' => 'format_etask',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::ACTIVITIES_SORTING_LATEST => new lang_string(
                                'activitiessorting_latest', 'format_etask'
                            ),
                            self::ACTIVITIES_SORTING_OLDEST => new lang_string(
                                'activitiessorting_oldest', 'format_etask'
                            ),
                            self::ACTIVITIES_SORTING_INHERIT => new lang_string(
                                'activitiessorting_inherit', 'format_etask'
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
     * Return module items.
     *
     * @param course_modinfo $modinfo
     * @return array
     */
    public function get_mod_items(course_modinfo $modinfo): array {
        $moditems = [];
        foreach ($modinfo->cms as $cm) {
            $moditems[$cm->modname][$cm->instance] = $cm->id;
        }

        return $moditems;
    }

    /**
     * Array of scale values.
     *
     * @param int $scaleid
     * @return array
     */
    public function get_scale(int $scaleid): array {
        global $DB;

        $scale = $DB->get_field('scale', 'scale', [
            'id' => $scaleid
        ], IGNORE_MISSING);

        return make_menu_from_list($scale);
    }

    /**
     * Return due date of grade item.
     *
     * @param grade_item $gradeitem
     * @param string $completionexpected
     * @return string
     */
    public function get_due_date(grade_item $gradeitem, string $completionexpected): ?string {
        global $DB;

        $timestamp = null;
        $gradedatefields = $this->get_grade_date_fields();

        if (isset($gradedatefields[$gradeitem->itemmodule])) {
            $timestamp = $DB->get_field($gradeitem->itemmodule, $gradedatefields[$gradeitem->itemmodule], [
                'id' => $gradeitem->iteminstance
            ], IGNORE_MISSING);
        }

        $duedate = null;
        if ($timestamp) {
            $duedate = userdate($timestamp);
        } else if ($completionexpected) {
            $duedate = userdate($completionexpected);
        }

        return $duedate;
    }

    /**
     * Set gradepass value for grade item.
     *
     * @param context $context
     * @param int $gradeitemid
     * @return void
     */
    public function update_grade_pass(context $context, int $gradeitemid): void {
        global $DB;

        if (data_submitted() && confirm_sesskey() && has_capability('format/etask:teacher', $context)) {
            $gradepassvalue = required_param('gradepass' . $gradeitemid, PARAM_INT);

            $gradeitemobj = new grade_item();
            $gradeitem = $gradeitemobj->fetch([
                'id' => $gradeitemid
            ]);
            $gradeitem->id = $gradeitemid;
            $gradeitem->gradepass = $gradepassvalue;

            if (!empty($gradeitem->scaleid)) {
                $scale = $this->get_scale($gradeitem->scaleid);
                $gradepass = isset($scale[$gradepassvalue]) ? $scale[$gradepassvalue] : '-';
            } else {
                $gradepass = $gradepassvalue;
            }

            if ($DB->update_record('grade_items', $gradeitem)) {
                notification::success(
                    get_string('gradesavingsuccess', 'format_etask', [
                        'itemName' => $gradeitem->itemname,
                        'gradePass' => $gradepass
                    ])
                );
            } else {
                notification::error(
                    get_string('gradesavingerror', 'format_etask', $gradeitem->itemname)
                );
            }
        }
    }

    /**
     * Return grade stasus.
     *
     * @param grade_item $gradeitem
     * @param float $grade
     * @param bool $activitycompletionstate
     * @return string
     */
    public function get_grade_item_status(
        grade_item $gradeitem,
        float $grade,
        bool $activitycompletionstate): string {
        $gradepass = (int) $gradeitem->gradepass;
        if (empty($grade) && $activitycompletionstate === true) {
            // Activity no have grade value and have completed status or is marked as completed.
            $status = self::STATUS_COMPLETED;
        } else if (empty($grade) || empty($gradepass)) {
            // Activity no have grade value and is not completed or grade to pass is not set.
            $status = self::STATUS_NONE;
        } else if ($grade >= $gradepass) {
            // Activity grade value is higher then grade to pass.
            $status = self::STATUS_PASSED;
        } else if ($grade < $gradepass) {
            // Activity grade value is lower then grade to pass.
            $status = self::STATUS_FAILED;
        }

        return $status;
    }

    /**
     * Get allowed course students.
     *
     * @param context_course $context
     * @param stdClass $course
     * @param int $selectedgroup
     * @return array
     */
    public function get_students(context_course $context, stdClass $course, int $selectedgroup = null): array {
        global $USER;

        $users = get_enrolled_users($context);
        // Get logged in user groups membership.
        $loggedinusergroups = current(groups_get_user_groups($course->id, $USER->id));
        // In the grading table show only users with role 'student'.
        $students = [];
        foreach ($users as $user) {
            $isalloweduser = $this->is_allowed_user($context, $course, $user, $selectedgroup, $loggedinusergroups);
            if ($isalloweduser === true) {
                $students[$user->id] = $user;
            }
        }

        return $students;
    }

    /**
     * Course groups.
     *
     * @param int $courseid
     * @return array
     */
    public function get_course_groups(int $courseid): array {
        $coursegroupsobjects = groups_get_all_groups($courseid);
        $coursegroups = [];
        foreach ($coursegroupsobjects as $coursegroup) {
            $coursegroups[$coursegroup->id] = $coursegroup->name;
        }
        return $coursegroups;
    }

    /**
     * Returns eTask config.
     *
     * @param stdClass $course
     * @return array
     */
    public function get_etask_config(stdClass $course): array {
        return [
            'privateview' => (bool) $course->privateview ?? true,
            'progressbars' => (bool) $course->progressbars ?? true,
            'studentsperpage' => (int) $course->studentsperpage ?? self::STUDENTS_PER_PAGE_DEFAULT,
            'activitiessorting' => $course->activitiessorting ?? self::ACTIVITIES_SORTING_LATEST,
            'placement' => $course->placement ?? self::PLACEMENT_ABOVE,
        ];
    }

    /**
     * Sort grade items by sections.
     *
     * @param array $gradeitems
     * @param array $moditems
     * @param array $sections
     * @return array
     */
    public function sort_grade_items_by_sections(array $gradeitems, array $moditems, array $sections): array {
        $sequence = [];
        $sorted = [];
        // Prepare sequence array. Sequence contains an array of grade items.
        foreach ($sections as $section) {
            foreach ($section as $order) {
                $sequence[$order][] = $order;
            }
        }

        // Prepare associative array of grade item instance and grade item ids for this instance.
        foreach ($gradeitems as $gradeitem) {
            $gradeiteminstanceids[$moditems[$gradeitem->itemmodule][$gradeitem->iteminstance]][] = $gradeitem->id;
        }

        // Replace sequence array with grade item instance ids. Sequence must contains grade item instances only.
        $sequence = array_replace(array_intersect_key($sequence, $gradeiteminstanceids), $gradeiteminstanceids);

        // Prepare array of sorted grade item ids.
        $sortedgradeitemids = [];
        foreach ($sequence as $gradeiteminstance) {
            foreach ($gradeiteminstance as $id) {
                $sortedgradeitemids[] = $id;
            }
        }

        // Sort grade items.
        foreach ($sortedgradeitemids as $gradeitemid) {
            $sorted[$gradeitemid] = $gradeitems[$gradeitemid];
        }

        return $sorted;
    }

    /**
     * Is user allowed in grade table view.
     *
     * @param context_course $context
     * @param stdClass $course
     * @param stdClass $user
     * @param int $selectedgroup
     * @param array $loggedinusergroups
     * @return bool
     */
    private function is_allowed_user(
        context_course $context,
        stdClass $course,
        stdClass $user,
        int $selectedgroup = null,
        array $loggedinusergroups = null): bool {
        $isalloweduser = false;
        // Default state of allowed user group (no groups mode).
        $allowedusergroup = true;
        // Get enroled user groups membership.
        $usergroups = current(groups_get_user_groups($course->id, $user->id));
        if (!empty($usergroups)) {
            // Filter users by filter or show students from logged in user group.
            if (!empty($selectedgroup)) {
                // Check if user is in allowed group.
                if (in_array($selectedgroup, $usergroups) === false) {
                    $allowedusergroup = false;
                }
            } else {
                // Check if user is in allowed group.
                foreach ($usergroups as $usergroup) {
                    if (in_array($usergroup, $loggedinusergroups) === false) {
                        $allowedusergroup = false;
                    }
                }
            }
        }

        if ($allowedusergroup === true && has_capability('format/etask:student', $context, $user, false)) {
            $isalloweduser = true;
        }
        return $isalloweduser;
    }

    /**
     * Get grade date fields array from config text.
     *
     * @return array
     */
    private function get_grade_date_fields(): array {
        $gradedatefields = [];
        $config = get_config('format_etask', 'registered_due_date_modules');
        $items = explode(',', $config);
        foreach ($items as $item) {
            if (!empty($item)) {
                list($module, $duedate) = explode(':', $item);
                $gradedatefields[trim($module)] = trim($duedate);
            }
        }
        return $gradedatefields;
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
