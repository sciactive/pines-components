<?php
/**
 * com_customer's configuration defaults.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'com_calendar',
		'cname' => 'Calendar Integration',
		'description' => 'Integrate with com_calendar.',
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
		'name' => 'customer_search_limit',
		'cname' => 'Customer Search Limit',
		'description' => 'Limit the customer search to this many results.',
		'value' => 20,
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
		'name' => 'check_ssn',
		'cname' => 'Check SSNs',
		'description' => 'Notify immediately if a requested SSN is available. (This can technically be used to determine that a specific SSN exists on the system.)',
		'value' => true,
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
		'name' => 'follow_um_rules',
		'cname' => 'Use User Manager Verification',
		'description' => 'Follow the configuration from the user manager about email verification.',
		'value' => true,
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
		'name' => 'follow_up',
		'cname' => 'Follow-Up Appointments',
		'description' => 'Automatically create follow-up appointments when sales are completed.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'negpoints',
		'cname' => 'Allow Negative Points',
		'description' => 'Allow customer\'s points to drop below zero.',
		'value' => false,
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
		'name' => 'critical_fields_customer',
		'cname' => 'Critical Fields',
		'description' => 'These fields are considered to be critical, and should be restricted. Only users with the extra ability can edit them once created.',
		'value' => array(
			'SSN' => 'ssn',
			'DOB' => 'dob',
		),
		'options' => array(
			'Name' => 'name',
			'SSN' => 'ssn',
			'DOB' => 'dob',
			'Email' => 'email',
			'Company' => 'company',
			'Account' => 'account',
			'Description' => 'description',
			'Membership' => 'membership'
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
	array(
		'name' => 'interaction_types',
		'cname' => 'Interaction Types',
		'description' => 'Ways employees can interact with customers. Uses this format: "Symbol:Interaction Type".',
		'value' => array(
			'✪:Greet',
			'☛:In-Person',
			'☎:Phone Call',
			'@:Email',
			'◈:Other',
		),
		'peruser' => true,
	),
	array(
		'name' => 'follow_ups',
		'cname' => 'Follow-Up Schedule',
		'description' => 'Dates after a sale to follow up with a customer. Uses this format: "Symbol|Timespan|Description".',
		'value' => array(
			'➂|3 days|Is the customer happy with their product? Do they have any questions or issues which we can help fix? You can also remind them of your referral program.',
			'➆|7 days|Is the customer happy with their purchase? Do they have any questions or issues with their product? You can also remind them of your referral program.',
			'➀➃|2 weeks|This is the customer\'s last day to return. Remind them that today is the last day they can return/exchange their product. Ask them how they like their purchase, if there are any problems with the product and remind them of your referral program.',
		),
		'peruser' => true,
	),
	array(
		'name' => 'wh_follow_up',
		'cname' => 'Warehouse Follow-Up',
		'description' => 'The date after a warehouse sale to follow up on shipping information. Uses this format: "Symbol|Timespan|Description".',
		'value' => '➄|5 days|This is a warehouse follow-up, make sure to check with the Inventory Department and then let the customer know the shipping status.',
		'peruser' => true,
	),
	array(
		'name' => 'global_customers',
		'cname' => 'Globalize Customers',
		'description' => 'Ensure that every user can access all customers by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
        array(
                'name' => 'no_autocomplete',
                'cname' => 'Disable Autocomplete for Customer Select',
                'description' => 'Enabling this option will not use autocomplete to send multiple requests for a customer query.',
                'value' => false,
        ),
);

?>
