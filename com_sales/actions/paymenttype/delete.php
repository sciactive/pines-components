<?php
/**
 * Delete a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletepaymenttype') )
	punt_user(null, pines_url('com_sales', 'paymenttype/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_payment_type) {
	$cur_entity = com_sales_payment_type::factory((int) $cur_payment_type);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_payment_type;
}
if (empty($failed_deletes)) {
	pines_notice('Selected payment type(s) deleted successfully.');
} else {
	pines_error('Could not delete payment types with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_sales', 'paymenttype/list'));

?>