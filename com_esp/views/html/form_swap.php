<?php
/**
 * Display a form to swap inventory on an ESP.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element pf-heading">
		<h1><?php echo htmlspecialchars($this->entity->item['entity']->name); ?></h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Item Serial</span>
		<span class="pf-field"><?php echo htmlspecialchars($this->entity->item['serial']); ?></span>
	</div>
	<div class="pf-element pf-heading">
		<h1>New Item</h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Item Serial</span>
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="new_serial" value="" />
	</div>
</form>