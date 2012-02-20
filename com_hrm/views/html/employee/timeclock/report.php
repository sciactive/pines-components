<?php
/**
 * Shows an hours clocked report.
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
$this->title = 'Hours Clocked Report';
?>
<style type="text/css">
	#p_muid_report {
		background: #FFF;
		color: #000;
	}
	#p_muid_report table {
		width: 100%;
	}
</style>
<div id="p_muid_report" class="pf-form">
	<div class="pf-element">
		<span class="pf-label">Generated</span>
		<span class="pf-field"><?php echo htmlspecialchars(format_date(time(), 'full_short')); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Timezone</span>
		<span class="pf-field"><?php echo $this->local_timezones ? 'Based on Employee' : htmlspecialchars(date_default_timezone_get()); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Dates Reported</span>
		<span class="pf-field"><?php echo htmlspecialchars(format_date($this->date_start, 'date_short').' - '.format_date($this->date_end - 1, 'date_short')); ?></span>
	</div>
	<?php $cur_timezone = date_default_timezone_get(); foreach ($this->employees as $cur_employee) {
		if ($this->local_timezones)
			date_default_timezone_set($cur_employee['timezone']);
		?>
	<div class="employee">
		<div class="pf-element pf-heading">
			<h3>
				<?php echo htmlspecialchars($cur_employee['entity']->name);
				if ($this->local_timezones) { ?>
				<small style="float: right;">
					Timezone: <?php echo htmlspecialchars($cur_employee['timezone']); ?>
				</small>
				<?php } ?>
			</h3>
			<?php if (isset($cur_employee['entity']->group->guid)) { ?>
			<p><?php echo htmlspecialchars($cur_employee['entity']->group->name); ?></p>
			<?php } ?>
		</div>
		<table>
			<thead>
				<tr>
					<th>Date</th>
					<th>Hours Clocked</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$cur_day = $cur_employee['date_start'];
				//while 
				?>
				<tr>
					<td>Total Hours</td>
					<td></td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php } date_default_timezone_set($cur_timezone); ?>
</div>