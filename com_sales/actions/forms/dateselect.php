<?php
/**
 * Select a start and end date.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'forms/dateselect'));

$pines->com_sales->date_select_form($_REQUEST['all_time'] == 'true', empty($_REQUEST['start_date']) ? null : $_REQUEST['start_date'], empty($_REQUEST['end_date']) ? null : $_REQUEST['end_date']);

?>
