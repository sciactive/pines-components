<?php
/**
 * Provides a form for the user to edit a vendor.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Vendor' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide vendor details in this form.';
?>
<form class="pform" method="post" id="vendor_details" action="<?php echo pines_url('com_sales', 'savevendor'); ?>">
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
		<label><span class="label">Email</span>
			<input class="field" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Address 1</span>
			<input class="field" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Address 2</span>
			<input class="field" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
	</div>
	<div class="element">
		<span class="label">City, State</span>
		<input class="field" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
		<input class="field" type="text" name="state" size="2" value="<?php echo $this->entity->state; ?>" />
	</div>
	<div class="element">
		<label><span class="label">Zip</span>
			<input class="field" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Corporate Phone</span>
			<input class="field" type="text" name="phone_work" size="24" value="<?php echo $this->entity->phone_work; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Fax</span>
			<input class="field" type="text" name="fax" size="24" value="<?php echo $this->entity->fax; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Account #</span>
			<input class="field" type="text" name="account_number" size="24" value="<?php echo $this->entity->account_number; ?>" /></label>
	</div>
	<fieldset class="group">
		<br />
		<legend>Client Details</legend>
		<div class="element">
			<label><span class="label">Username</span>
				<input class="field" type="text" name="client_username" size="24" value="<?php echo $this->entity->client_username; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Password</span>
				<input class="field" type="text" name="client_password" size="24" value="<?php echo $this->entity->client_password; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Rep Name</span>
				<input class="field" type="text" name="client_rep_name" size="24" value="<?php echo $this->entity->client_rep_name; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Email</span>
				<input class="field" type="text" name="client_email" size="24" value="<?php echo $this->entity->client_email; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Web Address</span>
				<input class="field" type="text" name="client_web_address" size="24" value="<?php echo $this->entity->client_web_address; ?>" /></label>
		</div>
		<br />
	</fieldset>
	<fieldset class="group">
		<br />
		<legend>Online Ordering</legend>
		<div class="element">
			<label><span class="label">Web Address</span>
				<input class="field" type="text" name="online_web_address" size="24" value="<?php echo $this->entity->online_web_address; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Customer ID</span>
				<input class="field" type="text" name="online_customer_id" size="24" value="<?php echo $this->entity->online_customer_id; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Username</span>
				<input class="field" type="text" name="online_username" size="24" value="<?php echo $this->entity->online_username; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Password</span>
				<input class="field" type="text" name="online_password" size="24" value="<?php echo $this->entity->online_password; ?>" /></label>
		</div>
		<br />
	</fieldset>
	<div class="element full_width">
		<label><span class="label">Terms</span>
			<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="terms"><?php echo $this->entity->terms; ?></textarea></span></label>
	</div>
	<div class="element full_width">
		<label><span class="label">Comments</span>
			<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></span></label>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listvendors'); ?>';" value="Cancel" />
	</div>
</form>