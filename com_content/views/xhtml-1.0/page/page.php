<?php
/**
 * Provides a form for the user to edit an page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if ($this->entity->show_title)
	$this->title = htmlentities($this->entity->name);
?>
<?php
if ($this->entity->show_intro) {
	echo "{$this->entity->intro}<br />";
}
?>
<?php echo $this->entity->content; ?>