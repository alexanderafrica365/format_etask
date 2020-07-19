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
 * Class containing data for grade item head cell content.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

use grade_item;
use html_writer;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to prepare a grade item head cell content for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradeitem_head implements renderable, templatable {

    /** @var grade_item */
    private $gradeitem;

    /** @var string */
    private $shortcut;

    /** @var float */
    private $progresscompleted;

    /** @var float */
    private $progresspassed;

    /** @var int|null */
    private $duedate;

    /** @var string */
    private $gradepass;

    /** @var string */
    private $grademax;

    /** @var string */
    private $itemmodule;

    public function __construct(grade_item $gradeitem, string $shortcut, array $gradeitemstatuses, int $studentscount) {
        global $PAGE;

        [$progresscompleted, $progresspassed] = course_get_format($PAGE->course)->get_progress_values(
            $gradeitemstatuses, $studentscount);
        $duedate = course_get_format($PAGE->course)->get_due_date($gradeitem);
        $gradepass = grade_format_gradevalue($gradeitem->gradepass, $gradeitem, true, null, 0);
        $grademax = grade_format_gradevalue($gradeitem->grademax, $gradeitem, true, null, 0);

        $this->gradeitem = $gradeitem;
        $this->shortcut = $shortcut;
        $this->progresscompleted = $progresscompleted;
        $this->progresspassed = $progresspassed;
        $this->duedate = $duedate;
        $this->gradepass = $gradepass;
        $this->grademax = $grademax;
        $this->itemmodule = $gradeitem->itemmodule;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->popover = $output->render(new popover($this->gradeitem, $this->progresscompleted, $this->progresspassed,
            $this->duedate, $this->gradepass, $this->grademax));
        $data->imageurl = $output->image_url('icon', $this->itemmodule);
        $data->shortcut = $this->shortcut;

        return $data;
    }
}