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
 * Newmodule
 *
 * @package    local_newmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");

$PAGE->set_url('/local/newmodule/index.php', array('id'=>$id));

$systemcontext = context_system::instance();

require_login();
$PAGE->set_pagelayout('incourse');


/*
$PAGE->navbar->add($strquizzes);
$PAGE->set_title($strquizzes);
$PAGE->set_button($streditquestions);
*/
$PAGE->set_heading($CFG->site);

echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($USER), 2);

$renderable = new \local_newmodule\output\main();
$renderer = $PAGE->get_renderer('local_newmodule');
//var_dump($renderer);

echo $renderer->render($renderable);


// Finish the page.
echo $OUTPUT->footer();