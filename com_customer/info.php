<?php
/**
 * com_customer's information.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'CRM',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Customer relationship manager',
	'description' => 'Manage your customers using accounts. Features include membership and point tracking.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&editor',
		'component' => 'com_user&com_jquery&com_bootstrap&com_pgrid&com_pnotify&com_pform'
	),
	'recommend' => array(
		'component' => 'com_sales'
	),
	'abilities' => array(
		array('defaultgroups', 'Edit Default Groups', 'User can set default customer groups.'),
		array('listcustomers', 'List Customers', 'User can see customers.'),
		array('listallcustomers', 'List All Customers', 'User can see all customers using the * search string.'),
		array('newcustomer', 'Create Customers', 'User can create new customers.'),
		array('editcustomer', 'Edit Customers', 'User can edit current customers.'),
		array('deletecustomer', 'Delete Customers', 'User can delete current customers.'),
		array('listcompanies', 'List Companies', 'User can see companies.'),
		array('newcompany', 'Create Companies', 'User can create new companies.'),
		array('editcompany', 'Edit Companies', 'User can edit current companies.'),
		array('deletecompany', 'Delete Companies', 'User can delete current companies.'),
		array('viewhistory', 'View History', 'User can view customers\' histories.'),
		array('newinteraction', 'Create Interactions', 'User can create new interaction records.'),
		array('editinteraction', 'Edit Interactions', 'User can edit interaction records.'),
		array('manageinteractions', 'Manage Interactions', 'User can manage interaction records for other users.'),
		array('editcritical', 'Edit Critical Info', 'User can edit critical information such as SSN and DOB.'),
		array('adjustpoints', 'Adjust Points', 'User can adjust customer\'s points.'),
		array('resetpoints', 'Reset Points', 'User can reset customer\'s points. (Including peak and total.)')
	),
);

?>