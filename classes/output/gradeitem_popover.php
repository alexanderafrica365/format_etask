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
 * Class containing data for grade item popover.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output;

defined('MOODLE_INTERNAL') || die();

use grade_item;
use html_writer;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
use stdClass;
use templatable;

/**
 * Class to prepare a grade item popover for display.
 *
 * @package format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradeitem_popover implements renderable, templatable {

    /** @var int */
    private $completed;

    /** @var int */
    private $passed;

    /** @var int|null */
    private $duedate;

    /** @var string */
    private $gradepass;

    /** @var string */
    private $grademax;

    /** @var bool */
    private $showprogressbars;

    /** @var string */
    private $itemmodule;

    /** @var single_select|null */
    private $select = null;

    /** @var bool */
    private $showsettings;

    /** @var string */
    private $viewurl;

    /** @var string */
    private $editurl;

    /**
     * The popover constructor.
     *
     * @param grade_item $gradeitem
     * @param int $completed
     * @param int $passed
     * @param int|null $duedate
     * @param string $gradepass
     * @param string $grademax
     */
    public function __construct(grade_item $gradeitem, int $completed, int $passed, ?int $duedate,
        ?string $gradepass, string $grademax) {

        global $PAGE;

        $cmid = (int) get_fast_modinfo($PAGE->course->id)->instances[$gradeitem->itemmodule][$gradeitem->iteminstance]->id;

        $this->itemname = $gradeitem->itemname;
        $this->timemodified = $gradeitem->timemodified;
        $this->completed = $completed;
        $this->passed = $passed;
        $this->duedate = $duedate;
        $this->gradepass = $gradepass;
        $this->grademax = $grademax;
        $this->showprogressbars = course_get_format($PAGE->course)->show_grade_item_progress_bars();
        $this->itemmodule = $gradeitem->itemmodule;
        $this->showsettings = has_capability('moodle/course:manageactivities', $PAGE->context);
        $this->viewurl = new moodle_url('/mod/' . $gradeitem->itemmodule . '/view.php', [
            'id' => $cmid
        ]);

        if ($this->showsettings === true) {
            $action = new moodle_url(
                '/course/format/etask/update_settings.php',
                [
                    'course' => $PAGE->course->id,
                    'gradeitemid' => $gradeitem->id,
                    'itemname' => $gradeitem->itemname,
                    'sesskey' => sesskey(),
                ]
            );

            $select = new single_select($action, 'gradepass', $this->get_options($gradeitem), round($gradeitem->gradepass, 0),
                [get_string('choose', 'format_etask')]);
            $select->set_label(get_string('gradepass', 'grades'), ['class' => 'mb-0']);
            $select->attributes = ['onchange' => 'this.form.submit()'];

            $this->select = $select;

            $sesskey = sesskey();
            $sectionreturn = optional_param('sr', 0, PARAM_INT);
            $this->editurl = new moodle_url('/course/mod.php', [
                'sesskey' => $sesskey,
                'sr' => $sectionreturn,
                'update' => $cmid
            ]);
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
        $data->itemname = $this->itemname;
        $data->timemodified = $this->timemodified;
        $data->completed = $this->completed;
        $data->passed = $this->passed;
        $data->duedate = $this->duedate;
        $data->gradepass = $this->gradepass;
        $data->grademax = $this->grademax;
        $data->showprogressbars = $this->showprogressbars;
        $data->itemmoduleicon = html_writer::img($output->image_url('icon', $this->itemmodule), '', [
            'class' => 'icon itemicon'
        ]);
        $data->settingsicon = $output->pix_icon('t/edit', get_string('edit'), 'core', ['class' => 'icon itemicon']);
        $data->select = $this->select ? $output->box($output->render($this->select), 'mt-n3') : null;
        $data->showsettings = $this->showsettings;
        $data->viewurl = $this->viewurl;
        $data->editurl = $this->editurl;
        $data->margintop = $this->gradepass !== null || $this->duedate !== null;

        return $data;
    }

    /**
     * Return gradepass options for select.
     *
     * @param grade_item $gradeitem
     *
     * @return array
     */
    private function get_options(grade_item $gradeitem): array {
        if (($scale = $gradeitem->load_scale()) !== null) {
            return make_menu_from_list($scale->scale);
        }

        $grademax = round($gradeitem->grademax, 0);
        $options = [];
        for ($i = $grademax; $i >= 1; --$i) {
            $options[$i] = $i;
        }

        return $options;
    }
}
