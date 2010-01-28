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

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_entity', 'test', null, false));

$test = new module('com_entity', 'test', 'content');

if (!($config->entity_manager)) {
	$test->error = true;
	return;
}

$entity_start_time = microtime(true);
$test->time_start = $entity_start_time;

// Creating entity...
$entity_test = entity::factory();
$entity_test->add_tag('com_entity', 'test');
$test->tests['create'][0] = (is_null($entity_test->guid));
$test->tests['create'][1] = microtime(true);
$test->tests['create'][2] = 'Creating entity...';

// Saving entity...
$entity_test->name = "Entity Test ".time();
$entity_test->parent = 0;
$entity_test->test_value = 'test';
$entity_test->match_test = "Hello, my name is Edward McCheese. It is a pleasure to meet you. As you can see, I have several hats of the most pleasant nature.

This one's email address is nice_hat-wednesday+newyork@im-a-hat.hat.
This one's phone number is (555) 555-1818.
This one's zip code is 92064.";
$test->tests['save'][0] = ($entity_test->save() && !is_null($entity_test->guid));
$test->tests['save'][1] = microtime(true);
$test->tests['save'][2] = 'Saving entity...';
$entity_guid = $entity_test->guid;

// Retrieving entity by GUID...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid);
$test->tests['by_guid'][0] = ($entity_result->name == $entity_test->name);
$test->tests['by_guid'][1] = microtime(true);
$test->tests['by_guid'][2] = 'Retrieving entity by GUID...';
unset($entity_result);

// Testing wrong GUID...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid + 1);
$test->tests['wrong_guid'][0] = (empty($entity_result) ? true : ($entity_result->name != $entity_test->name));
$test->tests['wrong_guid'][1] = microtime(true);
$test->tests['wrong_guid'][2] = 'Testing wrong GUID...';
unset($entity_result);

// Retrieving entity by GUID and tags...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity(array('guid' => $entity_test->guid, 'tags' => array('com_entity', 'test')));
$test->tests['guid_tags'][0] = ($entity_result->name == $entity_test->name);
$test->tests['guid_tags'][1] = microtime(true);
$test->tests['guid_tags'][2] = 'Retrieving entity by GUID and tags...';
unset($entity_result);

// Testing GUID and wrong tags...
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity(array('guid' => $entity_test->guid, 'tags' => array('com_entity', 'pickles')));
$test->tests['guid_wr_tags'][0] = empty($entity_result);
$test->tests['guid_wr_tags'][1] = microtime(true);
$test->tests['guid_wr_tags'][2] = 'Testing GUID and wrong tags...';
unset($entity_result);

// Retrieving entity by parent...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('parent' => $entity_test->parent));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['parent'][0] = ($found_match);
$test->tests['parent'][1] = microtime(true);
$test->tests['parent'][2] = 'Retrieving entity by parent...';
unset($entity_result);

// Testing wrong parent...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('parent' => 1));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_parent'][0] = (!$found_match);
$test->tests['wr_parent'][1] = microtime(true);
$test->tests['wr_parent'][2] = 'Testing wrong parent...';
unset($entity_result);

// Retrieving entity by tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('com_entity', 'test')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags'][0] = ($found_match);
$test->tests['tags'][1] = microtime(true);
$test->tests['tags'][2] = 'Retrieving entity by tags...';
unset($entity_result);

// Testing wrong tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('pickles')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags'][0] = (!$found_match);
$test->tests['wr_tags'][1] = microtime(true);
$test->tests['wr_tags'][2] = 'Testing wrong tags...';
unset($entity_result);

// Retrieving entity by tags exclusively...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('com_entity', 'test')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_exc'][0] = ($found_match);
$test->tests['tags_exc'][1] = microtime(true);
$test->tests['tags_exc'][2] = 'Retrieving entity by tags exclusively...';
unset($entity_result);

// Testing wrong exclusive tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('pickles')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_exc'][0] = (!$found_match);
$test->tests['wr_tags_exc'][1] = microtime(true);
$test->tests['wr_tags_exc'][2] = 'Testing wrong exclusive tags...';
unset($entity_result);

