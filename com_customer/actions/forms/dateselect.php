<?php
/**
 * Select a start and end date.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_customer', 'forms/dateselect'));

$pines->com_customer->date_select_form($_REQUEST['all_time'] == 'true', empty($_REQUEST['start_date']) ? null : $_REQUEST['start_date'], empty($_REQUEST['end_date']) ? null : $_REQUEST['end_date']);

?>
