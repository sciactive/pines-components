<?php
/**
 * Provides a form for the user to edit a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Payment Type' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide payment type details in this form.';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'paymenttype/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php if ($pines->config->com_sales->com_storefront) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Enabled in Storefront</span>
			<span class="pf-note">Check to make this a web storefront payment type.</span>
			<span class="pf-note">Uncheck "Enabled" to <em>only</em> show this in the web storefront.</span>
			<input class="pf-field" type="checkbox" name="storefront" value="ON"<?php echo $this->entity->storefront ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Kick Drawer</span>
			<span class="pf-note">If set, when this payment type is used, the cash drawer will be kicked open.</span>
			<input class="pf-field" type="checkbox" name="kick_drawer" value="ON"<?php echo $this->entity->kick_drawer ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Change Type</span>
			<span class="pf-note">If set, change will be given from this payment type. Usually "Cash" is the change type.</span>
			<input class="pf-field" type="checkbox" name="change_type" value="ON"<?php echo $this->entity->change_type ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Minimum Charge</span>
			<span class="pf-note">The minimum charge in dollars that this payment type will accept.</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="minimum" size="24" value="<?php echo htmlspecialchars($this->entity->minimum); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Maximum Charge</span>
			<span class="pf-note">The maximum charge in dollars that this payment type will accept.</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="maximum" size="24" value="<?php echo htmlspecialchars($this->entity->maximum); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Allow Return Payment</span>
			<span class="pf-note">If set, a negative payment on a return can be used to charge a return fee.</span>
			<input class="pf-field" type="checkbox" name="allow_return" value="ON"<?php echo $this->entity->allow_return ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Processing Type</span>
			<span class="pf-note">This will determine how the payment is approved and processed.</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="processing_type" size="6">
				<?php foreach ($this->processing_types as $cur_type) { ?>
				<option value="<?php echo htmlspecialchars($cur_type['name']); ?>" title="<?php echo htmlspecialchars($cur_type['description']); ?>"<?php echo $this->entity->processing_type == $cur_type['name'] ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_type['cname']); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'paymenttype/list')); ?>');" value="Cancel" />
	</div>
</form>