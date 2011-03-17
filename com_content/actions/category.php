<?php
/**
 * Show a category's pages.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	$entity = com_content_category::factory((int) $_REQUEST['id']);
} else {
	$entity = $pines->entity_manager->get_entity(
			array('class' => com_content_category),
			array('&',
				'tag' => array('com_content', 'category'),
				'data' => array('alias', $_REQUEST['a'])
			)
		);
}

if (!isset($entity->guid) || !$entity->ready())
	return 'error_404';

// Page title.
$pines->page->title_pre("$entity->name - ");

if ($entity->show_breadcrumbs) {
	$module = new module('com_content', 'breadcrumb', 'breadcrumbs');
	$module->entity = $entity;
}

$entity->print_category();

?>