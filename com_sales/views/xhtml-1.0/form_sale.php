<?php
/**
 * Provides a form for the user to edit a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'New Sale' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Use this form to process a sale.';
?>
<form class="pform" method="post" id="sale_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
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
		<label><span class="label">Customer</span>
			<input class="field" type="text" name="customer" size="20" value="<?php echo $this->entity->customer; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Items</span>
			<input class="field" type="text" name="items" size="20" value="<?php echo $this->entity->items; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Comments</span>
			<textarea rows="3" cols="35" class="field" name="comments" style="width: 100%;"><?php echo $this->entity->comments; ?></textarea></label>
	</div>
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listsales'); ?>';" value="Cancel" />
	</div>
</form>