<?php
/**
 * com_sales's configuration.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
	'name' => 'global_products',
	'cname' => 'Globalize Products',
	'description' => 'Ensure that every user can access all products by setting the "other" access control to read.',
	'value' => true,
  ),
  1 =>
  array (
	'name' => 'global_manufacturers',
	'cname' => 'Globalize Manufacturers',
	'description' => 'Ensure that every user can access all manufacturers by setting the "other" access control to read.',
	'value' => true,
  ),
  2 =>
  array (
	'name' => 'global_vendors',
	'cname' => 'Globalize Vendors',
	'description' => 'Ensure that every user can access all vendors by setting the "other" access control to read.',
	'value' => true,
  ),
  3 =>
  array (
	'name' => 'global_shippers',
	'cname' => 'Globalize Shippers',
	'description' => 'Ensure that every user can access all shippers by setting the "other" access control to read.',
	'value' => true,
  ),
  4 =>
  array (
	'name' => 'global_customers',
	'cname' => 'Globalize Customers',
	'description' => 'Ensure that every user can access all customers by setting the "other" access control to read.',
	'value' => true,
  ),
  5 =>
  array (
	'name' => 'global_tax_fees',
	'cname' => 'Globalize Taxes/Fees',
	'description' => 'Ensure that every user can access all taxes and fees by setting the "other" access control to read.',
	'value' => true,
  ),
);

?>