<?php
/**
 * Provide a form for overriding users/locations.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/overrideowner') )
	punt_user(null, pines_url('com_sales', 'forms/swap'));

$entity = com_sales_sale::factory((int) $_REQUEST['id']);
if (!isset($entity->guid))
	$entity = com_sales_return::factory((int) $_REQUEST['id']);

if (!isset($entity->guid)) {
	pines_error('Requested sale id is not accessible.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}

$pines->com_sales->override_form($entity);

?>