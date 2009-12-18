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

return array (
  0 =>
  array (
	'name' => 'global_customers',
	'cname' => 'Globalize Customers',
	'description' => 'Ensure that every user can access all customers by setting the "other" access control to read.',
	'value' => true,
  ),
);

?>