<?php
/**
 * Lists ESPs and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Extended Service Plans';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_esp/list']);
?>
<script type="text/javascript">
	// <![CDATA[
	var dispo_count = 0;
	pines(function(){
		var dispo_counter = 0;
		var disposal_id;
		var disposal_dialog = $("#p_muid_disposal_dialog");
		var disposition = <?php echo json_encode($this->show); ?>;
		var disposition_dialog = $("#p_muid_disposition_dialog");
		var disposed = disposition_dialog.find("div.disposed");

		disposal_dialog.find("form").submit(function(){
			disposal_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		disposal_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function(){
					var dispose_as = disposal_dialog.find(":input[name=dispose]").val();
					pines.post(<?php echo json_encode(pines_url('com_esp', 'dispose')); ?>, {
						items: disposal_id,
						dispose: dispose_as
					});
					disposal_dialog.dialog("close");
				}
			}
		});

		disposition_dialog.find("input.exclusive").change(function(){
			if (dispo_counter >= dispo_count) {
				disposition_dialog.find("input").each(function() {
					$(this).removeAttr("checked");
				});
				$(this).attr("checked", "checked");
			}
		}).change();
		disposition_dialog.find("input.inclusive").change(function(){
			if (dispo_counter >= dispo_count) {
				disposition_dialog.find("input.exclusive").each(function() {
					$(this).removeAttr("checked");
				});
			} else {
				dispo_counter++;
			}
		}).change();
		disposition_dialog.find("form").submit(function(){
			disposition_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		disposition_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function(){
					if (disposition_dialog.find(":input:[name=all]").attr("checked")) {
						disposition = disposition_dialog.find(":input:checked").val();
					} else {
						disposition = "";
						disposition_dialog.find(":input:checked").each(function() {
							if (disposition != "")
								disposition += ",";
							disposition += $(this).val();
						});
					}
					cur_options.pgrid_state_change(plan_grid.pgrid_export_state());
					pines.post(<?php echo json_encode(pines_url('com_esp', 'list')); ?>, {
						show: disposition
					});
					disposition_dialog.dialog("close");
				}
			}
		});

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_esp/claim')) { ?>
				{type: 'button', text: 'Claim', extra_class: 'picon picon-tools-wizard',
					click: function(e, row){
						var comments = prompt("Please give a description of the claim filed:");
						if (comments != null) {
							pines.post(<?php echo json_encode(pines_url('com_esp', 'claim')); ?>, {
								id: row.pgrid_export_rows()[0].key,
								comments: comments
							});
						}
					}
				},
				<?php } ?>
				{type: 'button', text: 'History', extra_class: 'picon picon-story-editor', url: <?php echo json_encode(pines_url('com_esp', 'history', array('id' => '__title__'))); ?>},
				<?php if (gatekeeper('com_esp/disposeplans')) { ?>
				{type: 'button', text: 'Dispose', extra_class: 'picon picon-document-properties', multi_select: true, click: function(e, rows){
					disposal_id = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (disposal_id != "")
							disposal_id += ",";
						disposal_id += this.key;
					});
					if (rows.length == 1)
						disposal_dialog.find("div.disposal_title").html('<h1>['+pines.safe(rows.pgrid_get_value(1))+']</h1>');
					else
						disposal_dialog.find("div.disposal_title").html('<h1>'+pines.safe(rows.length)+' ESPs</h1>');
					disposal_dialog.dialog("open");
				}},
				<?php } if (gatekeeper('com_esp/printplan')) { ?>
				{type: 'button', text: 'Print', extra_class: 'picon picon-document-print', url: <?php echo json_encode(pines_url('com_esp', 'print', array('id' => '__title__'))); ?>, delimiter: ','},
				<?php } if (gatekeeper('com_sales/swapsale')) { ?>
				{type: 'button', text: 'Swap', extra_class: 'picon picon-document-swap', click: function(e, row){
					plan_grid.swap_form($(row).attr("title"));
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_esp/deleteplan')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_esp', 'delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } if (gatekeeper('com_esp/filterplans')) { ?>
				{type: 'button', text: 'Filter', extra_class: 'picon picon-view-filter', selection_optional: true, click: function(){
					disposition_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'ESPs',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				state.disposition = disposition;
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_esp/list_plans", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var plan_grid = $("#p_muid_grid").pgrid(cur_options);
		cur_options.pgrid_state_change(plan_grid.pgrid_export_state());

		plan_grid.swap_form = function(esp_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_esp', 'swapform')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": esp_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the swap form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Swap Item [ESP: "+pines.safe(esp_id)+"]\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Swap Items": function(){
								var swap_item = form.find(":input[name=swap_item]:checked").val();
								var new_serial = form.find(":input[name=new_serial]").val();
								if (swap_item == "") {
									alert('Please specify the item you want to swap.');
								} else if (new_serial == "") {
									alert('Please specify the new item serial number.');
								} else {
									form.dialog('close');
									// Submit the swap request.
									pines.post(<?php echo json_encode(pines_url('com_esp', 'swap')); ?>, {
										"id": esp_id,
										"swap_item": swap_item,
										"new_serial": new_serial.trim()
									});
								}
							}
						}
					});
					pines.play();
				}
			});
		};
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Plan</th>
			<th>Card ID</th>
			<th>Customer</th>
			<th>Item</th>
			<th>Serial</th>
			<th>Expires On</th>
			<th>Sale ID</th>
			<th>Sale Date</th>
			<th>Status</th>
			<th>Unique ID</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($this->plans as $plan) { ?>
		<tr title="<?php echo (int) $plan->guid ?>">
			<td>ESP<?php echo htmlspecialchars($plan->id); ?></td>
			<td><?php echo htmlspecialchars($plan->card->product->name); ?></td>
			<td><?php echo htmlspecialchars($plan->card->serial); ?></td>
			<td><?php echo htmlspecialchars($plan->customer->name); ?></td>
			<td><?php echo htmlspecialchars($plan->item->product->name); ?></td>
			<td><?php echo htmlspecialchars($plan->item->serial); ?></td>
			<td><?php echo format_date($plan->expiration_date, 'date_sort'); ?></td>
			<td><?php echo htmlspecialchars($plan->sale->id); ?></td>
			<td><?php echo format_date($plan->sale->tender_date, 'date_sort'); ?></td>
			<td><?php echo ucwords($plan->status); ?></td>
			<td><?php echo htmlspecialchars($plan->unique_id); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_disposal_dialog" title="Dispose ESP" style="overflow: hidden; display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading disposal_title"></div>
		<div class="pf-element">
		<label><span class="pf-label">Dispose as</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="dispose">
				<?php
					foreach ($pines->config->com_esp->disposal_types as $cur_dispo) {
						$dispo_array = explode(':', $cur_dispo);
						echo '<option value="'.htmlspecialchars($dispo_array[0]).'">'.htmlspecialchars($dispo_array[1]).'</option>';
					}
				?>
			</select>
		</label>
	</div>
	</form>
</div>
<div id="p_muid_disposition_dialog" title="Filter ESP List" style="overflow: hidden; display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading">
			<h1>View</h1>
		</div>
		<div class="pf-element">
			<input class="pf-field exclusive" type="checkbox" name="all" value="all" <?php echo ($this->show == 'all') ? 'checked="checked"' : '' ?>/>All ESPs<hr style="border-top: dashed 1px; margin-top: 5px;"/><br />
			<?php
				$dispo_counter = 0;
				$filter_array = explode(',', $this->show);
				foreach ($pines->config->com_esp->disposal_types as $cur_dispo) {
					$dispo_array = explode(':', $cur_dispo);
					echo '<input class="pf-field inclusive" type="checkbox" name="'.htmlspecialchars($dispo_array[0]).'" value="'.htmlspecialchars($dispo_array[0]).'"';
					echo in_array($dispo_array[0], $filter_array) ? 'checked="checked">' : '>';
					echo htmlspecialchars($dispo_array[1]) . '<br />';
					$dispo_counter++;
				}
			?>
			<script type="text/javascript">
				// <![CDATA[
				dispo_count = <?php echo (int) $dispo_counter; ?>;
				// ]]>
			</script>
		</div>
	</form>
</div>