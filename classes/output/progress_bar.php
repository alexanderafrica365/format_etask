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
 * Class containing data for my overview block.
 *
 * @package    format_etask
 * @copyright  2019 Martin Drlik <martin.drlik@email.cz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_etask\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;


/**
 * Class to prepare a progress bar for display.
 *
 * @package format_etask
 * @copyright 2019 Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_bar implements renderable, templatable {

    /**
     * @var int
     */
    private $progressvalue;

    /**
     * @var string
     */
    private $progressstate;

    /**
     * progress_bar constructor.
     *
     * @param int $progressvalue
     * @param string $progressstate
     */
    public function __construct(int $progressvalue, string $progressstate) {
        $this->progressvalue = $progressvalue;
        $this->progressstate = $progressstate;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new \stdClass();
        $data->progressValue = $this->progressvalue;
        $data->progressState = $this->progressstate;
        return $data;
    }
}