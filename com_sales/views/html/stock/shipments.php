<?php
/**
 * Lists shipments and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = ($this->removed ? 'Completed ' : 'Pending ').'Shipments';
if (isset($this->location))
	$this->title .= htmlspecialchars(" at {$this->location->name} [{$this->location->groupname}]");
$this->note = $this->descendents ? 'Including Descendent Locations' : '';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/stock/shipments'];
$pines->com_jstree->load();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var submit_url = "<?php echo addslashes(pines_url('com_sales', 'stock/shipments')); ?>";
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.get(submit_url, {
				"location": location,
				"descendents": descendents
			});
		};

		// Location Defaults
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (!$this->removed) { ?>
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){shipments_grid.location_form();}},
				{type: 'separator'},
				<?php } if (gatekeeper('com_sales/managestock')) { ?>
				{type: 'button', text: 'Edit/Ship', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'stock/ship', array('type' => '__col_1__', 'id' => '__title__'))); ?>'},
				<?php } ?>
				{type: 'separator'},
				<?php if (!$this->removed) { ?>
				{type: 'button', text: 'Completed', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'stock/shipments', array('removed' => 'true'))); ?>'},
				<?php } else { ?>
				{type: 'button', text: 'Pending', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'stock/shipments')); ?>'},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'shipments',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/stock/shipments", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var shipments_grid = $("#p_muid_grid").pgrid(cur_options);

		shipments_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
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
			<th>Type</th>
			<th>ID/Number</th>
			<th>Tendered</th>
			<?php if (!$this->removed) { ?>
			<th>Location</th>
			<?php } ?>
			<th>Destination</th>
			<th>Shipper</th>
			<th>Tracking #</th>
			<th>ETA</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->sales as $sale) { ?>
		<tr title="<?php echo (int) $sale->guid ?>">
			<td>Sale</td>
			<td><?php echo htmlspecialchars($sale->id); ?></td>
			<td><?php echo format_date($sale->tender_date, 'full_sort'); ?></td>
			<?php if (!$this->removed) { ?>
			<td><?php echo htmlspecialchars("{$sale->group->name} [{$sale->group->groupname}]"); ?></td>
			<?php } ?>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $sale->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars("{$sale->shipping_address->name} ({$sale->customer->guid}: {$sale->customer->name})"); ?></a></td>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'shipper/edit', array('id' => $sale->shipper->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($sale->shipper->name); ?></a></td>
			<td><?php echo htmlspecialchars(isset($sale->tracking_numbers) ? implode(', ', $sale->tracking_numbers) : ''); ?></td>
			<td><?php echo isset($sale->eta) ? format_date($sale->eta, 'date_sort') : '' ; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>