<?php
/**
 * Prints custom content.
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

if ($pines->config->com_content->wrap_content)
	echo '<div style="position: relative;">';
echo format_content($this->icontent);
if ($pines->config->com_content->wrap_content)
	echo '</div>';

?>