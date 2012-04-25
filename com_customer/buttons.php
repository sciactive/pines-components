<?php
/**
 * com_customer's buttons.
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
	'companies' => array(
		'description' => 'Company list.',
		'text' => 'Companies',
		'class' => 'picon-resource-group',
		'href' => pines_url('com_customer', 'company/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_customer/listcompanies',
		),
	),
	'company_new' => array(
		'description' => 'New company.',
		'text' => 'Company',
		'class' => 'picon-resource-group-new',
		'href' => pines_url('com_customer', 'company/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_customer/newcompany',
		),
	),
	'customers' => array(
		'description' => 'Customer list.',
		'text' => 'Customers',
		'class' => 'picon-x-office-address-book',
		'href' => pines_url('com_customer', 'customer/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_customer/listcustomers',
		),
	),
	'customer_new' => array(
		'description' => 'New customer.',
		'text' => 'Customer',
		'class' => 'picon-list-resource-add',
		'href' => pines_url('com_customer', 'customer/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_customer/newcustomer',
		),
	),
);

?>