<?php
/**
 * Provide a form to edit a company.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_customer/editcompany') )
		punt_user(null, pines_url('com_customer', 'company/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_customer/newcompany') )
		punt_user(null, pines_url('com_customer', 'company/edit'));
}

$entity = com_customer_company::factory((int) $_REQUEST['id']);
$entity->print_form();

?>