<?php
/**
 * Provides a report of a product's history.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Stock Tracking';
$this->note = count($this->transactions).' transaction(s) for '.count($this->stock).' item(s) found.';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/product/track'];
$pines->com_jstree->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid a {
		text-decoration: underline;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var submit_url = "<?php echo addslashes(pines_url('com_sales', 'product/track')); ?>";
		var submit_search = function(){
			if ($("#p_muid_types_dialog [name=types_invoice]").attr('checked'))
				types.push('invoice');
			if ($("#p_muid_types_dialog [name=types_return]").attr('checked'))
				types.push('return');
			if ($("#p_muid_types_dialog [name=types_swap]").attr('checked'))
				types.push('swap');
			if ($("#p_muid_types_dialog [name=types_transfer]").attr('checked'))
				types.push('transfer');
			if ($("#p_muid_types_dialog [name=types_po]").attr('checked'))
				types.push('po');
			if ($("#p_muid_types_dialog [name=types_countsheet]").attr('checked'))
				types.push('countsheet');
			// Submit the form with all of the fields.
			pines.get(submit_url, {
				"serial": serial_box.val(),
				"sku": sku_box.val(),
				"types": types,
				"location": location,
				"descendents": descendents,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		var serial_box, sku_box;
		var types = new Array();
		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){history_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){history_grid.date_form();}},
				{type: 'button', title: 'Transactions', extra_class: 'picon picon-view-choose', selection_optional: true, click: function(){history_grid.types_form();}},
				{type: 'separator'},
				{type: 'text', label: 'SKU: ', load: function(textbox){
					// Display the current sku being searched.
					textbox.val("<?php echo htmlspecialchars($this->sku); ?>");
					textbox.keydown(function(e){
						if (e.keyCode == 13)
							submit_search();
					});
					sku_box = textbox;
				}},
				{type: 'separator'},
				{type: 'text', label: 'Serial #: ', load: function(textbox){
					// Display the current serial being searched.
					textbox.val("<?php echo htmlspecialchars($this->serial); ?>");
					textbox.keydown(function(e){
						if (e.keyCode == 13)
							submit_search();
					});
					serial_box = textbox;
				}},
				{type: 'separator'},
				{type: 'button', title: 'Update', extra_class: 'picon picon-view-refresh', selection_optional: true, click: submit_search}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/product/track", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var history_grid = $("#p_muid_grid").pgrid(cur_options);

		history_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the date form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Date Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 315,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
							}
						}
					});
				}
			});
		};
		history_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the location form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Location Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 250,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
							}
						}
					});
				}
			});
		};
		history_grid.types_form = function(row){
			var types_dialog = $("#p_muid_types_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 250,
				buttons: {
					'Done': function() {
						types_dialog.dialog('close');
					}
				}
			});
			types_dialog.dialog('open');
		};
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Created Date</th>
			<th>SKU</th>
			<th>Product</th>
			<th>Location</th>
			<th>Transaction #</th>
			<th>Type</th>
			<th>Status</th>
			<th>Qty</th>
			<th>Serials</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->transactions as $cur_transaction) {
		switch ($cur_transaction->type) {
			case 'sale':
				$link = pines_url('com_sales', 'sale/receipt', array('id' => $cur_transaction->entity->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->group->name} [{$cur_transaction->entity->group->groupname}]");
				$quantity = count($cur_transaction->entity->products);
				$serials = '';
				break;
			case 'return':
				$link = pines_url('com_sales', 'return/receipt', array('id' => $cur_transaction->entity->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->group->name} [{$cur_transaction->entity->group->groupname}]");
				$quantity = count($cur_transaction->entity->products);
				$serials = '';
				break;
			case 'swap':
				$link = pines_url('com_sales', 'sale/receipt', array('id' => $cur_transaction->entity->ticket->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->group->name} [{$cur_transaction->entity->group->groupname}]");
				$quantity = $cur_transaction->qty;
				$serials = implode(', ', $cur_transaction->serials);
				break;
			case 'countsheet':
				$link = pines_url('com_sales', 'countsheet/approve', array('id' => $cur_transaction->entity->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->group->name} [{$cur_transaction->entity->group->groupname}]");
				$quantity = count($cur_transaction->entity->products);
				$serials = '';
				break;
			case 'transfer':
				$link = pines_url('com_sales', 'transfer/edit', array('id' => $cur_transaction->entity->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->destination->name} [{$cur_transaction->entity->destination->groupname}]");
				$quantity = $cur_transaction->qty;
				$serials = implode(', ', $cur_transaction->serials);
				break;
			case 'po':
				$link = pines_url('com_sales', 'po/edit', array('id' => $cur_transaction->entity->guid));
				$groupname = htmlspecialchars("{$cur_transaction->entity->destination->name} [{$cur_transaction->entity->destination->groupname}]");
				$quantity = $cur_transaction->qty;
				$serials = implode(', ', $cur_transaction->serials);
				break;
			default:
				$link = '';
				$groupname = htmlspecialchars("{$cur_transaction->entity->group->name} [{$cur_transaction->entity->group->groupname}]");
				$quantity = '';
				$serials = '';
				break;
		}
	?>
		<tr title="<?php echo $cur_transaction->entity->guid; ?>">
			<td><?php echo format_date($cur_transaction->entity->p_cdate); ?></td>
			<td><?php echo htmlspecialchars($cur_transaction->product->sku); ?></td>
			<td><?php echo htmlspecialchars($cur_transaction->product->name); ?></td>
			<td><?php echo $groupname; ?></td>
			<td><a href="<?php echo $link; ?>" onclick="window.open(this.href); return false;"><?php echo $cur_transaction->entity->guid; ?></a></td>
			<td><?php echo ucwords($cur_transaction->type); ?></td>
			<td><?php echo htmlspecialchars($cur_transaction->transaction_info); ?></td>
			<td><?php echo htmlspecialchars($quantity); ?></td>
			<td><?php echo htmlspecialchars($serials); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_types_dialog" title="Transaction Types" style="display: none;" class="pf-form">
	<div class="pf-element pf-full-width">
		<span class="pf-label">Invoices</span>
		<input class="pf-field" type="checkbox" name="types_invoice" value="ON"<?php echo $this->types['invoice'] ? ' checked="checked"' : ''; ?> />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Returns</span>
		<input class="pf-field" type="checkbox" name="types_return" value="ON"<?php echo $this->types['return'] ? ' checked="checked"' : ''; ?> />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Swaps</span>
		<input class="pf-field" type="checkbox" name="types_swap" value="ON"<?php echo $this->types['swap'] ? ' checked="checked"' : ''; ?> />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Transfers</span>
		<input class="pf-field" type="checkbox" name="types_transfer" value="ON"<?php echo $this->types['transfer'] ? ' checked="checked"' : ''; ?> />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">POs</span>
		<input class="pf-field" type="checkbox" name="types_po" value="ON"<?php echo $this->types['po'] ? ' checked="checked"' : ''; ?> />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Countsheets</span>
		<input class="pf-field" type="checkbox" name="types_countsheet" value="ON"<?php echo $this->types['countsheet'] ? ' checked="checked"' : ''; ?> />
	</div>
</div>