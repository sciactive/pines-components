<?php
/**
 * Test an entity manager for compliance with Dandelion's entity management
 * system.
 *
 * @package Dandelion
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

/**
 * @todo Add tests for custom entity extended classes.
 */

if ( !gatekeeper() ) {
	$config->user_manager->punt_user("You are not logged in.", $config->template->url('com_entity', 'test', null, false));
	return;
}

$com_entity_test = new module('com_entity', 'test', 'content');
$com_entity_test->title = 'Entity Manager Tester';

if (!($config->entity_manager)) {
	$com_entity_test->error = true;
	return;
}

$entity_start_time = microtime(true);

$entity_test = new entity;
$com_entity_test->tests[0] = (is_null($entity_test->guid));

$entity_test->name = "Entity Test ".time();
$entity_test->parent = 0;
$entity_test->add_tag('com_entity', 'test');
$entity_test->test_value = 'test';
$com_entity_test->tests[1] = ($entity_test->save() && !is_null($entity_test->guid));

$com_entity_test->tests[2] = ($entity_test->has_tag('com_entity', 'test') && !$entity_test->has_tag('pickles'));

$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid);
$com_entity_test->tests[3] = ($entity_result->name == $entity_test->name);
unset($entity_result);

$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid + 1);
$com_entity_test->tests[4] = (empty($entity_result) ? true : ($entity_result->name != $entity_test->name));
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent($entity_test->parent);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[5] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent(1);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[6] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[7] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[8] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[9] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[10] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'test', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[11] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[12] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[13] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[14] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('pickles'), array('test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[15] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[16] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[17] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[18] = ($found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[19] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[20] = (!$found_match);
unset($entity_result);

$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$com_entity_test->tests[21] = (!$found_match);
unset($entity_result);

$com_entity_test->tests[22] = ($entity_test->delete() && is_null($entity_test->guid));

$com_entity_test->tests[23] = ($entity_test->save() && !is_null($entity_test->guid));

$com_entity_test->tests[24] = ($config->entity_manager->delete_entity_by_id($entity_test->guid));

$com_entity_test->time = microtime(true) - $entity_start_time;

?>
