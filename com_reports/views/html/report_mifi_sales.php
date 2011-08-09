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
			pines.get("<?php echo addslashes(pines_url('com_reports', 'reportmifisales')); ?>", {
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
				<th>Sale</th>
				<th>Date</th>
				<th>Loc</th>
				<th>Status</th>
				<th>Emp</th>
				<th>Customer</th>
				<th>Subtotal</th>
				<th>Taxes</th>
				<th>Total</th>
				<th>Rank</th>
				<th>ETS Date</th>
				<th>Credit Score</th>
				<th>Company</th>
				<th>Contract</th>
				<th>Faxsheet</th>
				<th>Comments</th>
				<th>Other Sales</th>
				<th>Returns</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->sales as $cur_sale) {
				$contract = $pines->entity_manager->get_entity(
						array('class' => com_mifi_contract),
						array('&',
							'tag' => array('com_mifi', 'contract'),
							'ref' => array('sale', $cur_sale)
						)
					);
				if (!isset($contract->guid))
					continue;

				// Find other sales to this customer and add up the total.
				$other_sales = $pines->entity_manager->get_entities(
						array('class' => com_sales_sale),
						array('&',
							'tag' => array('com_sales', 'sale'),
							'ref' => array('customer', $cur_sale->customer)
						),
						array('!&',
							'guid' => $cur_sale->guid
						)
					);
				$other = 0;
				foreach ($other_sales as $cur_other) {
					$other += $cur_other->total;
				}

				// Find returns to this customer and add up the total.
				$returns = $pines->entity_manager->get_entities(
						array('class'=> com_sales_sale),
						array('&',
							'tag' => array('com_sales', 'return'),
							'ref' => array('customer', $cur_sale->customer)
						)
					);
				$return = 0;
				foreach ($returns as $cur_return) {
					$return += $cur_return->total;
				}
			?>
			<tr title="<?php echo $cur_sale->guid; ?>">
				<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->id); ?></a></td>
				<td><?php echo format_date($cur_sale->p_cdate, 'date_sort'); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_user', 'editgroup', array('id' => $cur_sale->group->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->group->name); ?></a></td>
				<td><?php echo htmlspecialchars(ucwords($cur_sale->status)); ?></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_user', 'edituser', array('id' => $cur_sale->user->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->user->name); ?></a></td>
				<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id'=> $cur_sale->customer->guid)));?>" onclick="window.open(this.href);return false;"><?php echo htmlspecialchars($cur_sale->customer->name);?></a></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->subtotal, 2, '.', '')); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->taxes, 2, '.', '')); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars(number_format($cur_sale->total, 2, '.', '')); ?></td>
				<td><?php echo $pines->com_mifi->ranks[$contract->militaryPayGrade]; ?></td>
				<td><?php echo $contract->indefiniteETS ? 'Indefinite' : format_date($contract->ets_date, 'date_sort'); ?></td>
				<td style="text-align: right;"><?php echo htmlspecialchars($contract->credit_score); ?></td>
				<td><?php echo $pines->com_mifi->companies[$contract->company]['name']; ?></td>
				<td style="text-align: right;"><a href="<?php echo htmlspecialchars(pines_url('com_mifi', 'viewoffer', array('id' => $contract->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($contract->contract_id); ?></a></td>
				<td><?php echo ($contract->approved_faxsheet) ? 'Approved' : (isset($contract->faxsheet_request) ? 'Requested' : 'None'); ?></td>
				<td><?php echo ($contract->verified_sst) ? 'Yes' : 'No'; ?></td>
				<td>$<?php echo htmlspecialchars($other); ?></td>
				<td>$<?php echo htmlspecialchars($return); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>