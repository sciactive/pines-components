<?php
/**
 * Shows an employees timeclock history.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Employee Timeclock for {$this->user->name} [{$this->user->username}]";
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		function format_time(timestamp) {
			var d = new Date();
			d.setTime(timestamp * 1000);
			return d.toLocaleString();
		}

		$("#timeclock_grid .time").each(function(){
			$(this).html(format_time($(this).html()));
		});
		
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: false,
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_sales/view_clock", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#timeclock_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="timeclock_grid">
	<thead>
		<tr>
			<th>UTC ISO Time</th>
			<th>Local Time</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->user->com_sales->timeclock as $key => $entry) { ?>
		<tr title="<?php echo $key; ?>">
			<td><?php echo gmdate('c', $entry['time']); ?></td>
			<td class="time"><?php echo $entry['time']; ?></td>
			<td><?php echo $entry['status']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>