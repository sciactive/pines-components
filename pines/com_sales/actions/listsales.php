<?php
/**
 * List sales.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listsales') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listsales'));

if (!empty($_REQUEST['start_date'])) {
	$start_date = strtotime($_REQUEST['start_date'].' 00:00');
} else {
	$start_date = strtotime('-1 week');
}
if (!empty($_REQUEST['end_date']))
	$end_date = strtotime($_REQUEST['end_date'].' 24:00');

$pines->com_sales->list_sales($start_date, $end_date);
?>