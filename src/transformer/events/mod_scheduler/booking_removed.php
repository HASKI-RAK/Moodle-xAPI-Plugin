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
 * Transformer for booking removed event.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 *            Dimitri Bigler <dimitri.bigler@hs-kempten.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_scheduler;

use Exception;
use src\transformer\utils as utils;

/**
 * Transformer for booking removed event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function booking_removed(array $config, \stdClass $event): array {

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
    $cmid = $event->contextinstanceid;
    $lang = utils\get_course_lang($course);

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'https://wiki.haski.app/variables/xapi.clicked',
            'display' => [
                $lang => 'booking'
            ],
        ],
        'object' => utils\get_activity\booking($config, $cmid, $lang),
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
                        'http://id.tincanapi.com/activitytype/lms/module'
                    )
                ],
                'grouping' => [
                    utils\get_activity\site($config)
                ]
            ],
        ],
        'timestamp' => utils\get_event_timestamp($event)
    ]];
}
