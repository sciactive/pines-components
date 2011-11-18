<?php
/**
 * com_menueditor's information.
 *
 * @package Pines
 * @subpackage com_menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Menu Editor and Provider',
	'author' => 'SciActive',
	'version' => '0.0.1alpha',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Edit menus and associate menu entries with various items',
	'description' => 'A menu editor that allows you to edit menu entries. It also lets other components provide user managed menu entries for any of their items.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager',
		'component' => 'com_jquery&com_pgrid&com_pform&com_jstree'
	),
	'abilities' => array(
		array('listentries', 'List Entries', 'User can see menu entries.'),
		array('newentry', 'Create Entries', 'User can create new entries.'),
		array('editentry', 'Edit Entries', 'User can edit current entries.'),
		array('deleteentry', 'Delete Entries', 'User can delete current entries.'),
		array('jsentry', 'Edit Entry JavaScript', 'User can edit the onclick JavaScript value of entries.')
	),
);

?>