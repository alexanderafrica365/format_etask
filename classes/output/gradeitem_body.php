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
 * Class containing data for grade item body cell content.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

defined('MOODLE_INTERNAL') || die();

use format_etask;
use grade_item;
use html_writer;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to prepare a grade item body cell content for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradeitem_body implements renderable, templatable {

    /** @var bool */
    private $completed;

    /** @var string */
    private $value;

    /** @var moodle_url|null  */
    private $url = null;

    /** @var string */
    private $fullname;

    /** @var string */
    private $itemname;

    /** @var string|null */
    private $css = null;

    public function __construct(grade_item $gradeitem, stdClass $user, string $status) {
        global $PAGE;

        $usergrade = $gradeitem->get_grade($user->id);

        if ($status === format_etask::STATUS_COMPLETED) {
            $this->value = html_writer::tag('i', '', ['class' => 'fa fa-check-square-o', 'area-hidden' => 'true']);
        } else {
            $this->value = grade_format_gradevalue($usergrade->finalgrade, $gradeitem, true, null, 0);
        }

        if ($status !== format_etask::STATUS_NONE) {
            $this->css = 'text-white';
        }

        if (has_capability('moodle/grade:edit', $PAGE->context) === true) {
            $this->url = new moodle_url('/grade/edit/tree/grade.php', [
                'courseid' => $PAGE->course->id,
                'id' => $usergrade->id,
                'gpr_type' => 'report',
                'gpr_plugin' => 'grader',
                'gpr_courseid' => $PAGE->course->id
            ]);

            $this->fullname = fullname($user);
            $this->itemname = $gradeitem->itemname;
        }
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->url = $this->url;
        $data->fullname = $this->fullname;
        $data->itemname = $this->itemname;
        $data->value = $this->value;
        $data->css = $this->css;

        return $data;
    }
}
