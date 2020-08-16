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
 * This file contains main functions for the course format eTask
 *
 * @since     2.0
 * @package   format_etask
 * @copyright 2013 Martin Drlik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Enrolled students of the course.
 *
 * @see load_users()
 * @param object $context The current context course
 * @param int $courseid The id of current course
 * @param int $page The current page being viewed (when report is paged)
 * @return object Enrolled students of the course
 */
function get_etask_users($context, $courseid, $page) {
    // return tracking object
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'etask', 'courseid'=>$courseid, 'page'=>$page));

    // initialise the grader report object that produces the table
    $report = new grade_report_grader($courseid, $gpr, $context);

    // final grades MUST be loaded after the processing
    $users = $report->load_users();

    return $users;
}

/**
 * Formated user link. Link to a user profile contains user pictures,
 * first name and last name.
 *
 * @see user_picture()
 * @see moodle_url()
 * @see get_etask_users()
 * @param object $user The user of the course - you can use get_etask_users() function
 * @param int $courseid The id of current course
 * @return string Formated user link with user picture
 */
function get_formated_etask_user($user, $courseid) {
    global $OUTPUT;

    // user picture
    $userpicture = $OUTPUT->user_picture($user, array('size' => 30, 'courseid'=>$courseid));
    // profile url
    $url = new moodle_url('/user/view.php', array('id'=>$user->id, 'course'=>$courseid));
    // profile link
    $userlink = '&nbsp;<a href="' . $url . '" title="' . $user->firstname .
        ' ' . $user->lastname . '">' . $user->firstname . '&nbsp;' . $user->lastname . '</a>';
    $student = $userpicture . $userlink; // final user link

    return $student;
}

/**
 * Get visible assignments of current course by Data Manipulation Language
 * function.
 *
 * @see get_records_sql()
 * @param int $courseid The id of current course
 * @return object Assignments of the course
 * @since Moodle 2.3
 */
function get_etask_assignments($courseid) {
    global $DB;

    // SQL query for get_records_sql() DML function
    $sql = "SELECT a.id, a.name, a.duedate, cm.id as assign_id, m.name as module_name FROM ({assign} a INNER JOIN {course_modules}
        cm on a.id = cm.instance) INNER JOIN {modules} m on cm.module = m.id WHERE a.course = :a_course 
        AND cm.visible = :visible AND cm.course = :cm_course AND m.name = :module_name";
    // parameters in the where clause
    $params = array('a_course'=>$courseid, 'visible'=>1, 'cm_course'=>$courseid, 'module_name'=>'assign');
    $assignments = $DB->get_records_sql($sql, $params);

    return $assignments;
}


/**
 * Get array from the scale for current assignment of course. If
 * gradepass variable is true only grade to pass is returned.
 *
 * @see get_record_sql()
 * @see make_menu_from_list()
 * @param int $assignment The instance of the assignment
 * @param int $courseid The id of current course
 * @param bool $gradepass If true only one element (grade to pass) is returned
 * @return array Scale or grade to pass for current assignment of the course, index from 1
 */
function get_scale($assignment, $courseid, $gradepass=false) {
    global $DB;

    $scale = array();

    $sql = "SELECT gi.id, gi.gradepass, s.scale FROM {grade_items} gi LEFT JOIN {scale} s
        on gi.scaleid = s.id WHERE gi.courseid = :courseid AND gi.iteminstance = :iteminstance
        AND gi.itemtype = :itemtype AND gi.itemmodule = :itemmodule";
    $params = array('courseid'=>$courseid, 'iteminstance'=>$assignment, 'itemtype'=>'mod', 'itemmodule'=>'assign');
    $grade = $DB->get_record_sql($sql, $params);

    // if gradepass true, only grade to pass is returned
    if($gradepass) {
        // scale is not null
        if($grade->scale && round($grade->gradepass, 0)!=0) {
            $scales = make_menu_from_list($grade->scale, $separator=',');
				
            $index = round($grade->gradepass, 0);
				// when duplicate assignment, Undefined offset can be setted
            $scale = count($scales)<$index ? array(0=>get_string('notset', 'format_etask')) : array($index=>$scales[$index]);
        } else if(round($grade->gradepass, 0)==0) { // grade to pass is null
            $scale = array(0=>get_string('notset', 'format_etask')); // set 0 index
        } else {
            // get value of grade to pass - it is not user defined scale, only number 1 - 100
            $scale = array(round($grade->gradepass, 0)=>round($grade->gradepass, 0)); // key => value (the same)
        }
    } else {
        if($grade->scale) {
            // text scale to array - index from 1
            $scale = make_menu_from_list($grade->scale, $separator=',');
        } else {
            // numbers 1 - 100
            for($i=100; $i>=1; --$i) {
                $scale[$i]=$i;
            }
        }
    }

    return $scale;
}

