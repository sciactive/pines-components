<?php
/**
 * Update the entities to the new OOP style classes.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_user', 'updateentities', null, false));


// Users
$array = $config->entity_manager->get_entities(array('tags' => array('com_user', 'user'), 'class' => user));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	if ($cur->gid) {
		$cur->group = group::factory($cur->gid);
		if (is_null($cur->group->guid))
			unset($cur->group);
		unset($cur->gid);
	}
	foreach ($cur->groups as $key => &$cur2) {
		if (is_object($cur2))
			continue;
		$cur2 = group::factory($cur2);
		if (is_null($cur2->guid))
			unset($cur->groups[$key]);
	}
	unset($cur2);
	$cur->save();
}

// Groups
$array = $config->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));

if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	if (is_int($cur->parent)) {
		$cur->parent = group::factory($cur->parent);
		if (is_null($cur->parent->guid))
			unset($cur->parent);
	}
	$cur->save();
}

$query = 'ALTER TABLE table_name DROP COLUMN column_name';
$query = sprintf("ALTER TABLE `%scom_entity_entities` DROP COLUMN `parent`;",
	$config->com_mysql->prefix);
if ( !(mysql_query($query, $config->db_manager->link)) ) {
	if (function_exists('display_error'))
		display_error('Query failed: ' . mysql_error());
	return false;
}

?>