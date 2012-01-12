<?php
/**
 * Displays page intro.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->entity))
	$this->entity = com_content_page::factory((int) $this->id);

if ($this->entity->get_option('show_title'))
	$this->title = '<span class="page_title"><a href="'.htmlspecialchars(pines_url('com_content', 'page', array('a' => $this->entity->alias))).'">'.htmlspecialchars($this->entity->name).'</a></span>';

if ($this->entity->get_option('show_author_info'))
	$this->note = '<span class="page_info"><span class="page_posted_by_text">Posted by </span><span class="page_author">'.htmlspecialchars($this->entity->user->name).'</span><span class="page_posted_on_text"> on </span><span class="page_date">'.htmlspecialchars(format_date($this->entity->p_cdate, 'date_short')).'</span></span>';

if ($pines->config->com_content->wrap_pages)
	echo '<div style="position: relative;">';
echo format_content($this->entity->intro);
if ($pines->config->com_content->wrap_pages)
	echo '</div>';

if ($this->entity->get_option('show_content_in_list')) {
	if ($pines->config->com_content->wrap_pages)
		echo '<div style="position: relative;">';
	echo format_content($this->entity->content);
	if ($pines->config->com_content->wrap_pages)
		echo '</div>';
}

?>