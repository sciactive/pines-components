<?php
/**
 * List stock.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') && !gatekeeper('com_sales/seestock') )
	punt_user(null, pines_url('com_sales', 'stock/list'));

if (!empty($_REQUEST['location']))
	$location = group::factory((int) $_REQUEST['location']);

$descendants = ($_REQUEST['descendants'] == 'true');

$pines->com_sales->list_stock($_REQUEST['removed'] == 'true', $location, $descendants);
?>