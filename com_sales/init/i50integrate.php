<?php
/**
 * Determine whether to integrate with other components.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_sales->com_customer && !$pines->depend->check('component', 'com_customer'))
	$pines->config->com_sales->com_customer = false;

if ($pines->config->com_sales->com_esp && !$pines->depend->check('component', 'com_esp'))
	$pines->config->com_sales->com_esp = false;

if ($pines->config->com_sales->com_hrm && !$pines->depend->check('component', 'com_hrm'))
	$pines->config->com_sales->com_hrm = false;

if ($pines->config->com_sales->per_item_salesperson && !$pines->config->com_sales->com_hrm)
	$pines->config->com_sales->per_item_salesperson = false;

if ($pines->config->com_sales->com_storefront && !$pines->depend->check('component', 'com_storefront&com_content'))
	$pines->config->com_sales->com_storefront = false;

?>