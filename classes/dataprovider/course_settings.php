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

use format_etask;
use moodle_page;
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
     * @var moodle_page
     */
    private $page;

    /**
     * @var stdClass|null
     */
    private $course;

    /**
     * Course settings constructor.
     *
     * @param moodle_page $page
     */
    public function __construct(moodle_page $page, ?stdClass $course) {
        $this->page = $page;
        $this->course = $course;
    }

    /**
     * True for private view.
     *
     * @return bool
     */
    public function is_private_view(): bool {
        if (!$this->course) {
            return true;
        }

        if (has_capability('format/etask:teacher', $this->page->context)
                || has_capability('format/etask:noneditingteacher', $this->page->context)) {
            return false;
        }

        return $this->course->privateview;
    }

    /**
     * True for show progress bars.
     *
     * @return bool
     */
    public function is_show_progress_bars(): bool {
        if (!$this->course) {
            return true;
        }

        return $this->course->progressbars || has_capability('format/etask:teacher', $this->page->context)
            || has_capability('format/etask:noneditingteacher', $this->page->context);
    }

    /**
     * Students per page.
     *
     * @return int
     */
    public function get_students_per_page(): int {
        if (!$this->course) {
            return format_etask::STUDENTS_PER_PAGE_DEFAULT;
        }

        return $this->course->studentsperpage ?? format_etask::STUDENTS_PER_PAGE_DEFAULT;
    }

    /**
     * Activities sorting.
     *
     * @return string
     */
    public function get_activities_sorting(): string {
        if (!$this->course) {
            return format_etask::ACTIVITIES_SORTING_LATEST;
        }

        return $this->course->activitiessorting ?? format_etask::ACTIVITIES_SORTING_LATEST;
    }

    /**
     * True for above placement.
     *
     * @return bool
     */
    public function is_above_placement(): bool {
        if (!$this->course) {
            return true;
        }

        return $this->course->placement === format_etask::PLACEMENT_ABOVE;
    }

    /**
     * Placement.
     *
     * @return string
     */
    public function get_placement(): string {
        if (!$this->course) {
            format_etask::PLACEMENT_ABOVE;
        }

        return $this->course->placement ?? format_etask::PLACEMENT_ABOVE;
    }
}
