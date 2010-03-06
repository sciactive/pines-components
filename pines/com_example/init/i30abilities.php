<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_example', 'listwidgets', 'List Widgets', 'User can see widgets.');
$pines->ability_manager->add('com_example', 'newwidget', 'Create Widgets', 'User can create new widgets.');
$pines->ability_manager->add('com_example', 'editwidget', 'Edit Widgets', 'User can edit current widgets.');
$pines->ability_manager->add('com_example', 'deletewidget', 'Delete Widgets', 'User can delete current widgets.');
$pines->ability_manager->add('com_example', 'content', 'Example Content', 'User can view example content.');

?>