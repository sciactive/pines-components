<?php
/**
 * A list of hourly payroll.
 * 
 * Shows a list of payroll, as hourly it does not report anyone as commission vs
 * draw. Employees commissions are totaled but not added to their pay. That's
 * left for a different report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Payroll Summary Hourly ['.htmlspecialchars($this->location->name).']';
if ($this->descendents)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		search_invoices = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportpayrollhourly')); ?>", {
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
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var payroll_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sortable: true,
			pgrid_sort_col: 3,
			pgrid_sort_ord: 'desc',
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){payroll_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){payroll_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', text: 'Individual', extra_class: 'picon picon-document-print-preview', double_click: true, target: '_blank', url: '<?php echo addslashes(pines_url('com_reports', 'reportpayrollindividual', array('id' => '__title__', 'salary' => '__col_3__', 'commission' => '__col_12__', 'payperhour' => '__col_22__', 'total' => '__col_18__', 'hours' => '__col_8__', 'end_date' => format_date($this->end_date - 1, 'date_sort'), 'start_date' => format_date($this->start_date, 'date_sort'), 'template' => 'tpl_print', 'hourreport' => 'true'))); ?>'},
				{type: 'button', text: 'Multiple', extra_class: 'picon picon-document-print-preview', multi_select: true, target: '_blank', url: '<?php echo addslashes(pines_url('com_reports', 'reportpayrollmultiple', array('id' => '__title__', 'salary' => '__col_3__', 'commission' => '__col_12__', 'payperhour' => '__col_22__', 'total' => '__col_18__', 'hours' => '__col_8__', 'end_date' => format_date($this->end_date - 1, 'date_sort'), 'start_date' => format_date($this->start_date, 'date_sort'), 'template' => 'tpl_print', 'hourreport' => 'true'))); ?>', delimiter: ";"},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'invoice_summary',
						content: rows
					});
				}}
			]
		});

		payroll_grid.date_form = function(){
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
					pines.pause();
					var form = $("<div title=\"Date Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
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
								search_invoices();
							}
						}
					});
					pines.play();
				}
			});
		};
		payroll_grid.location_form = function(){
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
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
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
								search_invoices();
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
			<th>Name</th>
			<th>Pay Type</th>
			<th>Salary</th>
			<th>Comm Rate</th>
			<th># of Sales</th>
			<th>Sale Amount</th>
			<th>Hourly Rate</th>
			<th>Hours Worked</th>
			<th>Reg Hourly</th>
			<th>Overtime $</th>
			<th>Hourly Total</th>
			<th>Commission</th>
			<th>Bonus</th>
			<th>Weekly Draw</th>
			<th>Net Bonus</th>
			<th>Total</th>
			<th>Reimbursements</th>
			<th>Total Pay</th>
			<th>Manager</th>
			<th>Location</th>
			<th>Position</th>
			<th>Hourly Rate</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$commission_total = array();
		$commission_total[0] = $commission_total[1]=0;
		foreach ($this->employees as $cur_employee) {
			// Print out each employee's row of information.
			if ($cur_employee['entity']->pay_type == 'commission_draw' ) {
				if($cur_employee['sale_total'] == 0)
					$commission_total[0] += 6.0;
				else
					$commission_total[0] += ($cur_employee['commission'] / $cur_employee['sales_total']) * 100;
				$commission_total[1]++;
			}
			?>
		<tr title="<?php echo (int) $cur_employee['entity']->guid?>" >
			<td><a href="<?php echo htmlspecialchars(pines_url('com_user', 'edituser', array('id' => $cur_employee['entity']->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_employee['entity']->name); ?></a></td>
			<td><?php echo htmlspecialchars(strtoupper($cur_employee['commission_status']));?></td>
			<td><?php echo $cur_employee['commission_status'] != 'salary' ? 'N/A' : number_format($cur_employee['salary_pay_period'], 2, '.', ''); ?></td>
			<td style="text-align: center;"><?php echo ($cur_employee['commission_status'] != 'salary' && $cur_employee['sales_total'] != 0 && $cur_employee['commission_status'] != 'hourly') ? number_format((($cur_employee['commission'] / $cur_employee['sales_total']) * 100), 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? htmlspecialchars($cur_employee['number_sales']) : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? '$'.number_format($cur_employee['sales_total'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? number_format($cur_employee['entity']->pay_rate, 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? htmlspecialchars($cur_employee['hour_total']) : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? '$'.number_format($cur_employee['reghours'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? '$'.number_format($cur_employee['overtimehours'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? '$'.number_format($cur_employee['hour_pay_total'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? '$'.number_format($cur_employee['commission'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;">$<?php echo number_format($cur_employee['bonus'], 2, '.', '');?></td>
			<td style="text-align: center;"><?php echo ($cur_employee['commission_status'] != 'salary' && $cur_employee['weekly'] != 0) ? '$'.number_format($cur_employee['weekly'], 2, '.', '') : '-'; ?></td>
			<td style="text-align: center;">$<?php echo number_format($cur_employee['bonus'], 2, '.', '');?></td>
			<td style="text-align: center;">$<?php echo number_format($cur_employee['pay_total'], 2, '.', '');?></td>
			<td style="text-align: center;">$<?php echo number_format($cur_employee['adjustments'], 2, '.', '');?></td>
			<td><?php echo "$".number_format($cur_employee['total_with_reimburse'], 2, '.', '');?></td>
			<td></td>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_user', 'editgroup', array('id' => $cur_employee['entity']->group->guid)));?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_employee['entity']->group->name);?></a></td>
			<td><?php echo htmlspecialchars($cur_employee->job_title)?></td>
			<td style="text-align: center;"><?php echo $cur_employee['commission_status'] != 'salary' ? number_format($cur_employee['total_rate'], 2, '.', '') : '-'; ?></td>
		</tr>
		<?php }
		 // Now printing out the totals row.
		?>
		<tr>
			<td style="font-weight: bold; text-align: center;">Totals</td>
			<td style="font-weight: bold; text-align: center;"><?php echo number_format($this->commission_percent * 100, 2, '.', '').'%';?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_salary_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo $commission_total[1] != 0 ? number_format($commission_total[0] / $commission_total[1], 2, '.', '') : '0.00'; ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo htmlspecialchars($this->group_num_sales); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_sales_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo number_format($this->pay_rate_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo number_format($this->group_hours, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_reg_hours, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_overtime_hours, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_overtime_hours + $this->group_reg_hours, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->commission_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_bonus, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_weekly_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_bonus, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_pay_total, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_adjustments, 2, '.', ''); ?></td>
			<td style="font-weight: bold; text-align: center;"><?php echo '$'.number_format($this->group_pay_total_with_reimburse, 2, '.', ''); ?></td>
			<td></td>
			<td></td>
			<td></td>
			<td style="font-weight: bold; text-align: center;"><?php echo number_format($this->group_percent_rate, 2, '.', ''); ?></td>
		</tr>
	</tbody>
</table>