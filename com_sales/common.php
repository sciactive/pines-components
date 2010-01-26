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

$config->ability_manager->add('com_sales', 'manager', 'Manager', 'User is a manager. This lets the user approve payments.');
$config->ability_manager->add('com_sales', 'clock', 'Clock In/Out', 'User can use the employee timeclock.');
$config->ability_manager->add('com_sales', 'manageclock', 'Manage Clock', 'User can manage the employee timeclock.');
$config->ability_manager->add('com_sales', 'receive', 'Receive Inventory', 'User can receive inventory into their stock.');
$config->ability_manager->add('com_sales', 'managestock', 'Manage Stock', 'User can transfer and adjust stock.');
$config->ability_manager->add('com_sales', 'totalsales', 'Total Sales', 'User can see sales totals.');
$config->ability_manager->add('com_sales', 'totalothersales', 'Total Other Sales', 'User can see sales totals of other locations.');
$config->ability_manager->add('com_sales', 'listsales', 'List Sales', 'User can view sale list.');
$config->ability_manager->add('com_sales', 'newsale', 'Create Sales', 'User can create new sales.');
$config->ability_manager->add('com_sales', 'editsale', 'Edit Sales', 'User can edit current sales.');
$config->ability_manager->add('com_sales', 'deletesale', 'Delete Sales', 'User can delete current sales.');
$config->ability_manager->add('com_sales', 'listmanufacturers', 'List Manufacturers', 'User can view manufacturer list.');
$config->ability_manager->add('com_sales', 'newmanufacturer', 'Create Manufacturers', 'User can create new manufacturers.');
$config->ability_manager->add('com_sales', 'editmanufacturer', 'Edit Manufacturers', 'User can edit current manufacturers.');
$config->ability_manager->add('com_sales', 'deletemanufacturer', 'Delete Manufacturers', 'User can delete current manufacturers.');
$config->ability_manager->add('com_sales', 'listvendors', 'List Vendors', 'User can view vendor list.');
$config->ability_manager->add('com_sales', 'newvendor', 'Create Vendors', 'User can create new vendors.');
$config->ability_manager->add('com_sales', 'editvendor', 'Edit Vendors', 'User can edit current vendors.');
$config->ability_manager->add('com_sales', 'deletevendor', 'Delete Vendors', 'User can delete current vendors.');
$config->ability_manager->add('com_sales', 'listshippers', 'List Shippers', 'User can view shipper list.');
$config->ability_manager->add('com_sales', 'newshipper', 'Create Shippers', 'User can create new shippers.');
$config->ability_manager->add('com_sales', 'editshipper', 'Edit Shippers', 'User can edit current shippers.');
$config->ability_manager->add('com_sales', 'deleteshipper', 'Delete Shippers', 'User can delete current shippers.');
$config->ability_manager->add('com_sales', 'listtaxfees', 'List Taxes/Fees', 'User can view tax/fee list.');
$config->ability_manager->add('com_sales', 'newtaxfee', 'Create Taxes/Fees', 'User can create new taxes/fees.');
$config->ability_manager->add('com_sales', 'edittaxfee', 'Edit Taxes/Fees', 'User can edit current taxes/fees.');
$config->ability_manager->add('com_sales', 'deletetaxfee', 'Delete Taxes/Fees', 'User can delete current taxes/fees.');
$config->ability_manager->add('com_sales', 'listpaymenttypes', 'List Payment Types', 'User can view payment type list.');
$config->ability_manager->add('com_sales', 'newpaymenttype', 'Create Payment Types', 'User can create new payment types.');
$config->ability_manager->add('com_sales', 'editpaymenttype', 'Edit Payment Types', 'User can edit current payment types.');
$config->ability_manager->add('com_sales', 'deletepaymenttype', 'Delete Payment Types', 'User can delete current payment types.');
$config->ability_manager->add('com_sales', 'listproducts', 'List Products', 'User can view product list.');
$config->ability_manager->add('com_sales', 'newproduct', 'Create Products', 'User can create new products.');
$config->ability_manager->add('com_sales', 'editproduct', 'Edit Products', 'User can edit current products.');
$config->ability_manager->add('com_sales', 'deleteproduct', 'Delete Products', 'User can delete current products.');
$config->ability_manager->add('com_sales', 'listpos', 'List Purchase Orders', 'User can view PO list.');
$config->ability_manager->add('com_sales', 'newpo', 'Create Purchase Orders', 'User can create new POs.');
$config->ability_manager->add('com_sales', 'editpo', 'Edit Purchase Orders', 'User can edit current POs.');
$config->ability_manager->add('com_sales', 'deletepo', 'Delete Purchase Orders', 'User can delete current POs.');
$config->ability_manager->add('com_sales', 'managecategories', 'Manage Categories', 'User can manage categories.');
$config->ability_manager->add('com_sales', 'viewcategories', 'View Categories', 'User can view categories.');

?>