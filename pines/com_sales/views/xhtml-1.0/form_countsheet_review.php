<?php
/**
 * Provides a form for the user to edit a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Reviewing Countsheet ['.htmlentities($this->entity->guid).']';
if (isset($this->entity->guid))
	$this->note = 'Created by ' . $pines->user_manager->get_username($this->entity->uid) . ' on ' . date('Y-m-d', $this->entity->p_cdate) . ' - Last Modified on ' . date('Y-m-d', $this->entity->p_mdate);
?>
<style type="text/css" >
	/* <![CDATA[ */
	#countsheet_details fieldset.missing {
		//background-color: whitesmoke;
		border: 1px dotted red;
		color: red;
	}
	#countsheet_details fieldset.matched {
		//background-color: cornsilk;
		border: 1px dashed green;
		color: green;
	}
	#countsheet_details fieldset.sold {
		//background-color: whitesmoke;
		border: 1px dotted chocolate;
		color: chocolate;
	}
	#countsheet_details fieldset.sold hr {
		border-top: 1px dashed chocolate;
		border-bottom: 0px;
		width: 50%;
	}
	#countsheet_details fieldset.extraneous {
		background-color: aliceblue;
		color: black;
	}
	/* ]]> */
</style>
<form class="pform" method="post" id="countsheet_details" action="<?php echo pines_url('com_sales', 'savecountsheetstatus'); ?>">
	<?php if (count($this->missing) > 0) { ?>
	<fieldset class="group missing">
		<legend>Missing Items</legend>
		<?php foreach ($this->missing as $cur_missing) { ?>
		<div class="element-full-width">
			<span class="label"><?php echo $cur_missing; ?></span>
		</div>
		<?php } ?>
	</fieldset>
	<?php } if (count($this->matched) > 0) { ?>
	<fieldset class="group matched">
		<legend>Matched Items</legend>
		<?php foreach ($this->matched as $cur_matched) { ?>
		<div class="element-full-width">
			<span class="label"><?php echo $cur_matched; ?></span>
		</div>
		<?php } ?>
	</fieldset>
	<?php } if (count($this->sold) > 0) { ?>
	<fieldset class="group sold">
		<legend>Potential Matches</legend>
		<?php foreach ($this->sold as $cur_sold) { ?>
		<div class="element-full-width">
			<span class="label"><?php echo $cur_sold; ?></span>
		</div>
		<?php } ?>
	</fieldset>
	<?php } if (count($this->extraneous) > 0) { ?>
	<fieldset class="group ui-priority-secondary">
		<legend>Extraneous Items</legend>
		<?php foreach ($this->extraneous as $cur_extraneous) { ?>
		<div class="element-full-width">
			<span class="label"><?php echo $cur_extraneous; ?></span>
		</div>
		<?php } ?>
	</fieldset>
	<?php } if (!empty($this->entity->comments)) {?>
	<div class="element">
		<label>
			<span class="label">Comments:</span>
			<span class="field"><?php echo $this->entity->comments; ?></span>
		</label>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Update Status To:</span>
		<select class="field ui-widget-content" name="status" size="1">
				<option value="approved" <?php echo ($this->entity->status == 'approved') ? 'selected="selected"' : ''; ?>>Approved</option>
				<option value="declined" <?php echo ($this->entity->status == 'declined') ? 'selected="selected"' : ''; ?>>Declined</option>
				<option value="info_requested" <?php echo ($this->entity->status == 'info_requested') ? 'selected="selected"' : ''; ?>>Info Requested</option>
				<option value="pending" <?php echo ($this->entity->status == 'pending') ? 'selected="selected"' : ''; ?>>Pending</option>
		</select></label>
	</div>
	<div class="element full_width">
		<span class="label">Review Comments:</span>
	</div>
	<div class="element full_width">
		<span class="field"><textarea style="width: 98%;" rows="3" cols="35" name="review_comments"><?php echo $this->entity->review_comments; ?></textarea></span>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input name="approve" class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'listcountsheets'); ?>');" value="Cancel" />
	</div>
</form>