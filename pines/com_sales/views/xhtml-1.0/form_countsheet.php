<?php
/**
 * Provides a form for the user to edit a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'New Countsheet' : (($this->entity->final) ? 'Viewing' : 'Editing').' Countsheet ['.htmlentities($this->entity->guid).']';
if (isset($this->entity->guid))
	$this->note = 'Created by ' . $pines->user_manager->get_username($this->entity->uid) . ' on ' . date('Y-m-d', $this->entity->p_cdate) . ' - Last Modified on ' . date('Y-m-d', $this->entity->p_mdate);
?>
<form class="pform" method="post" id="countsheet_details" action="<?php echo pines_url('com_sales', 'savecountsheet'); ?>">
	<script type="text/javascript">
		// <![CDATA[
		var entries, entries_table, entry_counter;

		$(function(){
			entries = $("#entries");
			entries_table = $("#entries_table");
			entry_counter = $("#entry_counter").val();
			
			entries_table.pgrid({
				pgrid_view_height: "360px",
				pgrid_paginate: false
				<?php if ( !$this->entity->final ) { ?>,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'text',
						label: 'Code: ',
						load: function(textbox){
							textbox.keydown(function(e){
								if (e.keyCode == 13) {
									var code = textbox.val();
									if (code == "") {
										alert("Please enter a product code.");
										return;
									}
									textbox.val("");
									entry_counter++;
									entries_table.pgrid_add([{key: entry_counter, values: [code]}]);
									entries.val(JSON.stringify(entries_table.pgrid_get_all_rows().pgrid_export_rows()));
								}
							});
						}
					},
					{type: 'separator'},
					{
							type: 'button',
							text: 'Remove',
							extra_class: 'icon picon_16x16_actions_edit-delete',
							confirm: true,
							multi_select: true,
							click: function(e, rows){
								rows.pgrid_delete();
								entries.val(JSON.stringify(entries_table.pgrid_get_all_rows().pgrid_export_rows()));
							}
					}
				]
				<?php } ?>
			});
		});
		$(document).ready(function() {
			entries.val(JSON.stringify(entries_table.pgrid_get_all_rows().pgrid_export_rows()));
		});
		// ]]>
	</script>
	<div class="element full_width">
		<div class="field">
			<table id="entries_table">
				<thead>
					<tr>
						<th>Serial Number / SKU</th>
					</tr>
				</thead>
				<tbody>
					<?php $entry_count = 0;
					foreach ($this->entity->entries as $cur_entry) { ?>
					<tr title="<?php echo $cur_entry->key; ?>">
						<td><?php echo $cur_entry->values[0]; ?></td>
					</tr>
					<?php $entry_count++; } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input type="hidden" id="entry_counter" name="entry_counter" value="<?php echo $entry_count; ?>" />
		<input type="hidden" id="entries" name="entries" value="<?php echo $entry_dump; ?>" />
		<input type="hidden" id="save" name="save" value="" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#save').val('save');" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#save').val('commit');" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'listcountsheets'); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'listcountsheets'); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>