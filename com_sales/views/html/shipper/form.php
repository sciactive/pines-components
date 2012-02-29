<?php
/**
 * Provides a form for the user to edit a shipper.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Shipper' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide shipper details in this form.';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'shipper/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Email</span>
			<input class="pf-field" type="email" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Address 1</span>
			<input class="pf-field" type="text" name="address_1" size="24" value="<?php echo htmlspecialchars($this->entity->address_1); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Address 2</span>
			<input class="pf-field" type="text" name="address_2" size="24" value="<?php echo htmlspecialchars($this->entity->address_2); ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">City, State</span>
		<input class="pf-field" type="text" name="city" size="15" value="<?php echo htmlspecialchars($this->entity->city); ?>" />
		<input class="pf-field" type="text" name="state" size="2" value="<?php echo htmlspecialchars($this->entity->state); ?>" />
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Zip</span>
			<input class="pf-field" type="text" name="zip" size="24" value="<?php echo htmlspecialchars($this->entity->zip); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Corporate Phone</span>
			<input class="pf-field" type="tel" name="phone_work" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->phone_work)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Fax</span>
			<input class="pf-field" type="tel" name="fax" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->fax)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Account #</span>
			<input class="pf-field" type="text" name="account_number" size="24" value="<?php echo htmlspecialchars($this->entity->account_number); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Terms</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="terms"><?php echo htmlspecialchars($this->entity->terms); ?></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Comments</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'shipper/list'))); ?>);" value="Cancel" />
	</div>
</form>