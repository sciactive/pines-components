<?php
/**
 * com_repository's information.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Package Repository',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines package repository',
	'description' => 'Host a Pines repository of Slim packages. You can use the repository to distribute your components to other Pines users.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_slim&com_jquery&com_pgrid'
	),
	'abilities' => array(
		array('listallpackages', 'List All Packages', 'User can see all packages.'),
		array('listpackages', 'List Packages', 'User can see their own packages.'),
		array('newpackage', 'Create Packages', 'User can upload new packages.'),
		array('deletepackage', 'Delete Packages', 'User can delete current packages.'),
		array('makeallindices', 'Make All Indices', 'User can (re)generate all indices.'),
		array('makeindices', 'Make Own Index', 'User can (re)generate their own index.')
	),
);

?>