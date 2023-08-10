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
 * Transformer utility for retrieving course category data.
 *
 * @package   logstore_xapi
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace src\transformer\utils\get_activity;

use Exception;

/**
 * Transformer utility for retrieving course category data.
 *
 * @param array $config The transformer config settings.
 * @param int $categoryid The id of the core course category.
 * @param string $lang The language of the course.
 * @return array
 */
function course_category(array $config, int $categoryid, string $lang): array {

    try {
        $repo = $config['repo'];
        $category = $repo->read_record_by_id('course_categories', $categoryid);
        $name = property_exists($category, 'name') ? $category->name : 'Category';
        $description = property_exists($category, 'description') ? $category->description : 'description of the category';
        if (is_null($description) ) {
            $description = '';
        }
    } catch (Exception $e) {
        // OBJECT_NOT_FOUND.
        $name = 'category id ' . $categoryid;
        $description = 'deleted';
    }

    $url = $config['app_url'] . '/course/index.php?categoryid=' . $categoryid;

    return [
        'id' => $url,
        'definition' => [
            'type' => 'http://id.tincanapi.com/activitytype/category',
            'name' => [
                $lang => 'course category ' . $name,
            ],
            'description' => [
                $lang => $description,
            ],
        ],
    ];
}
