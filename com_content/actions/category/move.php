<?php
/**
 * Move a category.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_content/editcategory') )
	punt_user(null, pines_url('com_content', 'category/edit', array('id' => $_REQUEST['id'])));

$entity = com_content_category::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested category id is not accessible.');
	pines_redirect(pines_url('com_content', 'category/list'));
	return;
}
if (!isset($entity->parent->guid)) {
	pines_error('Requested category has no parent.');
	pines_redirect(pines_url('com_content', 'category/list'));
	return;
}

$key = $entity->array_search($entity->parent->children);
if ($key !== false) {
	switch ($_REQUEST['dir']) {
		case 'up':
			if (!isset($entity->parent->children[$key - 1])) {
				pines_notice('Category is already first under its parent.');
				pines_redirect(pines_url('com_content', 'category/list'));
				return;
			} else {
				$replace = $entity->parent->children[$key - 1];
				$entity->parent->children[$key - 1] = $entity->parent->children[$key];
				$entity->parent->children[$key] = $replace;
			}
			break;
		case 'down':
		default:
			if (!isset($entity->parent->children[$key + 1])) {
				pines_notice('Category is already last under its parent.');
				pines_redirect(pines_url('com_content', 'category/list'));
				return;
			} else {
				$replace = $entity->parent->children[$key + 1];
				$entity->parent->children[$key + 1] = $entity->parent->children[$key];
				$entity->parent->children[$key] = $replace;
			}
			break;
	}
	if (!$entity->parent->save())
		pines_error('Couldn\'t save new order in parent category. Do you have permission?');
}

pines_redirect(pines_url('com_content', 'category/list'));

?>