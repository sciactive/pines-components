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
	pines(function(){
		var entries = $("#p_muid_form input[name=entries]");
		var entries_table = $("#p_muid_entries_table");
		var entry_counter = $("#p_muid_entry_counter").val();
		<?php if ( $this->entity->final ) { ?>
		entries_table.pgrid({
			pgrid_view_height: "360px",
			pgrid_paginate: false
		});
		<?php } else { ?>
		var code_box;
		entries_table.pgrid({
			pgrid_view_height: "360px",
			pgrid_paginate: false,
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
								var item_count = 0;
								entries_table.pgrid_get_all_rows().each(function(){
									// Check each entry, increment Qty if it already exists.
									var cur_row = $(this);
									if (code == cur_row.pgrid_get_value(1)) {
										item_count = parseInt(cur_row.pgrid_get_value(2))+1;
										if (!isNaN(item_count))
											cur_row.pgrid_set_value(2, item_count);
									}
								});
								if (item_count == 0) {
									entry_counter++;
									entries_table.pgrid_add([{key: entry_counter, values: [code, 1]}]);
								}
								update_entries();
							}
						});
						code_box = textbox;
					}
				},
				{type: 'separator'},
				{
					type: 'button',
					text: 'Quantity',
					extra_class: 'picon picon-document-multiple',
					confirm: false,
					multi_select: false,
					click: function(e, rows){
						var loader;
						$.ajax({
							url: "<?php echo addslashes(pines_url('com_sales', 'product/search')); ?>",
							type: "POST",
							dataType: "json",
							data: {"code": rows.pgrid_get_value(1)},
							beforeSend: function(){
								loader = $.pnotify({
									pnotify_title: 'Product Search',
									pnotify_text: 'Retrieving product from server...',
									pnotify_notice_icon: 'picon picon-throbber',
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
								while ((isNaN(parseInt(qty)) || parseInt(qty) != qty) && qty != null)
									qty = prompt("Please enter a quantity:", rows.pgrid_get_value(2));
								if (qty == null)
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
					extra_class: 'picon picon-edit-delete',
					confirm: true,
					multi_select: true,
					click: function(e, rows){
						rows.pgrid_delete();
						update_entries();
					}
				}
			]
		});
		code_box.focus();
		<?php } ?>

		var update_entries = function(){
			entries.val(JSON.stringify(entries_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_entries();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'countsheet/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlentities("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlentities("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
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
		<div class="pf-field"><?php echo htmlentities($this->entity->review_comments); ?></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Entries</h1>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_entries_table">
			<thead>
				<tr>
					<th>Serial Number / SKU</th>
					<th>Quantity</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->entries as $cur_entry) { ?>
				<tr>
					<td><?php echo htmlentities($cur_entry->code); ?></td>
					<td><?php echo htmlentities($cur_entry->qty); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input type="hidden" name="entries" value="" />
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#p_muid_save').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'countsheet/list')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'countsheet/list')); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>