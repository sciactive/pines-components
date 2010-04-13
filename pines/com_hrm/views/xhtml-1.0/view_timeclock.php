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
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: false,
			pgrid_select: false,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_hrm/view_timeclock", state: cur_state});
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
			<th>Local Time</th>
			<th>UTC ISO Time</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->entity->timeclock as $key => $entry) { ?>
		<tr title="<?php echo $key; ?>" class="<?php echo ($entry['status'] == 'in') ? 'ui-state-active' : 'ui-state-default'; ?>">
			<td><?php echo pines_date_format($entry['time'], $this->entity->get_timezone(true)); ?></td>
			<td><?php echo gmdate('c', $entry['time']); ?></td>
			<td><?php echo $entry['status']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>