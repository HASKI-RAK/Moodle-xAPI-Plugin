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
 * Transformer utility for retrieving recent activity data.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 *            Dimitri Bigler <dimitri.bigler@hs-kempten.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\utils\get_activity;

use src\transformer\utils as utils;

/**
 * Transformer utility for retrieving recent activity data.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $course The course object.
 * @param string $lang The language of the course.
 * @return array
 */

function recent_activity(array $config, \stdClass $course, string $lang): array {

    $name = property_exists($course, 'fullname') ? $course->fullname : 'A Moodle course';
    $url = $config['app_url'] . '/course/recent.php?id=' . $course->id;

    $object = [
        'id' => $url,
        'definition' => [
            'name' => [
              $lang => $name . ' recent activity',
            ],
            'type' => 'http://activitystrea.ms/schema/1.0/page'
        ],
    ];

    if (utils\is_enabled_config($config, 'send_short_course_id')) {
        $lmsshortid = 'https://w3id.org/learning-analytics/learning-management-system/short-id';
        $object['definition']['extensions'][$lmsshortid] = $course->shortname;
    }

    if (utils\is_enabled_config($config, 'send_course_and_module_idnumber')) {
        $courseidnumber = property_exists($course, 'idnumber') ? $course->idnumber : null;
        $lmsexternalid = 'https://w3id.org/learning-analytics/learning-management-system/external-id';
        $object['definition']['extensions'][$lmsexternalid] = $courseidnumber;
    }

    return $object;
}
