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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   format_etask
 * @copyright 2013 Martin Drlik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Grade to pass settings form.
 *
 * @package   format_etask
 * @copyright 2013 Martin Drlik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradepass_form extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    function definition() {
        $mform =& $this->_form; // Don't forget the underscore!

        $gradescale = get_scale($this->_customdata['assignmentid'], $this->_customdata['courseid']); // array of scales
        $gradescale[0] = '-'; // index 0 - not grade

        $gradepassname = 'gradepass' . $this->_customdata['assignmentinstance'];
        $submitgradename = 'submitgrade' . $this->_customdata['assignmentinstance'];

        // select element
        $select = $mform->addElement('select', $gradepassname, get_string('gradepass', 'grades'), $gradescale);
        $select->setSelected($this->_customdata['selected']); // set selected option
        $mform->addElement('submit', $submitgradename, get_string('save', 'format_etask')); // submit button
    }

    /**
     * Display this moodle form
     *
     * @return object Grade to pass form
     */
    public function display() {
        // finalize the form definition if not yet done
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        ob_start();
        $this->_form->display();
        $form = ob_get_clean();

        return $form;
    }
}
