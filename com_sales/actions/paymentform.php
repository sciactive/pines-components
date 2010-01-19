<?php
/**
 * Provide a form for a payment process type to collect information.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editsale') && !gatekeeper('com_sales/newsale') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'payment_form', null, false));

$page->override = true;
$config->run_sales->call_payment_process(array(
	'action' => 'request',
	'name' => $_REQUEST['name']
));

?>