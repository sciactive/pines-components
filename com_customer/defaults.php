<?php
/**
 * com_customer's configuration defaults.
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
	array(
		'name' => 'global_customers',
		'cname' => 'Globalize Customers',
		'description' => 'Ensure that every user can access all customers by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'com_sales',
		'cname' => 'Sales Integration',
		'description' => 'Integrate with com_sales.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'new_users',
		'cname' => 'Add New Users',
		'description' => 'Make all new users customers as well.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'reg_users',
		'cname' => 'Add Newly Registered Users',
		'description' => 'Make new users who are registering themselves customers as well.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'hide_customers',
		'cname' => 'Hide Customers in Users',
		'description' => 'Hide customers when viewing user list in the user manager.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'adjustpoints',
		'cname' => 'Allow Point Adjust',
		'description' => 'Allow customer\'s points to be adjusted by users with the ability.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'resetpoints',
		'cname' => 'Allow Point Reset',
		'description' => 'Allow customer\'s points to be reset by users with the ability.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'membervalues',
		'cname' => 'Member Day Values',
		'description' => 'List of the days of membership available to add as product actions in the POS (com_sales).',
		'value' => array(
			30,
			60
		),
		'peruser' => true,
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
		'peruser' => true,
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
		'peruser' => true,
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
		'peruser' => true,
	),
	array(
		'name' => 'ssn_field',
		'cname' => 'SSN Field',
		'description' => 'Allow Pines to store a Social Security Number for customers.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'shown_fields_customer',
		'cname' => 'Shown Fields',
		'description' => 'These fields will be displayed when creating a customer.',
		'value' => array(
			'Name' => 'name',
			'SSN' => 'ssn',
			'DOB' => 'dob',
			'Email' => 'email',
			'Company' => 'company',
			'Phone' => 'phone',
			'Referrer' => 'referrer',
			'Password' => 'password',
			'Description' => 'description',
			'Points' => 'points',
			'Membership' => 'membership',
			'Address' => 'address',
			'Attributes' => 'attributes',
		),
		'options' => array(
			'Name' => 'name',
			'SSN' => 'ssn',
			'DOB' => 'dob',
			'Email' => 'email',
			'Company' => 'company',
			'Phone' => 'phone',
			'Referrer' => 'referrer',
			'Password' => 'password',
			'Description' => 'description',
			'Points' => 'points',
			'Membership' => 'membership',
			'Address' => 'address',
			'Attributes' => 'attributes',
		),
		'peruser' => true,
	),
	array(
		'name' => 'required_fields_customer',
		'cname' => 'Required Fields',
		'description' => 'These fields must be filled out when creating a customer.',
		'value' => array(
			'Name' => 'name',
			'Phone' => 'phone',
		),
		'options' => array(
			'Name' => 'name',
			'SSN' => 'ssn',
			'DOB' => 'dob',
			'Email' => 'email',
			'Company' => 'company',
			'Phone' => 'phone',
			'Referrer' => 'referrer',
			'Password' => 'password',
			'Description' => 'description',
			'Address' => 'address'
		),
		'peruser' => true,
	),
	array(
		'name' => 'referrer_values',
		'cname' => 'Referrer Values',
		'description' => 'These options will be available for "How Did You Hear About Us".',
		'value' => array(
			'Friend',
			'Link',
			'Google',
			'Yahoo',
			'Other Web Search',
			'TV Ad',
			'Radio Ad',
			'Magazine Ad',
			'Other',
		),
		'peruser' => true,
	),
);

?>
