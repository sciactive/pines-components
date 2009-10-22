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

$config->ability_manager->add('com_sales', 'managemanufacturers', 'Manage Manufacturers', 'User can manage manufacturers.');
$config->ability_manager->add('com_sales', 'newmanufacturer', 'Create Manufacturers', 'User can create new manufacturers.');
$config->ability_manager->add('com_sales', 'editmanufacturer', 'Edit Manufacturers', 'User can edit current manufacturers.');
$config->ability_manager->add('com_sales', 'deletemanufacturer', 'Delete Manufacturers', 'User can delete current manufacturers.');

?>