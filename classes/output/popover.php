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
 * Class containing data for popover.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to prepare a popover for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class popover implements renderable, templatable {

    /** @var int */
    private $completed;

    /** @var int */
    private $passed;

    /** @var string */
    private $duedate;

    /** @var string */
    private $gradetopass;

    /** @var string */
    private $type;

    /** @var bool */
    private $progressbarsallowed;

    /**
     * The popover constructor.
     *
     * @param int $completed
     * @param int $passed
     * @param string $duedate
     * @param string $gradetopass
     * @param string $type
     * @param bool $progressbarsallowed
     */
    public function __construct(int $completed, int $passed, string $duedate, string $gradetopass, string $type,
            bool $progressbarsallowed) {
        $this->completed = $completed;
        $this->passed = $passed;
        $this->duedate = $duedate;
        $this->gradetopass = $gradetopass;
        $this->type = $type;
        $this->progressbarsallowed = $progressbarsallowed;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->completed = $this->completed;
        $data->passed = $this->passed;
        $data->duedate = $this->duedate;
        $data->gradetopass = $this->gradetopass;
        $data->type = $this->type;
        $data->progressbarsallowed = $this->progressbarsallowed;

        return $data;
    }
}