<?php
/**
 * Shows a list of all sales and relevant MiFi info.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'MiFi Report ['.$this->location->name.']';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_mifi'];

$pay_grades = array(
	'1' => 'E1 Private',
	'2' => 'E2 Private',
	'3' => 'E3 Private 1st class',
	'4' => 'E4 Specialist',
	'5' => 'E4 Corporal',
	'6' => 'E5 Sergeant',
	'7' => 'E6 Staff Sergeant',
	'8' => 'E7 Sergeant 1st Class',
	'9' => 'E8 Master Sergeant',
	'10' => 'E8 First Sergeant',
	'11' => 'E9 Sergeant Major',
	'12' => 'E9 Command Sergeant Major',
	'13' => 'E9 Sergeant Major of the Army',
	'14' => 'O1 Second Lieutenant',
	'16' => 'O2 First Lieutenant',
	'18' => 'O3 Captain',
	'20' => 'O4 Major',
	'21' => 'O5 Lt. Colonel',
	'22' => 'O6 Colonel',
	'23' => 'O7 Brigadier General',
	'24' => 'O8 Major General',
	'25' => 'O9 Lt. General',
	'26' => 'O10 General',
	'27' => 'W1 Warrent Officer',
	'28' => 'W2 Chief Warrant Officer',
	'29' => 'W3 Chief Warrant Officer',
	'30' => 'W4 Chief Warrant Officer',
	'31' => 'W5 Chief Warrant Officer',
	'15' => 'O1E Second Lieutenant',
	'17' => 'O2E First Lieutenant',
	'19' => 'O3E Captain'
);

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
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportmifi')); ?>", {
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_mifi", state: cur_state});
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
				<th>Sale ID</th>
				<th>Date</th>
				<th>Location</th>
				<th>Status</th>
				<th>Employee</th>
				<th>Customer</th>
				<th>Subtotal</th>
				<th>Taxes</th>
				<th>Total</th>
				<th>Contract</th>
				<th>Rank</th>
				<th>ETS Date</th>
				<th>Faxsheet</th>
				<th>Credit</th>
				<th>Comments</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->sales as $cur_sale) {
				$contract = $pines->entity_manager->get_entity(
						array('class' => com_mifi_application),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'ref' => array(
								array('customer', $cur_sale->customer),
								array('sale', $cur_sale)
							)
						)
					);
				$contract_link = pines_url('com_mifi', 'viewoffer', array('id' => $contract->guid));
				$faxsheet = $pines->entity_manager->get_entity(array('class' => com_faxsheet_sheet), array('&', 'tag' => array('com_faxsheet', 'faxsheet'), 'ref' => array('customer', $cur_sale->customer)));
				$sheet_link = pines_url('com_faxsheet', 'editsst', array('id' => $faxsheet->guid));
				$return_status = ($cur_sale->returned_total > 0) ? ' (Returned)' : '';
			?>
			<tr title="<?php echo $cur_sale->guid; ?>">
				<td><?php echo htmlspecialchars($cur_sale->id); ?></td>
				<td><?php echo format_date($cur_sale->p_cdate, 'date_sort'); ?></td>
				<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
				<td><?php echo htmlspecialchars(ucwords($cur_sale->status).$return_status); ?></td>
				<td><?php echo htmlspecialchars($cur_sale->user->name); ?></td>
				<td><a href="<?php echo pines_url('com_customer', 'customer/edit', array('id' => $cur_sale->customer->guid)); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->customer->name); ?></a></td>
				<td>$<?php echo htmlspecialchars($cur_sale->subtotal); ?></td>
				<td>$<?php echo htmlspecialchars($cur_sale->taxes); ?></td>
				<td>$<?php echo htmlspecialchars($cur_sale->total); ?></td>
				<td><?php echo isset($contract->guid) ? '<a href="'.htmlspecialchars($contract_link).'" onclick="window.open(this.href); return false;">'.htmlspecialchars($contract->app_id).'</a>' : 'No'; ?></td>
				<td><?php echo isset($contract->guid) ? $pay_grades[$contract->militaryPayGrade] : 'NA'; ?></td>
				<td><?php echo isset($contract->guid) ? format_date($contract->ets_date, 'date_sort') : 'NA'; ?></td>
				<td><?php echo isset($faxsheet->guid) ? '<a href="'.htmlspecialchars($sheet_link).'" onclick="window.open(this.href); return false;">'.$faxsheet->guid.'</a>' : 'No'; ?></td>
				<td><?php echo isset($contract->guid) ? htmlspecialchars($contract->credit_score) : 'NA'; ?></td>
				<td><?php echo htmlspecialchars($cur_sale->comments); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>