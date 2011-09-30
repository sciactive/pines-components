<?php
/**
 * Provide a form for a payment process type to collect information.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

if (!gatekeeper())
	return;

if ($pines->config->com_storefront->catalog_mode)
	return;

// Load the sale.
if (!$pines->com_storefront->build_sale())
	return;

$pines->com_sales->call_payment_process(array(
	'action' => 'request_cust',
	'name' => $_REQUEST['name'],
	'ticket' => $_SESSION['com_storefront_sale']
), $module);

if (isset($module))
	$pines->page->override_doc($module->render());

?>