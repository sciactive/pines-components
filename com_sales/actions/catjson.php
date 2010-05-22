<?php
/**
 * Perform actions on categories, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managecategories') || !gatekeeper('com_sales/viewcategories') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'catjson', $_REQUEST));

$pines->page->override = true;

$categories = $pines->com_sales->get_category_array();

if (!isset($_REQUEST['do'])) {
	$categories_json_struct = $pines->com_jstree->entity_json_struct($categories);
	$pines->page->override_doc(json_encode($categories_json_struct));
}

if ( !gatekeeper('com_sales/managecategories') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'catjson', $_REQUEST));

switch ($_REQUEST['do']) {
	case 'new':
		$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
		$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
		$entity = $pines->com_sales->new_category($parent_id, $name);
		if ($entity !== false) {
			$pines->page->override_doc(json_encode((object) array(
				"status" => true,
				"id" => $entity->guid
			)));
		} else {
			$pines->page->override_doc(json_encode((object) array(
				"status" => false
			)));
		}
		break;
	case 'rename':
		$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
		$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
		$entity = $pines->com_sales->get_category($guid);
		if (!isset($entity)) {
			$pines->page->override_doc(json_encode((object) array(
				"status" => false
			)));
		} else {
			$entity->name = $name;
			$pines->page->override_doc(json_encode((object) array(
				"status" => $entity->save()
			)));
		}
		break;
	case 'delete':
		$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
		$entity = $pines->com_sales->get_category($guid);
		if (!isset($entity)) {
			$pines->page->override_doc(json_encode((object) array(
				"status" => false
			)));
		} else {
			$pines->page->override_doc(json_encode((object) array(
				"status" => $pines->com_sales->delete_category_recursive($entity)
			)));
		}
		break;
	case 'move':
		$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
		$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
		$parent = !isset($parent_id) ? null : $pines->com_sales->get_category($parent_id);
		$entity = $pines->com_sales->get_category($guid);
		if (!isset($entity) || (isset($parent_id) && !isset($parent))) {
			$pines->page->override_doc(json_encode((object) array(
				"status" => false
			)));
		} else {
			$entity->parent = !isset($parent_id) ? null : $parent->guid;
			$pines->page->override_doc(json_encode((object) array(
				"status" => $entity->save()
			)));
		}
		break;
	case 'copy':
		$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
		$parent_id = (isset($_REQUEST['parent']) && $_REQUEST['parent'] != 'null' ? intval($_REQUEST['parent']) : null);
		$parent = !isset($parent_id) ? null : $pines->com_sales->get_category($parent_id);
		$entity = $pines->com_sales->get_category($guid);
		if (!isset($entity) || (isset($parent_id) && !isset($parent))) {
			$pines->page->override_doc(json_encode((object) array(
				"status" => false
			)));
		} else {
			$entity->guid = null;
			$entity->parent = !isset($parent_id) ? null : $parent->guid;
			$pines->page->override_doc(json_encode((object) array(
				"status" => $entity->save()
			)));
		}
		break;
}

?>