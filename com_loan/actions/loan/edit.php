<?php
/**
 * Provide a form to edit a loan.
 *
 * @package Components
 * @subpackage loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_loan/editloan') )
		punt_user(null, pines_url('com_loan', 'loan/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_loan/newloan') )
		punt_user(null, pines_url('com_loan', 'loan/edit'));
}

$entity = com_loan_loan::factory((int) $_REQUEST['id']);
$entity->print_form();

?>