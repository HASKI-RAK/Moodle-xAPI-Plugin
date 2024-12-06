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
 * Transformer for statement received event.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 *            Dimitri Bigler <dimitri.bigler@hs-kempten.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_h5pactivity;

use Exception;
use src\transformer\utils as utils;

/**
 * Transformer for statement received event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function statement_received(array $config, \stdClass $event): array {
    global $DB;

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

    $activityid = $event->objectid;
    $cmid = $event->contextinstanceid;
    $lang = utils\get_course_lang($course);

    // Fetch H5P results or scores
    try {
        $resultdata = $DB->get_record_sql(
            "SELECT *
                 FROM {h5pactivity_attempts}
                 WHERE h5pactivityid = :activityid AND userid = :userid
                 ORDER BY attempt DESC
                 LIMIT 1",
            [
                'activityid' => $activityid,
                'userid' => $userid
            ]
        );

        $scoreraw = (float) ($resultdata->rawscore ?? 0);
        $scoremax = (float) ($resultdata->maxscore ?? 0);
        $durationSeconds = (int) ($resultdata->duration ?? 0); // Duration in seconds
        $scaledscore = (float) ($resultdata->scaled ?? 0);
        $success = ($scoreraw >= $scoremax); // Define success criteria

        // Convert duration to ISO 8601 format
        $hours = intdiv($durationSeconds, 3600);
        $minutes = intdiv($durationSeconds % 3600, 60);
        $seconds = $durationSeconds % 60;

        $duration = "PT";
        if ($hours > 0) {
            $duration .= "{$hours}H";
        }
        if ($minutes > 0) {
            $duration .= "{$minutes}M";
        }
        if ($seconds > 0 || ($hours === 0 && $minutes === 0)) {
            $duration .= "{$seconds}S";
        }

    } catch (Exception $e) {
        // Default to no results
        $scoreraw = null;
        $scoremax = null;
        $duration = "PT0S"; // Default to 0 seconds if no duration
        $success = false;
        $scaledscore = null;
    }

    // Build the xAPI statement
    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'https://wiki.haski.app/variables/xapi.answered',
            'display' => [
                $lang => 'answered'
            ]
        ],
        'object' => utils\get_activity\h5p_statement($config, $lang, $activityid, $user, $cmid),
        'result' => [
            'score' => [
                'raw' => $scoreraw,
                'max' => $scoremax,
                'scaled' => $scaledscore
            ],
            'duration' => $duration,
            'completion' => true,
            'success' => $success
        ],
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'parent' => [
                    utils\get_activity\course($config, $course),
                    utils\get_activity\course_module(
                        $config,
                        $course,
                        $cmid,
                        'https://h5p.org/x-api/h5p-local-content-id'
                    )
                ],
                'grouping' => [
                    utils\get_activity\site($config)
                ]
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event)
    ]];
}

