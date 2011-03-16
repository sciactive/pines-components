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

$this->title = 'MiFi Sales ['.$this->location->name.']';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_mifi'];

$reference_types = array(
	'FA'	=> 'Father',
	'FR'	=> 'Friend',
	'MA'	=> 'Mother',
	'NB'	=> 'Neighbor',
	'REL'	=> 'Relative',
	'SP'	=> 'Spouse'
);
$account_types = array(
	'C' => 'Checking',
	'S' => 'Savings'
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
				"end_date": end_date,
				"verbose": verbose
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;
		var verbose = <?php echo $this->verbose ? 'true' : 'false'; ?>;

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
				data: {
					"location": location,
					"descendents": descendents,
					"verbose": verbose
				},
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
				<th>Company</th>
				<th>Rank</th>
				<th>ETS Date</th>
				<th>Credit Score</th>
				<th>Faxsheet</th>
				<th>SST Verified</th>
				<th>Comments</th>
				<?php if ($this->verbose) { ?>
				<th>APR</th>
				<th>Finance Charge</th>
				<th># of Payments</th>
				<th>Amount of Payments</th>
				<th>Email</th>
				<th>Birth Date</th>
				<th>SSN</th>
				<th>Active Status</th>
				<th>Branch</th>
				<th>BAH</th>
				<th>Time in Reserves</th>
				<th>Break in Service</th>
				<th>PEB Date</th>
				<th>Country</th>
				<th>Address</th>
				<th>City</th>
				<th>State</th>
				<th>Zip</th>
				<th>Phone</th>
				<th>Cell Phone</th>
				<th>HOR Address</th>
				<th>HOR City</th>
				<th>HOR State</th>
				<th>HOR Zip</th>
				<th>HOR Phone</th>
				<th>Housing Type</th>
				<th>Housing Payment</th>
				<th>Child Support</th>
				<th>Advance Pay</th>
				<th>Advance Pay Remaining</th>
				<th>Secure Email</th>
				<th>Brigade</th>
				<th>Battalion</th>
				<th>Company</th>
				<th>Platoon</th>
				<th>Supervisor</th>
				<th>Supervisor Rank</th>
				<th>Supervisor Phone</th>
				<th>Unit Phone</th>
				<th>Unit Address</th>
				<th>Unit City</th>
				<th>Unit State</th>
				<th>Unit Zip</th>
				<th>Reference 1</th>
				<th>Reference 1 Name</th>
				<th>Reference 1 Phone</th>
				<th>Reference 1 Address</th>
				<th>Reference 1 City</th>
				<th>Reference 1 State</th>
				<th>Reference 1 Zip</th>
				<th>Reference 2</th>
				<th>Reference 2 Name</th>
				<th>Reference 2 Phone</th>
				<th>Reference 2 Address</th>
				<th>Reference 2 City</th>
				<th>Reference 2 State</th>
				<th>Reference 2 Zip</th>
				<th>Bank Account Type</th>
				<th>Bank Account Number</th>
				<th>Bank Routing Number</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->sales as $cur_sale) {
				$contract = $pines->entity_manager->get_entity(
						array('class' => com_mifi_contract),
						array('&',
							'tag' => array('com_mifi', 'contract'),
							'ref' => array(
								array('customer', $cur_sale->customer),
								array('sale', $cur_sale)
							)
						)
					);
				$contract_link = pines_url('com_mifi', 'viewoffer', array('id' => $contract->guid));
			?>
			<tr title="<?php echo $cur_sale->guid; ?>">
				<td><?php echo htmlspecialchars($cur_sale->id); ?></td>
				<td><?php echo format_date($cur_sale->p_cdate, 'date_sort'); ?></td>
				<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
				<td><?php echo htmlspecialchars(ucwords($cur_sale->status)); ?></td>
				<td><?php echo htmlspecialchars($cur_sale->user->name); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $cur_sale->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->customer->name); ?></a></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->subtotal, 2, '.', '')); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->taxes, 2, '.', '')); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->total, 2, '.', '')); ?></td>
				<td style="text-align: right;"><?php echo isset($contract->guid) ? '<a href="'.htmlspecialchars($contract_link).'" onclick="window.open(this.href); return false;">'.htmlspecialchars($contract->contract_id).'</a>' : 'No'; ?></td>
				<td><?php echo isset($contract->guid) ? $pines->com_mifi->companies[$contract->company]['name'] : 'NA'; ?></td>
				<td><?php echo isset($contract->guid) ? $pines->com_mifi->ranks[$contract->militaryPayGrade] : 'NA'; ?></td>
				<td><?php echo isset($contract->guid) ? format_date($contract->ets_date, 'date_sort') : 'NA'; ?></td>
				<td style="text-align: right;"><?php echo isset($contract->guid) ? htmlspecialchars($contract->credit_score) : 'NA'; ?></td>
				<td><?php echo ($contract->approved_faxsheet) ? 'Approved' : (isset($contract->faxsheet_request) ? 'Requested' : 'None'); ?></td>
				<td><?php echo ($contract->verified_sst) ? 'Yes' : 'No'; ?></td>
				<td><?php echo htmlspecialchars($contract->application->comments); ?></td>
				<?php if ($this->verbose) { ?>
				<td><?php echo htmlspecialchars(($contract->apr * 100).'%'); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->finance_charge, 2, '.', '')); ?></td>
				<td><?php echo htmlspecialchars($contract->term); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->total_of_payments, 2, '.', '')); ?></td>
				<td><?php echo htmlspecialchars($contract->application->email); ?></td>
				<td><?php echo format_date($contract->application->customer_info['birth_date'], 'date_sort'); ?></td>
				<td><?php echo htmlspecialchars($contract->application->customer_info['ssn']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->active_status); ?></td>
				<td><?php echo htmlspecialchars($pines->com_mifi->branches[$contract->application->branch]); ?></td>
				<td><?php echo htmlspecialchars($contract->application->bahReceived); ?></td>
				<td><?php echo htmlspecialchars($contract->application->timeInReserves ? 'Yes' : 'No'); ?></td>
				<td><?php echo htmlspecialchars($contract->application->breakInService ? 'Yes' : 'No'); ?></td>
				<td><?php echo format_date($contract->application->entry_base_date, 'date_sort'); ?></td>
				<td><?php echo $contract->application->country == '1' ? 'US' : 'Other'; ?></td>
				<td><?php echo htmlspecialchars($contract->application->address1.' '.$contract->application->address2); ?></td>
				<td><?php echo htmlspecialchars($contract->application->city); ?></td>
				<td><?php echo htmlspecialchars($contract->application->state); ?></td>
				<td><?php echo htmlspecialchars($contract->application->zip); ?></td>
				<td><?php echo format_phone($contract->application->phone); ?></td>
				<td><?php echo format_phone($contract->application->cellPhone); ?></td>
				<td><?php echo htmlspecialchars($contract->application->hor_address.' '.$contract->application->hor_address2); ?></td>
				<td><?php echo htmlspecialchars($contract->application->hor_city); ?></td>
				<td><?php echo htmlspecialchars($contract->application->hor_state); ?></td>
				<td><?php echo htmlspecialchars($contract->application->hor_zip); ?></td>
				<td><?php echo format_phone($contract->application->hor_phone); ?></td>
				<td><?php echo htmlspecialchars($contract->application->housingType); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->application->housingPayment, 2, '.', '')); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->application->childSupport, 2, '.', '')); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->application->advancePay, 2, '.', '')); ?></td>
				<td>$<?php echo htmlspecialchars(number_format($contract->application->advancePayBalance, 2, '.', '')); ?></td>
				<td><?php echo htmlspecialchars($contract->application->email_secure); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_brigade); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_battalion); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_company); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_platoon); ?></td>
				<td><?php echo htmlspecialchars($contract->application->supervisor_name); ?></td>
				<td><?php echo $pines->com_mifi->ranks[$contract->application->supervisor_paygrade]; ?></td>
				<td><?php echo format_phone($contract->application->supervisor_phone); ?></td>
				<td><?php echo format_phone($contract->application->unit_phone); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_address); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_city); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_state); ?></td>
				<td><?php echo htmlspecialchars($contract->application->unit_zip); ?></td>
				<td><?php echo $reference_types[$contract->application->references[0]['relationship']]; ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[0]['name_first'].' '.$contract->application->references[0]['name_last']); ?></td>
				<td><?php echo format_phone($contract->application->references[0]['phone']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[0]['address']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[0]['city']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[0]['state']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[0]['zip']); ?></td>
				<td><?php echo $reference_types[$contract->application->references[1]['relationship']]; ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[1]['name_first'].' '.$contract->application->references[0]['name_last']); ?></td>
				<td><?php echo format_phone($contract->application->references[1]['phone']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[1]['address']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[1]['city']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[1]['state']); ?></td>
				<td><?php echo htmlspecialchars($contract->application->references[1]['zip']); ?></td>
				<td><?php echo $account_types[$contract->application->bank_account_type]; ?></td>
				<td><?php echo htmlspecialchars($contract->application->bank_account_number); ?></td>
				<td><?php echo htmlspecialchars($contract->application->bank_routing_number); ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>