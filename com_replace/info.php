<?php
/**
 * com_replace's information.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Content Search Replace',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Content search replace',
	'description' => 'Search and replace strings in content, such as pages.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager',
		'component' => 'com_jquery&com_pgrid'
	),
	'abilities' => array(
		array('listreplacements', 'List Replacements', 'User can see replacements.'),
		array('newreplacement', 'Create Replacements', 'User can create new replacements.'),
		array('editreplacement', 'Edit Replacements', 'User can edit current replacements.'),
		array('deletereplacement', 'Delete Replacements', 'User can delete current replacements.')
	),
);

?>