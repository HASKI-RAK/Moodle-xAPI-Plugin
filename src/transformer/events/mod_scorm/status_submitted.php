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
 * Transform for the scorm status submitted event.
 *
 * @package   logstore_xapi
 * @copyright Jerret Fowler <jerrett.fowler@gmail.com>
 *            Ryan Smith <https://www.linkedin.com/in/ryan-smith-uk/>
 *            David Pesce <david.pesce@exputo.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_scorm;

use Exception;
use src\transformer\utils as utils;

/**
 * Transformer for SCORM status submitted event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */
function status_submitted(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $userid = $event->userid;
    if ($userid < 2) {
        $userid = 1;
    }
    $user = $repo->read_record_by_id('user', $userid);
    try {
        $course = $repo->read_record_by_id('course', $event->courseid);
    } catch (Exception $e) {
        // OBJECT_NOT_FOUND.
        $course = $repo->read_record_by_id('course', 1);
    }
    $scormid = $event->objectid;
    $cmid = $event->contextinstanceid;
    $lang = utils\get_course_lang($course);
    $other = unserialize($event->other);
    if (!$other) {
        $other = json_decode($event->other);
        $attempt = $other->attemptid;
    } else {
        $attempt = $other['attemptid'];
    }
    try {
        $scormscoestracks = $repo->read_records('scorm_scoes_track', [
            'userid' => $user->id,
            'scormid' => $scormid,
            'scoid' => $cmid,
            'attempt' => $attempt
        ]);
        $verb = utils\get_scorm_verb($scormscoestracks, $lang);
    } catch (Exception $e) {
        // OBJECT_NOT_FOUND.
        $verb = utils\get_verb('deleted', $config, $lang);
    }

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => $verb,
        'object' => utils\get_activity\course_scorm($config, $cmid, $scormid, $lang),
        'timestamp' => utils\get_event_timestamp($event),
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course),
                ],
                'category' => [
                    utils\get_activity\source($config),
                ]
            ],
        ]
    ]];
}
