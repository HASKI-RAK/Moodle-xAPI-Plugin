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
 * Transformer utility for retrieving book data.
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
 * Transformer utility for retrieving book data.
 *
 * @param array $config The transformer config settings.
 * @param int $bookid The id of the book.
 * @param int $cmid The course module id.
 * @param string $lang The language of the course.
 * @return array
 */

function book(array $config, int $bookid, int $cmid, string $lang): array {

    try {
        $repo = $config['repo'];
        $book = $repo->read_record_by_id('book', $bookid);
        $name = property_exists($book, 'name') ? $book->name : 'Book';
        $coursemodule = $repo->read_record_by_id('course_modules', $cmid);
        $status = $coursemodule->deletioninprogress;
        if ($status == 0) {
            $description = 'the book activity';
        } else {
            $description = 'deletion in progress';
        }
    } catch (Exception $e) {
        // OBJECT_NOT_FOUND.
        $name = 'book id ' . $bookid;
        $description = 'deleted';
    }

    $url = $config['app_url'].'/mod/book/tool/print/index.php?id=' . $cmid;

    return [
        'id' => $url,
        'definition' => [
            'name' => [
                $lang => 'book ' . $name,
            ],
            'type' => 'http://id.tincanapi.com/activitytype/book',
        ],
    ];
}
