<?php
/**
 * Provides a form for the user to edit a vendor.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Vendor' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide vendor details in this form.';
$pines->uploader->load();
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'vendor/save')); ?>">
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
		<label><span class="pf-label">Email</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="email" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Address 1</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_1" size="24" value="<?php echo htmlspecialchars($this->entity->address_1); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Address 2</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_2" size="24" value="<?php echo htmlspecialchars($this->entity->address_2); ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">City, State</span>
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="city" size="15" value="<?php echo htmlspecialchars($this->entity->city); ?>" />
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="state" size="2" value="<?php echo htmlspecialchars($this->entity->state); ?>" />
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Zip</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="zip" size="24" value="<?php echo htmlspecialchars($this->entity->zip); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Corporate Phone</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone_work" size="24" value="<?php echo format_phone($this->entity->phone_work); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Fax</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Account #</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="account_number" size="24" value="<?php echo htmlspecialchars($this->entity->account_number); ?>" /></label>
	</div>
	<?php if (isset($this->entity->logo)) { ?>
	<div class="pf-element">
		<span class="pf-label">Company Logo</span>
		<div class="pf-group">
			<span class="pf-field"><img src="<?php echo htmlspecialchars($this->entity->get_logo()); ?>" alt="Group Logo" /></span><br />
			<label><span class="pf-field"><input class="pf-field ui-widget-content ui-corner-all" type="checkbox" name="remove_logo" value="ON" />Remove this logo.</span></label>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label"><?php echo isset($this->entity->logo) ? 'Change Logo' : 'Company Logo'; ?></span>
			<input class="pf-field ui-widget-content ui-corner-all puploader" type="text" name="logo" /></label>
	</div>
	<br />
	<fieldset class="pf-group">
		<legend>Client Details</legend>
		<div class="pf-element">
			<label><span class="pf-label">Username</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="client_username" size="24" value="<?php echo htmlspecialchars($this->entity->client_username); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Password</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="client_password" size="24" value="<?php echo htmlspecialchars($this->entity->client_password); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Rep Name</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="client_rep_name" size="24" value="<?php echo htmlspecialchars($this->entity->client_rep_name); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Email</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="email" name="client_email" size="24" value="<?php echo htmlspecialchars($this->entity->client_email); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Web Address</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="url" name="client_web_address" size="24" value="<?php echo htmlspecialchars($this->entity->client_web_address); ?>" /></label>
		</div>
		<br />
	</fieldset>
	<br />
	<fieldset class="pf-group">
		<legend>Online Ordering</legend>
		<div class="pf-element">
			<label><span class="pf-label">Web Address</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="url" name="online_web_address" size="24" value="<?php echo htmlspecialchars($this->entity->online_web_address); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Customer ID</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="online_customer_id" size="24" value="<?php echo htmlspecialchars($this->entity->online_customer_id); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Username</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="online_username" size="24" value="<?php echo htmlspecialchars($this->entity->online_username); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Password</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="online_password" size="24" value="<?php echo htmlspecialchars($this->entity->online_password); ?>" /></label>
		</div>
		<br />
	</fieldset>
	<br />
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Terms</span>
			<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="terms"><?php echo $this->entity->terms; ?></textarea></span></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Comments</span>
			<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></span></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'vendor/list')); ?>');" value="Cancel" />
	</div>
</form>