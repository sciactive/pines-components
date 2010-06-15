<?php
/**
 * Provide a form to edit a stock entry.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/managestock') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'stock/edit', array('id' => $_REQUEST['id'])));
} else {
	punt_user('No id specified.');
}

$entity = com_sales_stock::factory((int) $_REQUEST['id']);
$entity->print_form();

?>