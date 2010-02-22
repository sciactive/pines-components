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

$pines->ability_manager->add('com_customer', 'listcustomers', 'List Customers', 'User can see customers.');
$pines->ability_manager->add('com_customer', 'newcustomer', 'Create Customers', 'User can create new customers.');
$pines->ability_manager->add('com_customer', 'editcustomer', 'Edit Customers', 'User can edit current customers.');
$pines->ability_manager->add('com_customer', 'deletecustomer', 'Delete Customers', 'User can delete current customers.');
$pines->ability_manager->add('com_customer', 'listcompanies', 'List Companies', 'User can see companies.');
$pines->ability_manager->add('com_customer', 'newcompany', 'Create Companies', 'User can create new companies.');
$pines->ability_manager->add('com_customer', 'editcompany', 'Edit Companies', 'User can edit current companies.');
$pines->ability_manager->add('com_customer', 'deletecompany', 'Delete Companies', 'User can delete current companies.');
$pines->ability_manager->add('com_customer', 'adjustpoints', 'Adjust Points', 'User can adjust customer\'s points.');
$pines->ability_manager->add('com_customer', 'resetpoints', 'Reset Points', 'User can reset customer\'s points. (Including peak and total.)');

?>