/**
 * Get grade to pass settings form. It sets grade to pass value for
 * current assignment.
 *
 * @see gradepass_form()
 * @param string $action The form action atribute
 * @param string $sesskey
 * @param object $assignment Assignment of current course
 * @param int $courseid The id of current course
 * @param int $selected The key (index) of grade to pass value from array
 * @return string Grade to pass settings form
 */
function get_gradepass_form($action, $sesskey, $assignment, $courseid, $selected) {
    // new grade to pass form with action atribute
    $mform = new gradepass_form($action, array('assignmentid'=>$assignment->id,
        'assignmentinstance'=>$assignment->assign_id, 'courseid'=>$courseid, 'selected'=>$selected));

    $form = '<div class="gradesettings" style="display:none;" id="gradepassform' . $assignment->assign_id . '">';
    $form .= '<h3>' . get_string('pluginname', 'assign') . ': ' . $assignment->name . '</h3>';
    $form .= $mform->display();
    $form .= '</div>';

    return $form;
}

/**
 * Formated assignment link. Link to a assignment detail/editing and grade to pass
 * settings if editing mode is on.
 *
 * @see moodle_url()
 * @see get_scale()
 * @see user_is_editing()
 * @see has_capability()
 * @see get_gradepass_form()
 * @see pix_url()
 * @see userdate()
 * @param object $assignment Assignment of current course
 * @param object $context The current context course
 * @param int $courseid The id of current course
 * @param string $sesskey
 * @param int $sectionreturn
 * @return string Formated assignment link with grade to pass settings icon and form
 */
function get_formated_etask_assignment($assignment, $context, $courseid, $sesskey, $sectionreturn) {
    global $PAGE, $OUTPUT;
    
    // if duedate 0
    $duedate = $assignment->duedate == 0 ? get_string('notset', 'format_etask') : userdate($assignment->duedate);

    // form action atribute
    $action = str_replace('&amp;', '&', (string) new moodle_url('/course/view.php', array('id'=>$courseid,
        'gradepass'=>$assignment->assign_id)));

    $gradesettings = null; // variable initialization
    $gradepass = get_scale($assignment->id, $courseid, true); // array with grade to pass value

    // editing is turned on, user has capibility for course update - editing of assignment is allowed
    if($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
        // url for editing assignment
         $url = new moodle_url('/course/mod.php', array('sesskey'=>$sesskey, 'sr'=>$sectionreturn, 'update'=>$assignment->assign_id));

        // settings icon for grade to pass - the form is rendered and shown on click event
        $selected = key(get_scale($assignment->id, $courseid, true)); // selected value
        $gradesettings = '&nbsp;<a title="'.get_string('gradesettings', 'format_etask').'" name="gradepassform' .
            $assignment->assign_id . '" onclick="toggle(this.name)" href="#" class="toggle" id="toggle' .
            $assignment->assign_id . '"><img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="' .
            get_string('update'). '" /></a>' . get_gradepass_form($action, $sesskey, $assignment, $courseid, $selected);
    } else {
        // link for displaying assignment
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$assignment->assign_id));
    }

    // formated assignment block with link and settings icon and grade to pass settings form (on title the YUI tooltip is used)
    $assign = '<div class="assigncontainer"><img class="icon" src="'. $OUTPUT->pix_url('icon', 'assign') .
        '" alt="' . get_string('pluginname', 'assign') . '" title="' . get_string('pluginname', 'assign') .
        '" /><a href="' . $url . '" title="<h3>' . get_string('pluginname', 'assign') . ': ' . $assignment->name .
        '</h3><p><strong>' . get_string('duedate', 'assign') . ': </strong>' . $duedate .
        '<br /><strong>' . get_string('gradepass', 'grades') . ': </strong>' . $gradepass[key($gradepass)] .
        '</p>" class="etasktooltip">' . $assignment->name . '</a>' . $gradesettings . '</div>';

    return $assign;
}

