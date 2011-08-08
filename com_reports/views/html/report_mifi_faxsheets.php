<?php
/**
 * Shows a list of MiFi faxsheet info.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'MiFi Faxsheets';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_mifi_faxsheets'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid a {
		text-decoration: underline;
	}
	#p_muid_grid .amount {
		text-align: right;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'mifi_faxsheets',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_mifi_faxsheets", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		$("#p_muid_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<div class="pf-element pf-full-width">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Customer</th>
				<th>SSN</th>
				<th>Primary</th>
				<th>Secondary</th>
				<th>Password</th>
				<th>New</th>
				<th>Existing</th>
				<th>Total</th>
				<th>Approved</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->contracts as $cur_contract) {
			$firstnet = $pines->com_mifi->verify_firstnet($cur_contract->application->firstnet(), $cur_contract->customer, $cur_contract->total_monthly_allotment); ?>
			<tr title="<?php echo $cur_contract->guid; ?>">
				<td style="text-align: right;"><a href="<?php echo htmlspecialchars(pines_url('com_mifi', 'contract/view', array('id' => $cur_contract->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_contract->contract_id); ?></a></td>
				<td><?php echo format_date($cur_contract->p_cdate, 'date_sort'); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $cur_contract->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_contract->customer->name); ?></a></td>
				<td><?php echo $cur_contract->customer_info['ssn']; ?></td>
				<td><?php echo htmlspecialchars($cur_contract->faxsheet_request['primary']); ?></td>
				<td><?php echo htmlspecialchars($cur_contract->faxsheet_request['secondary']); ?></td>
				<td><?php echo htmlspecialchars($cur_contract->faxsheet_request['password']); ?></td>
				<td class="amount"><?php echo addslashes('$'.number_format($firstnet['new'], 2)); ?></td>
				<td class="amount"><?php echo addslashes('$'.number_format($firstnet['existing'], 2)); ?></td>
				<td class="amount"><?php echo addslashes('$'.number_format($firstnet['total'], 2)) ?></td>
				<td><?php echo $cur_contract->approved_faxsheet ? htmlspecialchars("Yes ({$cur_contract->approved_faxsheet_user->name})") : 'No'; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>