<?php
/**
 * Shows the sales rankings for a given location.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Rankings: '.$this->location->name.' ('.format_date($this->entity->start_date, 'date_sort').' - '.format_date($this->entity->end_date - 1, 'date_sort').')';
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/rank_sales'];

// Status levels for in the green, yellow and red classifications.
$green_status = $pines->config->com_reports->rank_level_green;
$yellow_status = $pines->config->com_reports->rank_level_yellow;

?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid td, #p_muid_grid th {
		font-weight: bold;
		text-align: center;
	}
	#p_muid_grid td.right_justify {
		text-align: right;
	}
	#p_muid_grid .total td {
		border-top-width: .2em;
	}
	#p_muid_grid .total td.rank, #p_muid_grid .total td.ui-pgrid-table-expander {
		background-color: gray;
	}
	#p_muid_grid .total td.ui-pgrid-table-expander {
		border-right: none;
	}
	#p_muid_grid .green td {
		background-color: green;
		color: black;
	}
	#p_muid_grid .yellow td {
		background-color: yellow;
		color: black;
	}
	#p_muid_grid .red td {
		background-color: red;
		color: black;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		pines.search_rankings = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'viewsalesranking')); ?>", {
				"id": "<?php echo $this->entity->guid; ?>",
				"location": location,
				"descendents": descendents
			});
		};

		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var rankings_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_reports/listsalesrankings')) { ?>
				{type: 'button', text: '&laquo; Rankings List', extra_class: 'picon picon-view-choose', selection_optional: true, url: '<?php echo addslashes(pines_url('com_reports', 'salesrankings')); ?>'},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){rankings_grid.location_form();}},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'sales_rankings',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc'
		});

		rankings_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the date form:\n"+XMLHttpRequest.status+": "+textStatus);
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
								pines.search_rankings();
							}
						}
					});
				}
			});
		};
		rankings_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the location form:\n"+XMLHttpRequest.status+": "+textStatus);
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
								pines.search_rankings();
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
			<th style="width: 5%;">Rank</th>
			<th style="width: 35%;">Employee</th>
			<th style="width: 10%;">Current</th>
			<th style="width: 10%;">Last</th>
			<th style="width: 10%;">MTD</th>
			<th style="width: 10%;">Goal</th>
			<th style="width: 10%;">Trend</th>
			<th style="width: 10%;">Trend %</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($this->rankings as $cur_rank) {
			// Employees are "in the green", "yellow" or "red".
			if ($cur_rank['pct'] >= $green_status) {
				$class = 'green';
			} elseif ($cur_rank['pct'] >= $yellow_status) {
				$class = 'yellow';
			} elseif ($cur_rank['pct'] < $yellow_status) {
				$class = 'red';
			}
		?>
		<tr title="<?php echo $cur_rank['employee']->guid; ?>" class="<?php echo $class; ?>">
			<td><?php echo htmlspecialchars($cur_rank['rank']); ?></td>
			<td><?php echo htmlspecialchars($cur_rank['employee']->name); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($cur_rank['current']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($cur_rank['last']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($cur_rank['mtd']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($cur_rank['goal']); ?></td>
			<td class="right_justify">$<?php echo round($cur_rank['trend'], 2); ?></td>
			<td class="right_justify"><?php echo round($cur_rank['pct'], 2); ?>%</td>
		</tr>
		<?php
		}
		// Companies / Locations are "in the green", "yellow" or "red".
		if ($this->total['pct'] >= $green_status) {
			$class = 'green';
		} elseif ($this->total['pct'] >= $yellow_status) {
			$class = 'yellow';
		} elseif ($this->total['pct'] < $yellow_status) {
			$class = 'red';
		}
		?>
		<tr class="total <?php echo $class; ?>">
			<td class="rank"><span style="display: none;">99999999</span></td>
			<td>Total</td>
			<td class="right_justify">$<?php echo htmlspecialchars($this->total['current']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($this->total['last']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($this->total['mtd']); ?></td>
			<td class="right_justify">$<?php echo htmlspecialchars($this->total['goal']); ?></td>
			<td class="right_justify">$<?php echo round($this->total['trend'], 2); ?></td>
			<td class="right_justify"><?php echo round($this->total['pct'], 2); ?>%</td>
		</tr>
	</tbody>
</table>