<?php
/**
 * com_customer's common file.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_customer', 'managecustomers', 'Manage Customer Accounts', 'User can manage customer accounts.');
$config->ability_manager->add('com_customer', 'editcustomer', 'Edit Customer Accounts', 'User can edit current customer accounts.');
$config->ability_manager->add('com_customer', 'adjustpoints', 'Adjust Points', 'User can adjust customer\'s points.');

?>