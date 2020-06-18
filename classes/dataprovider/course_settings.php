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
 * Class containing course settings data.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\dataprovider;

defined('MOODLE_INTERNAL') || die();

use context;
use format_etask;
use stdClass;

/**
 * Class to provide course settings data.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings {

    /**
     * @var context
     */
    private $context;

    /**
     * @var stdClass|null
     */
    private $course;

    /**
     * Course settings constructor.
     *
     * @param context $context
     */
    public function __construct(context $context, ?stdClass $course) {
        $this->context = $context;
        $this->course = $course;
    }

    /**
     * If true, student privacy is required.
     *
     * @return bool
     */
    public function is_student_privacy(): bool {
        if (has_capability('moodle/grade:viewall', $this->context)) {
            return false;
        }

        return (bool) $this->course->studentprivacy;
    }

    /**
     * If true, activity progress bars can be shown.
     *
     * @return bool
     */
    public function show_activity_progress_bars(): bool {
        if (has_capability('moodle/grade:viewall', $this->context)) {
            return true;
        }

        return (bool) $this->course->activityprogressbars;
    }

    /**
     * Get students per page.
     *
     * @return int
     */
    public function get_students_per_page(): int {
        return $this->course->studentsperpage ?? format_etask::STUDENTS_PER_PAGE_DEFAULT;
    }

    /**
     * Get activities sorting.
     *
     * @return string
     */
    public function get_activities_sorting(): string {
        return $this->course->activitiessorting ?? format_etask::ACTIVITIES_SORTING_LATEST;
    }

    /**
     * Get grading table placement.
     *
     * @return string
     */
    public function get_placement(): string {
        return $this->course->placement ?? format_etask::PLACEMENT_ABOVE;
    }
}
