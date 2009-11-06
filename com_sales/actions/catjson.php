<?php
/**
 * Perform actions on categories, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managecategories') || !gatekeeper('com_sales/viewcategories') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'catjson', $_REQUEST, false));
	return;
}

$page->override = true;

$categories = $config->run_sales->get_category_array();

if (!isset($_REQUEST['do'])) {
    $categories_json_struct = $config->run_sales->category_json_struct($categories);
    $page->override_doc(json_encode($categories_json_struct));
}

if ( !gatekeeper('com_sales/managecategories') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'catjson', $_REQUEST, false));
	return;
}

switch ($_REQUEST['do']) {
    case 'new':
	$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
	$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
	$entity = $config->run_sales->new_category($parent_id, $name);
	if ($entity !== false) {
	    $page->override_doc(json_encode((object) array(
		"status" => true,
		"id" => $entity->guid
	    )));
	} else {
	    $page->override_doc(json_encode((object) array(
		"status" => false
	    )));
	}
	break;
    case 'rename':
	$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
	$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
	$entity = $config->run_sales->get_category($guid);
	if (is_null($entity)) {
	    $page->override_doc(json_encode((object) array(
		"status" => false
	    )));
	} else {
	    $entity->name = $name;
	    $page->override_doc(json_encode((object) array(
		"status" => $entity->save()
	    )));
	}
	break;
    case 'delete':
	$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
	$entity = $config->run_sales->get_category($guid);
	if (is_null($entity)) {
	    $page->override_doc(json_encode((object) array(
		"status" => false
	    )));
	} else {
	    $page->override_doc(json_encode((object) array(
		"status" => $config->run_sales->delete_category_recursive($entity)
	    )));
	}
	break;
    case 'move':
	$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
	$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
	$parent = is_null($parent_id) ? null : $config->run_sales->get_category($parent_id);
	$entity = $config->run_sales->get_category($guid);
	if (is_null($entity) || (!is_null($parent_id) && is_null($parent))) {
	    $page->override_doc(json_encode((object) array(
		"status" => false
	    )));
	} else {
	    $entity->parent = is_null($parent_id) ? null : $parent->guid;
	    $page->override_doc(json_encode((object) array(
		"status" => $entity->save()
	    )));
	}
	break;
    case 'copy':
	$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
	$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
	$parent = is_null($parent_id) ? null : $config->run_sales->get_category($parent_id);
	$entity = $config->run_sales->get_category($guid);
	if (is_null($entity) || (!is_null($parent_id) && is_null($parent))) {
	    $page->override_doc(json_encode((object) array(
		"status" => false
	    )));
	} else {
	    $entity->guid = null;
	    $entity->parent = is_null($parent_id) ? null : $parent->guid;
	    $page->override_doc(json_encode((object) array(
		"status" => $entity->save()
	    )));
	}
	break;
}

?>