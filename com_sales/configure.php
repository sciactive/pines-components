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
    'name' => 'global_customers',
    'cname' => 'Globalize Customers',
    'description' => 'Ensure that every user can access all customers by erasing ownership on customers.',
    'value' => true,
  ),
  1 =>
  array (
    'name' => 'global_manufacturers',
    'cname' => 'Globalize Manufacturers',
    'description' => 'Ensure that every user can access all manufacturers by erasing ownership on manufacturers.',
    'value' => true,
  ),
  2 =>
  array (
    'name' => 'global_vendors',
    'cname' => 'Globalize Vendors',
    'description' => 'Ensure that every user can access all vendors by erasing ownership on vendors.',
    'value' => true,
  ),
);

?>