<?php
/**
 * com_customer's configuration.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_customers',
		'cname' => 'Globalize Customers',
		'description' => 'Ensure that every user can access all customers by setting the "other" access control to read.',
		'value' => true,
	),
	array(
		'name' => 'adjustpoints',
		'cname' => 'Allow Point Adjust',
		'description' => 'Allow customer\'s points to be adjusted by users with the ability.',
		'value' => true,
	),
	array(
		'name' => 'resetpoints',
		'cname' => 'Allow Point Reset',
		'description' => 'Allow customer\'s points to be reset by users with the ability.',
		'value' => true,
	),
	array(
		'name' => 'pointvalues',
		'cname' => 'Point Values',
		'description' => 'List of the point values available to add as product actions in the POS (com_sales). Values can be negative to take away points.',
		'value' => array(
			60,
			100,
			120,
			500,
			1000
		),
	),
	array(
		'name' => 'ssn_field',
		'cname' => 'SSN Field',
		'description' => 'Allow Pines to store a Social Security Number for customers.',
		'value' => true,
	),
);

?>