<?php
/**
 * com_sales's common file.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_sales', 'managemanufacturers', 'Manage Manufacturers', 'User can manage manufacturers.');
$config->ability_manager->add('com_sales', 'newmanufacturer', 'Create Manufacturers', 'User can create new manufacturers.');
$config->ability_manager->add('com_sales', 'editmanufacturer', 'Edit Manufacturers', 'User can edit current manufacturers.');
$config->ability_manager->add('com_sales', 'deletemanufacturer', 'Delete Manufacturers', 'User can delete current manufacturers.');
$config->ability_manager->add('com_sales', 'managevendors', 'Manage Vendors', 'User can manage vendors.');
$config->ability_manager->add('com_sales', 'newvendor', 'Create Vendors', 'User can create new vendors.');
$config->ability_manager->add('com_sales', 'editvendor', 'Edit Vendors', 'User can edit current vendors.');
$config->ability_manager->add('com_sales', 'deletevendor', 'Delete Vendors', 'User can delete current vendors.');
$config->ability_manager->add('com_customer', 'managecustomers', 'Manage', 'User can manage customers.');
$config->ability_manager->add('com_customer', 'newcustomer', 'Create', 'User can create new customers.');
$config->ability_manager->add('com_customer', 'editcustomer', 'Edit', 'User can edit current customers.');
$config->ability_manager->add('com_customer', 'deletecustomer', 'Delete', 'User can delete current customers.');

?>