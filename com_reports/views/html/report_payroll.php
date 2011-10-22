<?php
/**
 * Shows a list of employee totals.
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

$this->title = 'Payroll Report';
if (isset($this->location))
	$this->title .= ' ['.htmlspecialchars($this->location->name).']';
$this->title .= ' ('.format_date($this->start_date, 'date_short').' - '.format_date($this->end_date, 'date_short').')';

if ($this->descendents)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_grid .amount {
		text-align: right;
	}
	#p_muid_grid .negative {
		color: red;
	}
	#p_muid_grid .total {
		text-align: right;
		font-weight: bold;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		entire_company = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportpayroll')); ?>", {
				"entire_company": true
			});
		};
		search_employees = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportpayroll')); ?>", {
				"location": location,
				"descendents": descendents
			});
		};

		// Payroll report settings
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var employees_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sortable: true,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar_contents: [
				<?php if ($this->entire_company) { ?>
				{type: 'button', title: 'Finalize', extra_class: 'picon picon-document-save', selection_optional: true, confirm: true, click: function(){pines.finalize();}},
				<?php } else { ?>
				{type: 'button', title: 'Entire Company', extra_class: 'picon picon-view-process-all', selection_optional: true, click: function(){entire_company();}},
				<?php } ?>
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){employees_grid.location_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'payroll_report',
						content: rows
					});
				}}
			]
		});
		employees_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'locationselect')); ?>",
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
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								search_employees();
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
				<th>Employee</th>
				<th>Location</th>
				<th>Pay Type</th>
				<th># Sold</th>
				<th># Ref</th>
				<th>$ Sold</th>
				<th>$ Ref</th>
				<th>Scheduled</th>
				<th>Worked</th>
				<th>Variance</th>
				<th>Commission</th>
				<th>Penalties</th>
				<th>Bonuses</th>
				<th>Total Pay</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$counted = $total_store = array();
			foreach ($this->invoices as $cur_invoice) {
				if ($cur_invoice->has_tag('sale')) {
					foreach ($cur_invoice->products as $cur_product) {
						if (!isset($totals[$cur_product['salesperson']->guid])){
							$totals[$cur_product['salesperson']->guid] = array(
								'employee' => $cur_product['salesperson'],
								'qty_sold' => 0,
								'qty_returned' => 0,
								'total_sold' => 0,
								'total_returned' => 0,
								'scheduled' => 0,
								'clocked' => 0,
								'variance' => 0,
								'commission' => 0,
								'penalties' => 0,
								'bonuses' => 0,
								'total_pay' => 0
							);
							$commissions[$cur_product['salesperson']->guid] = $cur_product['salesperson']->commissions;
						}
						if (!in_array($cur_invoice->guid, $counted)) {
							$totals[$cur_product['salesperson']->guid]['qty_sold']++;
							$counted[] = $cur_invoice->guid;
						}
						$totals[$cur_product['salesperson']->guid]['total_sold'] += $cur_product['line_total'];
						foreach ((array) $commissions[$cur_product['salesperson']->guid] as $key => $cur_commission) {
							if ($cur_commission['ticket']->guid == $cur_invoice->guid) {
								$totals[$cur_product['salesperson']->guid]['commission'] += $cur_commission['amount'];
								unset($commissions[$cur_product['salesperson']->guid][$key]);
							}
						}
					}
				} elseif ($cur_invoice->has_tag('return')) {
					foreach ($cur_invoice->products as $cur_product) {
						if (!isset($totals[$cur_product['salesperson']->guid])){
							$totals[$cur_product['salesperson']->guid] = array(
								'employee' => $cur_product['salesperson'],
								'qty_sold' => 0,
								'qty_returned' => 0,
								'total_sold' => 0,
								'total_returned' => 0,
								'scheduled' => 0,
								'clocked' => 0,
								'variance' => 0,
								'commission' => 0,
								'penalties' => 0,
								'bonuses' => 0,
								'total_pay' => 0
							);
							$commissions[$cur_product['salesperson']->guid] = $cur_product['salesperson']->commissions;
						}
						if (!in_array($cur_invoice->guid, $counted)) {
							$totals[$cur_product['salesperson']->guid]['qty_returned']++;
							$counted[] = $cur_invoice->guid;
						}
						$totals[$cur_product['salesperson']->guid]['total_returned'] += $cur_product['line_total'];
						foreach ((array) $commissions[$cur_product['salesperson']->guid] as $key => $cur_commission) {
							if ($cur_commission['ticket']->guid == $cur_invoice->guid) {
								$totals[$cur_product['salesperson']->guid]['commission'] += $cur_commission['amount'];
								unset($commissions[$cur_product['salesperson']->guid][$key]);
							}
						}
					}
				}
			}
			foreach ($this->employees as $cur_employee) {
				if (!isset($totals[$cur_employee->guid])){
					$totals[$cur_employee->guid] = array(
						'employee' => $cur_employee,
						'qty_sold' => 0,
						'qty_returned' => 0,
						'total_sold' => 0,
						'total_returned' => 0,
						'scheduled' => 0,
						'clocked' => 0,
						'variance' => 0,
						'commission' => 0,
						'penalties' => 0,
						'bonuses' => 0,
						'total_pay' => 0
					);
				}
				$schedule = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'gte' => array('start', $this->start_date),
						'lt' => array('end', $this->end_date),
						'ref' => array('employee', $cur_employee)
					)
				);
				foreach ($schedule as $cur_schedule)
					$totals[$cur_employee->guid]['scheduled'] += $cur_schedule->scheduled;

				$issues = $pines->entity_manager->get_entities(
					array('class' => com_hrm_issue),
					array('&',
						'tag' => array('com_hrm', 'issue'),
						'gte' => array('date', $this->start_date),
						'lte' => array('date', $this->end_date),
						'ref' => array('employee', $cur_employee)
					)
				);
				foreach ($issues as $cur_issue)
					$totals[$cur_employee->guid]['penalties'] += $cur_issue->issue_type->penalty*$cur_issue->quantity;

				$bonuses = $pines->entity_manager->get_entities(
					array('class' => com_hrm_bonus),
					array('&',
						'tag' => array('com_hrm', 'bonus'),
						'gte' => array('date', $this->start_date),
						'lte' => array('date', $this->end_date),
						'ref' => array('employee', $cur_employee)
					)
				);
				foreach ($bonuses as $cur_bonus)
					$totals[$cur_employee->guid]['bonuses'] += $cur_bonus->amount;

				$totals[$cur_employee->guid]['clocked'] = $cur_employee->timeclock->sum($this->start_date, $this->end_date);
				$totals[$cur_employee->guid]['variance'] = ($totals[$cur_employee->guid]['clocked'] - $totals[$cur_employee->guid]['scheduled']);
				// Calculate the total pay for this employee.
				switch ($cur_employee->pay_type) {
					case 'hourly':
						$totals[$cur_employee->guid]['total_pay'] = ($totals[$cur_employee->guid]['clocked']/3600) * $cur_employee->pay_rate;
						break;
					case 'commission':
						$totals[$cur_employee->guid]['total_pay'] = $totals[$cur_employee->guid]['commission'];
						break;
					case 'hourly_commission':
						$totals[$cur_employee->guid]['total_pay'] = (($totals[$cur_employee->guid]['clocked']/3600) * $cur_employee->pay_rate) + $totals[$cur_employee->guid]['commission'];
						break;
					case 'commission_draw':
						$totals[$cur_employee->guid]['total_pay'] = max(($totals[$cur_employee->guid]['clocked']/3600) * $cur_employee->pay_rate, $totals[$cur_employee->guid]['commission']);
						break;
					case 'salary':
						$days_worked = isset($this->start_date) ? ceil(($this->end_date-$this->start_date)/86400) : ceil((time()-$cur_employee->p_cdate)/86400);
						$totals[$cur_employee->guid]['total_pay'] = ($cur_employee->pay_rate/365)*($days_worked);
						break;
					case 'salary_commission':
						$days_worked = isset($this->start_date) ? ceil(($this->end_date-$this->start_date)/86400) : ceil((time()-$cur_employee->p_cdate)/86400);
						$totals[$cur_employee->guid]['total_pay'] = (($cur_employee->pay_rate/365)*($days_worked)) + $totals[$cur_employee->guid]['commission'];
						break;
				}
				$totals[$cur_employee->guid]['total_pay'] -= $totals[$cur_employee->guid]['penalties'];
				$totals[$cur_employee->guid]['total_pay'] += $totals[$cur_employee->guid]['bonuses'];
			}
			foreach ($totals as $cur_total) {
			?>
			<tr title="<?php echo $cur_total['employee']->guid; ?>">
				<td><?php echo htmlspecialchars($cur_total['employee']->name); ?></td>
				<td><?php echo htmlspecialchars($cur_total['employee']->group->name); ?></td>
				<td><?php echo htmlspecialchars($cur_total['employee']->pay_type); ?></td>
				<td><?php echo htmlspecialchars($cur_total['qty_sold']); ?></td>
				<td><?php echo htmlspecialchars($cur_total['qty_returned']); ?></td>
				<td class="amount">$<?php echo number_format($cur_total['total_sold'], 2, '.', ''); ?></td>
				<td class="amount">$<?php echo number_format($cur_total['total_returned'], 2, '.', ''); ?></td>
				<td><?php echo round($cur_total['scheduled'] / 3600, 2); ?> hours</td>
				<td><?php echo round($cur_total['clocked'] / 3600, 2); ?> hours</td>
				<td><span<?php echo ($cur_total['variance'] < 0 ) ? ' class="negative;"' : ''; ?>><?php echo round($cur_total['variance'] / 3600, 2); ?> hours</span></td>
				<td class="amount">$<?php echo number_format($cur_total['commission'], 2, '.', ''); ?></td>
				<td class="amount"><span<?php echo ($cur_total['penalties'] > 0 ) ? ' class="negative"' : ''; ?>>$<?php echo number_format($cur_total['penalties'], 2, '.', ''); ?></span></td>
				<td class="amount"><span>$<?php echo number_format($cur_total['bonuses'], 2, '.', ''); ?></span></td>
				<td class="total"><span<?php echo ($cur_total['total_pay'] < 0 ) ? ' class="negative"' : ''; ?>>$<?php echo number_format($cur_total['total_pay'], 2, '.', ''); ?></span></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var rows = $("#p_muid_grid").pgrid_get_all_rows();
			var totals = JSON.stringify(rows.pgrid_export_rows());
			pines.finalize = function() {
				// Finalize the paystub.
				pines.get("<?php echo addslashes(pines_url('com_reports', 'savepaystub')); ?>", {
					"start": '<?php echo format_date($this->start_date); ?>',
					"end": '<?php echo format_date($this->end_date); ?>',
					"totals": totals
				});
			};
		});
		// ]]>
	</script>
</div>