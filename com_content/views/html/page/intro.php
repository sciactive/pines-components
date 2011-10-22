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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->entity))
	$this->entity = com_content_page::factory((int) $this->id);

if ($this->entity->get_option('show_title'))
	$this->title = '<a href="'.htmlspecialchars(pines_url('com_content', 'page', array('a' => $this->entity->alias))).'">'.htmlspecialchars($this->entity->name).'</a>';

if ($this->entity->get_option('show_author_info'))
	$this->note = htmlspecialchars('Posted by '.$this->entity->user->name.' on '.format_date($this->entity->p_cdate, 'date_short'));

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