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
 * This file contains main class for the course format Topic
 *
 * @since Moodle 2.0
 * @package format_etask
 * @copyright 2017 Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Additional lib class for the eTask topics course format
 *
 * @package format_etask
 * @copyright 2016 Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FormatEtaskLib
{

    /**
     * @var string
     */
    const STATUS_COMPLETED = 'completed';

    /*
     * @var string
     */
    const STATUS_PASSED = 'passed';

    /**
     * @var string
     */
    const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    const STATUS_NONE = 'none';

    /**
     * @var int
     */
    const STUDENTS_PER_PAGE_DEFAULT = 10;

    /**
     * @var string
     */
    const ACTIVITIES_SORTING_LATEST = 'latest';

    /**
     * @var string
     */
    const ACTIVITIES_SORTING_OLDEST = 'oldest';

    /**
     * @var string
     */
    const ACTIVITIES_SORTING_INHERIT = 'inherit';

    /**
     * @var string
     */
    const PLACEMENT_ABOVE = 'above';

    /**
     * @var string
     */
    const PLACEMENT_BELOW = 'below';


    /**
     * Return module items.
     *
     * @param course_modinfo $modInfo
     * @return array
     */
    public function getModItems(course_modinfo $modInfo): array
    {
        $modItems = [];
        foreach ($modInfo->cms as $cm) {
            $modItems[$cm->modname][$cm->instance] = $cm->id;
        }

        return $modItems;
    }

    /**
     * Array of scale values.
     *
     * @param int $scaleId
     * @return array
     */
    public function getScale(int $scaleId): array
    {
        global $DB;

        $scale = $DB->get_field('scale', 'scale', [
            'id' => $scaleId
        ], IGNORE_MISSING);

        return make_menu_from_list($scale);
    }

    /**
     * Return due date of grade item.
     *
     * @param grade_item $gradeItem
     * @param string $completionExpected
     * @return string
     */
    public function getDueDate(grade_item $gradeItem, string $completionExpected): string
    {
        global $DB;

        $timestamp = '';
        $gradeDateFields = $this->getGradeDateFields();

        if (isset($gradeDateFields[$gradeItem->itemmodule])) {
            $timestamp = $DB->get_field($gradeItem->itemmodule, $gradeDateFields[$gradeItem->itemmodule], [
                'id' => $gradeItem->iteminstance
            ], IGNORE_MISSING);
        }

        $dueDate = '';
        if (!empty($timestamp)) {
            $dueDate = userdate($timestamp);
        } elseif (!empty($completionExpected)) {
            $dueDate = userdate($completionExpected);
        }

        return $dueDate;
    }

    /**
     * Set gradepass value for grade item.
     *
     * @param context $context
     * @param int $gradeItemId
     * @return array
     */
    public function updateGradePass(context $context, int $gradeItemId): array
    {
        global $DB;

        $messageData = [];
        if (data_submitted() && confirm_sesskey() && has_capability('format/etask:teacher', $context)) {
            $gradePassValue = required_param('gradePass' . $gradeItemId, PARAM_INT);

            $gradeItemObj = new grade_item();
            $gradeItem = $gradeItemObj->fetch([
                'id' => $gradeItemId
            ]);
            $gradeItem->id = $gradeItemId;
            $gradeItem->gradepass = $gradePassValue;

            if (!empty($gradeItem->scaleid)) {
                $scale = $this->getScale($gradeItem->scaleid);
                $gradePass = isset($scale[$gradePassValue]) ? $scale[$gradePassValue] : '-';
            } else {
                $gradePass = $gradePassValue;
            }

            $res = $DB->update_record('grade_items', $gradeItem);

            if ($res !== false) {
                $messageData = [
                    'message' => get_string('gradesavingsuccess', 'format_etask', [
                        'itemName' => $gradeItem->itemname,
                        'gradePass' => $gradePass
                    ]),
                    'success' => true
                ];
            } else {
                $messageData = [
                    'message' => get_string('gradesavingerror', 'format_etask', $gradeItem->itemname),
                    'success' => false
                ];
            }
        }

        return $messageData;
    }

    /**
     * Return grade stasus.
     *
     * @param grade_item $gradeItem
     * @param float $grade
     * @param bool $activityCompletionState
     * @return string
     */
    public function getGradeItemStatus(
        grade_item $gradeItem,
        float $grade,
        bool $activityCompletionState): string
    {
        $gradePass = (int) $gradeItem->gradepass;
        // Activity no have grade value and have completed status or is marked as completed.
        if (empty($grade) && $activityCompletionState === true) {
            $status = self::STATUS_COMPLETED;
        // Activity no have grade value and is not completed or grade to pass is not set.
        } elseif (empty($grade) || empty($gradePass)) {
            $status = self::STATUS_NONE;
        // Activity grade value is higher then grade to pass.
        } elseif ($grade >= $gradePass) {
            $status = self::STATUS_PASSED;
        // Activity grade value is lower then grade to pass.
        } elseif ($grade < $gradePass) {
            $status = self::STATUS_FAILED;
        }

        return $status;
    }

    /**
     * Get allowed course students.
     *
     * @param context_course $context
     * @param stdClass $course
     * @param int $selectedGroup
     * @return array
     */
    public function getStudents(context_course $context, stdClass $course, int $selectedGroup = null): array
    {
        global $USER;

        $users = get_enrolled_users($context);
        // Get logged in user groups membership.
        $loggedInUserGroups = current(groups_get_user_groups($course->id, $USER->id));
        // In the grading table show only users with role 'student'.
        $students = [];
        foreach ($users as $user) {
            $isAllowedUser = $this->isAllowedUser($context, $course, $user, $selectedGroup, $loggedInUserGroups);
            if ($isAllowedUser === true) {
                $students[$user->id] = $user;
            }
        }

        return $students;
    }

    /**
     * Course groups.
     *
     * @param int $courseId
     * @return array
     */
    public function getCourseGroups(int $courseId): array
    {
        $courseGroupsObjects = groups_get_all_groups($courseId);
        $courseGroups = [];
        foreach ($courseGroupsObjects as $courseGroup) {
            $courseGroups[$courseGroup->id] = $courseGroup->name;
        }
        return $courseGroups;
    }

    /**
     * Returns eTask config.
     *
     * @param stdClass $course
     * @return array
     */
    public function getEtaskConfig(stdClass $course): array
    {
        $config = course_get_format($course)->get_course();
        return [
            'privateview' => (bool) $config->privateview ?? (bool) get_config('format_etask', 'private_view') ?? true,
            'progressbars' => (bool) $config->progressbars ?? (bool) get_config('format_etask', 'calculate_progress_bars') ?? true,
            'studentsperpage' => (int) $config->studentsperpage ?? (int) get_config('format_etask', 'students_per_page') ?? self::STUDENTS_PER_PAGE_DEFAULT,
            'activitiessorting' => $config->activitiessorting ?? self::ACTIVITIES_SORTING_LATEST,
            'placement' => $config->placement ?? self::PLACEMENT_ABOVE,
        ];
    }

    /**
     * Sort grade items by sections.
     *
     * @param array $gradeItems
     * @param array $modItems
     * @param array $sections
     * @return array
     */
    public function sortGradeItemsBySections(array $gradeItems, array $modItems, array $sections): array
    {
        $sequence = [];
        $sorted = [];
        // Prepare sequence array. Sequence contains an array of grade items.
        foreach ($sections as $section) {
            foreach ($section as $order) {
                $sequence[$order][] = $order;
            }
        }

        // Prepare associative array of grade item instance and grade item ids for this instance.
        foreach ($gradeItems as $gradeItem) {
            $gradeItemInstanceIds[$modItems[$gradeItem->itemmodule][$gradeItem->iteminstance]][] = $gradeItem->id;
        }

        // Replace sequence array with grade item instance ids. Sequence must contains grade item instances only.
        $sequence = array_replace(array_intersect_key($sequence, $gradeItemInstanceIds), $gradeItemInstanceIds);

        // Prepare array of sorted grade item ids.
        $sortedGradeItemIds = [];
        foreach ($sequence as $gradeItemInstance) {
            foreach ($gradeItemInstance as $id) {
                $sortedGradeItemIds[] = $id;
            }
        }

        // Sort grade items.
        foreach ($sortedGradeItemIds as $gradeItemId) {
            $sorted[$gradeItemId] = $gradeItems[$gradeItemId];
        }

        return $sorted;
    }

    /**
     * Is user allowed in grade table view.
     *
     * @param context_course $context
     * @param stdClass $course
     * @param stdClass $user
     * @param int $selectedGroup
     * @param array $loggedInUserGroups
     * @return bool
     */
    private function isAllowedUser(
        context_course $context,
        stdClass $course,
        stdClass $user,
        int $selectedGroup = null,
        array $loggedInUserGroups = null): bool
    {
        $isAllowedUser = false;
        // Default state of allowed user group (no groups mode).
        $allowedUserGroup = true;
        // Get enroled user groups membership.
        $userGroups = current(groups_get_user_groups($course->id, $user->id));
        if (!empty($userGroups)) {
            // Filter users by filter or show students from logged in user group.
            if (!empty($selectedGroup)) {
                // Check if user is in allowed group.
                if (in_array($selectedGroup, $userGroups) === false) {
                    $allowedUserGroup = false;
                }
            } else {
                // Check if user is in allowed group.
                foreach ($userGroups as $userGroup) {
                    if (in_array($userGroup, $loggedInUserGroups) === false) {
                        $allowedUserGroup = false;
                    }
                }
            }
        }

        if ($allowedUserGroup === true && has_capability('format/etask:student', $context, $user, false)) {
            $isAllowedUser = true;
        }
        return $isAllowedUser;
    }

    /**
     * Get grade date fields array from config text.
     *
     * @return array
     */
    private function getGradeDateFields(): array
    {
        $gradeDateFields = [];
        $config = get_config('format_etask', 'registered_due_date_modules');
        $items = explode(',', $config);
        foreach ($items as $item) {
            if (!empty($item)) {
                list($module, $duedate) = explode(':', $item);
                $gradeDateFields[trim($module)] = trim($duedate);
            }
        }
        return $gradeDateFields;
    }
}
