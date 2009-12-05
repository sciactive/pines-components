<?php
/**
 * Test an entity manager for compliance with Pines' entity management
 * system.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * @todo Add tests for custom entity extended classes.
 */

if ( !gatekeeper('system/all') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_entity', 'test', null, false));
	return;
}

$test = new module('com_entity', 'test', 'content');

if (!($config->entity_manager)) {
	$test->error = true;
	return;
}

$entity_start_time = microtime(true);

// Creating entity...
$entity_test = new entity('com_entity', 'test');
$test->tests['create'] = (is_null($entity_test->guid));

// Saving entity...
$entity_test->name = "Entity Test ".time();
$entity_test->parent = 0;
$entity_test->test_value = 'test';
$entity_test->wilcard_test = '"quotes" %percents% _underscores_ \'single quotes\' /slashes/ \backslashes\ ;semicolons;';
$test->tests['save'] = ($entity_test->save() && !is_null($entity_test->guid));
$entity_guid = $entity_test->guid;

// Checking entity's has_tag method...
$test->tests['has_tag'] = ($entity_test->has_tag('com_entity', 'test') && !$entity_test->has_tag('pickles'));

// Retrieving entity by GUID...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid);
$test->tests['by_guid'] = ($entity_result->name == $entity_test->name);
unset($entity_result);

// Testing wrong GUID...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid + 1);
$test->tests['wrong_guid'] = (empty($entity_result) ? true : ($entity_result->name != $entity_test->name));
unset($entity_result);

// Retrieving entity by GUID and tags...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid, array('com_entity', 'test'));
$test->tests['guid_tags'] = ($entity_result->name == $entity_test->name);
unset($entity_result);

// Testing GUID and wrong tags...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid, array('com_entity', 'pickles'));
$test->tests['guid_wr_tags'] = empty($entity_result);
unset($entity_result);

// Retrieving entity by parent...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent($entity_test->parent);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['parent'] = ($found_match);
unset($entity_result);

// Testing wrong parent...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent(1);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_parent'] = (!$found_match);
unset($entity_result);

// Retrieving entity by tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags'] = ($found_match);
unset($entity_result);

// Testing wrong tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags'] = (!$found_match);
unset($entity_result);

// Retrieving entity by tags exclusively...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_exc'] = ($found_match);
unset($entity_result);

// Testing wrong exclusive tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_exc'] = (!$found_match);
unset($entity_result);

// Retrieving entity by tags inclusively...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'test', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_inc'] = ($found_match);
unset($entity_result);

// Testing wrong inclusive tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_inc'] = (!$found_match);
unset($entity_result);

// Retrieving entity by mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['mixed_tags'] = ($found_match);
unset($entity_result);

// Testing wrong inclusive mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_inc_mx_tags'] = (!$found_match);
unset($entity_result);

// Testing wrong exclusive mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('pickles'), array('test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_exc_mx_tags'] = (!$found_match);
unset($entity_result);

// Retrieving entity by data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['data'] = ($found_match);
unset($entity_result);

// Testing wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_data'] = (!$found_match);
unset($entity_result);

// Retrieving entity by data wildcards...
$entity_result = array();
$passed_all = true;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '%'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '%'), array('test'), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '______________________________________________________________________________________'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '"%" \%________\% \_underscores\_ \'single quotes\' /%/ \\backslashes\\ ;semicolons;'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
/* base tests on this:
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '"quotes" %percents% _underscores_ \'single quotes\' /slashes/ \backslashes\ ;semicolons;'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
 */
$test->tests['wild'] = ($passed_all);
unset($entity_result);

// Testing wrong data wildcards...
$entity_result = array();
$passed_all = false;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '%z%'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '\\\\\\\\'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '%'), array('pickle'), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
/* base tests on this:
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('wilcard_test' => '"quotes" %percents% _underscores_ \'single quotes\' /slashes/ \backslashes\ ;semicolons;'), array(), true);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
 */
$test->tests['wr_wild'] = (!$passed_all);
unset($entity_result);

// Retrieving entity by tags and data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_data'] = ($found_match);
unset($entity_result);

// Testing wrong tags and right data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_data'] = (!$found_match);
unset($entity_result);

// Testing right tags and wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_wr_data'] = (!$found_match);
unset($entity_result);

// Testing wrong tags and wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_wr_data'] = (!$found_match);
unset($entity_result);

// Testing referenced entities.
$entity_reference_test = new entity('com_entity', 'test');
$entity_reference_test->save();
$entity_reference_guid = $entity_reference_test->guid;
$entity_test->reference = $entity_reference_test;
$entity_test->ref_array = array(0 => array('entity' => $entity_reference_test));
$entity_test->save();
unset($entity_test);
$entity_reference_test->test = 'good';
$entity_reference_test->save();
unset($entity_reference_test);
$entity_test = $config->entity_manager->get_entity($entity_guid);
$test->tests['ref'] = ($entity_test->reference->test == 'good');

// Testing referenced entity arrays.
$test->tests['ref_array'] = ($entity_test->ref_array[0]['entity']->test == 'good');

// Deleting referenced entities.
$test->tests['del_ref'] = ($entity_test->reference->delete() && is_null($entity_test->reference->guid));

// Deleting entity...
$test->tests['del'] = ($entity_test->delete() && is_null($entity_test->guid));

// Resaving entity...
$test->tests['resave'] = ($entity_test->save() && !is_null($entity_test->guid));

// Deleting entity by GUID...
$test->tests['del_guid'] = ($config->entity_manager->delete_entity_by_id($entity_test->guid));

$test->time = microtime(true) - $entity_start_time;

?>