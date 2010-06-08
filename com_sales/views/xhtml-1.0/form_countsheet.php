<?php
/**
 * Provides a form for the user to edit a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'New Countsheet' : (($this->entity->final) ? 'Viewing' : 'Editing').' Countsheet ['.htmlentities($this->entity->guid).']';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[
	var entries, entries_table, entry_counter;

	pines(function(){
		entries = $("#countsheet_details input[name=entries]");
		entries_table = $("#entries_table");
		entry_counter = $("#entry_counter").val();

		entries_table.pgrid({
			pgrid_view_height: "360px",
			<?php if ( !$this->entity->final ) { ?>
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
								entries_table.pgrid_add([{key: entry_counter, values: [code, 1]}]);
								update_entries();
							}
						});
					}
				},
				{type: 'separator'},
				{
					type: 'button',
					text: 'Quantity',
					extra_class: 'picon picon_16x16_document-multiple',
					confirm: false,
					multi_select: false,
					click: function(e, rows){
						var loader;
						$.ajax({
							url: "<?php echo pines_url('com_sales', 'productsearch'); ?>",
							type: "POST",
							dataType: "json",
							data: {"code": rows.pgrid_get_value(1)},
							beforeSend: function(){
								loader = $.pnotify({
									pnotify_title: 'Product Search',
									pnotify_text: 'Retrieving product from server...',
									pnotify_notice_icon: 'picon picon_16x16_throbber',
									pnotify_nonblock: true,
									pnotify_hide: false,
									pnotify_history: false
								});
							},
							complete: function(){
								loader.pnotify_remove();
							},
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while trying to lookup the product code:\n"+XMLHttpRequest.status+": "+textStatus);
							},
							success: function(data){
								if (!data) {
									alert("No product was found with the SKU ["+rows.pgrid_get_value(1)+"].");
									return;
								}
								var qty = prompt("Please enter a quantity:", rows.pgrid_get_value(2));
								if (qty == null || isNaN(parseInt(qty)))
									qty = rows.pgrid_get_value(2);
								// Update the quantity of the item.
								rows.pgrid_set_value(2, qty);
								update_entries();
							}
						});
					}
				},
				{
					type: 'button',
					text: 'Remove',
					extra_class: 'picon picon_16x16_edit-delete',
					confirm: true,
					multi_select: true,
					click: function(e, rows){
						rows.pgrid_delete();
						update_entries();
					}
				}
			],
			<?php } ?>
			pgrid_paginate: false
		});

		function update_entries() {
			entries.val(JSON.stringify(entries_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_entries();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="countsheet_details" action="<?php echo htmlentities(pines_url('com_sales', 'savecountsheet')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
		<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<?php if (!empty($this->entity->review_comments)) {?>
	<div class="pf-element pf-heading">
		<h1>Reviewer Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field"><?php echo $this->entity->review_comments; ?></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Entries</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field">
			<table id="entries_table">
				<thead>
					<tr>
						<th>Serial Number / SKU</th>
						<th>Quantity</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->entity->entries as $cur_entry) { ?>
					<tr>
						<td><?php echo $cur_entry->code; ?></td>
						<td><?php echo $cur_entry->qty; ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-field pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></span>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input type="hidden" name="entries" value="" />
		<input type="hidden" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#countsheet_details input[name=save]').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#countsheet_details input[name=save]').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listcountsheets')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listcountsheets')); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>