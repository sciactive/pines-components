<?php
/**
 * List stock.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'stock/list'));

if (empty($_REQUEST['location'])) {
	$location = $_SESSION['user']->group;
} elseif ($_REQUEST['location'] != 'all') {
	$location = group::factory((int) $_REQUEST['location']);
}

$pines->com_sales->list_stock($_REQUEST['removed'] == 'true', $location);
?>