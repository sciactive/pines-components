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

$this->title = 'Company Paystub';
if (isset($this->location))
	$this->title .= ' ['.htmlspecialchars($this->location->name).']';
$this->title .= ' ('.format_date($this->entity->start, 'date_short').' - '.format_date($this->entity->end, 'date_short').')';

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
				"id": paystub,
				"entire_company": true
			});
		};
		search_employees = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportpayroll')); ?>", {
				"id": paystub,
				"location": location,
				"descendents": descendents
			});
		};

		// Payroll report settings
		var paystub = '<?php echo $this->entity->guid; ?>';
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var employees_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sortable: true,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Paystubs', extra_class: 'picon picon-edit-clear-locationbar-ltr', selection_optional: true, url: '<?php echo addslashes(pines_url('com_reports', 'listpaystubs')); ?>'},
				<?php if (!$this->entire_company) { ?>
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
		<?php foreach ($this->entity->payroll as $cur_payment) {
			if (!$this->entire_company && ($cur_payment['location']->guid != $this->location->guid))
				continue;
		?>
		<tr title="<?php echo $cur_payment['employee']->guid; ?>">
			<td><?php echo htmlspecialchars($cur_payment['employee']->name); ?></td>
			<td><?php echo htmlspecialchars($cur_payment['location']->name); ?></td>
			<td><?php echo htmlspecialchars($cur_payment['pay_type']); ?></td>
			<td><?php echo htmlspecialchars($cur_payment['qty_sold']); ?></td>
			<td><?php echo htmlspecialchars($cur_payment['qty_returned']); ?></td>
			<td class="amount">$<?php echo number_format($cur_payment['total_sold'], 2, '.', ''); ?></td>
			<td class="amount">$<?php echo number_format($cur_payment['total_returned'], 2, '.', ''); ?></td>
			<td><?php echo round($cur_payment['scheduled'] / 3600, 2); ?> hours</td>
			<td><?php echo round($cur_payment['clocked'] / 3600, 2); ?> hours</td>
			<td><span<?php echo ($cur_payment['variance'] < 0 ) ? ' class="negative;"' : ''; ?>><?php echo round($cur_payment['variance'] / 3600, 2); ?> hours</span></td>
			<td class="amount">$<?php echo number_format($cur_payment['commission'], 2, '.', ''); ?></td>
			<td class="amount"><span<?php echo ($cur_payment['penalties'] > 0 ) ? ' class="negative"' : ''; ?>>$<?php echo number_format($cur_payment['penalties'], 2, '.', ''); ?></span></td>
			<td class="amount"><span<?php echo ($cur_payment['bonuses'] < 0 ) ? ' class="negative"' : ''; ?>>$<?php echo number_format($cur_payment['bonuses'], 2, '.', ''); ?></span></td>
			<td class="total"><span<?php echo ($cur_payment['total_pay'] < 0 ) ? ' class="negative"' : ''; ?>>$<?php echo number_format($cur_payment['total_pay'], 2, '.', ''); ?></span></td>
		</tr>
		<?php } ?>
	</tbody>
</table>