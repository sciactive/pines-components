<?php
/**
 * Verify the inventory that is listed in the storefront.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editproduct') )
	punt_user(null, pines_url('com_storefront', 'verifystock'));

$pines->com_storefront->verify_stock();

?>