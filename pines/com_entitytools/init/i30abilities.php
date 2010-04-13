<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($pines->ability_manager) ) {
	$pines->ability_manager->add('com_entitytools', 'test', 'Test/Benchmark', 'User can test and benchmark the entity manager.');
	$pines->ability_manager->add('com_entitytools', 'export', 'Export', 'User can export all entities.');
	$pines->ability_manager->add('com_entitytools', 'import', 'Import', 'User can import entities.');
}
?>