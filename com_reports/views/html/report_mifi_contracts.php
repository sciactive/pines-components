<?php
/**
 * Shows a list of active MiFi contracts.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Active MiFi Contracts ['.$this->location->name.']';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_mifi_contracts'];

?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid a {
		text-decoration: underline;
	}
	.p_muid_mifi_actions button {
		padding: 0;
	}
	.p_muid_mifi_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		var search_mifi = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportmificontracts')); ?>", {
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
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){mifi_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){mifi_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Printer Friendly', extra_class: 'picon picon-document-print', selection_optional: true, click: function(){
						pines.get("<?php echo addslashes(pines_url('com_reports', 'reportmificontracts')); ?>", {
							"location": location,
							"descendents": descendents,
							"all_time": all_time,
							"start_date": start_date,
							"end_date": end_date,
							"template": "tpl_print"
						}, "_blank");
				}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'mifi_sales',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_mifi_contracts", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var mifi_grid = $("#p_muid_grid").pgrid(cur_options);

		mifi_grid.date_form = function(){
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
								search_mifi();
							}
						}
					});
				}
			});
		};
		mifi_grid.location_form = function(){
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
								search_mifi();
							}
						}
					});
				}
			});
		};
	});
	// ]]>
</script>
<div class="pf-element pf-full-width">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Location</th>
				<th>Employee</th>
				<th>Customer</th>
				<th>Principal</th>
				<th>APR</th>
				<th>Term</th>
				<th>Company</th>
				<th>Rank</th>
				<th>ETS Date</th>
				<th>Credit Score</th>
				<th>SST Verified</th>
				<th>Contract</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->contracts as $cur_contract) { ?>
			<tr title="<?php echo $cur_contract->guid; ?>">
				<td>#<?php echo htmlspecialchars($cur_contract->contract_id); ?></td>
				<td><?php echo format_date($cur_contract->p_cdate, 'date_sort'); ?></td>
				<td><?php echo htmlspecialchars($cur_contract->group->name); ?></td>
				<td><?php echo htmlspecialchars($cur_contract->user->name); ?></td>
				<td><?php echo htmlspecialchars($cur_contract->customer->name); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_contract->principal, 2, '.', '')); ?></td>
				<td style="text-align: right;"><?php echo htmlspecialchars(number_format($cur_contract->apr * 100, 2, '.', '')); ?>%</td>
				<td style="text-align: right;"><?php echo htmlspecialchars($cur_contract->term); ?></td>
				<td><?php echo htmlspecialchars($pines->com_mifi->companies[$cur_contract->company]['name']); ?></td>
				<td><?php echo htmlspecialchars($pines->com_mifi->ranks[$cur_contract->militaryPayGrade]); ?></td>
				<td><?php echo format_date($cur_contract->ets_date, 'date_sort'); ?></td>
				<td style="text-align: right;"><?php echo isset($cur_contract->credit_score) ? htmlspecialchars($cur_contract->credit_score) : 'NA'; ?></td>
				<td><?php echo ($cur_contract->verified_sst) ? 'Yes' : 'No'; ?></td>
				<td><?php echo $cur_contract->verified_sst ? '<a href="'.htmlspecialchars(pines_url('com_mifi', 'contract/view', array('id' => $cur_contract->guid))).'" onclick="window.open(this.href); return false;">Download</a>' : 'NA'; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>