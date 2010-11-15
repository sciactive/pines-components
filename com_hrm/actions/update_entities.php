<?php
/**
 * Save all of the events for the company calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$event = $pines->entity_manager->get_entities(array('class' => com_hrm_event, 'skip_ac' => true), array('&', 'tag' => array('com_hrm', 'event')));

foreach ($event as $cur_event) {
	$cur_event->delete();
}

?>