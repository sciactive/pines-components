<?php
/**
 * com_sales' information.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'POS',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Point of Sales system',
	'description' => 'Manage products, inventory, sales, shipments, etc. Sell merchandise. Integrates with a cash drawer.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager&entity_manager&editor',
		'component' => 'com_jquery&com_pgrid&com_pnotify&com_ptags&com_jstree'
	),
	'recommend' => array(
		'component' => 'com_customer'
	),
	'abilities' => array(
		array('manager', 'Manager', 'User is a manager. This lets the user approve payments.'),
		array('receive', 'Receive Inventory', 'User can receive inventory into their stock.'),
		array('managestock', 'Manage Stock', 'User can transfer and adjust stock.'),
		array('totalsales', 'Total Sales', 'User can see sales totals.'),
		array('totalothersales', 'Total Other Sales', 'User can see sales totals of other locations.'),
		array('listsales', 'List Sales', 'User can see sales.'),
		array('newsale', 'Create Sales', 'User can create new sales.'),
		array('editsale', 'Edit Sales', 'User can edit current sales.'),
		array('voidsale', 'Void Sales', 'User can void sales, returning any stock to inventory.'),
		array('deletesale', 'Delete Sales', 'User can delete current sales.'),
		array('listreturns', 'List Returns', 'User can see returns.'),
		array('newreturn', 'Create Returns', 'User can create new returns.'),
		array('editreturn', 'Edit Returns', 'User can edit current returns.'),
		array('voidreturn', 'Void Returns', 'User can void returns.'),
		array('deletereturn', 'Delete Returns', 'User can delete current returns.'),
		array('listmanufacturers', 'List Manufacturers', 'User can see manufacturers.'),
		array('newmanufacturer', 'Create Manufacturers', 'User can create new manufacturers.'),
		array('editmanufacturer', 'Edit Manufacturers', 'User can edit current manufacturers.'),
		array('deletemanufacturer', 'Delete Manufacturers', 'User can delete current manufacturers.'),
		array('listvendors', 'List Vendors', 'User can see vendors.'),
		array('newvendor', 'Create Vendors', 'User can create new vendors.'),
		array('editvendor', 'Edit Vendors', 'User can edit current vendors.'),
		array('deletevendor', 'Delete Vendors', 'User can delete current vendors.'),
		array('listshippers', 'List Shippers', 'User can see shippers.'),
		array('newshipper', 'Create Shippers', 'User can create new shippers.'),
		array('editshipper', 'Edit Shippers', 'User can edit current shippers.'),
		array('deleteshipper', 'Delete Shippers', 'User can delete current shippers.'),
		array('listtaxfees', 'List Taxes/Fees', 'User can see taxes/fees.'),
		array('newtaxfee', 'Create Taxes/Fees', 'User can create new taxes/fees.'),
		array('edittaxfee', 'Edit Taxes/Fees', 'User can edit current taxes/fees.'),
		array('deletetaxfee', 'Delete Taxes/Fees', 'User can delete current taxes/fees.'),
		array('listpaymenttypes', 'List Payment Types', 'User can see payment types.'),
		array('newpaymenttype', 'Create Payment Types', 'User can create new payment types.'),
		array('editpaymenttype', 'Edit Payment Types', 'User can edit current payment types.'),
		array('deletepaymenttype', 'Delete Payment Types', 'User can delete current payment types.'),
		array('listproducts', 'List Products', 'User can see products.'),
		array('newproduct', 'Create Products', 'User can create new products.'),
		array('editproduct', 'Edit Products', 'User can edit current products.'),
		array('deleteproduct', 'Delete Products', 'User can delete current products.'),
		array('listcategories', 'List Categories', 'User can see categories. (Not needed to see categories during a sale.)'),
		array('newcategory', 'Create Categories', 'User can create new categories.'),
		array('editcategory', 'Edit Categories', 'User can edit current categories.'),
		array('deletecategory', 'Delete Categories', 'User can delete current categories.'),
		array('listpos', 'List Purchase Orders', 'User can see POs.'),
		array('newpo', 'Create Purchase Orders', 'User can create new POs.'),
		array('editpo', 'Edit Purchase Orders', 'User can edit current POs.'),
		array('deletepo', 'Delete Purchase Orders', 'User can delete current POs.'),
		array('listcashcounts', 'List Cash Counts', 'User can see cash counts.'),
		array('newcashcount', 'Create Cash Counts', 'User can create new cash counts.'),
		array('editcashcount', 'Edit Cash Counts', 'User can edit current cash counts.'),
		array('deletecashcount', 'Delete Cash Counts', 'User can delete current cash counts.'),
		array('approvecashcount', 'Approve Cash Counts', 'User can approve cash counts.'),
		array('assigncashcount', 'Assign Cash Counts', 'User can assign cash counts.'),
		array('listcountsheets', 'List Countsheets', 'User can see countsheets.'),
		array('newcountsheet', 'Create Countsheets', 'User can create new countsheets.'),
		array('editcountsheet', 'Edit Countsheets', 'User can edit current countsheets.'),
		array('deletecountsheet', 'Delete Countsheets', 'User can delete current countsheets.'),
		array('printcountsheet', 'Print Countsheets', 'User can print countsheets.'),
		array('approvecountsheet', 'Approve Countsheets', 'User can approve countsheets.'),
		array('assigncountsheet', 'Assign Countsheets', 'User can assign countsheets.')
	),
);

?>