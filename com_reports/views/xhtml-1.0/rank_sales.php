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
$this->title = 'Sales Rankings: '.$this->location->name.' ('.format_date($this->entity->start_date, 'date_short').' - '.format_date($this->entity->end_date, 'date_short').')';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/rank_sales'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	#sales_rankings_grid td, #sales_rankings_grid th {
		font-weight: bold;
		text-align: center;
	}
	#sales_rankings_grid .total td {
		border-top-width: .2em;
	}
	#sales_rankings_grid .total td.rank, #sales_rankings_grid .total td.ui-pgrid-table-expander {
		background-color: gray;
	}
	#sales_rankings_grid .total td.ui-pgrid-table-expander {
		border-right: none;
	}
	#sales_rankings_grid .green td {
		background-color: green;
		color: black;
	}
	#sales_rankings_grid .yellow td {
		background-color: yellow;
		color: black;
	}
	#sales_rankings_grid .red td {
		background-color: red;
		color: black;
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
				{type: 'button', text: 'Select All', extra_class: 'picon picon_16x16_document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon_16x16_document-close', select_none: true},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon_16x16_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'sales_rankings',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_reports/rank_sales", state: cur_state});
			}
		};
		$("#sales_rankings_grid").pgrid(cur_defaults);
	});
	// ]]>
</script>
<table id="sales_rankings_grid">
	<thead>
		<tr>
			<th style="width: 5%;">Rank</th>
			<th style="width: 20%;">Employee</th>
			<th style="width: 12.5%;">Current</th>
			<th style="width: 12.5%;">Last</th>
			<th style="width: 12.5%;">MTD</th>
			<th style="width: 12.5%;">Goal</th>
			<th style="width: 12.5%;">Trend</th>
			<th style="width: 12.5%;">Trend %</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$total_current = $total_last = $total_mtd = $total_goal = $total_trend = $total_pct = 0;
		foreach($this->rankings as $cur_rank) {
			// Employees are "in the green", "yellow" or "red".
			if ($cur_rank['pct'] >= 100) {
				$class = 'green';
			} elseif ($cur_rank['pct'] >= 80) {
				$class = 'yellow';
			} elseif ($cur_rank['pct'] <= 79) {
				$class = 'red';
			}
			// Update totals for the entire company location(s).
			$total_goal += $cur_rank['goal'];
			$total_trend += $cur_rank['trend'];
			$total_last += $cur_rank['last'];
			$total_current += $cur_rank['current'];
		?>
		<tr title="<?php echo $cur_rank['employee']->guid; ?>" class="<?php echo $class; ?>">
			<td><?php echo $cur_rank['rank']; ?></td>
			<td><?php echo $cur_rank['employee']->name; ?></td>
			<td><?php echo $cur_rank['current']; ?></td>
			<td><?php echo $cur_rank['last']; ?></td>
			<td><?php echo $cur_rank['mtd']; ?></td>
			<td><?php echo $cur_rank['goal']; ?></td>
			<td><?php echo $cur_rank['trend']; ?></td>
			<td><?php echo round($cur_rank['pct'], 2); ?>%</td>
		</tr>
		<?php
		}
		// Account for employees potentially having $0 as a goal.
		if ($total_goal > 0) {
			$total_pct = $total_trend / $total_goal;
		} else {
			$total_pct = 100;
		}
		// Companies / Locations are "in the green", "yellow" or "red".
		if ($total_pct >= 100) {
			$class = 'green';
		} elseif ($total_pct >= 80) {
			$class = 'yellow';
		} elseif ($total_pct <= 79) {
			$class = 'red';
		}
		?>
		<tr class="total <?php echo $class; ?>">
			<td class="rank">&nbsp;</td>
			<td>Total</td>
			<td><?php echo $total_current; ?></td>
			<td><?php echo $total_last; ?></td>
			<td><?php echo $total_mtd; ?></td>
			<td><?php echo $total_goal; ?></td>
			<td><?php echo $total_trend; ?></td>
			<td><?php echo round($total_pct, 2); ?>%</td>
		</tr>
	</tbody>
</table>