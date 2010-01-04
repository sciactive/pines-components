<?php
/**
 * Provides a sidebar with a link to the customer form.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Account';
?>
<div class="pform">
	<div class="element">
		<span class="label" style="width: 100px;">Name: </span>
		<span class="field"><?php echo $this->entity->name; ?></span>
	</div>
	<div class="element">
		<span class="label" style="width: 100px;">Current Points: </span>
		<span class="field"><?php echo $this->entity->com_customer->points; ?></span>
	</div>
	<div class="element">
		<span class="label" style="width: 100px;">Peak Points: </span>
		<span class="field"><?php echo $this->entity->com_customer->peak_points; ?></span>
	</div>
	<div class="element">
		<span class="label" style="width: 100px;">All Time Points: </span>
		<span class="field"><?php echo $this->entity->com_customer->total_points; ?></span>
	</div>
	<div class="element buttons" style="padding-left: 10px;">
		<input class="button ui-state-default ui-corner-all" type="button" onclick="if (confirm('Leaving this page will lose any changes. Are you sure?')) window.location='<?php echo pines_url('com_customer', 'editcustomer', array('id' => $this->entity->guid)); ?>';" value="Edit Account" />
	</div>
</div>