/**
 * Get array of grading objects. Grading for all students of all assignments of course.
 *
 * @see grade_get_grades()
 * @see get_etask_assignments()
 * @see get_etask_users()
 * @param int $courseid The id of current course
 * @param object $assignments Assignments of current course
 * @param object $users Students of the course
 * @return array Grading
 */
function get_etask_grade($courseid, $assignments, $users) {
    $gradinginfo = array();

    foreach($assignments as $assignment){
        $gradinginfo[] = grade_get_grades($courseid, 'mod', 'assign', $assignment->id, array_keys($users));
    }

    return $gradinginfo;
}

/**
 * Formats grade from decimal to int. If grade by string scale given,
 * return without changes.
 *
 * @param string $grade
 * @return string Grade
 */
function get_formated_etask_grade($grade) {
    if(substr($grade, -3)==',00' or substr($grade, -3)=='.00') {
        return round($grade, 0);
    }
    return $grade;
}

/**
 * Get information about assignment submission. True if assignment status is "submitted".
 *
 * @param int $assignment The assignment id
 * @param int $user The user id
 * @return string Submitted or null
 * @since Moodle 2.3
 */
function is_submitted($assignment, $user) {
    global $DB;

    // get assignment status
    $sql = "SELECT status FROM {assign_submission} WHERE assignment = :assignment AND userid = :user AND status = :status";
    $params = array('assignment'=>$assignment, 'user'=>$user, 'status'=>'submitted'); // || draft
    $submittion = $DB->get_record_sql($sql, $params);

    return $submittion;
}

/**
 * CSS class for table cell.
 *
 * @see is_submitted()
 * @param string $submission The information about submission - is_submitted()
 * @param int $gradepass The grade to pass value
 * @param int $grade The current grade
 * @return string Class for table cell
 */
function get_cell_color($submission, $gradepass, $grade) {
    $class = null;

    // class submitted/passed/failed/unsubmitted
    if($submission && $grade===null) {
        $class = 'submitted';
    } else if($grade!==null && $grade>=$gradepass && $gradepass!=0) {
        $class = 'passed';
    } else if($grade!==null && $grade<$gradepass && $gradepass!=0) {
        $class = 'failed';
    } else {
        $class = 'unsubmitted';
    }

    return $class;
}

/**
 * Set session sortby in flextable
 *
 * @param string $field The field from database talbe of user
 */
function mod_assign_grading_sort_by($field) {
    $attributes = new stdClass();

    $attributes->uniqueid = 'mod_assign_grading';
    $attributes->sortby = array($field=>4);

    $_SESSION['SESSION']->flextable = array($attributes->uniqueid=>$attributes);
}

/**
 * Render gradebook table with all functionality.
 *
 * @see get_formated_etask_assignment()
 * @see html_table_row()
 * @see html_table_cell()
 * @see get_formated_etask_user()
 * @see get_cell_color()
 * @see is_submitted()
 * @see moodle_url()
 * @see get_formated_etask_grade()
 * @see html_table()
 * @param object $assignments Assignment of current course - get_etask_assignments()
 * @param object $context The current context course
 * @param int $courseid The id of current course
 * @param string $sesskey
 * @param int $sectionreturn
 * @param object $users Students of the course - get_etask_users()
 * @param array $gradinginfo Array of grading objects - get_etask_grade()
 * @return string Gradebook table
 */
