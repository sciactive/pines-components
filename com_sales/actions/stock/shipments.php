<?php
/**
 * List pending shipments.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'stock/shipments'));

if (!empty($_REQUEST['location']))
	$location = group::factory((int) $_REQUEST['location']);

$descendents = ($_REQUEST['descendents'] == 'true');

$pines->com_sales->list_shipments($_REQUEST['removed'] == 'true', $location, $descendents);
?>