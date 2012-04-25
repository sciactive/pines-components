<?php
/**
 * Load Bootstrap.
 *
 * @package Components\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// Load the scripts on the currently in construction page.
if ($pines->config->com_bootstrap->always_load)
	$pines->com_bootstrap->load();

// Tell any editor to load the CSS in the edit view.
if ($pines->editor)
	$pines->editor->add_css($pines->config->location.'components/com_bootstrap/includes/themes/'.clean_filename($pines->config->com_bootstrap->theme).'/css/bootstrap.css');

?>