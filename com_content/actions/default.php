<?php
/**
 * Front page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$time = time();
if ($pines->config->com_content->def_page_show_front_page) {
	$selector = array('!|',
			'strict' => array('show_front_page', false),
			'isset' => 'show_front_page'
		);
} else {
	$selector = array('&',
			'strict' => array('show_front_page', true)
		);
}
$pages = $pines->entity_manager->get_entities(
		array('class' => com_content_page),
		array('&',
			'tag' => array('com_content', 'page'),
			'data' => array('enabled', true),
			'lte' => array('publish_begin', $time)
		),
		$selector,
		array('|',
			'data' => array('publish_end', null),
			'gt' => array('publish_end', $time)
		)
	);

foreach ($pages as $cur_page) {
	$cur_page->print_page();
}

?>