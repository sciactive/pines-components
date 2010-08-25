<?php
/**
 * Provides a form for the user to review a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Reviewing Countsheet ['.htmlspecialchars($this->entity->guid).'] at '.$this->entity->group->name;
$this->note = 'Created by '.$this->entity->user->name.' on '.format_date($this->entity->p_cdate, 'full_long').'.';
if (isset($this->entity->run_count_date))
	$this->note .= ' Run on '.format_date($this->entity->run_count_date, 'full_long').'.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var options = {
			pgrid_view_height: "auto",
			pgrid_paginate: false,
			pgrid_select: false,
			pgrid_multi_select: false,
			pgrid_resize: false
		};
		$("#p_muid_missing_table, #p_muid_matched_table, #p_muid_potential_table, #p_muid_invalid_table")
		.find("tr.ui-priority-primary").bind("mouseover", function(e){
			e.stopImmediatePropagation();
		}).end()
		.pgrid(options)
		.find("tr.expandme").pgrid_expand_rows().filter("tr.collapseme").pgrid_collapse_rows();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo addslashes(pines_url('com_sales', 'countsheet/savestatus')); ?>">
	<?php if ($this->entity->missing) { ?>
	<div class="pf-element pf-heading">
		<h1 style="color: red;">Missing Items</h1>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_missing_table">
			<thead>
				<tr>
					<th style="width: 40%;">Name</th>
					<th style="width: 10%;">Qty</th>
					<th style="width: 25%;">SKU</th>
					<th style="width: 25%;">Serials</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->missing as $cur_entry) {
					if ($missing_counted[$cur_entry->product->guid])
						continue;
					$missing_counted[$cur_entry->product->guid] = true;
					?>
				<tr class="ui-state-error">
					<td><?php echo htmlspecialchars($cur_entry->product->name); ?></td>
					<td><?php echo htmlspecialchars($this->entity->missing_count[$cur_entry->product->guid]); ?></td>
					<td><?php echo htmlspecialchars($cur_entry->product->sku); ?></td>
					<td><?php echo htmlspecialchars(implode(', ', $this->entity->missing_serials[$cur_entry->product->guid])); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } if ($this->entity->matched) { ?>
	<div class="pf-element pf-heading">
		<h1 style="color: green;">Matched Items</h1>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_matched_table">
			<thead>
				<tr>
					<th style="width: 40%;">Name</th>
					<th style="width: 10%;">Qty</th>
					<th style="width: 25%;">SKU</th>
					<th style="width: 25%;">Serials</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->matched as $cur_entry) {
					if ($matched_counted[$cur_entry->product->guid])
						continue;
					$matched_counted[$cur_entry->product->guid] = true;
					?>
				<tr>
					<td><?php echo htmlspecialchars($cur_entry->product->name); ?></td>
					<td><?php echo htmlspecialchars($this->entity->matched_count[$cur_entry->product->guid]); ?></td>
					<td><?php echo htmlspecialchars($cur_entry->product->sku); ?></td>
					<td><?php echo htmlspecialchars(implode(', ', $this->entity->matched_serials[$cur_entry->product->guid])); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } if ($this->entity->potential) { ?>
	<div class="pf-element pf-heading">
		<h1 style="color: blue;">Potential Matches</h1>
		<p>A non-exhaustive list of entries the user may have meant.</p>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_potential_table">
			<thead>
				<tr>
					<th style="width: 10%;">Entry</th>
					<th style="width: 15%;">Potential Match</th>
					<th style="width: 15%;">SKU</th>
					<th style="width: 15%;">Serial</th>
					<th style="width: 15%;">Available</th>
					<th style="width: 15%;">Last Transaction</th>
					<th style="width: 15%;">Location</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($this->entity->potential as $cur_match) {
					?><tr class="parent expandme collapseme ui-priority-primary" title="<?php echo htmlspecialchars($cur_match['name']); ?>"><td><?php echo $cur_match['count'] > 1 ? htmlspecialchars("{$cur_match['name']} x {$cur_match['count']}") : htmlspecialchars("{$cur_match['name']}"); ?></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><?php
					if ($cur_match['closest']) {
						?><tr class="parent expandme ui-priority-primary child <?php echo htmlspecialchars($cur_match['name']); ?>" title="<?php echo htmlspecialchars($cur_match['name']); ?>_same"><td>Same Location</td><td></td><td></td><td></td><td></td><td></td><td></td></tr><?php
						foreach ($cur_match['closest'] as $cur_closest) {
							?><tr class="child <?php echo htmlspecialchars($cur_match['name']); ?>_same">
								<td></td>
								<td><?php echo htmlspecialchars($cur_closest->product->name); ?></td>
								<td><?php echo htmlspecialchars($cur_closest->product->sku); ?></td>
								<td><?php echo htmlspecialchars($cur_closest->serial); ?></td>
								<td><?php echo $cur_closest->available ? 'Yes' : 'No'; ?></td>
								<td><?php echo htmlspecialchars($cur_closest->last_reason()); ?></td>
								<td><?php echo htmlspecialchars($cur_closest->location->name); ?></td>
							</tr><?php
						}
					}
					if ($cur_match['entries']) {
						?><tr class="parent expandme ui-priority-primary child <?php echo htmlspecialchars($cur_match['name']); ?>" title="<?php echo htmlspecialchars($cur_match['name']); ?>_else"><td>Other Location</td><td></td><td></td><td></td><td></td><td></td><td></td></tr><?php
						foreach ($cur_match['entries'] as $cur_entry) {
							?><tr class="child <?php echo htmlspecialchars($cur_match['name']); ?>_else">
								<td></td>
								<td><?php echo htmlspecialchars($cur_entry->product->name); ?></td>
								<td><?php echo htmlspecialchars($cur_entry->product->sku); ?></td>
								<td><?php echo htmlspecialchars($cur_entry->serial); ?></td>
								<td><?php echo $cur_entry->available ? 'Yes' : 'No'; ?></td>
								<td><?php echo htmlspecialchars($cur_entry->last_reason()); ?></td>
								<td><?php echo htmlspecialchars($cur_entry->location->name); ?></td>
							</tr><?php
						}
					}
				} ?>
			</tbody>
		</table>
	</div>
	<?php } if ($this->entity->invalid) { ?>
	<div class="pf-element pf-heading">
		<h1 style="color: gray;">Invalid/Unknown Entries</h1>
		<p>No matches could be found for these entries.</p>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_invalid_table">
			<thead>
				<tr>
					<th style="width: 100%;">Entry</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->invalid as $cur_entry) { ?>
				<tr>
					<td><?php echo htmlspecialchars($cur_entry); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } if (!empty($this->entity->comments)) {?>
	<div class="pf-element">
		<span class="pf-label">Comments</span>
		<div class="pf-group">
			<div class="pf-field"><?php echo htmlspecialchars($this->entity->comments); ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label>
			<span class="pf-label">Update Status</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="status" size="1">
				<option value="approved" <?php echo ($this->entity->status == 'approved') ? 'selected="selected"' : ''; ?>>Approved</option>
				<option value="declined" <?php echo ($this->entity->status == 'declined') ? 'selected="selected"' : ''; ?>>Declined</option>
				<option value="info_requested" <?php echo ($this->entity->status == 'info_requested') ? 'selected="selected"' : ''; ?>>Info Requested</option>
				<option value="pending" <?php echo ($this->entity->status == 'pending') ? 'selected="selected"' : ''; ?>>Pending</option>
			</select>
		</label>
	</div>
	<div class="pf-element pf-full-width">
		<label>
			<span class="pf-label">Review Comments</span>
			<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="review_comments"><?php echo $this->entity->review_comments; ?></textarea></span>
		</label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input name="approve" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'countsheet/list')); ?>');" value="Cancel" />
	</div>
</form>