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
 * Class containing data for course format content.
 *
 * @package   format_etask
 * @copyright 2021, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_etask\output\courseformat;

use coding_exception;
use core_courseformat\base as course_format;
use core_courseformat\output\local\content as core_courseformat_content;
use format_etask\output\gradingtable;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to render a course format.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends core_courseformat_content implements renderable, templatable {

    /** @var string */
    protected $gradingtable;

    /** @var bool */
    protected $placementbelow;

    /**
     * Content constructor.
     *
     * @param course_format $format
     */
    public function __construct(course_format $format) {
        parent::__construct($format);

        $this->gradingtable = new gradingtable();
        $this->placementbelow = course_get_format($format->get_course())->get_placement() === \format_etask::PLACEMENT_BELOW;
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */

    /**
     * Export for template.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $data->gradingtable = $output->render($this->gradingtable);
        $data->placementbelow = $this->placementbelow;

        return $data;
    }
}