// Retrieving entity by tags inclusively...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags_i' => array('pickles', 'test', 'barbecue')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_inc'][0] = ($found_match);
$test->tests['tags_inc'][1] = microtime(true);
$test->tests['tags_inc'][2] = 'Retrieving entity by tags inclusively...';
unset($entity_result);

// Testing wrong inclusive tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags_i' => array('pickles', 'barbecue')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_inc'][0] = (!$found_match);
$test->tests['wr_tags_inc'][1] = microtime(true);
$test->tests['wr_tags_inc'][2] = 'Testing wrong inclusive tags...';
unset($entity_result);

// Retrieving entity by mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('com_entity'), 'tags_i' => array('pickles', 'test', 'barbecue')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['mixed_tags'][0] = ($found_match);
$test->tests['mixed_tags'][1] = microtime(true);
$test->tests['mixed_tags'][2] = 'Retrieving entity by mixed tags...';
unset($entity_result);

// Testing wrong inclusive mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('com_entity'), 'tags_i' => array('pickles', 'barbecue')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_inc_mx_tags'][0] = (!$found_match);
$test->tests['wr_inc_mx_tags'][1] = microtime(true);
$test->tests['wr_inc_mx_tags'][2] = 'Testing wrong inclusive mixed tags...';
unset($entity_result);

// Testing wrong exclusive mixed tags...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('tags' => array('pickles'), 'tags_i' => array('test', 'barbecue')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_exc_mx_tags'][0] = (!$found_match);
$test->tests['wr_exc_mx_tags'][1] = microtime(true);
$test->tests['wr_exc_mx_tags'][2] = 'Testing wrong exclusive mixed tags...';
unset($entity_result);

// Retrieving entity by data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'test')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['data'][0] = ($found_match);
$test->tests['data'][1] = microtime(true);
$test->tests['data'][2] = 'Retrieving entity by data...';
unset($entity_result);

// Testing wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'pickles')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_data'][0] = (!$found_match);
$test->tests['wr_data'][1] = microtime(true);
$test->tests['wr_data'][2] = 'Testing wrong data...';
unset($entity_result);

// Retrieving entity by regex match...
$entity_result = array();
$passed_all = true;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/.*/'))); // anything
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/Edward McCheese/'), 'tags' => array('com_entity', 'test'))); // a substring
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match_i' => array('test_value' => '/\d/', 'match_test' => '/Edward McCheese/'), 'tags' => array('com_entity', 'test'))); // inclusive test
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/\b[\w\-+]+@[\w-]+\.\w{2,4}\b/'), 'tags' => array('com_entity', 'test'))); // a simple email
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/\(\d{3}\)\s\d{3}-\d{4}/'), 'tags' => array('com_entity', 'test'))); // a phone number
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all && $found_match;
$test->tests['match'][0] = ($passed_all);
$test->tests['match'][1] = microtime(true);
$test->tests['match'][2] = 'Retrieving entity by regex match...';
unset($entity_result);

