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

use grade_item;
use html_writer;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
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

    /** @var int|null */
    private $duedate;

    /** @var string */
    private $gradetopass;

    /** @var bool */
    private $showprogressbars;

    /** @var string */
    private $itemmodule;

    /** @var single_select|null */
    private $select = null;

    /** @var bool */
    private $showsettings;

    /**
     * The popover constructor.
     *
     * @param grade_item $gradeitem
     * @param int $completed
     * @param int $passed
     * @param int|null $duedate
     * @param string $gradetopass
     * @param bool $showprogressbars
     * @param bool $showsettings
     * @param int $cmid
     */
    public function __construct(grade_item $gradeitem, int $completed, int $passed, ?int $duedate,
                string $gradetopass, bool $showprogressbars, bool $showsettings, int $cmid) {
        global $COURSE;

        $this->itemname = $gradeitem->itemname;
        $this->timemodified = $gradeitem->timemodified;
        $this->completed = $completed;
        $this->passed = $passed;
        $this->duedate = $duedate;
        $this->gradetopass = $gradetopass;
        $this->showprogressbars = $showprogressbars;
        $this->itemmodule = $gradeitem->itemmodule;
        $this->showsettings = $showsettings;

        if ($this->showsettings) {
            $action = new moodle_url(
                '/course/format/etask/update_settings.php',
                [
                    'course' => $COURSE->id,
                    'gradeitemid' => $gradeitem->id,
                    'itemname' => $gradeitem->itemname,
                    'sesskey' => sesskey(),
                ]
            );

            $select = new single_select($action, 'gradepass', $this->get_options($gradeitem), round($gradeitem->gradepass, 0));
            $select->set_label(get_string('gradepass', 'grades'), ['class' => 'mb-0']);
            $select->attributes = ['onchange' => 'this.form.submit()'];

            $this->select = $select;
        }

//        $sesskey = sesskey();
//        $sectionreturn = optional_param('sr', 0, PARAM_INT);
        // Prepare activity short link.
//        if (has_capability('format/etask:teacher', $this->page->context)) {
//            $itemtitleshortlink = html_writer::link(new moodle_url('/course/mod.php', [
//                'sesskey' => $sesskey,
//                'sr' => $sectionreturn,
//                'update' => $cmid
//            ]), $ico . $itemtitleshort, [
//                'class' => 'd-inline-block p-2 dropdown-toggle font-weight-normal',
//                'data-toggle' => 'popover',
//                'data-content' => $this->render($popover)
//            ]);
//        } else {
//            $itemtitleshortlink = html_writer::link(new moodle_url('/mod/' . $gradeitem->itemmodule . '/view.php', [
//                'id' => $cmid
//            ]), $ico . ' ' . $itemtitleshort, [
//                'class' => 'd-inline-block p-2 dropdown-toggle font-weight-normal',
//                'data-toggle' => 'popover',
//                'data-content' => $this->render($popover)
//            ]);
//        }
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->itemname = $this->itemname;
        $data->timemodified = $this->timemodified;
        $data->completed = $this->completed;
        $data->passed = $this->passed;
        $data->duedate = $this->duedate;
        $data->gradetopass = $this->gradetopass;
        $data->showprogressbars = $this->showprogressbars;
        $data->itemmoduleicon = html_writer::img($output->image_url('icon', $this->itemmodule), '', [
            'class' => 'icon itemicon'
        ]);
        $data->settingsicon = $output->pix_icon('t/edit', get_string('edit'), 'core', ['class' => 'icon itemicon']);
        $data->select = $this->select ? $output->box($output->render($this->select), 'mt-n3') : null;
        $data->showsettings = $this->showsettings;

        return $data;
    }

    private function get_options(grade_item $gradeitem): array {
        if ($scale = $gradeitem->load_scale()) {
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