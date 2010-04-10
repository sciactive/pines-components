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
		border: 1px dotted red;
		color: red;
	}
	#countsheet_details fieldset.missing legend {
		font-weight: bold;
	}
	#countsheet_details fieldset.matched {
		border: 1px dashed green;
		color: green;
	}
	#countsheet_details fieldset.sold {
		border: 1px dotted chocolate;
		color: chocolate;
	}
	#countsheet_details fieldset.sold div.element.heading {
		border-bottom: 1px dotted chocolate;
	}
	/* ]]> */
</style>
<form class="pform" method="post" id="countsheet_details" action="<?php echo pines_url('com_sales', 'savecountsheetstatus'); ?>">
	<?php if ($this->missing) { ?>
	<fieldset class="group missing">
		<legend>Missing Items</legend>
		<ul style="list-style-type: circle;">
			<?php foreach ($this->missing as $cur_entry) { ?>
			<li><?php echo "{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku})"; ?></li>
			<?php } ?>
		</ul>
	</fieldset>
	<?php } if ($this->matched) { ?>
	<fieldset class="group matched">
		<legend>Matched Items</legend>
		<ul style="list-style-type: square;">
			<?php foreach ($this->matched as $cur_entry) { ?>
			<li><?php echo "{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku})"; ?></li>
			<?php } ?>
		</ul>
	</fieldset>
	<?php } if ($this->potential) { ?>
	<fieldset class="group sold">
		<legend>Potential Matches</legend>
		<?php foreach ($this->potential as $cur_entry) { ?>
			<?php if ($cur_entry['closest']) { ?>
			<div class="element heading">
				<p>Items Matching "<strong><?php echo $cur_entry['name']; ?></strong>" in Location</p>
			</div>
			<ul style="list-style-type: disc;">
				<?php foreach ($cur_entry['closest'] as $cur_closest) { ?>
				<li><?php echo "{$cur_closest->product->name} (".(isset($cur_closest->serial) ? "Serial: {$cur_closest->serial}, " : '')."SKU: {$cur_closest->product->sku})"; ?></li>
				<?php } ?>
			</ul>
			<?php } if ($cur_entry['entries']) { ?>
			<div class="element heading">
				<p>Items Matching "<strong><?php echo $cur_entry['name']; ?></strong>" Not in Location</p>
			</div>
			<ul style="list-style-type: disc;">
				<?php foreach ($cur_entry['entries'] as $cur_entry) {
					switch ($cur_entry->status) {
						case 'sold_at_store':
							$txs = $pines->entity_manager->get_entities(array('ref' => array('stock' => $cur_entry), 'data' => array('type' => 'removed'), 'tags' => array('com_sales', 'transaction', 'stock_tx'), 'class' => com_sales_tx));
							if (!$txs) {
								echo "<li>{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku}, Sold at an unknown store)</li>";
								continue;
							}
							$tx = end($txs);
							unset($txs);
							echo "<li>{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku}, Sold on ".pines_date_format($tx->p_cdate)." from: {$tx->old_location->name} [{$tx->old_location->groupname}])</li>";
							break;
						case 'sold_pending':
							$txs = $pines->entity_manager->get_entities(array('ref' => array('stock' => $cur_entry), 'data' => array('type' => 'removed'), 'tags' => array('com_sales', 'transaction', 'stock_tx'), 'class' => com_sales_tx));
							if (!$txs) {
								echo "<li>{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku}, Sold and awaiting pickup at an unkown store)</li>";
								continue;
							}
							$tx = end($txs);
							unset($txs);
							echo "<li>{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku}, Sold on ".pines_date_format($tx->p_cdate)." and awaiting pickup from: {$cur_entry->location->name} [{$cur_entry->location->groupname}])</li>";
							break;
						default:
							echo "<li>{$cur_entry->product->name} (".(isset($cur_entry->serial) ? "Serial: {$cur_entry->serial}, " : '')."SKU: {$cur_entry->product->sku}, Location: Location: {$cur_entry->location->name} [{$cur_entry->location->groupname}])</li>";
							break;
					}
				} ?>
			</ul>
			<?php } ?>
		<?php } ?>
	</fieldset>
	<?php } if ($this->extra) { ?>
	<fieldset class="group ui-priority-secondary">
		<legend>Extraneous Items</legend>
		<ul style="list-style-type: circle;">
			<?php foreach ($this->extra as $cur_entry) { ?>
			<li>"<?php echo $cur_entry; ?>" has no record in this location's inventory.</li>
			<?php } ?>
		</ul>
	</fieldset>
	<?php } if (!empty($this->entity->comments)) {?>
	<div class="element">
		<span class="label">Comments</span>
		<div class="group">
			<div class="field"><?php echo $this->entity->comments; ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="element">
		<label>
			<span class="label">Update Status</span>
			<select class="field ui-widget-content" name="status" size="1">
				<option value="approved" <?php echo ($this->entity->status == 'approved') ? 'selected="selected"' : ''; ?>>Approved</option>
				<option value="declined" <?php echo ($this->entity->status == 'declined') ? 'selected="selected"' : ''; ?>>Declined</option>
				<option value="info_requested" <?php echo ($this->entity->status == 'info_requested') ? 'selected="selected"' : ''; ?>>Info Requested</option>
				<option value="pending" <?php echo ($this->entity->status == 'pending') ? 'selected="selected"' : ''; ?>>Pending</option>
			</select>
		</label>
	</div>
	<div class="element full_width">
		<label>
			<span class="label">Review Comments</span>
			<span class="field full_width"><textarea style="width: 98%;" rows="3" cols="35" name="review_comments"><?php echo $this->entity->review_comments; ?></textarea></span>
		</label>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input name="approve" class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'listcountsheets'); ?>');" value="Cancel" />
	</div>
</form>