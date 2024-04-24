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
 * From for Inactive user cleanup email setting
 *
 * @package   tool_inactive_user_reminder
 * @author DualCube <admin@dualcube.com>
 * @copyright DualCube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');
/**
 * Email Cofiguration Form for Inactive user cleanup.
 *
 * @copyright DualCube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_inactive_user_reminder_config_form extends moodleform {
    /**
     * Definition.
     */
    public function definition () {
        $mform = $this->_form;
        $mform->addElement('header', 'configheader', get_string('setting', 'tool_inactive_user_reminder'));
        $mform->addElement('text', 'config_daysofinactivity', get_string('daysofinactivity', 'tool_inactive_user_reminder'));
        $mform->setDefault('config_daysofinactivity', '21');
        $mform->setType('config_daysofinactivity', PARAM_INT);
        $mform->addElement('text', 'config_numberofemails', get_string('numberofemails', 'tool_inactive_user_reminder'));
        $mform->setType('config_numberofemails', PARAM_INT);
        $mform->setDefault('config_numberofemails', 3);
        $mform->addElement('text', 'config_daysbetweenemails', get_string('daysbetweenemails', 'tool_inactive_user_reminder'));
        $mform->setType('config_daysbetweenemails', PARAM_INT);
        $mform->setDefault('config_daysbetweenemails', 7);

        // add a select dropdown for a list of courses
        $courses = get_courses();
        $course_options = array();
        foreach ($courses as $course) {
            $course_options[$course->id] = $course->fullname;
        }
        $mform->addElement('select', 'config_course', get_string('course', 'tool_inactive_user_reminder'), $course_options);
        $mform->setType('config_course', PARAM_INT);
        $mform->setDefault('config_course', 1);

        $mform->addElement('header', 'config_headeremail', get_string('emailsetting', 'tool_inactive_user_reminder'));
        $mform->addElement('text', 'config_subjectemail', get_string('emailsubject', 'tool_inactive_user_reminder'));
        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => 1,
        'maxbytes' => 1024);
        $mform->addElement('editor', 'config_bodyemail', get_string('emailbody', 'tool_inactive_user_reminder'), $editoroptions);
        $mform->setType('config_subjectemail', PARAM_TEXT);
        $mform->setDefault('config_subjectemail', 'subject');
        $mform->setType('config_bodyemail', PARAM_RAW);
        $mform->setDefault('config_bodyemail', 'body');
        $this->add_action_buttons();
    }
}
