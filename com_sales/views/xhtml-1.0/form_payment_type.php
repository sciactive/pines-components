<?php
/**
 * Provides a form for the user to edit a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Payment Type' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide payment type details in this form.';
?>
<form class="pform" method="post" id="payment_type_details" action="<?php echo pines_url('com_sales', 'savepaymenttype'); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Name</span>
			<input class="field" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Enabled</span>
			<input class="field" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="element">
		<label><span class="label">Change Type</span>
			<span class="note">If set, change will be given from this payment type. Usually "Cash" is the change type.</span>
			<input class="field" type="checkbox" name="change_type" size="24" value="ON"<?php echo $this->entity->change_type ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="element">
		<label><span class="label">Minimum Charge</span>
			<span class="note">The minimum charge in dollars that this payment type will accept.</span>
			<input class="field" type="text" name="minimum" size="24" value="<?php echo $this->entity->minimum; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Maximum Charge</span>
			<span class="note">The maximum charge in dollars that this payment type will accept.</span>
			<input class="field" type="text" name="maximum" size="24" value="<?php echo $this->entity->maximum; ?>" /></label>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listpaymenttypes'); ?>';" value="Cancel" />
	</div>
</form>