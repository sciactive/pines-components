<?php
/**
 * com_sales's common file.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_sales', 'managecustomers', 'Manage', 'User can manage customers.');
$config->ability_manager->add('com_sales', 'new', 'Create', 'User can create new customers.');
$config->ability_manager->add('com_sales', 'edit', 'Edit', 'User can edit current customers.');
$config->ability_manager->add('com_sales', 'delete', 'Delete', 'User can delete current customers.');

?>