function get_etask_gradebook($assignments, $context, $courseid, $sesskey, $sectionreturn, $users, $gradinginfo) {
    global $PAGE;

    // ses session sortby in mod_assign_grading flextable, sort users by lastname
    mod_assign_grading_sort_by('lastname');

    // information about no data for gradebook table (no students and no assignments)
    if($assignments == null && $users == null) {
        return '<div class="no-data">' . get_string('nodata', 'format_etask') . '</div>';
    }

    // table head
    $tablerow = array('&nbsp;'); // first cell of the thead is empty
    foreach($assignments as $assignment) {
        // thead
        $cell = new html_table_cell(); // object of the table cell
        $cell->text = get_formated_etask_assignment($assignment, $context, $courseid, $sesskey, $sectionreturn);
        $cell->attributes = array('class'=>'assignment'); // atributes of cell
        $tablerow[] = $cell; // adding cell to the table row array
    }
    $tablehead = new html_table_row(); // object of the table row
    $tablehead = $tablerow; // table head is the table row array

    // table body
    $data = array();
    $rownum = 0; // row of grading user
    foreach($users as $user) {
        $tablerow = array();
        $cell = new html_table_cell(); // object of the cell
        $cell->text = get_formated_etask_user($user, $courseid);
        $cell->attributes = array('class'=>'user'); // attributes of cell
        $tablerow[] = $cell; // adding cell to the table row array

        $assignmentindex = 0;
        foreach($assignments as $assignment) {
            // class for the table cell
            $submission = is_submitted($assignment->id, $user->id);
            $gradepass = $gradinginfo[$assignmentindex]->items[0]->gradepass;
            $grade = $gradinginfo[$assignmentindex]->items[0]->grades[$user->id]->grade;

            $class = get_cell_color($submission, $gradepass, $grade);

            $url = str_replace('&amp;', '&', (string) new moodle_url('/mod/assign/view.php',
                array(
                    'id'=>$assignment->assign_id,
                    'rownum'=>$rownum,
                    'assign'=>$assignment->assign_id,
                    'action'=>'grade')));

            // onli user with capability of update the course can clicked on table cell
            if(has_capability('moodle/course:update', $context)) {
                $onclickcell = array(
                    'class'=>"grade pointer center $class",
                    'title'=>$user->firstname . ' ' . $user->lastname . ', ' . $assignment->name,
                    'onclick'=>'location.href="' . $url . '";'); // atributes of cell
            } else {
                $onclickcell = array(
                    'class'=>"grade center $class",
                    'title'=>$user->firstname . ' ' . $user->lastname . ', ' . $assignment->name); // atributes of cell
            }

            $cell = new html_table_cell(); // object of the cell
            $cell->text = get_formated_etask_grade($gradinginfo[$assignmentindex]->items[0]->grades[$user->id]->str_grade);
            $cell->attributes = $onclickcell;
            $tablerow[] = $cell; // adding cell to the table row array
            $assignmentindex++;
        }

        $tablebody = new html_table_row(); // object of the row
        $tablebody = $tablerow; // table body is the table row array
        $data[] = $tablebody;

        $rownum++;
    }

    // html table
    $gradebook = new html_table();
        $gradebook->attributes = array('class'=>'flexible generaltable generalbox etask');
        $gradebook->head = $tablehead;
        $gradebook->data = $data;
    return html_writer::table($gradebook);
}

/**
 * Update grade to pass value of the assignment.
 *
 * @see get_record_sql()
 * @see data_submitted()
 * @see confirm_sesskey()
 * @see has_capability()
 * @see update_record()
 * @param object $context The current context course
 * @param int $courseid The id of current course
 */
function update_gradepass($context, $courseid) {
    global $DB;

    $assign = optional_param('gradepass', 0, PARAM_INT); // parameter from url

    // get id of assignment
    $sql = "SELECT cm.instance FROM {course_modules} cm WHERE cm.id = :assign";
    $params = array('assign'=>$assign);
    $assignid = $DB->get_record_sql($sql, $params);

    // update record
    if(data_submitted() and confirm_sesskey() and has_capability('moodle/grade:edit', $context) and $assign) {
        $assignment  = $assignid->instance; // assignment id
        $gradepass = trim(optional_param('gradepass' . $assign, 0, PARAM_INT)); // post grade pass value

        // assignment id in grade_items - tehere is gradepass
        $sql = "SELECT gi.id FROM {grade_items} gi WHERE gi.courseid = :courseid AND gi.iteminstance = :iteminstance
            AND gi.itemtype = :itemtype AND gi.itemmodule = :itemmodule";
        $params = array('courseid'=>$courseid, 'iteminstance'=>$assignid->instance, 'itemtype'=>'mod', 'itemmodule'=>'assign');
        $gradeitem = $DB->get_record_sql($sql, $params);

        // update object
        $grade = new stdClass ();
        $grade->id = $gradeitem->id;
        $grade->gradepass = $gradepass;

        $DB->update_record('grade_items', $grade);
    }
}
