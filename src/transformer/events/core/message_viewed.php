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
 * Transform for message viewed event.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 *            Dimitri Bigler <dimitri.bigler@hs-kempten.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\core;

use src\transformer\utils as utils;

/**
 * Transformer for message viewed event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function message_viewed(array $config, \stdClass $event): array {

    $repo = $config['repo'];
    $userid = $event->userid;
    if ($userid < 2) {
        $userid = 1;
    }
    $user = $repo->read_record_by_id('user', $userid);
    $senderid = $event->relateduserid;
    if ($senderid < 2) {
        $senderid = 1;
    }
    $sender = $repo->read_record_by_id('user', $senderid);
    $lang = $config['source_lang'];

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'https://wiki.haski.app/viewed',
            'display' => [
                $lang => 'viewed'
            ],
        ],
        'object' => utils\get_activity\message($config, $lang, null, null),
        'context' => [
            'platform' => $config['source_name'],
            'team' => utils\get_user($config, $sender),
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, null),
            'contextActivities' => [
                'parent' => [
                    utils\get_activity\site($config)
                ],
                'grouping' => [
                    utils\get_activity\site($config)
                ]
            ],
        ],
        'timestamp' => utils\get_event_timestamp($event)
    ]];
}
