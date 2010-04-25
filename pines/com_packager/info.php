<?php
/**
 * com_packager's information.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Package Creator',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'short_description' => 'Pines package creator',
	'description' => 'Package your components and templates into a Pines repository ready Slim archive. You can use these packages to distribute your component to other Pines users.',
	'abilities' => array(
		array('listpackages', 'List Packages', 'User can see packages.'),
		array('newpackage', 'Create Packages', 'User can create new packages.'),
		array('editpackage', 'Edit Packages', 'User can edit current packages.'),
		array('deletepackage', 'Delete Packages', 'User can delete current packages.')
	),
);

?>