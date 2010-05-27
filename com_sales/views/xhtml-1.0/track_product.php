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
$this->title = 'Stock History';
$this->note = count($this->transactions).' record(s) found.';
$pines->com_pgrid->load();
$pines->com_jstree->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#history_grid a {
		color: blue;
		text-decoration:  underline;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){

		var submit_url = "<?php echo pines_url('com_sales', 'trackproduct'); ?>";
		function submit_search() {
			// Submit the form with all of the fields.
			pines.post(submit_url, {
				"tracking_code": tracking_code_box.val(),
				"sku": sku_box.val(),
				"location": location,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		}

		var history_grid = $("#history_grid");
		var tracking_code_box, sku_box;
		// Timespan Defaults
		var all_time = "<?php echo $this->all_time; ?>";
		var start_date = "<?php echo $this->start_date; ?>";
		var end_date = "<?php echo $this->end_date; ?>";
		// Location Defaults
		var location = "<?php echo $this->location; ?>";
		
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{type: 'button', text: 'Location', extra_class: 'picon picon_16x16_applications-internet', selection_optional: true, click: function(){
					history_grid.location_form();
				}},
				{type: 'separator'},
				{
					type: 'text',
					label: 'SKU: ',
					load: function(textbox){
						// Display the current sku being searched.
						textbox.val("<?php echo $this->sku; ?>");
						textbox.keydown(function(e){
							if (e.keyCode == 13)
								submit_search();
						});
						sku_box = textbox;
					}
				},
				{type: 'separator'},
				{
					type: 'text',
					label: 'Tracking #: ',
					load: function(textbox){
						// Display the current tracking code being searched.
						textbox.val("<?php echo $this->tracking_code; ?>");
						textbox.keydown(function(e){
							if (e.keyCode == 13)
								submit_search();
						});
						tracking_code_box = textbox;
					}
				},
				{type: 'separator'},
				{type: 'button', text: 'Timespan', extra_class: 'picon picon_16x16_preferences-system-time', selection_optional: true, click: function(){
					history_grid.date_form();
				}},
				{type: 'separator'},
				{type: 'button', text: 'Update &raquo;', extra_class: 'picon picon_16x16_appointment-recurring', selection_optional: true, click: function(){
					submit_search();
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/track_product", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#history_grid").pgrid(cur_options);

		history_grid.date_form = function(){
			$.ajax({
				url: "<?php echo pines_url('com_sales', 'dateselectform'); ?>",
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
							form.append(data);
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
				url: "<?php echo pines_url('com_sales', 'locationselectform'); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location},
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
							form.append(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								if (form.find(":input[name=location_saver]").val() == "all") {
									location = 'all';
								} else {
									location = form.find(":input[name=location]").val();
								}
								form.dialog('close');
							}
						}
					});
				}
			});
		};
	});
	// ]]>
</script>
<table id="history_grid">
	<thead>
		<tr>
			<th>Created</th>
			<th>Transaction #</th>
			<th>SKU</th>
			<th>Description</th>
			<th>Location</th>
			<th>Transaction</th>
			<th>Qty</th>
			<th>Tracking #</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->transactions as $cur_transaction) {
		$transaction_type = $cur_transaction->entity->tags[1];
		switch ($transaction_type) {
			case 'sale':
				$link = pines_url('com_sales', 'receiptsale', array('id' => $cur_transaction->entity->guid));
				break;
			case 'countsheet':
				$link = pines_url('com_sales', 'editcountsheet', array('id' => $cur_transaction->entity->guid));
				break;
			case 'transfer':
				$link = pines_url('com_sales', 'edittransfer', array('id' => $cur_transaction->entity->guid));
				break;
			case 'po':
				$link = pines_url('com_sales', 'editpo', array('id' => $cur_transaction->entity->guid));
				break;
			default:
				$link = '';
				break;
		}
	?>
		<tr title="<?php echo $cur_transaction->entity->guid; ?>">
			<td><?php echo format_date($cur_transaction->entity->p_cdate); ?></td>
			<td><a href="<?php echo $link; ?>" target="_blank"><?php echo $cur_transaction->entity->guid; ?></a></td>
			<td><?php echo $cur_transaction->product->sku; ?></td>
			<td><?php echo $cur_transaction->product->name; ?></td>
			<td><?php echo $cur_transaction->entity->group->name; ?></td>
			<td><?php echo $cur_transaction->transaction_info; ?></td>
			<td><?php echo $cur_transaction->qty; ?></td>
			<td><?php echo $cur_transaction->serial; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table><br />
<form class="pf-form" method="post">
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'liststock'); ?>');" value="Done" />
	</div>
</form>