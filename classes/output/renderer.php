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
 * Renderer for outputting the eTask topics course format.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 4.0
 */

namespace format_etask\output;

use format_etask\output\courseformat\content;
use \format_topics\output\renderer as format_topics_renderer;

defined('MOODLE_INTERNAL') || die();

/**
 * Basic renderer for eTask topics format.
 *
 * @package   format_etask
 * @copyright 2021, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends format_topics_renderer {
    public function render_format_etask(content $widget) {
        $data = $widget->export_for_template($this);
        return $this->render_from_template('format_etask/content', $data);
    }
}
