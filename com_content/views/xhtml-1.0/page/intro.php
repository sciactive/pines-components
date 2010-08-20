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
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->entity))
	$this->entity = com_content_page::factory((int) $this->id);

if ($this->entity->show_title)
	$this->title = '<a href="'.htmlentities(pines_url('com_content', 'page', array('a' => $this->entity->alias))).'">'.htmlentities($this->entity->name).'</a>';

echo $this->entity->intro;

?>