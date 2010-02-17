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
		'name' => 'membervalues',
		'cname' => 'Member Day Values',
		'description' => 'List of the days of membership available to add as product actions in the POS (com_sales).',
		'value' => array(
			30,
			60
		),
	),
	array(
		'name' => 'pointvalues',
		'cname' => 'Static Point Values',
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
		'name' => 'guest_point_lookup',
		'cname' => 'Guest Point Lookup',
		'description' => 'When a non-member customer purchases points, this table is used to calculate the number of points to add. Entries should be the lowest price, then a colon, then the price per point for that price range.',
		'value' => array(
			'0:0.198',
			'4.99:0.1848',
			'9.99:0.1537',
			'14.99:0.1199',
			'19.99:0.0999',
			'49.99:0.09085',
			'99.99:0.07999',
			'199.99:0.07017',
			'499.99:0.06101'
		),
	),
	array(
		'name' => 'member_point_lookup',
		'cname' => 'Member Point Lookup',
		'description' => 'When a member customer purchases points, this table is used to calculate the number of points to add.',
		'value' => array(
			'0:0.165',
			'4.99:0.1188',
			'9.99:0.1',
			'14.99:0.0855',
			'19.99:0.0754',
			'49.99:0.0649',
			'99.99:0.0606',
			'199.99:0.05755',
			'499.99:0.05555'
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