<?php
/**
 * Shows a product details report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Product Details Report ['.htmlspecialchars($this->location->name).']';
if (!$this->all_time)
	$this->note = htmlspecialchars(format_date($this->start_date, 'date_short')).' - '.htmlspecialchars(format_date($this->end_date - 1, 'date_short'));

$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_product_details'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	.p_muid_return td {
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		pines.search_details = function(){
			if ($("#p_muid_types_dialog [name=types_sold]").attr('checked'))
				types.push('sold');
			if ($("#p_muid_types_dialog [name=types_returned]").attr('checked'))
				types.push('returned');
			if ($("#p_muid_types_dialog [name=types_invoiced]").attr('checked'))
				types.push('invoiced');
			if ($("#p_muid_types_dialog [name=types_voided]").attr('checked'))
				types.push('voided');
			if ($("#p_muid_types_dialog [name=types_return]").attr('checked'))
				types.push('return');
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportproducts')); ?>", {
				"location": location,
				"descendents": descendents,
				"types": types,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		var types = new Array();
		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){details_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){details_grid.date_form();}},
				{type: 'button', title: 'Transactions', extra_class: 'picon picon-view-choose', selection_optional: true, click: function(){details_grid.types_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'product_details',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_sort_col: 2,
			pgrid_sort_ord: "desc"
		};
		var cur_options = $.extend(cur_defaults);
		var details_grid = $("#p_muid_grid").pgrid(cur_options);

		details_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Date Selector\"></div>");
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
								pines.search_details();
							}
						}
					});
				}
			});
		};
		details_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Location Selector\"></div>");
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
								pines.search_details();
							}
						}
					});
				}
			});
		};
		details_grid.types_form = function(row){
			var types_dialog = $("#p_muid_types_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 250,
				buttons: {
					'Done': function() {
						types_dialog.dialog('close');
						pines.search_details();
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
			<th>ID</th>
			<th>Date</th>
			<th>Transaction</th>
			<th>Delivery</th>
			<th>Location</th>
			<th>Employee</th>
			<th>Customer</th>
			<th>SKU</th>
			<th>Serial</th>
			<th>Product</th>
			<th>Unit Cost</th>
			<th>Price</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($this->transactions as $cur_tx) {
			if ( empty($cur_tx->products) ||
				($cur_tx->status == 'invoiced' && !$this->types['invoiced']) ||
				($cur_tx->status == 'quoted' && !$this->types['quoted']) ||
				($cur_tx->status == 'voided' && !$this->types['voided']) )
					continue;

			if ($cur_tx->has_tag('return')) {
				$class = 'class="p_muid_return"';
				$tx_type = 'RE';
			} else {
				$tx_type = 'SA';
			}

			foreach ($cur_tx->products as $cur_item) {
				if ($tx_type == 'SA')
					$class = '';
				$cur_status = strtoupper($cur_tx->status);
				if (!empty($cur_item['returned_quantity'])) {
					if ($this->types['returned']) {
						$class = 'class="p_muid_return"';
						$cur_status = 'RETURNED';
					} else {
						continue;
					}
				}
			?>
			<tr <?php echo $class; ?>>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', ($tx_type == 'SA' ? 'sale/receipt' : 'return/receipt'), array('id' => $cur_tx->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($tx_type.$cur_tx->id); ?></a></td>
				<td><?php echo format_date($cur_tx->p_cdate); ?></td>
				<td><?php echo htmlspecialchars($cur_status); ?></td>
				<td><?php echo htmlspecialchars($cur_item['delivery']); ?></td>
				<td><?php echo htmlspecialchars($cur_tx->group->name); ?></td>
				<td><?php echo htmlspecialchars($cur_item['salesperson']->name); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $cur_tx->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_tx->customer->name); ?></a></td>
				<td><?php echo htmlspecialchars($cur_item['sku']); ?></td>
				<td><?php echo htmlspecialchars($cur_item['serial']); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_item['entity']->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_item['entity']->name); ?></a></td>
				<td>$<?php echo round($cur_item['entity']->vendors[0]['cost'], 2); ?></td>
				<td>$<?php echo round($cur_item['price'], 2); ?></td>
			</tr>
		<?php }
		} ?>
	</tbody>
</table>
<div id="p_muid_types_dialog" title="Transaction Types" style="display: none;">
	<div class="pf-form">
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Net Sales</span>
				<input class="pf-field" type="checkbox" name="types_sold" value="ON"<?php echo $this->types['sold'] ? ' checked="checked"' : ''; ?> /></label>
		</div>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Returned Sales</span>
				<input class="pf-field" type="checkbox" name="types_returned" value="ON"<?php echo $this->types['returned'] ? ' checked="checked"' : ''; ?> /></label>
		</div>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Invoices</span>
				<input class="pf-field" type="checkbox" name="types_invoiced" value="ON"<?php echo $this->types['invoiced'] ? ' checked="checked"' : ''; ?> /></label>
		</div>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Voids</span>
				<input class="pf-field" type="checkbox" name="types_voided" value="ON"<?php echo $this->types['voided'] ? ' checked="checked"' : ''; ?> /></label>
		</div>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Returns</span>
				<input class="pf-field" type="checkbox" name="types_return" value="ON"<?php echo $this->types['return'] ? ' checked="checked"' : ''; ?> /></label>
		</div>
	</div>
	<br />
</div>