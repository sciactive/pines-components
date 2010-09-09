<?php
/**
 * Edit an event in the company schedule.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_hrm', 'editevent'));

$event = com_hrm_event::factory((int)$_REQUEST['id']);
if (!isset($event->guid))
	$event = com_hrm_event::factory();

if (isset($_REQUEST['location']))
	$location = group::factory((int)$_REQUEST['location']);

$event->print_form($location);

?>