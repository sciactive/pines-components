<?php
/**
 * com_customer's information.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'CRM',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Customer relationship manager',
	'description' => 'Manage your customers using accounts. Features include membership and point tracking.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&editor',
		'component' => 'com_jquery&com_pgrid&com_pnotify'
	),
	'recommend' => array(
		'component' => 'com_sales'
	),
	'abilities' => array(
		array('listcustomers', 'List Customers', 'User can see customers.'),
		array('newcustomer', 'Create Customers', 'User can create new customers.'),
		array('editcustomer', 'Edit Customers', 'User can edit current customers.'),
		array('deletecustomer', 'Delete Customers', 'User can delete current customers.'),
		array('listcompanies', 'List Companies', 'User can see companies.'),
		array('newcompany', 'Create Companies', 'User can create new companies.'),
		array('editcompany', 'Edit Companies', 'User can edit current companies.'),
		array('deletecompany', 'Delete Companies', 'User can delete current companies.'),
		array('adjustpoints', 'Adjust Points', 'User can adjust customer\'s points.'),
		array('resetpoints', 'Reset Points', 'User can reset customer\'s points. (Including peak and total.)')
	),
);

?>