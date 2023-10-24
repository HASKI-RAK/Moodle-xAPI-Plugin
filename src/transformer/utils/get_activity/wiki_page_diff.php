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
 * Transformer utility for retrieving wiki page diff data.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright 2023 Daniela Rotelli <danielle.rotelli@gmail.com>
 *            Dimitri Bigler <dimitri.bigler@hs-kempten.de>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace src\transformer\utils\get_activity;

use Exception;

/**
 * Transformer utility for retrieving wiki page diff data.
 *
 * @param array $config The transformer config settings.
 * @param int $pageid The id of the page.
 * @param string $other The object other.
 * @param string $lang The language of the wiki page.
 * @param int $cmid The course module id.
 * @return array
 */

function wiki_page_diff(array $config, int $pageid, string $other, string $lang, int $cmid): array {

    try {
        $repo = $config['repo'];
        $page = $repo->read_record_by_id('wiki_pages', $pageid);
        $name = property_exists($page, 'title') ? $page->title : 'Wiki page';
        $coursemodule = $repo->read_record_by_id('course_modules', $cmid);
        $status = $coursemodule->deletioninprogress;
        if ($status == 0) {
            $description = 'differences between wiki pages';
        } else {
            $description = 'deletion in progress';
        }
    } catch (Exception $e) {
        // OBJECT_NOT_FOUND.
        $name = 'wiki page id: ' . $pageid;
        $description = 'deleted';
    }

    $other = unserialize($other);
    if (!$other) {
        $other = json_decode($other);
        $comparewith = empty($other->comparewith) ? '' : $other->comparewith;
        $compare = empty($other->compare) ? '' : $other->compare;
    } else {
        $comparewith = empty($other['comparewith']) ? '' : $other['comparewith'];
        $compare = empty($other['compare']) ? '' : $other['compare'];
    }

    $url = $config['app_url'].'/mod/wiki/diff.php?pageid='.$pageid.'&comparewith='.$comparewith.'&compare='.$compare;

    return [
        'id' => $url,
        'definition' => [
            'name' => [
                $lang => 'differences in ' . $name,
            ],
            'type' => 'http://activitystrea.ms/schema/1.0/page'
        ],
    ];
}


