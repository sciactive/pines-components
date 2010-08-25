<?php
/**
 * Shows an employees timeclock history.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Employee Timeclock for {$this->entity->name}";
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/employee/timeclock/view'];
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: false,
			pgrid_select: false,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_row_hover_effect: false,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_hrm/employee/timeclock/view", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>UTC ISO Time</th>
			<th>Formatted Time</th>
			<th>Status</th>
			<th>Time Difference</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->entity->timeclock as $key => $entry) { ?>
		<tr title="<?php echo htmlspecialchars($key); ?>" class="<?php echo ($entry['status'] == 'in') ? 'ui-state-active' : 'ui-state-highlight'; ?>">
			<td><?php echo gmdate('c', $entry['time']); ?></td>
			<td><?php echo format_date($entry['time'], 'full_long', '', $this->entity->get_timezone(true)); ?></td>
			<td><?php echo htmlspecialchars($entry['status']); ?></td>
			<td><?php if (isset($last_time)) {
				$seconds = $entry['time'] - $last_time;
				$days = floor($seconds / 86400);
				$seconds %= 86400;
				$hours = floor($seconds / 3600);
				$seconds %= 3600;
				$minutes = floor($seconds / 60);
				$seconds %= 60;
				$string = '';
				if ($days > 0) {
					$string .= "{$days}d ";
				}
				if ($hours > 0) {
					$string .= "{$hours}h ";
				}
				if ($minutes > 0) {
					$string .= "{$minutes}m ";
				}
				if ($seconds > 0) {
					$string .= "{$seconds}s";
				}
				echo htmlspecialchars(trim($string));
			} ?></td>
		</tr>
	<?php $last_time = $entry['time']; } ?>
	</tbody>
</table>