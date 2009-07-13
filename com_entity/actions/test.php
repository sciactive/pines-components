<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
	$config->user_manager->punt_user("You are not logged in.", $config->template->url('com_entity', 'test', null, false));
	return;
}

$com_entity_test = new module('com_entity', 'test', 'content');
$com_entity_test->title = 'Entity Manager Tester';

$com_entity_test->content("This entity manager tester will test the current entity manager for required functionality. If the entity manager does not successfully pass any part of the test, it is not considered to be a compatible entity manager. Please note that this test does not test all aspects of an entity manager, and even if it passes, it may still have bugs.<br /><br /><pre>Test is starting...");

if (!($config->entity_manager)) {
	$com_entity_test->content("<br />Error: Either there is no entity manager installed, or it hasn't registered itself as the system's entity manager! Test cannot continue!");
	return;
}

$com_entity_test->content("<ol>");

$com_entity_test->content(str_pad('<li>Creating entity... ', 45));
$entity_test = new entity;
if (is_null($entity_test->guid)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content(str_pad('<li>Saving entity... ', 45));
$entity_test->name = "Entity Test ".time();
$entity_test->parent = 0;
$entity_test->add_tag('com_entity', 'test');
$entity_test->test_value = 'test';
if ($entity_test->save() && !is_null($entity_test->guid)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content(str_pad('<li>Checking entity\'s has_tag method... ', 45));
if ($entity_test->has_tag('com_entity', 'test') && !$entity_test->has_tag('pickles')) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content(str_pad('<li>Retrieving entity by GUID... ', 45));
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid);
if ($entity_result->name == $entity_test->name) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong GUID... ', 45));
$entity_result = new entity;
$entity_result = $config->entity_manager->get_entity($entity_test->guid + 1);
if (empty($entity_result) ? true : ($entity_result->name != $entity_test->name)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by parent... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent($entity_test->parent);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong parent... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_parent(1);
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by tags exclusively... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('com_entity', 'test');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong exclusive tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_exclusive('pickles');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by tags inclusively... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'test', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong inclusive tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_inclusive('pickles', 'barbecue');
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by mixed tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong inclusive mixed tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('com_entity'), array('pickles', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong exclusive mixed tags... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_tags_mixed(array('pickles'), array('test', 'barbecue'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Retrieving entity by tags and data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if ($found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong tags and right data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'test'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing right tags and wrong data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('com_entity', 'test'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Testing wrong tags and wrong data... ', 45));
$entity_result = array();
$found_match = false;
$entity_result = $config->entity_manager->get_entities_by_data(array('test_value' => 'pickles'), array('pickles'));
foreach ($entity_result as $cur_entity) {
	if ($cur_entity->name == $entity_test->name)
		$found_match = true;
}
if (!$found_match) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}
unset($entity_result);

$com_entity_test->content(str_pad('<li>Deleting entity... ', 45));
if ($entity_test->delete() && is_null($entity_test->guid)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content(str_pad('<li>Resaving entity... ', 45));
if ($entity_test->save() && !is_null($entity_test->guid)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content(str_pad('<li>Deleting entity by GUID... ', 45));
if ($config->entity_manager->delete_entity_by_id($entity_test->guid)) {
	$com_entity_test->content("<span style=\"color: green;\">[PASS]</span></li>");
} else {
	$com_entity_test->content("<span style=\"color: red;\">[FAIL]</span></li>");
}

$com_entity_test->content("</ol>The test is now complete.</pre>");

?>
