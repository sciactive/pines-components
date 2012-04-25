<?php
/**
 * Load Inuit.
 *
 * @package Components
 * @subpackage inuitcss
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// Load the CSS on the currently in construction page.
if ($pines->config->com_inuitcss->always_load)
	$pines->com_inuitcss->load();

// Tell any editor to load the CSS in the edit view.
if ($pines->editor) {
	$pines->editor->add_css($pines->config->location.'components/com_inuitcss/includes/core/css/inuit.css');
	$pines->editor->add_css($pines->config->location.'components/com_inuitcss/includes/'.clean_filename($pines->config->com_inuitcss->grid_layout));
}

?>