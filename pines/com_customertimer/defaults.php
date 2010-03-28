<?php
/**
 * com_customertimer's configuration defaults.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'openlogin',
		'cname' => 'Open Login',
		'description' => 'Allow all users and guests to see the customer login page.',
		'value' => true,
	),
	array(
		'name' => 'debtlogin',
		'cname' => 'Debt Login',
		'description' => 'Allow customers to log in when their account is overdrawn.',
		'value' => false,
	),
	array(
		'name' => 'ppm',
		'cname' => 'Points per Minute',
		'description' => 'The amount of points to subtract from the user\'s account for every minute they are logged in.',
		'value' => 1,
	),
	array(
		'name' => 'level_warning',
		'cname' => 'Warning Level',
		'description' => 'Amount of points to consider the customer to be in the warning level.',
		'value' => 10,
	),
	array(
		'name' => 'level_critical',
		'cname' => 'Critical Level',
		'description' => 'Amount of points to consider the customer to be in the critical level.',
		'value' => 3,
	),
);

?>