<?php
/**
 * com_entitytools' information.
 *
 * @package Components\entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Entity Manager Tools',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Tools for testing and maintaining your entity manager',
	'description' => 'Includes the following tools: test, benchmark, export, import.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager',
		'component' => 'com_jquery&com_bootstrap&com_pform'
	),
	'abilities' => array(
		array('test', 'Test/Benchmark', 'User can test and benchmark the entity manager.'),
		array('export', 'Export', 'User can export all entities.'),
		array('import', 'Import', 'User can import entities.')
	),
);

?>