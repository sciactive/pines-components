<?php
/**
 * Displays pending warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = ($this->ordered ? 'Ordered' : 'New').' Pending Warehouse Orders';
if (isset($this->location)) {
	$this->title .= htmlspecialchars(" at {$this->location->name} [{$this->location->groupname}]");
	if ($this->descendents)
		$this->title .= ' and Below';
}
if ($this->all_time) {
	$this->note = 'All time included.';
} elseif (isset($this->start_date) || isset($this->end_date)) {
	if (isset($this->start_date))
		$this->note = format_date($this->start_date, 'date_short').' - ';
	else
		$this->note = 'Up to and including ';
	if (isset($this->end_date))
		$this->note .= format_date($this->end_date - 1, 'date_short').'.';
	else
		$this->note .= ' and beyond.';
}
$pines->com_pgrid->load();
$pines->com_jstree->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/warehouse/pending'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var submit_url = "<?php echo addslashes(pines_url('com_sales', 'warehouse/pending', array('ordered' => ($this->ordered ? 'true' : 'false')))); ?>";
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.get(submit_url, {
				"location": location,
				"descendents": descendents,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){pending_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){pending_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', text: 'Guide', title: 'See information about where current stock is available.', extra_class: 'picon picon-view-calendar-tasks', double_click: true, click: function(e, rows){
					var loader;
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_sales', 'warehouse/pending_info')); ?>",
						type: "POST",
						dataType: "html",
						data: {id: rows.attr("title")},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Stock Location Guide',
								pnotify_text: 'Retrieving info...',
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
							pines.error("An error occured while trying to create guide:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							$("<div title=\"Stock Location Guide\"></div>").html(data+"<br />").dialog({
								modal: false,
								width: 800
							})
						}
					});
				}},
				<?php if (!$this->ordered) { ?>
				{type: 'button', text: 'Mark Ordered', extra_class: 'picon picon-task-complete', multi_select: true, confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/markordered', array('id' => '__title__', 'ordered' => 'true'))); ?>', delimiter: ','},
				<?php } else { ?>
				{type: 'button', text: 'Mark Not Ordered', extra_class: 'picon picon-task-attempt', multi_select: true, confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/markordered', array('id' => '__title__', 'ordered' => 'false'))); ?>', delimiter: ','},
				<?php } ?>
				{type: 'button', text: 'Attach PO', extra_class: 'picon picon-mail-attachment', multi_select: true, confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/attachpo', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'button', text: 'Detach PO', extra_class: 'picon picon-list-remove', multi_select: true, confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/detachpo', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'button', text: 'Assign Stock', extra_class: 'picon picon-document-import', multi_select: true, confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/assignstock', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'button', title: 'Flag', extra_class: 'picon picon-flag-red', multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/flag', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php if (!$this->ordered) { ?>
				{type: 'button', text: 'Ordered', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/pending', array('ordered' => 'true', 'location' => $this->location->guid, 'descendents' => ($this->descendents ? 'true' : 'false'), 'all_time' => ($this->all_time ? 'true' : 'false'), 'start_date' => ($this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''), 'end_date' => ($this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : '')))); ?>'},
				<?php } else { ?>
				{type: 'button', text: 'New Orders', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/pending', array('location' => $this->location->guid, 'descendents' => ($this->descendents ? 'true' : 'false'), 'all_time' => ($this->all_time ? 'true' : 'false'), 'start_date' => ($this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''), 'end_date' => ($this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : '')))); ?>'},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'pending warehouse orders',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/warehouse/pending", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var pending_grid = $("#p_muid_grid").pgrid(cur_options);

		pending_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+XMLHttpRequest.status+": "+textStatus);
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
		pending_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+XMLHttpRequest.status+": "+textStatus);
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
							"Update": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
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
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date</th>
			<th>Product</th>
			<th>Quantity</th>
			<th>Sale ID</th>
			<th>Sale Location</th>
			<th>Customer</th>
			<th>PO</th>
			<th>Vendors</th>
			<th>Flag Comments</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->sales as $sale) {
		foreach ($sale->products as $key => $cur_product) {
			// Filter non warehouse products.
			if ($cur_product['delivery'] != 'warehouse' || ($cur_product['ordered'] xor $this->ordered))
				continue;
			// Have they already all been assigned?
			if (count($cur_product['stock_entities']) >= ($cur_product['quantity'] + $cur_product['returned_quantity']))
				continue;
			$styles = array();
			if (isset($cur_product['flag_bgcolor']))
				$styles[] = 'background-color: '.htmlspecialchars($cur_product['flag_bgcolor']).';';
			if (isset($cur_product['flag_textcolor']))
				$styles[] = 'color: '.htmlspecialchars($cur_product['flag_textcolor']).';';
			if ($styles)
				$style = ' style="'.implode (' ', $styles).'"';
			else
				$style = '';
		?>
		<tr title="<?php echo $sale->guid.'_'.$key; ?>">
			<td<?php echo $style; ?>><?php echo format_date($sale->tender_date, 'full_sort'); ?></td>
			<td<?php echo $style; ?>><a<?php echo $style; ?> href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product['entity']->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars("{$cur_product['entity']->sku} : {$cur_product['entity']->name}"); ?></a></td>
			<td<?php echo $style; ?>><?php echo htmlspecialchars($cur_product['quantity'] - (count($cur_product['stock_entities']) - $cur_product['returned_stock_entities'])); ?></td>
			<td<?php echo $style; ?>><a<?php echo $style; ?> href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $sale->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($sale->id); ?></a></td>
			<td<?php echo $style; ?>><?php echo htmlspecialchars("{$sale->group->name} [{$sale->group->groupname}]"); ?></td>
			<td<?php echo $style; ?>><a<?php echo $style; ?> href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $sale->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars("{$sale->customer->guid}: {$sale->customer->name}"); ?></a></td>
			<?php if (isset($cur_product['po'])) { ?>
			<td<?php echo $style; ?>><a<?php echo $style; ?> href="<?php echo htmlspecialchars(pines_url('com_sales', 'po/edit', array('id' => $cur_product['po']->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_product['po']->po_number); ?></a></td>
			<?php } else { ?>
			<td<?php echo $style; ?>>None Attached</td>
			<?php } ?>
			<td<?php echo $style; ?>>
				<?php
				$vendors = array(); 
				foreach ($cur_product['entity']->vendors as $cur_vendor) {
					$cur_string = '';
					if (!empty($cur_vendor['link']))
						$cur_string .= '<a'.$style.' href="'.htmlspecialchars($cur_vendor['link']).'" onclick="window.open(this.href); return false;">';
					$cur_string .= htmlspecialchars($cur_vendor['entity']->name);
					if (!empty($cur_vendor['link']))
						$cur_string .= '</a>';
					$vendors[] = $cur_string;
				}
				echo implode(', ', $vendors);
				?>
			</td>
			<td<?php echo $style; ?>><?php echo htmlspecialchars($cur_product['flag_comments']); ?></td>
		</tr>
	<?php } } ?>
	</tbody>
</table>