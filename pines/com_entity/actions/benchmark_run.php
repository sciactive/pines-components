<?php
/**
 * Run a benchmark.
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

$pines->page->override = true;

set_time_limit(3600);

$benchmark->time_start = microtime(true);

// Creating 1000 entities...
$pass = true;
for ($i=0; $i<1000; $i++) {
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
$benchmark->create[0] = $pass;
$benchmark->create[1] = microtime(true);

// Retrieving entities...
$entities = $pines->entity_manager->get_entities(array('limit' => 1000, 'tags' => array('com_entity', 'benchmark')));
$benchmark->retrieve[0] = count($entities) == 1000;
$benchmark->retrieve[1] = microtime(true);

// Deleting entities...
$pass = true;
foreach ($entities as $cur_entity) {
	$pass = $pass && $cur_entity->delete();
}
$benchmark->delete[0] = $pass;
$benchmark->delete[1] = microtime(true);

$benchmark->time_end = microtime(true);

$pines->page->override_doc(json_encode($benchmark));

?>