<?php
/**
 * Get pages in a category, returning JSON.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// TODO: Gatekeeper checks?

$pines->page->override = true;
header('Content-Type: application/json');

$category = com_content_category::factory((int) $_REQUEST['id']);

if (!isset($category->guid)) {
	$pines->page->override_doc(json_encode(array()));
	return;
}

$return = array();
foreach ($category->pages as $page) {
	if (!$page->enabled)
		continue;
	
	$json_struct = (object) array(
		'guid' => $page->guid,
		'name' => $page->name
	);

	$return[] = $json_struct;
}

$pines->page->override_doc(json_encode($return));

?>