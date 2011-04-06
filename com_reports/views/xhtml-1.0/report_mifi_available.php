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

$this->title = 'Available Financing for MiFi Customers';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_mifi_avialable'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid a {
		text-decoration: underline;
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
						filename: 'mifi_available',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_mifi_available", state: cur_state});
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
				<th>Phone</th>
				<th>Max Approval</th>
				<?php if (gatekeeper('com_mifi/overrideapp')) { ?>
				<th>Online Approval</th>
				<?php } ?>
				<th>Location</th>
				<th>Employee</th>
				<th>Rank</th>
				<th>ETS Date</th>
				<th>Notes</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->applications as $cur_available) { ?>
			<tr title="<?php echo $cur_available->guid; ?>">
				<td style="text-align: right;"><a href="<?php echo htmlspecialchars(pines_url('com_mifi', 'viewoffer', array('id' => $cur_available->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_available->app_id); ?></a></td>
				<td><?php echo format_date($cur_available->p_cdate, 'date_sort'); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $cur_available->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_available->customer->name); ?></a></td>
				<td><?php echo !empty($cur_available->cellPhone) ? format_phone($cur_available->cellPhone) : format_phone($cur_available->hor_phone); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_available->approval_amount, 2, '.', '')); ?></td>
				<?php if (gatekeeper('com_mifi/overrideapp')) { ?>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_available->web_approval_amount, 2, '.', '')); ?></td>
				<?php } ?>
				<td><?php echo htmlspecialchars($cur_available->group->name); ?></td>
				<td><?php echo htmlspecialchars($cur_available->user->name); ?></td>
				<td><?php echo $pines->com_mifi->ranks[$cur_available->militaryPayGrade]; ?></td>
				<td><?php echo format_date($cur_available->ets_date, 'date_sort'); ?></td>
				<td><?php echo implode(', ', $cur_available->notes); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>