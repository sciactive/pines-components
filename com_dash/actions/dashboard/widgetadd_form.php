<?php
/**
 * Show a form list of widgets.
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

$module = new module('com_dash', 'dashboard/widget_add_form');
$module->widgets = $pines->com_dash->widget_types();
foreach ($module->widgets as $cur_component => $cur_widget_set) {
	foreach ($cur_widget_set as $cur_widget_name => $cur_widget) {
		// Check its conditions.
		foreach ((array) $cur_widget['widget']['depends'] as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				unset($module->widgets[$cur_component][$cur_widget_name]);
				if (!$module->widgets[$cur_component])
					unset($module->widgets[$cur_component]);
			}
		}
	}
}

$content = $module->render();
$pines->page->override_doc($content);

?>