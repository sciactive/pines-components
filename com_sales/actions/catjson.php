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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listcustomers', null, false));
	return;
}

$page->override = true;

$categories = $config->run_sales->get_category_array();

if (!isset($_REQUEST['do'])) {
    $categories_json_struct = $config->run_sales->category_json_struct($categories);
    $page->override_doc(json_encode($categories_json_struct));
}

switch ($_REQUEST['do']) {
    case 'new':
	$parent = (isset($_REQUEST['parent']) ? intval($_REQUEST['parent']) : null);
	$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
	if ($config->run_sales->new_category($parent, $name)) {
	    $page->override_doc(json_encode(true));
	} else {
	    $page->override_doc(json_encode(false));
	}
	break;
    case 'rename':
	$guid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);
	$name = (isset($_REQUEST['name']) ? $_REQUEST['name'] : 'untitled');
	$entity = $config->run_sales->get_category($guid);
	if (is_null($entity)) {
	    $page->override_doc(json_encode(false));
	} else {
	    $entity->name = $name;
	    $page->override_doc(json_encode($entity->save()));
	}
	break;
    case 'delete':
	break;
    case 'move':
	break;
    case 'copy':
	break;
}

?>