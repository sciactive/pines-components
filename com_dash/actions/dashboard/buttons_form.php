<?php
/**
 * Show a form list of buttons.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/dash') || !gatekeeper('com_dash/editdash') )
	punt_user(null, pines_url('com_dash'));

$pines->page->override = true;

$module = new module('com_dash', 'dashboard/buttons_form');
$module->current_buttons = $_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['buttons'];
$module->buttons_size = $_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['buttons_size'];
$module->buttons = $pines->com_dash->button_types();
foreach ($module->buttons as $cur_component => $cur_button_set) {
	foreach ($cur_button_set as $cur_button_name => $cur_button) {
		// Check its conditions.
		foreach ((array) $cur_button['depends'] as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				unset($module->buttons[$cur_component][$cur_button_name]);
				if (!$module->buttons[$cur_component])
					unset($module->buttons[$cur_component]);
			}
		}
	}
}

$content = $module->render();
$pines->page->override_doc($content);

?>