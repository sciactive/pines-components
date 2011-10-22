<?php
/**
 * Adjust a product's quantity in the cart.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');
$pines->page->override_doc(json_encode($pines->com_storefront->adjust_quantity((int) $_REQUEST['id'], (int) $_REQUEST['qty'])));

?>