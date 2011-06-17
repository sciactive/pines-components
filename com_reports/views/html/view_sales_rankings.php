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
$this->title = 'Sales Rankings: '.$this->entity->name.' ('.format_date($this->entity->start_date, 'date_sort').' - '.format_date($this->entity->end_date - 1, 'date_sort').')';
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/rank_sales'];

// Status levels for in the green, yellow and red classifications.
$green_status = $pines->config->com_reports->rank_level_green;
$yellow_status = $pines->config->com_reports->rank_level_yellow;

$prefix = $pines->config->com_reports->use_points ? '' : '$';
$multiplier = $pines->config->com_reports->use_points ? $pines->config->com_reports->point_multiplier : 1;

?>
<style type="text/css" >
	/* <![CDATA[ */
	.p_muid_grid td, .p_muid_grid th {
		font-weight: bold;
		text-align: center;
	}
	.p_muid_grid td.right_justify {
		text-align: right;
	}
	.p_muid_grid .total td {
		border-top-width: .2em;
		background-color: lightblue;
		color: black;
	}
	.p_muid_grid .total td.rank, .p_muid_grid .total td.ui-pgrid-table-expander {
		background-color: gray;
	}
	.p_muid_grid .total td.ui-pgrid-table-expander {
		border-right: none;
	}
	.p_muid_grid .green td {
		background-color: lightgreen;
		color: black;
	}
	.p_muid_grid .yellow td {
		background-color: yellow;
		color: black;
	}
	.p_muid_grid .red td {
		background-color: red;
		color: black;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$(".p_muid_grid").each(function(){
			var cur_grid = $(this);
			cur_grid.pgrid({
				pgrid_toolbar: true,
				pgrid_toolbar_contents: [
					{type: 'label', label: cur_grid.attr("title")},
					{type: 'separator'},
					{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
					{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
					{type: 'separator'},
					{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
						pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
							filename: 'sales_rankings',
							content: rows
						});
					}}
				],
				pgrid_sortable: true,
				pgrid_sort_col: 1,
				pgrid_sort_ord: 'asc',
				pgrid_paginate: false,
				pgrid_view_height: 'auto',
				pgrid_resize: false
			});
		});
	});
	// ]]>
