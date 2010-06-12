<?php
/**
 * Lists returns and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Returns';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/return/list'];
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var submit_url = "<?php echo pines_url('com_sales', 'return/list'); ?>";
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.post(submit_url, {
				"location": location,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};
		
		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid ? $this->location->guid : 'all'; ?>";
		
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){return_grid.location_form();}},
				{type: 'button', text: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){return_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/newreturn')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'return/edit'); ?>'},
				<?php } if (gatekeeper('com_sales/editreturn')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: '<?php echo pines_url('com_sales', 'return/edit', array('id' => '__title__')); ?>'},
				<?php } ?>
				{type: 'button', text: 'Receipt', extra_class: 'picon picon-document-print-preview', double_click: true, url: '<?php echo pines_url('com_sales', 'return/receipt', array('id' => '__title__')); ?>'},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletereturn')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'return/delete', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'returns',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/return/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var return_grid = $("#return_grid").pgrid(cur_options);

		return_grid.date_form = function(){
			$.ajax({
				url: "<?php echo pines_url('com_sales', 'forms/dateselect'); ?>",
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
							"Update": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
		return_grid.location_form = function(){
			$.ajax({
				url: "<?php echo pines_url('com_sales', 'forms/locationselect'); ?>",
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
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								if (form.find(":input[name=location_saver]").val() == "all") {
									location = 'all';
								} else {
									location = form.find(":input[name=location]").val();
								}
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
	});
	// ]]>
</script>
<table id="return_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Date</th>
			<th>Status</th>
			<th>User</th>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<th>Customer</th>
			<?php } ?>
			<th>Products</th>
			<th>Subtotal</th>
			<th>Item Fees</th>
			<th>Tax</th>
			<th>Total</th>
			<th>Tendered</th>
			<th>Change Given</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->returns as $return) { ?>
		<tr title="<?php echo $return->guid; ?>">
			<td><?php echo $return->id; ?></td>
			<td><?php echo date('Y-m-d', $return->p_cdate); ?></td>
			<td><?php echo ucwords($return->status); ?></td>
			<td><?php echo !isset($return->user->guid) ? '' : "{$return->user->name} [{$return->user->username}]"; ?></td>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<td><?php echo htmlentities($return->customer->guid ? "{$return->customer->guid}: \"{$return->customer->name}\"" : ''); ?></td>
			<?php } ?>
			<td><?php
			$number = 0;
			foreach ($return->products as $cur_product) {
				$number += (int) $cur_product['quantity'];
			}
			echo $number; ?></td>
			<td><?php echo $return->subtotal; ?></td>
			<td><?php echo $return->item_fees; ?></td>
			<td><?php echo $return->taxes; ?></td>
			<td><?php echo $return->total; ?></td>
			<td><?php echo $return->amount_tendered; ?></td>
			<td><?php echo $return->change; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>