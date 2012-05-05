<?php
/**
 * Displays category listing.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->classes[] = 'content_category';

if (!isset($this->entity))
	$this->entity = com_content_category::factory((int) $this->id);

if (!isset($this->entity->guid)) {
	echo 'No category is specified or the category is inaccessible.';
	return;
}

// Custom head code.
if ($this->entity->enable_custom_head && $pines->config->com_content->custom_head) {
	$head = new module('system', 'null', 'head');
	$head->content($this->entity->custom_head);
}

if ($this->entity->get_option('show_title'))
	$this->title = htmlspecialchars($this->entity->name);

$this->show_title = $this->entity->get_option('show_title');

if (!empty($this->entity->intro)) {
	if ($pines->config->com_content->wrap_pages)
		echo '<div style="position: relative;">';
	echo format_content($this->entity->intro);
	if ($pines->config->com_content->wrap_pages)
		echo '</div>';
}

if ((!$this->show_title || (empty($this->title) && empty($this->note))) && empty($this->entity->intro))
	$this->detach();

?>