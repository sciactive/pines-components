<?php
/**
 * Show a page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	$entity = com_content_page::factory((int) $_REQUEST['id']);
} else {
	$entity = $pines->entity_manager->get_entity(
			array('class' => com_content_page),
			array('&',
				'data' => array('alias', $_REQUEST['a']),
				'tag' => array('com_content', 'page')
			)
		);
}
$entity->print_page();

?>