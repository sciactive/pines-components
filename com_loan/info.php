<?php
/**
 * com_loan's information.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Loans',
	'author' => 'SciActive',
	'version' => '1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Create and edit fixed loans',
	'description' => 'This component creates, amortizes, and records payments on loans.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'entity_manager&user_manager',
		'component' => 'com_customer&com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listloans', 'List Loans', 'User can view the list of loans.'),
		array('listallloans', 'List All Loans', 'User can search all loans using *.'),
		array('newloan', 'Create Loans', 'User can create new loans.'),
		array('editloan', 'Edit Loans', 'User can edit current loans.'),
		array('editpayments', 'Edit Payments', 'User can edit payments and view the edit log on a loan.'),
		array('changestatus', 'Change Loan Status', 'User can change status on loans.'),
		array('activeloan', 'Set Active Loan', 'User can change status on loans to active.'),
		array('payoffloan', 'Pay Off Loan', 'User can change status on loans to paid off.'),
		array('writeoffloan', 'Write Off Loan', 'User can change status on loans to written off.'),
		array('cancelloan', 'Cancel Loan', 'User can change status on loans to cancelled.'),
		array('soldloan', 'Sold Loan', 'User can change status on loans to sold.'),
		array('changecollectioncode', 'Change Collection Code', 'User can change the collection code on a loan.'),
		array('deletepayments', 'Delete Payments', 'User can delete and restore payments on loans.'),
		array('makepayment', 'Make Payment', 'User can make payments on loans.'),
		array('viewloan', 'View Loans', 'User can view current loans.'),
		array('deleteloan', 'Delete Loans', 'User can delete current loans.')
	),
);

?>