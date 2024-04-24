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
 * The Inactive user cleanup library
 *
 * @package   tool_inactive_user_reminder
 * @author DualCube <admin@dualcube.com>
 * @copyright DualCube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * tool_inactive_user_reminder is standard cron function
 */

namespace tool_inactive_user_reminder\task;

use core\check\performance\debugging;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task for Inactive user cleanup.
 *
 * @copyright DualCube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_inactive_user_reminder_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'tool_inactive_user_reminder');
    }

    public function execute() {
        global $DB;
        $inactivity = get_config('tool_inactive_user_reminder', 'daysofinactivity');
        if (!$inactivity || $inactivity < 1) {
            return;
        }
        $subject = get_config('tool_inactive_user_reminder', 'emailsubject');
        $body = get_config('tool_inactive_user_reminder', 'emailbody');
        $numberofemails = get_config('tool_inactive_user_reminder', 'numberofemails');
        $daysbetweenemails = get_config('tool_inactive_user_reminder', 'daysbetweenemails');
        $courseid = get_config('tool_inactive_user_reminder', 'course');

        $lastaccesstime = time() - ($inactivity * 24 * 60 * 60);
        $users =
            $DB->get_records_sql("SELECT * FROM {user} u WHERE lastaccess < :lastaccess AND deleted = 0 AND lastaccess <> 0 AND lastaccess IS NOT NULL",
                array('lastaccess' => $lastaccesstime));

        if ($courseid) {
            $context = \context_course::instance($courseid);
            $users = get_enrolled_users($context);
            // if the user is not enrolled in the course, or has completed the course, then remove them from the list
            foreach ($users as $key => $user) {
                $course = $DB->get_record('course', array('id' => $courseid));
                $coursemodule = $DB->get_record('course_modules', array('course' => $courseid));
                $completion = new \completion_info($course);
                $completionstate = $completion->get_data($user->id, true);
                if (!$completionstate || $completionstate->completionstate != \completion_info::COMPLETE) {
                    unset($users[$key]);
                }
            }
        }

        $messagetext = html_to_text($body);
        $mainadminuser = get_admin();
        foreach ($users as $usersdetails) {
            $cleanupcheck = $DB->get_record('tool_inactive_user_reminder', array('userid' => $usersdetails->id));
            if (!$cleanupcheck) {
                $record = new \stdClass();
                $record->userid = $usersdetails->id;
                $record->emailsent = 0;
                $DB->insert_record('tool_inactive_user_reminder', $record, false);
            }
            $cleanupcheck = $DB->get_record('tool_inactive_user_reminder', array('userid' => $usersdetails->id));
            if (!$cleanupcheck) {
                debugging('No cleanup check found for user with id: ' . $usersdetails->id);
                continue;
            }
            if ($cleanupcheck->emailsent >= $numberofemails) {
                continue;
            }
            if ($cleanupcheck->emailsent > 0) {
                if ($daysbetweenemails) {
                    $lastemailtime = $cleanupcheck->date;
                    if (time() - $lastemailtime < $daysbetweenemails * 24 * 60 * 60) {
                        continue;
                    }
                } else {
                    continue; // No more emails allowed for this user.
                }
            }

            if (email_to_user($usersdetails, $mainadminuser, $subject, $messagetext)) {
                if (!$cleanupcheck->emailsent) {
                    $cleanupcheck->emailsent = 1;
                } else {
                    ++$cleanupcheck->emailsent;
                }
                $cleanupcheck->date = time();
                $DB->update_record('tool_inactive_user_reminder', $cleanupcheck);
            }
        }
    }
}
