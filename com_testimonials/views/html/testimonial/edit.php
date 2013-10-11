<?php
/**
 * Provides a form for the user to edit or create a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Testimonial' : 'Editing Testimonial ['.htmlspecialchars($this->entity->id).'] for '.htmlspecialchars($this->entity->customer->name);
$this->note = 'Provide testimonial details in this form.';
$pines->com_customer->load_customer_select();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_testimonials', 'testimonial/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			// Customer Autocomplete.
			$("#p_muid_customer").customerselect();
		});
	</script>
	<?php var_dump($this->entity->quotefeedback); if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>Customer: <span><?php echo htmlspecialchars("{$this->entity->customer->name} [{$this->entity->customer->username}]"); ?></span></div>
		<div>User: <span><?php echo ($this->entity->user) ? htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]") : 'See Customer.'; ?></span></div>
		<div>Group: <span><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Customer</h3>
	</div>
	<div class="pf-element">
		<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
		<input id="p_muid_customer" class="pf-field" type="text" name="customer" value="<?php echo (isset($this->entity->customer->guid) ? htmlspecialchars("{$this->entity->customer->guid}: {$this->entity->customer->name}") : ''); ?>"/>
	</div>
	<div class="pf-element pf-heading">
		<h3>Feedback</h3>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Customer Feedback
			<span class="pf-note">Testimonial uses this feedback as default if it's approved.</span>
		</span>
		<textarea class="pf-field" style="width:60%; min-height: 125px;" name="feedback"><?php echo isset($this->entity->feedback) ? htmlspecialchars($this->entity->feedback) : ''; ?></textarea>
	</div>
	<?php if (gatekeeper('com_testimonials/quotetestimonials')) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Quote Feedback for Testimonial 
				<span class="pf-note">If desired, you can quote part of the feedback for the testimonial instead.</span>
			</span>
			<textarea class="pf-field" style="width:60%; min-height: 125px;" name="quotefeedback"><?php echo isset($this->entity->quotefeedback) ? htmlspecialchars($this->entity->quotefeedback) : ''; ?></textarea>
		</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Allow us to Share Feedback</span>
		<input class="pf-field" type="checkbox" name="share" value="ON" <?php echo ($this->entity->share) ? 'checked="checked"' : ''; ?>/>
	</div>
	<div class="pf-element">
		<span class="pf-label">Share Anonymously</span>
		<input class="pf-field" type="checkbox" name="anon" value="ON" <?php echo ($this->entity->anon) ? 'checked="checked"' : ''; ?>/>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="type" value="form" />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_testimonials', 'testimonial/list'))); ?>);" value="Cancel" />
	</div>
</form>