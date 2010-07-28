<?php
/**
 * Determine whether to integrate with com_customer and com_hrm.
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

if ($pines->config->com_sales->com_hrm && !$pines->depend->check('component', 'com_hrm'))
	$pines->config->com_sales->com_hrm = false;

if ($pines->config->com_sales->com_storefront && !$pines->depend->check('component', 'com_storefront'))
	$pines->config->com_sales->com_storefront = false;

?>