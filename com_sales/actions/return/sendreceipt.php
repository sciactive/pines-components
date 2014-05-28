<?php
/**
 * Send an e-mail receipt.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editreturn') && !gatekeeper('com_sales/newreturn') )
	punt_user(null, pines_url('com_sales', 'return/sendreceipt', $_REQUEST));

$return = com_sales_return::factory((int) $_REQUEST['id']);
if (isset($return->guid)) {
	if ($return->email_receipt()) {
		pines_notice('Receipt Successfully Sent');
	} else {
		pines_notice('Receipt Failed to Send');
	}
} else {
	pines_notice('Cannot send a receipt for the specified invoice.');
}

pines_redirect(pines_url('com_sales', 'return/receipt', array('id' => $_REQUEST['id'])));

?>