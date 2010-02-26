<?php
/**
 * Run a benchmark to test an entity manager's speed.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_entity', 'benchmark', null, false));

if ($_REQUEST['sure'] != 'yes') {
	$benchmark = new module('com_entity', 'benchmark_confirm', 'content');
	return;
}
$benchmark = new module('com_entity', 'benchmark', 'content');

if (!($pines->entity_manager)) {
	$benchmark->error = true;
	return;
}

set_time_limit(3600);

$entity_start_time = microtime(true);
$benchmark->time_start = $entity_start_time;

// Creating 10000 entities...
$pass = true;
for ($i=0; $i<10000; $i++) {
	$entity = entity::factory();
	$entity->add_tag('com_entity', 'benchmark');
	$entity->name = "Entity Benchmark ".time();
	$entity->int = 1000;
	$entity->float = 10.5;
	$entity->null = null;
	$entity->array = array('string', 0, 1.5, null);
	$entity->object = (object) array('string' => 'string', 'int' => 0, 'float' => 1.5, 'null' => null);
	$pass = $pass && $entity->save();
}
$benchmark->tests['create'][0] = $pass;
$benchmark->tests['create'][1] = microtime(true);
$benchmark->tests['create'][2] = 'Creating 100000 entities...';

// Retrieving entities...
for ($i=0; $i<10000; $i++) {
	$entities = $pines->entity_manager->get_entities(array('offset' => $i, 'limit' => 1, 'tags' => array('com_entity', 'benchmark')));
}
$benchmark->tests['retrieve'][0] = count($entities);
$benchmark->tests['retrieve'][1] = microtime(true);
$benchmark->tests['retrieve'][2] = 'Retrieving entities...';

// Deleting entities...
$pass = true;
for ($i=0; $i<10000; $i++) {
	$entities = $pines->entity_manager->get_entities(array('offset' => $i, 'limit' => 1, 'tags' => array('com_entity', 'benchmark')));
	$pass = $pass && $entities[0]->delete();
}
$benchmark->tests['delete'][0] = $pass;
$benchmark->tests['delete'][1] = microtime(true);
$benchmark->tests['delete'][2] = 'Deleting entities...';

$benchmark->time_end = microtime(true);

?>