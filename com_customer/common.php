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

$config->ability_manager->add('com_customer', 'listcustomers', 'List Customers', 'User can view customer list.');
$config->ability_manager->add('com_customer', 'newcustomer', 'Create Customers', 'User can create new customers.');
$config->ability_manager->add('com_customer', 'editcustomer', 'Edit Customers', 'User can edit current customers.');
$config->ability_manager->add('com_customer', 'deletecustomer', 'Delete Customers', 'User can delete current customers.');
$config->ability_manager->add('com_customer', 'listcompanies', 'List Companies', 'User can view company list.');
$config->ability_manager->add('com_customer', 'newcompany', 'Create Companies', 'User can create new companies.');
$config->ability_manager->add('com_customer', 'editcompany', 'Edit Companies', 'User can edit current companies.');
$config->ability_manager->add('com_customer', 'deletecompany', 'Delete Companies', 'User can delete current companies.');
$config->ability_manager->add('com_customer', 'adjustpoints', 'Adjust Points', 'User can adjust customer\'s points.');

?>