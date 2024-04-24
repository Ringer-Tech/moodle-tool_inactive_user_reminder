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
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/inactive_user_reminder/settings_form.php');
require_login();
admin_externalpage_setup('toolinactive_user_reminder');
echo $OUTPUT->header();
$settingsform = new tool_inactive_user_reminder_config_form();
$fromdata = $settingsform->get_data();
$configdata = get_config('tool_inactive_user_reminder');
if (!empty($configdata->daysbeforedeletion)) {
    $data = new stdClass();
    $data->config_daysofinactivity = $configdata->daysofinactivity;
    $data->config_subjectemail = $configdata->emailsubject;
    $data->config_numberofemails = $configdata->numberofemails;
    $data->config_daysbetweenemails = $configdata->daysbetweenemails;
    $data->config_bodyemail['text'] = $configdata->emailbody;
    $settingsform->set_data($data);
}
$settingsform->display();

if ($settingsform->is_submitted()) {
    set_config('daysofinactivity', $fromdata->config_daysofinactivity, 'tool_inactive_user_reminder');
    set_config('emailsubject', $fromdata->config_subjectemail, 'tool_inactive_user_reminder');
    set_config('numberofemails', $fromdata->config_numberofemails, 'tool_inactive_user_reminder');
    set_config('daysbetweenemails', $fromdata->config_daysbetweenemails, 'tool_inactive_user_reminder');
    set_config('emailbody', $fromdata->config_bodyemail['text'], 'tool_inactive_user_reminder');
}

echo $OUTPUT->footer();