</script>
<div class="pf-form">
	<?php foreach ($this->locations as $key => $cur_location_rankings) { // TODO: Make this more customizeable. ?>
	<div class="pf-element pf-full-width">
		<table class="p_muid_grid" title="<?php echo $key == count($this->locations)-1 ? 'Location' : 'District'; ?> Rankings">
			<thead>
				<tr>
					<th style="width: 5%;">Rank</th>
					<th style="width: 35%;">Location</th>
					<th style="width: 35%;">Manager</th>
					<th style="width: 10%;">Current</th>
					<th style="width: 10%;">Last</th>
					<th style="width: 10%;">MTD</th>
					<th style="width: 10%;">Goal</th>
					<th style="width: 10%;">Trend</th>
					<th style="width: 10%;">Trend %</th>
					<?php if ($key == count($this->locations)-1) { ?>
					<th style="width: 35%;">Top Rep</th>
					<th style="width: 35%;">Mgr Bns</th>
					<?php } else { ?>
					<th style="width: 35%;">Stores</th>
					<th style="width: 35%;">Avg</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$totals = array('current' => 0.00, 'last' => 0.00, 'mtd' => 0.00, 'goal' => 0.00, 'trend' => 0.00);
				foreach($cur_location_rankings as $cur_rank) {
					// Skip locations with no goal.
					if ($cur_rank['goal'] <= 0)
						continue;
					// Locations are "in the green", "yellow" or "red".
					if ($cur_rank['pct'] >= $green_status) {
						$class = 'green';
					} elseif ($cur_rank['pct'] >= $yellow_status) {
						$class = 'yellow';
					} elseif ($cur_rank['pct'] < $yellow_status) {
						$class = 'red';
					}
					$totals['current'] += $cur_rank['current'];
					$totals['last'] += $cur_rank['last'];
					$totals['mtd'] += $cur_rank['mtd'];
					$totals['goal'] += $cur_rank['goal'];
					$totals['trend'] += $cur_rank['trend'];
				?>
				<tr title="<?php echo $cur_rank['entity']->guid; ?>" class="<?php echo $class; ?>">
					<td><?php echo htmlspecialchars($cur_rank['rank']); ?></td>
					<td><?php echo htmlspecialchars($cur_rank['entity']->name); ?></td>
					<td><?php echo isset($cur_rank['manager']->guid) ? htmlspecialchars($cur_rank['manager']->name) : 'OPEN'; ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['current'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['last'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['mtd'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['goal'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['trend'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo htmlspecialchars(round($cur_rank['pct'], 2)); ?>%</td>
					<?php if ($key == count($this->locations)-1) { ?>
					<td style="text-align: center;"><?php
					if ($cur_rank['pct'] >= 100) {
						echo '$'.sprintf('%01f', ($cur_rank['trend'] * $multiplier) * (4 * min(1.5, $cur_rank['pct'] / 100)) + (($cur_rank['goal'] * $multiplier) >= 80 ? 80 : 0) );
					} else {
						echo '$0';
					}
					?></td>
					<td style="text-align: center;"><?php
					if ($cur_rank['pct'] >= 100) {
						echo '$'.sprintf('%01f', ($cur_rank['trend'] * $multiplier) * (15 * min(1.5, $cur_rank['pct'] / 100)) + (($cur_rank['goal'] * $multiplier) >= 80 ? 1000 : 0) );
					} else {
						echo '$0';
					}
					?></td>
					<?php } else { ?>
					<td style="text-align: center;"><?php echo (int) $cur_rank['child_count']; ?></td>
					<td style="text-align: center;"><?php echo $prefix.htmlspecialchars(round($cur_rank['child_count'] > 0 ? $cur_rank['trend'] / $cur_rank['child_count'] * $multiplier : 0, 2)); ?></td>
					<?php } ?>
				</tr>
				<?php } if ($key == count($this->locations)-1) {
					$totals['pct'] = ($totals['goal'] > 0 ? $totals['trend'] / $totals['goal'] * 100 : 0);
					if ($totals['pct'] >= $green_status) {
						$class = 'green';
					} elseif ($totals['pct'] >= $yellow_status) {
						$class = 'yellow';
					} elseif ($totals['pct'] < $yellow_status) {
						$class = 'red';
					} ?>
				<tr class="total <?php echo $class; ?>">
					<td class="rank"><span style="display: none;">9999999</span></td>
					<td colspan="2">Total</td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($totals['current'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($totals['last'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($totals['mtd'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($totals['goal'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($totals['trend'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo htmlspecialchars(round($totals['pct'], 2)); ?>%</td>
					<td colspan="2"></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
	<div class="pf-element pf-full-width">
		<table class="p_muid_grid" title="Employee Rankings">
			<thead>
				<tr>
					<th style="width: 5%;">Rank</th>
					<th style="width: 35%;">Employee</th>
					<th style="width: 35%;">Location</th>
					<th style="width: 10%;">Current</th>
					<th style="width: 10%;">Last</th>
					<th style="width: 10%;">MTD</th>
					<th style="width: 10%;">Goal</th>
					<th style="width: 10%;">Trend</th>
					<th style="width: 10%;">Trend %</th>
					<th style="width: 35%;">Prizes</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($this->employees as $cur_rank) {
					// Skip employees with no goal.
					if ($cur_rank['goal'] <= 0)
						continue;
					// Employees are "in the green", "yellow" or "red".
					if ($cur_rank['pct'] >= $green_status) {
						$class = 'green';
					} elseif ($cur_rank['pct'] >= $yellow_status) {
						$class = 'yellow';
					} elseif ($cur_rank['pct'] < $yellow_status) {
						$class = 'red';
					}
				?>
				<tr title="<?php echo $cur_rank['entity']->guid; ?>" class="<?php echo $class; ?>">
					<td><?php echo htmlspecialchars($cur_rank['rank']); ?></td>
					<td><?php echo htmlspecialchars($cur_rank['entity']->name); ?></td>
					<td><?php echo htmlspecialchars("{$cur_rank['entity']->group->name} (".preg_replace('/\s.*/', '', $cur_rank['entity']->group->parent->name).')'); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['current'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['last'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['mtd'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['goal'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['trend'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo htmlspecialchars(round($cur_rank['pct'], 2)); ?>%</td>
					<td class="right_justify">
						<?php switch ($cur_rank['rank']) {
							case 1:
								echo '1st - $550';
								break;
							case 2:
								echo '2nd - $500';
								break;
							case 3:
								echo '3rd - $375';
								break;
							case 4:
								echo '4th - $350';
								break;
							case 5:
								echo '5th - $325';
								break;
							case 6:
								echo '6th - $300';
								break;
							case 7:
								echo '7th - $275';
								break;
						} ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-full-width">
		<table class="p_muid_grid" title="New Hire Rankings">
			<thead>
				<tr>
					<th style="width: 5%;">Rank</th>
					<th style="width: 35%;">Employee</th>
					<th style="width: 35%;">Location</th>
					<th style="width: 10%;">Current</th>
					<th style="width: 10%;">Last</th>
					<th style="width: 10%;">MTD</th>
					<th style="width: 10%;">Goal</th>
					<th style="width: 10%;">Trend</th>
					<th style="width: 10%;">Trend %</th>
					<th style="width: 35%;">Prizes</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($this->new_hires as $cur_rank) {
					// Skip employees with no goal.
					if ($cur_rank['goal'] <= 0)
						continue;
					// Employees are "in the green", "yellow" or "red".
					if ($cur_rank['pct'] >= $green_status) {
						$class = 'green';
					} elseif ($cur_rank['pct'] >= $yellow_status) {
						$class = 'yellow';
					} elseif ($cur_rank['pct'] < $yellow_status) {
						$class = 'red';
					}
				?>
				<tr title="<?php echo $cur_rank['entity']->guid; ?>" class="<?php echo $class; ?>">
					<td><?php echo htmlspecialchars($cur_rank['rank']); ?></td>
					<td><?php echo htmlspecialchars($cur_rank['entity']->name); ?></td>
					<td><?php echo htmlspecialchars($cur_rank['entity']->group->name); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['current'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['last'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['mtd'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['goal'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo $prefix.htmlspecialchars(round($cur_rank['trend'] * $multiplier, 2)); ?></td>
					<td class="right_justify"><?php echo htmlspecialchars(round($cur_rank['pct'], 2)); ?>%</td>
					<td class="right_justify">
						<?php switch ($cur_rank['rank']) {
							case 1:
								echo '1st - $200';
								break;
							case 2:
								echo '2nd - $150';
								break;
							case 3:
								echo '3rd - $100';
								break;
						} ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>