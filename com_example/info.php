<?php
/**
 * com_example's information.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Example Component',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'short_description' => 'An example component design',
	'description' => 'This component functions as an example of how to use various features of the Pines framework.',
	'abilities' => array(
		array('listwidgets', 'List Widgets', 'User can see widgets.'),
		array('newwidget', 'Create Widgets', 'User can create new widgets.'),
		array('editwidget', 'Edit Widgets', 'User can edit current widgets.'),
		array('deletewidget', 'Delete Widgets', 'User can delete current widgets.'),
		array('content', 'Example Content', 'User can view example content.')
	),
);

?>