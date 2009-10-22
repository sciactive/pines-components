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

$config->ability_manager->add('com_customer', 'managecustomers', 'Manage', 'User can manage customers.');
$config->ability_manager->add('com_customer', 'new', 'Create', 'User can create new customers.');
$config->ability_manager->add('com_customer', 'edit', 'Edit', 'User can edit current customers.');
$config->ability_manager->add('com_customer', 'delete', 'Delete', 'User can delete current customers.');

?>