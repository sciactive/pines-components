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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Timeclock for '.htmlspecialchars($this->entity->user->name);
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_hrm/employee/timeclock/view']);
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_hrm/employee/timeclock/view", state: cur_state});
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
			<th>Time In</th>
			<th>Time Out</th>
			<th>Time</th>
			<th>Comments</th>
			<th>IP In</th>
			<th>IP Out</th>
			<th>User Agent In</th>
			<th>User Agent Out</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->entries as $key => $entry) { ?>
		<tr title="<?php echo htmlspecialchars($key); ?>">
			<td><?php echo format_date($entry->in, 'custom', 'Y-m-d H:i:s T', $this->entity->user->get_timezone(true)); ?></td>
			<td><?php echo format_date($entry->out, 'custom', 'Y-m-d H:i:s T', $this->entity->user->get_timezone(true)); ?></td>
			<td><?php
				$seconds = $entry->out - $entry->in;
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
				$string .= sprintf('%d', $hours).':'.sprintf('%02d', $minutes).':'.sprintf('%02d', $seconds);
				echo htmlspecialchars($string);
			?></td>
			<td><?php echo htmlspecialchars($entry->comments); ?></td>
			<td><?php echo htmlspecialchars($entry->extras['ip_in']); ?></td>
			<td><?php echo htmlspecialchars($entry->extras['ip_out']); ?></td>
			<td><?php echo htmlspecialchars($entry->extras['ua_in']); ?></td>
			<td><?php echo htmlspecialchars($entry->extras['ua_out']); ?></td>
		</tr>
		<?php } ?>
		<?php if ($this->entity->clocked_in_time()) { ?>
		<tr title="<?php echo htmlspecialchars($key); ?>">
			<td><?php echo format_date($this->entity->clocked_in_time(), 'full_sort', '', $this->entity->user->get_timezone(true)); ?></td>
			<td></td>
			<td><?php
				$seconds = time() - $this->entity->clocked_in_time();
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
				$string .= sprintf('%d', $hours).':'.sprintf('%02d', $minutes).':'.sprintf('%02d', $seconds);
				echo htmlspecialchars($string);
			?></td>
			<td><strong>Currently clocked in.</strong></td>
			<td><?php echo htmlspecialchars($this->entity->ip_in); ?></td>
			<td></td>
			<td><?php echo htmlspecialchars($this->entity->ua_in); ?></td>
			<td></td>
		</tr>
		<?php } ?>
	</tbody>
</table>