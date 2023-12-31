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
 * Log loader.
 *
 * @package   Moodle-xAPI-Plugin
 * @copyright Jerret Fowler <jerrett.fowler@gmail.com>
 *            Ryan Smith <https://www.linkedin.com/in/ryan-smith-uk/>
 *            David Pesce <david.pesce@exputo.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\loader\log;

use src\loader\utils as utils;

/**
 * Load logs.
 *
 * @param array $config An array of configuration settings.
 * @param array $transformedevents An array of transformed events.
 * @return array
 */
function load(array $config, array $transformedevents) {
    $statements = array_reduce($transformedevents, function ($result, $transformedevent) {
        $eventstatements = $transformedevent['statements'];
        return array_merge($result, $eventstatements);
    }, []);
    echo(json_encode($statements, JSON_PRETTY_PRINT)."\n");
    return utils\construct_loaded_events($transformedevents, true);
}
