<?php
/**
 * com_modules' information.
 *
 * @package Components
 * @subpackage modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Module Manager',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'A module management system',
	'description' => 'A module management system, which allows you to place modules in different positions on the page.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listmodules', 'List Modules', 'User can see all modules.'),
		array('newmodule', 'Create Modules', 'User can create new modules.'),
		array('editmodule', 'Edit Modules', 'User can edit current modules.'),
		array('deletemodule', 'Delete Modules', 'User can delete current modules.')
	),
);

?>