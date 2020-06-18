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
 * Class containing data for footer.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

use coding_exception;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
use stdClass;
use templatable;

/**
 * Class to prepare a footer for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements renderable, templatable {

    /** @var single_select|null */
    private $select = null;

    /** @var string */
    private $pagingbar;

    /**
     * Footer constructor.
     *
     * @param array $groups
     * @param int $selectedgroup
     * @param ?string $pagingbar
     * @throws coding_exception
     */
    public function __construct(string $pagingbar, /*bool $showgroupselect,*/ array $groups, ?int $selectedgroup) {
        global $COURSE;

        //if ($showgroupselect) {
            $action = new moodle_url(
                '/course/format/etask/update_settings.php',
                [
                    'course' => $COURSE->id,
                ]
            );

            $select = new single_select($action, 'group', $groups, $selectedgroup, []);
            $select->set_label(get_string('group'), ['class' => 'mb-0 d-none d-md-inline']);
            $this->select = $select;
        //}

        $this->pagingbar = $pagingbar;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->select = $this->select ? $output->box($output->render($this->select), 'mt-n3') : null;
        $data->pagingbar = $this->pagingbar;

        return $data;
    }
}