// Testing wrong regex match...
$entity_result = array();
$passed_all = false;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/Q/')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match' => array('match_test' => '/.*/'), 'tags' => array('pickle')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('match_i' => array('test_value' => '/\d/', 'match_test' => '/,,/')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$passed_all = $passed_all || $found_match;
$test->tests['wr_match'][0] = (!$passed_all);
$test->tests['wr_match'][1] = microtime(true);
$test->tests['wr_match'][2] = 'Testing wrong regex match...';
unset($entity_result);

// Retrieving entity by tags and data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'test'), 'tags' => array('com_entity', 'test')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_data'][0] = ($found_match);
$test->tests['tags_data'][1] = microtime(true);
$test->tests['tags_data'][2] = 'Retrieving entity by tags and data...';
unset($entity_result);

// Testing wrong tags and right data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'test'), 'tags' => array('pickles')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_data'][0] = (!$found_match);
$test->tests['wr_tags_data'][1] = microtime(true);
$test->tests['wr_tags_data'][2] = 'Testing wrong tags and right data...';
unset($entity_result);

// Testing right tags and wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'pickles'), 'tags' => array('com_entity', 'test')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['tags_wr_data'][0] = (!$found_match);
$test->tests['tags_wr_data'][1] = microtime(true);
$test->tests['tags_wr_data'][2] = 'Testing right tags and wrong data...';
unset($entity_result);

// Testing wrong tags and wrong data...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('data' => array('test_value' => 'pickles'), 'tags' => array('pickles')));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['wr_tags_wr_data'][0] = (!$found_match);
$test->tests['wr_tags_wr_data'][1] = microtime(true);
$test->tests['wr_tags_wr_data'][2] = 'Testing wrong tags and wrong data...';
unset($entity_result);

// Testing referenced entities...
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
$test->tests['ref'][0] = ($entity_test->reference->test == 'good');
$test->tests['ref'][1] = microtime(true);
$test->tests['ref'][2] = 'Testing referenced entities...';

// Testing referenced entity arrays...
$test->tests['ref_array'][0] = ($entity_test->ref_array[0]['entity']->test == 'good');
$test->tests['ref_array'][1] = microtime(true);
$test->tests['ref_array'][2] = 'Testing referenced entity arrays...';

// Retrieving entity by reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref' => array('reference' => $entity_reference_guid)));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_get'][0] = ($found_match);
$test->tests['ref_get'][1] = microtime(true);
$test->tests['ref_get'][2] = 'Retrieving entity by reference...';
unset($entity_result);

// Testing wrong reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref' => array('reference' => array($entity_reference_guid, $entity_reference_guid + 1))));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_wr_get'][0] = (!$found_match);
$test->tests['ref_wr_get'][1] = microtime(true);
$test->tests['ref_wr_get'][2] = 'Testing wrong reference...';
unset($entity_result);

// Testing non-existent reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref' => array('pickle' => $entity_reference_guid)));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_ne_get'][0] = (!$found_match);
$test->tests['ref_ne_get'][1] = microtime(true);
$test->tests['ref_ne_get'][2] = 'Testing non-existent reference...';
unset($entity_result);

// Retrieving entity by inclusive reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref_i' => array('reference' => array($entity_reference_guid, $entity_reference_guid + 1))));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_i_get'][0] = ($found_match);
$test->tests['ref_i_get'][1] = microtime(true);
$test->tests['ref_i_get'][2] = 'Retrieving entity by inclusive reference...';
unset($entity_result);

// Testing wrong inclusive reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref_i' => array('reference' => array($entity_reference_guid + 2, $entity_reference_guid + 1))));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_wr_i_get'][0] = (!$found_match);
$test->tests['ref_wr_i_get'][1] = microtime(true);
$test->tests['ref_wr_i_get'][2] = 'Testing wrong inclusive reference...';
unset($entity_result);

// Retrieving entity by array reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref' => array('ref_array' => $entity_reference_guid)));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_a_get'][0] = ($found_match);
$test->tests['ref_a_get'][1] = microtime(true);
$test->tests['ref_a_get'][2] = 'Retrieving entity by array reference...';
unset($entity_result);

// Testing wrong array reference...
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities(array('ref' => array('ref_array' => array($entity_reference_guid, $entity_reference_guid + 1))));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
$test->tests['ref_wr_a_get'][0] = (!$found_match);
$test->tests['ref_wr_a_get'][1] = microtime(true);
$test->tests['ref_wr_a_get'][2] = 'Testing wrong array reference...';
unset($entity_result);

// Deleting referenced entities...
$test->tests['del_ref'][0] = ($entity_test->reference->delete() && is_null($entity_test->reference->guid));
$test->tests['del_ref'][1] = microtime(true);
$test->tests['del_ref'][2] = 'Deleting referenced entities...';

// Deleting entity...
$test->tests['del'][0] = ($entity_test->delete() && is_null($entity_test->guid));
$test->tests['del'][1] = microtime(true);
$test->tests['del'][2] = 'Deleting entity...';

// Resaving entity...
$test->tests['resave'][0] = ($entity_test->save() && !is_null($entity_test->guid));
$test->tests['resave'][1] = microtime(true);
$test->tests['resave'][2] = 'Resaving entity...';

// Deleting entity by GUID...
$test->tests['del_guid'][0] = ($config->entity_manager->delete_entity_by_id($entity_test->guid));
$test->tests['del_guid'][1] = microtime(true);
$test->tests['del_guid'][2] = 'Deleting entity by GUID...';

$test->time_end = microtime(true);

?>