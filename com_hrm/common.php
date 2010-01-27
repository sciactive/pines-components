<?php
/**
 * com_hrm's common file.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_hrm', 'listemployees', 'List Employees', 'User can see employees.');
$config->ability_manager->add('com_hrm', 'newemployee', 'Create Employees', 'User can create new employees.');
$config->ability_manager->add('com_hrm', 'editemployee', 'Edit Employees', 'User can edit current employees.');
$config->ability_manager->add('com_hrm', 'deleteemployee', 'Delete Employees', 'User can delete current employees.');

?>