<?php
/**
 * Edit calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/viewcalendar') && !gatekeeper('com_hrm/editcalendar') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

$pines->com_hrm->show_calendar((int) $_REQUEST['id'], (int) $_REQUEST['location']);

?>