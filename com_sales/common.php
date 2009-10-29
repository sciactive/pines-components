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
$config->ability_manager->add('com_sales', 'managecustomers', 'Manage Customers', 'User can manage customers.');
$config->ability_manager->add('com_sales', 'newcustomer', 'Create Customers', 'User can create new customers.');
$config->ability_manager->add('com_sales', 'editcustomer', 'Edit Customers', 'User can edit current customers.');
$config->ability_manager->add('com_sales', 'deletecustomer', 'Delete Customers', 'User can delete current customers.');
$config->ability_manager->add('com_sales', 'managetaxfees', 'Manage Taxes/Fees', 'User can manage taxes/fees.');
$config->ability_manager->add('com_sales', 'newtaxfee', 'Create Taxes/Fees', 'User can create new taxes/fees.');
$config->ability_manager->add('com_sales', 'edittaxfee', 'Edit Taxes/Fees', 'User can edit current taxes/fees.');
$config->ability_manager->add('com_sales', 'deletetaxfee', 'Delete Taxes/Fees', 'User can delete current taxes/fees.');
$config->ability_manager->add('com_sales', 'manageproducts', 'Manage Products', 'User can manage products.');
$config->ability_manager->add('com_sales', 'newproduct', 'Create Products', 'User can create new products.');
$config->ability_manager->add('com_sales', 'editproduct', 'Edit Products', 'User can edit current products.');
$config->ability_manager->add('com_sales', 'deleteproduct', 'Delete Products', 'User can delete current products.');

?>