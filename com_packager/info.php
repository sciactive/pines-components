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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Package Creator',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines package creator',
	'description' => 'Package your components and templates into a Pines repository ready Slim archive. You can use these packages to distribute your component to other Pines users.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&uploader&editor&configurator',
		'component' => 'com_slim&com_jquery&com_pgrid&com_ptags&com_pform',
		'package' => 'com_packager-data'
	),
	'abilities' => array(
		array('listpackages', 'List Packages', 'User can see packages.'),
		array('newpackage', 'Create Packages', 'User can create new packages.'),
		array('editpackage', 'Edit Packages', 'User can edit current packages.'),
		array('makepackage', 'Make Packages', 'User can make packages.'),
		array('deletepackage', 'Delete Packages', 'User can delete current packages.')
	),
);

?>