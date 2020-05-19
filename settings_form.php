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
 * This file contains the form to edit settings.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;

/**
 * Form to edit settings.
 *
 * @package     format_etask
 * @copyright   2020, Martin Drlik <martin.drlik@email.cz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_form extends moodleform {

    /**
     * Called to define this moodle form.
     *
     * @throws coding_exception
     * @return void
     */
    public function definition() {
        array_push($this->_customdata['scale'], '-');
        sort($this->_customdata['scale']);

        $mform =& $this->_form; // Don't forget the underscore.
        $mform->updateAttributes(['id' => 'grade-pass-form_' . $this->_customdata['grade_item']->id,
            'class' => 'inline-form settings']);

        // Select element.
        $mform
            ->addElement('select', 'gradepass' . $this->_customdata['grade_item']->id, get_string('gradepass', 'grades') . ':',
                $this->_customdata['scale'])
            ->setSelected(round($this->_customdata['grade_item']->gradepass, 0));
        $mform->disable_form_change_checker();
    }
}
