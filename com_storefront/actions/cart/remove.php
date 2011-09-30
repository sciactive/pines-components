<?php
/**
 * Remove a product from the cart.
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
$pines->page->override_doc(json_encode($pines->com_storefront->remove_from_cart((int) $_REQUEST['id'])));

?>